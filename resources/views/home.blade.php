@extends('layouts.app')
@section('title', 'Beranda')

@section('content')
<!-- Hero Carousel Dinamis Dari Database / Admin -->
<div class="position-relative mb-5" data-aos="fade-up">
  <div id="heroCarousel" class="carousel slide carousel-fade rounded-4 overflow-hidden shadow-lg" data-bs-ride="carousel" data-bs-interval="4500">
    <div class="carousel-indicators mb-4">
      @foreach($sliders as $idx => $s)
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $idx }}" class="{{ $loop->first ? 'active' : '' }} rounded-pill" style="width:30px;height:6px;"></button>
      @endforeach
    </div>

    <div class="carousel-inner">
      @foreach($sliders as $slider)
      @php
        $imgSrc = $slider->image_url ?: 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=1600&auto=format&fit=crop&q=80';
      @endphp
      <div class="carousel-item {{ $loop->first ? 'active' : '' }} position-relative" style="height: 480px; background-color: #064e3b;">
        <img src="{{ $imgSrc }}" class="d-block w-100 h-100" style="object-fit: cover; filter: brightness(0.46);" alt="{{ $slider->title }}">
        <div class="carousel-caption text-start pb-5" style="left: 6%; right: 6%; bottom: 10%;">
          @if($slider->badge)
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-20 backdrop-blur mb-3 text-warning fw-bold small text-uppercase" style="backdrop-filter: blur(8px);">
            <i class="bi bi-star-fill"></i> {{ $slider->badge }}
          </div>
          @endif
          <h1 class="display-4 fw-extrabold text-white mb-2" style="font-weight: 800; letter-spacing: -1px;">{{ $slider->title }}</h1>
          @if($slider->subtitle)
          <p class="fs-5 text-light col-lg-8 opacity-90 mb-4">{{ $slider->subtitle }}</p>
          @endif
          <div class="d-flex flex-wrap gap-3">
            <a href="{{ $slider->button_url ?: route('booking.create') }}" class="btn btn-gradient-warning btn-lg px-4 py-3">
              <i class="bi bi-calendar-check-fill me-2"></i>{{ $slider->button_text ?: 'Booking Sekarang' }}
            </a>
            <a href="#daftar-lapangan" class="btn btn-outline-light btn-lg px-4 py-3 rounded-3">
              Lihat Tarif & Info
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="p-2 rounded-circle bg-dark bg-opacity-50 d-inline-flex align-items-center justify-content-center">
        <i class="bi bi-chevron-left fs-4"></i>
      </span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="p-2 rounded-circle bg-dark bg-opacity-50 d-inline-flex align-items-center justify-content-center">
        <i class="bi bi-chevron-right fs-4"></i>
      </span>
    </button>
  </div>
</div>

<!-- Highlight Banner: Jam Buka & Aturan -->
<div class="card card-custom p-4 mb-5 border-0 bg-white" data-aos="fade-up" data-aos-delay="100">
  <div class="row align-items-center g-3">
    <div class="col-lg-8 d-flex align-items-center gap-3">
      <div class="p-3 rounded-4 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; flex-shrink:0;">
        <span class="pulse-indicator"></span>
      </div>
      <div>
        <h6 class="fw-bold mb-1 text-dark">Buka Setiap Hari: 08.00 – 23.00 WIB</h6>
        <div class="text-muted small">
          <span class="badge bg-light text-dark border me-1"><i class="bi bi-clock me-1 text-primary"></i>Min. 1 Jam Sebelum Main</span>
          <span class="badge bg-light text-dark border me-1"><i class="bi bi-shield-check me-1 text-success"></i>Bebas Durasi Jam (Selama Ready)</span>
          <span class="badge bg-light text-dark border"><i class="bi bi-qr-code me-1 text-success"></i>Pembayaran QRIS Statis & Approval Admin</span>
        </div>
      </div>
    </div>
    <div class="col-lg-4 text-lg-end">
      <a href="{{ route('booking.create') }}" class="btn btn-gradient-primary px-4 py-2">
        <i class="bi bi-calendar-plus me-1"></i> Cek Jadwal Lapangan
      </a>
    </div>
  </div>
</div>

