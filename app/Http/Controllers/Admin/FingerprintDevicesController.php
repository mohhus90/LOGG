<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Employee;
use App\Models\Branche;
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

        // إحصائيات سريعة
        $totalLogs = FingerprintLog::where('com_code', $this->comCode())->count();
        $pendingLogs = FingerprintLog::where('com_code', $this->comCode())
            ->where('is_processed', 0)->count();

        return view('admin.fingerprint_devices.index',
            compact('devices', 'totalLogs', 'pendingLogs'));
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
        $processResult  = $service->processLogs(
            $this->comCode(),
            $request->sync_date_from,
            $request->sync_date_to,
            $forceReprocess
        );

        if (!$processResult['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ في المعالجة: ' . $processResult['error']);
        }

        $msg = "✅ {$device->device_name}: جُلب {$syncResult['count']} سجل. "
             . "حضور: {$processResult['imported']}، بصمة ناقصة: {$processResult['missing']}، غياب: {$processResult['absent']}.";

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
        $service        = new FingerprintService();
        $result         = $service->processLogs(
            $this->comCode(),
            $request->process_date_from,
            $request->process_date_to,
            $forceReprocess
        );

        if (!$result['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ: ' . $result['error']);
        }

        $msg = "✅ تمت المعالجة: حضور {$result['imported']}، بصمة ناقصة {$result['missing']}، غياب {$result['absent']}.";
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

        $logs = $query->orderByDesc('punch_time')->paginate(50);

        // finger_id → اسم الموظف: يشمل الفرع الأساسي والفروع الإضافية للجهاز
        $empQuery = Employee::where('com_code', $this->comCode());
        if ($device->branches_id) {
            $branchIds = array_values(array_unique(array_filter(array_merge(
                [$device->branches_id],
                is_array($device->extra_branch_ids) ? $device->extra_branch_ids : []
            ))));
            $empQuery->whereIn('branches_id', $branchIds);
        }
        $employees = $empQuery->get()->keyBy('finger_id');

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

        $processResult = $service->processLogs(
            $this->comCode(),
            $request->sync_date_from,
            $request->sync_date_to,
            $forceReprocess
        );

        $msg = "✅ تمت مزامنة {$devices->count()} جهاز. إجمالي السجلات: $totalLogs. "
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
