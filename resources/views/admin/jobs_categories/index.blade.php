@extends('admin.layouts.admin')
@section('title')
{{ __('admin.jobs_title') }}
@endsection
@section('start')
{{ __('admin.settings_menu') }}
@endsection
@section('home')
<a href="{{ route('jobs_categories.index') }}"> {{ __('admin.jobs_title') }}</a>
@endsection
@section('startpage')
    {{ __('admin.view') }}
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('admin.jobs_data') }}
              <a class="btn btn-success" href="{{ route('jobs_categories.create') }}">{{ __('admin.add_new') }}</a>
            </h3>
        </div>

        <div class="form-group text-center " >
          <div class="form-group form-inline" style="padding: 5px">
            <label for="job_name" class="col-sm-2 ">{{ __('admin.job_name') }}</label>
            <div class="d-flex gap-2">
              <input type="text" class="form-control" name="job_name_search" value="" id="job_name_search">
              <button type="button" id="bulk_delete_btn" class="btn btn-danger">
                <i class="fas fa-trash"></i> مسح السجلات
              </button>
            </div>
        </div>
        <div class="card-body" id="ajax_res_search_div">
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
              <div class="" id="#ajax_pagination_in_search">
                {{ $data->links() }}
              </div>
            @else
            <h1>{{ __('admin.no_data') }}</h1>
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
  <script>
    $(document).ready(function () {
        ajax_search();
        $(document).on('input', '#job_name_search', function (e) {
          e.preventDefault();
            ajax_search();
        });
        $(document).on('click', '#bulk_delete_btn', function () {
            var search = $('#job_name_search').val();
            var label  = search !== '' ? 'السجلات التي تحتوي على "' + search + '"' : 'جميع سجلات الوظائف';
            if (!confirm('هل أنت متأكد من حذف ' + label + '؟')) return;
            $.ajax({
                url: '{{ route('jobs_categories.bulk_delete') }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', job_name_search: search },
                success: function (res) {
                    alert(res.message);
                    ajax_search();
                },
                error: function () {
                    alert('حدث خطأ أثناء الحذف');
                }
            });
        });
        $(document).on('click', '#ajax_pagination_in_search a', function (e) {
            e.preventDefault();
            var job_name_search = $("#job_name_search").val();
            var linkurl = $(this).attr("href");
            var pageNumber = linkurl.split('page=')[1];
            jQuery.ajax({
                url: linkurl,
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', job_name_search: job_name_search },
                success: function (data) {
                    $("#ajax_res_search_div").html(data);
                    window.history.pushState({}, '', linkurl);
                },
                error: function (xhr, status, error) {
                var errorMessage = "{{ __('admin.error_occurred') }}";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
            });
        });
        window.onpopstate = function (event) {
            event.preventDefault();
            ajax_search();
        };
        function ajax_search() {
            var job_name_search = $("#job_name_search").val();
            jQuery.ajax({
                url: '{{ route('jobs_categories.ajaxsearch') }}',
                type: 'post',
                'datatype': 'html',
                cache: false,
                data: {"_token": '{{ csrf_token() }}', job_name_search: job_name_search },
                success: function (data) {
                    $("#ajax_res_search_div").html(data);
                },
                error: function (xhr, status, error) {
                var errorMessage = "{{ __('admin.error_occurred') }}";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
            });
        }
    });
  </script>
@endsection
