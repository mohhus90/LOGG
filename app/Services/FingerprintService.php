<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shifts_type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ZKTecoService
 *
 * يتصل بأجهزة البصمة مباشرةً عبر الشبكة دون برنامج وسيط.
 *
 * المكتبات المدعومة:
 *  - ZKTeco/Suprema/Anviz: عبر TCP socket بروتوكول ZKLib (المنفذ 4370)
 *  - Hikvision/Dahua: عبر HTTP REST API
 *  - Generic: HTTP Webhook (الجهاز يرسل إلى السيرفر)
 *
 * التركيب:
 *   composer require rats/zkteco
 *   (مكتبة PHP خفيفة للتواصل مع أجهزة ZKTeco عبر TCP)
 */
class FingerprintService
{
    // ─────────────────────────────────────────────
    //  جلب السجلات من جهاز ZKTeco عبر TCP
    // ─────────────────────────────────────────────
    public function syncDevice(FingerprintDevice $device): array
    {
        $result = ['success' => false, 'count' => 0, 'error' => null];

        try {
            switch ($device->protocol) {
                case 'zkteco':
                case 'suprema':
                    $result = $this->syncZKTeco($device);
                    break;

                case 'hikvision':
                    $result = $this->syncHikvision($device);
                    break;

                case 'dahua':
                    $result = $this->syncDahua($device);
                    break;

                case 'anviz':
                    $result = $this->syncAnviz($device);
                    break;

                case 'agent':
                    // أجهزة Agent ترسل البيانات تلقائياً — لا يمكن الاتصال بها مباشرة
                    return ['success' => true, 'count' => 0, 'error' => null];

                default:
                    $result['error'] = 'البروتوكول غير مدعوم: ' . $device->protocol;
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            Log::error("FingerprintService::syncDevice [{$device->device_name}] " . $e->getMessage());
        }

        // تحديث حالة الجهاز
        $device->update([
            'last_sync_at'      => now(),
            'last_sync_records' => $result['count'],
            'last_error'        => $result['error'],
            'status'            => $result['success'] ? 1 : 3,
        ]);

        return $result;
    }

    // ─────────────────────────────────────────────
    //  ZKTeco TCP Protocol (rats/zkteco package)
    // ─────────────────────────────────────────────
    private function syncZKTeco(FingerprintDevice $device): array
    {
        // التحقق من وجود مكتبة ZKTeco
        if (!class_exists('\Rats\Zkteco\Lib\ZKTeco')) {
            return [
                'success' => false, 'count' => 0,
                'error'   => 'مكتبة ZKTeco غير مثبتة. شغّل: composer require rats/zkteco',
            ];
        }

        $zk = new \Rats\Zkteco\Lib\ZKTeco($device->ip_address, $device->port);

        if (!$zk->connect()) {
            return ['success' => false, 'count' => 0, 'error' => 'تعذّر الاتصال بالجهاز على ' . $device->ip_address . ':' . $device->port];
        }

        // تعطيل الجهاز أثناء القراءة لتجنب تعارض البيانات
        $zk->disableDevice();

        try {
            $attendanceLogs = $zk->getAttendance();
        } finally {
            $zk->enableDevice();
            $zk->disconnect();
        }

        if (empty($attendanceLogs)) {
            return ['success' => true, 'count' => 0, 'error' => null];
        }

        return $this->saveLogs($device, $attendanceLogs, 'zkteco');
    }

    // ─────────────────────────────────────────────
    //  Hikvision HTTP REST API
    // ─────────────────────────────────────────────
    private function syncHikvision(FingerprintDevice $device): array
    {
        $baseUrl = "http://{$device->ip_address}:{$device->port}";
        $auth    = base64_encode("admin:" . ($device->password ?? 'admin'));

        // جلب آخر 1000 سجل
        $url      = "$baseUrl/ISAPI/AccessControl/AcsEvent?format=json";
        $response = $this->httpGet($url, ['Authorization' => "Basic $auth"]);

        if (!$response['ok']) {
            return ['success' => false, 'count' => 0, 'error' => 'Hikvision API: ' . $response['error']];
        }

        $data = json_decode($response['body'], true);
        $logs = [];

        foreach ($data['AcsEvent']['InfoList'] ?? [] as $event) {
            if (!isset($event['employeeNoString']) || !isset($event['time'])) continue;
            $logs[] = [
                'uid'        => $event['employeeNoString'],
                'id'         => $event['employeeNoString'],
                'state'      => 0,
                'timestamp'  => Carbon::parse($event['time'])->format('Y-m-d H:i:s'),
                'type'       => 0,
            ];
        }

        return $this->saveLogs($device, $logs, 'hikvision');
    }

    // ─────────────────────────────────────────────
    //  Dahua HTTP REST API
    // ─────────────────────────────────────────────
    private function syncDahua(FingerprintDevice $device): array
    {
        $baseUrl  = "http://{$device->ip_address}:{$device->port}";
        $response = $this->httpGet(
            "$baseUrl/cgi-bin/recordUpdater.cgi?action=getFile&name=AccessControl&StartTime=00:00:00&EndTime=23:59:59",
            ['Authorization' => 'Basic ' . base64_encode("admin:" . ($device->password ?? 'admin'))]
        );

        if (!$response['ok']) {
            return ['success' => false, 'count' => 0, 'error' => 'Dahua API: ' . $response['error']];
        }

        // تحليل استجابة Dahua النصية
        $logs = [];
        foreach (explode("\n", $response['body']) as $line) {
            if (preg_match('/CardNo=(\d+).*Time=([^\s]+)/', $line, $m)) {
                $logs[] = ['uid' => $m[1], 'id' => $m[1], 'state' => 0, 'timestamp' => $m[2], 'type' => 0];
            }
        }

        return $this->saveLogs($device, $logs, 'dahua');
    }

    // ─────────────────────────────────────────────
    //  Anviz TCP Protocol
    // ─────────────────────────────────────────────
    private function syncAnviz(FingerprintDevice $device): array
    {
        // Anviz يستخدم بروتوكول TCP مشابه لـ ZKTeco
        // يمكن استخدام نفس مكتبة ZKTeco مع تعديل البورت
        return $this->syncZKTeco($device);
    }

    // ─────────────────────────────────────────────
    //  حفظ السجلات الخام في fingerprint_logs
    // ─────────────────────────────────────────────
    public function saveLogs(FingerprintDevice $device, array $logs, string $protocol): array
    {
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($logs as $log) {
                // استخراج finger_id ووقت البصمة حسب بروتوكول كل جهاز
                $fingerId  = (int)($log['id']  ?? $log['uid'] ?? 0);
                $punchTime = $log['timestamp'] ?? null;

                if (!$fingerId || !$punchTime) continue;

                try {
                    $punchCarbon = Carbon::parse($punchTime);
                } catch (\Exception $e) {
                    continue;
                }

                // تجنب تكرار نفس السجل
                $exists = FingerprintLog::where('device_id', $device->id)
                    ->where('finger_id', $fingerId)
                    ->where('punch_time', $punchCarbon->format('Y-m-d H:i:s'))
                    ->exists();

                if (!$exists) {
                    FingerprintLog::create([
                        'device_id'   => $device->id,
                        'finger_id'   => $fingerId,
                        'punch_time'  => $punchCarbon,
                        'punch_type'  => (int)($log['type'] ?? 0),
                        'is_processed'=> 0,
                        'com_code'    => $device->com_code,
                    ]);
                    $count++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'count' => 0, 'error' => $e->getMessage()];
        }

        return ['success' => true, 'count' => $count, 'error' => null];
    }

    // ─────────────────────────────────────────────
    //  إعادة معالجة بصمة موظف محدد ليوم محدد بشيفت جديد
    //  يُستخدم عند تغيير الشيفت المخصص لسجل حضور معين
    // ─────────────────────────────────────────────
    public function reprocessAttendanceFromLogs(
        Attendance $attendance,
        Employee $employee,
        Shifts_type $shift
    ): array {
        $fingerId = (int)$employee->finger_id;
        if (!$fingerId) {
            return ['success' => false, 'error' => 'الموظف ليس لديه رقم بصمة مسجّل'];
        }

        $date        = $attendance->attendance_date->format('Y-m-d');
        $nextDateStr = Carbon::parse($date)->addDay()->format('Y-m-d');

        // موظف غير نشط: إذا كان التاريخ بعد تاريخ الاستقالة → تحويل السجل لغياب إجباري بيوم ثابت
        if ($employee->functional_status == 2 && $employee->resignation_date && $date > $employee->resignation_date) {
            $attendance->check_in_time          = null;
            $attendance->check_out_time         = null;
            $attendance->missing_punch          = null;
            $attendance->status                 = 2;
            $attendance->late_minutes           = 0;
            $attendance->overtime_hours         = 0;
            $attendance->overtime_amount        = 0;
            $attendance->late_deduction         = 0;
            $attendance->absence_deduction_days = 1.0;
            $attendance->notes                  = 'غياب بعد ترك العمل';
            $attendance->save();
            return ['success' => false, 'resigned' => true, 'error' => 'تاريخ بعد ترك الموظف للعمل'];
        }
        $isNight     = $shift->to_time < $shift->from_time;

        // نافذة البحث حسب نوع الشيفت:
        // - ليلي:  من (D from_time - 3h) إلى (D+1 to_time + 3h)
        // - نهاري: من (D from_time - 3h) إلى (D+1 from_time - 1د) — يشمل الأوفرتايم
        if ($isNight) {
            $windowStart = Carbon::parse($date . ' ' . $shift->from_time)->subHours(3);
            $windowEnd   = Carbon::parse($nextDateStr . ' ' . $shift->to_time)->addHours(3);
        } else {
            $windowStart    = Carbon::parse($date . ' ' . $shift->from_time)->subHours(3);
            $windowEndFull  = Carbon::parse($nextDateStr . ' ' . $shift->from_time)->subMinute();
            $overnightCutoff = Carbon::parse($nextDateStr . ' 06:00:00');
            // لا نلتقط بصمات بعد 06:00 ص من اليوم التالي — فهي حضور جديد لـ D+1
            $windowEnd = $windowEndFull->lt($overnightCutoff) ? $windowEndFull : $overnightCutoff;
        }

        // أجهزة الفرع الخاص بالموظف
        $deviceIds = $this->getDeviceIdsForEmployee($employee);

        $logsQuery = FingerprintLog::where('com_code', $employee->com_code)
            ->where('finger_id', $fingerId)
            ->whereBetween('punch_time', [
                $windowStart->format('Y-m-d H:i:s'),
                $windowEnd->format('Y-m-d H:i:s'),
            ])
            ->orderBy('punch_time');

        if (!empty($deviceIds)) {
            $logsQuery->whereIn('device_id', $deviceIds);
        }

        $logs = $logsQuery->get();

        if ($logs->isEmpty()) {
            // إذا كان وقت الحضور المحفوظ خارج النافذة الصالحة → بيانات قديمة خاطئة، نمسحها
            $storedCheckIn = $attendance->check_in_time
                ? Carbon::parse($date . ' ' . $attendance->check_in_time)
                : null;
            if ($storedCheckIn && $storedCheckIn->lt($windowStart)) {
                $attendance->check_in_time   = null;
                $attendance->check_out_time  = null;
                $attendance->missing_punch   = null;
                $attendance->status          = 2;
                $attendance->late_minutes    = 0;
                $attendance->overtime_hours  = 0;
                $attendance->overtime_amount = 0;
                $attendance->late_deduction  = 0;
                $attendance->save();
                return [
                    'success' => false,
                    'cleared' => true,
                    'error'   => "لا توجد بصمات بين {$windowStart->format('Y-m-d H:i')} و {$windowEnd->format('Y-m-d H:i')} — تم مسح البيانات القديمة وتعيين الغياب",
                ];
            }
            return [
                'success' => false,
                'error'   => "لا توجد بصمات بين {$windowStart->format('Y-m-d H:i')} و {$windowEnd->format('Y-m-d H:i')}",
            ];
        }

        $isSingle = $logs->count() === 1;
        $checkIn  = $logs->first()->punch_time->format('H:i');
        $checkOut = $isSingle ? null : $logs->last()->punch_time->format('H:i');

        $attendance->check_in_time  = $checkIn;
        $attendance->check_out_time = $checkOut;
        $attendance->missing_punch  = $isSingle ? 'out' : null;
        $attendance->status         = 1;

        FingerprintLog::whereIn('id', $logs->pluck('id'))->update(['is_processed' => 1]);

        return [
            'success'  => true,
            'checkIn'  => $checkIn,
            'checkOut' => $checkOut,
            'punches'  => $logs->count(),
        ];
    }

    // يُعيد معرّفات أجهزة البصمة المرتبطة بفرع الموظف
    private function getDeviceIdsForEmployee(Employee $employee): array
    {
        if (!$employee->branches_id) {
            return [];
        }

        return FingerprintDevice::where('com_code', $employee->com_code)
            ->get()
            ->filter(fn(FingerprintDevice $d) =>
                $d->branches_id === $employee->branches_id
                || in_array($employee->branches_id, $d->extra_branch_ids ?? [])
            )
            ->pluck('id')
            ->toArray();
    }

    // ─────────────────────────────────────────────
    //  معالجة السجلات الخام → جدول الحضور
    //  يدعم نطاق تاريخ، إعادة المعالجة، الشيفت الليلي، والبصمة الناقصة
    // ─────────────────────────────────────────────
    public function processLogs(
        int $comCode,
        string $dateFrom,
        string $dateTo,
        bool $forceReprocess = false,
        ?int $employeeId = null
    ): array {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->startOfDay();

        // تحميل الموظفين: النشطون + المستقيلون الذين تاريخ تركهم ضمن نطاق الشهر المعالَج
        $fromMonthStart = $from->copy()->startOfMonth()->toDateString();
        $toMonthEnd     = $to->copy()->endOfMonth()->toDateString();

        $empQuery = Employee::with('shifts_type')
            ->where('com_code', $comCode)
            ->where(function ($q) use ($fromMonthStart, $toMonthEnd) {
                $q->where('functional_status', 1)
                  ->orWhere(function ($q2) use ($fromMonthStart, $toMonthEnd) {
                      $q2->where('functional_status', 2)
                         ->whereNotNull('resignation_date')
                         ->whereBetween('resignation_date', [$fromMonthStart, $toMonthEnd]);
                  });
            });
        if ($employeeId) {
            $empQuery->where('id', $employeeId);
        }
        $allEmployees = $empQuery->get();

        // الموظفون النشطون فقط → يُستخدمون في خرائط البصمة والفلتر
        // (الموظفون غير النشطين يدخلون حلقة الغياب فقط، ولا يُضافون للخرائط)
        $activeEmployees = $allEmployees->where('functional_status', 1)->values();

        // finger_ids المطلوبة للفلتر (null = جميع الموظفين) — مبنية على النشطين فقط
        $filteredFingerIds = $employeeId
            ? $activeEmployees->pluck('finger_id')->filter()->values()->toArray()
            : null;

        // إذا forceReprocess: إعادة ضبط is_processed للسجلات في النطاق (+ يوم احتياطي للشيفت الليلي)
        if ($forceReprocess) {
            $fpQ = FingerprintLog::where('com_code', $comCode)
                ->where('is_processed', 1)
                ->whereBetween('punch_time', [
                    $from->copy()->subDay()->format('Y-m-d H:i:s'),
                    $to->copy()->addDays(2)->format('Y-m-d H:i:s'),
                ]);
            if ($filteredFingerIds !== null) {
                $fpQ->whereIn('finger_id', $filteredFingerIds ?: [0]);
            }
            $fpQ->update(['is_processed' => 0]);
        }

        // تحميل إعدادات الشركة مرة واحدة قبل الحلقة
        $settings = \App\Models\Admin_panel_setting::where('com_code', $comCode)->first();

        // بناء معاملات الحساب لكل موظف وتاريخ (يراعي إعدادات الشركة كاملاً)
        $buildParams = function (Employee $emp, string $date) use ($settings): array {
            $dayDivisor = match ((int)($settings->day_rate_divisor_type ?? 1)) {
                2 => 30,
                3 => Carbon::parse($date)->daysInMonth,
                4 => max(1, (float)($settings->day_rate_divisor_custom ?? 26)),
                default => 26,
            };
            $dailyRate = $emp->emp_sal ? ($emp->emp_sal / $dayDivisor) : 0;

            $settingsRate   = (float)($settings->overtime_multiplier ?? 1.5);
            $overtimeMult   = ($settingsRate == 0.0 || !($emp->overtime_enabled ?? 1))
                ? 0.0
                : (float)($emp->custom_overtime_multiplier ?? $settingsRate);
            $sanctionsMult  = max(1.0, (float)($settings->sanctions_value_minute_delay ?? 1));

            return [
                'dailyRate'                => $dailyRate,
                'overtimeMultiplier'       => $overtimeMult,
                'sanctionsMultiplier'      => $sanctionsMult,
                'overtimeEnabled'          => (bool)($emp->overtime_enabled ?? 1),
                'lateDeductEnabled'        => (bool)($emp->late_deduction_enabled ?? 1),
                'hourDivisorType'          => (int)($settings->hour_rate_divisor_type ?? 1),
                'hourDivisorCustom'        => max(1.0, (float)($settings->hour_rate_divisor_custom ?? 8)),
                'graceMinutes'             => (float)($settings->after_minute_calc_delay ?? 0),
                'graceEarlyMinutes'        => (float)($settings->after_minute_calc_early ?? 0),
                'delayCalcMode'            => (int)($settings->delay_calc_mode ?? 1),
                'afterMinuteQuarterday'    => (float)($settings->after_minute_quarterday ?? 0),
                'delayTier1Minutes'        => (float)($settings->delay_tier1_minutes ?? 0),
                'delayHalfDayMinutes'      => (float)($settings->delay_halfday_minutes ?? 0),
                'delayFullDayMinutes'      => (float)($settings->delay_fullday_minutes ?? 0),
                'earlyHalfDayMinutes'      => (float)($settings->early_departure_halfday_minutes ?? 0),
                'earlyFullDayMinutes'      => (float)($settings->early_departure_fullday_minutes ?? 0),
                'earlyFullPlusHalfMinutes' => (float)($settings->early_departure_fullplushalf_minutes ?? 0),
            ];
        };

        // خرائط البصمة → فقط النشطون (لمنع التعارض مع رقم بصمة مشترك بين فرعين)
        $branchFingerMap = [];
        $fallbackMap     = $activeEmployees->keyBy(fn($e) => (int)$e->finger_id);
        foreach ($activeEmployees as $emp) {
            if ($emp->branches_id) {
                $branchFingerMap[$emp->branches_id . '_' . (int)$emp->finger_id] = $emp;
            }
        }

        $totalImported = 0;
        $totalMissing  = 0;
        $totalAbsent   = 0;
        $notFound      = [];

        // معالجة يوم يوم
        $currentDate = $from->copy();
        while ($currentDate->lte($to)) {
            $dateStr = $currentDate->format('Y-m-d');

            // تحميل بصمات اليوم الحالي
            $dayLogsQ = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('is_processed', 0)
                ->whereDate('punch_time', $dateStr);
            if ($filteredFingerIds !== null) {
                $dayLogsQ->whereIn('finger_id', $filteredFingerIds ?: [0]);
            }
            $dayLogs = $dayLogsQ->orderBy('punch_time')->get();

            // تحميل بصمات اليوم التالي (للشيفت الليلي والأوفرتايم النهاري)
            $nextDateStr = $currentDate->copy()->addDay()->format('Y-m-d');
            $nextDayLogsQ = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('is_processed', 0)
                ->whereDate('punch_time', $nextDateStr);
            if ($filteredFingerIds !== null) {
                $nextDayLogsQ->whereIn('finger_id', $filteredFingerIds ?: [0]);
            }
            $nextDayLogs = $nextDayLogsQ->orderBy('punch_time')->get();

            // تجميع بصمات اليوم بـ (device_id + finger_id)
            $grouped = [];
            foreach ($dayLogs as $log) {
                $key = $log->device_id . '_' . $log->finger_id;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'finger_id'        => $log->finger_id,
                        'branch_id'        => $log->device?->branches_id,
                        'extra_branch_ids' => $log->device?->extra_branch_ids ?? [],
                        'punches'          => [],
                    ];
                }
                $grouped[$key]['punches'][] = $log;
            }

            // تجميع بصمات اليوم التالي بنفس المفتاح
            $nextGrouped = [];
            foreach ($nextDayLogs as $log) {
                $key = $log->device_id . '_' . $log->finger_id;
                if (!isset($nextGrouped[$key])) {
                    $nextGrouped[$key] = [];
                }
                $nextGrouped[$key][] = $log;
            }

            $presentEmployeeIds = [];
            $imported = 0;
            $missing  = 0;
            $absent   = 0;

            DB::beginTransaction();
            try {
                foreach ($grouped as $key => $group) {
                    $fingerId = $group['finger_id'];
                    $branchId = $group['branch_id'];

                    $extraBranchIds = $group['extra_branch_ids'] ?? [];

                    if ($branchId) {
                        $employee = $branchFingerMap[$branchId . '_' . $fingerId] ?? null;
                        // البحث في الفروع الإضافية المرتبطة بالجهاز
                        if (!$employee) {
                            foreach ($extraBranchIds as $extraId) {
                                $employee = $branchFingerMap[(int)$extraId . '_' . $fingerId] ?? null;
                                if ($employee) break;
                            }
                        }
                        // إذا لم يُعثر عليه عبر الفرع → fallback بـ finger_id فقط
                        // (نفس منطق reprocessAttendanceFromLogs التي تبحث بـ finger_id + com_code)
                        if (!$employee) {
                            $employee = $fallbackMap->get((int)$fingerId);
                        }
                    } else {
                        $employee = $fallbackMap->get((int)$fingerId);
                    }

                    if (!$employee) {
                        // لا نُعلّم البصمات كـ processed — ربما يُضاف الموظف/الجهاز لاحقاً
                        $notFound[] = $fingerId . ($branchId ? " (فرع {$branchId})" : '');
                        continue;
                    }

                    // موظف غير نشط: تجاهل بصماته بعد تاريخ الاستقالة وتحويل أي سجل موجود لغياب
                    if ($employee->functional_status == 2 && $employee->resignation_date && $dateStr > $employee->resignation_date) {
                        // تعليم البصمات كـ processed لمنع إعادة معالجتها مستقبلاً
                        $logIds = collect($group['punches'])->pluck('id')->toArray();
                        if ($logIds) {
                            FingerprintLog::whereIn('id', $logIds)->update(['is_processed' => 1]);
                        }
                        // تحديث السجل الموجود (حضر من معالجة سابقة) إلى غياب إن وُجد
                        Attendance::where('employee_id', $employee->id)
                            ->where('attendance_date', $dateStr)
                            ->where('is_manual_lock', 0)
                            ->update([
                                'status'          => 2,
                                'check_in_time'   => null,
                                'check_out_time'  => null,
                                'missing_punch'   => null,
                                'late_minutes'    => 0,
                                'overtime_hours'  => 0,
                                'overtime_amount' => 0,
                                'late_deduction'  => 0,
                                'notes'           => 'غياب بعد ترك العمل',
                            ]);
                        continue;
                    }

                    $shift = $employee->shifts_type;

                    // هل الشيفت ليلي (ينتهي في اليوم التالي)؟
                    $isNightShift = $shift && ($shift->to_time < $shift->from_time);

                    $punches        = collect($group['punches'])->sortBy('punch_time')->values();
                    $nextDayPunches = collect($nextGrouped[$key] ?? [])->sortBy('punch_time')->values();

                    if ($isNightShift && $shift) {
                        /*
                         * نافذة الشيفت الليلي ليوم D:
                         *   البداية : D + from_time − 3 ساعات
                         *   النهاية : D+1 + to_time + 3 ساعات
                         *
                         * أي بصمة على D قبل حد البداية تنتمي لانصراف شيفت D-1.
                         */
                        $windowStart = Carbon::parse($dateStr . ' ' . $shift->from_time)->subHours(3);
                        $windowEnd   = Carbon::parse($nextDateStr . ' ' . $shift->to_time)->addHours(3);

                        $excludedPunches = $punches->filter(fn($l) => $l->punch_time->lt($windowStart))->values();
                        $punches         = $punches->filter(fn($l) => $l->punch_time->gte($windowStart))->values();

                        // بصمات اليوم التالي ضمن نهاية النافذة = انصراف شيفت اليوم
                        $nextRelevant = $nextDayPunches->filter(fn($l) => $l->punch_time->lte($windowEnd))->values();
                        if ($nextRelevant->isNotEmpty()) {
                            $punches = $punches->concat($nextRelevant)->sortBy('punch_time')->values();
                        }

                        // البصمات المُستبعدة تنتمي لانصراف شيفت D-1 (مفقود)
                        if ($excludedPunches->isNotEmpty()) {
                            $prevDateStr = $currentDate->copy()->subDay()->format('Y-m-d');
                            $prevAtt = Attendance::where('employee_id', $employee->id)
                                                 ->where('attendance_date', $prevDateStr)
                                                 ->whereNull('check_out_time')
                                                 ->first();
                            if ($prevAtt && !$prevAtt->is_manual_lock) {
                                $lastExcluded = $excludedPunches->last();
                                $prevAtt->check_out_time = $lastExcluded->punch_time->format('H:i');
                                $prevAtt->missing_punch  = null;
                                if ($prevAtt->check_in_time) {
                                    $p = $buildParams($employee, $prevDateStr);
                                    $prevAtt->calculateDelayAndOvertime($p['graceMinutes'], $p['graceEarlyMinutes']);
                                    $prevAtt->calculateAmounts(
                                        $p['dailyRate'], $p['overtimeMultiplier'], $p['sanctionsMultiplier'],
                                        $p['overtimeEnabled'], $p['lateDeductEnabled'],
                                        $p['hourDivisorType'], $p['hourDivisorCustom'],
                                        $p['delayCalcMode'], $p['afterMinuteQuarterday'],
                                        $p['delayTier1Minutes'], $p['delayHalfDayMinutes'], $p['delayFullDayMinutes'],
                                        $p['earlyHalfDayMinutes'], $p['earlyFullDayMinutes'], $p['earlyFullPlusHalfMinutes']
                                    );
                                }
                                $prevAtt->save();
                            }
                            FingerprintLog::whereIn('id', $excludedPunches->pluck('id')->toArray())
                                ->update(['is_processed' => 1]);
                        }

                        if ($punches->isEmpty()) {
                            continue;
                        }
                    }

                    // ── شيفت نهاري: نافذة [D from_time−3h , D+1 min(from_time, 06:00)] ──────
                    // نستبعد بصمات الفجر الباكر (قد تكون أوفرتايم من D-1)
                    // نُضيف أوفرتايم D فقط إذا كانت قبل 06:00 ص من اليوم التالي
                    // (بصمة بعد 06:00 ص تُعدّ حضوراً جديداً لـ D+1، وليست أوفرتايم)
                    if (!$isNightShift && $shift) {
                        $dayWindowStart = Carbon::parse($dateStr . ' ' . $shift->from_time)->subHours(3);
                        $punches = $punches->filter(fn($l) => $l->punch_time->gte($dayWindowStart))->values();

                        if ($nextDayPunches->isNotEmpty()) {
                            $nextShiftStart  = Carbon::parse($nextDateStr . ' ' . $shift->from_time);
                            $overnightCutoff = Carbon::parse($nextDateStr . ' 06:00:00');
                            $overtimePunches = $nextDayPunches
                                ->filter(fn($l) =>
                                    $l->punch_time->lt($nextShiftStart) &&
                                    $l->punch_time->lt($overnightCutoff)
                                )
                                ->values();
                            if ($overtimePunches->isNotEmpty()) {
                                $punches = $punches->concat($overtimePunches)->sortBy('punch_time')->values();
                            }
                        }
                    }

                    // إذا بقيت $punches فارغة بعد التصفية → غياب (لا بصمة صالحة لهذا الشيفت)
                    if ($punches->isEmpty()) {
                        continue;
                    }

                    // يُعدّ حاضراً فقط عند وجود بصمة صالحة لشيفت هذا اليوم
                    $presentEmployeeIds[] = $employee->id;

                    $firstPunch   = $punches->first();
                    $lastPunch    = $punches->last();
                    $allLogIds    = $punches->pluck('id')->toArray();
                    $isSinglePunch = $punches->count() === 1;

                    $checkIn  = $firstPunch->punch_time->format('H:i');
                    $checkOut = $isSinglePunch ? null : $lastPunch->punch_time->format('H:i');

                    $att = Attendance::firstOrNew([
                        'employee_id'     => $employee->id,
                        'attendance_date' => $dateStr,
                    ]);

                    // سجل مثبَّت يدوياً — لا تُعدِّله أي معالجة بصمة
                    if ($att->exists && $att->is_manual_lock) {
                        FingerprintLog::whereIn('id', $allLogIds)->update(['is_processed' => 1]);
                        $presentEmployeeIds[] = $employee->id;
                        continue;
                    }

                    // إذا وصلت بصمة واحدة جديدة لموظف عنده حضور مُسجل مع انصراف مفقود
                    // → البصمة الجديدة هي الانصراف وليست حضوراً جديداً، ويجب الحفاظ على وقت الحضور المُخزن
                    if ($isSinglePunch && $att->exists && $att->check_in_time && $att->missing_punch === 'out') {
                        $newPunchTime  = $firstPunch->punch_time;
                        $storedCheckIn = Carbon::parse($dateStr . ' ' . $att->check_in_time);
                        if ($newPunchTime->gt($storedCheckIn)) {
                            $checkIn      = $att->check_in_time;
                            $checkOut     = $newPunchTime->format('H:i');
                            $isSinglePunch = false;
                        }
                    }

                    $att->shift_id       = $employee->shifts_types_id;
                    $att->check_in_time  = $checkIn;
                    $att->check_out_time = $checkOut;
                    $att->missing_punch  = $isSinglePunch ? 'out' : null;
                    $att->status         = 1;
                    $att->com_code       = $comCode;
                    $att->notes          = 'مزامنة جهاز بصمة';

                    if (!$att->added_by) {
                        $att->added_by = 1;
                    }

                    if ($checkIn && $checkOut) {
                        $p = $buildParams($employee, $dateStr);
                        $att->calculateDelayAndOvertime($p['graceMinutes'], $p['graceEarlyMinutes']);
                        $att->calculateAmounts(
                            $p['dailyRate'], $p['overtimeMultiplier'], $p['sanctionsMultiplier'],
                            $p['overtimeEnabled'], $p['lateDeductEnabled'],
                            $p['hourDivisorType'], $p['hourDivisorCustom'],
                            $p['delayCalcMode'], $p['afterMinuteQuarterday'],
                            $p['delayTier1Minutes'], $p['delayHalfDayMinutes'], $p['delayFullDayMinutes'],
                            $p['earlyHalfDayMinutes'], $p['earlyFullDayMinutes'], $p['earlyFullPlusHalfMinutes']
                        );
                    }

                    $att->save();

                    FingerprintLog::whereIn('id', $allLogIds)->update(['is_processed' => 1]);
                    $isSinglePunch ? $missing++ : $imported++;
                }

                // الغائبون في هذا اليوم
                $dayOfWeek = $currentDate->dayOfWeek; // Carbon: 0=الأحد...6=السبت
                foreach ($allEmployees as $emp) {
                    // موظف غير نشط: تجاهله كلياً بعد شهر استقالته
                    if ($emp->functional_status == 2 && $emp->resignation_date) {
                        $resignYM  = substr($emp->resignation_date, 0, 7);
                        $currentYM = substr($dateStr, 0, 7);
                        if ($currentYM > $resignYM) continue;
                    }

                    if (in_array($emp->id, $presentEmployeeIds)) continue;

                    // هل التاريخ بعد تاريخ ترك العمل (في نفس الشهر)؟ → غياب إجباري حتى نهاية الشهر
                    $isAfterResignation = $emp->functional_status == 2
                        && $emp->resignation_date
                        && $dateStr > $emp->resignation_date;

                    $existingAtt = Attendance::where('employee_id', $emp->id)
                        ->where('attendance_date', $dateStr)
                        ->first();

                    if ($existingAtt) {
                        // بعد الاستقالة → تحديث السجل دائماً (حتى لو كان غياباً عادياً) لضبط الملاحظة و absence_deduction_days
                        if ($isAfterResignation && !$existingAtt->is_manual_lock) {
                            $needsUpdate = $existingAtt->status != 2
                                || $existingAtt->notes != 'غياب بعد ترك العمل'
                                || (float)$existingAtt->absence_deduction_days !== 1.0;
                            if ($needsUpdate) {
                                $existingAtt->update([
                                    'status'                 => 2,
                                    'check_in_time'          => null,
                                    'check_out_time'         => null,
                                    'missing_punch'          => null,
                                    'late_minutes'           => 0,
                                    'overtime_hours'         => 0,
                                    'overtime_amount'        => 0,
                                    'late_deduction'         => 0,
                                    'absence_deduction_days' => 1.0,
                                    'notes'                  => 'غياب بعد ترك العمل',
                                ]);
                                $absent++;
                            }
                        }
                        continue;
                    }

                    $isWeeklyOff  = !$isAfterResignation
                                    && $emp->weekly_off_day !== null
                                    && (int)$emp->weekly_off_day === $dayOfWeek;
                    $isBeforeHire = !$isAfterResignation && $emp->emp_start_date && $dateStr < $emp->emp_start_date;

                    $notes = $isBeforeHire       ? 'قبل التعيين'
                           : ($isWeeklyOff       ? 'إجازة أسبوعية - بصمة'
                           : ($isAfterResignation ? 'غياب بعد ترك العمل'
                           : 'غياب - بصمة'));

                    Attendance::create([
                        'employee_id'            => $emp->id,
                        'shift_id'               => $emp->shifts_types_id,
                        'attendance_date'        => $dateStr,
                        'status'                 => $isWeeklyOff ? 6 : 2,
                        'is_before_hire'         => $isBeforeHire ? 1 : 0,
                        'absence_deduction_days' => $isAfterResignation ? 1.0 : ($isBeforeHire ? 0 : null),
                        'late_minutes'           => 0,
                        'overtime_hours'         => 0,
                        'overtime_amount'        => 0,
                        'late_deduction'         => 0,
                        'notes'                  => $notes,
                        'com_code'               => $comCode,
                        'added_by'               => 1,
                    ]);
                    $absent++;
                }

                DB::commit();
                $totalImported += $imported;
                $totalMissing  += $missing;
                $totalAbsent   += $absent;
            } catch (\Exception $e) {
                DB::rollBack();
                return ['success' => false, 'error' => $e->getMessage()];
            }

            $currentDate->addDay();
        }

