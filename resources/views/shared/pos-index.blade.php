@php
  $prefix = $prefix ?? 'admin';
  $initCourtRem = $selectedBooking ? $selectedBooking->court_remaining : 0;
  $initEquipRem = $selectedBooking ? $selectedBooking->equipment_remaining : 0;
  $initTotalRem = $selectedBooking ? $selectedBooking->remaining_amount : 0;
@endphp

<div class="lx-page-header">
  <div>
    <h2 class="lx-page-title">Kasir Pembayaran & POS Sewa Alat</h2>
    <p class="lx-page-sub">Bayar langsung sewa peralatan baru, lunasi sisa tagihan lapangan/DP, atau gabungkan keduanya dalam 1 transaksi.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    @if(session('print_receipt_id'))
      <a href="{{ route($prefix . '.bookings.receipt', session('print_receipt_id')) }}" target="_blank" class="btn btn-dark">
        <i class="bi bi-printer me-1"></i> Cetak Struk Terakhir
      </a>
    @endif
    <a href="{{ route($prefix . '.bookings.index') }}" class="btn btn-light">
      <i class="bi bi-inbox me-1"></i> Daftar Booking
    </a>
  </div>
</div>

{{-- Banner Bukti Pembayaran Terakhir & Auto-Print Struk Langsung --}}
@if(session('print_receipt_id'))
  @php
    $autoPrintUrl = route($prefix . '.bookings.receipt', [
      'booking'       => session('print_receipt_id'),
      'cash_received' => session('cash_received'),
      'cash_change'   => session('cash_change'),
      'autoprint'     => 1,
    ]);
  @endphp
  <iframe id="autoPrintReceiptFrame"
          src="{{ route($prefix . '.bookings.receipt', ['booking' => session('print_receipt_id'), 'cash_received' => session('cash_received'), 'cash_change' => session('cash_change')]) }}"
          style="position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden"
          onload="setTimeout(function(){ try { var f = document.getElementById('autoPrintReceiptFrame'); if (f && f.contentWindow) { f.contentWindow.focus(); f.contentWindow.print(); } } catch(e){} }, 250);"></iframe>

  <div class="card mb-4" style="background:#ecfdf5;border:1px solid #6ee7b7">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
      <div class="d-flex align-items-center gap-3">
        <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#059669;color:#fff">
          <i class="bi bi-printer-fill fs-5"></i>
        </span>
        <div>
          <div class="fw-bold text-dark">
            Transaksi Selesai ({{ session('last_paid_method', 'TUNAI') }} Rp{{ number_format(session('last_paid_total', 0), 0, ',', '.') }}) — Mencetak Struk Otomatis...
          </div>
          <div class="small text-muted">
            {{ session('last_paid_summary') }}
            @if(session('cash_change') > 0)
              · <strong class="text-success">Kembalian: Rp{{ number_format(session('cash_change'), 0, ',', '.') }}</strong>
            @endif
          </div>
        </div>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-dark btn-sm"
                onclick="var f=document.getElementById('autoPrintReceiptFrame'); if(f&&f.contentWindow){f.contentWindow.focus();f.contentWindow.print();}else{window.open('{{ $autoPrintUrl }}','_blank');}">
          <i class="bi bi-printer-fill me-1"></i> Cetak Ulang Struk
        </button>
        <a href="{{ $autoPrintUrl }}" target="_blank" class="btn btn-light btn-sm">
          <i class="bi bi-box-arrow-up-right me-1"></i> Buka Struk
        </a>
      </div>
    </div>
  </div>
@endif

