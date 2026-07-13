@extends('admin.layouts.admin')

@section('title')تطبيق زيادة رواتب@endsection
@section('start')إدارة الموارد البشرية@endsection
@section('home')<a href="{{ route('salary_increases.index') }}">زيادات الرواتب</a>@endsection
@section('startpage')إضافة@endsection

@section('content')
<div class="col-12">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-arrow-trend-up mr-2"></i>تطبيق زيادة رواتب جديدة</h3>
    </div>
    <div class="card-body">

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('salary_increases.store') }}" id="increase-form">
        @csrf
        <div class="row">
          <div class="col-md-4 form-group">
            <label>نطاق التطبيق <span class="text-danger">*</span></label>
            <select name="scope_type" id="scope_type" class="form-control" required>
              <option value="">— اختر —</option>
              <option value="global">عام لكل الموظفين (افتراضي)</option>
              <option value="client">عميل معيّن</option>
              <option value="department">إدارة معيّنة</option>
              <option value="branch">فرع معيّن</option>
              <option value="job">وظيفة معيّنة</option>
              <option value="employee">موظف معيّن</option>
            </select>
          </div>

          <div class="col-md-4 form-group scope-id-wrap" data-scope="client" style="display:none">
            <label>العميل</label>
            <select class="form-control select2 scope-id-select" data-scope="client">
              <option value="">— اختر العميل —</option>
              @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->client_name }}</option>@endforeach
            </select>
          </div>

          <div class="col-md-4 form-group scope-id-wrap" data-scope="department" style="display:none">
            <label>الإدارة</label>
            <select class="form-control select2 scope-id-select" data-scope="department">
              <option value="">— اختر الإدارة —</option>
              @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->dep_name }}</option>@endforeach
            </select>
          </div>

          <div class="col-md-4 form-group scope-id-wrap" data-scope="branch" style="display:none">
            <label>الفرع</label>
            <select class="form-control select2 scope-id-select" data-scope="branch">
              <option value="">— اختر الفرع —</option>
              @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->branch_name }}</option>@endforeach
            </select>
          </div>

          <div class="col-md-4 form-group scope-id-wrap" data-scope="job" style="display:none">
            <label>الوظيفة</label>
            <select class="form-control select2 scope-id-select" data-scope="job">
              <option value="">— اختر الوظيفة —</option>
              @foreach($jobs_categories as $j)<option value="{{ $j->id }}">{{ $j->job_name }}</option>@endforeach
            </select>
          </div>

          <div class="col-md-4 form-group scope-id-wrap" data-scope="employee" style="display:none">
            <label>الموظف</label>
            <select class="form-control select2 scope-id-select" data-scope="employee">
              <option value="">— اختر الموظف —</option>
              @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->employee_id }} — {{ $e->employee_name_A }}</option>@endforeach
            </select>
          </div>

          <input type="hidden" name="scope_id" id="scope_id">
        </div>

        <div class="row">
          <div class="col-md-3 form-group">
            <label>طريقة الزيادة <span class="text-danger">*</span></label>
            <select name="method" id="method" class="form-control" required>
              <option value="fixed_amount">مبلغ ثابت</option>
              <option value="percentage">نسبة مئوية</option>
            </select>
          </div>
          <div class="col-md-3 form-group">
            <label>القيمة <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.01" min="0.01" name="value" id="value" class="form-control" required>
              <div class="input-group-append"><span class="input-group-text" id="value-suffix">EGP</span></div>
            </div>
          </div>
          <div class="col-md-3 form-group">
            <label>تاريخ السريان <span class="text-danger">*</span></label>
            <input type="date" name="effective_date" id="effective_date" class="form-control" value="{{ old('effective_date', today()->format('Y-m-d')) }}" required>
          </div>
          <div class="col-md-3 form-group">
            <label>ملاحظات</label>
            <input type="text" name="notes" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <button type="button" id="btn-preview" class="btn btn-outline-primary">
            <i class="fas fa-eye mr-1"></i>معاينة الموظفين المتأثرين
          </button>
        </div>

        <div id="preview-area" style="display:none">
          <div class="alert alert-info">
            سيتم تطبيق الزيادة على <strong id="preview-count">0</strong> موظف.
          </div>
          <div class="table-responsive mb-3">
            <table class="table table-bordered table-sm">
              <thead class="thead-light">
                <tr><th>كود الموظف</th><th>الاسم</th><th>الراتب الحالي</th><th>الراتب الجديد</th></tr>
              </thead>
              <tbody id="preview-rows"></tbody>
            </table>
          </div>
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary btn-lg col-3" id="btn-submit" disabled>تطبيق الزيادة</button>
          <a class="btn btn-warning btn-lg col-3" href="{{ route('salary_increases.index') }}">إلغاء</a>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#method').on('change', function () {
        $('#value-suffix').text(this.value === 'percentage' ? '%' : 'EGP');
    });

    $('#scope_type').on('change', function () {
        var scope = $(this).val();
        $('.scope-id-wrap').hide();
        $('#scope_id').val('');
        if (scope && scope !== 'global') {
            $('.scope-id-wrap[data-scope="' + scope + '"]').show();
        }
        $('#btn-submit').prop('disabled', true);
        $('#preview-area').hide();
    });

    $('.scope-id-select').on('change', function () {
        $('#scope_id').val($(this).val());
        $('#btn-submit').prop('disabled', true);
        $('#preview-area').hide();
    });

    $('#btn-preview').on('click', function () {
        var scopeType = $('#scope_type').val();
        if (!scopeType) { alert('اختر نطاق التطبيق أولاً'); return; }
        if (scopeType !== 'global' && !$('#scope_id').val()) { alert('اختر عنصر النطاق'); return; }
        if (!$('#value').val()) { alert('أدخل قيمة الزيادة'); return; }

        $.ajax({
            url: '{{ route("salary_increases.preview") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                scope_type: scopeType,
                scope_id: $('#scope_id').val(),
                method: $('#method').val(),
                value: $('#value').val(),
                effective_date: $('#effective_date').val()
            },
            success: function (res) {
                $('#preview-count').text(res.count);
                var rows = '';
                res.rows.forEach(function (r) {
                    rows += '<tr><td>' + r.employee_id + '</td><td>' + r.employee_name_A + '</td>' +
                        '<td>' + Number(r.current_salary).toFixed(2) + '</td>' +
                        '<td><strong>' + Number(r.new_salary).toFixed(2) + '</strong></td></tr>';
                });
                $('#preview-rows').html(rows || '<tr><td colspan="4" class="text-center text-muted">لا يوجد موظفين مطابقين لهذا النطاق</td></tr>');
                $('#preview-area').show();
                $('#btn-submit').prop('disabled', res.count === 0);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'حدث خطأ أثناء المعاينة';
                alert(msg);
            }
        });
    });
});
</script>
@endsection
