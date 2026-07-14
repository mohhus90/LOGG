<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; direction: rtl; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header img { max-height: 60px; }
    .header h2 { margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    td, th { border: 1px solid #999; padding: 6px 8px; text-align: right; }
    th { background: #f0f0f0; }
    .totals td { font-weight: bold; }
    .net { background: #eaf7ea; font-size: 14px; }
</style>
</head>
<body>
    <div class="header">
        @if($company && $company->image)
            <img src="{{ public_path('storage/' . $company->image) }}">
        @endif
        <h2>{{ $company->com_name ?? '' }}</h2>
        <div>{{ $company->address ?? '' }}</div>
    </div>

    <h3>قسيمة راتب شهر {{ $payslip->month_name }} {{ $payslip->year }}</h3>

    <table>
        <tr><th>اسم الموظف</th><td>{{ $employee->employee_name_A }}</td>
            <th>الرقم القومي</th><td>{{ $employee->national_id }}</td></tr>
        <tr><th>الوظيفة</th><td>{{ $employee->jobs_categories->job_name ?? '' }}</td>
            <th>القسم</th><td>{{ $employee->department->dep_name ?? '' }}</td></tr>
        <tr><th>الفترة</th><td>{{ $payslip->period_from }} — {{ $payslip->period_to }}</td>
            <th>أيام العمل الفعلية</th><td>{{ $payslip->work_days }} / {{ $payslip->total_days }}</td></tr>
    </table>

    <table>
        <tr><th colspan="2">المستحقات</th></tr>
        <tr><td>الراتب الأساسي</td><td>{{ number_format($payslip->basic_salary, 2) }}</td></tr>
        <tr><td>الراتب المستحق حسب الحضور</td><td>{{ number_format($payslip->earned_salary, 2) }}</td></tr>
        <tr><td>الإضافات الثابتة</td><td>{{ number_format($payslip->fixed_allowances, 2) }}</td></tr>
        <tr><td>الأوفرتايم</td><td>{{ number_format($payslip->overtime_amount, 2) }}</td></tr>
        <tr><td>العمولات</td><td>{{ number_format($payslip->commissions_amount, 2) }}</td></tr>
        <tr class="totals"><td>إجمالي المستحقات</td><td>{{ number_format($payslip->gross_salary, 2) }}</td></tr>
    </table>

    <table>
        <tr><th colspan="2">الخصومات</th></tr>
        <tr><td>خصم التأخير</td><td>{{ number_format($payslip->late_deductions, 2) }}</td></tr>
        <tr><td>خصم الغياب</td><td>{{ number_format($payslip->absence_deductions, 2) }}</td></tr>
        <tr><td>خصومات أخرى</td><td>{{ number_format($payslip->deductions_amount, 2) }}</td></tr>
        <tr><td>قسط السلفة</td><td>{{ number_format($payslip->advance_installment, 2) }}</td></tr>
        <tr><td>التأمينات</td><td>{{ number_format($payslip->insurance_deduction, 2) }}</td></tr>
    </table>

    <table>
        <tr class="net"><td>صافي الراتب</td><td>{{ number_format($payslip->net_salary, 2) }} جنيه</td></tr>
    </table>
</body>
</html>
