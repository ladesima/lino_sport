<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Struk Lunas #{{ $booking->formatted_invoice_no }} - Lino Sport Center</title>

  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --mono-font: 'Space Mono', 'Courier New', Courier, monospace;
    }

    body {
      background-color: #e5e5e5;
      font-family: var(--mono-font);
      color: #000000;
      margin: 0;
      padding: 24px 12px;
      -webkit-font-smoothing: antialiased;
    }

    /* Bar Kontrol Cetak (Tidak ikut tercetak) */
    .no-print-bar {
      max-width: 440px;
      margin: 0 auto 18px auto;
      background: #ffffff;
      padding: 12px 16px;
      border-radius: 10px;
      border: 1px solid #000000;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      font-family: system-ui, -apple-system, sans-serif;
    }

    /* Kertas Struk Hitam Putih Murni */
    .receipt-wrapper {
      max-width: 320px;
      margin: 0 auto;
      background: #ffffff;
      color: #000000;
      padding: 18px 14px;
      border: 1px solid #000000;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
      font-size: 0.82rem;
    }

    .receipt-wrapper.mode-80mm {
      max-width: 380px;
      padding: 26px 22px;
      font-size: 0.88rem;
    }

    .receipt-wrapper.standard-mode {
      max-width: 500px;
      padding: 32px 28px;
      font-size: 0.95rem;
    }

    .receipt-wrapper * {
      color: #000000 !important;
      border-color: #000000 !important;
    }

    .receipt-header img {
      width: 64px;
      height: auto;
      object-fit: contain;
      filter: grayscale(100%) contrast(150%);
    }

    .dashed-divider {
      border: none;
      border-top: 1.5px dashed #000000 !important;
      margin: 12px 0;
      opacity: 1;
    }

    .solid-divider {
      border: none;
      border-top: 2px solid #000000 !important;
      margin: 12px 0;
      opacity: 1;
    }

    .double-divider {
      border: none;
      border-top: 3px double #000000 !important;
      margin: 12px 0;
      opacity: 1;
    }

    .stamp-box {
      display: inline-block;
      padding: 4px 14px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      font-size: 0.82rem;
      border: 2px solid #000000;
      background: #ffffff;
    }

    .section-title {
      font-size: 0.76rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 3px 0;
      border-bottom: 1px solid #000000;
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .meta-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.78rem;
      margin-bottom: 4px;
      line-height: 1.35;
    }

    .item-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 6px;
      font-size: 0.8rem;
      line-height: 1.35;
    }

    .item-row.bold {
      font-weight: 700;
    }

    .summary-box {
      border: 1.5px solid #000000;
      padding: 8px 10px;
      margin-top: 10px;
    }

    .barcode-box {
      background: repeating-linear-gradient(
        90deg,
        #000 0,
        #000 2px,
        #fff 2px,
        #fff 4px,
        #000 4px,
        #000 7px,
        #fff 7px,
        #fff 9px
      );
      height: 36px;
      width: 75%;
      margin: 12px auto 4px auto;
    }

    @media print {
      body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      .no-print-bar {
        display: none !important;
      }

      .receipt-wrapper {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
        max-width: 52mm !important;
      }

      .receipt-wrapper.mode-80mm {
        max-width: 76mm !important;
      }

      .receipt-wrapper.standard-mode {
        max-width: 100% !important;
      }

      @page {
        margin: 2mm;
        size: auto;
      }
    }
  </style>
