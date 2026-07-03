@extends('admin.layouts.admin')
@section('title') تقرير العمولات @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions.index') }}">العمولات</a> @endsection
@section('startpage') تقرير شامل @endsection

@section('css')
<style>
.summary-card { border-radius: 10px; }
.sort-link { color: inherit; text-decoration: none; white-space: nowrap; }
.sort-link:hover { text-decoration: underline; color: inherit; }
.sort-icon { font-size: .75em; opacity: .6; }
.filter-bar { background: #f8f9fa; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- بطاقات الإجماليات --}}
  @php
    $months = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $approved  = $summary->get(1);
    $pending   = $summary->get(2);
    $cancelled = $summary->get(3);
    $grandTotal = ($approved->total ?? 0) + ($pending->total ?? 0);
  @endphp

  <div class="row mb-3">
    <div class="col-md-3 col-6 mb-2">
      <div class="card summary-card bg-success text-white">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div style="font-size:.8em;opacity:.85">معتمدة</div>
              <div style="font-size:1.4em;font-weight:700">{{ number_format($approved->total ?? 0, 2) }}</div>
              <small>{{ $approved->cnt ?? 0 }} سجل</small>
            </div>
            <i class="fas fa-check-circle fa-2x" style="opacity:.5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="card summary-card bg-warning text-white">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div style="font-size:.8em;opacity:.85">معلقة</div>
              <div style="font-size:1.4em;font-weight:700">{{ number_format($pending->total ?? 0, 2) }}</div>
              <small>{{ $pending->cnt ?? 0 }} سجل</small>
            </div>
            <i class="fas fa-clock fa-2x" style="opacity:.5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="card summary-card bg-danger text-white">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div style="font-size:.8em;opacity:.85">ملغاة</div>
              <div style="font-size:1.4em;font-weight:700">{{ number_format($cancelled->total ?? 0, 2) }}</div>
              <small>{{ $cancelled->cnt ?? 0 }} سجل</small>
            </div>
            <i class="fas fa-times-circle fa-2x" style="opacity:.5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="card summary-card bg-primary text-white">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div style="font-size:.8em;opacity:.85">الإجمالي (معتمد + معلق)</div>
              <div style="font-size:1.4em;font-weight:700">{{ number_format($grandTotal, 2) }}</div>
              <small>{{ ($approved->cnt ?? 0) + ($pending->cnt ?? 0) }} سجل</small>
            </div>
            <i class="fas fa-coins fa-2x" style="opacity:.5"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- فلتر --}}
  <div class="filter-bar">
    <form method="GET" action="{{ route('commissions.report') }}">
      <input type="hidden" name="sort" value="{{ $sort }}">
      <input type="hidden" name="dir"  value="{{ $dir }}">
      <div class="row align-items-end">
        <div class="col-md-3 mb-2">
          <label class="small font-weight-bold mb-1">الموظف</label>
          <select name="employee_id" class="form-control form-control-sm">
            <option value="">-- كل الموظفين --</option>
            @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->employee_name_A }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">الشهر</label>
          <select name="month" class="form-control form-control-sm">
            <option value="">-- الكل --</option>
            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
            <option value="{{ $i+1 }}" {{ request('month') == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1 mb-2">
          <label class="small font-weight-bold mb-1">السنة</label>
          <input type="number" name="year" class="form-control form-control-sm"
            placeholder="السنة" value="{{ request('year', now()->year) }}" style="width:80px">
        </div>
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">الحالة</label>
          <select name="status" class="form-control form-control-sm">
            <option value="">-- الكل --</option>
            <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>معتمدة</option>
            <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>معلقة</option>
            <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>ملغاة</option>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <label class="small font-weight-bold mb-1">نوع العمولة</label>
          <select name="commission_type" class="form-control form-control-sm">
            <option value="">-- الكل --</option>
            @foreach($commissionTypes as $type)
            <option value="{{ $type }}" {{ request('commission_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 mb-2 d-flex" style="gap:6px;padding-top:20px">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="fas fa-search"></i> بحث
          </button>
          <a href="{{ route('commissions.report') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-undo"></i>
          </a>
        </div>
      </div>
    </form>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-file-alt ml-2 text-primary"></i>
        تقرير العمولات
        <small class="text-muted">({{ $data->total() }} سجل)</small>
      </h5>
      <a href="{{ route('commissions.create') }}" class="btn btn-sm btn-success">
        <i class="fas fa-plus"></i> إضافة عمولة
      </a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm mb-0">
          <thead class="thead-dark">
            <tr>
              <th style="width:40px">#</th>
              <th>الموظف</th>
              <th>
                @php
                  $dateDir = ($sort === 'date' && $dir === 'desc') ? 'asc' : 'desc';
                @endphp
                <a class="sort-link" href="{{ route('commissions.report', array_merge(request()->query(), ['sort'=>'date','dir'=>$dateDir])) }}">
                  التاريخ
                  @if($sort === 'date') <i class="fas fa-sort-{{ $dir === 'desc' ? 'down' : 'up' }} sort-icon"></i>
                  @else <i class="fas fa-sort sort-icon"></i> @endif
                </a>
              </th>
              <th>نوع العمولة</th>
              <th>
                @php $amountDir = ($sort === 'amount' && $dir === 'desc') ? 'asc' : 'desc'; @endphp
                <a class="sort-link" href="{{ route('commissions.report', array_merge(request()->query(), ['sort'=>'amount','dir'=>$amountDir])) }}">
                  القيمة
                  @if($sort === 'amount') <i class="fas fa-sort-{{ $dir === 'desc' ? 'down' : 'up' }} sort-icon"></i>
                  @else <i class="fas fa-sort sort-icon"></i> @endif
                </a>
              </th>
              <th>
                @php $monthDir = ($sort === 'month' && $dir === 'desc') ? 'asc' : 'desc'; @endphp
                <a class="sort-link" href="{{ route('commissions.report', array_merge(request()->query(), ['sort'=>'month','dir'=>$monthDir])) }}">
                  الشهر / السنة
                  @if($sort === 'month') <i class="fas fa-sort-{{ $dir === 'desc' ? 'down' : 'up' }} sort-icon"></i>
                  @else <i class="fas fa-sort sort-icon"></i> @endif
                </a>
              </th>
              <th>الحالة</th>
              <th>ملاحظات</th>
              <th>إجراء</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $com)
            <tr>
              <td class="text-center text-muted">{{ $data->firstItem() + $loop->index }}</td>
              <td>
                <strong>{{ $com->employee->employee_name_A ?? '—' }}</strong>
                <br><small class="text-muted">{{ $com->employee->employee_id ?? '' }}</small>
              </td>
              <td>{{ $com->commission_date }}</td>
              <td>{{ $com->commission_type ?? '—' }}</td>
              <td class="text-success font-weight-bold">{{ number_format($com->amount, 2) }} ج.م</td>
              <td>{{ $months[$com->month] ?? $com->month }} {{ $com->year }}</td>
              <td>{!! $com->status_label !!}</td>
              <td><small class="text-muted">{{ $com->notes }}</small></td>
              <td>
                <a href="{{ route('commissions.edit', $com->id) }}" class="btn btn-xs btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="{{ route('commissions.delete', $com->id) }}" class="btn btn-xs btn-danger"
                   onclick="return confirm('حذف هذه العمولة؟')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                لا توجد عمولات تطابق الفلتر المحدد
              </td>
            </tr>
            @endforelse
          </tbody>
          @if($data->count() > 0)
          <tfoot class="table-dark">
            <tr>
              <th colspan="4" class="text-right">إجمالي الصفحة الحالية</th>
              <th class="text-warning">{{ number_format($data->sum('amount'), 2) }} ج.م</th>
              <th colspan="4"></th>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>

      <div class="px-3 py-2">
        {{ $data->links() }}
      </div>
    </div>
  </div>

</div>
@endsection
