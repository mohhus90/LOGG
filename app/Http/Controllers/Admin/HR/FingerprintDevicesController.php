<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Employee;
use App\Models\Branche;
use App\Models\Attendance;
use App\Services\FingerprintService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FingerprintDevicesController extends Controller
{
    private function comCode(): int
    {
        return Auth::guard('admin')->user()->com_code;
    }

    // =========================================================
    //  INDEX — قائمة الأجهزة
    // =========================================================
    public function index()
    {
        $devices = FingerprintDevice::where('com_code', $this->comCode())
            ->orderBy('device_name')->get();

        $totalLogs = FingerprintLog::where('com_code', $this->comCode())->count();
        $pendingLogs = FingerprintLog::where('com_code', $this->comCode())
            ->where('is_processed', 0)->count();

        $employees = Employee::where('com_code', $this->comCode())
            ->where('functional_status', 1)
            ->orderBy('employee_name_A')
            ->get(['id', 'employee_name_A', 'finger_id']);

        return view('admin.fingerprint_devices.index',
            compact('devices', 'totalLogs', 'pendingLogs', 'employees'));
    }

    // =========================================================
    //  CREATE
    // =========================================================
    public function create()
    {
        $protocols = $this->protocolsList();
        $branches  = Branche::where('com_code', $this->comCode())->where('active', 1)->get();
        return view('admin.fingerprint_devices.create', compact('protocols', 'branches'));
    }

    // =========================================================
    //  STORE
    // =========================================================
    public function store(Request $request)
    {
        $isAgent = $request->protocol === 'agent';

        $rules = [
            'device_name' => 'required|string|max:100',
            'device_code' => 'required|string|max:50|unique:fingerprint_devices,device_code',
            'protocol'    => 'required|string',
            'location'    => 'nullable|string|max:100',
            'model'       => 'nullable|string|max:100',
        ];
        if (!$isAgent) {
            $rules['ip_address'] = 'required|ip';
            $rules['port']       = 'required|integer|between:1,65535';
        }

        $request->validate($rules, [
            'device_code.unique' => 'كود الجهاز مستخدم من قبل',
            'ip_address.ip'      => 'عنوان IP غير صحيح',
        ]);

        FingerprintDevice::create([
            'device_name'      => $request->device_name,
            'device_code'      => $request->device_code,
            'ip_address'       => $isAgent ? '0.0.0.0' : $request->ip_address,
            'port'             => $isAgent ? 0           : $request->port,
            'protocol'         => $request->protocol,
            'location'         => $request->location,
            'branches_id'      => $request->branches_id ?: null,
            'extra_branch_ids' => $request->extra_branch_ids ? array_map('intval', $request->extra_branch_ids) : null,
            'model'            => $request->model,
            'serial_number'    => $request->serial_number,
            'password'         => $request->device_password,
            'api_token'        => $isAgent ? Str::random(64) : null,
            'status'           => 1,
            'com_code'         => $this->comCode(),
            'added_by'         => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('fingerprint_devices.index')
            ->with('success', 'تم إضافة جهاز البصمة بنجاح');
    }

    // =========================================================
    //  EDIT / UPDATE
    // =========================================================
    public function edit(int $id)
    {
        $device    = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $protocols = $this->protocolsList();
        $branches  = Branche::where('com_code', $this->comCode())->where('active', 1)->get();
        return view('admin.fingerprint_devices.edit', compact('device', 'protocols', 'branches'));
    }

    public function update(Request $request, int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);

        $isAgent = $request->protocol === 'agent';

        $rules = [
            'device_name' => 'required|string|max:100',
            'device_code' => 'required|string|max:50|unique:fingerprint_devices,device_code,' . $id,
            'protocol'    => 'required|string',
        ];
        if (!$isAgent) {
            $rules['ip_address'] = 'required|ip';
            $rules['port']       = 'required|integer|between:1,65535';
        }

        $request->validate($rules);

        $device->update([
            'device_name'      => $request->device_name,
            'device_code'      => $request->device_code,
            'ip_address'       => $isAgent ? ($device->ip_address ?: '0.0.0.0') : $request->ip_address,
            'port'             => $isAgent ? ($device->port ?: 0)               : $request->port,
            'protocol'         => $request->protocol,
            'location'         => $request->location,
            'branches_id'      => $request->branches_id ?: null,
            'extra_branch_ids' => $request->extra_branch_ids ? array_map('intval', $request->extra_branch_ids) : null,
            'model'            => $request->model,
            'serial_number'    => $request->serial_number,
            'password'         => $request->device_password ?: $device->password,
            'status'           => $request->status ?? $device->status,
            'updated_by'       => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('fingerprint_devices.index')
            ->with('success', 'تم تحديث بيانات الجهاز بنجاح');
    }

    // =========================================================
    //  DELETE
    // =========================================================
    public function delete(int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        // حذف السجلات الخام المرتبطة
        FingerprintLog::where('device_id', $id)->delete();
        $device->delete();

        return redirect()->route('fingerprint_devices.index')
            ->with('success', 'تم حذف الجهاز وسجلاته بنجاح');
    }

    // =========================================================
    //  TEST CONNECTION — اختبار الاتصال
    // =========================================================
    public function testConnection(int $id)
    {
        $device  = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $service = new FingerprintService();
        $result  = $service->testConnection($device);

        return response()->json($result);
    }

    // =========================================================
    //  SYNC — مزامنة سجلات من الجهاز
    // =========================================================
    public function sync(Request $request, int $id)
    {
        $request->validate([
            'sync_date_from' => 'required|date',
            'sync_date_to'   => 'required|date|after_or_equal:sync_date_from',
        ]);

        $device  = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $service = new FingerprintService();

        // 1. جلب السجلات من الجهاز → fingerprint_logs
        $syncResult = $service->syncDevice($device);

        if (!$syncResult['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', "فشل الاتصال بـ {$device->device_name}: " . $syncResult['error']);
        }

        // 2. معالجة السجلات الخام → attendance
        $forceReprocess = $request->boolean('force_reprocess');
        $employeeId     = $request->filled('employee_id') ? (int)$request->employee_id : null;
        $processResult  = $service->processLogs(
            $this->comCode(),
            $request->sync_date_from,
            $request->sync_date_to,
            $forceReprocess,
            $employeeId
        );

        if (!$processResult['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ في المعالجة: ' . $processResult['error']);
        }

        $empLabel = $employeeId
            ? (' — موظف: ' . (Employee::find($employeeId)?->employee_name_A ?? $employeeId))
            : '';

        if ($device->protocol === 'agent') {
            $msg = "✅ {$device->device_name} (Agent){$empLabel}: معالجة السجلات المُرسلة من الفرع — "
                 . "حضور: {$processResult['imported']}، بصمة ناقصة: {$processResult['missing']}، غياب: {$processResult['absent']}.";
        } else {
            $msg = "✅ {$device->device_name}{$empLabel}: جُلب {$syncResult['count']} سجل. "
                 . "حضور: {$processResult['imported']}، بصمة ناقصة: {$processResult['missing']}، غياب: {$processResult['absent']}.";
        }

        if (!empty($processResult['notFound'])) {
            $msg .= " ⚠️ Finger IDs غير معروفة: " . implode('، ', $processResult['notFound']);
        }

        return redirect()->route('fingerprint_devices.index')->with('success', $msg);
    }

    // =========================================================
    //  PROCESS LOGS — معالجة السجلات الخام المخزنة
    //  (بدون اتصال بالجهاز — يعالج ما هو موجود في fingerprint_logs)
    // =========================================================
    public function processLogs(Request $request)
    {
        $request->validate([
            'process_date_from' => 'required|date',
            'process_date_to'   => 'required|date|after_or_equal:process_date_from',
        ]);

        $forceReprocess = $request->boolean('force_reprocess');
        $employeeId     = $request->filled('employee_id') ? (int)$request->employee_id : null;
        $service        = new FingerprintService();
        $result         = $service->processLogs(
            $this->comCode(),
            $request->process_date_from,
            $request->process_date_to,
            $forceReprocess,
            $employeeId
        );

        if (!$result['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ: ' . $result['error']);
        }

        $empLabel = $employeeId
            ? (' — موظف: ' . (Employee::find($employeeId)?->employee_name_A ?? $employeeId))
            : '';

        $msg = "✅ تمت المعالجة{$empLabel}: حضور {$result['imported']}، بصمة ناقصة {$result['missing']}، غياب {$result['absent']}.";
        if (!empty($result['notFound'])) {
            $msg .= " ⚠️ IDs غير معروفة: " . implode('، ', $result['notFound']);
        }

        return redirect()->route('fingerprint_devices.index')->with('success', $msg);
    }

    // =========================================================
    //  LOGS — عرض السجلات الخام للجهاز
    // =========================================================
    public function logs(Request $request, int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);

        $query = FingerprintLog::where('device_id', $id);

        if ($request->filled('log_date_from')) {
            $query->whereDate('punch_time', '>=', $request->log_date_from);
        }
        if ($request->filled('log_date_to')) {
            $query->whereDate('punch_time', '<=', $request->log_date_to);
        }
        if ($request->filled('processed')) {
            $query->where('is_processed', $request->processed);
        }

        // البحث بالاسم: نجد finger_ids المطابقة ثم نفلتر عليها
        if ($request->filled('employee_name')) {
            $matchedIds = Employee::where('com_code', $this->comCode())
                ->where('employee_name_A', 'like', '%' . $request->employee_name . '%')
                ->whereNotNull('finger_id')
                ->pluck('finger_id');
            $query->whereIn('finger_id', $matchedIds->isNotEmpty() ? $matchedIds : [0]);
        }

        $logs = $query->orderByDesc('punch_time')->paginate(50);

        // الخريطة الأساسية: موظفو فرع الجهاز فقط (لتجنب تضارب نفس finger_id بين الفروع)
        $branchIds = array_values(array_unique(array_filter(array_merge(
            [$device->branches_id],
            is_array($device->extra_branch_ids) ? $device->extra_branch_ids : []
        ))));
        $empQuery = Employee::where('com_code', $this->comCode())->whereNotNull('finger_id');
        if (!empty($branchIds)) {
            $empQuery->whereIn('branches_id', $branchIds);
        }
        $employees = $empQuery->get()->keyBy('finger_id');

        // Fallback: finger_ids في سجلات هذه الصفحة غير موجودة في فرع الجهاز
        // (موظف من فرع آخر يبصم على هذا الجهاز — مثل محمد محمود)
        $unmatchedIds = $logs->pluck('finger_id')->unique()
            ->filter(fn($fid) => !$employees->has($fid));
        if ($unmatchedIds->isNotEmpty()) {
            Employee::where('com_code', $this->comCode())
                ->whereIn('finger_id', $unmatchedIds)
                ->get()
                ->each(fn($emp) => $employees->put($emp->finger_id, $emp));
        }

        return view('admin.fingerprint_devices.logs', compact('device', 'logs', 'employees'));
    }

    // =========================================================
    //  SYNC ALL — مزامنة جميع الأجهزة دفعة واحدة
    // =========================================================
    public function syncAll(Request $request)
    {
        $request->validate([
            'sync_date_from' => 'required|date',
            'sync_date_to'   => 'required|date|after_or_equal:sync_date_from',
        ]);

        $devices = FingerprintDevice::where('com_code', $this->comCode())
            ->where('status', 1)->get();

        if ($devices->isEmpty()) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'لا توجد أجهزة نشطة للمزامنة');
        }

        $service        = new FingerprintService();
        $forceReprocess = $request->boolean('force_reprocess');
        $totalLogs      = 0;
        $errors         = [];

        foreach ($devices as $device) {
            $result = $service->syncDevice($device);
            if ($result['success']) {
                $totalLogs += $result['count'];
            } else {
                $errors[] = "{$device->device_name}: " . $result['error'];
            }
        }

        $employeeId    = $request->filled('employee_id') ? (int)$request->employee_id : null;
        $processResult = $service->processLogs(
            $this->comCode(),
            $request->sync_date_from,
            $request->sync_date_to,
            $forceReprocess,
            $employeeId
        );

        $empLabel = $employeeId
            ? (' — موظف: ' . (Employee::find($employeeId)?->employee_name_A ?? $employeeId))
            : '';

        $msg = "✅ تمت مزامنة {$devices->count()} جهاز{$empLabel}. إجمالي السجلات: $totalLogs. "
             . "حضور: {$processResult['imported']}، بصمة ناقصة: {$processResult['missing']}، غياب: {$processResult['absent']}.";

        if (!empty($errors)) {
            $msg .= " ⚠️ أخطاء: " . implode(' | ', $errors);
        }

        return redirect()->route('fingerprint_devices.index')->with('success', $msg);
    }

    // =========================================================
    //  GENERATE TOKEN — تجديد توكن Agent الفرع
    // =========================================================
    public function generateToken(int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $device->update(['api_token' => Str::random(64)]);

        return redirect()->route('fingerprint_devices.edit', $id)
            ->with('success', '✅ تم تجديد التوكن بنجاح — انسخه وحدّث ملف config.php في الفرع');
    }

    // =========================================================
    //  VOID LOGS — تفريغ البصمة → تحويل الحضور إلى غياب حسب الفلتر
    // =========================================================
    public function voidLogs(Request $request, int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);

        $query = FingerprintLog::where('device_id', $id);

        if ($request->filled('log_date_from')) {
            $query->whereDate('punch_time', '>=', $request->log_date_from);
        }
        if ($request->filled('log_date_to')) {
            $query->whereDate('punch_time', '<=', $request->log_date_to);
        }
        if ($request->filled('processed')) {
            $query->where('is_processed', $request->processed);
        }
        if ($request->filled('employee_name')) {
            $matchedIds = Employee::where('com_code', $this->comCode())
                ->where('employee_name_A', 'like', '%' . $request->employee_name . '%')
                ->whereNotNull('finger_id')
                ->pluck('finger_id');
            $query->whereIn('finger_id', $matchedIds->isNotEmpty() ? $matchedIds : [0]);
        }

        $logs = $query->get();
        if ($logs->isEmpty()) {
            return redirect()->back()->with('error', 'لا توجد سجلات تطابق الفلتر المحدد.');
        }

        // موظفو الجهاز
        $branchIds = array_values(array_unique(array_filter(array_merge(
            [$device->branches_id],
            is_array($device->extra_branch_ids) ? $device->extra_branch_ids : []
        ))));
        $empQuery = Employee::where('com_code', $this->comCode())->whereNotNull('finger_id');
        if (!empty($branchIds)) {
            $empQuery->whereIn('branches_id', $branchIds);
        }
        $empMap = $empQuery->get()->keyBy('finger_id');

        // تجميع أزواج (employee_id → dates) فريدة + خريطة الموظفين حسب id
        $pairs   = [];
        $logIds  = [];
        $empById = $empMap->keyBy('id');
        foreach ($logs as $log) {
            $emp = $empMap->get($log->finger_id);
            if (!$emp) {
                $logIds[] = $log->id;
                continue;
            }
            $date = $log->punch_time->format('Y-m-d');
            $pairs[$emp->id][$date] = true;
            $logIds[] = $log->id;
        }

        $updatedBy = Auth::guard('admin')->id();
        $voided    = 0;
        $locked    = 0;
        foreach ($pairs as $empId => $dates) {
            $emp = $empById->get($empId);
            foreach (array_keys($dates) as $date) {
                $dayOfWeek   = Carbon::parse($date)->dayOfWeek;
                $isWeeklyOff = $emp
                               && $emp->weekly_off_day !== null
                               && (int)$emp->weekly_off_day === $dayOfWeek;

                // تجاهل السجلات المثبَّتة يدوياً
                $existingAtt = Attendance::where('employee_id', $empId)
                    ->where('attendance_date', $date)
                    ->first();
                if ($existingAtt && $existingAtt->is_manual_lock) { $locked++; continue; }

                $updated = Attendance::where('employee_id', $empId)
                    ->where('attendance_date', $date)
                    ->update([
                        'status'                    => $isWeeklyOff ? 6 : 2,
                        'check_in_time'             => null,
                        'check_out_time'            => null,
                        'late_minutes'              => 0,
                        'overtime_hours'            => 0,
                        'overtime_amount'           => 0,
                        'late_deduction'            => 0,
                        'early_departure_minutes'   => 0,
                        'early_departure_deduction' => 0,
                        'early_departure_fraction'  => null,
                        'missing_punch'             => null,
                        'missing_punch_resolution'  => null,
                        'missing_punch_hours'       => null,
                        'permission_early_minutes'  => 0,
                        'notes'                     => $isWeeklyOff ? 'إجازة أسبوعية - تفريغ بصمة' : 'تم تفريغ البصمة يدوياً',
                        'updated_by'                => $updatedBy,
                    ]);
                if ($updated) $voided++;
            }
        }

        // إعادة is_processed إلى 0 حتى تُعالَج من جديد عند المزامنة
        if (!empty($logIds)) {
            FingerprintLog::whereIn('id', $logIds)->update(['is_processed' => 0]);
        }

        $msg = "✅ تم تفريغ بصمة {$logs->count()} سجل — {$voided} يوم حضور حُوِّل إلى غياب.";
        if ($locked) $msg .= " | 🔒 تجاهل {$locked} سجل مثبَّت.";

        return redirect()->route('fingerprint_devices.logs', array_merge(
            ['id' => $id], $request->only(['log_date_from','log_date_to','processed','employee_name'])
        ))->with('success', $msg);
    }

    // =========================================================
    //  UPDATE LOG — تعديل سجل بصمة فردي
    // =========================================================
    public function updateLog(Request $request, int $id, int $logId)
    {
        FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $log = FingerprintLog::where('device_id', $id)->findOrFail($logId);

        $request->validate([
            'punch_time' => 'required|date',
            'punch_type' => 'required|integer|in:0,1,2',
        ]);

        $log->update([
            'punch_time'   => Carbon::parse($request->punch_time),
            'punch_type'   => (int)$request->punch_type,
            'is_processed' => 0,
        ]);

        return redirect()->route('fingerprint_devices.logs', array_merge(
            ['id' => $id], $request->only(['log_date_from','log_date_to','processed','employee_name'])
        ))->with('success', 'تم تعديل سجل البصمة — سيُعاد معالجته في المزامنة القادمة.');
    }

    // =========================================================
    //  SETUP GUIDE — دليل إعداد Agent الفرع (قابل للطباعة كـ PDF)
    // =========================================================
    public function setupGuide(int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $serverUrl = url('/api/fingerprint-agent/push');
        return view('admin.fingerprint_devices.setup_guide', compact('device', 'serverUrl'));
    }

    // ─────────────────────────────────────────────
    private function protocolsList(): array
    {
        return [
            'zkteco'    => 'ZKTeco / ZKLib (TCP 4370) — الأكثر شيوعاً',
            'suprema'   => 'Suprema (TCP)',
            'anviz'     => 'Anviz (TCP)',
            'hikvision' => 'Hikvision (HTTP REST API)',
            'dahua'     => 'Dahua (HTTP REST API)',
            'generic'   => 'Generic HTTP Webhook',
            'agent'     => 'Agent — فرع بعيد عبر الإنترنت',
        ];
    }
}
