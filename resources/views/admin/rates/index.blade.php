@extends('layouts.admin')
@section('title', 'Tarif Sewa Lapangan')

@section('content')
<div class="lx-page-header">
  <div>
    <h2 class="lx-page-title">Tarif Sewa Lapangan</h2>
    <p class="lx-page-sub">Kelola tarif per jam berdasarkan hari (Senin–Jumat vs Sabtu–Minggu) dan sesi jam (Pukul 08.00–16.00 vs 16.00–23.00).</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card overflow-hidden">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-tag text-success"></i> Daftar Tarif per Jam</h6>
        <span class="text-muted small">{{ $rates->count() }} tarif terdaftar</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4">Cabang Olahraga</th>
              <th>Kategori Hari & Sesi Waktu</th>
              <th style="width:240px">Harga / Jam (Rp)</th>
              <th class="text-end pe-4" style="width:150px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rates as $r)
              @php
                $sportLabel = $r->sport === 'bulutangkis' ? 'Badminton' : ($r->sport === 'gym' ? 'Gym & Fitness' : ucfirst($r->sport));
              @endphp
              <tr>
                <td class="ps-4">
                  <span class="fw-semibold text-dark">{{ $sportLabel }}</span>
                  @if($r->sport === 'gym')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1" style="font-size:0.65rem;">Fasilitas</span>
                  @endif
                </td>
                <td>
                  @if($r->sport === 'gym')
                    <span class="lx-pill lx-pill-success">{{ $r->session_label }}</span>
                  @elseif(str_starts_with($r->day_type, 'weekend'))
                    <span class="lx-pill lx-pill-warning">{{ $r->session_label }}</span>
                  @else
                    <span class="lx-pill lx-pill-info">{{ $r->session_label }}</span>
                  @endif
                </td>
                <td>
                  <form action="{{ route('admin.rates.update', $r) }}" method="POST" id="rateForm{{ $r->id }}" class="d-flex gap-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="sport" value="{{ $r->sport }}">
                    <input type="hidden" name="day_type" value="{{ $r->day_type }}">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">Rp</span>
                      <input type="number" name="price" class="form-control fw-bold" value="{{ $r->price }}" min="1" required>
                    </div>
                  </form>
                </td>
                <td class="text-end pe-4">
                  <div class="d-inline-flex gap-1">
                    <button type="submit" form="rateForm{{ $r->id }}" class="btn btn-primary btn-sm">Simpan</button>
                    <form action="{{ route('admin.rates.destroy', $r) }}" method="POST" class="d-inline"
                          data-confirm="Hapus tarif {{ $sportLabel }} ({{ $r->session_label }})?"
                          data-confirm-title="Hapus Tarif" data-confirm-variant="danger">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="lx-empty">Belum ada tarif terdaftar.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h6 class="lx-card-title"><i class="bi bi-plus-circle text-success"></i> Tambah / Update Tarif</h6>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.rates.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Cabang Olahraga / Fasilitas</label>
            <select name="sport" class="form-select" required>
              <option value="padel">Padel</option>
              <option value="bulutangkis">Badminton (Bulutangkis)</option>
              <option value="pickleball">Pickleball</option>
              <option value="gym">Gym &amp; Fitness Center</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori Hari &amp; Sesi Waktu</label>
            <select name="day_type" class="form-select" required>
              <optgroup label="Sewa Lapangan (Padel, Badminton, Pickleball)">
                <option value="weekday_day">Senin – Jumat (Pukul 08.00 – 16.00)</option>
                <option value="weekday_night">Senin – Jumat (Pukul 16.00 – 23.00)</option>
                <option value="weekend_day">Sabtu – Minggu (Pukul 08.00 – 16.00)</option>
                <option value="weekend_night">Sabtu – Minggu (Pukul 16.00 – 23.00)</option>
              </optgroup>
              <optgroup label="Tarif Gym &amp; Fitness (Harian &amp; Member)">
                <option value="weekday">Senin – Jumat (Daily Pass / Harian)</option>
                <option value="weekend">Sabtu – Minggu (Daily Pass / Harian)</option>
                <option value="member">Member Bulanan (Akses Penuh)</option>
                <option value="admin">Biaya Administrasi Registrasi</option>
              </optgroup>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Harga per Jam (Rp)</label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="price" class="form-control fw-semibold" min="1" placeholder="Contoh: 250000" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg me-1"></i> Simpan Tarif
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
