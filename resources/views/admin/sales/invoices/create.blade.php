@extends('admin.layouts.sales')
@section('title') إضافة فاتورة مبيعات @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_invoices.index') }}">الفواتير</a> @endsection
@section('startpage') إضافة @endsection

@section('css')
<style>
    .items-table th, .items-table td { vertical-align: middle; }
    .totals-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; }
    .select2-container { width: 100% !important; }
    .invoice-type-label { cursor: pointer; }
</style>
@endsection

@section('content')
<div class="col-12">
    <form action="{{ route('sales_invoices.store') }}" method="POST" id="invoiceForm">
        @csrf

        {{-- Header Card --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice ml-2"></i> إضافة فاتورة مبيعات جديدة</h3>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                {{-- Invoice Type --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="d-block mb-2">نوع الفاتورة <span class="text-danger">*</span></label>
                        <div class="d-flex" style="gap:20px">
                            <label class="invoice-type-label d-flex align-items-center">
                                <input type="radio" name="invoice_type" value="cash"
                                       {{ old('invoice_type', 'cash') === 'cash' ? 'checked' : '' }}
                                       class="ml-2" id="typeCash" onchange="toggleDueDate()">
                                <span class="badge badge-success p-2 ml-2" style="font-size:13px">نقدي</span>
                            </label>
                            <label class="invoice-type-label d-flex align-items-center">
                                <input type="radio" name="invoice_type" value="credit"
                                       {{ old('invoice_type') === 'credit' ? 'checked' : '' }}
                                       class="ml-2" id="typeCredit" onchange="toggleDueDate()">
                                <span class="badge badge-info p-2 ml-2" style="font-size:13px">آجل (ذمم)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>رقم الفاتورة <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" class="form-control"
                                   value="{{ old('invoice_number', $nextNumber) }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3" id="dueDateWrapper">
                        <div class="form-group">
                            <label>تاريخ الاستحقاق <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" id="dueDate">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>المخزن</label>
                            <select name="warehouse_id" class="form-control select2">
                                <option value="">-- المخزن الافتراضي --</option>
                                @foreach($warehouses ?? [] as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="draft"  {{ old('status') == 'draft'  ? 'selected' : '' }}>مسودة</option>
                                <option value="issued" {{ old('status', 'issued') == 'issued' ? 'selected' : '' }}>مُصدَرة</option>
                            </select>
                        </div>
                    </div>
                    @if(isset($orders) && $orders->count() > 0)
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>مرتبط بأمر بيع (اختياري)</label>
                            <select name="order_id" class="form-control select2">
                                <option value="">-- بدون ربط --</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                        {{ $order->order_number }} - {{ $order->customer->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Items Card --}}
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود الفاتورة</h3>
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
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Totals + Notes --}}
        <div class="row">
            <div class="col-md-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات</h3></div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="ملاحظات الفاتورة...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-calculator ml-2"></i> ملخص الإجماليات</h3></div>
                    <div class="card-body totals-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span>الإجمالي الفرعي:</span><strong id="displaySubtotal">0.00</strong>
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
                            <span>مبلغ الخصم:</span><span id="displayDiscount">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>الضريبة (<span id="displayTaxRate">14</span>%):</span><span id="displayTax">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="h5">الإجمالي الكلي:</strong>
                            <strong class="h5 text-primary" id="displayTotal">0.00</strong>
                        </div>
                        <input type="hidden" name="subtotal"        id="subtotal"       value="0">
                        <input type="hidden" name="discount_amount" id="discountAmount" value="0">
                        <input type="hidden" name="tax_amount"      id="taxAmount"      value="0">
                        <input type="hidden" name="total"           id="total"          value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save ml-1"></i> حفظ الفاتورة
            </button>
            <a href="{{ route('sales_invoices.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-times ml-1"></i> إلغاء
            </a>
        </div>
    </form>
</div>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][item_id]" class="form-control form-control-sm item-select" required>
                <option value="">-- اختر الصنف --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" data-price="{{ $item->selling_price }}" data-unit="{{ $item->unit->name ?? '' }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="text"   name="items[__INDEX__][description]" class="form-control form-control-sm" placeholder="وصف اختياري"></td>
        <td><input type="text"   name="items[__INDEX__][unit]"        class="form-control form-control-sm row-unit" readonly></td>
        <td><input type="number" name="items[__INDEX__][qty]"         class="form-control form-control-sm row-qty"   value="1" min="0.001" step="0.001" required></td>
        <td><input type="number" name="items[__INDEX__][price]"       class="form-control form-control-sm row-price" value="0" min="0"     step="0.01"  required></td>
        <td><input type="number" name="items[__INDEX__][discount]"    class="form-control form-control-sm row-disc"  value="0" min="0"     max="100"    step="0.01"></td>
        <td><input type="number" name="items[__INDEX__][total]"       class="form-control form-control-sm row-total" value="0" readonly></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger row-remove"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>
@endsection

@section('script')
<script>
function toggleDueDate() {
    const isCash = document.getElementById('typeCash').checked;
    const wrapper = document.getElementById('dueDateWrapper');
    wrapper.style.display = isCash ? 'none' : '';
    document.getElementById('dueDate').required = !isCash;
}

$(document).ready(function () {
    toggleDueDate();
    $('select.select2').select2({ language: 'ar', width: '100%' });
    let rowIndex = 0;

    function addRow() {
        const template = document.getElementById('rowTemplate');
        let html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const $row = $(html);
        $('#itemsBody').append($row);
        $row.find('.item-select').select2({ language: 'ar', width: '100%', dropdownParent: $row.find('td:first') });
        $row.find('.item-select').on('change', function () {
            const $opt = $(this).find(':selected');
            $row.find('.row-price').val($opt.data('price') || 0);
            $row.find('.row-unit').val($opt.data('unit') || '');
            calcRowTotal($row);
        });
        $row.find('.row-qty, .row-price, .row-disc').on('input', function () { calcRowTotal($row); });
        $row.find('.row-remove').on('click', function () { $row.remove(); calcFooterTotals(); });
    }

    function calcRowTotal($row) {
        const qty   = parseFloat($row.find('.row-qty').val())   || 0;
        const price = parseFloat($row.find('.row-price').val()) || 0;
        const disc  = parseFloat($row.find('.row-disc').val())  || 0;
        $row.find('.row-total').val((qty * price * (1 - disc / 100)).toFixed(2));
        calcFooterTotals();
    }

    function calcFooterTotals() {
        let subtotal = 0;
        $('.row-total').each(function () { subtotal += parseFloat($(this).val()) || 0; });
        const taxRate = parseFloat($('#taxRate').val()) || 0;
        const discType = $('#discountType').val();
        const discVal  = parseFloat($('#discountValue').val()) || 0;
        let discAmt    = discType === 'percent' ? subtotal * discVal / 100 : discVal;
        if (discAmt > subtotal) discAmt = subtotal;
        const taxable = subtotal - discAmt;
        const taxAmt  = taxable * taxRate / 100;
        const total   = taxable + taxAmt;
        $('#displaySubtotal').text(subtotal.toFixed(2));
        $('#displayDiscount').text(discAmt.toFixed(2));
        $('#displayTaxRate').text(taxRate);
        $('#displayTax').text(taxAmt.toFixed(2));
        $('#displayTotal').text(total.toFixed(2));
        $('#subtotal').val(subtotal.toFixed(2));
        $('#discountAmount').val(discAmt.toFixed(2));
        $('#taxAmount').val(taxAmt.toFixed(2));
        $('#total').val(total.toFixed(2));
    }

    $('#taxRate, #discountType, #discountValue').on('input change', calcFooterTotals);
    $('#addRow').on('click', addRow);
    addRow();
});
</script>
@endsection
