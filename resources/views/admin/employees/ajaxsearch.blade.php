
            @if(@isset($data) and !@empty($data))
            <table id="example2" class="table table-bordered table-hover">
              <thead>
                <tr>
                <th scope="col">{{ __('admin.emp_code') }}</th>
                <th scope="col">{{ __('admin.emp_name_ar') }}</th>
                <th scope="col">{{ __('admin.emp_name_en') }}</th>
                <th scope="col">{{ __('admin.added_by') }}</th>
                <th scope="col">{{ __('admin.added_at') }}</th>
                <th scope="col">{{ __('admin.updated_by') }}</th>
                <th scope="col">{{ __('admin.updated_at') }}</th>
                <th scope="col">{{ __('admin.emp_photo') }}</th>
                <th scope="col">{{ __('admin.action') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data as $info)
                  <tr>
                    <td>{{ $info->employee_id }}</td>
                    <td>{{ $info->employee_name_A }}</td>
                    <td>{{ $info->employee_name_E }}</td>
                    <td>{{ $info->addedBy->name }}</td>
                    <td>{{ $info->created_at }}</td>
                    <td>
                        @if($info->updated_by > 0)
                            {{ $info->updatedBy->name }}
                        @else
                            {{ __('admin.no_updates') }}
                        @endif
                    </td>
                    <td>
                      @if(@isset($info->updated_at) and !@empty($info->updated_at))
                      {{ $info->updated_at }}
                      @else
                        {{ __('admin.none') }}
                      @endif
                    </td>
                    <td>
                      @if(!@empty($info->emp_photo))
                      <img src="{{ asset('assets/admin/uploads/' . $info->emp_photo) }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                      @else
                        @if($info->emp_gender==2)
                         <img src="{{ asset('assets/admin/uploads/woman.png') }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                        @else
                          <img src="{{ asset('assets/admin/uploads/man.png') }}" style="width: 80px; height: 80px;" class="rounded-circle" alt="{{ __('admin.emp_photo') }}">
                        @endif
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('employees.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                      <a href="{{ route('employees.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">{{ __('admin.delete') }}</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
              <br>
              <div id="ajax_pagination_in_search">
                  {{ $data->appends(request()->query())->links() }}
              </div>
            @else
            <h1>{{ __('admin.no_data') }}</h1>
            @endif
