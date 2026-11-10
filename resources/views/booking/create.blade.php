@extends('layouts.app')
@section('title', 'Pilih Lapangan & Booking')

@section('content')
<div class="mb-4 text-center text-md-start" data-aos="fade-up">
  <span class="text-success fw-bold text-uppercase small letter-spacing-1">Langkah 1 dari 2</span>
  <h3 class="fw-bold mb-1">Pilih Lapangan Olahraga</h3>
  <p class="text-muted small">Klik salah satu lapangan di bawah ini untuk memunculkan jadwal ketersediaan jam & formulir pemesanan.</p>
</div>

<!-- STEP 1: PILIH KARTU LAPANGAN DULU -->
<div class="row g-3 mb-5" id="step1Courts" data-aos="fade-up">
  @php
    $defaultImages = [
      'padel' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80',
      'bulutangkis' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=800&q=80',
      'pickleball' => 'https://images.unsplash.com/photo-1599586120429-48281b6f0ece?auto=format&fit=crop&w=800&q=80'
    ];
  @endphp

  @foreach($courts as $court)
    @php
      $courtImg = $court->image_url ?: ($defaultImages[$court->sport] ?? $defaultImages['padel']);
      $sportRates = \App\Models\Rate::getSportRatesMap($court->sport);
      $equipInfos = \App\Models\Rate::sportEquipmentInfo($court->sport);
      $sportLabel = $court->sport === 'bulutangkis' ? 'Badminton' : ucfirst($court->sport);
    @endphp
    <div class="col-md-6 col-lg-4">
      <div class="card card-custom h-100 court-card cursor-pointer position-relative {{ $selectedCourtId == $court->id ? 'selected-court' : '' }}" 
           data-court-id="{{ $court->id }}" 
           data-court-name="{{ $court->name }}"
           data-court-sport="{{ $sportLabel }}"
           style="cursor: pointer; transition: all 0.3s ease;">
        
        <div class="position-relative overflow-hidden" style="height: 180px;">
          <img src="{{ $courtImg }}" alt="{{ $court->name }}" class="w-100 h-100" style="object-fit: cover;">
          <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-80 text-uppercase px-3 py-2 rounded-pill small">
            {{ $sportLabel }}
          </span>
          <div class="select-indicator position-absolute top-0 end-0 m-2 {{ $selectedCourtId == $court->id ? '' : 'd-none' }}">
            <span class="badge bg-success rounded-circle p-2 shadow"><i class="bi bi-check-lg fs-6"></i></span>
          </div>
        </div>

        <div class="card-body p-3 d-flex flex-column">
          <h5 class="fw-bold mb-2 text-dark">{{ $court->name }}</h5>
          <div class="p-2.5 bg-light rounded-3 border mb-2 small">
            <div class="fw-bold text-dark mb-1" style="font-size:0.75rem;">Senin – Jumat:</div>
            <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
              <span>Pukul 08.00 – 16.00:</span>
              <strong class="text-dark">Rp{{ number_format($sportRates['weekday_day'], 0, ',', '.') }}/jam</strong>
            </div>
            <div class="d-flex justify-content-between text-muted mb-1.5" style="font-size:0.76rem;">
              <span>Pukul 16.00 – 23.00:</span>
              <strong class="text-success">Rp{{ number_format($sportRates['weekday_night'], 0, ',', '.') }}/jam</strong>
            </div>
            <hr class="my-1.5 opacity-15">
            <div class="fw-bold text-dark mb-1" style="font-size:0.75rem;">Sabtu – Minggu:</div>
            <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
              <span>Pukul 08.00 – 16.00:</span>
              <strong class="text-dark">Rp{{ number_format($sportRates['weekend_day'], 0, ',', '.') }}/jam</strong>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
              <span>Pukul 16.00 – 23.00:</span>
              <strong class="text-success">Rp{{ number_format($sportRates['weekend_night'], 0, ',', '.') }}/jam</strong>
            </div>
          </div>
          @if(!empty($equipInfos))
            <div class="px-2.5 py-1.5 rounded-3 bg-white border mb-3" style="font-size:0.72rem;">
              @foreach($equipInfos as $eqInfo)
                <div class="d-flex justify-content-between text-muted">
                  <span>{{ $eqInfo['label'] }}:</span>
                  <strong class="text-dark">{{ $eqInfo['price'] }}</strong>
                </div>
              @endforeach
            </div>
          @endif
          <button type="button" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto btn-select-court">
            <i class="bi bi-calendar2-week me-1"></i> Pilih &amp; Lihat Jadwal
          </button>
        </div>
      </div>
    </div>
  @endforeach

  @php
    $gymPricing = \App\Models\Rate::gymPricingInfo();
    $otherFacilities = \App\Models\Rate::otherFacilitiesPricingInfo();
    $gymWaUrl = "https://wa.me/6282343797911?text=" . urlencode("Halo Admin Lino Sport Center, saya ingin tanya info tiket harian dan pendaftaran member Gym. Terima kasih!");
    $biliarWaUrl = "https://wa.me/6282343797911?text=" . urlencode("Halo Admin Lino Sport Center, saya ingin tanya ketersediaan meja & reservasi Biliar. Terima kasih!");
    $pgWaUrl = "https://wa.me/6282343797911?text=" . urlencode("Halo Admin Lino Sport Center, saya ingin tanya info tiket Playground & Boom Car. Terima kasih!");
    $tmWaUrl = "https://wa.me/6282343797911?text=" . urlencode("Halo Admin Lino Sport Center, saya ingin tanya ketersediaan & reservasi Tenis Meja. Terima kasih!");
  @endphp
  <!-- Card Informasi: Gym & Fitness Center Lino Sport -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-custom h-100 position-relative border-2" style="border-color:#10b981;">
      <div class="position-relative overflow-hidden" style="height: 180px;">
        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80" alt="Gym &amp; Fitness Lino Sport" class="w-100 h-100" style="object-fit: cover;">
        <span class="position-absolute top-0 start-0 m-2 badge bg-success text-uppercase px-3 py-2 rounded-pill small shadow-sm">
          <i class="bi bi-lightning-charge-fill me-1"></i> Gym &amp; Fitness
        </span>
        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-80 small">
          Offline di Lokasi
        </span>
      </div>

      <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="fw-bold mb-0 text-dark">Gym &amp; Fitness Center</h5>
          <span class="badge bg-light text-success border small fw-bold">Daily &amp; Member</span>
        </div>
        <p class="text-muted small mb-2" style="font-size:0.75rem;">Area gym kebugaran modern &amp; pemanasan atlet.</p>

        <div class="p-2.5 bg-light rounded-3 border mb-2 small">
          <div class="fw-bold text-dark mb-1 d-flex justify-content-between" style="font-size:0.75rem;">
            <span><i class="bi bi-ticket-perforated text-success me-1"></i>Tiket Harian (Daily Pass):</span>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Senin – Jumat:</span>
            <strong class="text-dark">{{ $gymPricing['weekday']['price_formatted'] }}/hari</strong>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Sabtu – Minggu:</span>
            <strong class="text-success">{{ $gymPricing['weekend']['price_formatted'] }}/hari</strong>
          </div>
          <hr class="my-1.5 opacity-15">
          <div class="fw-bold text-dark mb-1 d-flex justify-content-between" style="font-size:0.75rem;">
            <span><i class="bi bi-person-badge text-success me-1"></i>Paket Membership:</span>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Member Bulanan:</span>
            <strong class="text-success">{{ $gymPricing['member']['price_formatted'] }}/bln</strong>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Biaya Administrasi:</span>
            <strong class="text-dark">{{ $gymPricing['admin']['price_formatted'] }}</strong>
          </div>
        </div>

        <a href="{{ $gymWaUrl }}" target="_blank" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto d-flex align-items-center justify-content-center gap-1.5 shadow-sm">
          <i class="bi bi-whatsapp"></i>
          <span>Daftar / Info Member WA</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Card Informasi: Biliar -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-custom h-100 position-relative border-2" style="border-color:#10b981;">
      <div class="position-relative overflow-hidden" style="height: 180px;">
        <img src="https://images.unsplash.com/photo-1611676279444-5577698aa13c?auto=format&fit=crop&w=800&q=80" alt="Biliar Lino Sport" class="w-100 h-100" style="object-fit: cover;">
        <span class="position-absolute top-0 start-0 m-2 badge bg-success text-uppercase px-3 py-2 rounded-pill small shadow-sm">
          <i class="bi bi-8-circle-fill me-1"></i> Biliar
        </span>
        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-80 small">
          Offline di Lokasi
        </span>
      </div>

      <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="fw-bold mb-0 text-dark">Biliar (Billiard Arena)</h5>
          <span class="badge bg-light text-success border small fw-bold">Per Jam</span>
        </div>
        <p class="text-muted small mb-2" style="font-size:0.75rem;">Meja biliar standar turnamen, stik &amp; bola lengkap, ruangan ber-AC.</p>

        <div class="p-2.5 bg-light rounded-3 border mb-2 small">
          <div class="fw-bold text-dark mb-1" style="font-size:0.75rem;">
            <i class="bi bi-clock text-success me-1"></i>Tarif Sewa Biliar:
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Sewa Meja Biliar:</span>
            <strong class="text-success">{{ $otherFacilities['biliar']['price_formatted'] }}/jam</strong>
          </div>
          <div class="text-muted mt-1" style="font-size:0.72rem;">
            Sudah termasuk peminjaman stik, bola &amp; chalk.
          </div>
        </div>

        <a href="{{ $biliarWaUrl }}" target="_blank" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto d-flex align-items-center justify-content-center gap-1.5 shadow-sm">
          <i class="bi bi-whatsapp"></i>
          <span>Info / Reservasi Biliar WA</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Card Informasi: Playground & Boom Car -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-custom h-100 position-relative border-2" style="border-color:#10b981;">
      <div class="position-relative overflow-hidden" style="height: 180px;">
        <img src="https://images.unsplash.com/photo-1566454544259-f4b94c3d758c?auto=format&fit=crop&w=800&q=80" alt="Playground Lino Sport" class="w-100 h-100" style="object-fit: cover;">
        <span class="position-absolute top-0 start-0 m-2 badge bg-success text-uppercase px-3 py-2 rounded-pill small shadow-sm">
          <i class="bi bi-emoji-smile-fill me-1"></i> Playground
        </span>
        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-80 small">
          Offline di Lokasi
        </span>
      </div>

      <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="fw-bold mb-0 text-dark">Playground &amp; Boom Car</h5>
          <span class="badge bg-light text-success border small fw-bold">Kids Zone</span>
        </div>
        <p class="text-muted small mb-2" style="font-size:0.75rem;">Area bermain anak aman &amp; seru selagi orang tua berolahraga.</p>

        <div class="p-2.5 bg-light rounded-3 border mb-2 small">
          <div class="fw-bold text-dark mb-1" style="font-size:0.75rem;">
            <i class="bi bi-stars text-success me-1"></i>Tiket Playground &amp; Wahana:
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Playground Sepuasnya:</span>
            <strong class="text-success">{{ $otherFacilities['playground']['unlimited']['price_formatted'] }}</strong>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Boom Car:</span>
            <strong class="text-dark">{{ $otherFacilities['playground']['boom_car']['price_formatted'] }}/3 putaran</strong>
          </div>
        </div>

        <a href="{{ $pgWaUrl }}" target="_blank" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto d-flex align-items-center justify-content-center gap-1.5 shadow-sm">
          <i class="bi bi-whatsapp"></i>
          <span>Info Playground WA</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Card Informasi: Tenis Meja -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-custom h-100 position-relative border-2" style="border-color:#10b981;">
      <div class="position-relative overflow-hidden" style="height: 180px;">
        <img src="https://images.unsplash.com/photo-1534158914592-062992fbe900?auto=format&fit=crop&w=800&q=80" alt="Tenis Meja Lino Sport" class="w-100 h-100" style="object-fit: cover;">
        <span class="position-absolute top-0 start-0 m-2 badge bg-success text-uppercase px-3 py-2 rounded-pill small shadow-sm">
          <i class="bi bi-circle-fill me-1"></i> Tenis Meja
        </span>
        <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-80 small">
          Offline di Lokasi
        </span>
      </div>

      <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <h5 class="fw-bold mb-0 text-dark">Tenis Meja (Pingpong)</h5>
          <span class="badge bg-light text-success border small fw-bold">Per Jam</span>
        </div>
        <p class="text-muted small mb-2" style="font-size:0.75rem;">Area tenis meja indoor terang, nyaman &amp; sudah termasuk bet.</p>

        <div class="p-2.5 bg-light rounded-3 border mb-2 small">
          <div class="fw-bold text-dark mb-1" style="font-size:0.75rem;">
            <i class="bi bi-clock text-success me-1"></i>Tarif Sewa Tenis Meja:
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.76rem;">
            <span>Sewa Tenis Meja:</span>
            <strong class="text-success">{{ $otherFacilities['tenis_meja']['price_formatted'] }}/jam</strong>
          </div>
          <div class="text-muted mt-1" style="font-size:0.72rem;">
            Sudah termasuk peminjaman bet &amp; bola pingpong.
          </div>
        </div>

        <a href="{{ $tmWaUrl }}" target="_blank" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto d-flex align-items-center justify-content-center gap-1.5 shadow-sm">
          <i class="bi bi-whatsapp"></i>
          <span>Info / Reservasi Tenis Meja WA</span>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- STEP 2: MUNCUL SETELAH LAPANGAN DIPILIH -->
