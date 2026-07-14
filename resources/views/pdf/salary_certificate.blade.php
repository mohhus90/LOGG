<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; direction: rtl; line-height: 1.9; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header img { max-height: 60px; }
    .title { text-align: center; font-size: 18px; font-weight: bold; margin: 25px 0; text-decoration: underline; }
    .body { margin: 0 20px; text-align: justify; }
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

    <div class="body">
        <p>
            تشهد إدارة {{ $company->com_name ?? 'الشركة' }} بأن السيد/ة
            <strong>{{ $employee->employee_name_A }}</strong>
            الحامل للرقم القومي <strong>{{ $employee->national_id }}</strong>
            يعمل لدى الشركة بوظيفة <strong>{{ $employee->jobs_categories->job_name ?? '' }}</strong>
            منذ تاريخ <strong>{{ $employee->emp_start_date ? \Carbon\Carbon::parse($employee->emp_start_date)->format('Y-m-d') : '' }}</strong>
            وحتى تاريخ إصدار هذه الشهادة.
        </p>
        <p>
            وأن راتبه الشهري الأساسي يبلغ
            <strong>{{ number_format($employee->emp_sal, 2) }} جنيه</strong>.
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
