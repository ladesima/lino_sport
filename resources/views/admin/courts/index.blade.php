@extends('layouts.admin')
@section('title', 'Kelola Lapangan')

@section('content')
<div class="lx-page-head">
  <div>
    <h1 class="lx-page-title">Kelola Lapangan</h1>
    <p class="lx-page-sub">Atur daftar lapangan aktif, kategori cabang olahraga, dan foto tampilan beranda.</p>
  </div>
</div>

<div class="row g-4">
  <!-- Daftar Lapangan -->
  <div class="col-lg-8">
    <div class="lx-card">
      <div class="lx-card-header">
        <div>
          <h6 class="lx-card-title">Daftar Lapangan Terdaftar</h6>
          <div class="lx-card-sub">Total {{ $courts->count() }} fasilitas lapangan olahraga</div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="lx-table">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th style="width: 90px;">Foto</th>
              <th>Nama Lapangan</th>
              <th>Olahraga</th>
              <th>Status</th>
              <th class="text-end" style="width: 150px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @forelse($courts as $c)
          <tr>
            <td class="lx-mono text-muted fw-semibold">#{{ $c->id }}</td>
            <td>
              @if($c->image)
                <img src="{{ asset('storage/' . $c->image) }}" alt="{{ $c->name }}" class="rounded-3 border" style="width: 64px; height: 44px; object-fit: cover;">
              @else
                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted small" style="width: 64px; height: 44px; font-size: 0.7rem;">
                  <i class="bi bi-image"></i>
                </div>
              @endif
            </td>
            <td>
              <div class="fw-bold text-dark">{{ $c->name }}</div>
            </td>
            <td>
              <span class="lx-pill lx-pill-info">{{ ucfirst($c->sport) }}</span>
            </td>
            <td>
              @if($c->is_active)
                <span class="lx-pill lx-pill-success"><span class="lx-pill-dot"></span>Aktif</span>
              @else
                <span class="lx-pill lx-pill-neutral"><span class="lx-pill-dot"></span>Nonaktif</span>
              @endif
            </td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a href="{{ route('admin.courts.edit', $c) }}" class="lx-btn lx-btn-outline lx-btn-sm">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="{{ route('admin.courts.destroy', $c) }}" method="POST" class="d-inline" data-confirm="Hapus lapangan '{{ $c->name }}'? Data jadwal terkait akan ikut terpengaruh." data-confirm-title="Hapus Lapangan">
                  @csrf @method('DELETE')
                  <button type="submit" class="lx-btn lx-btn-ghost lx-btn-sm text-danger" title="Hapus Lapangan">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6">
              <div class="lx-empty">
                <div class="lx-empty-icon"><i class="bi bi-Dribbble"></i></div>
                <div class="lx-empty-title">Belum ada data lapangan</div>
                <div class="lx-empty-sub">Tambahkan lapangan pertama melalui formulir di samping.</div>
              </div>
            </td>
          </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Form Cepat Tambah Lapangan -->
  <div class="col-lg-4">
    <div class="lx-card position-sticky" style="top: 80px;">
      <div class="lx-card-header">
        <div>
          <h6 class="lx-card-title"><i class="bi bi-plus-circle text-success me-1"></i>Tambah Lapangan Baru</h6>
          <div class="lx-card-sub">Langsung aktif untuk dipesan pelanggan</div>
        </div>
      </div>
      <div class="lx-card-body">
        <form action="{{ route('admin.courts.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="lx-label">Nama Lapangan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Contoh: Padel Court A">
          </div>

          <div class="mb-3">
            <label class="lx-label">Cabang Olahraga <span class="text-danger">*</span></label>
            <select name="sport" class="form-select" required>
              @foreach(['padel' => 'Padel', 'bulutangkis' => 'Bulutangkis (Badminton)', 'pickleball' => 'Pickleball'] as $val => $label)
                <option value="{{ $val }}" {{ old('sport') == $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="lx-label">Foto Lapangan (Opsional)</label>
            <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
            <span class="lx-help">Format JPG, PNG, WEBP (Maks. 2MB).</span>
          </div>

          <div class="mb-4">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="isActiveNew" value="1" checked>
              <label class="form-check-label small fw-semibold" for="isActiveNew">Langsung Aktifkan Lapangan</label>
            </div>
          </div>

          <button type="submit" class="lx-btn lx-btn-primary w-100">
            <i class="bi bi-check2-circle"></i> Simpan Lapangan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
