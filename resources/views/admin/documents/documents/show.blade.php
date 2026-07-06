@extends('admin.layouts.documents')
@section('title') {{ $document->title }} @endsection
@section('start') إدارة الوثائق @endsection
@section('home') <a href="{{ route('documents.index') }}">الوثائق</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> {{ $document->title }}
                <span class="badge badge-{{ $document->status_color }} mr-2">{{ $document->status_label }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i> تحميل</a>
                @if($document->status === 'draft' || $document->status === 'pending')
                <form action="{{ route('documents.approve', $document->id) }}" method="POST" class="d-inline">
                    @csrf<button class="btn btn-sm btn-success"><i class="fas fa-check"></i> اعتماد</button>
                </form>
                <form action="{{ route('documents.reject', $document->id) }}" method="POST" class="d-inline">
                    @csrf<button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> رفض</button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>الفئة:</strong> {{ $document->category->name ?? '-' }}</div>
                <div class="col-md-4"><strong>اسم الملف:</strong> {{ $document->file_original_name }}</div>
                <div class="col-md-4"><strong>رفعها:</strong> {{ $document->uploadedBy->name ?? '-' }}</div>
                @if($document->approved_at)
                <div class="col-md-4 mt-2"><strong>اعتمدها/رفضها:</strong> {{ $document->approver->name ?? '-' }} بتاريخ {{ $document->approved_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
