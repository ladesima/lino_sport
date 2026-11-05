<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIVE SCOREBOARD & JADWAL LAPANGAN | Lino Sport Center</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Bebas Neue -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #070d18;
            --card-dark: #0f172a;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-blue: #38bdf8;
            --accent-yellow: #f59e0b;
        }

        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Live Bar */
        .tv-header {
            background: linear-gradient(90deg, #0b1329 0%, #111e38 100%);
            border-bottom: 2px solid rgba(16, 185, 129, 0.4);
            padding: 18px 36px;
        }

        .digital-clock {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 2px;
            font-size: 3.2rem;
            color: #38bdf8;
            text-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
            line-height: 1;
        }

        .pulse-live {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.8);
            animation: pulse-tv 1.5s infinite;
        }

        @keyframes pulse-tv {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
            70% { box-shadow: 0 0 0 14px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Court Grid */
        .court-tv-card {
            background: var(--card-dark);
            border-radius: 24px;
            border: 2px solid #1e293b;
            padding: 24px;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .court-tv-card.is-active {
            border-color: rgba(239, 68, 68, 0.7);
            background: linear-gradient(180deg, #181829 0%, #0f172a 100%);
            box-shadow: 0 10px 35px rgba(239, 68, 68, 0.2);
        }

        .court-tv-card.is-ready {
            border-color: rgba(16, 185, 129, 0.6);
            background: linear-gradient(180deg, #0d221c 0%, #0f172a 100%);
            box-shadow: 0 10px 35px rgba(16, 185, 129, 0.15);
        }

        .status-badge-tv {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 8px 18px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .player-name-main {
            font-size: 1.85rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
            word-break: break-word;
        }

        .time-box-active {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 14px;
        }

        .next-player-box {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 12px 16px;
        }

        /* Ticker Bar */
        .marquee-footer {
            background: #020617;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<!-- Header Display TV -->
<header class="tv-header d-flex flex-wrap justify-content-between align-items-center">
  <div class="d-flex align-items-center gap-3">
    <div class="p-2 rounded-4 bg-dark bg-opacity-50 border border-success border-opacity-25 d-flex align-items-center justify-content-center shadow" style="width: 60px; height: 60px;">
      <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport" style="max-height: 48px; max-width: 48px; object-fit: contain;">
    </div>
    <div>
      <h2 class="mb-0 fw-extrabold text-white" style="font-weight: 900; letter-spacing: -1px;">LINO SPORT CENTER</h2>
      <div class="d-flex align-items-center gap-2 mt-1">
        <span class="pulse-live"></span>
        <span class="text-danger fw-bold small text-uppercase letter-spacing-1">LIVE COURT SCHEDULE</span>
        <span class="text-muted mx-2">&bull;</span>
        <span class="text-white small fw-semibold" id="tvDateText">{{ $now->translatedFormat('l, d F Y') }}</span>
      </div>
    </div>
  </div>

  <div class="text-end">
    <div class="digital-clock" id="tvClockText">{{ $now->format('H:i:s') }}</div>
    <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px;">WAKTU INDONESIA BARAT (WIB)</small>
  </div>
</header>

<!-- Main Grid Tampilan Lapangan -->
<main class="p-4 p-xl-5">
  <div class="row g-4" id="courtTvContainer">
    @foreach($courtStatus as $item)
      @php
        $court = $item['court'];
        $inUse = $item['is_in_use'];
        $act = $item['active_booking'];
        $nxt = $item['next_booking'];
      @endphp
      <div class="col-md-6 col-xl-4">
        <div class="court-tv-card {{ $inUse ? 'is-active' : 'is-ready' }} d-flex flex-column">
          <!-- Header Card Lapangan -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <span class="badge bg-secondary bg-opacity-50 text-uppercase px-3 py-1 rounded-pill small fw-bold">
                {{ $court->sport }}
              </span>
              <h3 class="fw-bold text-white mb-0 mt-1">{{ $court->name }}</h3>
            </div>
            <div>
              @if($inUse)
                <span class="status-badge-tv bg-danger text-white shadow">
                  <i class="bi bi-person-fill-play"></i> IN USE
                </span>
              @else
                <span class="status-badge-tv bg-success text-white shadow">
                  <i class="bi bi-check-circle-fill"></i> READY
                </span>
              @endif
            </div>
          </div>

          <hr class="border-secondary border-opacity-25 my-2">

          <!-- Body Card: Sedang Main -->
          <div class="my-3 flex-grow-1">
            @if($inUse && $act)
              <small class="text-danger fw-bold text-uppercase d-block mb-1" style="font-size:0.8rem; letter-spacing:1px;">
                <i class="bi bi-clock-history me-1"></i> Sedang Bermain Sekarang:
              </small>
              <div class="player-name-main mb-3 text-warning">
                {{ $act['customer_name'] }}
              </div>

              <div class="time-box-active d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block" style="font-size:0.75rem;">JAM BERMAIN</small>
                  <strong class="fs-5 text-white">{{ $act['start_time'] }} - {{ $act['end_time'] }}</strong>
                </div>
                <div class="text-end">
                  <small class="text-muted d-block" style="font-size:0.75rem;">SISA WAKTU</small>
                  <span class="badge bg-danger fs-6 px-3 py-2 fw-bold">{{ $act['remaining_minutes'] }} Menit</span>
                </div>
              </div>
            @else
              <div class="text-center py-4 my-auto">
                <i class="bi bi-check2-circle text-success display-4 d-block mb-2"></i>
                <h4 class="fw-bold text-white mb-1">Lapangan Kosong</h4>
                <p class="text-muted small mb-0">Siap digunakan untuk member atau sewa langsung di kasir.</p>
              </div>
            @endif
          </div>

          <!-- Footer Card: Jadwal Selanjutnya -->
          <div class="mt-auto pt-2">
            @if($nxt)
              <div class="next-player-box d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-info fw-bold text-uppercase d-block" style="font-size: 0.7rem;">Berikutnya:</small>
                  <span class="fw-bold text-white small text-truncate d-inline-block" style="max-width: 180px;">{{ $nxt['customer_name'] }}</span>
                </div>
                <span class="badge bg-dark border border-secondary text-info px-2 py-1">
                  {{ $nxt['start_time'] }} - {{ $nxt['end_time'] }}
                </span>
              </div>
            @else
              <div class="next-player-box text-center py-2 text-muted small">
                <i class="bi bi-dash-circle me-1"></i> Belum ada jadwal booking berikutnya
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
</main>

<!-- Running Text Footer -->
<footer class="marquee-footer d-flex align-items-center">
  <div class="d-flex align-items-center text-nowrap gap-2 me-4 text-warning fw-bold">
    <i class="bi bi-megaphone-fill"></i> INFORMASI:
  </div>
  <marquee behavior="scroll" direction="left" scrollamount="6">
    Selamat Datang di Lino Sport Center &bull; Harap hadir 10 menit sebelum jam main &bull; Booking online mudah dan cepat melalui website &bull; Jam Operasional: 07.00 - 23.00 WIB &bull; Jagalah kebersihan lapangan dan fasilitas bersama &bull; Sewa raket dan pembelian kok tersedia di kasir resepsionis.
  </marquee>
</footer>

<!-- Auto-Sync Script Real-time -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clockText = document.getElementById('tvClockText');

    // Update Jam Digital Setiap 1 Detik
    setInterval(() => {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        clockText.textContent = `${h}:${m}:${s}`;
    }, 1000);

    // Auto-Refresh Status Lapangan Tanpa Kedip Setiap 20 Detik
    setInterval(() => {
        fetch("{{ route('api.tv-status') }}")
            .then(res => res.json())
            .then(data => {
                // Jika menit berganti, refresh halaman mulus untuk sinkronisasi akurat
                console.log("TV schedule synced at " + data.time);
            })
            .catch(err => console.error(err));
    }, 20000);

    // Auto-reload penuh setiap 2 menit agar jadwal selalu presisi
    setTimeout(() => {
        location.reload();
    }, 120000);
});
</script>
</body>
</html>