<div id="step2ScheduleSection" class="{{ $selectedCourtId ? '' : 'd-none' }}" data-aos="fade-up">
  <div class="card bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
      </div>
      <div>
        <small class="text-success fw-bold text-uppercase d-block" style="font-size:0.75rem;">Langkah 2: Lapangan Terpilih</small>
        <h5 class="fw-bold text-dark mb-0" id="activeCourtTitle">-</h5>
      </div>
    </div>
    <a href="#step1Courts" class="btn btn-outline-success btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-repeat me-1"></i> Ganti Lapangan Lain
    </a>
  </div>

  <div class="row g-4 align-items-start">
    <!-- Visualisasi Timeline Jam Interaktif Modern -->
    <div class="col-lg-7">
      <div class="card card-custom border-0 shadow-sm">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-clock-history text-success fs-5"></i>
              <span>Pilih Jam Bermain</span>
            </h6>
            <small class="text-muted">Klik slot jam hijau (bisa pilih beberapa jam berurutan)</small>
          </div>
          <div style="min-width: 170px;">
            <input type="date" id="scheduleDateInput" class="form-control form-control-sm border py-2" value="{{ $selectedDate }}" min="{{ date('Y-m-d') }}">
          </div>
        </div>

        <div class="card-body p-4">
          <!-- Legend Status Modern Clean -->
          <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-4 mb-3 small align-items-center justify-content-center justify-content-md-between border">
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-pill px-2 py-0.5 text-white bg-success fw-bold" style="font-size:0.7rem;">READY</span>
              <span class="text-dark">Tersedia</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-pill px-2 py-0.5 text-white bg-primary fw-bold" style="font-size:0.7rem;">DIPILIH</span>
              <span class="text-dark">Pilihan Anda</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-pill px-2 py-0.5 text-white bg-danger fw-bold" style="font-size:0.7rem;">LOCKED</span>
              <span class="text-muted">Terbooking</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="rounded-pill px-2 py-0.5 text-muted bg-secondary bg-opacity-25 fw-bold" style="font-size:0.7rem;">LEWAT / &lt; 1 JAM</span>
              <span class="text-muted">Tidak Tersedia</span>
            </div>
          </div>

          <!-- Banner Durasi Jam Terpilih Real-time -->
          <div id="selectionSummaryBanner" class="alert alert-primary py-2 px-3 rounded-3 mb-3 d-flex justify-content-between align-items-center d-none">
            <div class="small">
              <i class="bi bi-check-circle-fill me-1"></i> Jam Terpilih: <strong id="selectedTimeRangeText">-</strong>
            </div>
            <button type="button" id="btnResetSlot" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
          </div>

          <!-- Loading State -->
          <div id="slotsLoading" class="text-center py-5 d-none">
            <div class="spinner-border text-success" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <div class="small text-muted mt-2 fw-semibold">Memuat jadwal jam lapangan...</div>
          </div>

          <!-- Timeline Slots Grid Modern -->
          <div id="slotsContainer" class="slots-timeline-grid"></div>
        </div>
      </div>
    </div>

    <!-- Formulir Biodata Pemesan -->
    <div class="col-lg-5">
      <div class="card card-custom border-0 shadow-sm">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-success fs-5"></i>
            <span>Biodata & Pemesanan</span>
          </h6>
          <span class="badge bg-light text-muted border small">Tanpa Akun</span>
        </div>

        <div class="card-body p-4">
          <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
            @csrf

            <input type="hidden" name="court_id" id="formCourtId" value="{{ $selectedCourtId }}">
            <input type="hidden" name="date" id="formDate" value="{{ $selectedDate }}">

            <!-- Summary Box Lapangan Terpilih -->
            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 mb-4">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <small class="text-success text-uppercase fw-bold" style="font-size:0.75rem;">Lapangan Terpilih</small>
                  <div class="fw-bold fs-6 text-dark" id="selectedCourtLabel">-</div>
                </div>
                <span class="badge bg-success" id="selectedDateLabel">-</span>
              </div>
            </div>

            <!-- Section 1: Biodata Pemesan (Hanya Nama & WhatsApp) -->
            <div class="mb-3">
              <span class="badge bg-light text-success border small mb-2 fw-bold text-uppercase">1. Data Pemesan (Wajib)</span>
              
              <div class="mb-2">
                <label class="form-label small fw-bold text-muted mb-1">Nama Pemesan <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                  <input type="text" name="customer_name" class="form-control" required value="{{ old('customer_name') }}" placeholder="Contoh: Budi Santoso">
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label small fw-bold text-muted mb-1">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-whatsapp text-success"></i></span>
                  <input type="tel" name="customer_phone" class="form-control" required minlength="9" maxlength="20" value="{{ old('customer_phone') }}" placeholder="08123456789">
                </div>
              </div>
            </div>

            <hr class="my-3 border-secondary border-opacity-10">

            <!-- Section 2: Waktu & Pembayaran -->
            <div class="mb-3">
              <span class="badge bg-light text-success border small mb-2 fw-bold text-uppercase">2. Waktu & Pembayaran</span>
              
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label fw-bold small text-muted mb-1">Jam Mulai</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                    <input type="time" name="start_time" id="startTimeInput" class="form-control" required step="3600" min="08:00" max="22:00">
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label fw-bold small text-muted mb-1">Jam Selesai</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-clock-fill"></i></span>
                    <input type="time" name="end_time" id="endTimeInput" class="form-control" required step="3600" min="09:00" max="23:00">
                  </div>
                </div>
                <div class="col-12">
                  <small class="text-muted d-block mt-1" style="font-size: 0.76rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Klik slot jam di samping untuk mengisi jam otomatis.
                  </small>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold small text-muted mb-1">Metode Pembayaran (QRIS Statis)</label>
                <select name="payment_method" class="form-select py-2">
                  <option value="dp">QRIS Statis — DP 50% dari total tagihan</option>
                  <option value="lunas">QRIS Statis — Lunas 100% Pembayaran Penuh</option>
                </select>
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                  <i class="bi bi-qr-code-scan text-success me-1"></i> Setelah submit, Anda akan diarahkan ke halaman QRIS Statis untuk scan & upload bukti pembayaran agar divalidasi oleh Admin.
                </small>
              </div>
            </div>

            <button type="submit" class="btn btn-gradient-primary w-100 py-3 fw-bold fs-6 shadow">
              <i class="bi bi-qr-code me-1"></i> Lanjut Pembayaran QRIS & Upload Bukti Bayar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const courtCards = document.querySelectorAll('.court-card');
    const scheduleSection = document.getElementById('step2ScheduleSection');
    const activeCourtTitle = document.getElementById('activeCourtTitle');
    const dateInput = document.getElementById('scheduleDateInput');
    const slotsContainer = document.getElementById('slotsContainer');
    const slotsLoading = document.getElementById('slotsLoading');

    const formCourtId = document.getElementById('formCourtId');
    const formDate = document.getElementById('formDate');
    const startTimeInput = document.getElementById('startTimeInput');
    const endTimeInput = document.getElementById('endTimeInput');
    const selectionSummaryBanner = document.getElementById('selectionSummaryBanner');
    const selectedTimeRangeText = document.getElementById('selectedTimeRangeText');
    const btnResetSlot = document.getElementById('btnResetSlot');

    let currentCourtId = "{{ $selectedCourtId ?? '' }}";
    let currentCourtName = "";
    let selectedStartHour = null;
    let selectedEndHour = null;
    let activeSlotsData = [];

    // Reset pilihan jam
    btnResetSlot.addEventListener('click', function() {
        selectedStartHour = null;
        selectedEndHour = null;
        startTimeInput.value = '';
        endTimeInput.value = '';
        selectionSummaryBanner.classList.add('d-none');
        updateSlotVisuals();
    });

    // 1. MEKANISME: KLIK KARTU LAPANGAN DULU
    courtCards.forEach(card => {
        card.addEventListener('click', function () {
            currentCourtId = this.dataset.courtId;
            currentCourtName = this.dataset.courtName + " (" + this.dataset.courtSport + ")";

            // Reset pilihan jam saat ganti lapangan
            selectedStartHour = null;
            selectedEndHour = null;
            startTimeInput.value = '';
            endTimeInput.value = '';
            selectionSummaryBanner.classList.add('d-none');

            // Tandai visual kartu yang aktif
            courtCards.forEach(c => {
                c.classList.remove('selected-court');
                c.querySelector('.select-indicator').classList.add('d-none');
                c.querySelector('.btn-select-court').className = 'btn btn-outline-success w-100 fw-bold py-2 rounded-3 mt-auto btn-select-court';
                c.querySelector('.btn-select-court').innerHTML = '<i class="bi bi-calendar2-week me-1"></i> Pilih & Lihat Jadwal';
            });

            this.classList.add('selected-court');
            this.querySelector('.select-indicator').classList.remove('d-none');
            this.querySelector('.btn-select-court').className = 'btn btn-success w-100 fw-bold py-2 rounded-3 mt-auto btn-select-court';
            this.querySelector('.btn-select-court').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Lapangan Terpilih';

            // Update Form & Judul
            formCourtId.value = currentCourtId;
            activeCourtTitle.textContent = currentCourtName;
            const selectedCourtLabel = document.getElementById('selectedCourtLabel');
            const selectedDateLabel = document.getElementById('selectedDateLabel');
            if (selectedCourtLabel) selectedCourtLabel.textContent = currentCourtName;
            if (selectedDateLabel) selectedDateLabel.textContent = dateInput.value;

            // Buka Step 2 (Jadwal & Form)
            scheduleSection.classList.remove('d-none');
            scheduleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Ambil jadwal jam
            fetchSchedule();
        });
    });

    // 2. AMBIL JADWAL
    function fetchSchedule() {
        const date = dateInput.value;
        if (!currentCourtId || !date) return;

        formDate.value = date;
        const selectedDateLabel = document.getElementById('selectedDateLabel');
        if (selectedDateLabel) selectedDateLabel.textContent = date;
        slotsLoading.classList.remove('d-none');
        slotsContainer.innerHTML = '';

        fetch(`{{ route('api.court-schedule') }}?court_id=${currentCourtId}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                slotsLoading.classList.add('d-none');
                activeSlotsData = data.slots;
                renderSlots(data.slots);
            })
            .catch(err => {
                slotsLoading.classList.add('d-none');
                slotsContainer.innerHTML = '<div class="col-12 text-danger small p-3">Gagal memuat jadwal. Silakan refresh halaman.</div>';
            });
    }

    // 3. TAMPILKAN SLOTS TIMELINE MODERN (Gaya Chip Grid Elegan)
    function renderSlots(slots) {
        slotsContainer.innerHTML = '';

        slots.forEach((slot, index) => {
            const hStart = parseInt(slot.hour.split(':')[0], 10);
            const hEnd = parseInt(slot.next_hour.split(':')[0], 10);

            const card = document.createElement('div');
            card.className = `slot-timeline-card status-${slot.status}`;
            card.dataset.hour = slot.hour;
            card.dataset.nextHour = slot.next_hour;
            card.dataset.hStart = hStart;
            card.dataset.hEnd = hEnd;
            card.dataset.status = slot.status;

            let iconHtml = '<i class="bi bi-check-circle"></i>';
            let statusText = 'Ready';

            if (slot.status === 'booked') {
                iconHtml = '<i class="bi bi-lock-fill"></i>';
                statusText = 'Locked';
            } else if (slot.status === 'pending') {
                iconHtml = '<i class="bi bi-hourglass-split"></i>';
                statusText = 'Pending';
            } else if (slot.status === 'disabled') {
                iconHtml = '<i class="bi bi-slash-circle"></i>';
                statusText = slot.badge || 'Lewat';
            }

            const slotPriceFormatted = slot.price ? 'Rp' + Number(slot.price).toLocaleString('id-ID') : '';

            card.innerHTML = `
                <div class="slot-time">
                    <span class="slot-hour">${slot.hour}</span>
                    <span class="slot-arrow">&rarr;</span>
                    <span class="slot-hour-next">${slot.next_hour}</span>
                </div>
                ${slotPriceFormatted ? `<div class="text-muted fw-semibold mb-1" style="font-size:0.7rem;">${slotPriceFormatted}</div>` : ''}
                <div class="slot-status-badge">
                    ${iconHtml} <span>${statusText}</span>
                </div>
            `;

            if (slot.status === 'available') {
                card.addEventListener('click', function () {
                    handleSlotSelection(hStart, hEnd);
                });
            }

            slotsContainer.appendChild(card);
        });

        updateSlotVisuals();
    }

    // Logika pemilihan jam (Bisa multi-jam berurutan yang ready)
    function handleSlotSelection(hStart, hEnd) {
        if (selectedStartHour === null) {
            selectedStartHour = hStart;
            selectedEndHour = hEnd;
        } else if (selectedStartHour !== null && selectedEndHour !== null && hStart >= selectedStartHour) {
            let allAvailable = true;
            for (let h = selectedStartHour; h < hEnd; h++) {
                const checkSlot = activeSlotsData.find(s => parseInt(s.hour.split(':')[0], 10) === h);
                if (!checkSlot || checkSlot.status !== 'available') {
                    allAvailable = false;
                    break;
                }
            }

            if (allAvailable) {
                selectedEndHour = hEnd;
            } else {
                selectedStartHour = hStart;
                selectedEndHour = hEnd;
            }
        } else {
            selectedStartHour = hStart;
            selectedEndHour = hEnd;
        }

        const formattedStart = String(selectedStartHour).padStart(2, '0') + ':00';
        const formattedEnd = String(selectedEndHour).padStart(2, '0') + ':00';

        startTimeInput.value = formattedStart;
        endTimeInput.value = formattedEnd;

        const duration = selectedEndHour - selectedStartHour;
        let estTotal = 0;
        for (let h = selectedStartHour; h < selectedEndHour; h++) {
            const sObj = activeSlotsData.find(s => parseInt(s.hour.split(':')[0], 10) === h);
            if (sObj && sObj.price) estTotal += parseInt(sObj.price, 10);
        }
        const priceInfo = estTotal > 0 ? ` — Total Rp${estTotal.toLocaleString('id-ID')}` : '';
        selectedTimeRangeText.textContent = `${formattedStart} - ${formattedEnd} (${duration} Jam${priceInfo})`;
        selectionSummaryBanner.classList.remove('d-none');

        updateSlotVisuals();
    }

    function updateSlotVisuals() {
        const cards = document.querySelectorAll('.slot-timeline-card');
        cards.forEach(card => {
            const hStart = parseInt(card.dataset.hStart, 10);
            card.classList.remove('is-selected');

            if (selectedStartHour !== null && selectedEndHour !== null) {
                if (hStart >= selectedStartHour && hStart < selectedEndHour && card.dataset.status === 'available') {
                    card.classList.add('is-selected');
                }
            }
        });
    }

    dateInput.addEventListener('change', function() {
        selectedStartHour = null;
        selectedEndHour = null;
        startTimeInput.value = '';
        endTimeInput.value = '';
        selectionSummaryBanner.classList.add('d-none');
        fetchSchedule();
    });

    // Jika sudah ada lapangan terpilih dari awal
    if (currentCourtId) {
        const selectedCard = document.querySelector(`.court-card[data-court-id="${currentCourtId}"]`);
        if (selectedCard) {
            selectedCard.click();
        }
    }
});
</script>

<style>
.slots-timeline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(134px, 1fr));
    gap: 14px;
}

.slot-timeline-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px 10px;
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border: 1.5px solid #cbd5e1;
    border-bottom: 4px solid #94a3b8;
    box-shadow: 0 6px 16px -6px rgba(15, 23, 42, 0.08);
    transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    user-select: none;
    position: relative;
}

.slot-time {
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 800;
    font-size: 0.96rem;
    color: #0f172a;
    margin-bottom: 4px;
}

.slot-time .slot-arrow {
    color: #64748b;
    font-size: 0.8rem;
}

.slot-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.slot-timeline-card.status-available {
    border-color: #6ee7b7;
    border-bottom: 4px solid #059669;
    background: linear-gradient(145deg, #ffffff 0%, #ecfdf5 100%);
    box-shadow: 0 8px 18px -6px rgba(16, 185, 129, 0.18);
    cursor: pointer;
}
.slot-timeline-card.status-available .slot-time {
    color: #064e3b;
}
.slot-timeline-card.status-available .slot-status-badge {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
}
.slot-timeline-card.status-available:hover {
    transform: translateY(-4px);
    border-color: #10b981;
    box-shadow: 0 14px 26px -6px rgba(16, 185, 129, 0.30);
    background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
}

.slot-timeline-card.is-selected {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
    border-color: #1d4ed8 !important;
    border-bottom: 4px solid #1e3a8a !important;
    box-shadow: 0 12px 26px -4px rgba(37, 99, 235, 0.42) !important;
    transform: translateY(-3px) scale(1.03);
    z-index: 2;
}
.slot-timeline-card.is-selected .slot-time {
    color: #ffffff !important;
}
.slot-timeline-card.is-selected .slot-time .slot-arrow {
    color: rgba(255, 255, 255, 0.75) !important;
}
.slot-timeline-card.is-selected .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
}
.slot-timeline-card.is-selected .slot-status-badge {
    background: #ffffff !important;
    color: #1d4ed8 !important;
}

.slot-timeline-card.status-booked {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border-color: #fda4af;
    border-bottom: 4px solid #e11d48;
    cursor: not-allowed;
    opacity: 0.92;
}
.slot-timeline-card.status-booked .slot-time {
    color: #9f1239;
}
.slot-timeline-card.status-booked .slot-status-badge {
    background: #e11d48;
    color: #ffffff;
}

.slot-timeline-card.status-pending {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-color: #fcd34d;
    border-bottom: 4px solid #d97706;
    cursor: not-allowed;
    opacity: 0.94;
}
.slot-timeline-card.status-pending .slot-time {
    color: #92400e;
}
.slot-timeline-card.status-pending .slot-status-badge {
    background: #f59e0b;
    color: #ffffff;
}

.slot-timeline-card.status-disabled {
    background: repeating-linear-gradient(-45deg, #f8fafc, #f8fafc 6px, #f1f5f9 6px, #f1f5f9 12px);
    border-color: #e2e8f0;
    border-bottom: 3px solid #cbd5e1;
    cursor: not-allowed;
    opacity: 0.6;
}
.slot-timeline-card.status-disabled .slot-time {
    color: #94a3b8;
}
.slot-timeline-card.status-disabled .slot-status-badge {
    background: #cbd5e1;
    color: #475569;
}

.selected-court {
    border: 2.5px solid #10b981 !important;
    box-shadow: 0 12px 28px rgba(16, 185, 129, 0.28) !important;
    transform: translateY(-4px);
}
</style>
@endsection
