@extends('layouts.admin')
@section('title', 'Kelola User Admin')

@section('content')
<div class="lx-page-head">
  <div>
    <h1 class="lx-page-title">Kelola User Admin</h1>
    <p class="lx-page-sub">Tambahkan akun User Admin untuk memverifikasi pembayaran QRIS dan melacak siapa Admin yang meng-approve pesanan. User Admin selain Admin Utama hanya dapat mengakses <strong>Dashboard</strong>, <strong>Jadwal Lapangan</strong>, serta <strong>Daftar Booking & Approval</strong>.</p>
  </div>
</div>

<div class="row g-4">
  <!-- Daftar User Admin -->
  <div class="col-lg-8">
    <div class="lx-card">
      <div class="lx-card-header">
        <div>
          <h6 class="lx-card-title">Daftar Akun Admin Aktif</h6>
          <div class="lx-card-sub">{{ $cashiers->count() }} akun terdaftar beserta hak akses dan jumlah booking yang telah di-approve</div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="lx-table">
          <thead>
            <tr>
              <th>Nama & Level Akses</th>
              <th>Kontak & Email</th>
              <th>Hak Akses Menu</th>
              <th class="text-center">Pesanan Di-Approve</th>
              <th class="text-end" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cashiers as $c)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle {{ $c->isSuperAdmin() ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success' }} fw-bold d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.82rem;">
                      {{ strtoupper(substr($c->name, 0, 1)) }}
                    </div>
                    <div>
                      <div class="fw-bold text-dark">
                        {{ $c->name }}
                        @if($c->id === auth()->id())
                          <span class="badge bg-success bg-opacity-10 text-success border ms-1" style="font-size:0.65rem;">Anda</span>
                        @endif
                      </div>
                      <div class="d-flex align-items-center gap-1 mt-0.5">
                        @if($c->isSuperAdmin())
                          <span class="badge bg-dark" style="font-size: 0.65rem;">Admin Utama</span>
                        @else
                          <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">User Admin</span>
                        @endif
                        <span class="text-muted lx-mono" style="font-size: 0.7rem;">#{{ $c->id }}</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="text-dark small fw-semibold">{{ $c->email }}</div>
                  <div class="text-muted lx-mono small">{{ $c->phone ?: '-' }}</div>
                </td>
                <td>
                  @if($c->isSuperAdmin())
                    <span class="lx-pill lx-pill-info">Akses Penuh (Semua Menu)</span>
                  @else
                    <div class="small text-muted" style="font-size: 0.75rem; line-height: 1.35;">
                      <i class="bi bi-check2 text-success"></i> Dashboard<br>
                      <i class="bi bi-check2 text-success"></i> Jadwal Lapangan<br>
                      <i class="bi bi-check2 text-success"></i> Daftar Booking & Approval
                    </div>
                  @endif
                </td>
                <td class="text-center">
                  <span class="lx-pill lx-pill-success">{{ $c->approved_bookings_count ?? 0 }} Booking</span>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="{{ route('admin.cashiers.edit', $c) }}" class="lx-btn lx-btn-outline lx-btn-sm" title="Edit User Admin">
                      <i class="bi bi-pencil"></i> Edit
                    </a>
                    @if($c->id !== auth()->id() && !$c->isSuperAdmin())
                      <form action="{{ route('admin.cashiers.destroy', $c) }}" method="POST" class="d-inline" data-confirm="Hapus akun User Admin '{{ $c->name }}'? Admin ini tidak akan bisa login lagi." data-confirm-title="Hapus User Admin">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lx-btn lx-btn-ghost lx-btn-sm text-danger" title="Hapus User Admin">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5">
                  <div class="lx-empty">
                    <div class="lx-empty-icon"><i class="bi bi-person-badge"></i></div>
                    <div class="lx-empty-title">Belum ada akun User Admin tambahan</div>
                    <div class="lx-empty-sub">Gunakan formulir di samping untuk membuat akun User Admin baru.</div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Form Cepat Tambah User Admin -->
  <div class="col-lg-4">
    <div class="lx-card position-sticky" style="top: 80px;">
      <div class="lx-card-header">
        <div>
          <h6 class="lx-card-title"><i class="bi bi-person-plus text-success me-1"></i>Tambah User Admin Baru</h6>
          <div class="lx-card-sub">Akses: Dashboard, Jadwal Lapangan, & Approval Booking</div>
        </div>
      </div>
      <div class="lx-card-body">
        <form action="{{ route('admin.cashiers.store') }}" method="POST" autocomplete="off">
          @csrf
          <div class="mb-3">
            <label class="lx-label">Nama Lengkap Admin <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Admin Raka" autocomplete="off" required>
          </div>

          <div class="mb-3">
            <label class="lx-label">Email Login <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="raka@lino.com" autocomplete="new-email" required>
          </div>

          <div class="mb-3">
            <label class="lx-label">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
            <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08123456789" autocomplete="off" required minlength="9">
          </div>

          <div class="mb-3">
            <label class="lx-label">Kata Sandi <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password" required minlength="6">
          </div>

          <div class="mb-4">
            <label class="lx-label">Ulangi Kata Sandi <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi kata sandi" autocomplete="new-password" required minlength="6">
          </div>

          <button type="submit" class="lx-btn lx-btn-primary w-100">
            <i class="bi bi-check2-circle"></i> Buat Akun User Admin
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
