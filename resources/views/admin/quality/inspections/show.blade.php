@extends('admin.layouts.quality')
@section('title') فحص {{ $inspection->inspection_number }} @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_inspections.index') }}">فحوصات الجودة</a> @endsection
@section('startpage') {{ $inspection->inspection_number }} @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search ml-2"></i> فحص {{ $inspection->inspection_number }}
                <span class="badge badge-{{ $inspection->result_color }} mr-2">{{ $inspection->result_label }}</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>القالب:</strong> {{ $inspection->checklist->name ?? '-' }}</div>
                <div class="col-md-4"><strong>المصدر:</strong> {{ $inspection->source_type_label }}</div>
                <div class="col-md-4"><strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($inspection->date)->format('Y-m-d') }}</div>
                <div class="col-md-4 mt-2"><strong>الفاحص:</strong> {{ $inspection->inspector->name ?? '-' }}</div>
            </div>
            @if($inspection->notes)<p class="mt-2"><strong>ملاحظات:</strong> {{ $inspection->notes }}</p>@endif

            <table class="table table-bordered mt-3">
                <thead class="thead-dark"><tr><th>البند</th><th>النتيجة</th><th>ملاحظات</th></tr></thead>
                <tbody>
                    @foreach($inspection->items as $item)
                    <tr>
                        <td>{{ $item->checklistItem->criterion ?? '-' }}</td>
                        <td>
                            @if($item->result === 'pass')<span class="badge badge-success">ناجح</span>
                            @elseif($item->result === 'fail')<span class="badge badge-danger">فشل</span>
                            @else<span class="badge badge-secondary">غير منطبق</span>@endif
                        </td>
                        <td>{{ $item->notes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
