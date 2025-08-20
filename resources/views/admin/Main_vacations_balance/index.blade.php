@extends('admin.layouts.admin')
@section('title')
بيانات السنوى
@endsection
@section('start')
قائمة السنوى
@endsection
@section('home')
<a href="{{ route('Main_vacations_balance.index') }}"> رصيد السنوى</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات رصيد الموظفين السنوى
              
            </h3>
        </div>
       

        {{-- <div class="form-group form-inline" style="padding: 5px">
          <label for="employee_name_A" class="col-sm-2">اسم الموظف</label>
          <div class="">
              <input type="text" class="col-sm-10 form-control" name="employee_name_A_search" value="" id="employee_name_A_search">
          </div>
        </div> --}}
        <div class="row "style="padding: 5px" > {{-- Start a Bootstrap row for grouping inputs --}}
          <div class="col-md-4" > {{-- Each input will take 4 columns (12/3 = 4) --}}
              <label for="employee_id">كود الموظف </label>
              <input type="text" class=" col-sm-10 form-control" name="employee_id_search" id="employee_id_search" value="">
          </div>
            <div class="col-md-4" >
            <label for="employee_name_A">اسم الموظف</label>
            <div class="">
                <input type="text" class="col-sm-10 form-control" name="employee_name_A_search" value="" id="employee_name_A_search">
            </div>
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
                <th scope="col">الصورة</th>
                <th scope="col">الرصيد</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data as $info)
                  <tr>
                    <td> {{ $info->employee_id }}</td>
                    <td> {{ $info->employee_name_A }}</td>
                    <td> {{ $info->employee_name_E }}</td>
                    <td> 
                      @if(!@empty($info->emp_photo) )
                      <img src="{{ asset('assets/admin/uploads/' . $info->emp_photo) }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="صورة الموظف">
              
                      @else
                        @if ($info->emp_gender==2)
                         <img src="{{ asset('assets/admin/uploads/woman.png')}}" style="width: 80px; height: 80px;" class="rounded-circle"  alt="صورة الموظف">
                        @else
                          <img src="{{ asset('assets/admin/uploads/man.png')}}" style="width: 80px; height: 80px;" class="rounded-circle"  alt="صورة الموظف">
                        @endif
                      @endif
                    </td>
                    <td>
                         <a href="{{ route('Main_vacations_balance.show',$info->id) }}" class="btn btn-sm btn-info">عرض</a>
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
$(document).ready(function(){

    // البحث
    $(document).on('input', '#employee_name_A_search', function(){
        ajax_search(1);
    });
    $(document).on('input', '#employee_id_search', function(){
        ajax_search(1);
    });

    // الضغط على الصفحات
    $(document).on('click', '#ajax_pagination_in_search a', function(e){
        e.preventDefault();
        var pageNumber = $(this).attr('href').split('page=')[1];
        ajax_search(pageNumber);
    });

    function ajax_search(page){
        var employee_name_A_search = $("#employee_name_A_search").val();
        var employee_id_search = $("#employee_id_search").val();
        var interInput = employee_id_search  + '-' + employee_name_A_search
        $.ajax({
            url: '{{ route("Main_vacations_balance.index") }}?page=' + page,
            type: 'get',
            dataType: 'html',
            cache: false,
            data: {
                employee_name_A_search: employee_name_A_search,
                employee_id_search: employee_id_search
            },
            success: function(data){
                $("#ajax_res_search_div").html(data);
                window.history.pushState({}, '', '{{ route("Main_vacations_balance.index") }}?page=' + page + '&employee=' + interInput);
            },
            error: function(xhr){
                var errorMessage = "حدث خطأ غير متوقع";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                alert(errorMessage);
            }
        });
    }

});

</script>
@endsection

