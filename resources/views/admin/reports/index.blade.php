@extends('layouts.admin')
@section('title', 'Laporan Keuangan & Pendapatan')

@section('content')
<div class="lx-page-head d-print-none">
  <div>
    <h1 class="lx-page-title">Laporan Keuangan & Omset</h1>
    <p class="lx-page-sub">Rekapitulasi kas masuk, sisa piutang di lokasi, dan performa pendapatan per lapangan.</p>
  </div>
  <div class="lx-page-actions">
    <button onclick="window.print()" class="lx-btn lx-btn-outline">
      <i class="bi bi-printer"></i> Cetak / Simpan PDF
    </button>
  </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="{{ route('admin.reports.index') }}" class="lx-filter d-print-none">
  <div>
    <label class="lx-label mb-1">Dari Tanggal</label>
    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
  </div>
  <div>
    <label class="lx-label mb-1">Sampai Tanggal</label>
    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
  </div>
  <div class="lx-filter-grow">
    <label class="lx-label mb-1">Fasilitas Lapangan</label>
    <select name="court_id" class="form-select form-select-sm">
      <option value="">Semua Lapangan</option>
      @foreach($courts as $c)
        <option value="{{ $c->id }}" {{ $courtId == $c->id ? 'selected' : '' }}>
          {{ $c->name }} ({{ ucfirst($c->sport) }})
        </option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="lx-label mb-1">Skema Bayar</label>
    <select name="payment_method" class="form-select form-select-sm">
      <option value="">Semua Skema</option>
      <option value="lunas" {{ $paymentMethod == 'lunas' ? 'selected' : '' }}>Lunas (100%)</option>
      <option value="dp" {{ $paymentMethod == 'dp' ? 'selected' : '' }}>DP (50%)</option>
    </select>
  </div>
  <div class="d-flex gap-2">
    <button type="submit" class="lx-btn lx-btn-primary lx-btn-sm" style="height: 36px;">
      <i class="bi bi-funnel"></i> Terapkan
    </button>
    @if(request()->hasAny(['start_date', 'end_date', 'court_id', 'payment_method']))
      <a href="{{ route('admin.reports.index') }}" class="lx-btn lx-btn-outline lx-btn-sm" style="height: 36px;">Reset</a>
    @endif
  </div>
</form>

<!-- 4 Kartu KPI Keuangan Utama -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="lx-kpi">
      <div class="lx-kpi-top">
        <span class="lx-kpi-label">Uang Diterima (Kas Masuk)</span>
        <div class="lx-kpi-icon green"><i class="bi bi-cash-stack"></i></div>
      </div>
      <div class="lx-kpi-value text-success">Rp{{ number_format($totalPendapatanMasuk, 0, ',', '.') }}</div>
      <div class="lx-kpi-meta">Pembayaran terverifikasi masuk</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="lx-kpi">
      <div class="lx-kpi-top">
        <span class="lx-kpi-label">Sisa Piutang Lokasi</span>
        <div class="lx-kpi-icon amber"><i class="bi bi-hourglass-split"></i></div>
      </div>
      <div class="lx-kpi-value {{ $totalSisaPelunasan > 0 ? 'text-warning' : '' }}">Rp{{ number_format($totalSisaPelunasan, 0, ',', '.') }}</div>
      <div class="lx-kpi-meta">Belum dilunasi pelanggan DP</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="lx-kpi">
      <div class="lx-kpi-top">
        <span class="lx-kpi-label">Total Nilai Sewa Bruto</span>
        <div class="lx-kpi-icon blue"><i class="bi bi-graph-up-arrow"></i></div>
      </div>
      <div class="lx-kpi-value">Rp{{ number_format($totalNilaiBooking, 0, ',', '.') }}</div>
      <div class="lx-kpi-meta">{{ $totalTransaksi }} transaksi terkonfirmasi</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="lx-kpi">
      <div class="lx-kpi-top">
        <span class="lx-kpi-label">Komposisi Skema Bayar</span>
        <div class="lx-kpi-icon slate"><i class="bi bi-pie-chart"></i></div>
      </div>
      <div class="d-flex justify-content-between align-items-baseline mt-1">
        <div>
          <span class="text-muted small d-block" style="font-size: 0.72rem;">LUNAS</span>
          <strong class="fs-5 text-dark">{{ $totalLunasCount }}</strong>
        </div>
        <div class="text-end">
          <span class="text-muted small d-block" style="font-size: 0.72rem;">DP 50%</span>
          <strong class="fs-5 text-warning">{{ $totalDpCount }}</strong>
        </div>
      </div>
      <div class="lx-kpi-meta">Dari total {{ $totalTransaksi }} booking</div>
    </div>
  </div>
</div>

