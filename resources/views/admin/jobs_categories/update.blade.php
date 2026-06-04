@extends('admin.layouts.admin')
@section('title')
الوظائف
@endsection
@section('start')
    الضبط العام
@endsection
@section('home')
<a href="{{ route('jobs_categories.index') }}">الوظائف</a>

@endsection
@section('startpage')
اضافة
@endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">تحديث الوظيفة</h3>
        </div>
        <div class="card-body">
              <form method="POST" action="{{ route('jobs_categories.update', $data['id']) }}">
                @csrf

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">اسم الوظيفة <span class="text-danger">*</span></label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" name="job_name" id="job_name"
                        value="{{ old('job_name', $data['job_name']) }}">
                    @error('job_name')<div class="text-danger">{{ $message }}</div>@enderror
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">المستوى الوظيفي</label>
                  <div class="col-sm-5">
                    <select name="org_level_id" class="form-select">
                      <option value="">— اختر المستوى الوظيفي —</option>
                      @foreach($orgLevels ?? [] as $level)
                        <option value="{{ $level->id }}"
                            {{ old('org_level_id', $data->org_level_id) == $level->id ? 'selected' : '' }}>
                          {{ str_repeat('— ', $level->level_order - 1) }}{{ $level->name }}
                          @if($level->receives_seller_commission) [عمولة بائع] @endif
                          @if($level->receives_manager_commission) [عمولة مدير] @endif
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn btn-primary btn-lg col-2">تعديل</button>
                  <a class="btn btn-warning btn-lg col-2" href="{{ route('jobs_categories.index') }}">إلغاء</a>
                </div>
              </form>
        </div>
    </div>
</div>
   
@endsection
