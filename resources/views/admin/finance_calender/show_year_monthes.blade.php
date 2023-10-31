
@if(@isset($finance_cln_periods) and !@empty($finance_cln_periods) )
<table class="table table-bordered">
    <thead>
      <th scope="col">الشهر</th>
      <th scope="col">Month</th>
      <th scope="col">تاريخ البداية</th>
      <th scope="col">تاريخ النهاية</th>
      <th scope="col">الاضافة بواسطة</th>
      <th scope="col">التحديث بواسطة</th>
      <th scope="col">الاضافة بتاريخ</th>
      <th scope="col">التحديث بتاريخ</th>
      <th scope="col">تعديل</th>
      
    </thead>
    <tbody>
      @foreach ($finance_cln_periods as $info)
      
        
        <tr>
          <td> {{ $info->Month->monthe_name }}</td>
          <td> {{ $info->Month->monthe_name_en }}</td>
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