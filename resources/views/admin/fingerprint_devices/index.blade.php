@extends('admin.layouts.admin')
@section('title') أجهزة البصمة @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('fingerprint_devices.index') }}">أجهزة البصمة</a> @endsection
@section('startpage') إدارة @endsection

@section('css')
<style>
.device-card { border-radius:10px; transition:.2s; }
.device-card:hover { box-shadow:0 4px 15px rgba(0,0,0,.1); transform:translateY(-2px); }
.badge-protocol { font-size:.75em; padding:4px 8px; border-radius:4px; background:#e9ecef; color:#495057; }
.sync-form { display:inline; }
.status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-left:5px; }
.dot-active  { background:#28a745; }
.dot-disabled{ background:#6c757d; }
.dot-error   { background:#dc3545; animation: blink 1s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>
@endsection

@section('content')
<div class="col-12">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-fingerprint text-primary ml-2"></i>إدارة أجهزة البصمة</h4>
        <div>
            <a href="{{ route('fingerprint_devices.create') }}" class="btn btn-success">
                <i class="fas fa-plus ml-1"></i> إضافة جهاز
            </a>
            <button class="btn btn-info mr-1" data-toggle="modal" data-target="#syncAllModal">
                <i class="fas fa-sync ml-1"></i> مزامنة الكل
            </button>
            <button class="btn btn-warning mr-1" data-toggle="modal" data-target="#processModal">
                <i class="fas fa-cogs ml-1"></i> معالجة السجلات
            </button>
        </div>
    </div>

    {{-- إحصائيات --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-fingerprint"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي الأجهزة</span>
                    <span class="info-box-number">{{ $devices->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">أجهزة نشطة</span>
                    <span class="info-box-number">{{ $devices->where('status',1)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">إجمالي سجلات البصمة</span>
                    <span class="info-box-number">{{ number_format($totalLogs) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">سجلات غير معالَجة</span>
                    <span class="info-box-number">{{ number_format($pendingLogs) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    {{-- بطاقات الأجهزة --}}
    @forelse($devices as $device)
    <div class="card device-card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                {{-- معلومات الجهاز --}}
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <span class="status-dot dot-{{ $device->status==1 ? 'active' : ($device->status==3 ? 'error' : 'disabled') }}"></span>
                        <div>
                            <h5 class="mb-0">{{ $device->device_name }}</h5>
                            <small class="text-muted">{{ $device->device_code }}</small>
                            @if($device->location)
                                <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $device->location }}</small>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- تفاصيل الاتصال --}}
                <div class="col-md-3">
                    <div><i class="fas fa-network-wired text-primary ml-1"></i>
                        <strong>{{ $device->ip_address }}</strong>:{{ $device->port }}
                    </div>
                    <div class="mt-1">
                        <span class="badge-protocol">{{ $device->protocol_label }}</span>
                    </div>
                    @if($device->model)
                        <small class="text-muted">موديل: {{ $device->model }}</small>
                    @endif
                </div>

                {{-- آخر مزامنة --}}
                <div class="col-md-2 text-center">
                    {!! $device->status_label !!}
                    @if($device->last_sync_at)
                        <div class="mt-1">
                            <small class="text-muted">آخر مزامنة:</small><br>
                            <small>{{ $device->last_sync_at->diffForHumans() }}</small><br>
                            <small class="text-info">{{ $device->last_sync_records }} سجل</small>
                        </div>
                    @else
                        <div class="mt-1"><small class="text-muted">لم تتم مزامنة</small></div>
                    @endif
                    @if($device->last_error)
                        <small class="text-danger" title="{{ $device->last_error }}">
                            <i class="fas fa-exclamation-triangle"></i> خطأ
                        </small>
                    @endif
                </div>

                {{-- أزرار الإجراءات --}}
                <div class="col-md-3 text-left">
                    {{-- اختبار الاتصال --}}
                    <button class="btn btn-sm btn-outline-info mb-1"
                        onclick="testConnection({{ $device->id }}, this)">
                        <i class="fas fa-plug"></i> اختبار
                    </button>

                    {{-- مزامنة --}}
                    <button class="btn btn-sm btn-primary mb-1"
                        data-toggle="modal"
                        data-target="#syncModal{{ $device->id }}">
                        <i class="fas fa-sync"></i> مزامنة
                    </button>

                    {{-- السجلات --}}
                    <a href="{{ route('fingerprint_devices.logs', $device->id) }}"
                       class="btn btn-sm btn-secondary mb-1">
                        <i class="fas fa-list"></i> سجلات
                    </a>

                    {{-- تعديل --}}
                    <a href="{{ route('fingerprint_devices.edit', $device->id) }}"
                       class="btn btn-sm btn-warning mb-1">
                        <i class="fas fa-edit"></i>
                    </a>

                    {{-- حذف --}}
                    <a href="{{ route('fingerprint_devices.delete', $device->id) }}"
                       class="btn btn-sm btn-danger mb-1"
                       onclick="return confirm('حذف الجهاز وجميع سجلاته؟')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal مزامنة الجهاز --}}
    <div class="modal fade" id="syncModal{{ $device->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-sync ml-1"></i>مزامنة: {{ $device->device_name }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('fingerprint_devices.sync', $device->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>تاريخ المعالجة</label>
                            <input type="date" name="sync_date" class="form-control"
                                value="{{ today()->format('Y-m-d') }}">
                            <small class="text-muted">
                                سيتم جلب السجلات من الجهاز ثم معالجة سجلات هذا التاريخ وتحويلها إلى حضور/انصراف
                            </small>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle ml-1"></i>
                            الجهاز: <strong>{{ $device->ip_address }}:{{ $device->port }}</strong>
                            | البروتوكول: <strong>{{ $device->protocol_label }}</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync ml-1"></i> بدء المزامنة
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @empty
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-fingerprint fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">لا توجد أجهزة بصمة مضافة</h5>
            <a href="{{ route('fingerprint_devices.create') }}" class="btn btn-success mt-2">
                <i class="fas fa-plus ml-1"></i> إضافة جهاز الآن
            </a>
        </div>
    </div>
    @endforelse
</div>

{{-- Modal مزامنة الكل --}}
<div class="modal fade" id="syncAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-sync ml-1"></i>مزامنة جميع الأجهزة</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('fingerprint_devices.sync_all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>تاريخ المعالجة</label>
                        <input type="date" name="sync_date" class="form-control" value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <p class="text-muted">سيتم الاتصال بجميع الأجهزة النشطة ({{ $devices->where('status',1)->count() }} جهاز) ومعالجة سجلاتها.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-sync ml-1"></i> مزامنة الكل
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal معالجة السجلات الخام --}}
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-cogs ml-1"></i>معالجة السجلات الخام</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('fingerprint_devices.process_logs') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>تاريخ المعالجة</label>
                        <input type="date" name="process_date" class="form-control" value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <p class="text-muted">
                        يعالج السجلات الخام الموجودة في قاعدة البيانات (<strong>{{ number_format($pendingLogs) }}</strong> سجل غير معالَج) ويحوّلها إلى حضور وانصراف.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-cogs ml-1"></i> بدء المعالجة
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function testConnection(deviceId, btn) {
    const original = btn.innerHTML;
    btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';
    btn.disabled   = true;

    fetch(`{{ url('admin/dashboard/fingerprint_devices') }}/${deviceId}/test`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const cls = data.success ? 'alert-success' : 'alert-danger';
        const msg = `<div class="alert ${cls} alert-dismissible mt-2" style="font-size:.9em">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            ${data.message}
        </div>`;
        btn.closest('.card-body').insertAdjacentHTML('beforeend', msg);
    })
    .catch(() => alert('خطأ في الاتصال'))
    .finally(() => { btn.innerHTML = original; btn.disabled = false; });
}
</script>
@endsection
