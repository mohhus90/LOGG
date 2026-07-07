@extends('admin.layouts.sales')
@section('title') جلسات الكاشير @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos_sessions.index') }}">جلسات الكاشير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-receipt ml-2"></i> سجل جلسات الكاشير</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th><th>الكاشير</th><th>فُتحت بواسطة</th><th>وقت الفتح</th><th>وقت الإغلاق</th>
                        <th>الافتتاحية</th><th>المتوقع</th><th>الفعلي</th><th>الفرق</th><th>الحالة</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->register->name ?? '-' }}</td>
                        <td>{{ $s->openedBy->name ?? '-' }}</td>
                        <td>{{ optional($s->opened_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $s->closed_at ? $s->closed_at->format('Y-m-d H:i') : '-' }}</td>
                        <td>{{ number_format($s->opening_amount, 2) }}</td>
                        <td>{{ $s->expected_closing_amount !== null ? number_format($s->expected_closing_amount, 2) : '-' }}</td>
                        <td>{{ $s->counted_closing_amount !== null ? number_format($s->counted_closing_amount, 2) : '-' }}</td>
                        <td>
                            @if($s->difference !== null)
                                <span class="badge badge-{{ $s->difference == 0 ? 'success' : ($s->difference > 0 ? 'info' : 'danger') }}">
                                    {{ number_format($s->difference, 2) }}
                                </span>
                            @else - @endif
                        </td>
                        <td>
                            @if($s->status === 'open')<span class="badge badge-success">مفتوحة</span>
                            @else<span class="badge badge-secondary">مغلقة</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('pos_sessions.show', $s->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            @if($s->status === 'open')
                                <a href="{{ route('pos_sessions.close_form', $s->id) }}" class="btn btn-xs btn-danger"><i class="fas fa-door-closed"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">لا توجد جلسات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $sessions->links() }}</div>
    </div>
</div>
@endsection
