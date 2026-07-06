@extends('admin.layouts.quality')
@section('title') لوحة الجودة @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_reports.index') }}">لوحة الجودة</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>إجمالي الفحوصات</p></div>
                <div class="icon"><i class="fas fa-search"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['pass'] }}</h3><p>ناجحة</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['fail'] }}</h3><p>مرفوضة</p></div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['pass_rate'] }}%</h3><p>نسبة النجاح</p></div>
                <div class="icon"><i class="fas fa-percentage"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">آخر الفحوصات</h3></div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead><tr><th>رقم الفحص</th><th>القالب</th><th>النتيجة</th></tr></thead>
                <tbody>
                    @forelse($recent as $inspection)
                    <tr>
                        <td><a href="{{ route('quality_inspections.show', $inspection->id) }}">{{ $inspection->inspection_number }}</a></td>
                        <td>{{ $inspection->checklist->name ?? '-' }}</td>
                        <td><span class="badge badge-{{ $inspection->result_color }}">{{ $inspection->result_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">لا توجد فحوصات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