<div class="row g-4 align-items-start">
  {{-- Kolom Kiri: Pilih Pemain + Katalog Item --}}
  <div class="col-lg-7">

    {{-- 1. Pilih Pemain / Booking Aktif (Tanpa Reload agar keranjang tidak hilang) --}}
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><span class="badge bg-dark">1</span> Pilih Pemain / Booking Lapangan</h6>
        <a href="{{ route($prefix . '.bookings.show', $selectedBooking?->id ?? ($bookings->first()?->id ?? 0)) }}"
           id="posDetailLink" class="btn btn-ghost btn-sm {{ $selectedBooking ? '' : 'd-none' }}">
          Rincian Invoice <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
      <div class="card-body">
        <select id="posBookingSelect" class="form-select">
          <option value="" {{ !$selectedBookingId ? 'selected' : '' }}>-- Pilih pemain yang sedang/akan bermain --</option>
          @foreach($bookings as $b)
            @php
              $bDate = \Carbon\Carbon::parse($b->start_dt)->format('d M Y');
              $bTime = \Carbon\Carbon::parse($b->start_dt)->format('H:i') . '–' . \Carbon\Carbon::parse($b->end_dt)->format('H:i');
              $cRem = $b->court_remaining;
              $eRem = $b->equipment_remaining;
              $tRem = $b->remaining_amount;
              $payTag = $b->is_fully_paid ? 'LUNAS' : 'Sisa Rp' . number_format($tRem, 0, ',', '.');
            @endphp
            <option value="{{ $b->id }}"
                    data-url="{{ route($prefix . '.bookings.show', $b->id) }}"
                    data-name="{{ $b->display_name }}"
                    data-court="{{ $b->court->name }}"
                    data-time="{{ $bDate }} · {{ $bTime }}"
                    data-paid="{{ $b->is_fully_paid ? '1' : '0' }}"
                    data-remaining="{{ $tRem }}"
                    data-court-rem="{{ $cRem }}"
                    data-equip-rem="{{ $eRem }}"
                    {{ $selectedBookingId == $b->id ? 'selected' : '' }}>
              #{{ $b->formatted_invoice_no }} — {{ $b->display_name }} · {{ $b->court->name }} ({{ $bDate }} {{ $bTime }}) [{{ $payTag }}]
            </option>
          @endforeach
        </select>

        <div id="posBookingSummary" class="mt-3 p-3 rounded-3 bg-light border small {{ $selectedBooking ? '' : 'd-none' }}">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <strong class="text-dark" id="posSumName">{{ $selectedBooking?->display_name }}</strong>
              <div class="text-muted" id="posSumMeta">
                @if($selectedBooking)
                  {{ $selectedBooking->court->name }} · {{ \Carbon\Carbon::parse($selectedBooking->start_dt)->format('d M Y · H:i') }}–{{ \Carbon\Carbon::parse($selectedBooking->end_dt)->format('H:i') }}
                @endif
              </div>
            </div>
            <div class="d-flex gap-1 flex-wrap" id="posSumBadges">
              @if($selectedBooking)
                @if($selectedBooking->is_fully_paid)
                  <span class="lx-pill lx-pill-success">Semua Tagihan Lunas</span>
                @else
                  @if($initCourtRem > 0)
                    <span class="lx-pill lx-pill-warning">Sisa Lapangan: Rp{{ number_format($initCourtRem, 0, ',', '.') }}</span>
                  @else
                    <span class="lx-pill lx-pill-success">Lapangan Lunas</span>
                  @endif
                  @if($initEquipRem > 0)
                    <span class="lx-pill lx-pill-danger">Sisa Alat: Rp{{ number_format($initEquipRem, 0, ',', '.') }}</span>
                  @endif
                @endif
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 2. Katalog Peralatan (1-Klik) --}}
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><span class="badge bg-dark">2</span> Tambah Sewa Alat / Item (Opsional)</h6>
        <span class="text-muted small">Klik item untuk masuk ke pembayaran</span>
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          @foreach($equipmentList as $eq)
            @php
              $isHourly = $eq->is_hourly;
              $unit = $eq->unit_label;
            @endphp
            <div class="col-6 col-md-4">
              <button type="button" class="card w-100 text-start p-3 h-100 border bg-white pos-item-btn"
                      onclick="addToCart('{{ $eq->id }}', @js($eq->name), {{ (int)$eq->price }}, {{ $isHourly ? 'true' : 'false' }})"
                      style="cursor:pointer;transition:border-color .15s,background .15s">
                <div class="d-flex justify-content-between align-items-start w-100 mb-2">
                  <span class="rounded-2 d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:#ecfdf5;color:#059669">
                    <i class="bi bi-plus-lg"></i>
                  </span>
                  <span class="badge bg-light text-muted border">{{ $unit }}</span>
                </div>
                <div class="fw-semibold text-dark small mb-1">{{ $eq->name }}</div>
                <div class="fw-bold text-success">Rp{{ number_format($eq->price, 0, ',', '.') }}<small class="text-muted fw-normal" style="font-size:.72rem">{{ $unit }}</small></div>
              </button>
            </div>
          @endforeach
        </div>

        {{-- Tambah Item Kustom Inline --}}
        <div class="p-3 rounded-3 bg-light border">
          <div class="small fw-semibold text-dark mb-2"><i class="bi bi-plus-circle me-1 text-muted"></i>Item Kustom (Minuman / Handuk / Lainnya — Dihitung /item)</div>
          <div class="row g-2">
            <div class="col-sm-6">
              <input type="text" id="customPosName" class="form-control form-control-sm" placeholder="Nama item (mis. Air Mineral)">
            </div>
            <div class="col-sm-4">
              <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="number" id="customPosPrice" class="form-control form-control-sm" placeholder="Harga /item" min="0">
              </div>
            </div>
            <div class="col-sm-2 d-grid">
              <button type="button" class="btn btn-light btn-sm" onclick="addCustomPosItem()">+ Tambah</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Kolom Kanan: Panel Pembayaran Langsung Kasir --}}
  <div class="col-lg-5">
    <div class="card position-sticky" style="top:76px">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="lx-card-title"><i class="bi bi-cash-coin text-success"></i> Pembayaran Langsung Kasir</h6>
        <span class="badge bg-light text-dark border" id="cartCountBadge">0 Item Alat</span>
      </div>

      <div class="card-body">
        <form action="{{ route($prefix . '.pos.checkout') }}" method="POST" id="posCheckoutForm">
          @csrf
          <input type="hidden" name="booking_id" id="checkoutBookingId" value="{{ $selectedBookingId }}">

          {{-- Opsi Sekaligus Lunasi Sisa Tagihan Lapangan / Alat Sebelumnya --}}
          <div id="unpaidBillBox" class="p-3 rounded-3 mb-3 {{ $initTotalRem > 0 ? '' : 'd-none' }}" style="background:#fffbeb;border:1px solid #fcd34d">
            <div class="form-check mb-1">
              <input class="form-check-input" type="checkbox" name="include_unpaid_bill" value="1" id="includeUnpaidCheck" {{ $initTotalRem > 0 ? 'checked' : '' }}>
              <label class="form-check-label fw-bold text-dark small d-flex justify-content-between align-items-center" for="includeUnpaidCheck" style="cursor:pointer">
                <span>Sekaligus Lunasi Sisa Tagihan Booking</span>
                <span class="text-danger font-mono" id="unpaidBillAmountText">Rp{{ number_format($initTotalRem, 0, ',', '.') }}</span>
              </label>
            </div>
            <div class="text-muted ps-4" style="font-size:.72rem" id="unpaidBillDetailText">
              @if($initTotalRem > 0)
                Rincian: Sisa Lapangan Rp{{ number_format($initCourtRem, 0, ',', '.') }}
                @if($initEquipRem > 0) + Sisa Alat Rp{{ number_format($initEquipRem, 0, ',', '.') }} @endif
              @endif
            </div>
          </div>

          {{-- Daftar Item Alat Baru di Keranjang --}}
          <div id="cartEmptyState" class="lx-empty py-3 mb-3">
            <div class="lx-empty-icon"><i class="bi bi-cart3"></i></div>
            <div class="lx-empty-title small">Belum ada sewa alat baru dipilih</div>
            <div class="small">Klik alat di kiri jika ingin menyewa alat, atau langsung bayar sisa tagihan di atas.</div>
          </div>

          <div id="cartItemsContainer" class="mb-3 d-none" style="max-height:240px;overflow-y:auto"></div>

          {{-- Ringkasan Rincian Pembayaran Terpisah --}}
          <div class="p-3 rounded-3 bg-light border mb-3 small">
            <div class="d-flex justify-content-between text-muted mb-1">
              <span>Tagihan Sewa Alat Baru:</span>
              <strong class="text-dark font-mono" id="equipSubtotalLabel">Rp 0</strong>
            </div>
            <div class="d-flex justify-content-between text-muted mb-2 pb-2 border-bottom" id="unpaidSubtotalRow">
              <span>Pelunasan Sisa Tagihan Booking:</span>
              <strong class="text-dark font-mono" id="unpaidSubtotalLabel">Rp {{ number_format($initTotalRem, 0, ',', '.') }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold text-dark fs-6">Total Bayar Sekarang</span>
              <span class="fs-4 fw-extrabold text-success font-mono" id="grandTotalLabel">Rp {{ number_format($initTotalRem, 0, ',', '.') }}</span>
            </div>
          </div>

          {{-- Metode Pembayaran Langsung --}}
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran Langsung</label>
            <div class="row g-2">
              <div class="col-6">
                <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked>
                <label class="btn btn-light w-100 py-2" for="payCash">
                  <i class="bi bi-cash-stack me-1 text-success"></i> Tunai (Cash)
                </label>
              </div>
              <div class="col-6">
                <input type="radio" class="btn-check" name="payment_method" id="payQris" value="qris">
                <label class="btn btn-light w-100 py-2" for="payQris">
                  <i class="bi bi-qr-code-scan me-1 text-primary"></i> QRIS (Langsung Lunas)
                </label>
              </div>
            </div>
          </div>

          {{-- Kalkulator Tunai & Kembalian --}}
          <div id="cashCalculationBox" class="p-3 rounded-3 bg-light border mb-3">
            <label class="form-label">Uang Tunai Diterima</label>
            <div class="input-group mb-2">
              <span class="input-group-text">Rp</span>
              <input type="number" name="cash_received" id="cashReceivedInput" class="form-control fw-bold" value="{{ $initTotalRem > 0 ? $initTotalRem : '' }}" placeholder="0" min="0">
            </div>

            <div class="d-flex flex-wrap gap-1 mb-2">
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="exact">Uang Pas</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="20000">20rb</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="50000">50rb</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="100000">100rb</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="150000">150rb</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="200000">200rb</button>
              <button type="button" class="btn btn-light btn-sm py-0 px-2 quick-cash-btn" data-amount="300000">300rb</button>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div>
                <span class="small text-muted d-block">Kembalian</span>
                <span id="changeStatusBadge" class="lx-pill lx-pill-neutral">Menunggu Input</span>
              </div>
              <div class="fs-5 fw-extrabold font-mono text-success" id="changeAmountLabel">Rp 0</div>
            </div>
          </div>

          {{-- Info QRIS Langsung --}}
          <div id="qrisInfoBox" class="p-3 rounded-3 border mb-3 d-none small" style="background:#eff6ff;border-color:#bfdbfe !important">
            <div class="fw-bold text-primary mb-1"><i class="bi bi-qr-code-scan me-1"></i>Konfirmasi Pembayaran QRIS di Kasir</div>
            <div class="text-muted">Pastikan pelanggan telah melakukan scan QRIS di meja kasir sesuai nominal <strong>Total Bayar Sekarang</strong>, lalu klik tombol di bawah untuk langsung mencatat LUNAS.</div>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" id="btnSubmitPos" class="btn btn-primary py-2" disabled>
              <i class="bi bi-printer-fill me-1"></i> Bayar & Cetak Struk Sekarang
            </button>
            <button type="button" id="btnClearCart" class="btn btn-ghost btn-sm text-danger d-none" onclick="clearCart()">
              Kosongkan Item Alat
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@section('styles')
<style>
  .pos-item-btn:hover { border-color: var(--lx-brand) !important; background: var(--lx-brand-50) !important; }
  .btn-check:checked + .btn { border-color: var(--lx-brand) !important; background: var(--lx-brand-50) !important; color: var(--lx-ink) !important; box-shadow: inset 0 0 0 1px var(--lx-brand); }
</style>
@endsection

@section('scripts')
<script>
let cart = [];
let currentEquipSubtotal = 0;
let currentUnpaidBookingRemaining = {{ (int)$initTotalRem }};
let currentGrandTotal = {{ (int)$initTotalRem }};

const bookingSelect = document.getElementById('posBookingSelect');
const checkoutBookingInput = document.getElementById('checkoutBookingId');
const summaryBox = document.getElementById('posBookingSummary');
const sumName = document.getElementById('posSumName');
const sumMeta = document.getElementById('posSumMeta');
const sumBadges = document.getElementById('posSumBadges');
const detailLink = document.getElementById('posDetailLink');

const unpaidBillBox = document.getElementById('unpaidBillBox');
const includeUnpaidCheck = document.getElementById('includeUnpaidCheck');
const unpaidBillAmountText = document.getElementById('unpaidBillAmountText');
const unpaidBillDetailText = document.getElementById('unpaidBillDetailText');

if (bookingSelect) {
  bookingSelect.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    checkoutBookingInput.value = this.value;
    if (this.value) {
      const totalRem = parseInt(opt.dataset.remaining || 0);
      const courtRem = parseInt(opt.dataset.courtRem || 0);
      const equipRem = parseInt(opt.dataset.equipRem || 0);

      currentUnpaidBookingRemaining = totalRem;
      sumName.textContent = opt.dataset.name;
      sumMeta.textContent = opt.dataset.court + ' · ' + opt.dataset.time;

      if (opt.dataset.paid === '1' || totalRem <= 0) {
        sumBadges.innerHTML = '<span class="lx-pill lx-pill-success">Semua Tagihan Lunas</span>';
        unpaidBillBox.classList.add('d-none');
        includeUnpaidCheck.checked = false;
      } else {
        let pills = '';
        if (courtRem > 0) {
          pills += `<span class="lx-pill lx-pill-warning">Sisa Lapangan: Rp${courtRem.toLocaleString('id-ID')}</span> `;
        } else {
          pills += `<span class="lx-pill lx-pill-success">Lapangan Lunas</span> `;
        }
        if (equipRem > 0) {
          pills += `<span class="lx-pill lx-pill-danger">Sisa Alat: Rp${equipRem.toLocaleString('id-ID')}</span>`;
        }
        sumBadges.innerHTML = pills;

        unpaidBillBox.classList.remove('d-none');
        includeUnpaidCheck.checked = true;
        unpaidBillAmountText.textContent = 'Rp' + totalRem.toLocaleString('id-ID');
        let detailStr = 'Rincian: Sisa Lapangan Rp' + courtRem.toLocaleString('id-ID');
        if (equipRem > 0) detailStr += ' + Sisa Alat Rp' + equipRem.toLocaleString('id-ID');
        unpaidBillDetailText.textContent = detailStr;
      }

      summaryBox.classList.remove('d-none');
      if (detailLink) { detailLink.href = opt.dataset.url; detailLink.classList.remove('d-none'); }
    } else {
      currentUnpaidBookingRemaining = 0;
      unpaidBillBox.classList.add('d-none');
      includeUnpaidCheck.checked = false;
      summaryBox.classList.add('d-none');
      if (detailLink) detailLink.classList.add('d-none');
    }
    recalcTotals(true);
  });
}

