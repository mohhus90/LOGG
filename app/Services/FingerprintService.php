<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Employee;
use App\Models\Attendance;
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
    //  معالجة السجلات الخام → جدول الحضور
    //  يدعم نطاق تاريخ، إعادة المعالجة، الشيفت الليلي، والبصمة الناقصة
    // ─────────────────────────────────────────────
    public function processLogs(
        int $comCode,
        string $dateFrom,
        string $dateTo,
        bool $forceReprocess = false
    ): array {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->startOfDay();

        // إذا forceReprocess: إعادة ضبط is_processed للسجلات في النطاق (+ يوم احتياطي للشيفت الليلي)
        if ($forceReprocess) {
            FingerprintLog::where('com_code', $comCode)
                ->where('is_processed', 1)
                ->whereBetween('punch_time', [
                    $from->copy()->subDay()->format('Y-m-d H:i:s'),
                    $to->copy()->addDays(2)->format('Y-m-d H:i:s'),
                ])
                ->update(['is_processed' => 0]);
        }

        $allEmployees = Employee::with('shifts_type')
            ->where('com_code', $comCode)
            ->where('functional_status', 1)
            ->get();

        $branchFingerMap = [];
        $fallbackMap     = $allEmployees->keyBy(fn($e) => (int)$e->finger_id);
        foreach ($allEmployees as $emp) {
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
            $dayLogs = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('is_processed', 0)
                ->whereDate('punch_time', $dateStr)
                ->orderBy('punch_time')
                ->get();

            // تحميل بصمات اليوم التالي (للشيفت الليلي)
            $nextDateStr = $currentDate->copy()->addDay()->format('Y-m-d');
            $nextDayLogs = FingerprintLog::with('device')
                ->where('com_code', $comCode)
                ->where('is_processed', 0)
                ->whereDate('punch_time', $nextDateStr)
                ->orderBy('punch_time')
                ->get();

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
                    } else {
                        $employee = $fallbackMap->get((int)$fingerId);
                    }

                    if (!$employee) {
                        $notFound[] = $fingerId . ($branchId ? " (فرع {$branchId})" : '');
                        FingerprintLog::whereIn('id', collect($group['punches'])->pluck('id')->toArray())
                            ->update(['is_processed' => 1]);
                        continue;
                    }

                    $shift = $employee->shifts_type;

                    // هل الشيفت ليلي (ينتهي في اليوم التالي)؟
                    $isNightShift = $shift && ($shift->to_time < $shift->from_time);

                    $punches        = collect($group['punches'])->sortBy('punch_time')->values();
                    $nextDayPunches = collect($nextGrouped[$key] ?? [])->sortBy('punch_time')->values();
                    $excludedPunches = collect();

                    if ($isNightShift && $shift) {
                        /*
                         * نافذة الشيفت الليلي ليوم D:
                         *   البداية : D + from_time − 3 ساعات  (مثلاً 12:00 لشيفت 15:00)
                         *   النهاية : D+1 + to_time + 3 ساعات (مثلاً 04:00 لشيفت ينتهي 01:00)
                         *
                         * أي بصمة على D قبل حد البداية تنتمي لشيفت D-1 (انصراف شيفت الأمس).
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

                        // معالجة البصمات المُستبعدة: تنتمي لانصراف شيفت اليوم السابق
                        if ($excludedPunches->isNotEmpty()) {
                            $prevDateStr = $currentDate->copy()->subDay()->format('Y-m-d');
                            $prevAtt = Attendance::where('employee_id', $employee->id)
                                                 ->where('attendance_date', $prevDateStr)
                                                 ->whereNull('check_out_time')
                                                 ->first();
                            if ($prevAtt) {
                                $lastExcluded = $excludedPunches->last();
                                $prevAtt->check_out_time = $lastExcluded->punch_time->format('H:i');
                                $prevAtt->missing_punch  = null;
                                if ($prevAtt->check_in_time) {
                                    $prevAtt->calculateDelayAndOvertime();
                                    $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                                    $prevAtt->calculateAmounts($dailyRate);
                                }
                                $prevAtt->save();
                            }
                            FingerprintLog::whereIn('id', $excludedPunches->pluck('id')->toArray())
                                ->update(['is_processed' => 1]);
                        }

                        // لا توجد بصمات لشيفت اليوم الحالي → لم يحضر بعد (سيُسجَّل غائباً)
                        if ($punches->isEmpty()) {
                            continue;
                        }
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
                        $att->calculateDelayAndOvertime();
                        $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                        $att->calculateAmounts($dailyRate);
                    }

                    $att->save();

                    FingerprintLog::whereIn('id', $allLogIds)->update(['is_processed' => 1]);
                    $isSinglePunch ? $missing++ : $imported++;
                }

                // الغائبون في هذا اليوم
                foreach ($allEmployees as $emp) {
                    if (in_array($emp->id, $presentEmployeeIds)) continue;
                    if (Attendance::where('employee_id', $emp->id)->where('attendance_date', $dateStr)->exists()) continue;

                    Attendance::create([
                        'employee_id'     => $emp->id,
                        'shift_id'        => $emp->shifts_types_id,
                        'attendance_date' => $dateStr,
                        'status'          => 2,
                        'late_minutes'    => 0,
                        'overtime_hours'  => 0,
                        'overtime_amount' => 0,
                        'late_deduction'  => 0,
                        'notes'           => 'غياب - بصمة',
                        'com_code'        => $comCode,
                        'added_by'        => 1,
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
