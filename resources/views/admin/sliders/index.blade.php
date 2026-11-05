@extends('layouts.admin')
@section('title', 'Kelola Sliders Banner')

@section('content')
<div class="lx-page-head">
  <div>
    <h1 class="lx-page-title">Slider & Banner Beranda</h1>
    <p class="lx-page-sub">Atur gambar banner carousel, judul promosi, badge, dan tombol aksi di halaman utama.</p>
  </div>
  <div class="lx-page-actions">
    <a href="{{ route('admin.sliders.create') }}" class="lx-btn lx-btn-primary">
      <i class="bi bi-plus-lg"></i> Tambah Slider Baru
    </a>
  </div>
</div>

<div class="lx-card">
  <div class="table-responsive">
    <table class="lx-table">
      <thead>
        <tr>
          <th style="width: 75px;">Urutan</th>
          <th style="width: 145px;">Preview Banner</th>
          <th>Judul & Subjudul</th>
          <th>Badge Tag</th>
          <th>Tombol CTA</th>
          <th>Status</th>
          <th class="text-end" style="width: 150px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($sliders as $slider)
      @php
        $imgSrc = $slider->image_url ?: 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=800&auto=format&fit=crop&q=80';
      @endphp
      <tr>
        <td>
          <span class="lx-pill lx-pill-neutral lx-mono">#{{ $slider->order }}</span>
        </td>
        <td>
          <div class="rounded-3 overflow-hidden border" style="width: 128px; height: 72px;">
            <img src="{{ $imgSrc }}" alt="{{ $slider->title }}" class="w-100 h-100" style="object-fit: cover;">
          </div>
        </td>
        <td>
          <div class="fw-bold text-dark mb-1">{{ $slider->title }}</div>
          <div class="text-muted small" style="max-width: 340px;">{{ $slider->subtitle ?: '-' }}</div>
        </td>
        <td>
          @if($slider->badge)
            <span class="lx-pill lx-pill-warning">{{ $slider->badge }}</span>
          @else
            <span class="text-muted small">-</span>
          @endif
        </td>
        <td>
          <div class="small fw-semibold text-dark">{{ $slider->button_text }}</div>
          <div class="text-muted lx-mono" style="font-size: 0.72rem;">{{ $slider->button_url }}</div>
        </td>
        <td>
          @if($slider->is_active)
            <span class="lx-pill lx-pill-success"><span class="lx-pill-dot"></span>Aktif</span>
          @else
            <span class="lx-pill lx-pill-neutral"><span class="lx-pill-dot"></span>Nonaktif</span>
          @endif
        </td>
        <td class="text-end">
          <div class="d-inline-flex gap-1">
            <a href="{{ route('admin.sliders.edit', $slider) }}" class="lx-btn lx-btn-outline lx-btn-sm">
              <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-inline" data-confirm="Hapus slider banner '{{ $slider->title }}'?" data-confirm-title="Hapus Slider">
              @csrf @method('DELETE')
              <button type="submit" class="lx-btn lx-btn-ghost lx-btn-sm text-danger" title="Hapus Slider">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7">
          <div class="lx-empty">
            <div class="lx-empty-icon"><i class="bi bi-images"></i></div>
            <div class="lx-empty-title">Belum ada slider banner</div>
            <div class="lx-empty-sub">Tambahkan gambar promosi untuk ditampilkan pada carousel beranda.</div>
          </div>
        </td>
      </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
