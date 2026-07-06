@extends('admin.layouts.accounting')
@section('title') الفترات المحاسبية @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_periods.index') }}">الفترات المحاسبية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-check ml-2"></i> الفترات المحاسبية</h3>
            <div class="card-tools">
                <form action="{{ route('accounting_periods.generate') }}" method="POST" class="form-inline">
                    @csrf
                    <input type="number" name="fiscal_year" class="form-control form-control-sm ml-2" value="{{ date('Y') }}" style="width:100px" required>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إنشاء فترات سنة مالية</button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>السنة المالية</th><th>الشهر</th><th>من</th><th>إلى</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $period)
                    <tr>
                        <td>{{ $period->fiscal_year }}</td>
                        <td>{{ $period->period_month }}</td>
                        <td>{{ \Carbon\Carbon::parse($period->start_date)->format('Y-m-d') }}</td>
                        <td>{{ \Carbon\Carbon::parse($period->end_date)->format('Y-m-d') }}</td>
                        <td>
                            @if($period->is_closed)<span class="badge badge-danger">مغلقة</span>
                            @else<span class="badge badge-success">مفتوحة</span>@endif
                        </td>
                        <td>
                            @if(!$period->is_closed)
                                <form action="{{ route('accounting_periods.close', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('إغلاق هذه الفترة؟ لن يمكن الترحيل بعد ذلك.')">
                                    @csrf<button class="btn btn-xs btn-warning"><i class="fas fa-lock"></i> إغلاق</button>
                                </form>
                            @else
                                <form action="{{ route('accounting_periods.reopen', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('إعادة فتح هذه الفترة؟')">
                                    @csrf<button class="btn btn-xs btn-outline-secondary"><i class="fas fa-lock-open"></i> إعادة فتح</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد فترات محاسبية - أنشئ فترات السنة المالية من الأعلى</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
