@extends('layouts.admin')
@section('title', $court->id ? 'Edit Lapangan' : 'Tambah Lapangan')

@section('content')
<div class="lx-page-head">
  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('admin.courts.index') }}" class="lx-btn lx-btn-outline lx-btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <div>
      <h1 class="lx-page-title">{{ $court->id ? 'Edit Lapangan: ' . $court->name : 'Tambah Lapangan Baru' }}</h1>
      <p class="lx-page-sub">Perbarui informasi lapangan, foto fasilitas, dan status operasional.</p>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="lx-card">
      <div class="lx-card-body">
        <form action="{{ $court->id ? route('admin.courts.update', $court) : route('admin.courts.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          @if($court->id) @method('PUT') @endif

          <div class="mb-3">
            <label class="lx-label">Nama Lapangan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $court->name) }}" placeholder="Contoh: Padel Court">
          </div>

          <div class="mb-3">
            <label class="lx-label">Jenis Olahraga <span class="text-danger">*</span></label>
            <select name="sport" class="form-select" required>
              @foreach(['padel' => 'Padel', 'bulutangkis' => 'Bulutangkis (Badminton)', 'pickleball' => 'Pickleball'] as $val => $label)
                <option value="{{ $val }}" {{ old('sport', $court->sport) == $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="lx-label">Foto Lapangan</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <span class="lx-help">Format: JPG, PNG, WEBP. Maksimal 2MB.</span>
            @if($court->image)
              <div class="mt-3 p-2 bg-light rounded-3 border d-inline-block">
                <small class="text-muted d-block mb-1">Foto saat ini:</small>
                <img src="{{ asset('storage/' . $court->image) }}" alt="{{ $court->name }}" class="rounded-2 border" style="width: 140px; height: 90px; object-fit: cover;">
              </div>
            @endif
          </div>

          <div class="mb-4">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $court->is_active ?? true) ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold small" for="isActive">Status Lapangan Aktif (Bisa Dipesan)</label>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="lx-btn lx-btn-primary">
              <i class="bi bi-check2"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.courts.index') }}" class="lx-btn lx-btn-outline">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
