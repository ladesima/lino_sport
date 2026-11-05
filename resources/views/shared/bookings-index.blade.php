@php
  $prefix = $prefix ?? 'admin';
  $courts = $courts ?? \App\Models\Court::where('is_active', true)->get();
  $curStatus = request('status', '');
  $hasFilter = request()->filled('q') || request()->filled('status') || request()->filled('court') || request()->filled('date');
@endphp

<div class="lx-page-header">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h2 class="lx-page-title mb-0">Daftar Booking &amp; Approval Admin</h2>
      <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill small d-inline-flex align-items-center gap-1.5" style="font-size:0.72rem;">
        <span class="spinner-grow spinner-grow-sm text-success" style="width:7px;height:7px;"></span>
        <span>Auto-Update Realtime Aktif</span>
      </span>
    </div>
    <p class="lx-page-sub">Lihat preview bukti pembayaran QRIS yang diupload pelanggan, validasi &amp; approve jadwal, atau cetak struk.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route($prefix . '.bookings.export', request()->all()) }}" class="btn btn-success d-inline-flex align-items-center gap-1.5 shadow-sm" style="font-weight: 600;" title="Download data booking dalam format Microsoft Excel (.xlsx)">
      <i class="bi bi-file-earmark-excel-fill"></i>
      <span>Extract XLSX</span>
    </a>
    <a href="{{ route($prefix . '.bookings.schedule') }}" class="btn btn-light">
      <i class="bi bi-calendar3-week me-1"></i> Jadwal Visual
    </a>
  </div>
</div>

<div class="card overflow-hidden">
  {{-- Segmented Status Tabs + Filter Bar --}}
  <div class="lx-filter justify-content-between">
    <div class="lx-tabs" role="tablist">
      @php
        $tabs = [
          ''          => 'Semua',
          'pending'   => 'Menunggu Approve',
          'confirmed' => 'Sudah Di-Approve',
          'unpaid'    => 'Perlu Pelunasan DP',
          'cancelled' => 'Dibatalkan',
        ];
      @endphp
      @foreach($tabs as $val => $label)
        <a href="{{ route($prefix . '.bookings.index', array_filter(['status' => $val, 'q' => request('q'), 'court' => request('court'), 'date' => request('date')])) }}"
           class="lx-tab {{ $curStatus === $val ? 'active' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    <form method="GET" action="{{ route($prefix . '.bookings.index') }}" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1" style="max-width:640px" id="bookingFilterForm">
      @if($curStatus)
        <input type="hidden" name="status" value="{{ $curStatus }}">
      @endif
      <div class="lx-search">
        <i class="bi bi-search"></i>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama, no HP, atau #invoice...">
      </div>
      <select name="court" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter lapangan">
        <option value="">Semua Lapangan</option>
        @foreach($courts as $c)
          <option value="{{ $c->id }}" {{ request('court') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
      </select>
      <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm" onchange="this.form.submit()" aria-label="Filter tanggal">
      <button type="submit" class="btn btn-light btn-sm">Cari</button>
      @if($hasFilter)
        <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-ghost btn-sm" title="Reset filter"><i class="bi bi-x-lg"></i></a>
      @endif
    </form>
  </div>

  {{-- Container Tabel Dinamis yang Terupdate Otomatis Tanpa Refresh Halaman --}}
  <div id="bookingsTableContainer">
    @include('shared.bookings-table-partial')
  </div>
</div>

{{-- Modal Preview Gambar Bukti Pembayaran QRIS --}}
<div class="modal fade" id="listProofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0">
        <h6 class="modal-title fw-bold mb-0">
          <i class="bi bi-image text-warning me-1"></i> Preview Bukti Pembayaran QRIS — <span id="proofModalTitle">-</span>
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body bg-light text-center p-4">
        <img id="proofModalImg" src="#" alt="Bukti Pembayaran QRIS" class="img-fluid rounded-3 border shadow" style="max-height: 72vh; object-fit: contain; background: #fff;">
      </div>
      <div class="modal-footer justify-content-between">
        <div class="small text-muted">
          Pemesan: <strong class="text-dark" id="proofModalCustomer">-</strong> · Wajib Transfer: <strong class="text-success" id="proofModalNominal">-</strong>
        </div>
        <div class="d-flex gap-2">
          <form id="proofModalApproveForm" method="POST" action="">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-success fw-bold">
              <i class="bi bi-check2-circle me-1"></i> Validasi &amp; Approve Jadwal
            </button>
          </form>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('listProofModal');
  var bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;
  var tableContainer = document.getElementById('bookingsTableContainer');
  var isFetching = false;

  // Pasang event listener untuk tombol preview bukti dan row klik
  function bindBookingTableEvents() {
    if (!tableContainer) return;

    // 1. Preview modal bukti transfer
    tableContainer.querySelectorAll('.js-preview-proof').forEach(function (btn) {
      btn.onclick = function (e) {
        e.stopPropagation();
        if (!bsModal) return;
        document.getElementById('proofModalTitle').textContent = this.dataset.invoice;
        document.getElementById('proofModalCustomer').textContent = this.dataset.customer;
        document.getElementById('proofModalNominal').textContent = this.dataset.nominal;
        document.getElementById('proofModalImg').src = this.dataset.proofUrl;

        var approveForm = document.getElementById('proofModalApproveForm');
        approveForm.action = this.dataset.confirmUrl;
        approveForm.style.display = this.dataset.status === 'pending' ? 'inline-block' : 'none';

        bsModal.show();
      };
    });

    // 2. Klik baris tabel untuk buka detail
    tableContainer.querySelectorAll('.lx-row-link').forEach(function (row) {
      row.onclick = function (e) {
        if (e.target.closest('a, button, form, input, select')) return;
        var href = this.getAttribute('data-href');
        if (href) window.location.href = href;
      };
    });
  }

  // Fungsi reload tabel secara halus via AJAX tanpa reload halaman
  function refreshBookingsTable(callback) {
    if (isFetching || !tableContainer) return;
    isFetching = true;

    var currentUrl = window.location.href;

    fetch(currentUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html'
      }
    })
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.text();
    })
    .then(function (html) {
      if (html && html.trim().length > 0) {
        tableContainer.innerHTML = html;
        bindBookingTableEvents();
        if (typeof callback === 'function') callback();
      }
    })
    .catch(function (err) {
      console.warn('[Auto-Update Bookings] Error refreshing table:', err);
    })
    .finally(function () {
      isFetching = false;
    });
  }

  // Inisialisasi awal
  bindBookingTableEvents();

  // 1. Tanggapi event lonceng ketika ada booking baru / bukti bayar baru
  window.addEventListener('lino:pending-changed', function (e) {
    refreshBookingsTable(function () {
      var firstRow = tableContainer.querySelector('tbody tr');
      if (firstRow) {
        firstRow.style.transition = 'background-color 0.8s ease';
        firstRow.style.backgroundColor = '#d1fae5';
        setTimeout(function () {
          firstRow.style.backgroundColor = '';
        }, 2500);
      }
    });
  });

  // 2. Auto-refresh tabel berkala setiap 6 detik
  setInterval(function () {
    refreshBookingsTable();
  }, 6000);
});
</script>
@endsection
