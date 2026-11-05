@extends('layouts.admin')
@section('title', 'Peralatan & Item Sewa')

@section('content')
<div class="lx-page-header">
  <div>
    <h2 class="lx-page-title">Peralatan & Item Sewa</h2>
    <p class="lx-page-sub">Ubah nama atau harga langsung di tabel lalu klik Simpan. Perubahan langsung aktif di POS Kasir & Beranda.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card overflow-hidden">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-box-seam text-success"></i> Daftar Peralatan</h6>
        <span class="text-muted small">{{ $equipment->count() }} item</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th class="ps-4">Nama Peralatan / Item</th>
              <th style="width:130px">Skema Hitung</th>
              <th style="width:210px">Harga Sewa / Jual (Rp)</th>
              <th class="text-end pe-4" style="width:150px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($equipment as $e)
              <tr>
                <td class="ps-4">
                  <form action="{{ route('admin.equipment.update', $e) }}" method="POST" id="eqForm{{ $e->id }}">
                    @csrf @method('PUT')
                    <input type="text" name="name" class="form-control form-control-sm fw-semibold" value="{{ $e->name }}" required>
                  </form>
                </td>
                <td>
                  @if($e->is_hourly)
                    <span class="lx-pill lx-pill-info">Per Jam (/jam)</span>
                  @else
                    <span class="lx-pill lx-pill-success">Per Item (/item)</span>
                  @endif
                </td>
                <td>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="price" form="eqForm{{ $e->id }}" class="form-control fw-bold" value="{{ $e->price }}" min="1" required>
                    <span class="input-group-text text-muted">{{ $e->unit_label }}</span>
                  </div>
                </td>
                <td class="text-end pe-4">
                  <div class="d-inline-flex gap-1">
                    <button type="submit" form="eqForm{{ $e->id }}" class="btn btn-primary btn-sm">Simpan</button>
                    <form action="{{ route('admin.equipment.destroy', $e) }}" method="POST" class="d-inline"
                          data-confirm="Hapus item {{ $e->name }} dari katalog peralatan?"
                          data-confirm-title="Hapus Peralatan" data-confirm-variant="danger">
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
                <td colspan="4" class="lx-empty">Belum ada peralatan terdaftar.</td>
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
        <h6 class="lx-card-title"><i class="bi bi-plus-circle text-success"></i> Tambah Item Baru</h6>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.equipment.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Nama Peralatan / Item</label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Raket Padel / Bola Pickleball" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Harga (Rp)</label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="price" class="form-control fw-semibold" min="1" placeholder="Contoh: 20000" required>
            </div>
            <div class="form-text">Raket dihitung <strong>per jam (/jam)</strong>; Bola Padel, Bola Pickleball, &amp; Shuttlecock otomatis dihitung <strong>per item (/item)</strong>.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg me-1"></i> Tambah ke Katalog
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
