@extends('admin.layouts.sales')
@section('title') إضافة عرض سعر @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_quotations.index') }}">عروض الأسعار</a> @endsection
@section('startpage') إضافة @endsection

@section('css')
<style>
    .items-table th, .items-table td { vertical-align: middle; }
    .totals-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; }
    .row-remove { cursor: pointer; }
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
<div class="col-12">
    <form action="{{ route('sales_quotations.store') }}" method="POST" id="quotationForm">
        @csrf

        {{-- Header Card --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> إضافة عرض سعر جديد</h3>
            </div>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>رقم العرض <span class="text-danger">*</span></label>
                            <input type="text" name="quote_number" class="form-control"
                                   value="{{ old('quote_number', $nextNumber) }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>صالح حتى <span class="text-danger">*</span></label>
                            <input type="date" name="valid_until" class="form-control"
                                   value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>نسبة الضريبة %</label>
                            <input type="number" name="tax_rate" id="taxRate" class="form-control"
                                   value="{{ old('tax_rate', 14) }}" min="0" max="100" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>العميل <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-control select2" required>
                                <option value="">-- اختر العميل --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الفرع</label>
                            <select name="branch_id" class="form-control select2">
                                <option value="">-- اختر الفرع --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Card --}}
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود العرض</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" id="addRow">
                        <i class="fas fa-plus"></i> إضافة صنف
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered items-table mb-0" id="itemsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:220px">الصنف</th>
                                <th>الوصف</th>
                                <th style="width:100px">الوحدة</th>
                                <th style="width:90px">الكمية</th>
                                <th style="width:110px">السعر</th>
                                <th style="width:80px">خصم%</th>
                                <th style="width:110px">الإجمالي</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Rows added by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Totals + Notes Card --}}
        <div class="row">
            <div class="col-md-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات وشروط</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>الشروط والأحكام</label>
                            <textarea name="terms" class="form-control" rows="3" placeholder="شروط وأحكام العرض...">{{ old('terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calculator ml-2"></i> ملخص الإجماليات</h3>
                    </div>
                    <div class="card-body totals-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span>الإجمالي الفرعي:</span>
                            <strong id="displaySubtotal">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span>الخصم الإجمالي:</span>
                            <div class="d-flex align-items-center">
                                <select name="discount_type" id="discountType" class="form-control form-control-sm ml-1" style="width:80px">
                                    <option value="percent">%</option>
                                    <option value="amount">مبلغ</option>
                                </select>
                                <input type="number" name="discount_value" id="discountValue"
                                       class="form-control form-control-sm" style="width:80px"
                                       value="{{ old('discount_value', 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>مبلغ الخصم:</span>
                            <span id="displayDiscount">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>الوعاء الخاضع للضريبة:</span>
                            <span id="displayTaxable">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>الضريبة (<span id="displayTaxRate">14</span>%):</span>
                            <span id="displayTax">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="h5">الإجمالي الكلي:</strong>
                            <strong class="h5 text-primary" id="displayTotal">0.00</strong>
                        </div>
                        {{-- Hidden totals --}}
                        <input type="hidden" name="subtotal" id="subtotal" value="0">
                        <input type="hidden" name="discount_amount" id="discountAmount" value="0">
                        <input type="hidden" name="tax_amount" id="taxAmount" value="0">
                        <input type="hidden" name="total" id="total" value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save ml-1"></i> حفظ عرض السعر
            </button>
            <a href="{{ route('sales_quotations.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-times ml-1"></i> إلغاء
            </a>
        </div>
    </form>
</div>

{{-- Row Template (hidden) --}}
<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][item_id]" class="form-control form-control-sm item-select" required>
                <option value="">-- اختر الصنف --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}"
                            data-price="{{ $item->selling_price }}"
                            data-unit="{{ $item->unit->name ?? '' }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="items[__INDEX__][description]" class="form-control form-control-sm" placeholder="وصف اختياري">
        </td>
        <td>
            <input type="text" name="items[__INDEX__][unit]" class="form-control form-control-sm row-unit" readonly placeholder="الوحدة">
        </td>
        <td>
            <input type="number" name="items[__INDEX__][qty]" class="form-control form-control-sm row-qty"
                   value="1" min="0.001" step="0.001" required>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][price]" class="form-control form-control-sm row-price"
                   value="0" min="0" step="0.01" required>
        </td>
        <td>
            <input type="number" name="items[__INDEX__][discount]" class="form-control form-control-sm row-disc"
                   value="0" min="0" max="100" step="0.01">
        </td>
        <td>
            <input type="number" name="items[__INDEX__][total]" class="form-control form-control-sm row-total text-left"
                   value="0" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger row-remove">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@section('script')
