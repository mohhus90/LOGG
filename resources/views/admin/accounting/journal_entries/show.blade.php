@extends('admin.layouts.accounting')
@section('title') قيد {{ $entry->entry_number }} @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('journal_entries.index') }}">القيود اليومية</a> @endsection
@section('startpage') {{ $entry->entry_number }} @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book ml-2"></i> قيد رقم {{ $entry->entry_number }}
                <span class="badge badge-{{ $entry->status_color }} mr-2">{{ $entry->status_label }}</span>
            </h3>
            <div class="card-tools">
                @if($entry->status === 'posted')
                <form action="{{ route('journal_entries.reverse', $entry->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('عكس هذا القيد؟ سيتم إنشاء قيد عكسي مقابل.')">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-undo-alt"></i> عكس القيد</button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}</div>
                <div class="col-md-3"><strong>المصدر:</strong> {{ $entry->source_module ?? 'يدوي' }}</div>
                <div class="col-md-3"><strong>المرجع:</strong> {{ $entry->reference ?? '-' }}</div>
                <div class="col-md-3"><strong>أنشأه:</strong> {{ $entry->createdBy->name ?? '-' }}</div>
            </div>
            @if($entry->description)<p><strong>البيان:</strong> {{ $entry->description }}</p>@endif
            @if($entry->reversedEntry)
                <div class="alert alert-warning">هذا القيد عكسي لقيد <a href="{{ route('journal_entries.show', $entry->reversedEntry->id) }}">{{ $entry->reversedEntry->entry_number }}</a></div>
            @endif

            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr><th>الحساب</th><th>مركز التكلفة</th><th>البيان</th><th>مدين</th><th>دائن</th></tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                    <tr>
                        <td>{{ $line->account->account_code }} - {{ $line->account->account_name }}</td>
                        <td>{{ $line->costCenter->name ?? '-' }}</td>
                        <td>{{ $line->description ?? ($line->party_name ?? '-') }}</td>
                        <td>{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                        <td>{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <td colspan="3" class="text-left">الإجمالي</td>
                        <td>{{ number_format($entry->total_debit, 2) }}</td>
                        <td>{{ number_format($entry->total_credit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
