<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; direction: rtl; line-height: 1.9; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header img { max-height: 60px; }
    .title { text-align: center; font-size: 18px; font-weight: bold; margin: 25px 0 4px 0; }
    .title-rule { width: 160px; margin: 0 auto 20px auto; border-bottom: 1px solid #333; }
    .body { margin: 0 20px; text-align: justify; }
    .facts { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .facts th, .facts td { border: 1px solid #999; padding: 6px 10px; text-align: right; }
    .facts th { background: #f0f0f0; width: 35%; }
    .footer { margin-top: 60px; }
</style>
</head>
<body>
    <div class="header">
        @if($company && $company->image)
            <img src="{{ public_path('storage/' . $company->image) }}">
        @endif
        <h3>{{ $company->com_name ?? '' }}</h3>
    </div>

    <div class="title">شهادة راتب</div>
    <div class="title-rule"></div>

    <div class="body">
        <p>تشهد إدارة {{ $company->com_name ?? 'الشركة' }} بأن البيانات التالية صحيحة:</p>

        <table class="facts">
            <tr><th>اسم الموظف</th><td>{{ $employee->employee_name_A }}</td></tr>
            <tr><th>الرقم القومي</th><td>{{ $employee->national_id }}</td></tr>
            <tr><th>الوظيفة</th><td>{{ $employee->jobs_categories->job_name ?? '' }}</td></tr>
            <tr><th>تاريخ التعيين</th><td>{{ $employee->emp_start_date ? \Carbon\Carbon::parse($employee->emp_start_date)->format('Y-m-d') : '' }}</td></tr>
            <tr><th>الراتب الشهري الأساسي</th><td>{{ number_format($employee->emp_sal, 2) }} جنيه</td></tr>
        </table>

        <p>
            وأن الموظف المذكور أعلاه يعمل لدى الشركة حتى تاريخ إصدار هذه الشهادة.
        </p>
        <p>
            وقد أُعطيت هذه الشهادة بناءً على طلبه لتقديمها لمن يهمه الأمر دون أدنى مسؤولية على الشركة.
        </p>
    </div>

    <div class="footer">
        <p>تاريخ الإصدار: {{ now()->format('Y-m-d') }}</p>
    </div>
</body>
</html>
