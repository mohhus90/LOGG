
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
              <thead>
                <th scope="col">كود الوظيفة</th>
                <th scope="col">اسم الموظف</th>
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
                    <td> {{ $info->employee_name }}</td>
                    <td> {{ $info->added_by }}</td>
                    <td> {{ $info->created_at }}</td>
                    <td>
                      @if ($info->updated_by>0)
                      {{ $info->updated_by }}
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
                      <a href="{{ route('employees.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      <a href="{{ route('employees.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
                    </td>
                  </tr>  
                @endforeach
              </tbody>
            </table>
              <br>
              <div class="" id="ajax_pagination_in_search">
                {{ $data->links('pagination::bootstrap-5') }}
              </div>
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif
        