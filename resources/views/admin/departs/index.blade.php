@extends('admin.layouts.admin')
@section('title')
الادرات
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('departs.index') }}"> الادرات</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الادارات
              <a class="btn btn-success" href="{{ route('departs.create') }}">اضافة جديد</a>
            </h3>
        </div>
        <div class="card-body">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
                <thead>
                  <th scope="col">كود الادارة</th>
                  <th scope="col">اسم الادارة</th>
                  <th scope="col">الهاتف</th>
                  <th scope="col">الايميل</th>
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
                      <td> {{ $info->dep_name }}</td>
                      <td> {{ $info->phone }}</td>
                      <td> {{ $info->email }}</td>
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
                        <a href="{{ route('departs.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                        <a href="{{ route('departs.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
                      </td>
                    </tr>  
                  @endforeach
                </tbody>
              </table>
              <br>
              {{ $data->links('pagination::bootstrap-5') }}
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif
        </div>
    </div>
</div>
   
@endsection
