@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_gen_weekly') }} @endsection
@section('start') {{ __('admin.hr_management') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') {{ __('admin.att_gen_weekly') }} @endsection

@section('content')
<div class="col-md-6 mx-auto">
    <div class="card" style="border-color:#6f42c1">
        <div class="card-header" style="background:#6f42c1;color:#fff">
            <h3 class="card-title">
                <i class="fas fa-calendar-week ml-2"></i>
                {{ __('admin.att_gen_weekly') }}
            </h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3 mt-3">{!! session('success') !!}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mx-3 mt-3">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('attendance.generate_weekly_leaves') }}">
            @csrf
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle ml-1"></i>
                    {{ __('admin.att_gen_info') }}
                    <ul class="mb-0 mt-2">
                        <li>{{ __('admin.att_gen_skip_existing') }}</li>
                        <li>{{ __('admin.att_gen_punch_overrides') }}</li>
                        <li>{{ __('admin.att_gen_set_day_hint') }}</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>{{ __('admin.att_from_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="from_date" class="form-control"
                            value="{{ old('from_date', now()->startOfMonth()->format('Y-m-d')) }}" required>
                        @error('from_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>{{ __('admin.att_to_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="to_date" class="form-control"
                            value="{{ old('to_date', now()->endOfMonth()->format('Y-m-d')) }}" required>
                        @error('to_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn" style="background:#6f42c1;color:#fff">
                    <i class="fas fa-magic ml-1"></i> {{ __('admin.att_gen_submit') }}
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-right ml-1"></i> {{ __('admin.back') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
