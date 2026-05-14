@extends('admin.layouts.admin')
@section('title') استيراد الحضور من Excel @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('attendance.index') }}">الحضور والانصراف</a> @endsection
@section('startpage') استيراد Excel @endsection

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

    {{-- بطاقة الرفع --}}
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-excel ml-2"></i>
                استيراد الحضور والانصراف من Excel
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

                {{-- منطقة رفع الملف --}}
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('excelFile').click()">
                    <i class="fas fa-file-excel"></i>
                    <div id="dropText">
                        <h5>اضغط أو اسحب ملف Excel هنا</h5>
                        <p class="text-muted mb-0">يدعم: .xlsx, .xls, .csv — حجم أقصى 10MB</p>
                    </div>
                    <div id="filePreview" class="d-none mt-2">
                        <span class="badge badge-success p-2" id="fileName"></span>
                    </div>
                </div>
                <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv"
                    class="d-none" required onchange="previewFile(this)">

                <div class="row mt-3">
                    <div class="col-md-4 form-group">
                        <label>تاريخ الحضور <span class="text-danger">*</span></label>
                        <input type="date" name="attendance_date" class="form-control" required
                            value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>تنسيق الملف</label>
                        <select name="has_date_col" class="form-control" id="formatSelect">
                            <option value="0">A=Finger ID | B=حضور | C=انصراف</option>
                            <option value="1">A=Finger ID | B=التاريخ | C=حضور | D=انصراف</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الموظفون الغائبون</label>
                        <div class="mt-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="markAbsent"
                                    name="mark_absent" value="1" checked>
                                <label class="custom-control-label" for="markAbsent">
                                    تسجيل غياب تلقائي للغائبين
                                </label>
                            </div>
                            <small class="text-muted">الموظفون غير الموجودين في الملف يُسجَّل لهم غياب</small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload ml-1"></i> رفع ومعالجة
                </button>
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary mr-2">رجوع</a>
                <a href="{{ route('attendance.excel_template') }}" class="btn btn-outline-success mr-2">
                    <i class="fas fa-download ml-1"></i> تحميل نموذج Excel
                </a>
            </div>
        </form>
    </div>

    {{-- توضيح التنسيق --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle text-info ml-2"></i>تنسيق ملف Excel المطلوب</h5>
        </div>
        <div class="card-body">

            <h6 class="text-primary">التنسيق الأول (بدون عمود تاريخ)</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" style="max-width:500px">
                    <thead>
                        <tr>
                            <th class="template-col">A — Finger ID</th>
                            <th class="template-col">B — وقت الحضور</th>
                            <th class="template-col">C — وقت الانصراف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>08:02</td><td>17:05</td></tr>
                        <tr><td>2</td><td>07:58</td><td>17:30</td></tr>
                        <tr><td>5</td><td>09:15</td><td>18:00</td></tr>
                    </tbody>
                </table>
            </div>

            <h6 class="text-primary">التنسيق الثاني (مع عمود تاريخ — لأجهزة تصدّر سجلات متعددة الأيام)</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm" style="max-width:650px">
                    <thead>
                        <tr>
                            <th class="template-col">A — Finger ID</th>
                            <th class="template-col">B — التاريخ</th>
                            <th class="template-col">C — وقت الحضور</th>
                            <th class="template-col">D — وقت الانصراف</th>
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
                        <strong>ملاحظات:</strong>
                        <ul class="mb-0 mt-1">
                            <li>الصف الأول (Headers) يُتجاهل تلقائياً</li>
                            <li>إذا بصم موظف أكثر من مرة → أبكر وقت = حضور، أحدث وقت = انصراف</li>
                            <li>Finger ID يجب أن يتطابق مع بيانات الموظف في النظام</li>
                            <li>يدعم توقيت Excel الرقمي (0.354 = 08:30) تلقائياً</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle ml-1"></i>
                        <strong>تنبيهات:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Finger IDs غير الموجودة في النظام تُعرض في نتيجة الاستيراد</li>
                            <li>الموظف إذا كان لديه سجل مسبق في هذا اليوم لن يُستبدل</li>
                            <li>تأكد من تطابق finger_id في بيانات الموظف</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
function previewFile(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    document.getElementById('dropText').classList.add('d-none');
    document.getElementById('filePreview').classList.remove('d-none');
    document.getElementById('fileName').textContent = '📄 ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    document.getElementById('dropZone').classList.add('active');
}

// Drag & Drop
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
