@extends('admin.layouts.admin')
@section('title')
الموظفين
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('employees.index') }}"> الموظفين</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الموظفين
              <a class="btn btn-success" href="{{ route('employees.create') }}">اضافة جديد</a>
              <a class="btn btn-success" href="{{ route('employees.uploadexcel') }}">اضافة من اكسيل</a>
              <a class="btn btn-success" href="{{ route('employees.export') }}">تصدير الى اكسيل</a>
            </h3>
        </div>
       

        <div class="form-group form-inline" style="padding: 5px">
          <label for="employee_name_A" class="col-sm-2">اسم الموظف</label>
          <div class="">
              <input type="text" class="col-sm-10 form-control" name="employee_name_A_search" value="" id="employee_name_A_search">
          </div>
      </div>
        <div class="card-body" id="ajax_res_search_div">
            @if(@isset($data) and !@empty($data) )
            <table id="example2" class="table table-bordered table-hover">
              <thead>
                <tr>
                <th scope="col">كود الوظيفة</th>
                <th scope="col">اسم الموظف عربى</th>
                <th scope="col">اسم الموظف انجليزى</th>
                <th scope="col">الاضافة بواسطة</th>
                <th scope="col">تاريخ الاضافة </th>
                <th scope="col">التحديث بواسطة</th>
                <th scope="col">تاريخ التحديث</th>
                <th scope="col">اجراء</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data as $info)
                  <tr>
                    <td> {{ $info->employee_id }}</td>
                    <td> {{ $info->employee_name_A }}</td>
                    <td> {{ $info->employee_name_E }}</td>
                    <td> {{ $info->addedBy->name }}</td>
                    <td> {{ $info->created_at }}</td>
                    
                    <td>
                        @if($info->updated_by > 0)
                            {{ $info->updatedBy->name }}
                        @else
                            لا يوجد تحديث
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
                      <a href="{{ route('employees.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      <a href="{{ route('employees.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
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

    $(document).on('change keyup', '.search-input', function (e) {
        e.preventDefault();
        ajax_search();
    });

    $(document).on('click', '#ajax_pagination_in_search a', function (e) {
        e.preventDefault();
        var linkurl = $(this).attr("href");

        // Extract the page number from the link
        var pageNumber = linkurl.split('page=')[1];
        ajax_search(linkurl);
    });

    window.onpopstate = function (event) {
        // Handle popstate event, e.g., make an AJAX request to update content
        event.preventDefault();
        ajax_search();
    };

    function ajax_search(linkurl = null) {
        var employee_name_A_search = $("#employee_name_A_search").val();
        // Add more variables for additional search inputs if needed

        var data = {"_token": '{{ csrf_token() }}', employee_name_A_search: employee_name_A_search };
        // Add more data for additional search inputs if needed

        // Check if a link URL is provided (pagination)
        if (linkurl !== null) {
            // Extract the query parameters from the link URL
            var urlParams = new URLSearchParams(linkurl.split('?')[1]);
            // Add additional query parameters for additional search inputs if needed
            // Example: data['param_name'] = urlParams.get('param_name');
        }

        jQuery.ajax({
            url: '{{ route('employees.ajaxsearch') }}',
            type: 'post',
            'datatype': 'html',
            cache: false,
            data: data,
            success: function (data) {
                $("#ajax_res_search_div").html(data);

                // Initialize DataTable after search results are updated
                $('#ajax_res_search_div table').DataTable({
                    "paging": false,
                    "lengthChange": false,
                    "searching": false, // Disable searching here to avoid conflicts with custom search inputs
                    "ordering": true,
                    "info": false,
                    "autoWidth": false
                });
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

