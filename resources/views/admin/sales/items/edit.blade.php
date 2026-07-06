@extends('admin.layouts.sales')
@section('title') تعديل صنف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('items.index') }}">الأصناف</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-md-9 mx-auto">
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit ml-2"></i>
                تعديل صنف — {{ $item->name }}
            </h3>
        </div>
        <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- بيانات أساسية --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-info-circle ml-1"></i> البيانات الأساسية</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>الكود</label>
                                <input type="text" name="code" class="form-control"
                                    placeholder="كود الصنف" value="{{ old('code', $item->code) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>الباركود</label>
                                <input type="text" name="barcode" class="form-control"
                                    placeholder="باركود" value="{{ old('barcode', $item->barcode) }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>الاسم بالعربية <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="اسم الصنف" value="{{ old('name', $item->name) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>الاسم بالإنجليزية</label>
                                <input type="text" name="name_en" class="form-control"
                                    placeholder="Item name in English" value="{{ old('name_en', $item->name_en) }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>النوع</label>
                                <select name="type" class="form-control">
                                    <option value="product"       {{ old('type', $item->type) == 'product'       ? 'selected' : '' }}>منتج</option>
                                    <option value="service"       {{ old('type', $item->type) == 'service'       ? 'selected' : '' }}>خدمة</option>
                                    <option value="raw_material"  {{ old('type', $item->type) == 'raw_material'  ? 'selected' : '' }}>مادة خام</option>
                                    <option value="semi_finished" {{ old('type', $item->type) == 'semi_finished' ? 'selected' : '' }}>نصف مصنّع</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>الحالة</label>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_active"
                                        name="is_active" value="1"
                                        {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">مفعّل</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- التصنيف والوحدة --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-tags ml-1"></i> التصنيف والوحدة</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>المجموعة</label>
                                <select name="category_id" class="form-control select2">
                                    <option value="">-- اختر المجموعة --</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>وحدة القياس</label>
                                <select name="unit_id" class="form-control select2">
                                    <option value="">-- اختر الوحدة --</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ old('unit_id', $item->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الأسعار --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-dollar-sign ml-1"></i> الأسعار</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>سعر التكلفة</label>
                                <div class="input-group">
                                    <input type="number" name="cost_price" class="form-control"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('cost_price', $item->cost_price) }}">
                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>سعر البيع</label>
                                <div class="input-group">
                                    <input type="number" name="selling_price" class="form-control"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('selling_price', $item->selling_price) }}">
                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>أدنى سعر بيع</label>
                                <div class="input-group">
                                    <input type="number" name="min_selling_price" class="form-control"
                                        step="0.01" min="0" placeholder="0.00"
                                        value="{{ old('min_selling_price', $item->min_selling_price) }}">
                                    <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- وصف وصورة --}}
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-file-alt ml-1"></i> وصف وصورة</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>الوصف</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="وصف الصنف...">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>صورة الصنف</label>
                            @if($item->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $item->image) }}"
                                         alt="صورة الصنف" class="img-thumbnail" style="max-height:120px">
                                    <small class="text-muted d-block">الصورة الحالية — ارفع صورة جديدة للاستبدال</small>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image"
                                    name="image" accept="image/*">
                                <label class="custom-file-label" for="image">اختر صورة...</label>
                            </div>
                            <small class="text-muted">الصيغ المسموحة: JPG, PNG, WebP — الحجم الأقصى: 2MB</small>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save ml-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary mr-2">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('image').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'اختر صورة...';
    this.nextElementSibling.innerText = fileName;
});
</script>
@endsection
