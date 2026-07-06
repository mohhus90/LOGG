@extends('admin.layouts.manufacturing')
@section('title') أمر إنتاج جديد @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('production_orders.index') }}">أوامر الإنتاج</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-plus ml-2"></i> أمر إنتاج جديد</h3></div>
        <form action="{{ route('production_orders.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>قائمة المواد (BOM) <span class="text-danger">*</span></label>
                            <select name="bom_id" class="form-control select2" required>
                                <option value="">-- اختر قائمة المواد --</option>
                                @foreach($boms as $bom)
                                    <option value="{{ $bom->id }}">{{ $bom->item->name ?? '-' }} (v{{ $bom->version }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الكمية المخططة <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" name="planned_quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>الفرع (اختياري)</label>
                            <select name="branch_id" class="form-control select2">
                                <option value="">-- بدون --</option>
                                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>مخزن صرف المواد الخام <span class="text-danger">*</span></label>
                            <select name="source_warehouse_id" class="form-control select2" required>
                                <option value="">-- اختر المخزن --</option>
                                @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>مخزن استلام المنتج التام <span class="text-danger">*</span></label>
                            <select name="target_warehouse_id" class="form-control select2" required>
                                <option value="">-- اختر المخزن --</option>
                                @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>تاريخ البدء المخطط</label>
                            <input type="date" name="planned_start_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>تكلفة العمالة المقدّرة</label>
                            <input type="number" step="0.01" name="labor_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>التكاليف الصناعية غير المباشرة</label>
                            <input type="number" step="0.01" name="overhead_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> إنشاء أمر الإنتاج</button>
                <a href="{{ route('production_orders.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
