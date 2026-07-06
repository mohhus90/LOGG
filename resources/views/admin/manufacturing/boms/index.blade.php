@extends('admin.layouts.manufacturing')
@section('title') قوائم المواد @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('bill_of_materials.index') }}">قوائم المواد</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sitemap ml-2"></i> قوائم المواد (BOM)</h3>
            <div class="card-tools">
                <a href="{{ route('bill_of_materials.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> قائمة مواد جديدة</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>المنتج</th><th>الإصدار</th><th>كمية الإنتاج للدفعة</th><th>عدد المكونات</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $bom)
                    <tr>
                        <td>{{ $bom->item->name ?? '-' }}</td>
                        <td>v{{ $bom->version }}</td>
                        <td>{{ number_format($bom->output_quantity, 2) }}</td>
                        <td>{{ $bom->lines()->count() }}</td>
                        <td>
                            @if($bom->is_active)<span class="badge badge-success">مفعّلة</span>
                            @else<span class="badge badge-secondary">معطّلة</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('bill_of_materials.show', $bom->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <form action="{{ route('bill_of_materials.toggle', $bom->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-warning"><i class="fas fa-power-off"></i></button>
                            </form>
                            <a href="{{ route('bill_of_materials.delete', $bom->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف قائمة المواد؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد قوائم مواد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
