@extends('admin.layouts.admin')
@section('title')
{{ __('admin.shifts_title') }}
@endsection
@section('start')
{{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('shifts.index') }}"> {{ __('admin.shifts_title') }}</a>
@endsection
@section('startpage')
    {{ __('admin.view') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.shifts_data') }}
              <a class="btn btn-success" href="{{ route('shifts.create') }}">{{ __('admin.add_new') }}</a>
            </h3>
        </div>

        <div class="form-group form-inline" >
          <div class="form-group" style="padding: 5px">
          <label for="type" class="col-sm-3 col-form-label text-center">{{ __('admin.shift_type') }}</label>
          <select type="text" class="col-sm-8 form-select" aria-label="Disabled select example" name="type_search" id="type_search" >
            <option selected value="all" >{{ __('admin.search_all') }}</option>
            <option value="1" >{{ __('admin.morning') }}</option>
            <option value="2" >{{ __('admin.evening') }}</option>
          </select>
        </div>
        <div class="form-group ">
          <label for="from_time" class="col-sm-1 col-form-label text-center">{{ __('admin.shift_start') }}</label>
          <div class="col-sm-2">
              <input type="time" class="form-control" name="hour_from_range" id="hour_from_range" value="" oninput="calculateTotalHour()">
          </div>
          <div class="form-group " >
            <label for="to_time" style="margin-right: 30%" class="col-sm-1 col-form-label text-center">{{ __('admin.shift_end') }}</label>
            <div class="col-sm-2">
                <input type="time" class="form-control" name="hour_to_range" id="hour_to_range" value="" oninput="calculateTotalHour()">
            </div>
          </div>
        </div>
        <div class="card-body" id="ajax_res_search_div">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
                <thead>
                  <th scope="col">{{ __('admin.shift_code') }}</th>
                  <th scope="col">{{ __('admin.shift_type') }}</th>
                  <th scope="col">{{ __('admin.shift_start') }}</th>
                  <th scope="col">{{ __('admin.shift_end') }}</th>
                  <th scope="col">{{ __('admin.shift_hours') }}</th>
                  <th scope="col">{{ __('admin.created_by') }}</th>
                  <th scope="col">{{ __('admin.created_at') }}</th>
                  <th scope="col">{{ __('admin.updated_by') }}</th>
                  <th scope="col">{{ __('admin.updated_at') }}</th>
                  <th scope="col">{{ __('admin.action') }}</th>
                </thead>
                <tbody>
                  @foreach ($data as $info)
                    <tr>
                      <td> {{ $info->id }}</td>
                      <td> @if ( ($info->type) ==1)
                          {{ __('admin.morning') }}
                          @endif
                          @if ( ($info->type) ==2)
                          {{ __('admin.evening') }}
                        @endif
                      </td>
                      <td> {{ $info->from_time }}</td>
                      <td> {{ $info->to_time }}</td>
                      <td> {{ $info->total_hour }}</td>
                      <td> {{ $info->added?->name ?? __('admin.unknown') }}</td>
                      <td> {{ $info->created_at }}</td>
                      <td>
                        @if ($info->updated_by>0)
                        {{ $info->updatedby?->name ?? __('admin.unknown') }}
                        @else
                          —
                        @endif
                      </td>
                      <td>
                        @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                        {{ $info->updated_at }}
                        @else
                          —
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('shifts.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                        <a href="{{ route('shifts.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">{{ __('admin.delete') }}</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <br>
              <div class="" id="#ajax_pagination_in_search">
                {{ $data->links() }}
              </div>
            @else
            <h1>{{ __('admin.no_data') }}</h1>
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
  <script>
    $(document).ready(function(){
        $(document).on('change','#type_search',function (e) {
          ajax_search()
        });
        $(document).on('input','#hour_from_range',function (e) {
          ajax_search()
        });
        $(document).on('input','#hour_to_range',function (e) {
          ajax_search()
        });
        $(document).on('click','#ajax_pagination_in_search a',function (e) {
          e.preventDefault();
          var type_search=$("#type_search").val();
          var hour_from_range=$("#hour_from_range").val();
          var hour_to_range=$("#hour_to_range").val();
          var linkurl = $(this).attr("href");
          var pageNumber = linkurl.split('page=')[1];
              jQuery.ajax({
                  url: linkurl,
                  type: 'get',
                  'datatype': 'html',
                  cache: false,
                  data: {"_token": '{{ csrf_token() }}', type_search: type_search, hour_from_range: hour_from_range, hour_to_range: hour_to_range},
                  success: function (data) {
                      $("#ajax_res_search_div").html(data);
                      window.history.pushState({}, '', linkurl);
                  },
                  error: function (xhr, status, error) {
                var errorMessage = "{{ __('admin.error_occurred') }}";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
              });
          });
      function ajax_search(){
        var type_search=$("#type_search").val();
        var hour_from_range=$("#hour_from_range").val();
        var hour_to_range=$("#hour_to_range").val();
        jQuery.ajax({
          url:'{{ route('shifts.ajaxsearch') }}',
          type:'post',
          'datatype':'html',
          cache:false,
          data:{"_token":'{{ csrf_token() }}',type_search:type_search,hour_from_range:hour_from_range,hour_to_range:hour_to_range},
          success: function(data){
            $("#ajax_res_search_div").html(data);
          },
          error: function () {
            alert("{{ __('admin.error_occurred') }}")
          }
        });
      }
    })
  </script>
@endsection
