@extends('admin.layouts.purchasing')
@section('title') تعديل طلب شراء @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_requests.index') }}">طلبات الشراء</a> @endsection
@section('startpage') تعديل @endsection

@section('css')
<style>
    .items-table th, .items-table td { vertical-align: middle; }
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
<div class="col-12">
    <form action="{{ route('purchase_requests.update', $req->id) }}" method="POST" id="requestForm">
        @csrf

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit ml-2"></i> تعديل طلب الشراء رقم: <strong>{{ $req->request_number }}</strong>
                </h3>
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
                            <label>رقم الطلب</label>
                            <input type="text" class="form-control" value="{{ $req->request_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ old('date', \Carbon\Carbon::parse($req->date)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>مطلوب بتاريخ</label>
                            <input type="date" name="needed_by_date" class="form-control"
                                   value="{{ old('needed_by_date', optional($req->needed_by_date)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>الفرع</label>
                            <select name="branch_id" class="form-control select2">
                                <option value="">-- اختر الفرع --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $req->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود الطلب</h3>
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
                                <th style="width:250px">الصنف</th>
                                <th>الوصف</th>
                                <th style="width:100px">الوحدة</th>
                                <th style="width:110px">الكمية</th>
                                <th>ملاحظات</th>
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
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $req->notes) }}</textarea>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save ml-1"></i> حفظ التعديلات
            </button>
            <a href="{{ route('purchase_requests.show', $req->id) }}" class="btn btn-secondary mr-2">
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
                    <option value="{{ $item->id }}" data-unit="{{ $item->unit->name ?? '' }}">
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="text"   name="items[__INDEX__][description]" class="form-control form-control-sm" placeholder="وصف اختياري"></td>
        <td><input type="text"   name="items[__INDEX__][unit]"        class="form-control form-control-sm row-unit"  readonly></td>
        <td><input type="number" name="items[__INDEX__][qty]"         class="form-control form-control-sm row-qty" value="1" min="0.001" step="0.001" required></td>
        <td><input type="text"   name="items[__INDEX__][notes]"       class="form-control form-control-sm row-notes" placeholder="ملاحظات"></td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger row-remove"><i class="fas fa-times"></i></button>
        </td>
    </tr>
</template>

<script>var existingItems = @json($req->items ?? []);</script>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('select.select2').select2({ language: 'ar', width: '100%' });
    let rowIndex = 0;

    function addRow(data) {
        const template = document.getElementById('rowTemplate');
        let html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const $row = $(html);
        $('#itemsBody').append($row);
        $row.find('.item-select').select2({ language: 'ar', width: '100%', dropdownParent: $row.find('td:first') });

        if (data) {
            $row.find('.item-select').val(data.item_id).trigger('change.select2');
            $row.find('[name$="[description]"]').val(data.description || '');
            $row.find('.row-unit').val(data.unit || '');
            $row.find('.row-qty').val(data.quantity || 1);
            $row.find('.row-notes').val(data.notes || '');
        }

        $row.find('.item-select').on('change', function () {
            const $opt = $(this).find(':selected');
            $row.find('.row-unit').val($opt.data('unit') || '');
        });
        $row.find('.row-remove').on('click', function () { $row.remove(); });
    }

    $('#addRow').on('click', function () { addRow(); });

    if (existingItems && existingItems.length > 0) {
        existingItems.forEach(function (item) { addRow(item); });
    } else {
        addRow();
    }
});
</script>
@endsection
