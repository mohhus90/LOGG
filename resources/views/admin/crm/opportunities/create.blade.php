@extends('admin.layouts.crm')
@section('title') فرصة بيعية جديدة @endsection
@section('start') إدارة علاقات العملاء @endsection
@section('home') <a href="{{ route('crm_opportunities.index') }}">الفرص البيعية</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-7">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> فرصة بيعية جديدة</h3></div>
        <form action="{{ route('crm_opportunities.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>عنوان الفرصة <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label>العميل المحتمل (اختياري)</label>
                    <select name="lead_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" {{ ($selectedLead && $selectedLead->id == $lead->id) ? 'selected' : '' }}>{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>العميل الفعلي (اختياري)</label>
                    <select name="customer_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المرحلة</label>
                            <select name="stage" class="form-control">
                                <option value="prospecting">استكشاف</option>
                                <option value="proposal">عرض سعر</option>
                                <option value="negotiation">تفاوض</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>القيمة المتوقعة</label>
                            <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', 0) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>تاريخ الإغلاق المتوقع</label>
                            <input type="date" name="expected_close_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('crm_opportunities.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
