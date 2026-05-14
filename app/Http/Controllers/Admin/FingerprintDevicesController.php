<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Employee;
use App\Services\FingerprintService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return view('admin.fingerprint_devices.create', compact('protocols'));
    }

    // =========================================================
    //  STORE
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string|max:100',
            'device_code' => 'required|string|max:50|unique:fingerprint_devices,device_code',
            'ip_address'  => 'required|ip',
            'port'        => 'required|integer|between:1,65535',
            'protocol'    => 'required|string',
            'location'    => 'nullable|string|max:100',
            'model'       => 'nullable|string|max:100',
        ], [
            'device_code.unique' => 'كود الجهاز مستخدم من قبل',
            'ip_address.ip'      => 'عنوان IP غير صحيح',
        ]);

        FingerprintDevice::create([
            'device_name'    => $request->device_name,
            'device_code'    => $request->device_code,
            'ip_address'     => $request->ip_address,
            'port'           => $request->port,
            'protocol'       => $request->protocol,
            'location'       => $request->location,
            'model'          => $request->model,
            'serial_number'  => $request->serial_number,
            'password'       => $request->device_password,
            'status'         => 1,
            'com_code'       => $this->comCode(),
            'added_by'       => Auth::guard('admin')->id(),
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
        return view('admin.fingerprint_devices.edit', compact('device', 'protocols'));
    }

    public function update(Request $request, int $id)
    {
        $device = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);

        $request->validate([
            'device_name' => 'required|string|max:100',
            'device_code' => 'required|string|max:50|unique:fingerprint_devices,device_code,' . $id,
            'ip_address'  => 'required|ip',
            'port'        => 'required|integer|between:1,65535',
            'protocol'    => 'required|string',
        ]);

        $device->update([
            'device_name'   => $request->device_name,
            'device_code'   => $request->device_code,
            'ip_address'    => $request->ip_address,
            'port'          => $request->port,
            'protocol'      => $request->protocol,
            'location'      => $request->location,
            'model'         => $request->model,
            'serial_number' => $request->serial_number,
            'password'      => $request->device_password ?: $device->password,
            'status'        => $request->status ?? $device->status,
            'updated_by'    => Auth::guard('admin')->id(),
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
        $device  = FingerprintDevice::where('com_code', $this->comCode())->findOrFail($id);
        $service = new FingerprintService();

        // 1. جلب السجلات من الجهاز → fingerprint_logs
        $syncResult = $service->syncDevice($device);

        if (!$syncResult['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', "فشل الاتصال بـ {$device->device_name}: " . $syncResult['error']);
        }

        // 2. معالجة السجلات الخام → attendance
        $date          = $request->sync_date ?? today()->format('Y-m-d');
        $processResult = $service->processLogs($this->comCode(), $date);

        if (!$processResult['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ في المعالجة: ' . $processResult['error']);
        }

        $msg = "✅ {$device->device_name}: جُلب {$syncResult['count']} سجل. "
             . "حضور: {$processResult['imported']}، غياب: {$processResult['absent']}.";

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
        $request->validate(['process_date' => 'required|date']);

        $service = new FingerprintService();
        $result  = $service->processLogs($this->comCode(), $request->process_date);

        if (!$result['success']) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'خطأ: ' . $result['error']);
        }

        $msg = "✅ تمت المعالجة: حضور {$result['imported']}، غياب {$result['absent']}.";
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

        if ($request->filled('log_date')) {
            $query->whereDate('punch_time', $request->log_date);
        }
        if ($request->filled('processed')) {
            $query->where('is_processed', $request->processed);
        }

        $logs = $query->orderByDesc('punch_time')->paginate(50);

        // finger_id → اسم الموظف map
        $employees  = Employee::where('com_code', $this->comCode())->get()->keyBy('finger_id');

        return view('admin.fingerprint_devices.logs', compact('device', 'logs', 'employees'));
    }

    // =========================================================
    //  SYNC ALL — مزامنة جميع الأجهزة دفعة واحدة
    // =========================================================
    public function syncAll(Request $request)
    {
        $devices = FingerprintDevice::where('com_code', $this->comCode())
            ->where('status', 1)->get();

        if ($devices->isEmpty()) {
            return redirect()->route('fingerprint_devices.index')
                ->with('error', 'لا توجد أجهزة نشطة للمزامنة');
        }

        $service   = new FingerprintService();
        $date      = $request->sync_date ?? today()->format('Y-m-d');
        $totalLogs = 0;
        $errors    = [];

        foreach ($devices as $device) {
            $result = $service->syncDevice($device);
            if ($result['success']) {
                $totalLogs += $result['count'];
            } else {
                $errors[] = "{$device->device_name}: " . $result['error'];
            }
        }

        // معالجة جميع السجلات
        $processResult = $service->processLogs($this->comCode(), $date);

        $msg = "✅ تمت مزامنة {$devices->count()} جهاز. إجمالي السجلات: $totalLogs. "
             . "حضور: {$processResult['imported']}، غياب: {$processResult['absent']}.";

        if (!empty($errors)) {
            $msg .= " ⚠️ أخطاء: " . implode(' | ', $errors);
        }

        return redirect()->route('fingerprint_devices.index')->with('success', $msg);
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
        ];
    }
}
