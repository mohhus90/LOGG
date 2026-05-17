{{-- FILE: resources/views/admin/employee_requests/index.blade.php --}}
@extends('admin.layouts.admin')
@section('title') طلبات الموظفين @endsection
@section('start') شئون الموظفين @endsection
@section('home') <a href="{{ route('employee_requests.index') }}">طلبات الموظفين</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-inbox ml-2"></i>طلبات الموظفين
      @if($pendingCount > 0)
        <span class="badge badge-danger mr-1">{{ $pendingCount }} طلب جديد</span>
      @endif
    </h3>
  </div>

  {{-- فلاتر --}}
  <div class="card-body pb-0">
    <form method="GET" class="form-inline mb-3 flex-wrap">
      <select name="employee_id" class="form-control ml-2 mb-1">
        <option value="">-- كل الموظفين --</option>
        @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>
          {{ $emp->employee_name_A }}
        </option>
        @endforeach
      </select>
      <select name="type" class="form-control ml-2 mb-1">
        <option value="">-- كل الأنواع --</option>
        <option value="annual_vacation" {{ request('type')=='annual_vacation'?'selected':'' }}>إجازة اعتيادية</option>
        <option value="casual_vacation" {{ request('type')=='casual_vacation'?'selected':'' }}>إجازة عارضة</option>
        <option value="late_permission" {{ request('type')=='late_permission'?'selected':'' }}>إذن تأخير</option>
        <option value="early_leave"     {{ request('type')=='early_leave'?'selected':'' }}>إذن انصراف مبكر</option>
        <option value="mission"         {{ request('type')=='mission'?'selected':'' }}>مأمورية</option>
      </select>
      <select name="status" class="form-control ml-2 mb-1">
        <option value="">-- كل الحالات --</option>
        <option value="0" {{ request('status')==='0'?'selected':'' }}>⏳ قيد الانتظار</option>
        <option value="1" {{ request('status')=='1'?'selected':'' }}>✅ مقبول</option>
        <option value="2" {{ request('status')=='2'?'selected':'' }}>❌ مرفوض</option>
      </select>
      <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i></button>
      <a href="{{ route('employee_requests.index') }}" class="btn btn-secondary mb-1">مسح</a>
    </form>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mx-3">{{ session('error') }}</div>
  @endif

  <div class="card-body pt-0">
  <div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead class="thead-dark">
      <tr>
        <th>#</th>
        <th>الموظف</th>
        <th>نوع الطلب</th>
        <th>تاريخ الطلب</th>
        <th>من</th>
        <th>إلى / الوقت</th>
        <th>الأيام</th>
        <th>السبب</th>
        <th>الحالة</th>
        <th>إجراء</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $req)
      <tr class="{{ $req->status==0?'table-warning':'' }}">
        <td>{{ $loop->iteration }}</td>
        <td>
          <strong>{{ $req->employee->employee_name_A ?? '—' }}</strong>
          <br><small class="text-muted">{{ $req->employee->employee_id ?? '' }}</small>
        </td>
        <td>{{ $req->type_label }}</td>
        <td><small>{{ $req->created_at->format('Y-m-d') }}</small></td>
        <td>{{ $req->start_date->format('Y-m-d') }}</td>
        <td>
          @if($req->end_date && $req->end_date != $req->start_date)
            {{ $req->end_date->format('Y-m-d') }}
          @elseif($req->time_from)
            {{ $req->time_from }} → {{ $req->time_to }}
          @else —
          @endif
        </td>
        <td class="text-center">
          {{ $req->days_count > 0 ? $req->days_count.' يوم' : 'إذن' }}
        </td>
        <td><small>{{ Str::limit($req->reason, 40) }}</small></td>
        <td>{!! $req->status_label !!}</td>
        <td>
          @if($req->status == 0)
          {{-- قبول --}}
          <button class="btn btn-xs btn-success" data-toggle="modal"
            data-target="#approveModal{{ $req->id }}">
            <i class="fas fa-check"></i> قبول
          </button>
          {{-- رفض --}}
          <button class="btn btn-xs btn-danger" data-toggle="modal"
            data-target="#rejectModal{{ $req->id }}">
            <i class="fas fa-times"></i> رفض
          </button>
          @else
            <small class="text-muted">
              {{ $req->reviewer?->name ?? '' }}
              <br>{{ $req->reviewed_at?->format('Y-m-d') ?? '' }}
            </small>
          @endif
        </td>
      </tr>

      {{-- Modal قبول --}}
      @if($req->status == 0)
      <div class="modal fade" id="approveModal{{ $req->id }}" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title"><i class="fas fa-check ml-1"></i>قبول الطلب</h5>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('employee_requests.approve', $req->id) }}" method="POST">
              @csrf
              <div class="modal-body">
                <p>
                  <strong>{{ $req->employee->employee_name_A ?? '' }}</strong> —
                  {{ $req->type_label }} |
                  {{ $req->start_date->format('Y-m-d') }}
                  @if($req->days_count > 0) | {{ $req->days_count }} يوم @endif
                </p>
                @if($req->request_type=='annual_vacation' || $req->request_type=='casual_vacation')
                  @php $balance = \App\Models\EmployeeVacationBalance::where('employee_id',$req->employee_id)->where('year',$req->start_date->year)->first(); @endphp
                  @if($balance)
                    <div class="alert alert-info py-2 mb-2">
                      <small>
                        رصيد اعتيادي متبقي: <strong>{{ $balance->annual_remaining }}</strong> يوم |
                        رصيد عارض متبقي: <strong>{{ $balance->casual_remaining }}</strong> يوم
                      </small>
                    </div>
                  @endif
                @endif
                <div class="form-group mb-0">
                  <label>ملاحظة (اختياري)</label>
                  <input type="text" name="review_notes" class="form-control" placeholder="...">
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-check ml-1"></i>تأكيد القبول
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Modal رفض --}}
      <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="fas fa-times ml-1"></i>رفض الطلب</h5>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('employee_requests.reject', $req->id) }}" method="POST">
              @csrf
              <div class="modal-body">
                <p><strong>{{ $req->employee->employee_name_A ?? '' }}</strong> — {{ $req->type_label }}</p>
                <div class="form-group mb-0">
                  <label>سبب الرفض <span class="text-danger">*</span></label>
                  <textarea name="review_notes" class="form-control" rows="2" required
                    placeholder="اذكر سبب الرفض..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-danger">
                  <i class="fas fa-times ml-1"></i>تأكيد الرفض
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      @endif

      @empty
      <tr><td colspan="10" class="text-center py-4">لا توجد طلبات</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  {{ $data->appends(request()->query())->links() }}
  </div>
</div>
</div>
@endsection
