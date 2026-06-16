@extends('admin.layouts.admin')
@section('title')
{{ __('admin.branches_title') }}
@endsection
@section('start')
{{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('branches.index') }}"> {{ __('admin.branches_title') }}</a>
@endsection
@section('startpage')
    {{ __('admin.view') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.branches_data') }}
              <a class="btn btn-success" href="{{ route('branches.create') }}">{{ __('admin.add_new') }}</a>
            </h3>
        </div>
        <div class="card-body">
            @if(@isset($data) and !@empty($data) )
            <table class="table table-bordered">
                <thead>
                  <th scope="col">{{ __('admin.branch_code') }}</th>
                  <th scope="col">{{ __('admin.name') }}</th>
                  <th scope="col">{{ __('admin.address') }}</th>
                  <th scope="col">{{ __('admin.phone') }}</th>
                  <th scope="col">{{ __('admin.email') }}</th>
                  <th scope="col">{{ __('admin.created_by') }}</th>
                  <th scope="col">{{ __('admin.created_at') }}</th>
                  <th scope="col">{{ __('admin.updated_by') }}</th>
                  <th scope="col">{{ __('admin.updated_at') }}</th>
                  <th scope="col">{{ __('admin.status') }}</th>
                  <th scope="col">{{ __('admin.action') }}</th>
                </thead>
                <tbody>
                  @foreach ($data as $info)
                    <tr>
                      <td> {{ $info->id }}</td>
                      <td> {{ $info->branch_name }}</td>
                      <td> {{ $info->address }}</td>
                      <td> {{ $info->phone }}</td>
                      <td> {{ $info->email }}</td>
                      <td> {{ $info->added?->name ?? __('admin.unknown') }}</td>
                      <td> {{ $info->created_at }}</td>
                      <td>
                        @if ($info->updated_by>0)
                        {{ $info->updatedby?->name ?? __('admin.unknown') }}
                        @else
                          {{ __('admin.unknown') }}
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
                        @if ($info->active==1)
                           {{ __('admin.active') }}
                        @else
                           {{ __('admin.inactive') }}
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('branches.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                        <a href="{{ route('branches.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">{{ __('admin.delete') }}</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <br>
              {{ $data->links() }}
            @else
            <h1>{{ __('admin.no_data') }}</h1>
            @endif
        </div>
    </div>
</div>
@endsection
