@extends('admin.layouts.quality')
@section('title') فحص جودة جديد @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_inspections.index') }}">فحوصات الجودة</a> @endsection
@section('startpage') إضافة @endsection

@section('content')
<div class="col-lg-8">
    <form action="{{ route('quality_inspections.store') }}" method="POST">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-search ml-2"></i> فحص جودة جديد</h3></div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>رقم الفحص</label>
                            <input type="text" class="form-control" value="{{ $nextNumber }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>قالب الفحص <span class="text-danger">*</span></label>
                            <select name="checklist_id" id="checklistSelect" class="form-control" required>
                                <option value="">-- اختر القالب --</option>
                                @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}" data-applies="{{ $checklist->applies_to }}">{{ $checklist->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="d-block">نوع المصدر <span class="text-danger">*</span></label>
                    <div class="d-flex" style="gap:16px">
                        <label><input type="radio" name="source_type" value="production_order" checked onchange="toggleSource()"> أمر إنتاج</label>
                        <label><input type="radio" name="source_type" value="purchase_invoice" onchange="toggleSource()"> فاتورة شراء</label>
                    </div>
                </div>

                <div class="row" id="productionWrapper">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>أمر الإنتاج</label>
                            <select name="source_id_production" class="form-control select2">
                                <option value="">-- اختر أمر الإنتاج --</option>
                                @foreach($productionOrders as $order)
                                    <option value="{{ $order->id }}">{{ $order->order_number }} - {{ $order->item->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" id="purchaseWrapper" style="display:none">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>فاتورة الشراء</label>
                            <select name="source_id_purchase" class="form-control select2">
                                <option value="">-- اختر فاتورة الشراء --</option>
                                @foreach($purchaseInvoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} - {{ $invoice->supplier->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning card-outline" id="itemsCard" style="display:none">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود الفحص</h3></div>
            <div class="card-body p-0">
                @foreach($checklists as $checklist)
                <table class="table table-bordered mb-0 checklist-table" data-checklist="{{ $checklist->id }}" style="display:none">
                    <thead class="thead-dark"><tr><th>البند</th><th style="width:150px">النتيجة</th><th>ملاحظات</th></tr></thead>
                    <tbody>
                        @foreach($checklist->items as $item)
                        <tr>
                            <td>{{ $item->criterion }}
                                <input type="hidden" name="items[{{ $item->id }}][checklist_item_id]" value="{{ $item->id }}" disabled class="item-input">
                            </td>
                            <td>
                                <select name="items[{{ $item->id }}][result]" class="form-control form-control-sm item-input" disabled>
                                    <option value="pass">ناجح</option>
                                    <option value="fail">فشل</option>
                                    <option value="na">غير منطبق</option>
                                </select>
                            </td>
                            <td><input type="text" name="items[{{ $item->id }}][notes]" class="form-control form-control-sm item-input" disabled></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endforeach
            </div>
        </div>

        <div class="form-group mt-3">
            <label>ملاحظات عامة</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>

        <div class="card-footer bg-white border-top-0 px-0">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ الفحص</button>
            <a href="{{ route('quality_inspections.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
function toggleSource() {
    const type = document.querySelector('input[name=source_type]:checked').value;
    document.getElementById('productionWrapper').style.display = type === 'production_order' ? '' : 'none';
    document.getElementById('purchaseWrapper').style.display = type === 'purchase_invoice' ? '' : 'none';
}

$(document).ready(function () {
    $('select.select2').select2({ language: 'ar', width: '100%' });
    toggleSource();

    $('#checklistSelect').on('change', function () {
        const id = $(this).val();
        $('.checklist-table').hide().find('.item-input').prop('disabled', true);
        if (id) {
            const $table = $('.checklist-table[data-checklist=' + id + ']');
            $table.show().find('.item-input').prop('disabled', false);
            $('#itemsCard').show();
        } else {
            $('#itemsCard').hide();
        }
    });

    $('form').on('submit', function () {
        const type = document.querySelector('input[name=source_type]:checked').value;
        const sourceId = type === 'production_order' ? $('[name=source_id_production]').val() : $('[name=source_id_purchase]').val();
        $('<input>').attr({type: 'hidden', name: 'source_id'}).val(sourceId).appendTo(this);
    });
});
</script>
@endsection