<!-- Rincian Berdasarkan Cabang Olahraga -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="lx-card p-3 d-flex align-items-center justify-content-between">
      <div>
        <div class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.04em;">Kas Masuk Padel</div>
        <div class="fw-extrabold fs-5 text-dark mt-1">Rp{{ number_format($incomeBySport['padel'], 0, ',', '.') }}</div>
      </div>
      <span class="lx-pill lx-pill-success">Padel</span>
    </div>
  </div>
  <div class="col-md-4">
    <div class="lx-card p-3 d-flex align-items-center justify-content-between">
      <div>
        <div class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.04em;">Kas Masuk Bulutangkis</div>
        <div class="fw-extrabold fs-5 text-dark mt-1">Rp{{ number_format($incomeBySport['bulutangkis'], 0, ',', '.') }}</div>
      </div>
      <span class="lx-pill lx-pill-info">Badminton</span>
    </div>
  </div>
  <div class="col-md-4">
    <div class="lx-card p-3 d-flex align-items-center justify-content-between">
      <div>
        <div class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.04em;">Kas Masuk Pickleball</div>
        <div class="fw-extrabold fs-5 text-dark mt-1">Rp{{ number_format($incomeBySport['pickleball'], 0, ',', '.') }}</div>
      </div>
      <span class="lx-pill lx-pill-warning">Pickleball</span>
    </div>
  </div>
</div>

<!-- Tabel Rincian Transaksi Keuangan -->
<div class="lx-card">
  <div class="lx-card-header">
    <div>
      <h6 class="lx-card-title">Rincian Arus Kas Transaksi (Confirmed)</h6>
      <div class="lx-card-sub">Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="lx-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Jadwal Main</th>
          <th>Pelanggan</th>
          <th>Lapangan</th>
          <th>Tagihan Lapangan</th>
          <th>Di-Approve Oleh (Admin)</th>
          <th>Uang Masuk</th>
          <th>Sisa Piutang</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      @forelse($bookings as $b)
      @php
        $uangDiterima = $b->actual_paid_amount;
        $sisaTagihan  = $b->remaining_amount;
      @endphp
      <tr data-href="{{ route('admin.bookings.show', $b) }}">
        <td class="lx-mono fw-bold text-muted">#{{ $b->id }}</td>
        <td>
          <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($b->start_dt)->format('d M Y') }}</div>
          <div class="text-muted small lx-mono">{{ \Carbon\Carbon::parse($b->start_dt)->format('H:i') }} – {{ \Carbon\Carbon::parse($b->end_dt)->format('H:i') }}</div>
        </td>
        <td>
          <div class="fw-semibold text-dark">{{ $b->display_name }}</div>
          <div class="text-muted small lx-mono">{{ $b->display_phone }}</div>
        </td>
        <td>
          <div class="fw-semibold text-dark">{{ $b->court->name }}</div>
          <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">{{ $b->court->sport }}</span>
        </td>
        <td class="lx-mono fw-semibold">
          Rp{{ number_format($b->total_price, 0, ',', '.') }}
        </td>
        <td>
          <div class="fw-semibold text-dark">{{ $b->approver_name ?: 'Administrator' }}</div>
          @if($b->approved_at_formatted)
            <div class="text-muted small" style="font-size: 0.7rem;">{{ $b->approved_at_formatted }}</div>
          @endif
        </td>
        <td class="lx-mono fw-bold text-success">
          Rp{{ number_format($uangDiterima, 0, ',', '.') }}
        </td>
        <td class="lx-mono">
          @if($sisaTagihan > 0)
            <span class="lx-pill lx-pill-warning">Rp{{ number_format($sisaTagihan, 0, ',', '.') }}</span>
          @else
            <span class="text-muted small">—</span>
          @endif
        </td>
        <td>
          @if($sisaTagihan <= 0)
            <span class="lx-pill lx-pill-success"><span class="lx-pill-dot"></span>Lunas</span>
          @else
            <span class="lx-pill lx-pill-warning"><span class="lx-pill-dot"></span>DP 50%</span>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9">
          <div class="lx-empty">
            <div class="lx-empty-icon"><i class="bi bi-journal-x"></i></div>
            <div class="lx-empty-title">Tidak ada transaksi pada periode ini</div>
            <div class="lx-empty-sub">Ubah rentang tanggal atau filter lapangan di atas untuk melihat riwayat transaksi lainnya.</div>
          </div>
        </td>
      </tr>
      @endforelse
      </tbody>

      @if($bookings->isNotEmpty())
      <tfoot class="table-light fw-bold">
        <tr>
          <td colspan="4" class="text-end text-uppercase small text-muted">Grand Total:</td>
          <td class="lx-mono">Rp{{ number_format($bookings->sum->total_price, 0, ',', '.') }}</td>
          <td></td>
          <td class="lx-mono text-success">Rp{{ number_format($totalPendapatanMasuk, 0, ',', '.') }}</td>
          <td class="lx-mono text-warning">Rp{{ number_format($totalSisaPelunasan, 0, ',', '.') }}</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
