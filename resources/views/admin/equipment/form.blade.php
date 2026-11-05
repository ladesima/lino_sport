@extends('layouts.admin')
@section('title', $equipment->id ? 'Edit Peralatan' : 'Tambah Peralatan')
@section('content')
<div class="card" style="max-width:500px">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-4">{{ $equipment->id ? 'Edit' : 'Tambah' }} Peralatan</h5>
    <form action="{{ $equipment->id ? route('admin.equipment.update', $equipment) : route('admin.equipment.store') }}" method="POST">
      @csrf
      @if($equipment->id) @method('PUT') @endif
      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Peralatan</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $equipment->name) }}">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Harga Sewa / Jual (Rp)</label>
        <input type="number" name="price" class="form-control" required min="1" value="{{ old('price', $equipment->price) }}">
        <small class="text-muted">Raket dihitung per jam (/jam), sedangkan bola &amp; shuttlecock dihitung per item (/item).</small>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.equipment.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
