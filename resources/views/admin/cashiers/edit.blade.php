@extends('layouts.admin')
@section('title', 'Edit User Admin #' . $cashier->id)

@section('content')
<div class="lx-page-head">
  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('admin.cashiers.index') }}" class="lx-btn lx-btn-outline lx-btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <div>
      <h1 class="lx-page-title">Edit User Admin: {{ $cashier->name }}</h1>
      <p class="lx-page-sub">Perbarui profil User Admin atau atur ulang kata sandi login.</p>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="lx-card">
      <div class="lx-card-body">
        <form action="{{ route('admin.cashiers.update', $cashier) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="lx-label">Nama Lengkap Admin <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $cashier->name) }}" required>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="lx-label">Email Login Admin <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" value="{{ old('email', $cashier->email) }}" required>
            </div>
            <div class="col-md-6">
              <label class="lx-label">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" value="{{ old('phone', $cashier->phone) }}" required minlength="9">
            </div>
          </div>

          <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="fw-bold text-dark small mb-1"><i class="bi bi-shield-lock me-1 text-warning"></i>Ganti Kata Sandi (Opsional)</div>
            <p class="text-muted small mb-3">Kosongkan kolom di bawah jika tidak ingin mengubah kata sandi Admin.</p>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="lx-label">Kata Sandi Baru</label>
                <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tetap" minlength="6">
              </div>
              <div class="col-md-6">
                <label class="lx-label">Ulangi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi kata sandi baru" minlength="6">
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="lx-btn lx-btn-primary">
              <i class="bi bi-check2"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.cashiers.index') }}" class="lx-btn lx-btn-outline">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
