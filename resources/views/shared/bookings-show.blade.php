@php
  $prefix = $prefix ?? 'admin';
  $start = \Carbon\Carbon::parse($booking->start_dt);
  $end   = \Carbon\Carbon::parse($booking->end_dt);
  $durHours = $booking->duration_hours;

  $courtBasePrice = $booking->total_price;
  $courtPaid      = (int)$booking->paid_amount;
  $courtRem       = $booking->remaining_amount;
  $expectedInitialTransfer = $booking->payment_method === 'dp' ? $booking->dp_amount : $booking->total_price;
@endphp

<div class="lx-page-header">
  <div class="d-flex align-items-center gap-2">
    <a href="{{ route($prefix . '.bookings.index') }}" class="lx-icon-btn" title="Kembali ke daftar">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <h2 class="lx-page-title mb-0">Booking #{{ $booking->formatted_invoice_no }}</h2>
        @if($booking->status === 'pending')
          @if($booking->payment_proof)
            <span class="lx-pill lx-pill-info">Bukti QRIS Diupload · Siap Divalidasi</span>
          @else
            <span class="lx-pill lx-pill-warning">Menunggu Upload Bukti QRIS</span>
          @endif
        @elseif($booking->is_fully_paid)
          <span class="lx-pill lx-pill-success">Approved & Lunas</span>
        @elseif($booking->status === 'confirmed')
          <span class="lx-pill lx-pill-info">Approved (DP · Sisa Rp{{ number_format($courtRem, 0, ',', '.') }})</span>
        @else
          <span class="lx-pill lx-pill-danger">Dibatalkan</span>
        @endif
      </div>
      <p class="lx-page-sub">{{ $booking->display_name }} · {{ $booking->court->name }} · {{ $start->translatedFormat('d M Y') }} ({{ $start->format('H:i') }}–{{ $end->format('H:i') }})</p>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    @if($booking->status === 'pending')
      <form action="{{ route($prefix . '.bookings.confirm', $booking->id) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-success btn-sm">
          <i class="bi bi-check2-circle me-1"></i> Approve Jadwal Sekarang
        </button>
      </form>
    @endif
    <a href="{{ route($prefix . '.bookings.receipt', ['booking' => $booking->id, 'autoprint' => 1]) }}" target="_blank" class="btn btn-dark btn-sm">
      <i class="bi bi-printer me-1"></i> Cetak Struk
    </a>
  </div>
</div>

@if(session('print_receipt_id'))
  <iframe id="autoPrintDetailFrame"
          src="{{ route($prefix . '.bookings.receipt', ['booking' => session('print_receipt_id'), 'cash_received' => session('cash_received'), 'cash_change' => session('cash_change')]) }}"
          style="position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden"
          onload="setTimeout(function(){ try { var f = document.getElementById('autoPrintDetailFrame'); if (f && f.contentWindow) { f.contentWindow.focus(); f.contentWindow.print(); } } catch(e){} }, 250);"></iframe>
@endif

