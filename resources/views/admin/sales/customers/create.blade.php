@extends('admin.layouts.sales')
@section('title') إضافة عميل @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_customers.index') }}">العملاء</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-md-10 mx-auto">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus ml-2"></i>إضافة عميل جديد
            </h3>
        </div>
        <form action="{{ route('sales_customers.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- البيانات الأساسية --}}
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-id-card ml-1"></i> البيانات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>الكود</label>
                                <input type="text" name="code" class="form-control"
                                    placeholder="كود العميل" value="{{ old('code') }}">
                            </div>
                            <div class="col-md-5 form-group">
                                <label>الاسم بالعربية <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="اسم العميل" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>الاسم بالإنجليزية</label>
                                <input type="text" name="name_en" class="form-control"
                                    placeholder="Customer name in English" value="{{ old('name_en') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>نوع العميل <span class="text-danger">*</span></label>
                            <div class="d-flex mt-1">
                                <div class="custom-control custom-radio ml-4">
                                    <input type="radio" id="type_company" name="type" value="company"
                                        class="custom-control-input"
                                        {{ old('type', 'company') == 'company' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type_company">
                                        <i class="fas fa-building ml-1"></i> شركة
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type_individual" name="type" value="individual"
                                        class="custom-control-input"
                                        {{ old('type') == 'individual' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type_individual">
                                        <i class="fas fa-user ml-1"></i> فرد
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- بيانات التواصل --}}
                <div class="card card-outline card-info mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-phone ml-1"></i> بيانات التواصل</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>الهاتف</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="رقم الهاتف" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>هاتف بديل</label>
                                <input type="text" name="phone2" class="form-control"
                                    placeholder="رقم هاتف بديل" value="{{ old('phone2') }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="example@mail.com" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>العنوان</label>
                                <input type="text" name="address" class="form-control"
                                    placeholder="العنوان التفصيلي" value="{{ old('address') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>المدينة</label>
                                <input type="text" name="city" class="form-control"
                                    placeholder="المدينة" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>المحافظة</label>
                                <input type="text" name="governorate" class="form-control"
                                    placeholder="المحافظة" value="{{ old('governorate') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- البيانات التجارية --}}
                <div class="card card-outline card-warning mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-briefcase ml-1"></i> البيانات التجارية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>الرقم الضريبي</label>
                                <input type="text" name="tax_number" class="form-control"
                                    placeholder="الرقم الضريبي" value="{{ old('tax_number') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>السجل التجاري</label>
                                <input type="text" name="commercial_register" class="form-control"
                                    placeholder="رقم السجل التجاري" value="{{ old('commercial_register') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الشروط المالية --}}
                <div class="card card-outline card-success mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-money-bill-wave ml-1"></i> الشروط المالية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>حد الائتمان</label>
                                <div class="input-group">
                                    <input type="number" name="credit_limit" class="form-control"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('credit_limit', 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>شروط الدفع (أيام)</label>
                                <div class="input-group">
                                    <input type="number" name="payment_terms" class="form-control"
                                        min="0" placeholder="0"
                                        value="{{ old('payment_terms', 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">يوم</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>الرصيد الافتتاحي</label>
                                <div class="input-group">
                                    <input type="number" name="opening_balance" class="form-control"
                                        step="0.01" placeholder="0.00"
                                        value="{{ old('opening_balance', 0) }}">
                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                </div>
                                <small class="text-muted">موجب = دين على العميل، سالب = رصيد له</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ملاحظات وحالة --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-sticky-note ml-1"></i> ملاحظات وحالة</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active"
                                    name="is_active" value="1"
                                    {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">مفعّل</label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <a href="{{ route('sales_customers.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
