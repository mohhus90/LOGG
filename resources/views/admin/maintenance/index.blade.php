@extends('admin.layouts.admin')

@section('title', 'الصيانة والنسخ الاحتياطي - NEXA ERP')

@section('start', 'الصيانة والنسخ الاحتياطي')
@section('home', 'الرئيسية')
@section('startpage', 'الصيانة')

@section('content')
<div dir="rtl">

  {{-- ════ بطاقات الإحصاء ════ --}}
  <div class="row mb-3">
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-primary">
        <div class="inner">
          <h3>{{ $backupFiles->count() }}</h3>
          <p>نسخ احتياطية</p>
        </div>
        <div class="icon"><i class="fas fa-database"></i></div>
        <a href="#section-backups" class="small-box-footer">عرض <i class="fas fa-arrow-down"></i></a>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3>{{ count($logLines) }}</h3>
          <p>سطر في السجلات</p>
        </div>
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <a href="#section-logs" class="small-box-footer">عرض <i class="fas fa-arrow-down"></i></a>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3>{{ $activeAdmins->count() }}</h3>
          <p>مستخدمو النظام</p>
        </div>
        <div class="icon"><i class="fas fa-users-cog"></i></div>
        <a href="#section-users" class="small-box-footer">عرض <i class="fas fa-arrow-down"></i></a>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3>{{ config('database.connections.mysql.database') }}</h3>
          <p>قاعدة البيانات</p>
        </div>
        <div class="icon"><i class="fas fa-server"></i></div>
        <a href="#section-backups" class="small-box-footer">نسخ الآن <i class="fas fa-arrow-down"></i></a>
      </div>
    </div>
  </div>

  {{-- ════ قسم النسخ الاحتياطي ════ --}}
  <div id="section-backups" class="card card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-database ml-2"></i>النسخ الاحتياطي</h3>
    </div>
    <div class="card-body">

      {{-- زر النسخ اليدوي --}}
      <div class="mb-3">
        <form action="{{ route('maintenance.backup.now') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-success"
                  onclick="return confirm('هل تريد إنشاء نسخة احتياطية الآن؟')">
            <i class="fas fa-save ml-1"></i> إنشاء نسخة احتياطية الآن
          </button>
        </form>
        <small class="text-muted mr-3">
          <i class="fas fa-info-circle"></i>
          النسخ تُحفظ في: <code>storage/app/backups/</code>
        </small>
      </div>

      {{-- النسخ التلقائية - معلومات --}}
      <div class="alert alert-info">
        <i class="fas fa-clock ml-2"></i>
        <strong>النسخ التلقائي:</strong>
        لتفعيل النسخ التلقائي، أضف الأمر التالي في Cron/Task Scheduler:<br>
        <code>php {{ base_path() }}\artisan maintenance:backup</code>
        &nbsp;— يومياً في الساعة 2 صباحاً.
      </div>

      {{-- جدول النسخ الاحتياطية --}}
      @if($backupFiles->isEmpty())
        <div class="alert alert-warning"><i class="fas fa-folder-open ml-2"></i>لا توجد نسخ احتياطية بعد.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>اسم الملف</th>
              <th>الحجم</th>
              <th>التاريخ</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @foreach($backupFiles as $i => $file)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><code>{{ $file['name'] }}</code></td>
              <td><span class="badge badge-info">{{ $file['size'] }}</span></td>
              <td>{{ $file['date'] }}</td>
              <td>
                {{-- تنزيل --}}
                <a href="{{ route('maintenance.backup.download', ['file' => $file['name']]) }}"
                   class="btn btn-sm btn-primary">
                  <i class="fas fa-download"></i> تنزيل
                </a>

                {{-- استعادة --}}
                <form action="{{ route('maintenance.backup.restore') }}" method="POST" class="d-inline">
                  @csrf
                  <input type="hidden" name="backup_file" value="{{ $file['name'] }}">
                  <button type="submit" class="btn btn-sm btn-warning"
                          onclick="return confirm('تحذير: سيتم استبدال قاعدة البيانات الحالية بالكامل. هل أنت متأكد؟')">
                    <i class="fas fa-undo"></i> استعادة
                  </button>
                </form>

                {{-- حذف --}}
                <a href="{{ route('maintenance.backup.delete', ['file' => $file['name']]) }}"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('هل تريد حذف هذه النسخة؟')">
                  <i class="fas fa-trash"></i> حذف
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- ════ قسم مراقبة المستخدمين ════ --}}
  <div id="section-users" class="card card-success">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-users-cog ml-2"></i>مراقبة المستخدمين</h3>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>الاسم</th>
              <th>البريد الإلكتروني</th>
              <th>آخر نشاط</th>
              <th>الحالة</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activeAdmins as $i => $admin)
            @php
              $lastActive = \Carbon\Carbon::parse($admin->updated_at);
              $isOnline   = $lastActive->diffInMinutes(now()) <= 30;
            @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $admin->name }}</td>
              <td>{{ $admin->email }}</td>
              <td>{{ $lastActive->diffForHumans() }}</td>
              <td>
                @if($isOnline)
                  <span class="badge badge-success">نشط</span>
                @else
                  <span class="badge badge-secondary">غير نشط</span>
                @endif
              </td>
            </tr>
            @empty
              <tr><td colspan="5" class="text-center">لا يوجد مستخدمون</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <small class="text-muted">يُعتبر المستخدم نشطاً إذا سجّل دخولاً خلال آخر 30 دقيقة.</small>
    </div>
  </div>

  {{-- ════ قسم سجلات الأخطاء ════ --}}
  <div id="section-logs" class="card card-warning">
    <div class="card-header d-flex align-items-center">
      <h3 class="card-title ml-auto"><i class="fas fa-exclamation-triangle ml-2"></i>سجلات الأخطاء (آخر 200 سطر)</h3>
      <form action="{{ route('maintenance.logs.clear') }}" method="POST" class="mr-auto">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger"
                onclick="return confirm('هل تريد مسح ملف السجلات نهائياً؟')">
          <i class="fas fa-trash-alt ml-1"></i> مسح السجلات
        </button>
      </form>
    </div>
    <div class="card-body p-0">
      <div id="log-container" style="max-height:400px; overflow-y:auto; background:#1a1a2e; padding:12px; direction:ltr;">
        @if(empty($logLines))
          <p class="text-success text-center mt-2">ملف السجلات فارغ — لا توجد أخطاء.</p>
        @else
          @foreach($logLines as $line)
          @php
            $cls = 'text-white-50';
            if (str_contains($line, '.ERROR') || str_contains($line, '.CRITICAL')) $cls = 'text-danger';
            elseif (str_contains($line, '.WARNING')) $cls = 'text-warning';
            elseif (str_contains($line, '.INFO'))    $cls = 'text-info';
          @endphp
          <div class="{{ $cls }}" style="font-family:monospace;font-size:.78em;white-space:pre-wrap;word-break:break-all;">{{ $line }}</div>
          @endforeach
        @endif
      </div>
    </div>
  </div>

</div>
@endsection

@section('script')
<script>
  // التمرير للأسفل في منطقة السجلات
  var logBox = document.getElementById('log-container');
  if (logBox) { logBox.scrollTop = 0; }
</script>
@endsection
