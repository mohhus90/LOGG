@extends('admin.layouts.admin')
@section('title')
السنوات المالية
@endsection
@section('start')
السنوات المالية
@endsection
@section('home')
<a href="{{ route('finance_calender.index') }}"> السنوات المالية</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات السنوات المالية
              <a class="btn btn-success" href="{{ route('finance_calender.create') }}">اضافة جديد</a>
            </h3>
        </div>
        <div class="card-body">
          
          @if(@isset($data) and !@empty($data) )
          <table class="table table-bordered">
              <thead>
                <th scope="col">السنة المالية</th>
                {{-- <th scope="col">وصف السنة</th> --}}
                <th scope="col">تاريخ البداية</th>
                <th scope="col">تاريخ النهاية</th>
                <th scope="col">الاضافة بواسطة</th>
                <th scope="col">التحديث بواسطة</th>
                <th scope="col">الاضافة بتاريخ</th>
                <th scope="col">التحديث بتاريخ</th>
                <th scope="col">تعديل</th>
                
              </thead>
              <tbody>
                @foreach ($data as $info)
                
                  
                  <tr>
                    <td> {{ $info->finance_yr }}</td>
                    {{-- <td> {{ $info->finance_yr_desc }}</td> --}}
                    <td> {{ $info->start_date }}</td>
                    <td> {{ $info->end_date }}</td>
                    <td> {{ $info->added->name }}</td>
                    <td>
                      @if ($info->updated_by>0)
                      {{ $info->updatedby->name }}
                      @else
                        لا يوجد
                      @endif
                    </td>
                    <td> {{ $info->created_at }}</td>
                    
                    <td> 
                      @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                      {{ $info->updated_at }}
                      @else
                        لا يوجد
                      @endif
                    </td>
                    <td>
                      @if ($info->is_open==0)
                      <a href="{{ route('finance_calender.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      <a href="{{ route('finance_calender.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
                      <button class="btn btn-sm btn-info show_year_monthes" data-id="{{ $info->id }}" type="submit">عرض الشهور</button>
                      @else
                      سنة مالية مغلقة
                      <a href="{{ route('finance_calender.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      @endif
                    </td>                   
                  </tr>
                  
                  
                @endforeach
              </tbody>
            </table>
            <br>
              {{ $data->links('pagination::bootstrap-5') }}
          @else
          <h2>لا توجد بيانات للعرض</h2>
          @endif
        </div>
    </div>
</div>
 <!-- /.modal -->

 <div class="modal fade " id="show_year_monthesModal">
  <div class="modal-dialog modal-lg ">
    <div class="modal-content bg-info">
      <div class="modal-header">
        <h4 class="modal-title">عرض الشهور للسنة المالية</h4>
        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"> --}}
          {{-- <span aria-hidden="true">&times;</span></button> --}}
      </div>
      <div class="modal-body" id="show_year_monthesModalbody">
      </div>
      <div class="modal-footer justify-content-between">
        {{-- <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-outline-light">Save changes</button> --}}
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

@endsection
@section('script')
  <script>
    $(document).ready(function(){
      $(document).on('click','.show_year_monthes',function(){
        var id=$(this).data('id');
        jQuery.ajax({
          url:'{{ route('finance_calender.show_year_monthes') }}',
          type:'post',
          'datatype':'html',
          cache:false,
          data:{"_token":"{{ csrf_token() }}",'id':id},
          success:function(data){
            $("#show_year_monthesModalbody").html(data);
            $("#show_year_monthesModal").modal('show');

          },
          error:function(){
           
          }

        });

      });
    });
  </script>
@endsection