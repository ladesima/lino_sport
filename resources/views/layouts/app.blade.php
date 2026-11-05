<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lino Sport Center') | Booking Lapangan Modern</title>
    
    <!-- Favicon Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Bebas Neue -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --primary-light: #10b981;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-card: #1e293b;
            --light-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: #1e293b;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ================= INTRO SPLASH SCREEN ANIMATION ================= */
        #introSplash {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, #0f172a 0%, #060913 100%);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.8s cubic-bezier(0.77, 0, 0.175, 1), visibility 0.8s ease;
        }

        #introSplash.fade-out {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .intro-logo-box {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 24px;
        }

        .intro-logo-glow {
            position: absolute;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.5) 0%, rgba(245, 158, 11, 0.3) 50%, transparent 70%);
            filter: blur(25px);
            animation: pulse-glow 2.4s infinite alternate;
        }

        .intro-logo-img {
            width: 140px;
            height: auto;
            position: relative;
            z-index: 2;
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.7));
            animation: intro-zoom 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .intro-title {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            transform: translateY(20px);
            animation: text-reveal 0.9s 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .intro-subtitle {
            color: #94a3b8;
            font-size: 0.95rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            opacity: 0;
            transform: translateY(15px);
            animation: text-reveal 0.9s 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .intro-loader-bar {
            width: 180px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            margin-top: 28px;
            overflow: hidden;
            position: relative;
        }

        .intro-loader-progress {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #10b981, #f59e0b);
            border-radius: 999px;
            animation: load-progress 1.5s 0.3s cubic-bezier(0.65, 0, 0.35, 1) forwards;
        }

        @keyframes intro-zoom {
            0% { transform: scale(0.3) rotate(-10deg); opacity: 0; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes text-reveal {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes pulse-glow {
            0% { transform: scale(0.85); opacity: 0.4; }
            100% { transform: scale(1.3); opacity: 0.85; }
        }

        @keyframes load-progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        /* ================= NAVBAR & FOOTER STYLING (KONTRAS ELEGAN, KONTEN TETAP MENONJOL) ================= */
        .navbar-custom,
        .footer-custom {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%) !important;
            color: #ecfdf5;
            transition: all 0.3s ease;
        }

        .navbar-custom {
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 4px 16px rgba(6, 95, 70, 0.14);
        }

        .footer-custom {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }

        .navbar-logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
            background: #ffffff;
            padding: 4px 6px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .navbar-brand:hover .navbar-logo-img {
            transform: scale(1.06);
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff !important;
        }

        .nav-link {
            font-weight: 600;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.88) !important;
            padding: 8px 16px !important;
            border-radius: 999px;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.16);
        }

        /* Modern Gradient Buttons */
        .btn-gradient-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
        }

        .btn-gradient-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #0f172a;
            border: none;
            font-weight: 800;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
            transition: all 0.3s ease;
        }

        .btn-gradient-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(245, 158, 11, 0.5);
            color: #0f172a;
        }

        /* Card Modern Hover & Glow (Konten Menonjol) */
        .card-custom {
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
            border-color: #10b981;
        }

        .pulse-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
    @yield('styles')
</head>
<body>

@if(request()->routeIs('home'))
<!-- INTRO SPLASH SCREEN DENGAN ANIMASI LOGO (Tampil saat membuka Beranda) -->
<div id="introSplash">
    <div class="intro-logo-box">
        <div class="intro-logo-glow"></div>
        <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" class="intro-logo-img">
    </div>
    <div class="intro-title">LINO SPORT CENTER</div>
    <div class="intro-subtitle">Arena Padel &bull; Badminton &bull; Pickleball</div>
    <div class="intro-loader-bar">
        <div class="intro-loader-progress"></div>
    </div>
</div>
@endif

