@php $current = request()->route()->getName(); @endphp
<div style="background:#0d7a70">
  <div class="container">
    <div class="d-flex" style="overflow-x:auto">
      <a href="{{ route('employee.dashboard') }}" class="text-white p-2 px-3 {{ $current=='employee.dashboard'?'font-weight-bold':'' }}">
        <i class="fas fa-home ml-1"></i>الرئيسية
      </a>
      <a href="{{ route('employee.attendance') }}" class="text-white p-2 px-3 {{ $current=='employee.attendance'?'font-weight-bold':'' }}">
        <i class="fas fa-clock ml-1"></i>الحضور والانصراف
      </a>
      <a href="{{ route('employee.payslips') }}" class="text-white p-2 px-3 {{ $current=='employee.payslips'?'font-weight-bold':'' }}">
        <i class="fas fa-file-invoice-dollar ml-1"></i>قسائم الراتب
      </a>
      <a href="{{ route('employee.letters.salary_certificate') }}" class="text-white p-2 px-3">
        <i class="fas fa-file-signature ml-1"></i>شهادة راتب
      </a>
      <a href="{{ route('employee.documents') }}" class="text-white p-2 px-3 {{ $current=='employee.documents'?'font-weight-bold':'' }}">
        <i class="fas fa-folder-open ml-1"></i>مستنداتي
      </a>
      <a href="{{ route('employee.resignation') }}" class="text-white p-2 px-3 {{ $current=='employee.resignation'?'font-weight-bold':'' }}">
        <i class="fas fa-door-open ml-1"></i>طلب استقالة
      </a>
    </div>
  </div>
</div>
