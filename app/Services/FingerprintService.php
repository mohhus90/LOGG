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
    private function saveLogs(FingerprintDevice $device, array $logs, string $protocol): array
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
    //  يُستدعى بعد المزامنة أو من Scheduler
    // ─────────────────────────────────────────────
    public function processLogs(int $comCode, ?string $date = null): array
    {
        $date  = $date ?? today()->format('Y-m-d');
        $query = FingerprintLog::where('com_code', $comCode)
            ->where('is_processed', 0)
            ->whereDate('punch_time', $date)
            ->orderBy('punch_time');

        $rawLogs = $query->get();

        // جمع السجلات لكل finger_id: أبكر وقت = حضور، أحدث وقت = انصراف
        $grouped = [];
        foreach ($rawLogs as $log) {
            $fid = $log->finger_id;
            if (!isset($grouped[$fid])) {
                $grouped[$fid] = ['first' => $log->punch_time, 'last' => $log->punch_time, 'ids' => [$log->id]];
            } else {
                $grouped[$fid]['last']  = $log->punch_time;
                $grouped[$fid]['ids'][] = $log->id;
            }
        }

        $allEmployees   = Employee::where('com_code', $comCode)->get();
        $fingerMap      = $allEmployees->keyBy(fn($e) => (int)$e->finger_id);
        $presentFingers = array_keys($grouped);

        $imported = 0;
        $absent   = 0;
        $notFound = [];

        DB::beginTransaction();
        try {
            // 1. تسجيل الحاضرين
            foreach ($grouped as $fingerId => $times) {
                $employee = $fingerMap->get((int)$fingerId);

                if (!$employee) {
                    $notFound[] = $fingerId;
                    // تعليم السجلات كمعالَجة رغم أنها لم تُرتبط بموظف
                    FingerprintLog::whereIn('id', $times['ids'])->update(['is_processed' => 1]);
                    continue;
                }

                $att = Attendance::firstOrNew([
                    'employee_id'     => $employee->id,
                    'attendance_date' => $date,
                ]);

                $att->shift_id       = $employee->shifts_types_id;
                $att->check_in_time  = $times['first']->format('H:i');
                $att->check_out_time = $times['last']->format('H:i');
                $att->status         = 1;
                $att->com_code       = $comCode;
                $att->notes          = 'مزامنة جهاز بصمة';

                if (!$att->added_by) {
                    $att->added_by = 1; // system
                }

                if ($att->check_in_time && $att->check_out_time && $att->check_in_time !== $att->check_out_time) {
                    $att->calculateDelayAndOvertime();
                    $dailyRate = $employee->emp_sal ? ($employee->emp_sal / 26) : 0;
                    $att->calculateAmounts($dailyRate);
                }

                $att->save();

                FingerprintLog::whereIn('id', $times['ids'])->update(['is_processed' => 1]);
                $imported++;
            }

            // 2. الموظفون الغائبون
            foreach ($allEmployees as $emp) {
                if (in_array((int)$emp->finger_id, $presentFingers)) continue;
                if (Attendance::where('employee_id', $emp->id)->where('attendance_date', $date)->exists()) continue;

                Attendance::create([
                    'employee_id'     => $emp->id,
                    'shift_id'        => $emp->shifts_types_id,
                    'attendance_date' => $date,
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
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return [
            'success'  => true,
            'imported' => $imported,
            'absent'   => $absent,
            'notFound' => $notFound,
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