<!-- Daftar Lapangan (Dengan Foto Modern & Rincian Tarif Lengkap) -->
<div id="daftar-lapangan" class="mb-5">
  <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-up">
    <div>
      <span class="text-success fw-bold text-uppercase small letter-spacing-1">Pilihan Arena</span>
      <h3 class="fw-bold mb-0">Daftar Lapangan & Tarif Sewa</h3>
    </div>
    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small d-none d-md-inline-block">
      5 Lapangan Olahraga &amp; 1 Area Gym Modern
    </span>
  </div>

  <div class="row g-4">
    @php
      $defaultImages = [
        'padel' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80',
        'bulutangkis' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=800&q=80',
        'pickleball' => 'https://images.unsplash.com/photo-1599586120429-48281b6f0ece?auto=format&fit=crop&w=800&q=80'
      ];
    @endphp

    @foreach($allCourts as $court)
      @php
        $courtImg = $court->image_url ?: ($defaultImages[$court->sport] ?? $defaultImages['padel']);
        $sportRates = \App\Models\Rate::getSportRatesMap($court->sport);
        $equipInfos = \App\Models\Rate::sportEquipmentInfo($court->sport);
        $sportLabel = $court->sport === 'bulutangkis' ? 'Badminton' : ucfirst($court->sport);
      @endphp
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
        <div class="card card-custom h-100 d-flex flex-column">
          <div class="position-relative overflow-hidden" style="height: 215px;">
            <img src="{{ $courtImg }}" alt="{{ $court->name }}" class="w-100 h-100 transition-transform" style="object-fit: cover; transition: transform 0.5s ease;">
            
            <div class="position-absolute top-0 start-0 m-3">
              <span class="badge bg-dark bg-opacity-80 backdrop-blur px-3 py-2 rounded-pill fw-bold text-uppercase shadow-sm">
                {{ $sportLabel }}
              </span>
            </div>

            <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-gradient-to-t" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
              <span class="text-white fw-bold small"><i class="bi bi-shield-check text-success me-1"></i> Standar Resmi</span>
            </div>
          </div>

          <div class="card-body p-4 d-flex flex-column flex-grow-1">
            <h5 class="fw-bold mb-3 text-dark">{{ $court->name }}</h5>
            
            <!-- Tarif Senin - Jumat -->
            <div class="p-3 rounded-3 bg-light border mb-2">
              <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar-week text-success me-1"></i>Senin – Jumat</span>
                <span class="badge bg-white text-muted border" style="font-size:0.65rem;">Weekday</span>
              </div>
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="text-muted">Pukul 08.00 – 16.00:</span>
                <span class="fw-bold text-dark">Rp{{ number_format($sportRates['weekday_day'], 0, ',', '.') }}<small class="text-muted fw-normal">/jam</small></span>
              </div>
              <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Pukul 16.00 – 23.00:</span>
                <span class="fw-bold text-success">Rp{{ number_format($sportRates['weekday_night'], 0, ',', '.') }}<small class="text-muted fw-normal">/jam</small></span>
              </div>
            </div>

            <!-- Tarif Sabtu - Minggu -->
            <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
              <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar-heart text-success me-1"></i>Sabtu – Minggu</span>
                <span class="badge bg-success" style="font-size:0.65rem;">Weekend</span>
              </div>
              <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="text-muted">Pukul 08.00 – 16.00:</span>
                <span class="fw-bold text-dark">Rp{{ number_format($sportRates['weekend_day'], 0, ',', '.') }}<small class="text-muted fw-normal">/jam</small></span>
              </div>
              <div class="d-flex justify-content-between align-items-center small">
                <span class="text-muted">Pukul 16.00 – 23.00:</span>
                <span class="fw-bold text-success">Rp{{ number_format($sportRates['weekend_night'], 0, ',', '.') }}<small class="text-muted fw-normal">/jam</small></span>
              </div>
            </div>

            <!-- Info Sewa Alat di Lokasi -->
            @if(!empty($equipInfos))
              <div class="px-3 py-2 rounded-3 bg-white border mb-3 small">
                <div class="text-muted fw-bold text-uppercase mb-1" style="font-size:0.65rem;letter-spacing:0.4px;">
                  <i class="bi bi-shop text-warning me-1"></i>Info Sewa Alat (Offline di Lokasi):
                </div>
                @foreach($equipInfos as $eqInfo)
                  <div class="d-flex justify-content-between align-items-center text-muted" style="font-size:0.76rem;">
                    <span>{{ $eqInfo['label'] }}</span>
                    <strong class="text-dark">{{ $eqInfo['price'] }}</strong>
                  </div>
                @endforeach
              </div>
            @endif

            <div class="mt-auto">
              <a href="{{ route('booking.create') }}?court_id={{ $court->id }}" class="btn btn-outline-success w-100 fw-bold py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                <span>Pilih &amp; Booking</span>
                <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    @endforeach

    @php
      $gymPricing = \App\Models\Rate::gymPricingInfo();
    @endphp
    <!-- Card Khusus: Gym & Fitness Center Lino Sport -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="480">
      <div class="card card-custom h-100 d-flex flex-column border-2" style="border-color: #10b981;">
        <div class="position-relative overflow-hidden" style="height: 215px;">
          <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80" alt="Gym &amp; Fitness Lino Sport" class="w-100 h-100 transition-transform" style="object-fit: cover; transition: transform 0.5s ease;">
          
          <div class="position-absolute top-0 start-0 m-3">
            <span class="badge bg-success px-3 py-2 rounded-pill fw-bold text-uppercase shadow-sm">
              <i class="bi bi-lightning-charge-fill me-1"></i> Gym &amp; Fitness
            </span>
          </div>

          <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-gradient-to-t" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
            <span class="text-white fw-bold small"><i class="bi bi-check-circle-fill text-warning me-1"></i> Alat Beban &amp; Kardio Lengkap</span>
          </div>
        </div>

        <div class="card-body p-4 d-flex flex-column flex-grow-1">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="fw-bold mb-0 text-dark">Gym &amp; Fitness Center</h5>
            <span class="badge bg-light text-success border fw-bold" style="font-size:0.7rem;">Tiket &amp; Member</span>
          </div>
          <p class="text-muted small mb-3">Area gym kebugaran modern &amp; pemanasan atlet sebelum bertanding.</p>
          
          <!-- Tarif Tiket Harian -->
          <div class="p-3 rounded-3 bg-light border mb-2">
            <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between">
              <span><i class="bi bi-ticket-perforated-fill text-success me-1"></i>Tiket Harian (Daily Pass)</span>
              <span class="badge bg-white text-muted border" style="font-size:0.65rem;">Per Hari</span>
            </div>
            <div class="d-flex justify-content-between align-items-center small mb-1">
              <span class="text-muted">Senin – Jumat:</span>
              <strong class="text-dark">{{ $gymPricing['weekday']['price_formatted'] }}<small class="text-muted fw-normal">/hari</small></strong>
            </div>
            <div class="d-flex justify-content-between align-items-center small">
              <span class="text-muted">Sabtu – Minggu:</span>
              <strong class="text-success">{{ $gymPricing['weekend']['price_formatted'] }}<small class="text-muted fw-normal">/hari</small></strong>
            </div>
          </div>

          <!-- Paket Member & Registrasi -->
          <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
            <div class="fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between">
              <span><i class="bi bi-person-badge-fill text-success me-1"></i>Paket Membership</span>
              <span class="badge bg-success" style="font-size:0.65rem;">Bulanan</span>
            </div>
            <div class="d-flex justify-content-between align-items-center small mb-1">
              <span class="text-muted">Member Bulanan:</span>
              <strong class="text-success">{{ $gymPricing['member']['price_formatted'] }}<small class="text-muted fw-normal">/bln</small></strong>
            </div>
            <div class="d-flex justify-content-between align-items-center small">
              <span class="text-muted">Biaya Administrasi:</span>
              <strong class="text-dark">{{ $gymPricing['admin']['price_formatted'] }}<small class="text-muted fw-normal"> (sekali daftar)</small></strong>
            </div>
          </div>

          <div class="mt-auto">
            @php
              $gymWaText = urlencode("Halo Admin Lino Sport Center, saya ingin tanya informasi tiket harian & pendaftaran member Gym. Terima kasih!");
            @endphp
            <a href="https://wa.me/6282343797911?text={{ $gymWaText }}" target="_blank" class="btn btn-success w-100 fw-bold py-2 rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm">
              <i class="bi bi-whatsapp"></i>
              <span>Daftar / Info Gym via WA</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Fasilitas dalam Bentuk List Bersih & Elegan -->
