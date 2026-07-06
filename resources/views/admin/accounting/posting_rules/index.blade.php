@extends('admin.layouts.accounting')
@section('title') إعدادات الترحيل التلقائي @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('gl_posting_rules.index') }}">إعدادات الترحيل التلقائي</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cogs ml-2"></i> ربط أحداث الترحيل التلقائي بالحسابات</h3>
        </div>
        <form action="{{ route('gl_posting_rules.update') }}" method="POST">
            @csrf
            <div class="card-body">
                <p class="text-muted">
                    كل حدث تجاري (مثل إصدار فاتورة بيع) يحتاج تحديد الحساب الذي سيُرحّل إليه كل دور من أدوار القيد،
                    بدلاً من تثبيت ذلك في الكود. عدّل الربط هنا وقتما تغيّرت هيكلة دليل الحسابات.
                </p>
                @foreach($events as $eventType => $roles)
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">{{ $eventType }}</h5>
                    <div class="row">
                        @foreach($roles as $role => $roleLabel)
                        <div class="col-md-4 mb-2">
                            <label>{{ $roleLabel }} <small class="text-muted">({{ $role }})</small></label>
                            <select name="mapping[{{ $eventType }}][{{ $role }}]" class="form-control form-control-sm select2">
                                <option value="">-- غير مربوط --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}"
                                        {{ (($rules[$eventType][$role]->account_id ?? null) == $acc->id) ? 'selected' : '' }}>
                                        {{ $acc->account_code }} - {{ $acc->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ إعدادات الترحيل</button>
            </div>
        </form>
    </div>
</div>
@endsection
