<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientImportTemplate;
use App\Models\Employee;
use App\Models\PayrollFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// استيراد "مؤثرات" الرواتب الشهرية (أيام العمل، الغياب، الإجازات، التسويات، ...)
// لكل عميل من ملف Excel خاص به. شكل شيت العميل يختلف من عميل لآخر (راجع Klivvr
// مقابل Opay)، لذلك بدلاً من عمل Import class مخصص لكل عميل، الأدمن يحدد بنفسه
// أي عمود فى الملف يقابل أي حقل فى payroll_factors مرة واحدة، ثم يُحفظ هذا
// الربط كـ "قالب" (ClientImportTemplate) لإعادة استخدامه كل شهر بدون إعادة تحديده.
class PayrollFactorsController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public const TARGET_FIELDS = [
        ''                   => '— تجاهل هذا العمود —',
        'employee_code'      => 'كود الموظف (مطلوب للمطابقة)',
        'working_days'       => 'أيام العمل',
        'overtime_hours'     => 'ساعات الأوفرتايم',
        'absence_hours'      => 'ساعات الغياب',
        'leave_days'         => 'أيام الإجازة',
        'no_show_days'       => 'أيام عدم الحضور',
        'unpaid_leave_days'  => 'أيام إجازة بدون أجر',
        'sick_leave_days'    => 'أيام إجازة مرضية',
        'sick_leave_balance' => 'رصيد إجازة مرضية',
        'penalty_days'       => 'أيام جزاء',
        'settlement_hours'   => 'ساعات تسوية',
        'settlement_days'    => 'أيام تسوية',
        'settlement_amount'  => 'مبلغ تسوية',
        'bonus_amount'       => 'مكافأة',
        'other_allowance'    => 'بدلات أخرى',
        'other_deduction'    => 'خصومات/جزاءات أخرى',
        'monthly_stamp_tax'  => 'دمغة شهرية',
        'is_held'            => 'إيقاف عن الصرف (Y/1 = موقوف)',
    ];

    public function importForm(int $clientId)
    {
        $client   = Client::where('com_code', $this->comCode())->findOrFail($clientId);
        $template = ClientImportTemplate::where('client_id', $client->id)->first();
        return view('admin.payroll_factors.import', compact('client', 'template'));
    }

    // الخطوة 1: رفع الملف وعرض الأعمدة المكتشفة لربطها بحقول payroll_factors
    public function preview(Request $request, int $clientId)
    {
        $client = Client::where('com_code', $this->comCode())->findOrFail($clientId);

        $request->validate([
            'file'       => 'required|file|mimes:xlsx,xls,csv',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'header_row' => 'nullable|integer|min:1',
        ]);

        $headerRow = (int) ($request->header_row ?: 1);
        $storedPath = $request->file('file')->store('payroll_imports');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(Storage::path($storedPath));
        $worksheet   = $spreadsheet->getActiveSheet();
        $allRows     = $worksheet->toArray(null, true, true, false);

        $headers  = $allRows[$headerRow] ?? [];
        $previewRows = array_slice($allRows, $headerRow, 3);

        $savedTemplate = ClientImportTemplate::where('client_id', $client->id)->first();

        return view('admin.payroll_factors.map', [
            'client'        => $client,
            'month'         => (int) $request->month,
            'year'          => (int) $request->year,
            'headerRow'     => $headerRow,
            'storedPath'    => $storedPath,
            'headers'       => $headers,
            'previewRows'   => $previewRows,
            'targetFields'  => self::TARGET_FIELDS,
            'savedMapping'  => $savedTemplate->mapping ?? [],
        ]);
    }

    // الخطوة 2: تطبيق الربط المُحدَّد وحفظ سجلات payroll_factors
    public function store(Request $request, int $clientId)
    {
        $client = Client::where('com_code', $this->comCode())->findOrFail($clientId);

        $request->validate([
            'stored_path' => 'required|string',
            'header_row'  => 'required|integer|min:1',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
            'mapping'     => 'required|array',
        ]);

        $mapping = array_filter($request->mapping, fn($v) => $v !== '');
        if (!in_array('employee_code', $mapping, true)) {
            return back()->with('error', 'يجب تحديد عمود "كود الموظف" على الأقل للمطابقة')->withInput();
        }

        $fullPath = Storage::path($request->stored_path);
        if (!file_exists($fullPath)) {
            return back()->with('error', 'انتهت صلاحية الملف المرفوع، برجاء رفعه مرة أخرى');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
        $worksheet   = $spreadsheet->getActiveSheet();
        $allRows     = $worksheet->toArray(null, true, true, false);
        $dataRows    = array_slice($allRows, (int) $request->header_row);

        $adminId = Auth::guard('admin')->id();
        $imported = 0;
        $notFound = [];

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowNum => $row) {
                $values = [];
                foreach ($mapping as $colIndex => $field) {
                    $values[$field] = trim((string) ($row[$colIndex] ?? ''));
                }

                $code = $values['employee_code'] ?? '';
                if ($code === '') continue;

                $employee = Employee::where('client_id', $client->id)
                    ->where(function ($q) use ($code) {
                        $q->where('hrid', $code)->orWhere('employee_id', $code);
                    })->first();

                if (!$employee) {
                    $notFound[] = $code;
                    continue;
                }

                $factorValues = ['client_id' => $client->id, 'com_code' => $this->comCode(), 'added_by' => $adminId];
                foreach (self::TARGET_FIELDS as $key => $label) {
                    if ($key === '' || $key === 'employee_code') continue;
                    $raw = $values[$key] ?? null;
                    if ($key === 'is_held') {
                        $factorValues[$key] = in_array(strtolower((string) $raw), ['1', 'y', 'yes', 'true', 'نعم', 'موقوف'], true);
                    } else {
                        $numeric = is_numeric($raw) ? (float) $raw : 0.0;
                        $factorValues[$key] = $numeric;
                    }
                }

                PayrollFactor::updateOrCreate(
                    ['employee_id' => $employee->id, 'month' => (int) $request->month, 'year' => (int) $request->year],
                    $factorValues
                );
                $imported++;
            }

            ClientImportTemplate::updateOrCreate(
                ['client_id' => $client->id],
                ['mapping' => $mapping, 'header_row' => (int) $request->header_row, 'com_code' => $this->comCode()]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::delete($request->stored_path);
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        Storage::delete($request->stored_path);

        $message = "تم استيراد مؤثرات {$imported} موظف بنجاح.";
        if (!empty($notFound)) {
            $message .= ' أكواد غير موجودة: ' . implode(', ', array_slice($notFound, 0, 10))
                . (count($notFound) > 10 ? ' ...و' . (count($notFound) - 10) . ' أخرى' : '');
        }

        return redirect()->route('payroll.create')->with('success', $message);
    }
}
