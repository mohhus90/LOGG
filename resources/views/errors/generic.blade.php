<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>خطأ {{ $code ?? '' }}</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background:#f4f6f9; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
    .box { background:#fff; border-radius:12px; padding:50px 40px; text-align:center; max-width:500px; box-shadow:0 4px 20px rgba(0,0,0,.1); }
    .icon { font-size:60px; margin-bottom:20px; }
    h1 { color:#e74c3c; font-size:1.8rem; margin-bottom:10px; }
    p { color:#555; font-size:1rem; line-height:1.6; }
    a { display:inline-block; margin-top:25px; padding:10px 28px; background:#3490dc; color:#fff; border-radius:6px; text-decoration:none; font-size:.95rem; }
    a:hover { background:#2779bd; }
  </style>
</head>
<body>
  <div class="box">
    <div class="icon">⚠️</div>
    <h1>حدث خطأ</h1>
    <p>{{ $message ?? 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.' }}</p>
    @if(isset($code) && $code)
      <small style="color:#aaa">كود الخطأ: {{ $code }}</small>
    @endif
    <br>
    <a href="javascript:history.back()">← رجوع</a>
  </div>
</body>
</html>