</head>
<body>

  @php
    $startDt = \Carbon\Carbon::parse($booking->start_dt);
    $endDt   = \Carbon\Carbon::parse($booking->end_dt);
    $durHours = $booking->duration_hours;

    $courtPrice = $booking->court_price;
    $equipTotal = $booking->equipment_total;
    $grandTotal = $booking->total_price;
    $isPaidAll  = $booking->is_fully_paid;
    $paidSoFar  = (int)$booking->paid_amount;
    $remaining  = $booking->remaining_amount;

    if ($isPaidAll) {
      $cashRec = (int)(session('cash_received') ?? request('cash_received') ?? $grandTotal);
      if ($cashRec < $grandTotal) {
        $cashRec = $grandTotal;
      }
      $cashChg = max(0, (int)(session('cash_change') ?? request('cash_change') ?? ($cashRec - $grandTotal)));
    } else {
      $cashRec = $paidSoFar;
      $cashChg = 0;
    }
  @endphp

  <!-- Bar Kontrol Layar (Tidak Ikut Tercetak) -->
  <div class="no-print-bar">
    <div class="d-flex align-items-center gap-2">
      <button onclick="window.history.back()" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
      </button>
      <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-dark" id="btnMode58" onclick="setMode('58')">
          58mm (ALGOO)
        </button>
        <button type="button" class="btn btn-outline-dark" id="btnMode80" onclick="setMode('80')">
          80mm
        </button>
        <button type="button" class="btn btn-outline-dark" id="btnModeStandard" onclick="setMode('standard')">
          A4
        </button>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" onclick="printRawBT()" class="btn btn-success btn-sm fw-bold px-3 d-inline-flex align-items-center gap-1 shadow-sm" title="Kirim langsung ke printer Bluetooth ALGOO via RawBT">
        <i class="bi bi-bluetooth"></i> Cetak Bluetooth (RawBT)
      </button>
      <button type="button" onclick="window.print()" class="btn btn-dark btn-sm fw-bold px-3">
        <i class="bi bi-printer-fill me-1"></i> Cetak Browser
      </button>
    </div>
  </div>

  <!-- Lembar Struk Hitam Putih (Bukti Lunas & Selesai Bermain) -->
  <div class="receipt-wrapper" id="receiptSheet">

    <!-- Header Arena -->
    <div class="receipt-header text-center mb-2">
      <img src="{{ asset('images/logo.png') }}" alt="Logo Lino Sport Center" class="mb-2">
      <div class="fw-bold" style="font-size: 1.05rem; letter-spacing: 1px;">LINO SPORT CENTER</div>
      <div style="font-size: 0.75rem;">PADEL &bull; BADMINTON &bull; PICKLEBALL ARENA</div>
      <div style="font-size: 0.72rem;">Telp/WA: 0812-3456-789</div>

      <div class="mt-2">
        <span class="stamp-box">
          @if($isPaidAll)
            *** LUNAS &bull; SELESAI BERMAIN ***
          @else
            *** BELUM LUNAS ***
          @endif
        </span>
      </div>
    </div>

    <hr class="dashed-divider">

    <!-- Informasi Transaksi & Pemain -->
    <div class="mb-2">
      <div class="meta-row">
        <span>NO. STRUK</span>
        <strong>#{{ $booking->formatted_invoice_no }}</strong>
      </div>
      <div class="meta-row">
        <span>TGL CETAK / CHECK-OUT</span>
        <span>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
      </div>
      <div class="meta-row">
        <span>ADMIN APPROVER</span>
        <strong>{{ $booking->approver_name ?: ($booking->status === 'confirmed' ? 'Administrator' : 'Menunggu Approve Admin') }}</strong>
      </div>
      <div class="meta-row">
        <span>NAMA PEMAIN</span>
        <strong>{{ $booking->display_name }}</strong>
      </div>
      <div class="meta-row">
        <span>NO. WHATSAPP</span>
        <span>{{ $booking->display_phone }}</span>
      </div>
      <div class="meta-row">
        <span>STATUS BOOKING</span>
        <strong>{{ $booking->status === 'confirmed' ? ($isPaidAll ? 'APPROVED & LUNAS' : 'APPROVED (DP)') : 'MENUNGGU APPROVE ADMIN' }}</strong>
      </div>
    </div>

    <hr class="solid-divider">

    <!-- Tagihan Sewa Lapangan -->
    <div class="mb-3">
      <div class="section-title">
        <span>RINCIAN SEWA LAPANGAN</span>
        <span>{{ $booking->court_remaining <= 0 ? '[LUNAS]' : '[SISA Rp' . number_format($booking->court_remaining, 0, ',', '.') . ']' }}</span>
      </div>

      <div class="item-row bold">
        <div>
          <div>{{ strtoupper($booking->court->name) }} ({{ strtoupper($booking->court->sport) }})</div>
          <div style="font-size: 0.72rem; font-weight: 400;">
            Tgl Main : {{ $startDt->format('d/m/Y') }}<br>
            Sesi Jam : {{ $startDt->format('H:i') }} - {{ $endDt->format('H:i') }} WIB ({{ $durHours }} Jam)
          </div>
        </div>
        <div class="text-end">
          Rp{{ number_format($courtPrice, 0, ',', '.') }}
        </div>
      </div>

      <div class="meta-row border-top pt-1 mt-1" style="font-size: 0.74rem;">
        <span>Subtotal Lapangan:</span>
        <strong>Rp{{ number_format($courtPrice, 0, ',', '.') }}</strong>
      </div>
    </div>

    <hr class="double-divider">

    <!-- Rekapitulasi Pembayaran Lunas -->
    <div style="font-size: 0.8rem;">
      <div class="meta-row">
        <span>METODE PEMBAYARAN</span>
        <span>QRIS STATIS ({{ strtoupper($booking->payment_method ?? 'LUNAS') }})</span>
      </div>
      <div class="meta-row fw-bold" style="font-size: 0.92rem; border-top: 1px dashed #000; padding-top: 5px; margin-top: 4px;">
        <span>GRAND TOTAL</span>
        <span>Rp{{ number_format($grandTotal, 0, ',', '.') }}</span>
      </div>
      <div class="meta-row">
        <span>TOTAL DIBAYAR</span>
        <strong>Rp{{ number_format($cashRec, 0, ',', '.') }}</strong>
      </div>
      <div class="meta-row">
        <span>KEMBALIAN</span>
        <strong>Rp{{ number_format($cashChg, 0, ',', '.') }}</strong>
      </div>
      <div class="meta-row">
        <span>SISA TAGIHAN</span>
        <strong>{{ $remaining <= 0 ? 'Rp0 (LUNAS)' : 'Rp' . number_format($remaining, 0, ',', '.') }}</strong>
      </div>
    </div>

    <!-- Kotak Status Akhir -->
    <div class="summary-box text-center">
      @if($isPaidAll && $booking->status === 'confirmed')
        <div class="fw-bold" style="font-size: 0.8rem;">STATUS AKHIR: DI-APPROVE &amp; LUNAS</div>
        <div style="font-size: 0.7rem;">
          Disetujui oleh Admin: {{ $booking->approver_name ?: 'Administrator' }}. Seluruh tagihan sewa lapangan telah lunas.
        </div>
      @elseif($booking->status === 'confirmed')
        <div class="fw-bold" style="font-size: 0.8rem;">STATUS: DI-APPROVE ADMIN (DP DITERIMA)</div>
        <div style="font-size: 0.7rem;">
          Disetujui oleh Admin: {{ $booking->approver_name ?: 'Administrator' }}. Sisa tagihan wajib dilunasi di lokasi.
        </div>
      @else
        <div class="fw-bold" style="font-size: 0.8rem;">STATUS: MENUNGGU APPROVE ADMIN</div>
        <div style="font-size: 0.7rem;">
          Silakan upload bukti pembayaran QRIS pada halaman konfirmasi website untuk mendapatkan approval jadwal.
        </div>
      @endif
    </div>

    <!-- Barcode & Nomor Bukti -->
    <div class="text-center my-2">
      <div class="barcode-box"></div>
      <div style="font-size: 0.7rem; letter-spacing: 1.5px;">
        * {{ $booking->formatted_invoice_no }} *
      </div>
    </div>

    <hr class="dashed-divider">

    <!-- Footer Ucapan Terima Kasih Setelah Selesai Main -->
    <div class="text-center" style="font-size: 0.72rem; line-height: 1.4;">
      <div class="fw-bold mb-1">TERIMA KASIH TELAH BERMAIN DI LINO SPORT CENTER!</div>
      <div>Struk ini adalah bukti pembayaran sah untuk penyelesaian sesi bermain &amp; pengembalian alat olahraga.</div>
      <div class="mt-1 fw-bold">Sampai Jumpa di Pertandingan Berikutnya!</div>
    </div>

  </div>

  <script>
    function setMode(mode) {
      const sheet = document.getElementById('receiptSheet');
      const btn58 = document.getElementById('btnMode58');
      const btn80 = document.getElementById('btnMode80');
      const btnStandard = document.getElementById('btnModeStandard');

      sheet.classList.remove('mode-80mm', 'standard-mode');
      btn58.className = 'btn btn-outline-dark';
      btn80.className = 'btn btn-outline-dark';
      btnStandard.className = 'btn btn-outline-dark';

      if (mode === 'standard') {
        sheet.classList.add('standard-mode');
        btnStandard.className = 'btn btn-dark';
      } else if (mode === '80') {
        sheet.classList.add('mode-80mm');
        btn80.className = 'btn btn-dark';
      } else {
        btn58.className = 'btn btn-dark';
      }
    }

    function printRawBT() {
      const padCenter = (str, len = 32) => {
        str = String(str || '');
        if (str.length >= len) return str;
        const left = Math.floor((len - str.length) / 2);
        return ' '.repeat(left) + str;
      };

      const padRow = (left, right, len = 32) => {
        left = String(left || '');
        right = String(right || '');
        const spaces = Math.max(1, len - left.length - right.length);
        return left + ' '.repeat(spaces) + right;
      };

      const invoice = @json($booking->formatted_invoice_no);
      const customer = @json($booking->display_name);
      const phone = @json($booking->display_phone);
      const court = @json($booking->court->name ?? 'Lapangan');
      const sport = @json(ucfirst($booking->court->sport ?? ''));
      const dateStr = @json($startDt->translatedFormat('d M Y'));
      const timeStr = @json($startDt->format('H:i') . '-' . $endDt->format('H:i'));
      const duration = @json($durHours);
      const courtPrice = @json(number_format($courtPrice, 0, ',', '.'));
      const total = @json(number_format($grandTotal, 0, ',', '.'));
      const paid = @json(number_format($paidSoFar, 0, ',', '.'));
      const sisa = @json(number_format($remaining, 0, ',', '.'));
      const status = @json($isPaidAll ? 'LUNAS' : 'DP 50%');
      const admin = @json($booking->approver->name ?? 'Kasir');

      let text = "";
      text += padCenter("LINO SPORT CENTER") + "\n";
      text += padCenter("ARENA OLAHRAGA TERBAIK") + "\n";
      text += padCenter("WA: 0823-4379-7911") + "\n";
      text += "=".repeat(32) + "\n";
      text += padRow("No. Invoice:", "#" + invoice) + "\n";
      text += padRow("Tanggal    :", dateStr) + "\n";
      text += padRow("Jam Main   :", timeStr) + "\n";
      text += padRow("Durasi     :", duration + " Jam") + "\n";
      text += padRow("Pelanggan  :", customer) + "\n";
      text += padRow("No. HP     :", phone) + "\n";
      text += padRow("Lapangan   :", court) + "\n";
      text += padRow("Cabang     :", sport) + "\n";
      text += "-".repeat(32) + "\n";
      text += padRow("Sewa Lapangan", "Rp" + courtPrice) + "\n";
      text += "-".repeat(32) + "\n";
      text += padRow("TOTAL TAGIHAN", "Rp" + total) + "\n";
      text += padRow("SUDAH DIBAYAR", "Rp" + paid) + "\n";
      if (sisa !== "0") {
        text += padRow("SISA PELUNASAN", "Rp" + sisa) + "\n";
      }
      text += padRow("STATUS BOOKING", status) + "\n";
      text += padRow("Admin/Kasir   ", admin) + "\n";
      text += "=".repeat(32) + "\n";
      text += padCenter("TERIMA KASIH TELAH BERMAIN!") + "\n";
      text += padCenter("BUKTI SAH PENYELESAIAN SESI") + "\n";
      text += padCenter("LINO SPORT CENTER") + "\n\n\n\n";

      // Direct intent to RawBT printer app
      const base64 = btoa(unescape(encodeURIComponent(text)));
      window.location.href = "intent:base64," + base64 + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
    }

    @if(request()->boolean('autoprint'))
    window.addEventListener('load', function () {
      setTimeout(function () {
        window.focus();
        window.print();
      }, 300);
    });
    @endif
  </script>
</body>
</html>
