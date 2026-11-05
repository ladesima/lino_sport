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
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .login-card-header {
            padding: 36px 36px 20px;
            text-align: center;
        }

        .login-logo {
            height: 68px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(16, 185, 129, 0.3));
        }

        .login-card-body {
            padding: 10px 36px 36px;
        }

        .form-control {
            background: rgba(2, 6, 23, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background: rgba(2, 6, 23, 0.85);
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .input-group-text {
            background: rgba(2, 6, 23, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #94a3b8;
            border-radius: 12px 0 0 12px;
        }

        .btn-admin-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            font-weight: 700;
            padding: 14px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
            transition: all 0.25s ease;
        }

        .btn-admin-login:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.5);
        }

        .badge-admin {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 6px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 800;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-card-header">
        <div class="mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" class="login-logo">
        </div>
        <span class="badge-admin d-inline-block mb-2">
            <i class="bi bi-shield-lock-fill me-1"></i> Portal User Admin
        </span>
        <h4 class="fw-extrabold text-white mb-1" style="font-weight: 800;">Masuk ke Sistem Admin</h4>
        <p class="text-muted small mb-0">Portal operasional untuk verifikasi QRIS & approval jadwal booking</p>
    </div>

    <div class="login-card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 border-0 bg-danger bg-opacity-25 text-danger">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">Email Login Admin</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="loginEmail" class="form-control border-start-0" value="{{ old('email') }}" required autofocus placeholder="admin@lino.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="loginPassword" class="form-control border-start-0" required placeholder="••••••••">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" name="remember" class="form-check-input bg-dark border-secondary" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">Ingat Sesi Login</label>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.7rem;" onclick="document.getElementById('loginEmail').value='admin@lino.com';document.getElementById('loginPassword').value='admin123';">Isi Demo Admin</button>
                </div>
            </div>

            <button type="submit" class="btn btn-admin-login w-100 fs-6">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Portal
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
            <a href="{{ route('home') }}" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Website Utama
            </a>
        </div>
    </div>
</div>

</body>
</html>
