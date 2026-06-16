@extends('admin.layouts.admin')
@section('title') {{ __('admin.org_title') }} @endsection
@section('start') {{ __('admin.settings_menu') }} @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">{{ __('admin.org_title') }}</a>
@endsection
@section('startpage') {{ __('admin.view') }} @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ __('admin.org_subtitle') }}</h3>
            <div>
                <a href="{{ route('org_levels.templates') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-layer-group"></i> {{ __('admin.org_load_template') }}
                </a>
                <a href="{{ route('org_levels.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> {{ __('admin.org_add_level') }}
                </a>
            </div>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($levels->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">{{ __('admin.org_no_structure') }}</h4>
                    <p class="text-muted">{{ __('admin.org_no_structure_hint') }}</p>
                    <a href="{{ route('org_levels.templates') }}" class="btn btn-warning me-2">
                        <i class="fas fa-layer-group"></i> {{ __('admin.org_choose_template') }}
                    </a>
                    <a href="{{ route('org_levels.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> {{ __('admin.org_custom_structure') }}
                    </a>
                </div>
            @else
                <div class="org-tree mb-4">
                    @include('admin.org_levels._tree_node', ['nodes' => $tree, 'depth' => 0])
                </div>

                <hr>
                <h5 class="mb-3">{{ __('admin.org_level_table') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>{{ __('admin.org_level_name') }}</th>
                                <th>{{ __('admin.org_level_en_name') }}</th>
                                <th>{{ __('admin.org_rank') }}</th>
                                <th>{{ __('admin.org_parent') }}</th>
                                <th>{{ __('admin.org_type') }}</th>
                                <th>{{ __('admin.org_vendor_comm') }}</th>
                                <th>{{ __('admin.org_manager_comm') }}</th>
                                <th>{{ __('admin.org_linked_jobs') }}</th>
                                <th>{{ __('admin.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($levels->sortBy('level_order') as $level)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $level->name }}</strong></td>
                                <td>{{ $level->name_en ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $level->level_order }}</span></td>
                                <td>{{ $level->parent?->name ?? '<span class="text-muted">—</span>' }}</td>
                                <td>{!! $level->level_type_badge !!}</td>
                                <td>
                                    @if($level->receives_seller_commission)
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-light text-dark"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if($level->receives_manager_commission)
                                        <span class="badge bg-primary"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-light text-dark"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $level->jobs()->count() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('org_levels.edit', $level->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('org_levels.delete', $level->id) }}"
                                       class="btn btn-sm btn-danger are_you_sure"
                                       onclick="return confirm('{{ __('admin.confirm_delete') }}')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
.org-node {
    border-right: 3px solid #dee2e6;
    margin-right: 20px;
    padding: 8px 15px;
    position: relative;
}
.org-node::before {
    content: '';
    position: absolute;
    right: -3px;
    top: 50%;
    width: 15px;
    height: 2px;
    background: #dee2e6;
}
.org-node-card {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 15px;
    gap: 10px;
    transition: box-shadow 0.2s;
}
.org-node-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.org-tree > .org-node { border-right: none; }
.org-tree > .org-node::before { display: none; }
</style>
@endsection
