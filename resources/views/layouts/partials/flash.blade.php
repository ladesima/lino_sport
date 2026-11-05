{{-- Notifikasi terpadu: toast untuk pesan sesi + daftar error validasi inline --}}
@php
  $lxFlash = [];
  foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
    if (session()->has($key) && is_string(session($key))) $lxFlash[] = [$type, session($key)];
  }
  $lxIcons = ['success' => 'bi-check-lg', 'error' => 'bi-exclamation-octagon', 'warning' => 'bi-exclamation-triangle', 'info' => 'bi-info-lg'];
  $lxTitles = ['success' => 'Berhasil', 'error' => 'Gagal', 'warning' => 'Perhatian', 'info' => 'Info'];
@endphp

<div class="lx-toast-stack d-print-none" aria-live="polite">
  @foreach($lxFlash as [$type, $msg])
    <div class="lx-toast lx-toast-{{ $type }}" role="status" data-type="{{ $type }}">
      <span class="lx-toast-icon"><i class="bi {{ $lxIcons[$type] }}"></i></span>
      <div class="flex-grow-1">
        <div class="lx-toast-title">{{ $lxTitles[$type] }}</div>
        <div>{{ $msg }}</div>
      </div>
      <button type="button" class="lx-toast-close" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
    </div>
  @endforeach
</div>

@if(isset($errors) && $errors->any())
  <div class="alert alert-danger d-flex gap-2 align-items-start mb-4 d-print-none" role="alert">
    <i class="bi bi-exclamation-octagon mt-1"></i>
    <div>
      <div class="fw-bold mb-1">Periksa kembali isian berikut:</div>
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif
