@extends('admin.layouts.admin')
@section('title') احتساب مسير الرواتب @endsection
@section('start') الرواتب @endsection
@section('home') <a href="{{ route('payroll.index') }}">مسير الرواتب</a> @endsection
@section('startpage') احتساب @endsection

@section('content')
<div class="col-12">

    {{-- بطاقة الاحتساب الدفعي --}}
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calculator ml-2"></i>
                احتساب رواتب جميع الموظفين (دفعي)
            </h3>
        </div>
        <form action="{{ route('payroll.calculate_bulk') }}" method="POST">
            @csrf
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row">
                    <div class="col-md-2 form-group">
                        <label>الشهر <span class="text-danger">*</span></label>
                        <select name="month" class="form-control" required>
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ now()->month==$i+1?'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>السنة <span class="text-danger">*</span></label>
                        <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>بداية الفترة <span class="text-danger">*</span></label>
                        <input type="date" name="period_from" class="form-control" required
                            value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        <small class="text-muted">مثال: من 26 الشهر السابق</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>نهاية الفترة <span class="text-danger">*</span></label>
                        <input type="date" name="period_to" class="form-control" required
                            value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                        <small class="text-muted">مثال: إلى 25 الشهر الحالي</small>
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-block"
                            onclick="return confirm('سيتم احتساب رواتب جميع الموظفين. هل أنت متأكد؟')">
                            <i class="fas fa-play ml-1"></i> احتساب الكل
                        </button>
                    </div>
                </div>

                <div class="alert alert-info mt-0">
                    <i class="fas fa-info-circle ml-1"></i>
                    <strong>كيفية الاحتساب:</strong>
                    الراتب الصافي = (الراتب المستحق بأيام الحضور) + الإضافات الثابتة + الأوفرتايم + العمولات
                    − خصومات التأخير − خصومات الغياب − الخصومات الأخرى − قسط السلفة − التأمينات
                </div>
            </div>
        </form>
    </div>

    {{-- بطاقة احتساب موظف واحد --}}
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-check ml-2"></i>
                احتساب راتب موظف واحد
            </h3>
        </div>
        <form action="{{ route('payroll.calculate_single') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>الموظف <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_name_A }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>الشهر</label>
                        <select name="month" class="form-control" required>
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
                            <option value="{{ $i+1 }}" {{ now()->month==$i+1?'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>السنة</label>
                        <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>بداية الفترة</label>
                        <input type="date" name="period_from" class="form-control" required
                            value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>نهاية الفترة</label>
                        <input type="date" name="period_to" class="form-control" required
                            value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-1 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection