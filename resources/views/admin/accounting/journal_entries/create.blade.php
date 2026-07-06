@extends('admin.layouts.accounting')
@section('title') قيد يومية جديد @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('journal_entries.index') }}">القيود اليومية</a> @endsection
@section('startpage') إضافة @endsection

@section('css')
<style>
    .lines-table th, .lines-table td { vertical-align: middle; }
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
<div class="col-12">
    <form action="{{ route('journal_entries.store') }}" method="POST" id="jeForm">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-book ml-2"></i> قيد يومية يدوي جديد</h3></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>تاريخ القيد <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" value="{{ old('entry_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>المرجع</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>البيان</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="بيان القيد">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list ml-2"></i> سطور القيد</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" id="addRow"><i class="fas fa-plus"></i> إضافة سطر</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered lines-table mb-0" id="linesTable">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:260px">الحساب</th>
                                <th style="width:180px">مركز التكلفة</th>
                                <th>البيان</th>
                                <th style="width:130px">مدين</th>
                                <th style="width:130px">دائن</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody"></tbody>
                        <tfoot>
                            <tr class="bg-light font-weight-bold">
                                <td colspan="3" class="text-left">الإجمالي</td>
                                <td id="totalDebit">0.00</td>
                                <td id="totalCredit">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <span id="balanceWarning" class="text-danger ml-3" style="display:none">القيد غير متوازن - يجب أن يتساوى إجمالي المدين مع الدائن</span>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ وترحيل</button>
            <a href="{{ route('journal_entries.index') }}" class="btn btn-secondary mr-2">إلغاء</a>
        </div>
    </form>
</div>

<template id="rowTemplate">
    <tr class="line-row">
        <td>
            <select name="lines[__INDEX__][account_id]" class="form-control form-control-sm account-select" required>
                <option value="">-- اختر الحساب --</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="lines[__INDEX__][cost_center_id]" class="form-control form-control-sm">
                <option value="">-- بدون --</option>
                @foreach($costCenters as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="lines[__INDEX__][description]" class="form-control form-control-sm"></td>
        <td><input type="number" name="lines[__INDEX__][debit]"  class="form-control form-control-sm row-debit"  value="0" min="0" step="0.01"></td>
        <td><input type="number" name="lines[__INDEX__][credit]" class="form-control form-control-sm row-credit" value="0" min="0" step="0.01"></td>
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
        $row.find('.account-select').select2({ language: 'ar', width: '100%', dropdownParent: $row.find('td:first') });
        $row.find('.row-debit, .row-credit').on('input', function () { calcTotals(); });
        $row.find('.row-remove').on('click', function () { $row.remove(); calcTotals(); });
    }

    function calcTotals() {
        let debit = 0, credit = 0;
        $('.row-debit').each(function () { debit += parseFloat($(this).val()) || 0; });
        $('.row-credit').each(function () { credit += parseFloat($(this).val()) || 0; });
        $('#totalDebit').text(debit.toFixed(2));
        $('#totalCredit').text(credit.toFixed(2));
        $('#balanceWarning').toggle(Math.abs(debit - credit) > 0.009);
    }

    $('#addRow').on('click', addRow);
    addRow();
    addRow();
});
</script>
@endsection
