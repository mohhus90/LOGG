@extends('admin.layouts.admin')
@section('title')
الشيفتات
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('shifts.index') }}"> الشيفتات</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الشيفتات
              <a class="btn btn-success" href="{{ route('shifts.create') }}">اضافة جديد</a>
            </h3>
        </div>
       

        <div class="form-group form-inline" >
          <div class="form-group" style="padding: 5px">       
          <label for="type" class="col-sm-3 col-form-label text-center"> نوع الشيفت</label>
          <select type="text" class="col-sm-8 form-select" aria-label="Disabled select example" name="type_search" id="type_search" >
            <option selected value="all" > بحث بالكل</option>
            <option value="1" > صباحى</option>
            <option value="2" > مسائى</option>
          </select>
        </div> 
        <div class="form-group ">
          <label for="from_time" class="col-sm-1 col-form-label text-center">بداية الشيفت</label>
          <div class="col-sm-2">
              <input type="time" class="form-control" name="hour_from_range" id="hour_from_range" value="" oninput="calculateTotalHour()">
          </div>
          <div class="form-group " >
            <label for="to_time" style="margin-right: 30%" class="col-sm-1 col-form-label text-center">نهاية الشيفت</label>
            <div class="col-sm-2">
                <input type="time" class="form-control" name="hour_to_range" id="hour_to_range" value="" oninput="calculateTotalHour()">
            </div>
          </div>
        </div>
        <div class="card-body" id="ajax_res_search_div">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
                <thead>
                  <th scope="col">كود الشيفت</th>
                  <th scope="col">نوع الشيفت</th>
                  <th scope="col">بداية الشيفت</th>
                  <th scope="col">نهاية الشيفت</th>
                  <th scope="col">عدد ساعات الشيفت</th>
                  <th scope="col">الاضافة بواسطة</th>
                  <th scope="col">تاريخ الاضافة </th>
                  <th scope="col">التحديث بواسطة</th>
                  <th scope="col">تاريخ التحديث</th>
                  <th scope="col">اجراء</th>
                  
                </thead>
                <tbody>
                  @foreach ($data as $info)
                    <tr>
                      <td> {{ $info->id }}</td>
                      <td> @if ( ($info->type) ==1)
                          صباحى
                          @endif
                          @if ( ($info->type) ==2)
                          مسائى
                        @endif
                      </td>
                      <td> {{ $info->from_time }}</td>
                      <td> {{ $info->to_time }}</td>
                      <td> {{ $info->total_hour }}</td>
                      <td> {{ $info->added->name }}</td>
                      <td> {{ $info->created_at }}</td>
                      <td>
                        @if ($info->updated_by>0)
                        {{ $info->updatedby->name }}
                        @else
                          لا يوجد
                        @endif
                      </td>
                      <td> 
                        @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                        {{ $info->updated_at }}
                        @else
                          لا يوجد
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('shifts.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                        <a href="{{ route('shifts.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
                      </td>
                    </tr>  
                  @endforeach
                </tbody>
              </table>
              <br>
              <div class="" id="#ajax_pagination_in_search">
                {{ $data->links('pagination::bootstrap-5') }}
              </div>
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif
        </div>
    </div>
</div>
   
@endsection
{{-- @section('script')
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
            alert("عفوا لقد حدث خطأ")
          }

        });
        $(document).on('click', '#ajax_pagination_in_search a', function (e) {
        e.preventDefault();
        var type_search = $("#type_search").val();
        var hour_from_range = $("#hour_from_range").val();
        var hour_to_range = $("#hour_to_range").val();
        var linkurl = $(this).attr("href");

        // Extract the page number from the link
        var pageNumber = linkurl.split('page=')[1];

        jQuery.ajax({
            url: linkurl,
            type: 'get',
            'datatype': 'html',
            cache: false,
            data: {"_token": '{{ csrf_token() }}', type_search: type_search, hour_from_range: hour_from_range, hour_to_range: hour_to_range},
            success: function (data) {
                $("#ajax_res_search_div").html(data);

                // Update the URL to reflect the current page
                window.history.pushState({}, '', linkurl);
            },
            error: function () {
                alert("عفوا لقد حدث خطأ")
            }
        });
      });     
            }
          })
  </script>
@endsection --}}
@section('script')
  <script>
    $(document).ready(function () {
        // Initial AJAX request on page load
        ajax_search();

        $(document).on('change', '#type_search', function (e) {
            ajax_search();
        });

        $(document).on('input', '#hour_from_range', function (e) {
            ajax_search();
        });

        $(document).on('input', '#hour_to_range', function (e) {
            ajax_search();
        });

        $(document).on('click', '#ajax_pagination_in_search a', function (e) {
            e.preventDefault();
            var type_search = $("#type_search").val();
            var hour_from_range = $("#hour_from_range").val();
            var hour_to_range = $("#hour_to_range").val();
            var linkurl = $(this).attr("href");

            // Extract the page number from the link
            var pageNumber = linkurl.split('page=')[1];

            jQuery.ajax({
                url: linkurl,
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', type_search: type_search, hour_from_range: hour_from_range, hour_to_range: hour_to_range},
                success: function (data) {
                    $("#ajax_res_search_div").html(data);

                    // Update the URL to reflect the current page
                    window.history.pushState({}, '', linkurl);
                },
                error: function (xhr, status, error) {
                var errorMessage = "عفوا لقد حدث خطأ";

                // Check if the response contains a JSON error message
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                alert(errorMessage);
            }
            });
        });

        window.onpopstate = function (event) {
            // Handle popstate event, e.g., make an AJAX request to update content
            ajax_search();
        };

        function ajax_search() {
          // e.preventDefault();
            var type_search = $("#type_search").val();
            var hour_from_range = $("#hour_from_range").val();
            var hour_to_range = $("#hour_to_range").val();
            jQuery.ajax({
                url: '{{ route('shifts.ajaxsearch') }}',
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', type_search: type_search, hour_from_range: hour_from_range, hour_to_range: hour_to_range},
                success: function (data) {
                    $("#ajax_res_search_div").html(data);
                },
                error: function (xhr, status, error) {
                var errorMessage = "عفوا لقد حدث خطأ";

                // Check if the response contains a JSON error message
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                alert(errorMessage);
            }
            });
        }
    });
  </script>
@endsection

