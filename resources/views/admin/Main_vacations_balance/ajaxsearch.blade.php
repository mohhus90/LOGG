
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
              <div id="ajax_pagination_in_search">
                  {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
              </div>
              {{-- <div id="ajax_pagination_in_search">
                  {{ $data->links('pagination::bootstrap-5') }}
              </div> --}}
            @else
            <h1>لا توجد بيانات للعرض</h1>
            @endif

        