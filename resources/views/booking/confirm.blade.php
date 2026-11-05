@extends('layouts.app')
@section('title', 'Pembayaran QRIS & Upload Bukti #' . $booking->id)

@section('content')
@php
  $nominalToPay = $booking->payment_method === 'dp' ? $booking->dp_amount : $booking->total_price;
@endphp

<div class="row justify-content-center" data-aos="zoom-in">
  <div class="col-lg-7">
    <div class="card card-custom border-0 shadow-lg">
      <div class="card-header bg-white py-4 text-center border-bottom">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-bold text-uppercase mb-2">Invoice #{{ $booking->formatted_invoice_no }}</span>
        <h4 class="mb-0 fw-extrabold text-dark" style="font-weight: 800;">Pembayaran QRIS Statis & Upload Bukti</h4>
      </div>

      <div class="card-body p-4 p-md-5">

        <!-- Status Box Interaktif -->
        <div class="text-center mb-4">
          @if($booking->status === 'confirmed' && $booking->is_fully_paid)
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width:80px;height:80px;background: linear-gradient(135deg, #10b981, #059669);">
              <i class="bi bi-check-lg text-white" style="font-size:2.8rem"></i>
            </div>
            <h4 class="fw-bold text-success mb-1">Jadwal Di-Approve & Lunas!</h4>
            <p class="text-muted small mb-2">Bukti pembayaran Anda telah divalidasi. Slot lapangan resmi <strong>DISETUJUI (APPROVED) & LUNAS</strong>.</p>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
              <i class="bi bi-person-check-fill me-1"></i> Di-Approve oleh Admin: <strong>{{ $booking->approver_name ?: 'Administrator' }}</strong>
              @if($booking->approved_at_formatted)
                ({{ $booking->approved_at_formatted }})
              @endif
            </span>

          @elseif($booking->status === 'confirmed')
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width:80px;height:80px;background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
              <i class="bi bi-shield-check text-white" style="font-size:2.5rem"></i>
            </div>
            <h4 class="fw-bold text-primary mb-1">Jadwal Di-Approve Admin (DP Diterima)!</h4>
            <p class="text-muted small mb-2">Bukti pembayaran DP Anda telah divalidasi. Slot lapangan resmi terkunci.</p>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
              <i class="bi bi-person-check-fill me-1"></i> Di-Approve oleh Admin: <strong>{{ $booking->approver_name ?: 'Administrator' }}</strong>
              @if($booking->approved_at_formatted)
                ({{ $booking->approved_at_formatted }})
              @endif
            </span>

          @elseif($booking->status === 'cancelled')
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:80px;height:80px;background: #fef2f2;">
              <i class="bi bi-x-circle text-danger" style="font-size:2.5rem"></i>
            </div>
            <h4 class="fw-bold text-danger mb-1">Booking Dibatalkan</h4>
            <p class="text-muted small">Pesanan ini telah dibatalkan.</p>

          @elseif($booking->payment_proof)
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:80px;height:80px;background: #eff6ff;">
              <i class="bi bi-file-earmark-check-fill text-primary" style="font-size:2.5rem"></i>
            </div>
            <div class="mb-2">
              <span class="badge bg-primary px-3 py-1.5 rounded-pill fw-bold">
                <i class="bi bi-hourglass-split me-1"></i> BUKTI QRIS SUDAH DIUPLOAD &bull; MENUNGGU APPROVE ADMIN
              </span>
            </div>
            <h4 class="fw-bold text-dark mb-1">Menunggu Validasi & Approval Admin</h4>
            <p class="text-muted small mb-0">
              Bukti pembayaran QRIS Anda sudah diterima sistem dan sedang diperiksa oleh Admin. Halaman ini akan menampilkan nama Admin begitu pesanan di-approve.
            </p>

          @else
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:80px;height:80px;background: #fffbeb;">
              <i class="bi bi-qr-code-scan text-warning" style="font-size:2.5rem"></i>
            </div>
            <div class="mb-2">
              <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                <i class="bi bi-clock-history me-1"></i> STATUS: MENUNGGU UPLOAD BUKTI QRIS & APPROVAL ADMIN
              </span>
            </div>
            <h4 class="fw-bold text-dark mb-1">Scan QRIS & Upload Bukti Pembayaran</h4>
            <p class="text-muted small mb-0">
              Silakan scan <strong>QRIS Statis</strong> di bawah ini sesuai nominal tagihan, lalu <strong>upload foto/screenshot bukti pembayaran</strong> langsung pada form di bawah untuk divalidasi oleh Admin.
            </p>
          @endif
        </div>

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 mb-4 small shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger border-0 rounded-4 mb-4 small shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
          </div>
        @endif

        <!-- ============================================== -->
        <!-- KARTU QRIS STATIS & FORM UPLOAD BUKTI BAYAR    -->
        <!-- ============================================== -->
        @if($booking->status === 'pending')
          <div class="card border-2 border-success rounded-4 mb-4 overflow-hidden shadow-sm">
            <div class="bg-success text-white py-3 px-4 d-flex justify-content-between align-items-center">
              <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-qr-code-scan fs-5"></i>
                <span>1. SCAN QRIS STATIS</span>
              </div>
              <span class="badge bg-white text-success fw-bold">LINO SPORT CENTER</span>
            </div>

            <div class="card-body p-4 text-center bg-white">
              <p class="text-muted small mb-2">Scan kode QRIS di bawah ini menggunakan aplikasi Mobile Banking / E-Wallet (GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, BRI, BNI, dll):</p>

              <!-- Kotak QRIS Statis -->
              <div class="d-inline-block p-3 bg-white border border-2 rounded-4 shadow-sm my-2" style="max-width: 280px; width: 100%;">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                  <span class="fw-extrabold text-dark small" style="letter-spacing: 0.5px;">QRIS</span>
                  <span class="badge bg-danger" style="font-size: 0.65rem;">GPN &bull; STANDAR NASIONAL</span>
                </div>
                <div class="fw-bold text-dark small">LINO SPORT CENTER</div>
                <div class="text-muted mb-2" style="font-size: 0.7rem;">NMID: ID1026000881920</div>

                @if(file_exists(public_path('images/qris.png')))
                  <img src="{{ asset('images/qris.png') }}" alt="QRIS Statis Lino Sport Center" class="img-fluid rounded-3 border p-1" style="max-height: 220px;">
                @elseif(file_exists(public_path('images/qris.jpg')))
                  <img src="{{ asset('images/qris.jpg') }}" alt="QRIS Statis Lino Sport Center" class="img-fluid rounded-3 border p-1" style="max-height: 220px;">
                @else
                  <div class="p-2 bg-white border rounded-3 d-inline-block">
                    <svg viewBox="0 0 210 210" width="185" height="185" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QRIS Statis Lino Sport Center">
                      <rect width="210" height="210" fill="#ffffff"/>
                      <rect x="10" y="10" width="56" height="56" fill="#000"/>
                      <rect x="18" y="18" width="40" height="40" fill="#fff"/>
                      <rect x="26" y="26" width="24" height="24" fill="#000"/>
                      <rect x="144" y="10" width="56" height="56" fill="#000"/>
                      <rect x="152" y="18" width="40" height="40" fill="#fff"/>
                      <rect x="160" y="26" width="24" height="24" fill="#000"/>
                      <rect x="10" y="144" width="56" height="56" fill="#000"/>
                      <rect x="18" y="152" width="40" height="40" fill="#fff"/>
                      <rect x="26" y="160" width="24" height="24" fill="#000"/>
                      <rect x="76" y="14" width="10" height="10" fill="#000"/>
                      <rect x="96" y="14" width="20" height="10" fill="#000"/>
                      <rect x="124" y="14" width="10" height="20" fill="#000"/>
                      <rect x="76" y="34" width="20" height="10" fill="#000"/>
                      <rect x="106" y="34" width="10" height="20" fill="#000"/>
                      <rect x="76" y="54" width="10" height="10" fill="#000"/>
                      <rect x="124" y="44" width="10" height="20" fill="#000"/>
                      <rect x="14" y="76" width="20" height="10" fill="#000"/>
                      <rect x="44" y="76" width="20" height="10" fill="#000"/>
                      <rect x="14" y="96" width="10" height="20" fill="#000"/>
                      <rect x="34" y="106" width="20" height="10" fill="#000"/>
                      <rect x="54" y="124" width="10" height="10" fill="#000"/>
                      <rect x="76" y="76" width="18" height="18" fill="#064e3b"/>
                      <rect x="104" y="76" width="12" height="12" fill="#000"/>
                      <rect x="124" y="76" width="10" height="20" fill="#000"/>
                      <rect x="76" y="104" width="12" height="20" fill="#000"/>
                      <rect x="98" y="98" width="22" height="22" fill="#059669"/>
                      <rect x="128" y="106" width="10" height="18" fill="#000"/>
                      <rect x="148" y="76" width="20" height="10" fill="#000"/>
                      <rect x="178" y="76" width="20" height="20" fill="#000"/>
                      <rect x="148" y="96" width="12" height="20" fill="#000"/>
                      <rect x="168" y="106" width="20" height="10" fill="#000"/>
                      <rect x="144" y="126" width="16" height="10" fill="#000"/>
                      <rect x="172" y="126" width="26" height="10" fill="#000"/>
                      <rect x="76" y="136" width="20" height="10" fill="#000"/>
                      <rect x="106" y="136" width="10" height="20" fill="#000"/>
                      <rect x="126" y="144" width="20" height="10" fill="#000"/>
                      <rect x="76" y="156" width="12" height="20" fill="#000"/>
                      <rect x="96" y="166" width="20" height="10" fill="#000"/>
                      <rect x="126" y="166" width="10" height="30" fill="#000"/>
                      <rect x="76" y="186" width="26" height="12" fill="#000"/>
                      <rect x="148" y="148" width="32" height="32" fill="#000"/>
                      <rect x="156" y="156" width="16" height="16" fill="#fff"/>
                      <rect x="161" y="161" width="6" height="6" fill="#000"/>
                      <rect x="186" y="148" width="12" height="24" fill="#000"/>
                      <rect x="148" y="186" width="50" height="12" fill="#000"/>
                    </svg>
                  </div>
                @endif
              </div>

              <!-- Nominal Transfer -->
              <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-25 mt-2 mx-auto" style="max-width: 390px;">
                <div class="small text-muted fw-semibold">Nominal yang Harus Ditransfer ({{ $booking->payment_method === 'dp' ? 'DP 50%' : 'Lunas 100%' }}):</div>
                <div class="fs-3 fw-extrabold text-success" style="font-weight: 800;">
                  Rp{{ number_format($nominalToPay, 0, ',', '.') }}
                </div>
                <div class="small text-muted">Pastikan nominal transfer sesuai sebelum mengupload bukti pembayaran.</div>
              </div>

              <hr class="my-4">

              <!-- ============================================== -->
              <!-- BAGIAN 2: UPLOAD BUKTI PEMBAYARAN QRIS         -->
              <!-- ============================================== -->
              <div class="text-start">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="fw-extrabold text-dark mb-0">
                    <i class="bi bi-cloud-arrow-up-fill text-success me-1"></i>
                    2. UPLOAD BUKTI PEMBAYARAN QRIS
                  </h6>
                  @if($booking->payment_proof)
                    <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Sudah Diupload</span>
                  @else
                    <span class="badge bg-warning text-dark">Wajib Upload</span>
                  @endif
                </div>

                @if($booking->payment_proof)
                  <div class="p-3 rounded-4 bg-light border mb-3 text-center">
                    <div class="small fw-bold text-success mb-1">
                      <i class="bi bi-check-circle-fill me-1"></i> Bukti Pembayaran QRIS Anda Telah Berhasil Diupload!
                      @if($booking->payment_proof_uploaded_at_formatted)
                        <span class="text-muted fw-normal">({{ $booking->payment_proof_uploaded_at_formatted }})</span>
                      @endif
                    </div>
                    <div class="alert alert-success border-0 py-2 px-3 small rounded-3 mb-3 d-inline-flex align-items-center gap-2 text-start">
                      <i class="bi bi-whatsapp text-success fs-5 flex-shrink-0"></i>
                      <span>Notifikasi otomatis telah dikirim ke WhatsApp Admin <strong>(+62 823-4379-7911)</strong> untuk verifikasi dan approval jadwal.</span>
                    </div>
                    <div>
                      <a href="{{ $booking->payment_proof_url }}" target="_blank" title="Klik untuk memperbesar gambar">
                        <img src="{{ $booking->payment_proof_url }}" alt="Bukti Pembayaran QRIS" class="img-fluid rounded-3 border shadow-sm" style="max-height: 260px; object-fit: contain;">
                      </a>
                    </div>
                    @php
                      $waFollowUpText = urlencode("Halo Admin Lino Sport Center, saya sudah upload bukti pembayaran QRIS untuk Booking #" . $booking->formatted_invoice_no . " a.n " . $booking->display_name . " (" . $booking->court->name . "). Mohon bantuannya untuk approval jadwal. Terima kasih!");
                    @endphp
                    <div class="mt-2">
                      <a href="https://wa.me/6282343797911?text={{ $waFollowUpText }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold shadow-sm">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi WhatsApp Admin (+62 823-4379-7911)
                      </a>
                    </div>
                    <div class="small text-muted mt-2">Salah upload foto? Anda tetap dapat mengupload ulang bukti pembayaran di bawah ini:</div>
                  </div>
                @endif

                <form action="{{ route('booking.upload-proof', $booking->id) }}" method="POST" enctype="multipart/form-data" id="uploadProofForm">
                  @csrf
                  <div class="p-3 rounded-4 border border-2 border-dashed bg-light mb-3 text-center" style="border-style: dashed !important; border-color: #10b981 !important;">
                    <label for="paymentProofInput" class="form-label fw-bold text-dark mb-1 d-block" style="cursor:pointer;">
                      <i class="bi bi-image text-success fs-3 d-block mb-1"></i>
                      Pilih Screenshot / Foto Bukti Transfer QRIS
                    </label>
                    <div class="text-muted small mb-2">Format: JPG, PNG, WEBP (Maks. 5 MB)</div>
                    <input type="file" name="payment_proof" id="paymentProofInput" class="form-control form-control-sm mx-auto" style="max-width: 340px;" accept="image/jpeg,image/png,image/webp" required>

                    <!-- Live Preview Sebelum Upload -->
                    <div id="livePreviewWrap" class="mt-3 d-none">
                      <div class="small fw-semibold text-success mb-1">Preview Gambar yang Akan Diupload:</div>
                      <img id="livePreviewImg" src="#" alt="Preview Bukti QRIS" class="img-fluid rounded-3 border shadow-sm" style="max-height: 230px; object-fit: contain;">
                    </div>
                  </div>

                  <button type="submit" class="btn btn-gradient-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-cloud-check-fill fs-5"></i>
                    <span>{{ $booking->payment_proof ? 'Perbarui Bukti Pembayaran QRIS' : 'Upload Bukti Pembayaran & Ajukan Approval Admin' }}</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @elseif($booking->payment_proof)
          <!-- Jika sudah Approved, tetap tampilkan preview bukti pembayaran yang telah divalidasi -->
          <div class="p-3 rounded-4 bg-light border mb-4 text-center">
            <div class="small fw-bold text-dark mb-2">
              <i class="bi bi-patch-check-fill text-success me-1"></i> Bukti Pembayaran QRIS (Telah Divalidasi oleh {{ $booking->approver_name ?: 'Admin' }})
            </div>
            <a href="{{ $booking->payment_proof_url }}" target="_blank">
              <img src="{{ $booking->payment_proof_url }}" alt="Bukti Pembayaran QRIS" class="img-fluid rounded-3 border shadow-sm" style="max-height: 220px; object-fit: contain;">
            </a>
          </div>
        @endif

        <!-- Detail Rincian Tagihan Card -->
        <div class="p-4 rounded-4 bg-light mb-4 border">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <span class="fw-bold text-dark"><i class="bi bi-person-circle me-1 text-primary"></i> Rincian Pemesanan & Jadwal</span>
            <span class="badge bg-dark">{{ $booking->court->sport === 'bulutangkis' ? 'Badminton' : ucfirst($booking->court->sport) }}</span>
          </div>

          <div class="row g-2 small">
            <div class="col-5 text-muted">Nama Pemesan:</div>
            <div class="col-7 fw-bold text-dark text-end">{{ $booking->customer_name }}</div>

            <div class="col-5 text-muted">Nomor WhatsApp:</div>
            <div class="col-7 fw-bold text-dark text-end">{{ $booking->customer_phone }}</div>

            <div class="col-5 text-muted">Status Booking:</div>
            <div class="col-7 text-end">
              @if($booking->status === 'confirmed')
                <span class="badge bg-success">Disetujui (Approved)</span>
              @elseif($booking->status === 'pending')
                @if($booking->payment_proof)
                  <span class="badge bg-primary">Bukti Diupload &bull; Menunggu Approve</span>
                @else
                  <span class="badge bg-warning text-dark">Menunggu Upload Bukti QRIS</span>
                @endif
              @else
                <span class="badge bg-secondary">Dibatalkan</span>
              @endif
            </div>

            <div class="col-5 text-muted">Di-Approve Oleh:</div>
            <div class="col-7 fw-bold text-dark text-end">
              @if($booking->status === 'confirmed')
                <span class="text-success"><i class="bi bi-patch-check-fill me-1"></i>{{ $booking->approver_name ?: 'Administrator' }}</span>
              @else
                <span class="text-muted fst-italic">Menunggu Validasi Admin</span>
              @endif
            </div>

            <div class="col-12"><hr class="my-2 opacity-15"></div>

            <div class="col-5 text-muted">Lapangan:</div>
            <div class="col-7 fw-bold text-success text-end">{{ $booking->court->name }}</div>

            <div class="col-5 text-muted">Tanggal & Jam:</div>
            <div class="col-7 fw-bold text-dark text-end">
              {{ \Carbon\Carbon::parse($booking->start_dt)->translatedFormat('d F Y') }}<br>
              <span class="text-primary">{{ \Carbon\Carbon::parse($booking->start_dt)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_dt)->format('H:i') }} WIB ({{ $booking->duration_hours }} Jam)</span>
            </div>

            <div class="col-12"><hr class="my-2 opacity-15"></div>

            <!-- RINCIAN TAGIHAN SEWA LAPANGAN -->
            <div class="col-12">
              <div class="p-3 rounded-3 bg-white border mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-bold text-dark"><i class="bi bi-calendar2-check text-success me-1"></i>Tagihan Sewa Lapangan</span>
                  @if($booking->is_fully_paid)
                    <span class="badge bg-success">Lunas</span>
                  @elseif($booking->is_dp_paid)
                    <span class="badge bg-primary">DP 50% Diterima</span>
                  @else
                    <span class="badge bg-warning text-dark">Menunggu Validasi Admin</span>
                  @endif
                </div>
                <div class="d-flex justify-content-between text-muted">
                  <span>{{ $booking->court->name }} ({{ $booking->duration_hours }} jam)</span>
                  <strong class="text-dark">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1">
                  <span>Metode Pembayaran:</span>
                  <span class="fw-semibold text-dark">QRIS Statis ({{ $booking->payment_method === 'dp' ? 'DP 50%' : 'Lunas 100%' }})</span>
                </div>
                <div class="d-flex justify-content-between text-muted mt-1">
                  <span>Sudah Diverifikasi Admin:</span>
                  <span class="text-success fw-semibold">Rp{{ number_format($booking->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-1 mt-1 fw-bold">
                  <span>Sisa Tagihan:</span>
                  <span class="{{ $booking->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                    Rp{{ number_format($booking->remaining_amount, 0, ',', '.') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tombol Cetak Struk / Bukti Pemesanan -->
        <div class="d-grid gap-2 mb-3">
          <a href="{{ route('booking.receipt', $booking->id) }}" target="_blank" class="btn {{ $booking->status === 'confirmed' ? 'btn-success py-3' : 'btn-outline-dark py-2.5' }} rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
            <i class="bi bi-printer-fill fs-5"></i>
            <span>{{ $booking->status === 'confirmed' ? 'Cetak Struk Bukti Booking Resmi' : 'Cetak Struk / Invoice Tagihan' }}</span>
          </a>
        </div>

        <div class="text-center mt-3">
          <a href="{{ route('booking.check') }}" class="btn btn-link text-decoration-none text-muted small">
            <i class="bi bi-search me-1"></i>Cek Status Booking Lainnya
          </a>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var input = document.getElementById('paymentProofInput');
  var wrap  = document.getElementById('livePreviewWrap');
  var img   = document.getElementById('livePreviewImg');

  if (input && wrap && img) {
    input.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          img.src = e.target.result;
          wrap.classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
      } else {
        wrap.classList.add('d-none');
      }
    });
  }
});
</script>
@endsection
