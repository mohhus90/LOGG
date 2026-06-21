@extends('admin.layouts.admin')
@section('title') {{ __('admin.att_excel_title') }} @endsection
@section('start') {{ __('admin.att_title') }} @endsection
@section('home') <a href="{{ route('attendance.index') }}">{{ __('admin.att_title') }}</a> @endsection
@section('startpage') Excel @endsection

@section('css')
<style>
.drop-zone {
    border: 2px dashed #007bff; border-radius:10px; padding:40px;
    text-align:center; cursor:pointer; transition:.2s; background:#f8f9ff;
}
.drop-zone:hover, .drop-zone.active { background:#e8f0fe; border-color:#0056b3; }
.drop-zone i { font-size:3em; color:#007bff; margin-bottom:10px; }
.template-col { background:#fff3cd; font-weight:600; border:1px solid #ffc107; }
.col-optional { background:#d4edda; color:#155724; }
</style>
@endsection

@section('content')
<div class="col-md-9 mx-auto">

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-excel ml-2"></i>
                {{ __('admin.att_excel_title') }}
            </h3>
        </div>
        <form action="{{ route('attendance.excel_import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {!! session('success') !!}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="drop-zone" id="dropZone" onclick="document.getElementById('excelFile').click()">
                    <i class="fas fa-file-excel"></i>
                    <div id="dropText">
                        <h5>{{ __('admin.att_drop_here') }}</h5>
                        <p class="text-muted mb-0">{{ __('admin.att_file_types') }}</p>
                    </div>
                    <div id="filePreview" class="d-none mt-2">
                        <span class="badge badge-success p-2" id="fileName"></span>
                    </div>
                </div>
                <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv"
                    class="d-none" required onchange="previewFile(this)">

                <div class="row mt-3">
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                            value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_file_format') }}</label>
                        <select name="has_date_col" class="form-control" id="formatSelect">
                            <option value="0">A=Finger ID | B={{ __('admin.att_check_in') }} | C={{ __('admin.att_check_out') }}</option>
                            <option value="1">A=Finger ID | B={{ __('admin.att_date') }} | C={{ __('admin.att_check_in') }} | D={{ __('admin.att_check_out') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __('admin.att_absent_employees') }}</label>
                        <div class="mt-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="markAbsent"
                                    name="mark_absent" value="1" checked>
                                <label class="custom-control-label" for="markAbsent">
                                    {{ __('admin.att_auto_absent') }}
                                </label>
                            </div>
                            <small class="text-muted">{{ __('admin.att_not_in_file_absent') }}</small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload ml-1"></i> {{ __('admin.att_upload_process') }}
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">{{ __('admin.back') }}</a>
                <a href="{{ route('attendance.excel_template') }}" class="btn btn-outline-success mr-2">
                    <i class="fas fa-download ml-1"></i> {{ __('admin.att_download_template') }}
                </a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle text-info ml-2"></i>{{ __('admin.att_excel_format_title') }}</h5>
        </div>
        <div class="card-body">

            <h6 class="text-primary">{{ __('admin.att_format_one_title') }}</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" style="max-width:500px">
                    <thead>
                        <tr>
                            <th class="template-col">A — Finger ID</th>
                            <th class="template-col">B — {{ __('admin.att_check_in') }}</th>
                            <th class="template-col">C — {{ __('admin.att_check_out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>08:02</td><td>17:05</td></tr>
                        <tr><td>2</td><td>07:58</td><td>17:30</td></tr>
                        <tr><td>5</td><td>09:15</td><td>18:00</td></tr>
                    </tbody>
                </table>
            </div>

            <h6 class="text-primary">{{ __('admin.att_format_two_title') }}</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" style="max-width:650px">
                    <thead>
                        <tr>
                            <th class="template-col">A — Finger ID</th>
                            <th class="template-col">B — {{ __('admin.att_date') }}</th>
                            <th class="template-col">C — {{ __('admin.att_check_in') }}</th>
                            <th class="template-col">D — {{ __('admin.att_check_out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>2024-01-15</td><td>08:02</td><td>17:05</td></tr>
                        <tr><td>2</td><td>2024-01-15</td><td>07:58</td><td>17:30</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle ml-1"></i>
                        <strong>{{ __('admin.notes') }}:</strong>
                        <ul class="mb-0 mt-1">
                            <li>{{ __('admin.att_first_row_ignored') }}</li>
                            <li>{{ __('admin.att_multiple_punches') }}</li>
                            <li>{{ __('admin.att_finger_must_match') }}</li>
                            <li>{{ __('admin.att_excel_time_support') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle ml-1"></i>
                        <strong>{{ __('admin.att_warnings') }}:</strong>
                        <ul class="mb-0 mt-1">
                            <li>{{ __('admin.att_unknown_fingers') }}</li>
                            <li>{{ __('admin.att_existing_not_replaced') }}</li>
                            <li>{{ __('admin.att_check_finger') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('script')
<script>
function previewFile(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    document.getElementById('dropText').classList.add('d-none');
    document.getElementById('filePreview').classList.remove('d-none');
    document.getElementById('fileName').textContent = '📄 ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    document.getElementById('dropZone').classList.add('active');
}

const zone = document.getElementById('dropZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('active'); });
zone.addEventListener('dragleave', () => zone.classList.remove('active'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    const fileInput = document.getElementById('excelFile');
    fileInput.files = e.dataTransfer.files;
    previewFile(fileInput);
});
</script>
@endsection
