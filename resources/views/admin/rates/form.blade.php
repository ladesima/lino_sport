@extends('layouts.admin')
@section('title', $rate->id ? 'Edit Tarif' : 'Tambah Tarif')
@section('content')
<div class="card" style="max-width:500px">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-4">{{ $rate->id ? 'Edit' : 'Tambah' }} Tarif</h5>
    <form action="{{ $rate->id ? route('admin.rates.update', $rate) : route('admin.rates.store') }}" method="POST">
      @csrf
      @if($rate->id) @method('PUT') @endif
      <div class="mb-3">
        <label class="form-label fw-semibold">Olahraga</label>
        <select name="sport" class="form-select" required>
          @foreach(['padel','bulutangkis','pickleball'] as $s)
          <option value="{{ $s }}" {{ old('sport', $rate->sport) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Sesi Waktu (Berlaku Setiap Hari)</label>
        <select name="day_type" class="form-select" required>
          <option value="weekday" {{ old('day_type', $rate->day_type) == 'weekday' ? 'selected' : '' }}>Pagi – Sore (07.00 – 17.00)</option>
          <option value="weekend" {{ old('day_type', $rate->day_type) == 'weekend' ? 'selected' : '' }}>Sore – Malam (17.00 – 22.00)</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Harga per Jam (Rp)</label>
        <input type="number" name="price" class="form-control" required min="1" value="{{ old('price', $rate->price) }}">
      </div>
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.rates.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
