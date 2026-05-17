@extends('admin.layouts.admin')
@section('title') تعديل صلاحيات: {{ $targetAdmin->name }} @endsection
@section('start') الإدارة @endsection
@section('home') <a href="{{ route('admin.permissions.index') }}">صلاحيات المستخدمين</a> @endsection
@section('startpage') تعديل @endsection

@section('css')
<style>
    .permissions-table th, .permissions-table td { text-align: center; vertical-align: middle; }
    .permissions-table td:first-child { text-align: right; }
    .check-all-btn { cursor: pointer; }
    .module-icon { width: 20px; display: inline-block; }
</style>
@endsection

@section('content')
<div class="col-12">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield ml-2"></i>
                تعديل صلاحيات: <strong>{{ $targetAdmin->name }}</strong>
                <small class="text-muted">({{ $targetAdmin->email }})</small>
            </h3>
        </div>

        <form action="{{ route('admin.permissions.update', $targetAdmin->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered permissions-table mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="min-width:200px">القسم</th>
                                <th>
                                    <div>قراءة</div>
                                    <small class="text-warning check-all-btn" onclick="checkAll('can_read')">الكل</small>
                                </th>
                                <th>
                                    <div>إضافة</div>
                                    <small class="text-warning check-all-btn" onclick="checkAll('can_create')">الكل</small>
                                </th>
                                <th>
                                    <div>تعديل</div>
                                    <small class="text-warning check-all-btn" onclick="checkAll('can_update')">الكل</small>
                                </th>
                                <th>
                                    <div>حذف</div>
                                    <small class="text-warning check-all-btn" onclick="checkAll('can_delete')">الكل</small>
                                </th>
                                <th>
                                    <small class="text-info check-all-btn" onclick="checkRow()">تحديد الكل</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $module)
                            @php
                                $perm = $existingPerms[$module->id] ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <i class="{{ $module->module_icon }} module-icon text-primary"></i>
                                    {{ $module->module_name }}
                                </td>
                                <td>
                                    <input type="checkbox" class="perm-check can_read"
                                        name="permissions[{{ $module->id }}][can_read]"
                                        value="1"
                                        {{ $perm && $perm->can_read ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" class="perm-check can_create"
                                        name="permissions[{{ $module->id }}][can_create]"
                                        value="1"
                                        {{ $perm && $perm->can_create ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" class="perm-check can_update"
                                        name="permissions[{{ $module->id }}][can_update]"
                                        value="1"
                                        {{ $perm && $perm->can_update ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <input type="checkbox" class="perm-check can_delete"
                                        name="permissions[{{ $module->id }}][can_delete]"
                                        value="1"
                                        {{ $perm && $perm->can_delete ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-outline-secondary"
                                        onclick="checkRowById({{ $module->id }})">
                                        كل الصلاحيات
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save ml-1"></i> حفظ الصلاحيات
                </button>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
/* ── إصلاح أزرار الصلاحيات ── */

// تحديد/إلغاء تحديد عمود كامل
function checkAll(permType) {
  var checkboxes = document.querySelectorAll('input[name*="[' + permType + ']"]');
  var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });
  checkboxes.forEach(function(cb) { cb.checked = !allChecked; });
}

// تحديد/إلغاء تحديد كل الصلاحيات
function checkRow() {
  var all = document.querySelectorAll('.perm-check');
  var allChecked = Array.from(all).every(function(cb) { return cb.checked; });
  all.forEach(function(cb) { cb.checked = !allChecked; });
}

// تحديد/إلغاء صف قسم واحد بالكامل
function checkRowById(moduleId) {
  var row = document.querySelectorAll('input[name^="permissions[' + moduleId + ']"]');
  var allChecked = Array.from(row).every(function(cb) { return cb.checked; });
  row.forEach(function(cb) { cb.checked = !allChecked; });
}
</script>
@endsection
