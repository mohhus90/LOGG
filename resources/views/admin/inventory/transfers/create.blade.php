@extends('admin.layouts.inventory')
@section('title') إضافة تحويل مخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('stock_transfers.index') }}">تحويلات المخازن</a> @endsection
@section('startpage') إضافة @endsection

@section('css')
<style>
    .items-table th, .items-table td { vertical-align: middle; }
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
<div class="col-12">
    <form action="{{ route('stock_transfers.store') }}" method="POST" id="transferForm">
        @csrf

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dolly ml-2"></i> إضافة تحويل مخزون جديد</h3>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>رقم التحويل</label>
                            <input type="text" class="form-control" value="{{ $nextNumber }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>من مخزن <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-control select2" required>
                                <option value="">-- اختر المخزن --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>إلى مخزن <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-control select2" required>
                                <option value="">-- اختر المخزن --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> الأصناف</h3>
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
                                <th style="width:300px">الصنف</th>
                                <th style="width:150px">الكمية</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات</h3></div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save ml-1"></i> حفظ التحويل (كمسودة)
            </button>
            <a href="{{ route('stock_transfers.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-times ml-1"></i> إلغاء
            </a>
            <small class="text-muted mr-3">لن يتم تحديث أرصدة المخزون إلا بعد تنفيذ التحويل</small>
        </div>
    </form>
</div>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][item_id]" class="form-control form-control-sm item-select" required>
                <option value="">-- اختر الصنف --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__INDEX__][qty]" class="form-control form-control-sm" value="1" min="0.001" step="0.001" required></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger row-remove"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('select.select2').select2({ language: 'ar', width: '100%' });
    let rowIndex = 0;

    function addRow() {
        const template = document.getElementById('rowTemplate');
        let html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const $row = $(html);
        $('#itemsBody').append($row);
        $row.find('.item-select').select2({ language: 'ar', width: '100%', dropdownParent: $row.find('td:first') });
        $row.find('.row-remove').on('click', function () { $row.remove(); });
    }

    $('#addRow').on('click', addRow);
    addRow();
});
</script>
@endsection
