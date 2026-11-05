@php
  $prefix = $prefix ?? 'admin';
  $needsAction = $needsAction ?? collect();
  $todayBookings = $todayBookings ?? collect();
@endphp

<div class="lx-page-header">
  <div>
    <h2 class="lx-page-title">Halo, {{ auth()->user()?->name ?? 'Admin' }}</h2>
    <p class="lx-page-sub">Ringkasan operasional, validasi bukti pembayaran QRIS, dan jadwal lapangan hari ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}).</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route($prefix . '.bookings.schedule') }}" class="btn btn-light">
      <i class="bi bi-calendar3-week me-1"></i> Jadwal Lapangan
    </a>
    <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-primary">
      <i class="bi bi-check2-circle me-1"></i> Kelola & Approve Booking
    </a>
  </div>
</div>

{{-- 4 KPI Fokus Hari Ini --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card lx-kpi">
      <div class="lx-kpi-label"><i class="bi bi-calendar-check text-success"></i> Jadwal Hari Ini</div>
      <div class="lx-kpi-value">{{ $totalTodayCount ?? 0 }} <span class="fs-6 fw-semibold text-muted">sesi</span></div>
      <div class="lx-kpi-hint">{{ $confirmedCount ?? 0 }} di-approve · {{ $pendingCount ?? 0 }} menunggu approve</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card lx-kpi">
      <div class="lx-kpi-label"><i class="bi bi-arrow-down-left-circle text-success"></i> Uang Masuk Hari Ini</div>
      <div class="lx-kpi-value text-success">Rp{{ number_format($todayIncome ?? 0, 0, ',', '.') }}</div>
      <div class="lx-kpi-hint">QRIS DP & pelunasan tervalidasi</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card lx-kpi">
      <div class="lx-kpi-label"><i class="bi bi-clock-history text-warning"></i> Sisa Tagihan Hari Ini</div>
      <div class="lx-kpi-value {{ ($todayUnpaidRemaining ?? 0) > 0 ? 'text-danger' : '' }}">Rp{{ number_format($todayUnpaidRemaining ?? 0, 0, ',', '.') }}</div>
      <div class="lx-kpi-hint">Menunggu approve / pelunasan DP</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card lx-kpi">
      <div class="lx-kpi-label"><i class="bi bi-bell text-primary"></i> Perlu Approval / Pelunasan</div>
      <div class="lx-kpi-value">{{ $needsAction->count() }}</div>
      <div class="lx-kpi-hint">Menunggu validasi bukti QRIS / DP</div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Kolom Kiri: Antrean Perlu Tindakan (Validasi Bukti QRIS / Pelunasan) --}}
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title">
          <i class="bi bi-lightning-charge text-warning"></i> Antrean Validasi Bukti QRIS & Approval
          @if($needsAction->count() > 0)
            <span class="badge bg-light text-dark border">{{ $needsAction->count() }}</span>
          @endif
        </h6>
        <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-ghost btn-sm">Semua Booking <i class="bi bi-arrow-right ms-1"></i></a>
      </div>

      @if($needsAction->isEmpty())
        <div class="lx-empty">
          <div class="lx-empty-icon"><i class="bi bi-check2-all text-success"></i></div>
          <div class="lx-empty-title">Semua beres!</div>
          <div class="small">Tidak ada booking yang menunggu validasi bukti QRIS atau pelunasan saat ini.</div>
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Pemesan</th>
                <th>Lapangan & Jadwal</th>
                <th class="text-center">Bukti QRIS</th>
                <th class="text-end">Tagihan</th>
                <th class="text-end">Aksi Cepat</th>
              </tr>
            </thead>
            <tbody>
              @foreach($needsAction as $b)
                @php
                  $start = \Carbon\Carbon::parse($b->start_dt);
                  $end   = \Carbon\Carbon::parse($b->end_dt);
                  $nominalQris = $b->payment_method === 'dp' ? $b->dp_amount : $b->total_price;
                @endphp
                <tr class="lx-row-link" data-href="{{ route($prefix . '.bookings.show', $b) }}">
                  <td>
                    <div class="fw-semibold text-dark">{{ $b->display_name }}</div>
                    <div class="text-muted small font-mono">#{{ $b->formatted_invoice_no }} · {{ $b->display_phone }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $b->court->name }}</div>
                    <div class="text-muted small">{{ $start->format('d M') }} · {{ $start->format('H:i') }}–{{ $end->format('H:i') }}</div>
                  </td>
                  <td class="text-center">
                    @if($b->payment_proof)
                      <span class="lx-pill lx-pill-info"><i class="bi bi-image me-1"></i>Ada Bukti</span>
                    @else
                      <span class="badge bg-light text-muted border">Belum Upload</span>
                    @endif
                  </td>
                  <td class="text-end">
                    @if($b->status === 'pending')
                      <div class="fw-bold text-dark">Rp{{ number_format($nominalQris, 0, ',', '.') }}</div>
                      <span class="lx-pill lx-pill-warning">Menunggu Approve ({{ strtoupper($b->payment_method) }})</span>
                    @elseif($b->remaining_amount > 0)
                      <div class="fw-bold text-danger">Sisa Rp{{ number_format($b->remaining_amount, 0, ',', '.') }}</div>
                      <span class="lx-pill lx-pill-info">Approved: {{ $b->approver_name ?: 'Admin' }}</span>
                    @else
                      <span class="lx-pill lx-pill-success">Lunas</span>
                    @endif
                  </td>
                  <td class="text-end" onclick="event.stopPropagation();">
                    <div class="d-inline-flex gap-1">
                      @if($b->status === 'pending')
                        <form action="{{ route($prefix . '.bookings.confirm', $b) }}" method="POST" class="d-inline">
                          @csrf @method('PATCH')
                          <button type="submit" class="btn btn-success btn-sm" title="Approve Jadwal & Pembayaran QRIS">
                            <i class="bi bi-check2-circle me-1"></i> Approve
                          </button>
                        </form>
                      @endif
                      <a href="{{ route($prefix . '.bookings.show', $b) }}" class="btn btn-light btn-sm">
                        {{ $b->payment_proof && $b->status === 'pending' ? 'Cek Bukti' : ($b->status === 'confirmed' && $b->remaining_amount > 0 ? 'Lunasi' : 'Detail') }}
                      </a>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- Kolom Kanan: Timeline Jadwal Main Hari Ini --}}
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-calendar-day text-success"></i> Jadwal Main Hari Ini</h6>
        <a href="{{ route($prefix . '.bookings.schedule') }}" class="btn btn-ghost btn-sm">Layar Penuh <i class="bi bi-arrow-right ms-1"></i></a>
      </div>

      @if($todayBookings->isEmpty())
        <div class="lx-empty">
          <div class="lx-empty-icon"><i class="bi bi-calendar3"></i></div>
          <div class="lx-empty-title">Belum ada jadwal hari ini</div>
          <div class="small">Reservasi untuk hari ini akan otomatis tampil sesuai urutan jam mulai.</div>
        </div>
      @else
        <div class="lx-list">
          @foreach($todayBookings as $b)
            @php
              $start = \Carbon\Carbon::parse($b->start_dt);
              $end   = \Carbon\Carbon::parse($b->end_dt);
            @endphp
            <a href="{{ route($prefix . '.bookings.show', $b) }}" class="lx-list-item">
              <div class="lx-time-chip">
                {{ $start->format('H:i') }}
                <small>{{ $end->format('H:i') }}</small>
              </div>
              <div class="flex-grow-1" style="min-width:0">
                <div class="fw-semibold text-dark text-truncate">{{ $b->display_name }}</div>
                <div class="text-muted small text-truncate">
                  {{ $b->court->name }} · {{ $b->duration_hours }} jam
                  @if($b->approver_name)
                    · <span class="text-success">Acc: {{ $b->approver_name }}</span>
                  @endif
                </div>
              </div>
              <div class="text-end">
                @if($b->status === 'pending')
                  <span class="lx-pill lx-pill-warning">{{ $b->payment_proof ? 'Bukti Masuk' : 'Menunggu Approve' }}</span>
                @elseif($b->is_fully_paid)
                  <span class="lx-pill lx-pill-success">Approved & Lunas</span>
                @else
                  <span class="lx-pill lx-pill-info">DP · Sisa Rp{{ number_format($b->remaining_amount / 1000, 0) }}rb</span>
                @endif
              </div>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
