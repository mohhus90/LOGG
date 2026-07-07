@extends('admin.layouts.admin')
@section('title') شرائح ضريبة كسب العمل @endsection
@section('start') شرائح ضريبة كسب العمل @endsection
@section('home') <a href="{{ route('income_tax_brackets.index') }}">شرائح ضريبة كسب العمل</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

@if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
  </div>
@endif

<div class="card mb-3">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-shield-alt ml-2"></i>الإعفاء الضريبي الشهري</h3></div>
  <form method="POST" action="{{ route('income_tax_brackets.update_exemption') }}">
    @csrf
    <div class="card-body">
      <div class="row">
        <div class="col-md-4 form-group">
          <label>مبلغ الإعفاء الشهري (يُطرح من الوعاء قبل تطبيق الشرائح)</label>
          <div class="input-group">
            <input type="number" step="0.01" min="0" class="form-control" name="income_tax_exemption_monthly"
              value="{{ old('income_tax_exemption_monthly', $setting->income_tax_exemption_monthly ?? 0) }}">
            <div class="input-group-append"><span class="input-group-text">جنيه/شهر</span></div>
          </div>
          <small class="text-muted">مثال: لو الإعفاء السنوي القانوني 40,000 جنيه، أدخل هنا 3,333.33 (÷12)</small>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save ml-1"></i> حفظ</button>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-percentage ml-2"></i>الشرائح الضريبية (شهرية)</h3></div>
  <div class="card-body p-0">
    <table class="table table-bordered table-striped mb-0">
      <thead class="thead-dark"><tr><th>#</th><th>من</th><th>إلى</th><th>النسبة</th><th></th></tr></thead>
      <tbody>
        @forelse($brackets as $b)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ number_format($b->from_amount, 2) }}</td>
          <td>{{ $b->to_amount !== null ? number_format($b->to_amount, 2) : 'بلا حد أعلى' }}</td>
          <td>{{ number_format($b->rate, 2) }}%</td>
          <td>
            <form method="POST" action="{{ route('income_tax_brackets.destroy', $b->id) }}" onsubmit="return confirm('حذف الشريحة؟')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">لا توجد شرائح — لن يُخصم أي ضريبة حتى تُضاف شريحة واحدة على الأقل</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <form method="POST" action="{{ route('income_tax_brackets.store') }}" class="form-inline">
      @csrf
      <label class="ml-2">من</label>
      <input type="number" step="0.01" min="0" name="from_amount" class="form-control form-control-sm mx-2" style="width:120px" required>
      <label class="ml-2">إلى (اتركها فارغة لآخر شريحة)</label>
      <input type="number" step="0.01" min="0" name="to_amount" class="form-control form-control-sm mx-2" style="width:120px">
      <label class="ml-2">النسبة %</label>
      <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control form-control-sm mx-2" style="width:100px" required>
      <button type="submit" class="btn btn-success btn-sm mr-2"><i class="fas fa-plus"></i> إضافة شريحة</button>
    </form>
  </div>
</div>

</div>
@endsection
