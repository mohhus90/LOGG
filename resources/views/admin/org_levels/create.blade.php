@extends('admin.layouts.admin')
@section('title') {{ __('admin.org_title') }} @endsection
@section('start') {{ __('admin.org_title') }} @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">{{ __('admin.org_title') }}</a>
@endsection
@section('startpage') {{ __('admin.add') }} @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.org_add_form') }}</h3>
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

            <form action="{{ route('org_levels.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('admin.org_name_ar') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('admin.org_name_en_field') }}</label>
                        <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_hierarchy_order') }} <span class="text-danger">*</span></label>
                        <input type="number" name="level_order" class="form-control" min="1" value="{{ old('level_order', 1) }}" required>
                        <small class="text-muted">{{ __('admin.org_hierarchy_hint') }}</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_parent_label') }}</label>
                        <select name="parent_id" class="form-select">
                            <option value="">{{ __('admin.org_no_parent') }}</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $p->level_order - 1) }}{{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('admin.org_level_type') }} <span class="text-danger">*</span></label>
                        <select name="level_type" class="form-select" required>
                            <option value="top_management"    {{ old('level_type')=='top_management'    ? 'selected' : '' }}>{{ __('admin.org_type_top') }}</option>
                            <option value="middle_management" {{ old('level_type')=='middle_management' ? 'selected' : '' }}>{{ __('admin.org_type_middle') }}</option>
                            <option value="supervisor"        {{ old('level_type')=='supervisor'        ? 'selected' : '' }}>{{ __('admin.org_type_supervisor') }}</option>
                            <option value="sales"             {{ old('level_type')=='sales'             ? 'selected' : '' }}>{{ __('admin.org_type_sales') }}</option>
                            <option value="operational"       {{ old('level_type')=='operational'       ? 'selected' : '' }}>{{ __('admin.org_type_operational') }}</option>
                            <option value="support"           {{ old('level_type')=='support'           ? 'selected' : '' }}>{{ __('admin.org_type_support') }}</option>
                            <option value="other"             {{ old('level_type')=='other'             ? 'selected' : '' }}>{{ __('admin.org_type_other') }}</option>
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
                                                {{ old('is_management') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_management">{{ __('admin.org_is_management') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_sales_role" id="is_sales_role"
                                                {{ old('is_sales_role') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_sales_role">{{ __('admin.org_is_sales') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_seller_commission"
                                                id="receives_seller_commission"
                                                {{ old('receives_seller_commission') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="receives_seller_commission">
                                                <span class="text-success fw-bold">{{ __('admin.org_earns_vendor_comm') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="receives_manager_commission"
                                                id="receives_manager_commission"
                                                {{ old('receives_manager_commission') ? 'checked' : '' }}>
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
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> {{ __('admin.org_save_level') }}
                        </button>
                        <a href="{{ route('org_levels.index') }}" class="btn btn-secondary ms-2">{{ __('admin.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
