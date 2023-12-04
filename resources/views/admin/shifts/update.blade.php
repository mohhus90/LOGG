@extends('admin.layouts.admin')
@section('title')
الشيفتات
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('shifts.index') }}">الشيفتات</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">اضافة شيفت جديد</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('shifts.update',$data['id']) }}">
                @csrf
                
                <div class="form-group form-inline">
                  
                  <label for="type" class="col-sm-2 col-form-label text-center"> نوع الشيفت</label>
                  <select type="text" class="col-sm-5 form-select" aria-label="Disabled select example" name="type" id="type" >
                    <option selected value="" > اختر النوع</option>
                    <option @if ($data['type']==1)selected @endif value="1" > صباحى</option>
                    <option @if ($data['type']==2)selected @endif  value="2" > مسائى</option>
                  </select>
                </div>
                @error('type')
                <div class="text-danger text-center">{{ $message }}</div>
                @enderror
                
                
                <!-- Add the oninput events to from_time and to_time inputs -->
                <div class="form-group row">
                  <label for="from_time" class="col-sm-2 col-form-label text-center">بداية الشيفت</label>
                  <div class="col-sm-5">
                      <input type="time" class="form-control" name="from_time" id="from_time" value="{{ old('from_time',$data['from_time']) }}" oninput="calculateTotalHour()">
                  </div>
                  @error('from_time')
                  <span class="text-danger text-center">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-group row">
                  <label for="to_time" class="col-sm-2 col-form-label text-center">نهاية الشيفت</label>
                  <div class="col-sm-5">
                      <input type="time" class="form-control" name="to_time" id="to_time" value="{{ old('to_time',$data['to_time']) }}" oninput="calculateTotalHour()">
                  </div>
                  @error('to_time')
                  <span class="text-danger text-center ">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Updated total_hour input with readonly attribute -->
                <div class="form-group row">
                  <label for="total_hour" class="col-sm-2 col-form-label text-center">عدد ساعات الشيفت</label>
                  <div class="col-sm-5">
                      <input type="text" class="form-control" name="total_hour" id="total_hour" readonly value="{{ $data['total_hour'] }}">
                  </div>
                </div>

                <div class="text-center">
                  <button type="submit" class="text-center btn btn-primary btn-lg col-2">اضافة</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('shifts.index') }}">الغاء</a>
                </div>
              
              </form>
        </div>
    </div>
</div>
   
@endsection

            <!-- Add the calculateTotalHour JavaScript function -->
            <script>
              function calculateTotalHour() {
                  var fromTime = document.getElementById('from_time').value;
                  var toTime = document.getElementById('to_time').value;
          
                  // Parse the time strings to Date objects
                  var fromDate = new Date('1970-01-01T' + fromTime + 'Z');
                  var toDate = new Date('1970-01-01T' + toTime + 'Z');
          
                  // Calculate the time difference in milliseconds
                  var timeDifference = toDate - fromDate;
          
                  // If the timeDifference is negative, it means the toTime is on the next day
                  if (timeDifference < 0) {
                      toDate = new Date('1970-01-02T' + toTime + 'Z');
                      timeDifference = toDate - fromDate;
                  }
          
                  // Convert the time difference to hours and minutes
                  var hours = Math.floor(timeDifference / 3600000);
                  var minutes = Math.round((timeDifference % 3600000) / 60000);
          
                  // Update the total_hour input
                  var total_hour = document.getElementById('total_hour').value =(hours + (minutes/60)) ;
                  var total_hour_fixed = total_hour.toFixed(2);
              }
          </script>
          