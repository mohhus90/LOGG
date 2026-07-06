@extends('admin.layouts.manufacturing')
@section('title') قائمة مواد {{ $bom->item->name ?? '' }} @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('bill_of_materials.index') }}">قوائم المواد</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-9">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sitemap ml-2"></i> {{ $bom->item->name ?? '-' }} - إصدار {{ $bom->version }}</h3>
        </div>
        <div class="card-body">
            <p><strong>كمية الإنتاج لكل دفعة:</strong> {{ number_format($bom->output_quantity, 4) }}</p>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr><th>المكوّن</th><th>الكمية لكل دفعة</th><th>الوحدة</th><th>نسبة الهالك</th><th>ملاحظات</th></tr>
                </thead>
                <tbody>
                    @foreach($bom->lines as $line)
                    <tr>
                        <td>{{ $line->componentItem->name ?? '-' }}</td>
                        <td>{{ number_format($line->quantity, 4) }}</td>
                        <td>{{ $line->unit->name ?? '-' }}</td>
                        <td>{{ $line->scrap_percent }}%</td>
                        <td>{{ $line->notes }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