        return [
            'success'  => true,
            'imported' => $totalImported,
            'missing'  => $totalMissing,
            'absent'   => $totalAbsent,
            'notFound' => array_unique($notFound),
        ];
    }

    // ─────────────────────────────────────────────
    //  HTTP GET helper
    // ─────────────────────────────────────────────
    private function httpGet(string $url, array $headers = [], int $timeout = 10): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => array_map(
                fn($k, $v) => "$k: $v",
                array_keys($headers),
                $headers
            ),
        ]);

        $body  = curl_exec($ch);
        $error = curl_error($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) return ['ok' => false, 'error' => $error, 'body' => null];
        if ($code >= 400) return ['ok' => false, 'error' => "HTTP $code", 'body' => $body];

        return ['ok' => true, 'error' => null, 'body' => $body];
    }

    // ─────────────────────────────────────────────
    //  اختبار الاتصال بالجهاز (Ping)
    // ─────────────────────────────────────────────
    public function testConnection(FingerprintDevice $device): array
    {
        try {
            if ($device->protocol === 'agent') {
                return [
                    'success' => (bool)$device->api_token,
                    'message' => $device->api_token
                        ? "✅ جهاز Agent — التوكن نشط ({$device->device_name})"
                        : '⚠️ لا يوجد توكن — قم بتجديده من صفحة التعديل',
                ];
            }

            if (in_array($device->protocol, ['zkteco', 'suprema', 'anviz'])) {
                // TCP ping
                $fp = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 5);
                if ($fp) {
                    fclose($fp);
                    return ['success' => true, 'message' => "✅ الاتصال ناجح ({$device->ip_address}:{$device->port})"];
                }
                return ['success' => false, 'message' => "❌ فشل الاتصال: $errstr ($errno)"];
            }

            // HTTP ping للأجهزة الأخرى
            $resp = $this->httpGet("http://{$device->ip_address}:{$device->port}", [], 5);
            if ($resp['ok']) {
                return ['success' => true, 'message' => "✅ الجهاز يستجيب على {$device->ip_address}:{$device->port}"];
            }
            return ['success' => false, 'message' => "❌ الجهاز لا يستجيب: " . ($resp['error'] ?? '')];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => '❌ خطأ: ' . $e->getMessage()];
        }
    }
}