if (includeUnpaidCheck) {
  includeUnpaidCheck.addEventListener('change', function () {
    recalcTotals(true);
  });
}

function addToCart(equipId, name, price, isHourly) {
  const hourlyFlag = Boolean(isHourly);
  const existing = cart.find(item => item.equip_id === equipId && item.name === name);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ equip_id: equipId, name: name, price: parseInt(price), qty: 1, hours: 1, is_hourly: hourlyFlag });
  }
  renderCart();
}

function addCustomPosItem() {
  const nameEl = document.getElementById('customPosName');
  const priceEl = document.getElementById('customPosPrice');
  const name = (nameEl.value || '').trim();
  const price = parseInt(priceEl.value);
  if (!name || isNaN(price) || price <= 0) {
    LxToast('warning', 'Isi nama item dan harga satuan yang valid.');
    return;
  }
  addToCart('custom', name, price, false);
  nameEl.value = '';
  priceEl.value = '';
}

function updateQty(index, delta) {
  if (!cart[index]) return;
  cart[index].qty += delta;
  if (cart[index].qty <= 0) cart.splice(index, 1);
  renderCart();
}

function updateHours(index, val) {
  if (!cart[index]) return;
  cart[index].hours = cart[index].is_hourly ? Math.max(1, parseInt(val) || 1) : 1;
  renderCart();
}

