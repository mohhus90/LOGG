@extends('admin.layouts.assets')
@section('title') الأصل {{ $asset->asset_number }} @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('fixed_assets.index') }}">سجل الأصول</a> @endsection
@section('startpage') {{ $asset->asset_number }} @endsection

@section('content')
<div class="col-lg-9">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building ml-2"></i> {{ $asset->name }}
                <span class="badge badge-{{ $asset->status_color }} mr-2">{{ $asset->status_label }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('fixed_assets.edit', $asset->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>رقم الأصل:</strong> {{ $asset->asset_number }}</div>
                <div class="col-md-3"><strong>الفئة:</strong> {{ $asset->category->name ?? '-' }}</div>
                <div class="col-md-3"><strong>الفرع:</strong> {{ $asset->branch->name ?? '-' }}</div>
                <div class="col-md-3"><strong>الموقع:</strong> {{ $asset->location ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>تاريخ الشراء:</strong> {{ \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') }}</div>
                <div class="col-md-3 mt-2"><strong>تكلفة الشراء:</strong> {{ number_format($asset->purchase_cost, 2) }}</div>
                <div class="col-md-3 mt-2"><strong>القيمة التخريدية:</strong> {{ number_format($asset->salvage_value, 2) }}</div>
                <div class="col-md-3 mt-2"><strong>العمر الإنتاجي:</strong> {{ $asset->useful_life_years }} سنة</div>
                <div class="col-md-3 mt-2"><strong>مجمع الإهلاك:</strong> {{ number_format($asset->accumulated_depreciation, 2) }}</div>
                <div class="col-md-3 mt-2"><strong>القيمة الدفترية:</strong> {{ number_format($asset->book_value, 2) }}</div>
            </div>

            @if($asset->depreciationEntries->count())
            <hr>
            <h5>سجل الإهلاك</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>الشهر/السنة</th><th>قيمة الإهلاك</th><th>تاريخ التشغيل</th></tr></thead>
                <tbody>
                    @foreach($asset->depreciationEntries->sortByDesc(fn($e)=>$e->period_year.str_pad($e->period_month,2,'0',STR_PAD_LEFT)) as $entry)
                    <tr>
                        <td>{{ $entry->period_month }}/{{ $entry->period_year }}</td>
                        <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                        <td>{{ $entry->run_at ? \Carbon\Carbon::parse($entry->run_at)->format('Y-m-d') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($asset->status === 'active')
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6>نقل الأصل لفرع آخر</h6>
                    <form action="{{ route('fixed_assets.transfer', $asset->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <select name="to_branch_id" class="form-control form-control-sm" required>
                                <option value="">-- اختر الفرع --</option>
                                @foreach(\App\Models\Branche::where('com_code', $asset->com_code)->get() as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="date" name="transfer_date" class="form-control form-control-sm mb-2" value="{{ date('Y-m-d') }}" required>
                        <button class="btn btn-sm btn-info"><i class="fas fa-exchange-alt ml-1"></i> نقل</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h6>التخلص من الأصل (بيع/إتلاف)</h6>
                    <form action="{{ route('fixed_assets.dispose', $asset->id) }}" method="POST" onsubmit="return confirm('تأكيد التخلص من الأصل؟')">
                        @csrf
                        <input type="date" name="disposal_date" class="form-control form-control-sm mb-2" value="{{ date('Y-m-d') }}" required>
                        <input type="number" step="0.01" name="disposal_amount" class="form-control form-control-sm mb-2" placeholder="عائد التخلص (0 لو لا يوجد)" value="0" required>
                        <select name="proceeds_account_id" class="form-control form-control-sm mb-2">
                            <option value="">-- حساب استلام العائد (لو المبلغ أكبر من صفر) --</option>
                            @foreach(\App\Models\ChartOfAccount::where('com_code', $asset->com_code)->where('is_group', false)->orderBy('account_code')->get() as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="disposal_notes" class="form-control form-control-sm mb-2" placeholder="ملاحظات">
                        <button class="btn btn-sm btn-danger"><i class="fas fa-times-circle ml-1"></i> التخلص من الأصل</button>
                    </form>
                </div>
            </div>
            @endif

            @if($asset->status === 'disposed')
            <div class="alert alert-danger mt-3">
                تم التخلص من هذا الأصل بتاريخ {{ \Carbon\Carbon::parse($asset->disposal_date)->format('Y-m-d') }}
                بعائد {{ number_format($asset->disposal_amount, 2) }} — {{ $asset->disposal_notes }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
