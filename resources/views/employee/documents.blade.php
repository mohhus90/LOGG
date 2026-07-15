@php $__title = 'مستنداتي'; @endphp
@include('employee._header')

<div class="card req-card mb-4">
  <div class="card-header bg-light">
    <h5 class="mb-0"><i class="fas fa-folder-open ml-2 text-primary"></i>ملفاتي المحفوظة</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>نوع المستند</th>
            <th>اسم الملف</th>
            <th>تاريخ الرفع</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($documents as $doc)
          @php
            $typeInfo = \App\Models\EmployeeDocument::TYPES[$doc->doc_type] ?? null;
            $latestReq = $doc->latestAccessRequest();
            $status = match(true) {
                !$latestReq => 'none',
                $latestReq->status === 0 => 'pending',
                $latestReq->isAvailableForDownload() => 'approved',
                default => 'none',
            };
          @endphp
          <tr>
            <td>
              <i class="fas {{ $typeInfo['icon'] ?? 'fa-file' }} ml-1 text-muted"></i>
              {{ $typeInfo['ar'] ?? $doc->doc_type }}
            </td>
            <td><small>{{ $doc->doc_original_name }}</small></td>
            <td><small>{{ $doc->created_at->format('Y-m-d') }}</small></td>
            <td>
              @if($status === 'approved')
                <a href="{{ route('employee.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-download ml-1"></i>تحميل
                </a>
              @elseif($status === 'pending')
                <span class="badge badge-warning">⏳ بانتظار موافقة المسؤول</span>
              @else
                <form method="POST" action="{{ route('employee.documents.request_access', $doc->id) }}" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-hand-paper ml-1"></i>طلب الوصول
                  </button>
                </form>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center text-muted py-3">لا توجد مستندات محفوظة بعد</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('employee._footer')