<div class="card card-custom p-4 p-md-5 mb-5 border-0 bg-white" data-aos="fade-up">
  <div class="row align-items-center">
    <div class="col-lg-5 mb-4 mb-lg-0">
      <span class="text-success fw-bold text-uppercase small letter-spacing-1">Kenyamanan Anda</span>
      <h3 class="fw-bold mb-3">Fasilitas Lengkap</h3>
      <p class="text-muted mb-4">Kami menyediakan fasilitas komprehensif untuk memastikan pengalaman berolahraga Anda nyaman dari awal hingga selesai bermain.</p>
      
      <div class="p-3 bg-light rounded-4 d-inline-flex align-items-center gap-3">
        <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="small">
          <div class="fw-bold text-dark">Jam Operasional Fasilitas</div>
          <div class="text-muted">Buka mengikuti jadwal lapangan (08.00 - 23.00)</div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="row g-2">
        <div class="col-sm-6">
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">WC (Toilet Pria & Wanita)</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Musholla Bersih & Nyaman</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Ruang Ganti & Shower</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Café & Minuman Dingin</span>
            </li>
          </ul>
        </div>
        <div class="col-sm-6">
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Playground Anak</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Gym & Area Warm-Up</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Parkir Gratis Luas (Mobil & Motor)</span>
            </li>
            <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom">
              <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle me-3"><i class="bi bi-check2"></i></span>
              <span class="fw-semibold">Area Istirahat & Tribun Penonton</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Lokasi & Peta Petunjuk Arah Google Maps -->
