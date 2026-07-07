@extends('admin.layouts.sales')
@section('title') إغلاق جلسة كاشير @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos_sessions.index') }}">جلسات الكاشير</a> @endsection
@section('startpage') إغلاق جلسة #{{ $session->id }} @endsection

@section('content')
<div class="col-12">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card card-danger card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-door-closed ml-2"></i>إغلاق الجلسة #{{ $session->id }}</h3></div>
        <div class="card-body">
          <table class="table table-sm">
            <tr><td>الكاشير</td><td>{{ $session->register->name }}</td></tr>
            <tr><td>فُتحت بواسطة</td><td>{{ $session->openedBy->name ?? '-' }}</td></tr>
            <tr><td>وقت الفتح</td><td>{{ optional($session->opened_at)->format('Y-m-d H:i') }}</td></tr>
            <tr><td>الرصيد الافتتاحي</td><td>{{ number_format($session->opening_amount, 2) }}</td></tr>
            <tr><td>إجمالي مبيعات الجلسة النقدية</td><td>{{ number_format($session->sales_total, 2) }}</td></tr>
            <tr class="font-weight-bold"><td>المتوقع بالدرج</td><td>{{ number_format($session->opening_amount + $session->sales_total, 2) }}</td></tr>
          </table>

          <form method="POST" action="{{ route('pos_sessions.close', $session->id) }}">
            @csrf
            <div class="form-group">
              <label>المبلغ الفعلي المعدود بالدرج</label>
              <input type="number" step="0.01" min="0" class="form-control" name="counted_closing_amount" required
                value="{{ number_format($session->opening_amount + $session->sales_total, 2, '.', '') }}">
            </div>
            <button type="submit" class="btn btn-danger"><i class="fas fa-check ml-1"></i> تأكيد الإغلاق</button>
            <a href="{{ route('pos.terminal', $session->register_id) }}" class="btn btn-secondary mr-2">رجوع</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
