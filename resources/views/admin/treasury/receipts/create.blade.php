@extends('admin.layouts.treasury')
@section('title') سند قبض جديد @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('treasury_receipts.index') }}">سندات القبض</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-8">
    <form action="{{ route('treasury_receipts.store') }}" method="POST" id="voucherForm">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-hand-holding-usd ml-2"></i> سند قبض جديد</h3></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المبلغ <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم مرجعي</label>
                            <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">طريقة السداد <span class="text-danger">*</span></label>
                    <div class="d-flex" style="gap:16px">
                        <label><input type="radio" name="payment_method" value="cash" checked onchange="toggleMethod()"> نقدي</label>
                        <label><input type="radio" name="payment_method" value="bank" onchange="toggleMethod()"> بنكي</label>
                        <label><input type="radio" name="payment_method" value="cheque" onchange="toggleMethod()"> شيك</label>
                    </div>
                </div>

                <div class="row" id="cashBoxWrapper">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الخزنة النقدية</label>
                            <select name="cash_box_id" class="form-control select2">
                                <option value="">-- اختر الخزنة --</option>
                                @foreach($cashBoxes as $box)<option value="{{ $box->id }}">{{ $box->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="bankWrapper" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الحساب البنكي</label>
                            <select name="bank_account_id" class="form-control select2">
                                <option value="">-- اختر الحساب --</option>
                                @foreach($banks as $bank)<option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="chequeWrapper" style="display:none">
                    <div class="col-md-3">
                        <div class="form-group"><label>رقم الشيك</label><input type="text" name="cheque_number" class="form-control"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group"><label>البنك الساحب</label><input type="text" name="cheque_bank_name" class="form-control"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group"><label>تاريخ الشيك</label><input type="date" name="cheque_date" class="form-control"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group"><label>تاريخ الاستحقاق</label><input type="date" name="cheque_due_date" class="form-control"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الحساب البنكي المرتقب للتحصيل</label>
                            <select name="bank_account_id" class="form-control select2">
                                <option value="">-- اختر الحساب --</option>
                                @foreach($banks as $bank)<option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">وارد من <span class="text-danger">*</span></label>
                    <div class="d-flex" style="gap:16px">
                        <label><input type="radio" name="party_type" value="customer" checked onchange="togglePartyType()"> عميل</label>
                        <label><input type="radio" name="party_type" value="supplier" onchange="togglePartyType()"> مورد</label>
                        <label><input type="radio" name="party_type" value="employee" onchange="togglePartyType()"> موظف</label>
                        <label><input type="radio" name="party_type" value="other" onchange="togglePartyType()"> أخرى</label>
                    </div>
                </div>

                <div class="row" id="customerWrapper">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>العميل</label>
                            <select name="party_id" class="form-control select2" id="customerSelect">
                                <option value="">-- اختر العميل --</option>
                                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="supplierWrapper" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>المورد</label>
                            <select name="party_id_supplier" class="form-control select2">
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="employeeWrapper" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الموظف</label>
                            <select name="party_id_employee" class="form-control select2">
                                <option value="">-- اختر الموظف --</option>
                                @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="otherWrapper" style="display:none">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الحساب المحاسبي</label>
                            <select name="gl_account_id" class="form-control select2">
                                <option value="">-- اختر الحساب --</option>
                                @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ سند القبض</button>
                <a href="{{ route('treasury_receipts.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
function toggleMethod() {
    const method = document.querySelector('input[name=payment_method]:checked').value;
    document.getElementById('cashBoxWrapper').style.display = method === 'cash' ? '' : 'none';
    document.getElementById('bankWrapper').style.display   = method === 'bank' ? '' : 'none';
    document.getElementById('chequeWrapper').style.display = method === 'cheque' ? '' : 'none';
}

function togglePartyType() {
    const type = document.querySelector('input[name=party_type]:checked').value;
    document.getElementById('customerWrapper').style.display = type === 'customer' ? '' : 'none';
    document.getElementById('supplierWrapper').style.display = type === 'supplier' ? '' : 'none';
    document.getElementById('employeeWrapper').style.display = type === 'employee' ? '' : 'none';
    document.getElementById('otherWrapper').style.display    = type === 'other' ? '' : 'none';
}

$(document).ready(function () {
    $('select.select2').select2({ language: 'ar', width: '100%' });
    toggleMethod();
    togglePartyType();
    $('#voucherForm').on('submit', function () {
        const type = document.querySelector('input[name=party_type]:checked').value;
        const fieldMap = { customer: '#customerSelect', supplier: '[name=party_id_supplier]', employee: '[name=party_id_employee]' };
        if (fieldMap[type]) {
            $('<input>').attr({type: 'hidden', name: 'party_id'}).val($(fieldMap[type]).val()).appendTo(this);
        }
    });
});
</script>
@endsection
