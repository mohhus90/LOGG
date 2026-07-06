@extends('admin.layouts.sales')
@section('title') تفاصيل الصنف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('items.index') }}">الأصناف</a> @endsection
@section('startpage') عرض @endsection

@section('css')
<style>
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: #6c757d; font-weight: 500; }
    .detail-value { font-weight: 600; color: #343a40; }
    .badge-purple { background-color: #6f42c1; color: #fff; }
</style>
@endsection

@section('content')
<div class="col-md-9 mx-auto">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-box-open ml-2"></i>
                تفاصيل الصنف — {{ $item->name }}
            </h3>
            <div class="card-tools">
                <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit ml-1"></i> تعديل
                </a>
                <a href="{{ route('items.index') }}" class="btn btn-sm btn-secondary mr-1">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- الصورة --}}
                @if($item->image)
                <div class="col-md-3 text-center mb-3">
                    <img src="{{ asset('storage/' . $item->image) }}"
                         alt="صورة الصنف" class="img-fluid rounded shadow-sm" style="max-height:200px">
                </div>
                <div class="col-md-9">
                @else
                <div class="col-md-12">
                @endif

                    <div class="row">
                        {{-- معلومات أساسية --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0"><i class="fas fa-info-circle ml-1"></i> البيانات الأساسية</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="detail-row">
                                        <span class="detail-label">الكود</span>
                                        <span class="detail-value"><code>{{ $item->code ?? '—' }}</code></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">الباركود</span>
                                        <span class="detail-value"><code>{{ $item->barcode ?? '—' }}</code></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">الاسم بالعربية</span>
                                        <span class="detail-value">{{ $item->name }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">الاسم بالإنجليزية</span>
                                        <span class="detail-value">{{ $item->name_en ?? '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">النوع</span>
                                        <span class="detail-value">
                                            @php
                                                $types = [
                                                    'product'       => ['label' => 'منتج',       'class' => 'badge-primary'],
                                                    'service'       => ['label' => 'خدمة',       'class' => 'badge-info'],
                                                    'raw_material'  => ['label' => 'مادة خام',   'class' => 'badge-warning'],
                                                    'semi_finished' => ['label' => 'نصف مصنّع',  'class' => 'badge-purple'],
                                                ];
                                                $t = $types[$item->type] ?? ['label' => $item->type, 'class' => 'badge-secondary'];
                                            @endphp
                                            <span class="badge {{ $t['class'] }}">{{ $t['label'] }}</span>
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">الحالة</span>
                                        <span class="detail-value">
                                            @if($item->is_active)
                                                <span class="badge badge-success">مفعّل</span>
                                            @else
                                                <span class="badge badge-secondary">معطّل</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- التصنيف والأسعار --}}
                        <div class="col-md-6">
                            <div class="card card-outline card-success mb-3">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0"><i class="fas fa-tags ml-1"></i> التصنيف والأسعار</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="detail-row">
                                        <span class="detail-label">المجموعة</span>
                                        <span class="detail-value">{{ $item->category->name ?? '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">وحدة القياس</span>
                                        <span class="detail-value">{{ $item->unit->name ?? '—' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">سعر التكلفة</span>
                                        <span class="detail-value text-danger">
                                            {{ $item->cost_price !== null ? number_format($item->cost_price, 2) . ' ج.م' : '—' }}
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">سعر البيع</span>
                                        <span class="detail-value text-success">
                                            {{ $item->selling_price !== null ? number_format($item->selling_price, 2) . ' ج.م' : '—' }}
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">أدنى سعر بيع</span>
                                        <span class="detail-value text-warning">
                                            {{ $item->min_selling_price !== null ? number_format($item->min_selling_price, 2) . ' ج.م' : '—' }}
                                        </span>
                                    </div>
                                    @if($item->cost_price && $item->selling_price)
                                    <div class="detail-row">
                                        <span class="detail-label">هامش الربح</span>
                                        <span class="detail-value text-primary">
                                            {{ number_format($item->selling_price - $item->cost_price, 2) }} ج.م
                                            ({{ $item->cost_price > 0 ? number_format((($item->selling_price - $item->cost_price) / $item->cost_price) * 100, 1) : 0 }}%)
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الوصف --}}
                    @if($item->description)
                    <div class="card card-outline card-secondary">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0"><i class="fas fa-file-alt ml-1"></i> الوصف</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $item->description }}</p>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <a href="{{ route('items.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-right ml-1"></i> رجوع للقائمة
            </a>
        </div>
    </div>
</div>
@endsection
