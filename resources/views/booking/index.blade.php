@extends('layouts.app')
@section('title', 'Riwayat Booking Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
  <div>
    <span class="text-success fw-bold text-uppercase small letter-spacing-1">Member Dashboard</span>
    <h3 class="fw-bold mb-0">Riwayat Booking Saya</h3>
  </div>
  <a href="{{ route('booking.create') }}" class="btn btn-gradient-primary px-4 py-2">
    <i class="bi bi-calendar-plus me-1"></i> Booking Baru
  </a>
</div>

@if($bookings->isEmpty())
<div class="card card-custom text-center py-5 border-0 bg-white shadow-sm" data-aos="fade-up">
  <div class="p-4">
    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
      <i class="bi bi-calendar2-x text-muted fs-1"></i>
    </div>
    <h5 class="fw-bold text-dark">Belum Ada Reservasi</h5>
    <p class="text-muted small mb-4">Anda belum pernah melakukan booking lapangan. Pilih lapangan favorit Anda sekarang!</p>
    <a href="{{ route('booking.create') }}" class="btn btn-gradient-primary px-4 py-2">
      Mulai Booking Lapangan
    </a>
  </div>
</div>
@else
<div class="card card-custom border-0 shadow-sm" data-aos="fade-up">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-4">ID</th>
          <th>Lapangan</th>
          <th>Jadwal Main</th>
          <th>Total Tarif</th>
          <th>Metode</th>
          <th>Status Slot</th>
          <th>Pembayaran</th>
          <th class="pe-4 text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @foreach($bookings as $b)
      <tr>
        <td class="ps-4 fw-bold">#{{ $b->id }}</td>
        <td>
          <div class="fw-bold text-dark">{{ $b->court->name }}</div>
          <span class="badge bg-light text-muted border text-uppercase" style="font-size: 0.65rem;">{{ $b->court->sport }}</span>
        </td>
        <td>
          <div class="small fw-semibold text-dark">{{ \Carbon\Carbon::parse($b->start_dt)->format('d M Y') }}</div>
          <small class="text-muted">{{ \Carbon\Carbon::parse($b->start_dt)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($b->end_dt)->format('H:i') }} WIB</small>
        </td>
        <td>
          <div class="fw-bold text-dark">Rp{{ number_format($b->total_price, 0, ',', '.') }}</div>
          @if($b->payment_method === 'dp')
            <small class="text-warning d-block">DP: Rp{{ number_format($b->dp_amount, 0, ',', '.') }}</small>
          @endif
        </td>
        <td><span class="badge bg-primary text-uppercase">{{ $b->payment_method }}</span></td>
        <td>
          @if($b->status === 'confirmed')
            <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
          @elseif($b->status === 'pending')
            <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
          @else
            <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i>Cancelled</span>
          @endif
        </td>
        <td>
          @if($b->payment_status === 'paid')
            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1"><i class="bi bi-check-lg me-1"></i>Paid</span>
          @else
            <span class="badge bg-light text-muted border px-2 py-1">Unpaid</span>
          @endif
        </td>
        <td class="pe-4 text-end">
          <a href="{{ route('booking.confirm', $b) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
            Detail & Bayar &rarr;
          </a>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
