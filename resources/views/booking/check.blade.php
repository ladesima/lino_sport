@extends('layouts.app')
@section('title', 'Cek Status Booking')

@section('content')
<div class="row justify-content-center" data-aos="fade-up">
  <div class="col-lg-8">
    <div class="card card-custom border-0 shadow-sm mb-4">
      <div class="card-body p-4 p-md-5 text-center">
        <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;">
          <i class="bi bi-search fs-2"></i>
        </div>
        <h4 class="fw-bold mb-1">Cek Status & Invoice Booking</h4>
        <p class="text-muted small mb-4">Masukkan Nomor WhatsApp atau ID Booking yang Anda gunakan saat memesan.</p>

        <form action="{{ route('booking.check') }}" method="GET" class="row g-2 justify-content-center">
          <div class="col-md-8">
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" name="keyword" class="form-control border-start-0" required placeholder="Nomor WhatsApp / ID Booking" value="{{ request('keyword') }}">
            </div>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-gradient-primary btn-lg w-100 fw-bold">
              Cari Booking
            </button>
          </div>
        </form>
      </div>
    </div>

    @if($searched)
      <h5 class="fw-bold mb-3">Hasil Pencarian ({{ $bookings->count() }} Booking Ditemukan)</h5>
      @if($bookings->isEmpty())
        <div class="card card-custom text-center py-5 border-0 bg-white shadow-sm">
          <div class="p-4">
            <i class="bi bi-emoji-frown text-muted fs-1 mb-2 d-block"></i>
            <h6 class="fw-bold text-dark">Data Tidak Ditemukan</h6>
            <p class="text-muted small mb-0">Pastikan nomor WhatsApp atau email yang Anda masukkan persis sama saat memesan.</p>
          </div>
        </div>
      @else
        <div class="card card-custom border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">ID</th>
                  <th>Pemesan</th>
                  <th>Lapangan</th>
                  <th>Jadwal Main</th>
                  <th>Tagihan</th>
                  <th>Status Approval</th>
                  <th class="pe-4 text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($bookings as $b)
                <tr>
                  <td class="ps-4 fw-bold">#{{ $b->id }}</td>
                  <td>
                    <div class="fw-bold text-dark">{{ $b->customer_name }}</div>
                    <small class="text-muted">{{ $b->customer_phone }}</small>
                  </td>
                  <td>{{ $b->court->name }}</td>
                  <td>
                    <div class="small fw-semibold text-dark">{{ \Carbon\Carbon::parse($b->start_dt)->format('d M Y') }}</div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($b->start_dt)->format('H:i') }} - {{ \Carbon\Carbon::parse($b->end_dt)->format('H:i') }} WIB</small>
                  </td>
                  <td>
                    <div class="fw-bold text-dark">Rp{{ number_format($b->total_price, 0, ',', '.') }}</div>
                    <small class="text-uppercase text-muted" style="font-size:0.7rem;">QRIS {{ $b->payment_method }}</small>
                  </td>
                  <td>
                    @if($b->status === 'confirmed')
                      <span class="badge bg-success rounded-pill px-3 py-1">Approved</span>
                      <div class="small text-muted mt-1" style="font-size:0.72rem;">Oleh: {{ $b->approver_name ?: 'Admin' }}</div>
                    @elseif($b->status === 'pending')
                      <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Menunggu Approve Admin</span>
                      @if($b->payment_proof)
                        <div class="small text-success fw-semibold mt-1" style="font-size:0.72rem;">&#10003; Bukti QRIS Terupload</div>
                      @else
                        <div class="small text-danger fw-semibold mt-1" style="font-size:0.72rem;">! Belum Upload Bukti</div>
                      @endif
                    @else
                      <span class="badge bg-danger rounded-pill px-3 py-1">Dibatalkan</span>
                    @endif
                  </td>
                  <td class="pe-4 text-end">
                    <a href="{{ route('booking.confirm', $b) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                      {{ $b->status === 'pending' ? ($b->payment_proof ? 'Cek Status & Bukti' : 'QRIS & Upload Bukti') : 'Buka Invoice' }} &rarr;
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    @endif
  </div>
</div>
@endsection
