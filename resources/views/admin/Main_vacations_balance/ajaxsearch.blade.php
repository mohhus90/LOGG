
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
                <th scope="col">الصورة</th>
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
                      <a href="{{ route('employees.edit',$info->id) }}" class="btn btn-sm btn-success">تعديل</a>
                      <a href="{{ route('employees.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">حذف</a>
                    </td>
                  </tr>  
                @endforeach
              </tbody>
            </table>
              <br>
              <div id="ajax_pagination_in_search">
                  {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
              </div>
              {{-- <div id="ajax_pagination_in_search">
                  {{ $data->links('pagination::bootstrap-5') }}
              </div> --}}
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif

        