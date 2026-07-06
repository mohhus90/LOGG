<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXA ERP — اختر الموديول</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 RTL --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary:     #6366f1;
            --bg-dark:     #0b0f1a;
            --bg-card:     rgba(255,255,255,0.04);
            --border:      rgba(255,255,255,0.08);
            --text-muted:  rgba(255,255,255,0.45);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-dark);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== ANIMATED BACKGROUND ===== */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
            pointer-events: none;
        }
        .bg-glow-1 {
            position: fixed;
            width: 600px; height: 600px;
            top: -200px; right: -200px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow-2 {
            position: fixed;
            width: 500px; height: 500px;
            bottom: -150px; left: -150px;
            background: radial-gradient(circle, rgba(20,184,166,0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* ===== NAVBAR ===== */
        .erp-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(11,15,26,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .brand-logo {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -1px;
            box-shadow: 0 0 20px rgba(99,102,241,0.4);
        }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }
        .brand-sub {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .nav-user .badge-super {
            background: rgba(251,191,36,.15);
            color: #fbbf24;
            border: 1px solid rgba(251,191,36,.3);
            border-radius: 100px;
            font-size: 11px;
            padding: 2px 10px;
            font-weight: 700;
        }
        .nav-login-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff !important;
            border-radius: 10px;
            padding: 8px 22px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all .2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
        }
        .nav-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        }

        /* ===== HERO ===== */
        .hero {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 80px 20px 60px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.3);
            color: #a5b4fc;
            border-radius: 100px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 28px;
        }
        .hero-badge .dot {
            width: 7px; height: 7px;
            background: #6366f1;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(1.4); }
        }
        .hero h1 {
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
        }
        .hero h1 .gradient-text {
            background: linear-gradient(135deg, #6366f1, #a78bfa, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            color: var(--text-muted);
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 50px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-num {
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .section-title p {
            color: var(--text-muted);
            font-size: 16px;
        }
        .section-divider {
            width: 60px; height: 3px;
            background: linear-gradient(90deg, #6366f1, #14b8a6);
            border-radius: 2px;
            margin: 12px auto 0;
        }

        /* ===== MODULE CARDS ===== */
        .modules-section {
            position: relative;
            z-index: 1;
            padding: 20px 0 80px;
        }
        .module-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            transition: all .3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            display: block;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .module-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            opacity: 0;
            transition: opacity .3s;
        }
        .module-card:hover::before { opacity: 1; }
        .module-card:hover {
            transform: translateY(-6px);
            border-color: transparent;
            color: #fff;
            text-decoration: none;
        }
        .module-card.active-module:hover {
            box-shadow: 0 20px 50px -10px var(--module-color, rgba(99,102,241,.4));
        }
        .module-card.active-module {
            border-color: rgba(255,255,255,0.12);
        }
        .module-card.active-module:hover {
            border-color: var(--module-color, #6366f1);
        }
        .module-card.coming-soon {
            opacity: 0.7;
            cursor: default;
        }
        .module-card.coming-soon:hover {
            transform: translateY(-2px);
        }

        .module-icon-wrap {
            width: 70px; height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            position: relative;
            z-index: 1;
            transition: transform .3s;
        }
        .module-card:hover .module-icon-wrap {
            transform: scale(1.1);
        }
        .module-title {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
        }
        .module-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }
        .module-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 100px;
            position: relative;
            z-index: 1;
        }
        .badge-active {
            background: rgba(16,185,129,0.15);
            color: #34d399;
            border: 1px solid rgba(16,185,129,0.3);
        }
        .badge-soon {
            background: rgba(255,255,255,0.06);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .module-features {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        .feature-tag {
            font-size: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 3px 8px;
            border-radius: 6px;
        }

        /* Module-specific colors */
        .mod-hr     { --module-color: rgba(99,102,241,.5);  }
        .mod-sales  { --module-color: rgba(16,185,129,.5);  }
        .mod-acc    { --module-color: rgba(59,130,246,.5);  }
        .mod-inv    { --module-color: rgba(245,158,11,.5);  }
        .mod-prod   { --module-color: rgba(239,68,68,.5);   }
        .mod-crm    { --module-color: rgba(20,184,166,.5);  }
        .mod-purch  { --module-color: rgba(139,92,246,.5);  }
        .mod-asset  { --module-color: rgba(107,114,128,.5); }
        .mod-proj   { --module-color: rgba(249,115,22,.5);  }
        .mod-qual   { --module-color: rgba(6,182,212,.5);   }
        .mod-bi     { --module-color: rgba(236,72,153,.5);  }
        .mod-doc    { --module-color: rgba(234,179,8,.5);   }
        .mod-treasury { --module-color: rgba(202,138,4,.5); }

        .icon-hr     { background: rgba(99,102,241,.15);   color: #818cf8; }
        .icon-sales  { background: rgba(16,185,129,.15);  color: #34d399; }
        .icon-acc    { background: rgba(59,130,246,.15);   color: #60a5fa; }
        .icon-inv    { background: rgba(245,158,11,.15);   color: #fbbf24; }
        .icon-prod   { background: rgba(239,68,68,.15);    color: #f87171; }
        .icon-crm    { background: rgba(20,184,166,.15);   color: #2dd4bf; }
        .icon-purch  { background: rgba(139,92,246,.15);   color: #c084fc; }
        .icon-asset  { background: rgba(107,114,128,.15);  color: #9ca3af; }
        .icon-proj   { background: rgba(249,115,22,.15);   color: #fb923c; }
        .icon-qual   { background: rgba(6,182,212,.15);    color: #22d3ee; }
        .icon-bi     { background: rgba(236,72,153,.15);   color: #f472b6; }
        .icon-doc    { background: rgba(234,179,8,.15);    color: #facc15; }
        .icon-treasury { background: rgba(202,138,4,.15);  color: #eab308; }

        /* Active module glow on hover */
        .active-module.mod-hr:hover     { box-shadow: 0 20px 50px -10px rgba(99,102,241,.4),  0 0 0 1px rgba(99,102,241,.3); }
        .active-module.mod-sales:hover  { box-shadow: 0 20px 50px -10px rgba(16,185,129,.4),  0 0 0 1px rgba(16,185,129,.3); }
        .active-module.mod-purch:hover  { box-shadow: 0 20px 50px -10px rgba(139,92,246,.4),  0 0 0 1px rgba(139,92,246,.3); }
        .active-module.mod-inv:hover    { box-shadow: 0 20px 50px -10px rgba(245,158,11,.4),  0 0 0 1px rgba(245,158,11,.3); }

        /* ===== FEATURES STRIP ===== */
        .features-strip {
            position: relative;
            z-index: 1;
            padding: 60px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,0.015);
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 16px;
        }
        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .feature-icon.fi-1 { background: rgba(99,102,241,.15);  color: #818cf8; }
        .feature-icon.fi-2 { background: rgba(16,185,129,.15);  color: #34d399; }
        .feature-icon.fi-3 { background: rgba(245,158,11,.15);  color: #fbbf24; }
        .feature-icon.fi-4 { background: rgba(239,68,68,.15);   color: #f87171; }
        .feature-icon.fi-5 { background: rgba(6,182,212,.15);   color: #22d3ee; }
        .feature-icon.fi-6 { background: rgba(236,72,153,.15);  color: #f472b6; }
        .feature-text h4 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .feature-text p  { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* ===== ROADMAP ===== */
        .roadmap-section {
            position: relative;
            z-index: 1;
            padding: 80px 0;
        }
        .roadmap-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px 24px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            margin-bottom: 12px;
            transition: all .2s;
        }
        .roadmap-item:hover {
            border-color: rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
        }
        .roadmap-num {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
        }
        .rm-done  { background: rgba(16,185,129,.2); color: #34d399; }
        .rm-next  { background: rgba(99,102,241,.2); color: #818cf8; }
        .rm-plan  { background: rgba(255,255,255,.05); color: var(--text-muted); }
        .roadmap-info { flex: 1; }
        .roadmap-info h5 { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .roadmap-info p  { font-size: 12px; color: var(--text-muted); }
        .roadmap-status {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 100px;
        }
        .rs-done { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(16,185,129,.3); }
        .rs-next { background: rgba(99,102,241,.15); color: #818cf8; border: 1px solid rgba(99,102,241,.3); }
        .rs-plan { background: rgba(255,255,255,.05); color: var(--text-muted); border: 1px solid var(--border); }

        /* ===== FOOTER ===== */
        .erp-footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 40px 0;
            text-align: center;
        }
        .footer-logo { font-size: 24px; font-weight: 900; margin-bottom: 8px; }
        .footer-logo span { color: var(--text-muted); font-weight: 400; font-size: 14px; }
        .footer-copy { color: var(--text-muted); font-size: 13px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero { padding: 50px 20px 40px; }
            .hero-stats { gap: 24px; }
            .stat-num { font-size: 28px; }
        }

        /* ===== SCROLL REVEAL ANIMATION ===== */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Card counter animation */
        .count-up { display: inline-block; }
    </style>
</head>
<body>

{{-- Decorative background --}}
<div class="bg-grid"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

{{-- ===================== NAVBAR ===================== --}}
<nav class="erp-nav">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <div class="nav-brand">
                <div class="brand-logo">N</div>
                <div>
                    <div class="brand-name">NEXA ERP</div>
                    <div class="brand-sub">نظام تخطيط الموارد المؤسسية</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="nav-user">
                    <i class="fas fa-user-circle"></i>
                    {{ Auth::guard('admin')->user()->name }}
                    @if(Auth::guard('admin')->user()->is_super_admin)
                        <span class="badge-super">سوبر أدمن</span>
                    @endif
                </div>
                <form action="{{ route('admin.dashboard.logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" class="nav-login-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        خروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ===================== HERO ===================== --}}
<section class="hero">
    <div class="hero-badge">
        <span class="dot"></span>
        النظام جاهز للاستخدام — الموارد البشرية والمبيعات والمشتريات والمخازن مفعّلة بالكامل
    </div>
    <h1>
        اختر <span class="gradient-text">الموديول</span><br>
        الذي تريد الدخول إليه
    </h1>
    <p>
        كل موديول مستقل بواجهته وقوائمه — مع تكامل كامل في البيانات بين جميع أقسام النظام.
    </p>

    <div class="hero-stats reveal">
        <div class="stat-item">
            <span class="stat-num" data-target="12">0</span>
            <div class="stat-label">موديول متكامل</div>
        </div>
        <div class="stat-item">
            <span class="stat-num" data-target="47">0</span>
            <div class="stat-label">نموذج بيانات</div>
        </div>
        <div class="stat-item">
            <span class="stat-num" data-target="35">0</span>
            <div class="stat-label">وحدة تحكم إدارية</div>
        </div>
        <div class="stat-item">
            <span class="stat-num" data-target="128">0</span>
            <div class="stat-label">شاشة وتقرير</div>
        </div>
    </div>
</section>

{{-- ===================== MODULES ===================== --}}
<section class="modules-section">
    <div class="container">
        <div class="section-title reveal">
            <h2>موديولات النظام</h2>
            <p>اختر الموديول الذي تريد الدخول إليه</p>
            <div class="section-divider"></div>
        </div>

        <div class="row g-4">

            {{-- 1. HR --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('employees.index') }}" class="module-card active-module mod-hr">
                    <div class="module-icon-wrap icon-hr">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="module-title">الموارد البشرية</div>
                    <div class="module-subtitle">HR Management</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">الموظفون</span>
                        <span class="feature-tag">الحضور</span>
                        <span class="feature-tag">الرواتب</span>
                        <span class="feature-tag">الإجازات</span>
                        <span class="feature-tag">KPI</span>
                        <span class="feature-tag">البصمة</span>
                    </div>
                </a>
            </div>

            {{-- 2. Sales --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('sales_reports.index') }}" class="module-card active-module mod-sales">
                    <div class="module-icon-wrap icon-sales">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="module-title">المبيعات</div>
                    <div class="module-subtitle">Sales Management</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">عروض الأسعار</span>
                        <span class="feature-tag">أوامر البيع</span>
                        <span class="feature-tag">الفواتير</span>
                        <span class="feature-tag">العملاء</span>
                    </div>
                </a>
            </div>

            {{-- 3. Purchasing --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('purchase_reports.index') }}" class="module-card active-module mod-purch">
                    <div class="module-icon-wrap icon-purch">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="module-title">المشتريات</div>
                    <div class="module-subtitle">Purchasing</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">طلبات الشراء</span>
                        <span class="feature-tag">أوامر الشراء</span>
                        <span class="feature-tag">الموردون</span>
                        <span class="feature-tag">الفواتير</span>
                    </div>
                </a>
            </div>

            {{-- 4. Accounting --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('accounting_reports.index') }}" class="module-card active-module mod-acc">
                    <div class="module-icon-wrap icon-acc">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="module-title">الحسابات</div>
                    <div class="module-subtitle">Accounting</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">دليل الحسابات</span>
                        <span class="feature-tag">القيود</span>
                        <span class="feature-tag">الميزانية</span>
                        <span class="feature-tag">ETA</span>
                    </div>
                </a>
            </div>

            {{-- 4b. Treasury --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('treasury_reports.index') }}" class="module-card active-module mod-treasury">
                    <div class="module-icon-wrap icon-treasury">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div class="module-title">الخزينة</div>
                    <div class="module-subtitle">Treasury</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">سندات القبض</span>
                        <span class="feature-tag">سندات الصرف</span>
                        <span class="feature-tag">الشيكات</span>
                        <span class="feature-tag">البنوك</span>
                    </div>
                </a>
            </div>

            {{-- 5. Inventory --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('inventory_reports.index') }}" class="module-card active-module mod-inv">
                    <div class="module-icon-wrap icon-inv">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="module-title">المخازن والمستودعات</div>
                    <div class="module-subtitle">Inventory Management</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">المخازن</span>
                        <span class="feature-tag">حركة الأصناف</span>
                        <span class="feature-tag">التسويات</span>
                        <span class="feature-tag">التنبيهات</span>
                    </div>
                </a>
            </div>

            {{-- 5. Production --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('manufacturing_reports.index') }}" class="module-card active-module mod-prod">
                    <div class="module-icon-wrap icon-prod">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="module-title">الإنتاج والتصنيع</div>
                    <div class="module-subtitle">Manufacturing</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">أوامر الإنتاج</span>
                        <span class="feature-tag">قوائم المواد BOM</span>
                        <span class="feature-tag">صرف/استلام</span>
                        <span class="feature-tag">التكاليف</span>
                    </div>
                </a>
            </div>

            {{-- 6. CRM --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <div class="module-card coming-soon mod-crm">
                    <div class="module-icon-wrap icon-crm">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="module-title">إدارة علاقات العملاء</div>
                    <div class="module-subtitle">CRM</div>
                    <span class="module-badge badge-soon">
                        <i class="fas fa-clock" style="font-size:9px; margin-left:5px;"></i>
                        قريباً
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">العملاء المحتملون</span>
                        <span class="feature-tag">الفرص</span>
                        <span class="feature-tag">المتابعة</span>
                        <span class="feature-tag">الحملات</span>
                    </div>
                </div>
            </div>

            {{-- 8. Fixed Assets --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('asset_reports.index') }}" class="module-card active-module mod-asset">
                    <div class="module-icon-wrap icon-asset">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="module-title">الأصول الثابتة</div>
                    <div class="module-subtitle">Fixed Assets</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">سجل الأصول</span>
                        <span class="feature-tag">الاستهلاك</span>
                        <span class="feature-tag">النقل</span>
                        <span class="feature-tag">التخلص</span>
                    </div>
                </a>
            </div>

            {{-- 9. Projects --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <div class="module-card coming-soon mod-proj">
                    <div class="module-icon-wrap icon-proj">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="module-title">إدارة المشاريع</div>
                    <div class="module-subtitle">Project Management</div>
                    <span class="module-badge badge-soon">
                        <i class="fas fa-clock" style="font-size:9px; margin-left:5px;"></i>
                        قريباً
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">المشاريع</span>
                        <span class="feature-tag">المهام</span>
                        <span class="feature-tag">الجانت</span>
                        <span class="feature-tag">التكاليف</span>
                    </div>
                </div>
            </div>

            {{-- 10. Quality --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <a href="{{ route('quality_reports.index') }}" class="module-card active-module mod-qual">
                    <div class="module-icon-wrap icon-qual">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="module-title">ضبط الجودة</div>
                    <div class="module-subtitle">Quality Control</div>
                    <span class="module-badge badge-active">
                        <i class="fas fa-circle" style="font-size:7px; margin-left:5px;"></i>
                        مفعّل
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">قوالب الفحص</span>
                        <span class="feature-tag">الفحص</span>
                        <span class="feature-tag">أوامر الإنتاج</span>
                        <span class="feature-tag">التقارير</span>
                    </div>
                </a>
            </div>

            {{-- 11. BI & Analytics --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <div class="module-card coming-soon mod-bi">
                    <div class="module-icon-wrap icon-bi">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="module-title">التقارير والتحليلات</div>
                    <div class="module-subtitle">BI & Analytics</div>
                    <span class="module-badge badge-soon">
                        <i class="fas fa-clock" style="font-size:9px; margin-left:5px;"></i>
                        قريباً
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">لوحات التحكم</span>
                        <span class="feature-tag">التقارير</span>
                        <span class="feature-tag">الرسوم البيانية</span>
                        <span class="feature-tag">التصدير</span>
                    </div>
                </div>
            </div>

            {{-- 12. Document Management --}}
            <div class="col-xl-3 col-lg-4 col-md-6 reveal">
                <div class="module-card coming-soon mod-doc">
                    <div class="module-icon-wrap icon-doc">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="module-title">إدارة الوثائق</div>
                    <div class="module-subtitle">Document Management</div>
                    <span class="module-badge badge-soon">
                        <i class="fas fa-clock" style="font-size:9px; margin-left:5px;"></i>
                        قريباً
                    </span>
                    <div class="module-features">
                        <span class="feature-tag">الأرشفة</span>
                        <span class="feature-tag">الموافقات</span>
                        <span class="feature-tag">الإصدارات</span>
                        <span class="feature-tag">البحث</span>
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}
    </div>
</section>

{{-- ===================== FEATURES STRIP ===================== --}}
<section class="features-strip">
    <div class="container">
        <div class="section-title reveal" style="margin-bottom:40px;">
            <h2>لماذا NEXA ERP؟</h2>
            <p>مصمم خصيصاً للبيئة المصرية</p>
            <div class="section-divider"></div>
        </div>
        <div class="row g-3">
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-1"><i class="fas fa-language"></i></div>
                    <div class="feature-text">
                        <h4>عربي بالكامل</h4>
                        <p>واجهة RTL احترافية بخط Cairo، وتقارير بالاسم العربي والإنجليزي</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-2"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="feature-text">
                        <h4>متوافق مع هيئة الضرائب المصرية</h4>
                        <p>تكامل مباشر مع منظومة الفاتورة الإلكترونية ETA ونموذج 41</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-3"><i class="fas fa-fingerprint"></i></div>
                    <div class="feature-text">
                        <h4>تكامل البصمة ZKTeco</h4>
                        <p>مزامنة تلقائية مع أجهزة البصمة ZKTeco في جميع الفروع</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-4"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-text">
                        <h4>صلاحيات متقدمة</h4>
                        <p>نظام RBAC كامل بصلاحيات قراءة/إضافة/تعديل/حذف لكل موديول</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-5"><i class="fas fa-code-branch"></i></div>
                    <div class="feature-text">
                        <h4>متعدد الفروع والشركات</h4>
                        <p>إدارة شركات ومصانع متعددة من نظام واحد مع فصل كامل للبيانات</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="feature-item">
                    <div class="feature-icon fi-6"><i class="fas fa-file-excel"></i></div>
                    <div class="feature-text">
                        <h4>Excel Import/Export</h4>
                        <p>استيراد وتصدير البيانات بتنسيق Excel لجميع الموديولات</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== ROADMAP ===================== --}}
<section class="roadmap-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="section-title reveal">
                    <h2>خارطة تطوير النظام</h2>
                    <p>المراحل المنجزة والمخطط لها</p>
                    <div class="section-divider"></div>
                </div>

                <div class="reveal">
                    <div class="roadmap-item">
                        <div class="roadmap-num rm-done"><i class="fas fa-check"></i></div>
                        <div class="roadmap-info">
                            <h5>المرحلة الأولى — الموارد البشرية</h5>
                            <p>الموظفون • الحضور • الرواتب • الإجازات • KPI • البصمة • الضرائب</p>
                        </div>
                        <span class="roadmap-status rs-done">مكتمل</span>
                    </div>

                    <div class="roadmap-item">
                        <div class="roadmap-num rm-done">2</div>
                        <div class="roadmap-info">
                            <h5>المرحلة الثانية — المبيعات</h5>
                            <p>عروض الأسعار • أوامر البيع • الفواتير • المدفوعات • المرتجعات • التقارير</p>
                        </div>
                        <span class="roadmap-status rs-done">مكتملة</span>
                    </div>
                    <div class="roadmap-item">
                        <div class="roadmap-num rm-done">3</div>
                        <div class="roadmap-info">
                            <h5>المرحلة الثالثة — المشتريات</h5>
                            <p>طلبات الشراء • أوامر الشراء • الموردون • الفواتير • المدفوعات • المرتجعات</p>
                        </div>
                        <span class="roadmap-status rs-done">مكتملة</span>
                    </div>

                    <div class="roadmap-item">
                        <div class="roadmap-num rm-done">4</div>
                        <div class="roadmap-info">
                            <h5>المرحلة الرابعة — المخازن</h5>
                            <p>المخازن • أرصدة المخزون • حركة الأصناف • التسويات • التحويلات • التنبيهات</p>
                        </div>
                        <span class="roadmap-status rs-done">مكتملة</span>
                    </div>

                    <div class="roadmap-item">
                        <div class="roadmap-num rm-next">5</div>
                        <div class="roadmap-info">
                            <h5>المرحلة الخامسة — الحسابات</h5>
                            <p>دليل الحسابات • القيود اليومية • الميزانية العمومية</p>
                        </div>
                        <span class="roadmap-status rs-next">التالية</span>
                    </div>

                    <div class="roadmap-item">
                        <div class="roadmap-num rm-plan">6</div>
                        <div class="roadmap-info">
                            <h5>المرحلة السادسة — الإنتاج والجودة</h5>
                            <p>أوامر التصنيع • وصفات الإنتاج • خطوط التشغيل • مراقبة الجودة</p>
                        </div>
                        <span class="roadmap-status rs-plan">مخطط</span>
                    </div>

                    <div class="roadmap-item">
                        <div class="roadmap-num rm-plan">7</div>
                        <div class="roadmap-info">
                            <h5>المرحلة السابعة — CRM والتقارير المتقدمة</h5>
                            <p>إدارة العلاقات • الحملات التسويقية • لوحات BI التحليلية</p>
                        </div>
                        <span class="roadmap-status rs-plan">مخطط</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== FOOTER ===================== --}}
<footer class="erp-footer">
    <div class="container">
        <div class="footer-logo">
            NEXA <span>ERP System</span>
        </div>
        <p class="footer-copy mt-2">
            نظام تخطيط الموارد المؤسسية — مصمم للشركات والمصانع المصرية
            &nbsp;|&nbsp;
            جميع الحقوق محفوظة © {{ date('Y') }}
        </p>
    </div>
</footer>

{{-- ===================== SCRIPTS ===================== --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll reveal
    const revealEls = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    revealEls.forEach(el => observer.observe(el));

    // Count-up animation for stats
    function animateCount(el, target, duration = 1500) {
        let start = 0;
        const step = target / (duration / 16);
        const timer = setInterval(() => {
            start += step;
            if (start >= target) {
                el.textContent = target + '+';
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(start);
            }
        }, 16);
    }

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                document.querySelectorAll('.stat-num').forEach(el => {
                    animateCount(el, parseInt(el.dataset.target));
                });
                statsObserver.disconnect();
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.hero-stats');
    if (statsSection) statsObserver.observe(statsSection);
</script>
</body>
</html>