<script>
$(document).ready(function () {
    // Select2 init
    $('select.select2').select2({ language: 'ar', width: '100%' });

    let rowIndex = 0;

    function addRow(data) {
        const template = document.getElementById('rowTemplate');
        let html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const $row = $(html);
        $('#itemsBody').append($row);

        // Init select2 for item select in new row
        $row.find('.item-select').select2({
            language: 'ar',
            width: '100%',
            dropdownParent: $row.find('td:first')
        });

        if (data) populateRow($row, data);

        bindRowEvents($row);
    }

    function populateRow($row, data) {
        $row.find('.item-select').val(data.item_id).trigger('change');
        $row.find('[name$="[description]"]').val(data.description || '');
        $row.find('.row-unit').val(data.unit || '');
        $row.find('.row-qty').val(data.qty || 1);
        $row.find('.row-price').val(data.price || 0);
        $row.find('.row-disc').val(data.discount || 0);
        calcRowTotal($row);
    }

    function bindRowEvents($row) {
        $row.find('.item-select').on('change', function () {
            const $opt = $(this).find(':selected');
            const price = $opt.data('price') || 0;
            const unit  = $opt.data('unit') || '';
            $row.find('.row-price').val(price);
            $row.find('.row-unit').val(unit);
            calcRowTotal($row);
        });

        $row.find('.row-qty, .row-price, .row-disc').on('input', function () {
            calcRowTotal($row);
        });

        $row.find('.row-remove').on('click', function () {
            $row.remove();
            calcFooterTotals();
        });
    }

    function calcRowTotal($row) {
        const qty   = parseFloat($row.find('.row-qty').val())   || 0;
        const price = parseFloat($row.find('.row-price').val()) || 0;
        const disc  = parseFloat($row.find('.row-disc').val())  || 0;
        const total = qty * price * (1 - disc / 100);
        $row.find('.row-total').val(total.toFixed(2));
        calcFooterTotals();
    }

    function calcFooterTotals() {
        let subtotal = 0;
        $('.row-total').each(function () {
            subtotal += parseFloat($(this).val()) || 0;
        });

        const taxRate      = parseFloat($('#taxRate').val()) || 0;
        const discType     = $('#discountType').val();
        const discVal      = parseFloat($('#discountValue').val()) || 0;

        let discAmount = 0;
        if (discType === 'percent') {
            discAmount = subtotal * discVal / 100;
        } else {
            discAmount = discVal;
        }
        if (discAmount > subtotal) discAmount = subtotal;

        const taxable  = subtotal - discAmount;
        const taxAmt   = taxable * taxRate / 100;
        const total    = taxable + taxAmt;

        $('#displaySubtotal').text(subtotal.toFixed(2));
        $('#displayDiscount').text(discAmount.toFixed(2));
        $('#displayTaxable').text(taxable.toFixed(2));
        $('#displayTaxRate').text(taxRate);
        $('#displayTax').text(taxAmt.toFixed(2));
        $('#displayTotal').text(total.toFixed(2));

        $('#subtotal').val(subtotal.toFixed(2));
        $('#discountAmount').val(discAmount.toFixed(2));
        $('#taxAmount').val(taxAmt.toFixed(2));
        $('#total').val(total.toFixed(2));
    }

    // Bind footer inputs
    $('#taxRate, #discountType, #discountValue').on('input change', calcFooterTotals);

    // Add row button
    $('#addRow').on('click', function () { addRow(); });

    // Add first row on load
    addRow();
});
</script>
@endsection
