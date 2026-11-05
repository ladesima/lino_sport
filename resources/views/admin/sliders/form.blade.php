@extends('layouts.admin')
@section('title', $slider->id ? 'Edit Slider' : 'Tambah Slider Baru')

@section('content')
<div class="lx-page-head">
  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('admin.sliders.index') }}" class="lx-btn lx-btn-outline lx-btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <div>
      <h1 class="lx-page-title">{{ $slider->id ? 'Edit Slider Banner' : 'Tambah Slider Baru' }}</h1>
      <p class="lx-page-sub">Konfigurasi gambar visual, teks headline, dan tautan CTA halaman utama.</p>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="lx-card">
      <div class="lx-card-body">
        <form action="{{ $slider->id ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @if($slider->id) @method('PUT') @endif

          <div class="mb-3">
            <label class="lx-label">Judul Utama Banner <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $slider->title) }}" placeholder="Contoh: Main Padel Makin Seru & Berkelas">
          </div>

          <div class="mb-3">
            <label class="lx-label">Subjudul / Deskripsi Pendek</label>
            <textarea name="subtitle" class="form-control" rows="2" placeholder="Deskripsi singkat menarik...">{{ old('subtitle', $slider->subtitle) }}</textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="lx-label">Badge / Tag Khusus</label>
              <input type="text" name="badge" class="form-control" value="{{ old('badge', $slider->badge) }}" placeholder="Contoh: Lapangan Padel Eksklusif">
            </div>
            <div class="col-md-6">
              <label class="lx-label">Urutan Posisi (Sort Order)</label>
              <input type="number" name="order" class="form-control" value="{{ old('order', $slider->order ?? 0) }}" placeholder="1, 2, 3...">
            </div>
          </div>

          <div class="mb-3">
            <label class="lx-label">Upload Gambar Banner (Rasio 16:9)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <span class="lx-help">Format: JPG, PNG, WEBP. Maksimal 3MB.</span>

            @if($slider->image)
              <div class="mt-3 p-2 bg-light rounded-3 border d-inline-block">
                <small class="text-muted d-block mb-1">Gambar Saat Ini:</small>
                <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" class="rounded-2" style="width: 200px; height: 110px; object-fit: cover;">
              </div>
            @endif
          </div>

          <div class="mb-3">
            <label class="lx-label">Atau Gunakan URL Gambar Web (Opsional)</label>
            <input type="url" name="image_url" class="form-control" value="{{ old('image_url', str_starts_with($slider->image ?? '', 'http') ? $slider->image : '') }}" placeholder="https://images.unsplash.com/...">
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="lx-label">Teks Tombol CTA</label>
              <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $slider->button_text ?? 'Booking Sekarang') }}">
            </div>
            <div class="col-md-6">
              <label class="lx-label">Link / URL Tombol</label>
              <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $slider->button_url ?? '/booking/create') }}">
            </div>
          </div>

          <div class="mb-4 form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $slider->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold small text-dark" for="isActive">Tampilkan Slider Ini di Halaman Utama</label>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="lx-btn lx-btn-primary">
              <i class="bi bi-check2"></i> Simpan Slider
            </button>
            <a href="{{ route('admin.sliders.index') }}" class="lx-btn lx-btn-outline">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
