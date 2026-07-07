@extends('admin.layouts.sales')
@section('title') إضافة كاشير @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos_registers.index') }}">ماكينات الكاشير</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-store ml-2"></i> إضافة كاشير جديد</h3></div>
        <form method="POST" action="{{ route('pos_registers.store') }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>اسم الكاشير <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>الفرع</label>
                        <select class="form-control" name="branch_id">
                            <option value="">-- بدون --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>الخزنة النقدية <span class="text-danger">*</span></label>
                        <select class="form-control" name="cash_box_id" required>
                            <option value="">-- اختر --</option>
                            @foreach($cashBoxes as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->name }}</option>
                            @endforeach
                        </select>
                        @if($cashBoxes->isEmpty())
                            <small class="text-danger">لا توجد خزائن نقدية — أضف خزنة من موديول الخزينة أولاً.</small>
                        @endif
                    </div>
                    <div class="col-md-6 form-group">
                        <label>المخزن <span class="text-danger">*</span></label>
                        <select class="form-control" name="warehouse_id" required>
                            <option value="">-- اختر --</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="is_active">مفعّل</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('pos_registers.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
