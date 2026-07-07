@extends('admin.layouts.accounting')
@section('title') {{ $direction === 'Sent' ? 'فواتير المبيعات' : 'فواتير المشتريات' }} @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') {{ $direction === 'Sent' ? 'المبيعات' : 'المشتريات' }} @endsection

@section('content')
<div class="col-12">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title card_title_center">
        <i class="fas fa-{{ $direction === 'Sent' ? 'arrow-up text-success' : 'arrow-down text-primary' }} ml-2"></i>
        {{ $direction === 'Sent' ? 'فواتير المبيعات' : 'فواتير المشتريات' }}
      </h3>
    </div>

    {{-- فلاتر --}}
    <div class="card-body pb-0">
      <form method="GET" action="{{ route('tax.invoices') }}" class="flex-wrap" id="filterForm">
        <input type="hidden" name="direction" value="{{ $direction }}">
        <div class="row">
          <div class="col-md-2 mb-2">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="من تاريخ">
          </div>
          <div class="col-md-2 mb-2">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="إلى تاريخ">
          </div>
          <div class="col-md-2 mb-2">
            <select name="status" class="form-control">
              <option value="">-- الحالة --</option>
              @foreach(['Valid' => 'معتمدة','Invalid' => 'غير صالحة','Cancelled' => 'ملغاة','Submitted' => 'مرسلة','Rejected' => 'مرفوضة'] as $val => $lbl)
                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <select name="doc_type" class="form-control">
              <option value="">-- النوع --</option>
              <option value="I" {{ request('doc_type')=='I' ? 'selected':'' }}>فاتورة</option>
              <option value="C" {{ request('doc_type')=='C' ? 'selected':'' }}>إشعار دائن</option>
              <option value="D" {{ request('doc_type')=='D' ? 'selected':'' }}>إشعار مدين</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <select name="is_posted" class="form-control">
              <option value="">-- الترحيل --</option>
              <option value="1" {{ request('is_posted')==='1' ? 'selected':'' }}>مرحّلة</option>
              <option value="0" {{ request('is_posted')==='0' ? 'selected':'' }}>غير مرحّلة</option>
            </select>
          </div>
          <div class="col-md-2 mb-2">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
              placeholder="بحث (اسم / رقم)">
          </div>
        </div>
        <div class="mb-2">
          <button type="submit" class="btn btn-primary btn-sm ml-1"><i class="fas fa-search ml-1"></i>بحث</button>
          <a href="{{ route('tax.invoices', ['direction' => $direction]) }}" class="btn btn-secondary btn-sm ml-1">مسح</a>
          <a href="{{ route('tax.invoices', array_merge(request()->all(), ['direction' => $direction === 'Sent' ? 'Received' : 'Sent'])) }}"
            class="btn btn-{{ $direction === 'Sent' ? 'primary' : 'success' }} btn-sm ml-1">
            <i class="fas fa-exchange-alt ml-1"></i>
            {{ $direction === 'Sent' ? 'المشتريات' : 'المبيعات' }}
          </a>
          <a href="{{ route('tax.export', request()->all()) }}" class="btn btn-success btn-sm ml-1">
            <i class="fas fa-file-excel ml-1"></i> تصدير Excel
          </a>
          <button type="button" class="btn btn-warning btn-sm" id="postSelectedBtn" disabled onclick="postSelected()">
            <i class="fas fa-check-double ml-1"></i> ترحيل المحدد
          </button>
        </div>
      </form>
    </div>

    @if(session('success'))
      <div class="alert alert-success mx-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger mx-3">{{ session('error') }}</div>
    @endif

    {{-- مجاميع --}}
    @if($totals && $totals->total_amount > 0)
    <div class="card-body py-2 bg-light border-top border-bottom">
      <div class="row text-center">
        <div class="col"><small class="text-muted d-block">إجمالي المبيعات</small><strong>{{ number_format($totals->total_sales ?? 0, 2) }}</strong></div>
        <div class="col"><small class="text-muted d-block">إجمالي الخصم</small><strong>{{ number_format($totals->total_discount ?? 0, 2) }}</strong></div>
        <div class="col"><small class="text-muted d-block">صافي المبلغ</small><strong>{{ number_format($totals->net_amount ?? 0, 2) }}</strong></div>
        <div class="col"><small class="text-muted d-block">الضريبة</small><strong class="text-danger">{{ number_format($totals->total_vat ?? 0, 2) }}</strong></div>
        <div class="col"><small class="text-muted d-block">الإجمالي</small><strong class="text-success">{{ number_format($totals->total_amount ?? 0, 2) }}</strong></div>
      </div>
    </div>
    @endif

    <div class="card-body pt-2">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead class="thead-dark">
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>#</th>
              <th>الرقم الداخلي</th>
              <th>{{ $direction === 'Sent' ? 'المستلم' : 'المورد' }}</th>
              <th>الرقم الضريبي</th>
              <th>تاريخ الإصدار</th>
              <th>النوع</th>
              <th>الإجمالي</th>
              <th>الضريبة</th>
              <th>الحالة</th>
              <th>الترحيل</th>
              <th>إجراء</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $inv)
            <tr class="{{ $inv->is_posted ? 'table-light' : '' }}">
              <td>
                @if(!$inv->is_posted && $inv->status === 'Valid')
                  <input type="checkbox" class="inv-check" value="{{ $inv->id }}">
                @endif
              </td>
              <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
              <td>
                <a href="{{ route('tax.show', $inv->id) }}">
                  {{ $inv->internal_id ?? substr($inv->uuid, 0, 12).'...' }}
                </a>
              </td>
              <td>{{ Str::limit($direction === 'Sent' ? $inv->receiver_name : $inv->issuer_name, 25) }}</td>
              <td><small>{{ $direction === 'Sent' ? $inv->receiver_id : $inv->issuer_id }}</small></td>
              <td>{{ $inv->date_issued?->format('Y-m-d') }}</td>
              <td><span class="badge badge-secondary">{{ $inv->doc_type_label }}</span></td>
              <td class="text-right font-weight-bold">{{ number_format($inv->total_amount, 2) }}</td>
              <td class="text-right text-danger">{{ number_format($inv->total_vat, 2) }}</td>
              <td><span class="badge badge-{{ $inv->status_class }}">{{ $inv->status_label }}</span></td>
              <td class="text-center">
                @if($inv->is_posted)
                  <span class="badge badge-success" title="{{ $inv->posted_at?->format('Y-m-d H:i') }}">
                    <i class="fas fa-check"></i> مرحّل
                  </span>
                @else
                  <span class="badge badge-light text-muted">—</span>
                @endif
              </td>
              <td class="text-center">
                <a href="{{ route('tax.show', $inv->id) }}" class="btn btn-xs btn-info" title="تفاصيل">
                  <i class="fas fa-eye"></i>
                </a>
                @if($inv->status === 'Valid')
                  @if(!$inv->is_posted)
                    <form action="{{ route('tax.post', $inv->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-xs btn-warning" title="ترحيل محاسبي">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                  @else
                    <form action="{{ route('tax.unpost', $inv->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-xs btn-secondary" title="إلغاء الترحيل"
                        onclick="return confirm('إلغاء ترحيل هذه الفاتورة؟')">
                        <i class="fas fa-undo"></i>
                      </button>
                    </form>
                  @endif
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="12" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                لا توجد فواتير — استخدم زر "سحب فواتير جديدة من ETA"
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $data->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.inv-check').forEach(cb => cb.checked = this.checked);
    updatePostBtn();
});
document.querySelectorAll('.inv-check').forEach(cb => {
    cb.addEventListener('change', updatePostBtn);
});
function updatePostBtn() {
    const checked = document.querySelectorAll('.inv-check:checked').length;
    document.getElementById('postSelectedBtn').disabled = checked === 0;
}
function postSelected() {
    const ids = [...document.querySelectorAll('.inv-check:checked')].map(cb => cb.value);
    if (!ids.length) return;
    if (!confirm('ترحيل ' + ids.length + ' فاتورة محاسبياً؟')) return;

    fetch('{{ route("tax.post_bulk") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
    });
}
</script>
@endsection
