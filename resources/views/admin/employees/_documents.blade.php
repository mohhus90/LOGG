{{-- Employee documents upload grid — edit mode only (requires an existing employee id). --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-folder-open mr-2"></i>ملفات التعيين</h5>
    </div>
    <div class="card-body">
        <div class="doc-grid">
            @foreach($docTypes as $type => $info)
                @php $doc = $documents->get($type); @endphp
                <div class="doc-card {{ $doc ? 'has-file' : '' }}">
                    @if($doc)<span class="doc-badge-uploaded">✓ مرفوع</span>@endif
                    <div class="doc-icon"><i class="fas {{ $info['icon'] }}"></i></div>
                    <div class="doc-name">{{ $info['ar'] }}</div>
                    @if($doc)
                        <div class="doc-filename">{{ Str::limit($doc->doc_original_name, 30) }}</div>
                        <div class="doc-actions">
                            <a href="{{ route('employees.document.download', [$data->id, $doc->id]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> تنزيل
                            </a>
                            <a href="{{ route('employees.document.delete', [$data->id, $doc->id]) }}"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('حذف هذا الملف؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    @else
                        <div class="doc-filename" style="color:#9ca3af">لم يُرفع بعد</div>
                    @endif
                    <form action="{{ route('employees.document.upload', $data->id) }}" method="POST"
                          enctype="multipart/form-data" class="mt-2">
                        @csrf
                        <input type="hidden" name="doc_type" value="{{ $type }}">
                        <label class="btn btn-sm {{ $doc ? 'btn-outline-secondary' : 'btn-outline-primary' }} w-100">
                            <i class="fas fa-upload"></i> {{ $doc ? 'استبدال' : 'رفع ملف' }}
                            <input type="file" name="doc_file" class="d-none"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   onchange="this.closest('form').submit()">
                        </label>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="mt-3 p-3 rounded" style="background:#f0f9ff;border:1px solid #bae6fd;font-size:.82rem;color:#0369a1">
            <i class="fas fa-info-circle mr-1"></i>
            الملفات المقبولة: PDF، صور (JPG/PNG)، مستندات Word — الحجم الأقصى 10MB لكل ملف
        </div>
    </div>
</div>
