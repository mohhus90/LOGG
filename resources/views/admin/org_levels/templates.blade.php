@extends('admin.layouts.admin')
@section('title') نماذج الهياكل الوظيفية @endsection
@section('start') الهيكل الوظيفي @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">الهيكل الوظيفي</a>
@endsection
@section('startpage') النماذج الجاهزة @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-layer-group text-warning"></i>
                نماذج الهياكل الوظيفية الجاهزة
            </h3>
            <a href="{{ route('org_levels.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right"></i> العودة للهيكل الحالي
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>تنبيه:</strong> عند تحميل نموذج جاهز سيتم <strong>استبدال الهيكل الوظيفي الحالي</strong>.
                يمكنك تعديله بعد التحميل حسب احتياج شركتك.
            </div>

            <div class="row">
                @forelse($templates as $template)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-building"></i>
                                {{ $template->template_name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <strong>نوع الشركة:</strong> {{ $template->company_type_label }}
                            </p>
                            <h6 class="mb-2">المستويات الوظيفية:</h6>
                            <ul class="list-unstyled">
                                @foreach($template->levels_data as $lv)
                                <li class="mb-1">
                                    <span class="badge bg-secondary me-1">{{ $lv['level_order'] }}</span>
                                    {{ $lv['name'] }}
                                    @if(!empty($lv['receives_seller_commission']))
                                        <span class="badge bg-success" title="عمولة بائع">B</span>
                                    @endif
                                    @if(!empty($lv['receives_manager_commission']))
                                        <span class="badge bg-primary" title="عمولة مدير">M</span>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer">
                            <form action="{{ route('org_levels.load_template') }}" method="POST">
                                @csrf
                                <input type="hidden" name="template_id" value="{{ $template->id }}">
                                <button type="submit" class="btn btn-primary w-100"
                                    onclick="return confirm('سيتم استبدال الهيكل الحالي بهذا النموذج. هل تريد المتابعة؟')">
                                    <i class="fas fa-download"></i> تحميل هذا النموذج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                        <p class="text-muted">لا توجد نماذج جاهزة حالياً</p>
                    </div>
                </div>
                @endforelse
            </div>

            <hr>
            <div class="text-center">
                <a href="{{ route('org_levels.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> أو أنشئ هيكلاً مخصصاً من الصفر
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
