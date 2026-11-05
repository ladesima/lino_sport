<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Lino Sport Center</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at center, #0f172a 0%, #060913 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            color: #f8fafc;
        }

        .login-card {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .login-card-header {
            padding: 36px 36px 20px;
            text-align: center;
        }

        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 8px 18px;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        .login-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        .login-card-body {
            padding: 10px 36px 36px;
        }

        .form-label-custom {
            font-size: 0.78rem;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #e2e8f0;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-control-custom {
            background: rgba(2, 6, 23, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
        }

        .form-control-custom:focus {
            background: rgba(2, 6, 23, 0.9) !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.25) !important;
            color: #ffffff !important;
        }

        .input-group-text-custom {
            background: rgba(2, 6, 23, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #34d399 !important;
            border-radius: 12px 0 0 12px !important;
            padding-left: 14px;
            padding-right: 14px;
        }

        .btn-admin-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            font-weight: 700;
            padding: 14px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.45);
            transition: all 0.25s ease;
        }

        .btn-admin-login:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 26px rgba(16, 185, 129, 0.55);
        }

        .badge-admin {
            background: rgba(16, 185, 129, 0.18);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 6px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 800;
        }

        .link-back {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .link-back:hover {
            color: #34d399;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-card-header">
        <div class="mb-3">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" class="login-logo">
            </div>
        </div>
        
        <span class="badge-admin d-inline-block mb-3">
            <i class="bi bi-shield-lock-fill me-1"></i> Portal User Admin
        </span>

        <h4 class="fw-bold text-white mb-2" style="font-weight: 800; letter-spacing: -0.5px;">Masuk ke Sistem Admin</h4>
        <p class="mb-0" style="color: #cbd5e1; font-size: 0.85rem; line-height: 1.5;">Portal operasional untuk verifikasi QRIS & approval jadwal booking</p>
    </div>

    <div class="login-card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 border-0 bg-danger bg-opacity-25 text-danger fw-semibold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label form-label-custom">Email</label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-custom border-end-0">
                        <i class="bi bi-envelope-fill"></i>
                    </span>
                    <input type="email" name="email" id="loginEmail" class="form-control form-control-custom border-start-0" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label form-label-custom">Password</label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-custom border-end-0">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" name="password" id="loginPassword" class="form-control form-control-custom border-start-0" required autocomplete="current-password">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" name="remember" class="form-check-input bg-dark border-secondary" id="remember">
                    <label class="form-check-label small" for="remember" style="color: #cbd5e1; cursor: pointer;">Ingat Sesi Login</label>
                </div>
            </div>

            <button type="submit" class="btn btn-admin-login w-100 fs-6">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Portal
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top" style="border-color: rgba(255, 255, 255, 0.12) !important;">
            <a href="{{ route('home') }}" class="link-back">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Website Utama
            </a>
        </div>
    </div>
</div>

</body>
</html>
