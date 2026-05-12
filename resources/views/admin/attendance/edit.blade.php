@extends('admin.layouts.admin')
@section('title') تعديل سجل الحضور @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-8 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل سجل الحضور — {{ $attendance->employee->employee_name_A ?? '' }}
                <span class="badge badge-light mr-2">{{ $attendance->attendance_date->format('Y-m-d') }}</span>
            </h3>
        </div>
        <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                {{-- معلومات الشيفت --}}
                @if($attendance->shift)
                <div class="alert alert-info">
                    <i class="fas fa-clock ml-1"></i>
                    <strong>الشيفت المحدد:</strong>
                    {{ $attendance->shift->type }} —
                    من {{ $attendance->shift->from_time }} إلى {{ $attendance->shift->to_time }}
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الموظف</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->employee->employee_name_A ?? '-' }} ({{ $attendance->employee->employee_id ?? '' }})">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>التاريخ</label>
                        <input type="text" class="form-control bg-light" readonly
                            value="{{ $attendance->attendance_date->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>وقت الحضور</label>
                        <input type="time" name="check_in_time" class="form-control"
                            value="{{ old('check_in_time', $attendance->check_in_time) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>وقت الانصراف</label>
                        <input type="time" name="check_out_time" class="form-control"
                            value="{{ old('check_out_time', $attendance->check_out_time) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ $attendance->status==1?'selected':'' }}>حضر</option>
                            <option value="2" {{ $attendance->status==2?'selected':'' }}>غياب</option>
                            <option value="3" {{ $attendance->status==3?'selected':'' }}>إجازة</option>
                            <option value="4" {{ $attendance->status==4?'selected':'' }}>إجازة رسمية</option>
                            <option value="5" {{ $attendance->status==5?'selected':'' }}>مأمورية</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ old('notes', $attendance->notes) }}">
                </div>

                {{-- القيم المحتسبة الحالية --}}
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">تأخير</span>
                                <span class="info-box-number">{{ $attendance->late_minutes }} د</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">أوفرتايم</span>
                                <span class="info-box-number">{{ $attendance->overtime_hours }} س</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">خصم تأخير</span>
                                <span class="info-box-number">{{ number_format($attendance->late_deduction, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">قيمة أوفرتايم</span>
                                <span class="info-box-number">{{ number_format($attendance->overtime_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <small class="text-muted">* سيتم إعادة احتساب التأخير والأوفرتايم تلقائياً بعد الحفظ</small>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
