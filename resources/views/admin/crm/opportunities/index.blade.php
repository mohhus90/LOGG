@extends('admin.layouts.crm')
@section('title') الفرص البيعية @endsection
@section('start') إدارة علاقات العملاء @endsection
@section('home') <a href="{{ route('crm_opportunities.index') }}">الفرص البيعية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullseye ml-2"></i> الفرص البيعية</h3>
            <div class="card-tools">
                <a href="{{ route('crm_opportunities.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> فرصة جديدة</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($stages as $stageKey => [$stageLabel, $color])
                <div class="col-md-2">
                    <h6 class="text-{{ $color }}">{{ $stageLabel }} ({{ $board[$stageKey]->count() }})</h6>
                    <div class="kanban-col">
                        @forelse($board[$stageKey] as $opp)
                        <div class="kanban-card">
                            <a href="{{ route('crm_opportunities.show', $opp->id) }}"><strong>{{ $opp->title }}</strong></a>
                            <div class="small text-muted">{{ $opp->lead->name ?? $opp->customer->name ?? '-' }}</div>
                            <div class="small">{{ number_format($opp->value, 2) }}</div>
                        </div>
                        @empty
                        <div class="text-muted small text-center py-3">لا يوجد</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