<!-- Navbar Modern -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
  <div class="container">
    <a class="navbar-brand fs-4 d-flex align-items-center gap-2" href="{{ route('home') }}">
      <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" class="navbar-logo-img">
      <span class="d-flex flex-column leading-tight">
        <span class="fw-extrabold text-white" style="line-height: 1.1;">Lino<span style="color: #fde68a;">Sport</span></span>
        <small class="text-white opacity-75 text-uppercase fw-bold" style="font-size: 0.62rem; letter-spacing: 1px;">Sport Center</small>
      </span>
    </a>

    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <i class="bi bi-list fs-2 text-white"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto my-2 my-lg-0 gap-lg-1">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
            <i class="bi bi-house-door me-1"></i> Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('booking.create') ? 'active' : '' }}" href="{{ route('booking.create') }}">
            <i class="bi bi-calendar2-plus me-1"></i> Booking Lapangan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('booking.check') ? 'active' : '' }}" href="{{ route('booking.check') }}">
            <i class="bi bi-search me-1"></i> Cek Status Booking
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('home') }}#lokasi-arena">
            <i class="bi bi-geo-alt me-1"></i> Lokasi &amp; Peta
          </a>
        </li>
        @auth
          @if(auth()->user()->isAdmin())
          <li class="nav-item">
            <a class="nav-link text-warning fw-bold" href="{{ route('admin.dashboard') }}">
              <i class="bi bi-shield-lock-fill me-1"></i> Panel Admin
            </a>
          </li>
          @endif
        @endauth
      </ul>

      <ul class="navbar-nav align-items-lg-center gap-2">
        @auth
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center py-2 px-3 rounded-pill bg-white bg-opacity-10" href="#" data-bs-toggle="dropdown">
            <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center me-2 fw-bold" style="width:28px;height:28px;font-size:0.8rem;">
              {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="fw-semibold text-white">{{ auth()->user()->name }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2">
            <li><h6 class="dropdown-header small text-muted">{{ auth()->user()->role_name }}</h6></li>
            @if(auth()->user()->isAdmin())
              <li><a class="dropdown-item rounded-3 py-2 text-warning fw-semibold" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item rounded-3 py-2 text-danger fw-semibold" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit()">
                <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
              </a>
            </li>
          </ul>
        </li>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        @else
        <li class="nav-item">
          <a class="btn btn-outline-light rounded-pill px-4 fw-semibold btn-sm" href="{{ route('login') }}">
            <i class="bi bi-lock me-1"></i> Login Petugas
          </a>
        </li>
        <li class="nav-item">
          <a class="btn btn-gradient-warning rounded-pill px-4 btn-sm" href="{{ route('booking.create') }}">
            Booking Langsung &rarr;
          </a>
        </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>

<!-- Main Container -->
<main class="py-4 py-md-5">
  <div class="container">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert" data-aos="fade-down">
      <div class="rounded-circle bg-success text-white p-2 me-3 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
        <i class="bi bi-check-lg fs-5"></i>
      </div>
      <div>{{ session('success') }}</div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert" data-aos="fade-down">
      <div class="rounded-circle bg-danger text-white p-2 me-3 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
        <i class="bi bi-x-lg fs-6"></i>
      </div>
      <div>{{ session('error') }}</div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert" data-aos="fade-down">
      <div class="rounded-circle bg-warning text-dark p-2 me-3 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
        <i class="bi bi-exclamation-lg fs-5"></i>
      </div>
      <div>{{ session('warning') }}</div>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert" data-aos="fade-down">
      <div class="d-flex align-items-start">
        <div class="rounded-circle bg-danger text-white p-2 me-3 d-flex align-items-center justify-content-center" style="width:36px;height:36px;flex-shrink:0;">
          <i class="bi bi-exclamation-triangle-fill fs-6"></i>
        </div>
        <div>
          <strong>Perhatian:</strong>
          <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    </div>
    @endif

    @yield('content')
  </div>
</main>

<!-- Footer Modern (Warna Sama Persis dengan Navbar) -->
<footer class="footer-custom py-4">
  <div class="container">
    <div class="row g-4 align-items-center mb-3">
      <div class="col-md-7 text-center text-md-start d-flex flex-column flex-sm-row align-items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" class="navbar-logo-img" style="height: 50px;">
        <div>
          <h5 class="fw-extrabold text-white mb-1" style="line-height: 1.2;">
            Lino<span style="color: #fde68a;">Sport</span> Center
          </h5>
          <p class="small mb-0" style="color: rgba(255, 255, 255, 0.85);">Platform booking lapangan olahraga praktis tanpa ribet &mdash; isi biodata, bayar instan QRIS, langsung main!</p>
        </div>
      </div>
      <div class="col-md-5 text-center text-md-end">
        <div class="d-inline-flex gap-2">
          <a href="https://wa.me/6282343797911" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;" title="Hubungi WhatsApp Admin (+62 823-4379-7911)"><i class="bi bi-whatsapp"></i></a>
          <a href="https://maps.app.goo.gl/EPSFchkw15V3V3hRA" target="_blank" class="btn btn-outline-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;" title="Petunjuk Arah Google Maps Lino Sport Center"><i class="bi bi-geo-alt-fill"></i></a>
        </div>
      </div>
    </div>
    <hr style="border-color: rgba(255, 255, 255, 0.18); opacity: 1;" class="my-3">
    <div class="row align-items-center small" style="color: rgba(255, 255, 255, 0.88);">
      <div class="col-md-6 text-center text-md-start">
        &copy; {{ date('Y') }} <strong class="text-white">Lino Sport Center</strong>. All rights reserved.
      </div>
      <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
        Buka Setiap Hari: <strong style="color: #fde68a;">08.00 - 23.00 WITA</strong>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Inisialisasi AOS (Animate On Scroll)
    AOS.init({
      duration: 700,
      easing: 'ease-out-cubic',
      once: true,
      offset: 40
    });

    // 2. Kontrol Intro Splash Screen Animasi Logo (Tampil saat membuka Beranda)
    const splash = document.getElementById('introSplash');
    if (splash) {
      const hasSeenIntro = sessionStorage.getItem('lino_intro_shown');

      if (!hasSeenIntro) {
        setTimeout(() => {
          splash.classList.add('fade-out');
          sessionStorage.setItem('lino_intro_shown', 'true');
        }, 1900); // Tampil selama 1.9 detik lalu fade-out ke halaman home
      } else {
        // Jika sudah pernah dibuka di tab ini, langsung tampilkan home tanpa animasi berulang
        splash.style.display = 'none';
      }
    }
  });
</script>
@yield('scripts')
</body>
</html>
