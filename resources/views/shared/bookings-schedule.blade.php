@php
  $prefix = $prefix ?? 'admin';
@endphp

@section('styles')
<style>
  /* Hero Header & Elevated KPI Cards */
  .sched-hero {
    background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%);
    border-radius: 18px;
    padding: 22px 26px;
    color: #ffffff;
    box-shadow: 0 14px 32px -10px rgba(6, 78, 59, 0.45);
    position: relative;
    overflow: hidden;
  }
  .sched-hero::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 190px;
    height: 190px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
    pointer-events: none;
  }

  .sched-kpi-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 10px 24px -8px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
  }
  .sched-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 30px -8px rgba(15, 23, 42, 0.13);
  }
  .sched-kpi-card.kpi-locked   { border-bottom: 4px solid #ef4444; }
  .sched-kpi-card.kpi-pending  { border-bottom: 4px solid #f59e0b; }
  .sched-kpi-card.kpi-ready    { border-bottom: 4px solid #10b981; }
  .sched-kpi-card.kpi-occ      { border-bottom: 4px solid #3b82f6; }

  .sched-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: 0 6px 14px -4px rgba(0, 0, 0, 0.12);
  }
  .sched-kpi-icon.icon-locked  { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
  .sched-kpi-icon.icon-pending { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
  .sched-kpi-icon.icon-ready   { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
  .sched-kpi-icon.icon-occ     { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; }

  /* Floating Toolbar Navigator */
  .sched-toolbar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 20px;
    box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.06);
  }
  .legend-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 700;
    border: 1px solid transparent;
  }
  .legend-chip.chip-ready   { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
  .legend-chip.chip-pending { background: #fffbeb; color: #b45309; border-color: #fde68a; }
  .legend-chip.chip-locked  { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
  .legend-chip.chip-past    { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

  /* Matrix Container & Table */
  .schedule-container {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 18px 38px -12px rgba(15, 23, 42, 0.10), 0 4px 12px rgba(15, 23, 42, 0.04);
  }
  .table-schedule {
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 0;
  }
  .table-schedule th,
  .table-schedule td {
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    text-align: center;
    padding: 10px 8px;
    min-width: 112px;
    background-color: #f8fafc;
  }
  .table-schedule tbody tr:nth-child(even) td {
    background-color: #f1f5f9;
  }

  /* Deep Gradient Header Timeline */
  .table-schedule thead th {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
    color: #f8fafc !important;
    position: sticky;
    top: 0;
    z-index: 10;
    border-bottom: 2px solid #059669 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    padding: 12px 8px;
  }
  .hour-badge-top {
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    color: #ffffff;
  }
  .hour-badge-sub {
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 500;
  }

  /* Sticky Court Identity Column */
  .sticky-col {
    position: sticky;
    left: 0;
    background: #ffffff !important;
    z-index: 5;
    min-width: 225px !important;
    max-width: 240px;
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 6px 0 16px -6px rgba(15, 23, 42, 0.08);
    padding: 12px 14px !important;
  }
  thead .sticky-col {
    z-index: 15;
    background: linear-gradient(135deg, #064e3b 0%, #047857 100%) !important;
    color: #ffffff !important;
  }

  .court-row-card {
    display: flex;
    align-items: center;
    gap: 11px;
    text-align: left;
  }
  .court-sport-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  }
  .sport-padel       { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; }
  .sport-bulutangkis { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #ffffff; }
  .sport-pickleball  { background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; }

  /* Current Active Hour Highlight */
  .hour-current {
    background: linear-gradient(180deg, #059669 0%, #047857 100%) !important;
    box-shadow: inset 0 -3px 0 #34d399;
  }
  .hour-current-cell {
    background: rgba(16, 185, 129, 0.09) !important;
  }

  /* Tactile 3D-ish Slot Cards */
  .slot-card {
    border-radius: 12px;
    padding: 8px 8px;
    min-height: 68px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 700;
    position: relative;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  /* 1. READY SLOT */
  .slot-ready {
    background: linear-gradient(145deg, #ffffff 0%, #ecfdf5 100%);
    border: 1px solid #a7f3d0;
    border-bottom: 3px solid #10b981;
    color: #047857;
    box-shadow: 0 3px 8px rgba(16, 185, 129, 0.10);
  }
  .slot-ready:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(16, 185, 129, 0.18);
    background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
  }
  .slot-ready-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    margin-bottom: 3px;
  }

  /* 2. APPROVED / LOCKED SLOT */
  .slot-locked {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border: 1px solid #fda4af;
    border-left: 4px solid #e11d48;
    border-bottom: 3px solid #be123c;
    color: #9f1239;
    cursor: pointer;
    box-shadow: 0 6px 14px -3px rgba(225, 29, 72, 0.18);
  }
  .slot-locked:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 22px -4px rgba(225, 29, 72, 0.28);
    background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
  }
  .slot-status-pill-locked {
    background: #e11d48;
    color: #ffffff;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 999px;
    letter-spacing: 0.4px;
    margin-bottom: 3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    box-shadow: 0 2px 6px rgba(225, 29, 72, 0.3);
  }

  /* 3. PENDING (MENUNGGU APPROVE) SLOT */
  .slot-pending {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fcd34d;
    border-left: 4px solid #f59e0b;
    border-bottom: 3px solid #d97706;
    color: #92400e;
    cursor: pointer;
    box-shadow: 0 6px 14px -3px rgba(245, 158, 11, 0.22);
  }
  .slot-pending:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 22px -4px rgba(245, 158, 11, 0.34);
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  }
  .slot-status-pill-pending {
    background: #f59e0b;
    color: #ffffff;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 999px;
    letter-spacing: 0.4px;
    margin-bottom: 3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
  }

  /* 4. PAST / LEWAT SLOT */
  .slot-past {
    background: repeating-linear-gradient(
      -45deg,
      #f8fafc,
      #f8fafc 6px,
      #f1f5f9 6px,
      #f1f5f9 12px
    );
    border: 1px solid #cbd5e1;
    color: #94a3b8;
    opacity: 0.78;
  }
</style>
@endsection

{{-- Hero Banner Jadwal Visual --}}
<div class="sched-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 small fw-semibold mb-2">
      <i class="bi bi-calendar3-week text-warning"></i>
      <span>Visualisasi Matriks Lapangan Realtime</span>
    </div>
    <h2 class="fw-extrabold mb-1 text-white" style="font-weight:800;letter-spacing:-0.5px;">Jadwal Lapangan</h2>
    <p class="mb-0 text-white text-opacity-75 small">
      {{ $carbonDate->translatedFormat('l, d F Y') }} · Klik kartu slot <strong class="text-warning">Pending</strong> atau <strong class="text-white">Approved</strong> untuk melihat detail & melakukan approval.
    </p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-light fw-bold px-3 shadow-sm">
      <i class="bi bi-inbox me-1 text-success"></i> Daftar Booking & Approval
    </a>
  </div>
</div>

{{-- 4 KPI Ringkas Berdimensi --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="sched-kpi-card kpi-locked">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;letter-spacing:0.4px;">Slot Di-Approve (Locked)</div>
          <div class="fs-2 fw-extrabold text-dark mt-1" style="font-weight:800;line-height:1.1;">{{ $confirmedSlots }}</div>
          <div class="text-muted" style="font-size:0.72rem;">Jadwal resmi terkunci</div>
        </div>
        <div class="sched-kpi-icon icon-locked"><i class="bi bi-shield-lock-fill"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sched-kpi-card kpi-pending">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;letter-spacing:0.4px;">Menunggu Approve</div>
          <div class="fs-2 fw-extrabold text-dark mt-1" style="font-weight:800;line-height:1.1;">{{ $pendingSlots }}</div>
          <div class="text-muted" style="font-size:0.72rem;">Perlu verifikasi bukti QRIS</div>
        </div>
        <div class="sched-kpi-icon icon-pending"><i class="bi bi-hourglass-split"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sched-kpi-card kpi-ready">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;letter-spacing:0.4px;">Slot Kosong (Ready)</div>
          <div class="fs-2 fw-extrabold text-dark mt-1" style="font-weight:800;line-height:1.1;">{{ $readySlots }}</div>
          <div class="text-muted" style="font-size:0.72rem;">Tersedia untuk dibooking</div>
        </div>
        <div class="sched-kpi-icon icon-ready"><i class="bi bi-check2-circle"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sched-kpi-card kpi-occ">
      <div class="d-flex justify-content-between align-items-center">
        <div class="w-100 me-2">
          <div class="text-muted small fw-bold text-uppercase" style="font-size:0.7rem;letter-spacing:0.4px;">Tingkat Okupansi</div>
          <div class="fs-2 fw-extrabold text-dark mt-1" style="font-weight:800;line-height:1.1;">{{ $occupancyRate }}%</div>
          <div class="progress mt-2" style="height: 6px; border-radius: 99px; background:#e2e8f0;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $occupancyRate) }}%"></div>
          </div>
        </div>
        <div class="sched-kpi-icon icon-occ flex-shrink-0"><i class="bi bi-pie-chart-fill"></i></div>
      </div>
    </div>
  </div>
</div>

{{-- Toolbar Tanggal & Legenda Berdimensi --}}
<div class="sched-toolbar mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <div class="btn-group shadow-sm rounded-3 overflow-hidden">
      <a href="{{ route($prefix . '.bookings.schedule', ['date' => $prevDate]) }}" class="btn btn-light btn-sm px-3 fw-semibold border">
        <i class="bi bi-chevron-left"></i> Kemarin
      </a>
      <a href="{{ route($prefix . '.bookings.schedule', ['date' => $todayDate]) }}" class="btn {{ $isToday ? 'btn-success' : 'btn-light border' }} btn-sm px-3 fw-bold">
        <i class="bi bi-calendar-event me-1"></i> Hari Ini
      </a>
      <a href="{{ route($prefix . '.bookings.schedule', ['date' => $nextDate]) }}" class="btn btn-light btn-sm px-3 fw-semibold border">
        Besok <i class="bi bi-chevron-right"></i>
      </a>
    </div>
    <form action="{{ route($prefix . '.bookings.schedule') }}" method="GET" class="d-inline-flex gap-1">
      <input type="date" name="date" class="form-control form-control-sm fw-semibold shadow-sm" style="border-radius:10px;min-width:155px;" value="{{ $date }}" onchange="this.form.submit()">
    </form>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="legend-chip chip-ready">
      <i class="bi bi-check-circle-fill"></i> Ready (Kosong)
    </span>
    <span class="legend-chip chip-pending">
      <i class="bi bi-hourglass-split"></i> Menunggu Approve
    </span>
    <span class="legend-chip chip-locked">
      <i class="bi bi-lock-fill"></i> Approved (Locked)
    </span>
    <span class="legend-chip chip-past">
      <i class="bi bi-slash-circle"></i> Jam Lewat
    </span>
  </div>
</div>

{{-- Matriks Jadwal Lapangan Berdimensi --}}
<div class="schedule-container">
  <div class="table-responsive">
    <table class="table table-schedule align-middle">
      <thead>
        <tr>
          <th class="sticky-col text-start">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-grid-3x3-gap-fill text-warning"></i>
              <span class="text-uppercase fw-extrabold" style="font-size:0.76rem;letter-spacing:0.6px;">Arena / Jam Main</span>
            </div>
          </th>
          @foreach($hours as $hour)
            @php
              $isCurrentH = ($isToday && $hour === $currentHour);
              $nextH = sprintf('%02d:00', ((int)$hour) + 1);
            @endphp
            <th class="{{ $isCurrentH ? 'hour-current' : '' }}">
              <div class="hour-badge-top">{{ $hour }}</div>
              <div class="hour-badge-sub">s/d {{ $nextH }}</div>
              @if($isCurrentH)
                <span class="badge bg-warning text-dark mt-1" style="font-size:0.55rem;">SEKARANG</span>
              @endif
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($courts as $court)
          @php
            $sportClass = 'sport-' . strtolower($court->sport);
            $sportIcon = match(strtolower($court->sport)) {
              'padel' => 'bi-trophy-fill',
              'bulutangkis' => 'bi-lightning-charge-fill',
              default => 'bi-circle-square',
            };
          @endphp
          <tr>
            <td class="sticky-col text-start">
              <div class="court-row-card">
                <div class="court-sport-avatar {{ $sportClass }}">
                  <i class="bi {{ $sportIcon }}"></i>
                </div>
                <div class="min-w-0">
                  <div class="fw-extrabold text-dark text-truncate" style="font-size:0.88rem;font-weight:800;">{{ $court->name }}</div>
                  <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.62rem;letter-spacing:0.4px;">{{ $court->sport }}</span>
                </div>
              </div>
            </td>
            @foreach($hours as $hour)
              @php
                $slotStart = \Carbon\Carbon::parse($date . ' ' . $hour);
                $slotEnd   = \Carbon\Carbon::parse($date . ' ' . $hour)->addHour();
                $isCurrentH = ($isToday && $hour === $currentHour);
                $isPast = ($isToday && $slotEnd <= $now);
                $match = $bookings->first(function($b) use ($court, $slotStart, $slotEnd) {
                  if ($b->court_id != $court->id) return false;
                  $bStart = \Carbon\Carbon::parse($b->start_dt);
                  $bEnd   = \Carbon\Carbon::parse($b->end_dt);
                  return $bStart < $slotEnd && $bEnd > $slotStart;
                });
              @endphp
              <td class="{{ $isCurrentH ? 'hour-current-cell' : '' }}">
                @if($match)
                  @php
                    $isConfirmed = ($match->status === 'confirmed');
                    $modalData = json_encode([
                      'id' => $match->formatted_invoice_no,
                      'customer_name' => $match->display_name,
                      'customer_phone' => $match->display_phone,
                      'customer_email' => $match->customer_email ?: '-',
                      'court_name' => $court->name,
                      'sport' => ucfirst($court->sport),
                      'start_dt' => \Carbon\Carbon::parse($match->start_dt)->format('d M Y, H:i'),
                      'end_dt' => \Carbon\Carbon::parse($match->end_dt)->format('H:i'),
                      'total_price' => 'Rp' . number_format($match->total_price, 0, ',', '.'),
                      'remaining' => $match->remaining_amount > 0 ? 'Rp' . number_format($match->remaining_amount, 0, ',', '.') : 'Lunas',
                      'payment_method' => strtoupper($match->payment_method),
                      'payment_status' => $match->is_fully_paid ? 'LUNAS' : ($match->is_dp_paid ? 'DP MASUK' : 'MENUNGGU APPROVE'),
                      'approver_name' => $match->approver_name ?: ($isConfirmed ? 'Administrator' : 'Menunggu Approve Admin'),
                      'proof_url' => $match->payment_proof_url ?: '',
                      'status' => $match->status,
                      'confirm_url' => route($prefix . '.bookings.confirm', $match->id),
                      'cancel_url' => route($prefix . '.bookings.cancel', $match->id),
                      'manage_url' => route($prefix . '.bookings.show', $match->id),
                      'receipt_url' => route($prefix . '.bookings.receipt', $match->id)
                    ]);
                  @endphp
                  <div class="slot-card {{ $isConfirmed ? 'slot-locked' : 'slot-pending' }} js-open-booking-modal"
                       data-booking="{{ htmlspecialchars($modalData, ENT_QUOTES, 'UTF-8') }}">
                    @if($isConfirmed)
                      <span class="slot-status-pill-locked"><i class="bi bi-lock-fill"></i> APPROVED</span>
                    @else
                      <span class="slot-status-pill-pending"><i class="bi bi-hourglass-split"></i> PENDING</span>
                    @endif
                    <div class="text-truncate w-100 px-1 fw-extrabold" style="font-size:.72rem">{{ $match->display_name }}</div>
                    <small class="opacity-85" style="font-size:.62rem;font-weight:600;">
                      #{{ $match->id }}@if($isConfirmed && $match->approver_name) · {{ $match->approver_name }}@endif
                    </small>
                  </div>
                @elseif($isPast)
                  <div class="slot-card slot-past">
                    <i class="bi bi-clock-history mb-1" style="font-size:0.8rem;"></i>
                    <span>Lewat</span>
                  </div>
                @else
                  <div class="slot-card slot-ready">
                    <span class="slot-ready-icon"><i class="bi bi-check-lg"></i></span>
                    <span>Ready</span>
                  </div>
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Modal Detail Booking --}}
<div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:450px">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%);">
        <div>
          <h6 class="modal-title fw-bold mb-0 text-white" id="modalBookingId">Booking</h6>
          <small class="text-white text-opacity-75" id="modalCourtBadge">-</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-center p-2.5 px-3 rounded-3 bg-light border mb-3">
          <span class="fw-bold small text-dark" id="modalStatusText">-</span>
          <span class="lx-pill" id="modalPaymentStatusBadge">-</span>
        </div>

        <div class="small mb-3">
          <div class="d-flex justify-content-between py-1.5 border-bottom">
            <span class="text-muted">Pemesan</span>
            <strong class="text-dark" id="modalCustomerName">-</strong>
          </div>
          <div class="d-flex justify-content-between py-1.5 border-bottom">
            <span class="text-muted">WhatsApp</span>
            <span class="font-mono fw-semibold" id="modalCustomerPhone">-</span>
          </div>
          <div class="d-flex justify-content-between py-1.5 border-bottom">
            <span class="text-muted">Jadwal Main</span>
            <strong class="text-dark" id="modalScheduleTime">-</strong>
          </div>
          <div class="d-flex justify-content-between py-1.5 border-bottom">
            <span class="text-muted">Total Tagihan</span>
            <strong class="text-dark" id="modalTotalPrice">-</strong>
          </div>
          <div class="d-flex justify-content-between py-1.5 border-bottom">
            <span class="text-muted">Di-Approve Oleh</span>
            <strong class="text-success" id="modalApproverName">-</strong>
          </div>
          <div class="d-flex justify-content-between pt-1.5">
            <span class="text-muted">Sisa Pelunasan</span>
            <strong class="text-danger" id="modalRemaining">-</strong>
          </div>
        </div>

        {{-- Preview Gambar Bukti Pembayaran QRIS di Modal Jadwal --}}
        <div id="modalProofContainer" class="p-2.5 rounded-3 bg-light border text-center mb-3 d-none">
          <div class="small fw-bold text-dark mb-1.5">
            <i class="bi bi-image text-success me-1"></i> Preview Bukti Pembayaran QRIS
          </div>
          <a id="modalProofLink" href="#" target="_blank">
            <img id="modalProofImg" src="#" alt="Bukti QRIS" class="img-fluid rounded-3 border shadow-sm" style="max-height:190px;object-fit:contain;background:#fff;">
          </a>
        </div>
        <div id="modalNoProofBadge" class="alert alert-warning py-2 px-3 small mb-3 d-none">
          <i class="bi bi-info-circle me-1"></i> Pelanggan belum mengupload gambar bukti pembayaran QRIS.
        </div>

        <div class="d-grid gap-2">
          <div class="d-flex gap-2">
            <a href="#" id="modalManageTagihanBtn" class="btn btn-primary flex-grow-1 fw-bold">
              <i class="bi bi-receipt me-1"></i> Detail & Validasi Bukti
            </a>
            <a href="#" id="modalReceiptBtn" target="_blank" class="btn btn-light border" title="Cetak Struk">
              <i class="bi bi-printer"></i>
            </a>
          </div>

          <div class="d-flex gap-2">
            <form id="modalConfirmForm" method="POST" action="" class="flex-grow-1">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-success w-100 fw-bold">
                <i class="bi bi-check2-circle me-1"></i> Approve Jadwal
              </button>
            </form>
            <form id="modalCancelForm" method="POST" action="" class="flex-grow-1"
                  data-confirm="Batalkan booking ini dan lepaskan slot lapangan?"
                  data-confirm-title="Batalkan Booking" data-confirm-ok="Ya, batalkan" data-confirm-variant="danger">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-outline-danger w-100 fw-semibold">
                <i class="bi bi-x-lg me-1"></i> Batalkan
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalElem = document.getElementById('bookingDetailModal');
  var bsModal = new bootstrap.Modal(modalElem);

  document.querySelectorAll('.js-open-booking-modal').forEach(function (card) {
    card.addEventListener('click', function () {
      var data = JSON.parse(this.getAttribute('data-booking') || '{}');
      document.getElementById('modalBookingId').innerText = 'Booking #' + data.id;
      document.getElementById('modalCourtBadge').innerText = data.court_name + ' (' + data.sport + ')';
      document.getElementById('modalCustomerName').innerText = data.customer_name;
      document.getElementById('modalCustomerPhone').innerText = data.customer_phone;
      document.getElementById('modalScheduleTime').innerText = data.start_dt + ' – ' + data.end_dt;
      document.getElementById('modalTotalPrice').innerText = data.total_price;
      document.getElementById('modalApproverName').innerText = data.approver_name || '-';
      document.getElementById('modalRemaining').innerText = data.remaining;

      var statusText = document.getElementById('modalStatusText');
      var paymentBadge = document.getElementById('modalPaymentStatusBadge');
      var confirmForm = document.getElementById('modalConfirmForm');

      statusText.innerText = data.status === 'confirmed' ? 'Disetujui / Approved' : 'Menunggu Approve Admin';
      paymentBadge.className = 'lx-pill ' + (data.payment_status === 'LUNAS' ? 'lx-pill-success' : 'lx-pill-warning');
      paymentBadge.innerText = data.payment_status;
      confirmForm.style.display = data.status === 'confirmed' ? 'none' : 'block';

      confirmForm.action = data.confirm_url;
      document.getElementById('modalCancelForm').action = data.cancel_url;
      document.getElementById('modalManageTagihanBtn').href = data.manage_url;
      document.getElementById('modalReceiptBtn').href = data.receipt_url;

      var proofWrap = document.getElementById('modalProofContainer');
      var noProofBadge = document.getElementById('modalNoProofBadge');
      if (data.proof_url) {
        document.getElementById('modalProofImg').src = data.proof_url;
        document.getElementById('modalProofLink').href = data.proof_url;
        proofWrap.classList.remove('d-none');
        noProofBadge.classList.add('d-none');
      } else {
        proofWrap.classList.add('d-none');
        if (data.status === 'pending') {
          noProofBadge.classList.remove('d-none');
        } else {
          noProofBadge.classList.add('d-none');
        }
      }

      bsModal.show();
    });
  });
});
</script>
@endsection
