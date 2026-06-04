<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'LOGG HR'))</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Bootstrap 4 RTL -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('assets/admin/dist/css/adminlte.min.css') }}">
    <!-- Custom Theme (no npm needed) -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/logg-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/mycustomstyle.css') }}">

    <style>
        body { font-family: 'Cairo', 'Source Sans Pro', sans-serif !important; }
    </style>
    @yield('css')
</head>
<body class="hold-transition">
    @yield('content')

    <!-- jQuery -->
    <script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @yield('script')
</body>
</html>
