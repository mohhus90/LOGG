@extends('admin.layouts.admin')
@section('title') إدارة العملاء @endsection
@section('start') الإعدادات @endsection
@section('home') <a href="{{ route('clients.index') }}">العملاء</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                بيانات العملاء
                <a class="btn btn-success btn-sm ms-2" href="{{ route('clients.create') }}">+ إضافة عميل جديد</a>
            </h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(isset($data) && $data->count())
            <table class="table table-bordered table-hover text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>الاسم (English)</th>
                        <th>الاسم (عربي)</th>
                        <th>جهة الاتصال</th>
                        <th>الهاتف</th>
                        <th>القطاع</th>
                        <th>عدد الموظفين</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->client_name }}</td>
                        <td>{{ $client->client_name_A ?? '—' }}</td>
                        <td>{{ $client->contact_person ?? '—' }}</td>
                        <td>{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->industry ?? '—' }}</td>
                        <td>
                            <a href="{{ route('employees.index', ['client_id' => $client->id]) }}"
                               class="badge bg-primary text-decoration-none">
                                {{ $client->employees()->count() }} موظف
                            </a>
                        </td>
                        <td>
                            @if($client->active == 1)
                                <span class="badge bg-success">مفعّل</span>
                            @else
                                <span class="badge bg-secondary">معطّل</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('clients.import.form', $client->id) }}"
                               class="btn btn-info btn-sm" title="استيراد من CSV">
                                <i class="fas fa-file-import"></i> CSV
                            </a>
                            <a href="{{ route('clients.edit', $client->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('clients.delete', $client->id) }}"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('هل تريد حذف هذا العميل؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="text-center text-muted py-4">لا يوجد عملاء مسجلين</div>
            @endif
        </div>
    </div>
</div>
@endsection