<div class="row g-4">
  {{-- Kolom Kiri: Preview Bukti QRIS, Panel Approval Admin & Rincian Tagihan Lapangan --}}
  <div class="col-lg-8">

    {{-- KARTU 1: PANEL VALIDASI BUKTI QRIS & APPROVAL ADMIN --}}
    <div class="card mb-4 overflow-hidden">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title mb-0">
          <i class="bi bi-shield-check text-success"></i> Validasi Bukti Pembayaran QRIS & Approval Jadwal
        </h6>
        <span class="badge bg-light text-dark border">Metode: QRIS Statis ({{ strtoupper($booking->payment_method) }})</span>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-center">
          {{-- Kolom Preview Gambar Bukti Pembayaran QRIS --}}
          <div class="col-md-5">
            <div class="p-3 rounded-3 border bg-light text-center h-100 d-flex flex-column justify-content-center align-items-center">
              <div class="small fw-bold text-dark mb-2">
                <i class="bi bi-image text-success me-1"></i> Preview Bukti Pembayaran QRIS
              </div>

              @if($booking->payment_proof)
                <a href="#" data-bs-toggle="modal" data-bs-target="#proofZoomModal" title="Klik untuk memperbesar bukti pembayaran">
                  <img src="{{ $booking->payment_proof_url }}" alt="Bukti Pembayaran QRIS #{{ $booking->id }}"
                       class="img-fluid rounded-3 border shadow-sm" style="max-height: 230px; object-fit: contain; background: #fff;">
                </a>
                <div class="mt-2 d-flex flex-wrap justify-content-center gap-1">
                  <button type="button" class="btn btn-light btn-sm border" data-bs-toggle="modal" data-bs-target="#proofZoomModal">
                    <i class="bi bi-zoom-in me-1"></i> Perbesar Gambar
                  </button>
                  <a href="{{ $booking->payment_proof_url }}" target="_blank" class="btn btn-light btn-sm border">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                </div>
                @if($booking->payment_proof_uploaded_at_formatted)
                  <div class="text-muted mt-1" style="font-size:0.7rem;">Diupload: {{ $booking->payment_proof_uploaded_at_formatted }}</div>
                @endif
              @else
                <div class="py-4 px-2 text-muted">
                  <i class="bi bi-image-alt fs-1 d-block mb-2 opacity-50"></i>
                  <div class="fw-semibold small text-dark">Belum Ada Bukti QRIS Diupload</div>
                  <div style="font-size:0.74rem;">Pelanggan belum mengupload foto/screenshot bukti pembayaran QRIS.</div>
                </div>
              @endif
            </div>
          </div>

          {{-- Kolom Status & Tombol Validasi / Approve --}}
          <div class="col-md-7">
            @if($booking->status === 'pending')
              <div class="p-3 rounded-3 border {{ $booking->payment_proof ? 'border-primary bg-primary bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                <div class="fw-bold text-dark mb-1">
                  @if($booking->payment_proof)
                    <i class="bi bi-check2-square text-primary me-1"></i> Bukti QRIS Siap Divalidasi Admin
                  @else
                    <i class="bi bi-hourglass-split text-warning me-1"></i> Menunggu Bukti Pembayaran & Approval
                  @endif
                </div>
                <p class="small text-muted mb-2">
                  Nominal yang wajib ditransfer pelanggan via <strong>QRIS Statis</strong>:
                  <strong class="text-dark fs-6 d-block mt-1">Rp{{ number_format($expectedInitialTransfer, 0, ',', '.') }} ({{ $booking->payment_method === 'dp' ? 'DP 50%' : 'Lunas 100%' }})</strong>
                </p>
                <div class="small text-muted mb-3">
                  Periksa kesesuaian nominal pada <strong>Preview Gambar Bukti QRIS</strong> di samping. Saat Anda menekan tombol <strong>Approve</strong>, nama Anda (<strong>{{ auth()->user()?->name }}</strong>) akan tercatat sebagai Admin yang meng-approve pesanan ini.
                </div>

                <div class="d-flex flex-wrap gap-2">
                  <form action="{{ route($prefix . '.bookings.confirm', $booking->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success fw-bold">
                      <i class="bi bi-check2-circle me-1"></i> Validasi Bukti & Approve Jadwal (Rp{{ number_format($expectedInitialTransfer, 0, ',', '.') }})
                    </button>
                  </form>
                  <form action="{{ route($prefix . '.bookings.cancel', $booking->id) }}" method="POST"
                        data-confirm="Batalkan pesanan #{{ $booking->formatted_invoice_no }}?"
                        data-confirm-title="Tolak / Batalkan Booking" data-confirm-ok="Ya, batalkan" data-confirm-variant="danger">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger">
                      <i class="bi bi-x-circle me-1"></i> Tolak / Batalkan
                    </button>
                  </form>
                </div>
              </div>
            @elseif($booking->status === 'confirmed')
              <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="bi bi-person-check-fill fs-5"></i>
                  </div>
                  <div>
                    <div class="small text-muted text-uppercase fw-bold" style="font-size:.7rem;">Jadwal Telah Divalidasi & Di-Approve Oleh Admin</div>
                    <div class="fw-bold text-dark fs-5">{{ $booking->approver_name ?: 'Administrator' }}</div>
                    @if($booking->approved_at_formatted)
                      <div class="small text-muted">Waktu Approval: {{ $booking->approved_at_formatted }}</div>
                    @endif
                  </div>
                </div>
                <span class="lx-pill lx-pill-success">Slot Terkunci Resmi</span>
              </div>
            @else
              <div class="p-3 rounded-3 border bg-light text-muted small">
                Pesanan ini telah dibatalkan.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- KARTU 2: TAGIHAN SEWA LAPANGAN --}}
    <div class="card overflow-hidden">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <h6 class="lx-card-title mb-0"><i class="bi bi-calendar2-check text-success"></i> Rincian Tagihan Sewa Lapangan</h6>
          @if($courtRem <= 0)
            <span class="lx-pill lx-pill-success">Lunas</span>
          @elseif($courtPaid > 0)
            <span class="lx-pill lx-pill-warning">DP 50% Diterima</span>
          @else
            <span class="lx-pill lx-pill-danger">Belum Divalidasi</span>
          @endif
        </div>
        <span class="text-muted small text-uppercase fw-semibold">Skema: QRIS {{ $booking->payment_method }}</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4">Lapangan & Jadwal Main</th>
              <th class="text-center">Durasi</th>
              <th class="text-end">Tarif Rata-rata / Jam</th>
              <th class="text-end pe-4">Total Sewa Lapangan</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="ps-4">
                <div class="fw-semibold text-dark">{{ $booking->court->name }}</div>
                <div class="text-muted small">{{ ucfirst($booking->court->sport) }} · {{ $start->format('d M Y') }} ({{ $start->format('H:i') }}–{{ $end->format('H:i') }} WIB)</div>
              </td>
              <td class="text-center">{{ $durHours }} jam</td>
              <td class="text-end text-muted">Rp{{ number_format($courtBasePrice / max(1, $durHours), 0, ',', '.') }}/jam</td>
              <td class="text-end fw-semibold text-dark pe-4">Rp{{ number_format($courtBasePrice, 0, ',', '.') }}</td>
            </tr>
          </tbody>
          <tfoot class="border-top" style="background:#f8fafc">
            <tr>
              <td colspan="3" class="text-end text-muted ps-4">Sudah Divalidasi / Dibayar</td>
              <td class="text-end fw-semibold text-success pe-4">Rp{{ number_format($courtPaid, 0, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-end fw-bold text-dark ps-4">Sisa Tagihan Lapangan</td>
              <td class="text-end fw-extrabold {{ $courtRem > 0 ? 'text-danger' : 'text-success' }} pe-4">
                @if($courtRem <= 0) Rp 0 (LUNAS) @else Rp{{ number_format($courtRem, 0, ',', '.') }} @endif
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>

  {{-- Kolom Kanan: Pelunasan Sisa DP & Data Pemesan --}}
  <div class="col-lg-4">

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-wallet2 text-success"></i> Pelunasan & Cetak Struk</h6>
      </div>
      <div class="card-body">
        <div class="p-3 rounded-3 bg-light border mb-3 small">
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <span class="text-muted">Total Sewa Lapangan</span>
            <strong class="text-dark">Rp{{ number_format($courtBasePrice, 0, ',', '.') }}</strong>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <span class="text-muted">Sudah Dibayar</span>
            <strong class="text-success">Rp{{ number_format($courtPaid, 0, ',', '.') }}</strong>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-1">
            <span class="fw-bold text-dark">Sisa Tagihan</span>
            <span class="fs-5 fw-extrabold {{ $booking->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
              Rp{{ number_format($booking->remaining_amount, 0, ',', '.') }}
            </span>
          </div>
        </div>

        @if($booking->remaining_amount > 0 && $booking->status !== 'cancelled')
          <form action="{{ route($prefix . '.bookings.cash-settlement', $booking->id) }}" method="POST" id="deskCashForm">
            @csrf

            <label class="form-label">Nominal Pelunasan Diterima (QRIS / Tunai)</label>
            <div class="input-group mb-2">
              <span class="input-group-text">Rp</span>
              <input type="number" name="cash_received" id="deskCashInput" class="form-control fw-bold" value="{{ $booking->remaining_amount }}" min="1" required>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-2">
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-desk" data-val="exact">Uang Pas</button>
              @foreach([50000, 100000, 150000, 200000, 300000] as $p)
                <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-desk" data-val="{{ $p }}">{{ number_format($p/1000, 0) }}rb</button>
              @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center p-2 rounded-2 bg-light border mb-3">
              <span class="small text-muted">Kembalian</span>
              <span class="fw-bold font-mono text-success" id="deskChangeLabel">Rp 0</span>
            </div>

            <button type="submit" id="btnSubmitDeskCash" class="btn btn-primary w-100">
              <i class="bi bi-check2-all me-1"></i> Verifikasi Lunas 100% & Cetak Struk
            </button>
          </form>
        @else
          <div class="text-center py-3">
            <div class="lx-empty-icon mx-auto mb-2" style="background:#ecfdf5;color:#059669">
              <i class="bi bi-check-lg"></i>
            </div>
            <div class="fw-bold text-dark">Tagihan Lapangan Sudah Lunas</div>
            <p class="text-muted small mb-3">Di-approve oleh: <strong>{{ $booking->approver_name ?: 'Administrator' }}</strong></p>
            <a href="{{ route($prefix . '.bookings.receipt', $booking->id) }}" target="_blank" class="btn btn-primary w-100">
              <i class="bi bi-printer me-1"></i> Cetak Struk Lunas
            </a>
          </div>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-person text-muted"></i> Data Pemesan</h6>
      </div>
      <div class="card-body small">
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted">Nama Pemesan</span>
          <strong class="text-dark">{{ $booking->display_name }}</strong>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted">Nomor Telepon / WA</span>
          <span class="font-mono text-dark">{{ $booking->display_phone }}</span>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted">Status Bukti QRIS</span>
          <strong class="{{ $booking->payment_proof ? 'text-primary' : 'text-warning' }}">
            {{ $booking->payment_proof ? 'Sudah Diupload' : 'Belum Upload' }}
          </strong>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted">Di-Approve Oleh</span>
          <strong class="text-success">{{ $booking->approver_name ?: 'Menunggu Approve' }}</strong>
        </div>
        <div class="d-flex justify-content-between pt-2">
          <span class="text-muted">Halaman QRIS Pelanggan</span>
          <a href="{{ route('booking.confirm', $booking->id) }}" target="_blank" class="text-decoration-none">
            Buka <i class="bi bi-box-arrow-up-right"></i>
          </a>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Modal Zoom Preview Gambar Bukti Pembayaran QRIS --}}
@if($booking->payment_proof)
<div class="modal fade" id="proofZoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0">
        <h6 class="modal-title fw-bold mb-0">
          <i class="bi bi-image me-1 text-warning"></i> Bukti Pembayaran QRIS — Booking #{{ $booking->formatted_invoice_no }} ({{ $booking->display_name }})
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body bg-light text-center p-4">
        <img src="{{ $booking->payment_proof_url }}" alt="Bukti Pembayaran QRIS" class="img-fluid rounded-3 border shadow" style="max-height: 75vh; object-fit: contain;">
      </div>
      <div class="modal-footer justify-content-between">
        <div class="small text-muted">
          Nominal Wajib Transfer ({{ strtoupper($booking->payment_method) }}): <strong class="text-dark">Rp{{ number_format($expectedInitialTransfer, 0, ',', '.') }}</strong>
        </div>
        <div class="d-flex gap-2">
          @if($booking->status === 'pending')
            <form action="{{ route($prefix . '.bookings.confirm', $booking->id) }}" method="POST">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-success fw-bold">
                <i class="bi bi-check2-circle me-1"></i> Validasi & Approve Sekarang
              </button>
            </form>
          @endif
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var cashInput = document.getElementById('deskCashInput');
  var changeLabel = document.getElementById('deskChangeLabel');
  var submitBtn = document.getElementById('btnSubmitDeskCash');
  var targetAmt = {{ (int)$booking->remaining_amount }};

  function calcChange() {
    if (!cashInput || !changeLabel) return;
    var val = parseFloat(cashInput.value) || 0;
    var diff = val - targetAmt;
    if (diff < 0) {
      changeLabel.textContent = 'Kurang Rp ' + Math.abs(diff).toLocaleString('id-ID');
      changeLabel.className = 'fw-bold font-mono text-danger';
      if (submitBtn) submitBtn.disabled = true;
    } else {
      changeLabel.textContent = 'Rp ' + diff.toLocaleString('id-ID');
      changeLabel.className = 'fw-bold font-mono text-success';
      if (submitBtn) submitBtn.disabled = false;
    }
  }

  if (cashInput) {
    cashInput.addEventListener('input', calcChange);
    document.querySelectorAll('.quick-cash-desk').forEach(function (btn) {
      btn.addEventListener('click', function () {
        cashInput.value = this.dataset.val === 'exact' ? targetAmt : this.dataset.val;
        calcChange();
      });
    });
    calcChange();
  }
});
</script>
@endsection
