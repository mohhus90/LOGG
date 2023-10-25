@extends('admin.layouts.admin')
@section('title')
    الضبط العام
@endsection
@section('start')
الضبط العام
@endsection
@section('home')
<a href="{{ route('branches.index') }}"> الفروع</a>

@endsection
@section('startpage')
    عرض
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">بيانات الفروع</h3>
        </div>
        <div class="card-body">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
                <thead>
                  <th scope="col">كود الفرع</th>
                  <th scope="col">الاسم</th>
                  <th scope="col">العنوان</th>
                  <th scope="col">الهاتف</th>
                  <th scope="col">الايمي</th>
                  <th scope="col">حالة التفعيل</th>
                  <th scope="col">الاضافة بواسطة</th>
                  <th scope="col">تاريخ الاضافة </th>
                  <th scope="col">التحديث بواسطة</th>
                  <th scope="col">تاريخ التحديث</th>
                  
                </thead>
                <tbody>
                  @foreach ($data as $info)
                  
                    
                    <tr>
                      <td> {{ $info->id }}</td>
                      <td> {{ $info->branch_name }}</td>
                      <td> {{ $info->address }}</td>
                      <td> {{ $info->phone }}</td>
                      <td> {{ $info->email }}</td>
                      <td> {{ $info->active }}</td>
                      <td> {{ $info->added_by }}</td>
                      <td> {{ $info->created_at }}</td>
                      <td> {{ $info->updated_by }}</td>
                      <td> {{ $info->updated_at }}</td>                   
                    </tr>
                    
                    
                  @endforeach
                </tbody>
              </table>
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif
        </div>
    </div>
</div>
   
@endsection
