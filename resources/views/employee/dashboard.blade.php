{{-- FILE: resources/views/employee/dashboard.blade.php --}}
@php $__title = 'بوابة الموظف'; @endphp
@include('employee._header')

  {{-- رصيد الإجازات --}}
  @if($vacationBalance)
  <div class="row mb-4">
    <div class="col-md-3 mb-2">
      <div class="balance-box">
        <div class="balance-num">{{ $vacationBalance->annual_remaining }}</div>
        <div>رصيد إجازة اعتيادية</div>
        <small class="text-muted">من أصل {{ $vacationBalance->annual_balance }} يوم</small>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="balance-box" style="border-color:#fd7e14">
        <div class="balance-num" style="color:#fd7e14">{{ $vacationBalance->casual_remaining }}</div>
        <div>رصيد إجازة عارضة</div>
        <small class="text-muted">من أصل {{ $vacationBalance->casual_balance }} يوم</small>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="balance-box" style="border-color:#007bff">
        <div class="balance-num" style="color:#007bff">{{ $pendingRequests }}</div>
        <div>طلبات قيد الانتظار</div>
      </div>
    </div>
    <div class="col-md-3 mb-2">
      <div class="balance-box" style="border-color:#28a745">
        <div class="balance-num" style="color:#28a745">{{ $approvedRequests }}</div>
        <div>طلبات مقبولة هذا الشهر</div>
      </div>
    </div>
  </div>
  @endif

  <div class="row">

    {{-- نموذج الطلب الجديد --}}
    <div class="col-md-6">
      <div class="card req-card mb-4">
        <div class="card-header" style="background:#11998e;color:#fff;border-radius:12px 12px 0 0">
          <h5 class="mb-0"><i class="fas fa-paper-plane ml-2"></i>طلب جديد</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('employee.request.store') }}">
            @csrf

            <div class="form-group">
              <label>نوع الطلب <span class="text-danger">*</span></label>
              <select name="request_type" class="form-control" id="reqType" onchange="toggleFields()">
                <option value="annual_vacation">🏖 إجازة اعتيادية</option>
                <option value="casual_vacation">📅 إجازة عارضة</option>
                <option value="late_permission">⏰ إذن تأخير</option>
                <option value="early_leave">🚪 إذن انصراف مبكر</option>
                <option value="mission">🏢 مأمورية</option>
              </select>
            </div>

            {{-- حقول الإجازة --}}
            <div id="vacationFields">
              <div class="row">
                <div class="col-6 form-group">
                  <label>من تاريخ</label>
                  <input type="date" name="start_date" class="form-control"
                    value="{{ today()->format('Y-m-d') }}">
                </div>
                <div class="col-6 form-group">
                  <label>إلى تاريخ</label>
                  <input type="date" name="end_date" class="form-control"
                    value="{{ today()->format('Y-m-d') }}">
                </div>
              </div>
            </div>

            {{-- حقول الإذن --}}
            <div id="permissionFields" class="d-none">
              <div class="row">
                <div class="col-6 form-group">
                  <label>التاريخ</label>
                  <input type="date" name="perm_date" class="form-control"
                    value="{{ today()->format('Y-m-d') }}">
                </div>
                <div class="col-3 form-group">
                  <label>من الساعة</label>
                  <input type="time" name="time_from" class="form-control">
                </div>
                <div class="col-3 form-group">
                  <label>إلى</label>
                  <input type="time" name="time_to" class="form-control">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>السبب</label>
              <textarea name="reason" class="form-control" rows="2"
                placeholder="اذكر سبب الطلب..."></textarea>
            </div>

            <button type="submit" class="btn btn-block text-white" style="background:#11998e;border-radius:8px">
              <i class="fas fa-paper-plane ml-2"></i>إرسال الطلب
            </button>
          </form>
        </div>
      </div>
    </div>

    {{-- سجل الطلبات --}}
    <div class="col-md-6">
      <div class="card req-card mb-4">
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="fas fa-history ml-2 text-primary"></i>طلباتي السابقة</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>النوع</th>
                  <th>التاريخ</th>
                  <th>الحالة</th>
                </tr>
              </thead>
              <tbody>
                @forelse($requests as $req)
                <tr>
                  <td><small>{{ $req->type_label }}</small></td>
                  <td><small>{{ $req->start_date->format('d/m/Y') }}</small></td>
                  <td>{!! $req->status_label !!}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">لا توجد طلبات</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

<script>
function toggleFields() {
  const type = document.getElementById('reqType').value;
  const isPermission = ['late_permission','early_leave'].includes(type);
  document.getElementById('vacationFields').classList.toggle('d-none', isPermission);
  document.getElementById('permissionFields').classList.toggle('d-none', !isPermission);
}
</script>
@include('employee._footer')
