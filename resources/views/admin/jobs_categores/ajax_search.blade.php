
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
              <div class="" id="ajax_pagination_in_search">
                {{ $data->links('pagination::bootstrap-5') }}
              </div>
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif
        