function clearCart() {
  cart = [];
  renderCart();
}

function renderCart() {
  const container = document.getElementById('cartItemsContainer');
  const emptyState = document.getElementById('cartEmptyState');
  const countBadge = document.getElementById('cartCountBadge');
  const clearBtn = document.getElementById('btnClearCart');

  if (cart.length === 0) {
    container.classList.add('d-none');
    emptyState.classList.remove('d-none');
    clearBtn.classList.add('d-none');
    container.innerHTML = '';
    countBadge.innerText = '0 Item Alat';
    currentEquipSubtotal = 0;
    recalcTotals(true);
    return;
  }

  emptyState.classList.add('d-none');
  container.classList.remove('d-none');
  clearBtn.classList.remove('d-none');
  container.innerHTML = '';

  let equipSubtotal = 0;
  let totalCount = 0;

  cart.forEach((item, idx) => {
    const effectiveHours = item.is_hourly ? Math.max(1, item.hours) : 1;
    const itemSubtotal = item.price * item.qty * effectiveHours;
    equipSubtotal += itemSubtotal;
    totalCount += item.qty;
    const unitTag = item.is_hourly ? '/jam' : '/item';
    const hoursControlHtml = item.is_hourly
      ? `<div class="d-inline-flex align-items-center gap-1 ms-2">
           <input type="number" class="form-control form-control-sm text-center px-1 py-0" style="width:46px;height:24px;font-size:.75rem" min="1" value="${effectiveHours}" onchange="updateHours(${idx}, this.value)" title="Durasi sewa (jam)">
           <span class="text-muted" style="font-size:.72rem">jam</span>
         </div>`
      : `<span class="badge bg-light text-muted border ms-1" style="font-size:.65rem">Per Item</span>`;

    const row = document.createElement('div');
    row.className = 'p-2 mb-2 bg-white rounded-3 border d-flex justify-content-between align-items-center small';
    row.innerHTML = `
      <div class="me-2 flex-grow-1">
        <div class="fw-semibold text-dark d-flex align-items-center flex-wrap">${item.name} ${hoursControlHtml}</div>
        <div class="text-muted" style="font-size:.75rem">@ Rp${item.price.toLocaleString('id-ID')}${unitTag} × ${item.qty}${item.is_hourly && effectiveHours > 1 ? ' × ' + effectiveHours + ' jam' : ''} · <strong class="text-dark">Rp${itemSubtotal.toLocaleString('id-ID')}</strong></div>
        <input type="hidden" name="items[${idx}][equip_id]" value="${item.equip_id}">
        <input type="hidden" name="items[${idx}][name]" value="${item.name}">
        <input type="hidden" name="items[${idx}][price]" value="${item.price}">
        <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">
        <input type="hidden" name="items[${idx}][hours]" value="${effectiveHours}">
      </div>
      <div class="d-flex align-items-center gap-1">
        <button type="button" class="btn btn-light btn-sm btn-icon" style="width:26px;height:26px" onclick="updateQty(${idx}, -1)">-</button>
        <span class="px-2 fw-bold">${item.qty}</span>
        <button type="button" class="btn btn-light btn-sm btn-icon" style="width:26px;height:26px" onclick="updateQty(${idx}, 1)">+</button>
      </div>
    `;
    container.appendChild(row);
  });

  countBadge.innerText = totalCount + ' Item Alat';
  currentEquipSubtotal = equipSubtotal;
  recalcTotals(true);
}

