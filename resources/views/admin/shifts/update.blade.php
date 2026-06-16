@extends('admin.layouts.admin')
@section('title')
{{ __('admin.shifts_title') }}
@endsection
@section('start')
    {{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('shifts.index') }}">{{ __('admin.shifts_title') }}</a>
@endsection
@section('startpage')
{{ __('admin.edit') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.update_shift') }}</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('shifts.update',$data['id']) }}">
                @csrf
                <div class="form-group row">
                  <label for="from_time" class="col-sm-2 col-form-label text-center">{{ __('admin.shift_start') }}</label>
                  <div class="col-sm-5">
                      <input type="time" class="form-control" name="from_time" id="from_time" value="{{ old('from_time',$data['from_time']) }}" oninput="calculateTotalHour()">
                  </div>
                  @error('from_time')
                  <span class="text-danger text-center">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="to_time" class="col-sm-2 col-form-label text-center">{{ __('admin.shift_end') }}</label>
                  <div class="col-sm-5">
                      <input type="time" class="form-control" name="to_time" id="to_time" value="{{ old('to_time',$data['to_time']) }}" oninput="calculateTotalHour()">
                  </div>
                  @error('to_time')
                  <span class="text-danger text-center ">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group row">
                  <label for="total_hour" class="col-sm-2 col-form-label text-center">{{ __('admin.shift_hours') }}</label>
                  <div class="col-sm-5">
                      <input type="text" class="form-control" name="total_hour" id="total_hour" readonly value="{{ $data['total_hour'] }}">
                  </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">{{ __('admin.edit') }}</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('shifts.index') }}">{{ __('admin.cancel') }}</a>
                </div>
              </form>
        </div>
    </div>
</div>
@endsection

<script>
  function calculateTotalHour() {
      var fromTime = document.getElementById('from_time').value;
      var toTime = document.getElementById('to_time').value;
      var fromDate = new Date('1970-01-01T' + fromTime + 'Z');
      var toDate = new Date('1970-01-01T' + toTime + 'Z');
      var timeDifference = toDate - fromDate;
      if (timeDifference < 0) {
          toDate = new Date('1970-01-02T' + toTime + 'Z');
          timeDifference = toDate - fromDate;
      }
      var hours = Math.floor(timeDifference / 3600000);
      var minutes = Math.round((timeDifference % 3600000) / 60000);
      var total_hour = document.getElementById('total_hour').value = (hours + (minutes/60));
      var total_hour_fixed = total_hour.toFixed(2);
  }
</script>
