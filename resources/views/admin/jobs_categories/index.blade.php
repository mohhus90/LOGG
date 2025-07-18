@extends('admin.layouts.admin')
@section('title')
الوظائف
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('jobs_categories.index') }}"> الوظائف</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الوظائف
              <a class="btn btn-success" href="{{ route('jobs_categories.create') }}">اضافة جديد</a>
            </h3>
        </div>
       

        <div class="form-group text-center " >
          <div class="form-group form-inline" style="padding: 5px">       
            <label for="job_name" class="col-sm-2 "> اسم الوظيفة</label>
            <div class=" ">
              <input type="text" class="col-sm-10 form-control" name="job_name_search" value="" id="job_name_search">
            </div>
        </div> 
        <div class="card-body" id="ajax_res_search_div">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
              <thead>
                <th scope="col">كود الوظيفة</th>
                <th scope="col">اسم الوظيفة</th>
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
                    <td> {{ $info->job_name }}</td>
                    <td> {{ $info->addedby->name }}</td>
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
                      <a href="{{ route('jobs_categories.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      <a href="{{ route('jobs_categories.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
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
@section('script')
  <script>
    $(document).ready(function () {
        // Initial AJAX request on page load
        ajax_search();

        $(document).on('change', '#job_name_search', function (e) {
          e.preventDefault();
            ajax_search();
        });
        $(document).on('click', '#ajax_pagination_in_search a', function (e) {
            e.preventDefault();
            var job_name_search = $("#job_name_search").val();
            var linkurl = $(this).attr("href");

            // Extract the page number from the link
            var pageNumber = linkurl.split('page=')[1];
            jQuery.ajax({
                url: linkurl,
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', job_name_search: job_name_search },
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
            event.preventDefault();
            ajax_search();
            
        };

        function ajax_search() {
          
            var job_name_search = $("#job_name_search").val();
            jQuery.ajax({
                url: '{{ route('jobs_categories.ajaxsearch') }}',
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', job_name_search: job_name_search },
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

