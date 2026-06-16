@extends('admin.layouts.admin')
@section('title')
{{ __('admin.jobs_title') }}
@endsection
@section('start')
    {{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('jobs_categories.index') }}">{{ __('admin.jobs_title') }}</a>
@endsection
@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.edit_job') }}</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('jobs_categories.update', $data['id']) }}">
                @csrf

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">{{ __('admin.job_name') }} <span class="text-danger">*</span></label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="job_name" id="job_name"
                        value="{{ old('job_name', $data['job_name']) }}">
                    @error('job_name')<div class="text-danger">{{ $message }}</div>@enderror
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">{{ __('admin.org_level_label') }}</label>
                  <div class="col-sm-5">
                    <select name="org_level_id" class="form-select">
                      <option value="">{{ __('admin.org_select_level') }}</option>
                      @foreach($orgLevels ?? [] as $level)
                        <option value="{{ $level->id }}"
                            {{ old('org_level_id', $data->org_level_id) == $level->id ? 'selected' : '' }}>
                          {{ str_repeat('— ', $level->level_order - 1) }}{{ $level->name }}
                          @if($level->receives_seller_commission) [{{ __('admin.org_vendor_comm') }}] @endif
                          @if($level->receives_manager_commission) [{{ __('admin.org_manager_comm') }}] @endif
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn btn-primary btn-lg col-2">{{ __('admin.edit') }}</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('jobs_categories.index') }}">{{ __('admin.cancel') }}</a>
                </div>
              </form>
        </div>
    </div>
</div>
@endsection
