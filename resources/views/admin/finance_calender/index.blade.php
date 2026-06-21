@extends('admin.layouts.admin')
@section('title')
{{ __('admin.fiscal_title') }}
@endsection
@section('start')
{{ __('admin.fiscal_title') }}
@endsection
@section('home')
<a href="{{ route('finance_calender.index') }}"> {{ __('admin.fiscal_title') }}</a>
@endsection
@section('startpage')
    {{ __('admin.view') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.fiscal_data') }}
              <a class="btn btn-success" href="{{ route('finance_calender.create') }}">{{ __('admin.add_new') }}</a>
            </h3>
        </div>
        <div class="card-body">
          @if(@isset($data) and !@empty($data) )
          <table class="table table-bordered">
              <thead>
                <th scope="col">{{ __('admin.fiscal_year') }}</th>
                <th scope="col">{{ __('admin.fiscal_start') }}</th>
                <th scope="col">{{ __('admin.fiscal_end') }}</th>
                <th scope="col">{{ __('admin.created_by') }}</th>
                <th scope="col">{{ __('admin.updated_by') }}</th>
                <th scope="col">{{ __('admin.created_at') }}</th>
                <th scope="col">{{ __('admin.updated_at') }}</th>
                <th scope="col">{{ __('admin.action') }}</th>
              </thead>
              <tbody>
                @foreach ($data as $info)
                  <tr>
                    <td> {{ $info->finance_yr }}</td>
                    <td> {{ $info->start_date }}</td>
                    <td> {{ $info->end_date }}</td>
                    <td> {{ $info->added?->name ?? __('admin.unknown') }}</td>
                    <td>
                      @if ($info->updated_by>0)
                      {{ $info->updatedby?->name ?? __('admin.unknown') }}
                      @else
                        —
                      @endif
                    </td>
                    <td> {{ $info->created_at }}</td>
                    <td>
                      @if(@isset($info->updated_at) and !@empty($info->updated_at) )
                      {{ $info->updated_at }}
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      @if ($info->is_open==0)
                      <a href="{{ route('finance_calender.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                      <a href="{{ route('finance_calender.delete',$info->id) }}" class="btn btn-sm btn-danger are_you_sure">{{ __('admin.delete') }}</a>
                      <button class="btn btn-sm btn-info show_year_monthes" data-id="{{ $info->id }}" type="submit">{{ __('admin.fiscal_view_months') }}</button>
                      @else
                      {{ __('admin.fiscal_closed') }}
                      <a href="{{ route('finance_calender.edit',$info->id) }}" class="btn btn-sm btn-success">{{ __('admin.edit') }}</a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <br>
              {{ $data->links() }}
          @else
          <h2>{{ __('admin.no_data') }}</h2>
          @endif
        </div>
    </div>
</div>

 <div class="modal fade " id="show_year_monthesModal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-calendar-alt mr-2"></i>{{ __('admin.fiscal_months_title') }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="show_year_monthesModalbody">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
  <script>
    $(document).ready(function(){
      $(document).on('click','.show_year_monthes',function(){
        var id=$(this).data('id');
        jQuery.ajax({
          url:'{{ route('finance_calender.show_year_monthes') }}',
          type:'post',
          'datatype':'html',
          cache:false,
          data:{"_token":"{{ csrf_token() }}",'id':id},
          success:function(data){
            $("#show_year_monthesModalbody").html(data);
            $("#show_year_monthesModal").modal('show');
          },
          error:function(){}
        });
      });
    });
  </script>
@endsection
