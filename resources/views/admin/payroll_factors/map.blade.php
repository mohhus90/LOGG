@extends('admin.layouts.admin')
@section('title') ربط أعمدة {{ $client->client_name }} @endsection
@section('start') الرواتب @endsection
@section('home') <a href="{{ route('payroll.index') }}">كشف الرواتب</a> @endsection
@section('startpage') ربط الأعمدة @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                ربط أعمدة ملف {{ $client->client_name }} — {{ $month }}/{{ $year }}
            </h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info">
                حدد لكل عمود المكتشف فى الملف الحقل المقابل له فى النظام. الأعمدة التى تُترك على
                "تجاهل" لن تُستورد. يجب تحديد عمود "كود الموظف" على الأقل.
                @if(!empty($savedMapping))
                    <br><strong>تم اقتراح الربط المحفوظ من آخر استيراد لهذا العميل — راجعه قبل الحفظ.</strong>
                @endif
            </div>

            <form method="POST" action="{{ route('payroll_factors.import.store', $client->id) }}">
                @csrf
                <input type="hidden" name="stored_path" value="{{ $storedPath }}">
                <input type="hidden" name="header_row" value="{{ $headerRow }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                @foreach($headers as $colIndex => $headerText)
                                <th style="min-width:180px">{{ $headerText ?: '(عمود '.($colIndex+1).')' }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach($headers as $colIndex => $headerText)
                                <td>
                                    <select name="mapping[{{ $colIndex }}]" class="form-control form-control-sm">
                                        @foreach($targetFields as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ ($savedMapping[$colIndex] ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                @endforeach
                            </tr>
                            @foreach($previewRows as $row)
                            <tr class="text-muted small">
                                @foreach($headers as $colIndex => $headerText)
                                <td>{{ $row[$colIndex] ?? '' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i> استيراد وحفظ الربط كقالب لهذا العميل
                    </button>
                    <a class="btn btn-warning btn-lg" href="{{ route('payroll_factors.import.form', $client->id) }}">رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
