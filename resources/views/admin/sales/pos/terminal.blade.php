@extends('admin.layouts.sales')
@section('title') نقطة البيع — {{ $register->name }} @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos.select_register') }}">نقطة البيع</a> @endsection
@section('startpage') {{ $register->name }} @endsection

@section('content')
<div class="col-12">

@if(session('error'))
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('error') }}
  </div>
@endif
@if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
  </div>
@endif

@if(!$session)
  {{-- لا توجد جلسة مفتوحة — فورم فتح جلسة --}}
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-door-open ml-2"></i>فتح جلسة كاشير جديدة</h3></div>
        <form method="POST" action="{{ route('pos.open_session', $register->id) }}">
          @csrf
          <div class="card-body">
            <p class="text-muted">كاشير: <strong>{{ $register->name }}</strong></p>
            <div class="form-group">
              <label>الرصيد الافتتاحي (نقدية بالدرج)</label>
              <input type="number" step="0.01" min="0" class="form-control" name="opening_amount" value="0" required>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-play ml-1"></i> فتح الجلسة</button>
            <a href="{{ route('pos.select_register') }}" class="btn btn-secondary mr-2">رجوع</a>
          </div>
        </form>
      </div>
    </div>
  </div>

@else
  {{-- شاشة البيع --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <span class="badge badge-success p-2"><i class="fas fa-circle ml-1" style="font-size:8px"></i>جلسة مفتوحة #{{ $session->id }}</span>
      <span class="text-muted mr-2">افتتاحية: {{ number_format($session->opening_amount, 2) }}</span>
    </div>
    <a href="{{ route('pos_sessions.close_form', $session->id) }}" class="btn btn-sm btn-outline-danger">
      <i class="fas fa-door-closed ml-1"></i> إغلاق الجلسة
    </a>
  </div>

  <div class="row">
    {{-- بحث الأصناف --}}
    <div class="col-md-7">
      <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-search ml-2"></i>بحث عن صنف</h3></div>
        <div class="card-body">
          <input type="text" id="itemSearch" class="form-control mb-2" placeholder="اكتب اسم الصنف أو الكود..." autofocus>
          <div id="itemResults" class="list-group" style="max-height:420px;overflow-y:auto"></div>
        </div>
      </div>
    </div>

    {{-- السلة --}}
    <div class="col-md-5">
      <div class="card card-success card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-basket ml-2"></i>سلة البيع</h3></div>
        <div class="card-body p-2">
          <div class="form-group">
            <label class="small">العميل</label>
            <select class="form-control form-control-sm" id="customerSelect">
              <option value="{{ $walkIn->id }}">{{ $walkIn->name }} (نقدي)</option>
              @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>

          <table class="table table-sm table-bordered mb-2">
            <thead class="thead-light">
              <tr><th>الصنف</th><th style="width:70px">كمية</th><th style="width:90px">سعر</th><th style="width:90px">إجمالي</th><th style="width:30px"></th></tr>
            </thead>
            <tbody id="cartBody">
              <tr id="emptyCartRow"><td colspan="5" class="text-center text-muted py-3">السلة فارغة</td></tr>
            </tbody>
          </table>

          <div class="row">
            <div class="col-6 form-group">
              <label class="small">خصم (مبلغ)</label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="discountAmount" value="0">
            </div>
            <div class="col-6 form-group">
              <label class="small">ضريبة %</label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="taxRate" value="14">
            </div>
          </div>

          <table class="table table-sm mb-2">
            <tr><td>الإجمالي الفرعي</td><td class="text-left" id="sumSubtotal">0.00</td></tr>
            <tr><td>الخصم</td><td class="text-left" id="sumDiscount">0.00</td></tr>
            <tr><td>الضريبة</td><td class="text-left" id="sumTax">0.00</td></tr>
            <tr class="font-weight-bold"><td>الإجمالي</td><td class="text-left" id="sumTotal">0.00</td></tr>
          </table>

          <button type="button" class="btn btn-success btn-block btn-lg" id="checkoutBtn" disabled>
            <i class="fas fa-cash-register ml-1"></i> إتمام البيع
          </button>
        </div>
      </div>
    </div>
  </div>
@endif

</div>
@endsection

@section('script')
<script>
(function () {
  if (!document.getElementById('checkoutBtn')) return; // لا توجد جلسة مفتوحة

  var cart = [];
  var registerId = {{ $register->id }};
  var searchUrl  = '{{ route('items.ajax.search') }}';
  var checkoutUrl = '{{ route('pos.checkout') }}';
  var csrf = '{{ csrf_token() }}';

  document.getElementById('itemSearch').addEventListener('input', function () {
    var q = this.value.trim();
    if (q.length < 1) { document.getElementById('itemResults').innerHTML = ''; return; }
    fetch(searchUrl + '?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(renderResults);
  });

  function renderResults(items) {
    var box = document.getElementById('itemResults');
    if (!items.length) { box.innerHTML = '<div class="text-muted p-2">لا توجد نتائج</div>'; return; }
    box.innerHTML = items.map(function (it) {
      return '<a href="#" class="list-group-item list-group-item-action py-2" ' +
        'onclick="event.preventDefault(); addToCart(' + it.id + ', ' + JSON.stringify(it.name) + ', ' +
        (it.selling_price || 0) + ', ' + (it.unit_id || 'null') + ')">' +
        '<strong>' + it.name + '</strong> <span class="text-muted small">(' + (it.code || '') + ')</span>' +
        '<span class="float-left text-success">' + Number(it.selling_price || 0).toFixed(2) + '</span>' +
        '</a>';
    }).join('');
  }

  window.addToCart = function (itemId, name, price, unitId) {
    var existing = cart.find(function (c) { return c.item_id === itemId; });
    if (existing) { existing.qty += 1; }
    else { cart.push({ item_id: itemId, name: name, price: price, unit_id: unitId, qty: 1, discount_percent: 0 }); }
    renderCart();
  };

  window.removeFromCart = function (idx) { cart.splice(idx, 1); renderCart(); };
  window.updateQty = function (idx, val) { cart[idx].qty = Math.max(0.001, parseFloat(val) || 0); renderCart(); };
  window.updatePrice = function (idx, val) { cart[idx].price = Math.max(0, parseFloat(val) || 0); renderCart(); };

  function renderCart() {
    var body = document.getElementById('cartBody');
    if (!cart.length) {
      body.innerHTML = '<tr id="emptyCartRow"><td colspan="5" class="text-center text-muted py-3">السلة فارغة</td></tr>';
      document.getElementById('checkoutBtn').disabled = true;
    } else {
      body.innerHTML = cart.map(function (c, idx) {
        var lineTotal = c.qty * c.price * (1 - (c.discount_percent || 0) / 100);
        return '<tr>' +
          '<td>' + c.name + '</td>' +
          '<td><input type="number" step="0.01" min="0.001" class="form-control form-control-sm" value="' + c.qty + '" onchange="updateQty(' + idx + ', this.value)"></td>' +
          '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm" value="' + c.price + '" onchange="updatePrice(' + idx + ', this.value)"></td>' +
          '<td class="text-left">' + lineTotal.toFixed(2) + '</td>' +
          '<td><button type="button" class="btn btn-xs btn-danger" onclick="removeFromCart(' + idx + ')"><i class="fas fa-times"></i></button></td>' +
          '</tr>';
      }).join('');
      document.getElementById('checkoutBtn').disabled = false;
    }
    updateTotals();
  }

  function updateTotals() {
    var subtotal = cart.reduce(function (s, c) { return s + c.qty * c.price * (1 - (c.discount_percent || 0) / 100); }, 0);
    var discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
    var taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
    var taxable = Math.max(0, subtotal - discountAmount);
    var tax = taxable * taxRate / 100;
    var total = taxable + tax;

    document.getElementById('sumSubtotal').innerText = subtotal.toFixed(2);
    document.getElementById('sumDiscount').innerText = discountAmount.toFixed(2);
    document.getElementById('sumTax').innerText = tax.toFixed(2);
    document.getElementById('sumTotal').innerText = total.toFixed(2);
  }

  document.getElementById('discountAmount').addEventListener('input', updateTotals);
  document.getElementById('taxRate').addEventListener('input', updateTotals);

  document.getElementById('checkoutBtn').addEventListener('click', function () {
    if (!cart.length) return;
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i> جاري التنفيذ...';

    fetch(checkoutUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify({
        register_id: registerId,
        customer_id: document.getElementById('customerSelect').value,
        discount_amount: parseFloat(document.getElementById('discountAmount').value) || 0,
        tax_rate: parseFloat(document.getElementById('taxRate').value) || 0,
        items: cart.map(function (c) {
          return { item_id: c.item_id, unit_id: c.unit_id, qty: c.qty, price: c.price, discount_percent: c.discount_percent || 0 };
        })
      })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        window.open(data.print_url, '_blank');
        cart = [];
        document.getElementById('discountAmount').value = 0;
        document.getElementById('itemSearch').value = '';
        document.getElementById('itemResults').innerHTML = '';
        renderCart();
      } else {
        alert(data.message || 'حدث خطأ أثناء تنفيذ عملية البيع');
      }
    })
    .catch(function () { alert('فشل الاتصال بالخادم'); })
    .finally(function () {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-cash-register ml-1"></i> إتمام البيع';
    });
  });
})();
</script>
@endsection