function recalcTotals(autoFillExactCash) {
  const equipSubtotalLabel = document.getElementById('equipSubtotalLabel');
  const unpaidSubtotalLabel = document.getElementById('unpaidSubtotalLabel');
  const grandTotalLabel = document.getElementById('grandTotalLabel');
  const cashInput = document.getElementById('cashReceivedInput');

  const includeBill = includeUnpaidCheck && includeUnpaidCheck.checked ? currentUnpaidBookingRemaining : 0;
  currentGrandTotal = currentEquipSubtotal + includeBill;

  if (equipSubtotalLabel) equipSubtotalLabel.innerText = 'Rp ' + currentEquipSubtotal.toLocaleString('id-ID');
  if (unpaidSubtotalLabel) unpaidSubtotalLabel.innerText = 'Rp ' + includeBill.toLocaleString('id-ID');
  if (grandTotalLabel) grandTotalLabel.innerText = 'Rp ' + currentGrandTotal.toLocaleString('id-ID');

  if (cashInput && autoFillExactCash) {
    cashInput.value = currentGrandTotal > 0 ? currentGrandTotal : '';
  }
  calculateChange();
}

function isCashSelected() {
  const cashRadio = document.getElementById('payCash');
  return cashRadio && cashRadio.checked;
}

function updateSubmitState() {
  const submitBtn = document.getElementById('btnSubmitPos');
  const hasBooking = Boolean(checkoutBookingInput && checkoutBookingInput.value);
  const cashVal = parseInt(document.getElementById('cashReceivedInput')?.value || 0);
  if (!hasBooking || currentGrandTotal <= 0) {
    submitBtn.disabled = true;
    return;
  }
  if (isCashSelected() && cashVal < currentGrandTotal) {
    submitBtn.disabled = true;
    return;
  }
  submitBtn.disabled = false;
}

