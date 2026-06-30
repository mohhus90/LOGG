@extends('admin.layouts.admin')
@section('title') إضافة سلف @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('advances.index') }}">السلف</a> @endsection
@section('startpage') إضافة @endsection

@section('css')
<style>
.advances-table th { background:#28a745; color:#fff; font-size:.85rem; white-space:nowrap; }
.advances-table td { vertical-align:middle; }
.monthly-display { font-weight:bold; color:#155724; }
.row-num { color:#aaa; font-size:.8rem; }
.btn-remove-row { padding:2px 8px; }
</style>
@endsection

@section('content')
<div class="col-12">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i>إضافة سلف جديدة</h3>
            <div class="card-tools">
                <small class="text-white opacity-75">يمكنك إضافة أكثر من سلفة في نفس الوقت</small>
            </div>
        </div>
        <form action="{{ route('advances.store') }}" method="POST" id="advancesForm">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered advances-table" id="rowsTable">
                        <thead>
                            <tr>
                                <th style="width:30px">#</th>
                                <th style="min-width:200px">الموظف <span class="text-warning">*</span></th>
                                <th style="width:140px">تاريخ السلفة <span class="text-warning">*</span></th>
                                <th style="width:130px">قيمة السلفة <span class="text-warning">*</span></th>
                                <th style="width:110px">عدد الأقساط <span class="text-warning">*</span></th>
                                <th style="width:130px">القسط الشهري</th>
                                <th>ملاحظات</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="rowsBody">
                            <!-- صف أول افتراضي -->
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-success btn-sm" id="addRowBtn">
                    <i class="fas fa-plus ml-1"></i> إضافة سطر جديد
                </button>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save ml-1"></i> حفظ الجميع
                </button>
                <a href="{{ route('advances.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>

<!-- Template لكل صف -->
<template id="rowTemplate">
    <tr data-idx="__IDX__">
        <td class="text-center row-num">__NUM__</td>
        <td>
            <select name="rows[__IDX__][employee_id]" class="form-control form-control-sm select2-row" required>
                <option value="">-- اختر --</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->employee_name_A }} ({{ $emp->employee_id }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="date" name="rows[__IDX__][advance_date]" class="form-control form-control-sm"
                   required value="{{ today()->format('Y-m-d') }}">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="rows[__IDX__][amount]" class="form-control amount-input"
                       step="0.01" min="1" required placeholder="0.00">
                <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
            </div>
        </td>
        <td>
            <input type="number" name="rows[__IDX__][installments]" class="form-control form-control-sm inst-input"
                   min="1" required value="1">
        </td>
        <td class="monthly-display text-center">—</td>
        <td>
            <input type="text" name="rows[__IDX__][notes]" class="form-control form-control-sm" placeholder="اختياري">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-xs btn-remove-row" title="حذف">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@section('script')
<script>
let rowCount = 0;

function addRow() {
    rowCount++;
    const tmpl = document.getElementById('rowTemplate').innerHTML
        .replace(/__IDX__/g, rowCount)
        .replace(/__NUM__/g, rowCount);

    const tbody = document.getElementById('rowsBody');
    const temp = document.createElement('tbody');
    temp.innerHTML = tmpl;
    const newRow = temp.firstElementChild;
    if (newRow) tbody.appendChild(newRow);

    initRow(tbody.lastElementChild);
    reNumber();
}

function initRow(tr) {
    const amountInput = tr.querySelector('.amount-input');
    const instInput   = tr.querySelector('.inst-input');
    const monthly     = tr.querySelector('.monthly-display');

    function calc() {
        const a = parseFloat(amountInput.value) || 0;
        const i = parseInt(instInput.value)    || 1;
        monthly.textContent = i > 0 ? (a / i).toFixed(2) + ' ج.م' : '—';
    }
    amountInput.addEventListener('input', calc);
    instInput.addEventListener('input',   calc);
    calc();

    // select2 on the new select
    if (typeof $.fn.select2 !== 'undefined') {
        $(tr.querySelector('select')).select2({ width: '100%', language: 'ar', placeholder: '-- اختر --' });
    }

    tr.querySelector('.btn-remove-row').addEventListener('click', function () {
        if (document.querySelectorAll('#rowsBody tr').length > 1) {
            if (typeof $.fn.select2 !== 'undefined') $(tr.querySelector('select')).select2('destroy');
            tr.remove();
            reNumber();
        } else {
            alert('يجب أن يبقى صف واحد على الأقل');
        }
    });
}

function reNumber() {
    document.querySelectorAll('#rowsBody tr').forEach((tr, i) => {
        const num = tr.querySelector('.row-num');
        if (num) num.textContent = i + 1;
    });
}

document.getElementById('addRowBtn').addEventListener('click', addRow);

// أضف الصف الأول تلقائياً
addRow();
</script>
@endsection
