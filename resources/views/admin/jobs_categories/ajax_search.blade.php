
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
              <thead>
                <th scope="col">{{ __('admin.job_code') }}</th>
                <th scope="col">{{ __('admin.job_name') }}</th>
                <th scope="col">{{ __('admin.created_by') }}</th>
                <th scope="col">{{ __('admin.created_at') }}</th>
                <th scope="col">{{ __('admin.updated_by') }}</th>
                <th scope="col">{{ __('admin.updated_at') }}</th>
                <th scope="col">{{ __('admin.action') }}</th>
              </thead>
              <tbody>
                @foreach ($data as $info)
                  <tr>
                    <td> {{ $info->id }}</td>
                    <td> {{ $info->job_name }}</td>
                    <td> {{ $info->addedby?->name ?? __('admin.unknown') }}</td>
                    <td> {{ $info->created_at }}</td>
                    <td>
                      @if ($info->updated_by>0)
                      {{ $info->updatedby?->name ?? __('admin.unknown') }}
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                      {{ $info->updated_at }}
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('jobs_categories.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                      <a href="{{ route('jobs_categories.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">{{ __('admin.delete') }}</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
              <br>
              <div class="" id="ajax_pagination_in_search">
                {{ $data->links() }}
              </div>
            @else
            <h1>{{ __('admin.no_data') }}</h1>
            @endif
