@extends('admin.layouts.purchasing')
@section('title') إضافة دفعة @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_payments.index') }}">المدفوعات</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-12">
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i> إضافة دفعة جديدة</h3>
        </div>
        <form method="POST" action="{{ route('purchase_payments.store') }}">
            @csrf
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المورد <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id"
                                    class="form-control select2 @error('supplier_id') is-invalid @enderror" required>
                                <option value="">-- اختر المورد --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id', $selectedInv->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>الفاتورة (اختياري)</label>
                            <select name="invoice_id" id="invoice_id" class="form-control">
                                <option value="">-- اختر الفاتورة --</option>
                                @isset($selectedInv)
                                    <option value="{{ $selectedInv->id }}" selected>
                                        #{{ $selectedInv->invoice_number }} - متبقي: {{ number_format($selectedInv->remaining_amount, 2) }} ج.م
                                    </option>
                                @endisset
                            </select>
                            <small class="text-muted">يتم تحميل الفواتير غير المسددة بعد اختيار المورد</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المبلغ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $selectedInv->remaining_amount ?? '') }}"
                                       placeholder="0.00" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">ج.م</span>
                                </div>
                            </div>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>الرقم المرجعي</label>
                            <input type="text" name="reference_number" class="form-control"
                                   value="{{ old('reference_number') }}" placeholder="رقم مرجعي اختياري">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>طريقة الدفع <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input payment-method-radio" type="radio"
                                       name="payment_method" id="method_cash" value="cash"
                                       {{ old('payment_method','cash') == 'cash' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="method_cash">
                                    <i class="fas fa-money-bill-alt text-success ml-1"></i> نقدي
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input payment-method-radio" type="radio"
                                       name="payment_method" id="method_bank" value="bank"
                                       {{ old('payment_method') == 'bank' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="method_bank">
                                    <i class="fas fa-university text-primary ml-1"></i> تحويل بنكي
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input payment-method-radio" type="radio"
                                       name="payment_method" id="method_cheque" value="cheque"
                                       {{ old('payment_method') == 'cheque' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="method_cheque">
                                    <i class="fas fa-money-check text-warning ml-1"></i> شيك
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input payment-method-radio" type="radio"
                                       name="payment_method" id="method_pos" value="pos"
                                       {{ old('payment_method') == 'pos' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="method_pos">
                                    <i class="fas fa-credit-card text-info ml-1"></i> بطاقة ائتمان
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bank_fields" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم البنك</label>
                                <input type="text" name="bank_name" class="form-control"
                                       value="{{ old('bank_name') }}" placeholder="اسم البنك">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="cheque_fields" style="display:none;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>رقم الشيك</label>
                                <input type="text" name="cheque_number" class="form-control"
                                       value="{{ old('cheque_number') }}" placeholder="رقم الشيك">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>تاريخ الشيك</label>
                                <input type="date" name="cheque_date" class="form-control"
                                       value="{{ old('cheque_date') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ الدفعة
                </button>
                <a href="{{ route('purchase_payments.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times ml-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    $('.select2').select2({ placeholder: '-- اختر --', allowClear: true, width: '100%' });

    function togglePaymentFields() {
        var method = $('input[name="payment_method"]:checked').val();
        if (method === 'bank' || method === 'cheque') {
            $('#bank_fields').slideDown(200);
        } else {
            $('#bank_fields').slideUp(200);
            $('input[name="bank_name"]').val('');
        }
        if (method === 'cheque') {
            $('#cheque_fields').slideDown(200);
        } else {
            $('#cheque_fields').slideUp(200);
            $('input[name="cheque_number"]').val('');
            $('input[name="cheque_date"]').val('');
        }
    }

    togglePaymentFields();
    $('.payment-method-radio').on('change', function() { togglePaymentFields(); });

    $('#supplier_id').on('change', function() {
        var supplierId = $(this).val();
        var $invoiceSelect = $('#invoice_id');
        $invoiceSelect.empty().append('<option value="">-- اختر الفاتورة --</option>');
        if (!supplierId) return;
        $.get('/admin/dashboard/purchasing/suppliers/' + supplierId + '/unpaid-invoices', function(data) {
            $.each(data, function(i, inv) {
                $invoiceSelect.append(
                    '<option value="' + inv.id + '" data-remaining="' + inv.remaining_amount + '">#' + inv.invoice_number + ' - متبقي: ' + parseFloat(inv.remaining_amount).toFixed(2) + ' ج.م</option>'
                );
            });
        }).fail(function() {
            // No AJAX endpoint yet - silent fail
        });
    });

    $('#invoice_id').on('change', function() {
        var remaining = $(this).find('option:selected').data('remaining');
        if (remaining) {
            $('#amount').val(parseFloat(remaining).toFixed(2));
        }
    });
});
</script>
@endpush
