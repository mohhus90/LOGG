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
                      <a href="{{ route('finance_calender.edit',$info->id) }}" class="btn btn-success">تعديل</a>
                      <a href="{{ route('finance_calender.destroy',$info->id) }}" class="btn btn-danger">حذف</a>

                      @else
                        سنة مالية مغلقة
                      @endif
                    </td>                   
                  </tr>
                  
                  
                @endforeach
              </tbody>
            </table>
          @else
          <h2>لا توجد بيانات للعرض</h2>
          @endif
        </div>
    </div>
</div>
   
@endsection