function calculateChange() {
  const cashInput = document.getElementById('cashReceivedInput');
  const changeLabel = document.getElementById('changeAmountLabel');
  const statusBadge = document.getElementById('changeStatusBadge');

  if (!isCashSelected()) {
    updateSubmitState();
    return;
  }

  const cashGiven = parseInt(cashInput?.value || 0);
  if (currentGrandTotal === 0) {
    changeLabel.innerText = 'Rp 0';
    changeLabel.className = 'fs-5 fw-extrabold font-mono text-muted';
    statusBadge.className = 'lx-pill lx-pill-neutral';
    statusBadge.innerText = 'Pilih Item / Tagihan';
    updateSubmitState();
    return;
  }

  const diff = cashGiven - currentGrandTotal;
  if (diff < 0) {
    changeLabel.innerText = '- Rp ' + Math.abs(diff).toLocaleString('id-ID');
    changeLabel.className = 'fs-5 fw-extrabold font-mono text-danger';
    statusBadge.className = 'lx-pill lx-pill-danger';
    statusBadge.innerText = 'Uang Kurang';
  } else if (diff === 0) {
    changeLabel.innerText = 'Rp 0';
    changeLabel.className = 'fs-5 fw-extrabold font-mono text-success';
    statusBadge.className = 'lx-pill lx-pill-success';
    statusBadge.innerText = 'Uang Pas';
  } else {
    changeLabel.innerText = 'Rp ' + diff.toLocaleString('id-ID');
    changeLabel.className = 'fs-5 fw-extrabold font-mono text-success';
    statusBadge.className = 'lx-pill lx-pill-info';
    statusBadge.innerText = 'Ada Kembalian';
  }
  updateSubmitState();
}

document.addEventListener('DOMContentLoaded', function () {
  const cashInput = document.getElementById('cashReceivedInput');
  const cashBox = document.getElementById('cashCalculationBox');
  const qrisBox = document.getElementById('qrisInfoBox');

  if (cashInput) cashInput.addEventListener('input', calculateChange);

  document.querySelectorAll('.quick-cash-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      if (currentGrandTotal <= 0) return;
      const amt = this.getAttribute('data-amount');
      cashInput.value = amt === 'exact' ? currentGrandTotal : parseInt(amt);
      calculateChange();
    });
  });

  document.querySelectorAll('input[name="payment_method"]').forEach(r => {
    r.addEventListener('change', function () {
      if (this.value === 'cash') {
        cashBox.classList.remove('d-none');
        if (qrisBox) qrisBox.classList.add('d-none');
      } else {
        cashBox.classList.add('d-none');
        if (qrisBox) qrisBox.classList.remove('d-none');
      }
      calculateChange();
    });
  });

  recalcTotals(false);
});
</script>
@endsection
