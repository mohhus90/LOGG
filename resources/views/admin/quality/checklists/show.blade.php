@extends('admin.layouts.quality')
@section('title') {{ $checklist->name }} @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_checklists.index') }}">قوالب الفحص</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-7">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-list ml-2"></i> {{ $checklist->name }}</h3></div>
        <div class="card-body">
            <p><strong>يُطبَّق على:</strong> {{ $checklist->applies_to_label }}</p>
            <table class="table table-bordered">
                <thead class="thead-dark"><tr><th>#</th><th>البند</th></tr></thead>
                <tbody>
                    @foreach($checklist->items as $item)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $item->criterion }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
