@extends('admin.layouts.manufacturing')
@section('title') قائمة مواد جديدة @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('bill_of_materials.index') }}">قوائم المواد</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-12">
    <form action="{{ route('bill_of_materials.store') }}" method="POST" id="bomForm">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sitemap ml-2"></i> قائمة مواد جديدة</h3></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>المنتج التام / نصف المصنع <span class="text-danger">*</span></label>
                            <select name="item_id" class="form-control select2" required>
                                <option value="">-- اختر المنتج --</option>
                                @foreach($finishedItems as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>كمية الإنتاج لكل دفعة <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" name="output_quantity" class="form-control" value="1" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <input type="text" name="notes" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> المكونات (المواد الخام)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" id="addRow"><i class="fas fa-plus"></i> إضافة مكوّن</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="linesTable">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:260px">المكوّن</th>
                                <th style="width:130px">الكمية</th>
                                <th style="width:130px">الوحدة</th>
                                <th style="width:110px">نسبة الهالك %</th>
                                <th>ملاحظات</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ قائمة المواد</button>
            <a href="{{ route('bill_of_materials.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<template id="rowTemplate">
    <tr class="line-row">
        <td>
            <select name="lines[__INDEX__][component_item_id]" class="form-control form-control-sm component-select" required>
                <option value="">-- اختر المكوّن --</option>
                @foreach($componentItems as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.0001" name="lines[__INDEX__][quantity]" class="form-control form-control-sm" required></td>
        <td>
            <select name="lines[__INDEX__][unit_id]" class="form-control form-control-sm">
                <option value="">-- بدون --</option>
                @foreach($units as $unit)<option value="{{ $unit->id }}">{{ $unit->name }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" name="lines[__INDEX__][scrap_percent]" class="form-control form-control-sm" value="0"></td>
        <td><input type="text" name="lines[__INDEX__][notes]" class="form-control form-control-sm"></td>
        <td class="text-center"><button type="button" class="btn btn-xs btn-danger row-remove"><i class="fas fa-times"></i></button></td>
    </tr>
</template>
@endsection

@section('script')
<script>
$(document).ready(function () {
    let rowIndex = 0;
    function addRow() {
        const template = document.getElementById('rowTemplate');
        const html = template.innerHTML.replace(/__INDEX__/g, rowIndex++);
        const $row = $(html);
        $('#linesBody').append($row);
        $row.find('.component-select').select2({ language: 'ar', width: '100%', dropdownParent: $row.find('td:first') });
        $row.find('.row-remove').on('click', function () { $row.remove(); });
    }
    $('#addRow').on('click', addRow);
    addRow();
});
</script>
@endsection
