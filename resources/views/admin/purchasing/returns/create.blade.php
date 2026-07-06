@extends('admin.layouts.purchasing')
@section('title') إضافة مرتجع @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_returns.index') }}">المرتجعات</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-12">
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-undo ml-2"></i> إضافة مرتجع جديد</h3>
        </div>
        <form method="POST" action="{{ route('purchase_returns.store') }}" id="returnForm">
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
                    <div class="col-md-3">
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
                                        {{ old('supplier_id', $invoice->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label>الفاتورة المرتجع منها (اختياري)</label>
                            <select name="invoice_id" id="invoice_id" class="form-control">
                                <option value="">-- اختر الفاتورة --</option>
                                @isset($invoice)
                                    <option value="{{ $invoice->id }}" selected>
                                        #{{ $invoice->invoice_number }} - {{ \Carbon\Carbon::parse($invoice->date)->format('Y/m/d') }}
                                    </option>
                                @endisset
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>المخزن (صرف البضاعة المرتجعة)</label>
                            <select name="warehouse_id" class="form-control select2">
                                <option value="">-- المخزن الافتراضي --</option>
                                @foreach($warehouses ?? [] as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>سبب الإرجاع <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                              rows="2" placeholder="اذكر سبب إرجاع البضاعة..." required>{{ old('reason') }}</textarea>
                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="card card-light mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="fas fa-list ml-2"></i>أصناف المرتجع</h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="addItemRow">
                                <i class="fas fa-plus"></i> إضافة صنف
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="itemsTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="30%">الصنف / الوصف</th>
                                        <th width="20%">البيان</th>
                                        <th width="10%">الوحدة</th>
                                        <th width="10%">الكمية</th>
                                        <th width="12%">السعر</th>
                                        <th width="12%">الإجمالي</th>
                                        <th width="6%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php
                                        $preloadedItems = isset($invoice) ? $invoice->items : collect(old('items', [['item'=>'','description'=>'','unit'=>'','qty'=>1,'price'=>0]]));
                                    @endphp
                                    @foreach($preloadedItems as $i => $item)
                                    <tr class="item-row">
                                        <td>
                                            <input type="text" name="items[{{ $i }}][item]"
                                                   class="form-control form-control-sm"
                                                   value="{{ is_array($item) ? ($item['item'] ?? '') : ($item->item->name ?? '') }}"
                                                   placeholder="اسم الصنف">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][description]"
                                                   class="form-control form-control-sm"
                                                   value="{{ is_array($item) ? ($item['description'] ?? '') : ($item->description ?? '') }}"
                                                   placeholder="البيان">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][unit]"
                                                   class="form-control form-control-sm"
                                                   value="{{ is_array($item) ? ($item['unit'] ?? '') : ($item->unit->name ?? '') }}"
                                                   placeholder="وحدة">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][qty]"
                                                   class="form-control form-control-sm qty-input"
                                                   value="{{ is_array($item) ? ($item['qty'] ?? 1) : ($item->quantity ?? 1) }}"
                                                   min="1" step="1">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][price]"
                                                   class="form-control form-control-sm price-input"
                                                   value="{{ is_array($item) ? ($item['price'] ?? 0) : ($item->unit_price ?? 0) }}"
                                                   min="0" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][total]"
                                                   class="form-control form-control-sm row-total"
                                                   value="{{ is_array($item) ? (($item['qty'] ?? 1) * ($item['price'] ?? 0)) : (($item->quantity ?? 1) * ($item->unit_price ?? 0)) }}"
                                                   readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-danger remove-row">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-left font-weight-bold">الإجمالي الفرعي</td>
                                        <td><input type="number" id="subtotal" class="form-control form-control-sm" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-left font-weight-bold">ضريبة القيمة المضافة (14%)</td>
                                        <td><input type="number" id="tax_amount" name="tax_amount" class="form-control form-control-sm" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="5" class="text-left font-weight-bold">الإجمالي الكلي</td>
                                        <td><input type="number" id="grand_total" name="total" class="form-control form-control-sm font-weight-bold" readonly></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="ملاحظات إضافية...">{{ old('notes') }}</textarea>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save ml-1"></i> حفظ المرتجع
                </button>
                <a href="{{ route('purchase_returns.index') }}" class="btn btn-secondary mr-2">
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

    var rowIndex = {{ count($preloadedItems ?? [['x']]) }};

    $('#addItemRow').on('click', function() {
        var row = `<tr class="item-row">
            <td><input type="text" name="items[${rowIndex}][item]" class="form-control form-control-sm" placeholder="اسم الصنف"></td>
            <td><input type="text" name="items[${rowIndex}][description]" class="form-control form-control-sm" placeholder="البيان"></td>
            <td><input type="text" name="items[${rowIndex}][unit]" class="form-control form-control-sm" placeholder="وحدة"></td>
            <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm qty-input" value="1" min="1" step="1"></td>
            <td><input type="number" name="items[${rowIndex}][price]" class="form-control form-control-sm price-input" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowIndex}][total]" class="form-control form-control-sm row-total" value="0" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-row"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#itemsBody').append(row);
        rowIndex++;
        recalculate();
    });

    $(document).on('click', '.remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            recalculate();
        } else {
            alert('يجب أن يكون هناك صنف واحد على الأقل');
        }
    });

    $(document).on('input', '.qty-input, .price-input', function() {
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.qty-input').val()) || 0;
        var price = parseFloat(row.find('.price-input').val()) || 0;
        row.find('.row-total').val((qty * price).toFixed(2));
        recalculate();
    });

    function recalculate() {
        var subtotal = 0;
        $('.row-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        var tax = subtotal * 0.14;
        var grand = subtotal + tax;
        $('#subtotal').val(subtotal.toFixed(2));
        $('#tax_amount').val(tax.toFixed(2));
        $('#grand_total').val(grand.toFixed(2));
    }

    recalculate();

    $('#supplier_id').on('change', function() {
        var supplierId = $(this).val();
        var $invoiceSelect = $('#invoice_id');
        $invoiceSelect.empty().append('<option value="">-- اختر الفاتورة --</option>');
        if (!supplierId) return;
        $.get('/admin/dashboard/purchasing/suppliers/' + supplierId + '/invoices', function(data) {
            $.each(data, function(i, inv) {
                $invoiceSelect.append('<option value="' + inv.id + '">#' + inv.invoice_number + ' - ' + inv.date + '</option>');
            });
        }).fail(function() { /* silent */ });
    });
});
</script>
@endpush
