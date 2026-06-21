@extends('admin.layouts.admin')
@section('title') {{ __('admin.org_title') }} @endsection
@section('start') {{ __('admin.org_title') }} @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">{{ __('admin.org_title') }}</a>
@endsection
@section('startpage') {{ __('admin.edit') }} @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.org_edit_prefix') }} <strong>{{ $data->name }}</strong></h3>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('org_levels.update', $data->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('admin.org_name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $data->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('admin.org_name_en_field') }}</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $data->name_en) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_hierarchy_order') }} <span class="text-danger">*</span></label>
                        <input type="number" name="level_order" class="form-control" min="1"
                            value="{{ old('level_order', $data->level_order) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_parent_label') }}</label>
                        <select name="parent_id" class="form-select">
                            <option value="">{{ __('admin.org_no_parent') }}</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('parent_id', $data->parent_id) == $p->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $p->level_order - 1) }}{{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_level_type') }} <span class="text-danger">*</span></label>
                        <select name="level_type" class="form-select" required>
                            @foreach([
                                'top_management'    => __('admin.org_type_top'),
                                'middle_management' => __('admin.org_type_middle'),
                                'supervisor'        => __('admin.org_type_supervisor'),
                                'sales'             => __('admin.org_type_sales'),
                                'operational'       => __('admin.org_type_operational'),
                                'support'           => __('admin.org_type_support'),
                                'other'             => __('admin.org_type_other'),
                            ] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('level_type', $data->level_type) == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="card border-secondary">
                            <div class="card-header bg-light"><strong>{{ __('admin.org_properties') }}</strong></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_management" id="is_management"
                                                {{ old('is_management', $data->is_management) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_management">{{ __('admin.org_is_management') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_sales_role" id="is_sales_role"
                                                {{ old('is_sales_role', $data->is_sales_role) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_sales_role">{{ __('admin.org_is_sales') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_seller_commission"
                                                id="receives_seller_commission"
                                                {{ old('receives_seller_commission', $data->receives_seller_commission) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="receives_seller_commission">
                                                <span class="text-success fw-bold">{{ __('admin.org_earns_vendor_comm') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_manager_commission"
                                                id="receives_manager_commission"
                                                {{ old('receives_manager_commission', $data->receives_manager_commission) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="receives_manager_commission">
                                                <span class="text-primary fw-bold">{{ __('admin.org_earns_mgr_comm') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">{{ __('admin.org_optional_desc') }}</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $data->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('admin.org_save_changes') }}
                        </button>
                        <a href="{{ route('org_levels.index') }}" class="btn btn-secondary ms-2">{{ __('admin.cancel') }}</a>
                        <a href="{{ route('org_levels.delete', $data->id) }}"
                           class="btn btn-danger ms-2"
                           onclick="return confirm('{{ __('admin.confirm_delete') }}')">
                            <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