<div class="card card-custom p-4 p-md-5 mb-5 border-0 bg-white" data-aos="fade-up" id="lokasi-arena">
  <div class="row g-4 align-items-center">
    <div class="col-lg-5">
      <span class="text-success fw-bold text-uppercase small letter-spacing-1">Akses &amp; Transportasi</span>
      <h3 class="fw-bold mb-3">Lokasi Arena &amp; Petunjuk Arah</h3>
      <p class="text-muted mb-4">
        Lino Sport Center berlokasi strategis dan mudah dijangkau, dilengkapi dengan area parkir kendaraan yang luas, nyaman, dan gratis untuk seluruh pengunjung.
      </p>

      <div class="d-flex flex-column gap-3 mb-4">
        <div class="d-flex align-items-start gap-3">
          <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
            <i class="bi bi-geo-alt-fill fs-5"></i>
          </div>
          <div>
            <div class="fw-bold text-dark small">Titik Presisi Lokasi</div>
            <div class="text-muted small">Lino Sport Center (Koordinat: -5.678616, 119.751499)</div>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3">
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
            <i class="bi bi-clock-history fs-5"></i>
          </div>
          <div>
            <div class="fw-bold text-dark small">Jam Buka Arena</div>
            <div class="text-muted small">Buka Setiap Hari: Pukul 08.00 – 23.00 WITA</div>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3">
          <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
            <i class="bi bi-p-square-fill fs-5"></i>
          </div>
          <div>
            <div class="fw-bold text-dark small">Fasilitas Parkir</div>
            <div class="text-muted small">Area parkir motor &amp; mobil luas, aman, dan bebas biaya</div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="https://maps.app.goo.gl/EPSFchkw15V3V3hRA" target="_blank" class="btn btn-success fw-bold px-4 py-2.5 rounded-3 d-inline-flex align-items-center gap-2 shadow-sm">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Buka di Google Maps</span>
          <i class="bi bi-box-arrow-up-right small"></i>
        </a>
        <a href="https://www.google.com/maps/dir/?api=1&destination=-5.678616,119.751499" target="_blank" class="btn btn-outline-secondary fw-semibold px-3 py-2.5 rounded-3 d-inline-flex align-items-center gap-1.5">
          <i class="bi bi-sign-turn-right-fill text-success"></i>
          <span>Petunjuk Rute</span>
        </a>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="rounded-4 overflow-hidden shadow border border-2 border-light position-relative" style="height: 380px;">
        <iframe
          src="https://maps.google.com/maps?q=-5.678616,119.751499&hl=id&z=17&output=embed"
          width="100%"
          height="100%"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Peta Lokasi Lino Sport Center">
        </iframe>
      </div>
    </div>
  </div>
</div>
@endsection
