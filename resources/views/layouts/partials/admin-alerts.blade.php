{{-- Lonceng Notifikasi Realtime & Audio Chime untuk Panel Admin --}}
@if(auth()->check() && auth()->user()->is_admin)

<style>
  /* Animasi Goyang Lonceng saat Ada Pesanan Baru */
  @keyframes lxBellSwing {
    0% { transform: rotate(0); }
    15% { transform: rotate(14deg); }
    30% { transform: rotate(-14deg); }
    45% { transform: rotate(10deg); }
    60% { transform: rotate(-10deg); }
    75% { transform: rotate(6deg); }
    100% { transform: rotate(0); }
  }
  .lx-bell-swing {
    animation: lxBellSwing 1s ease-in-out infinite;
    transform-origin: top center;
    color: #f59e0b !important;
  }
  .lx-bell-pulse {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: lxBellPulse 1.8s infinite;
  }
  @keyframes lxBellPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
  }
  .lx-notif-item {
    transition: background 0.15s ease;
    border-radius: 10px;
    padding: 8px 10px;
    text-decoration: none;
    display: block;
  }
  .lx-notif-item:hover {
    background: #f1f5f9;
  }
</style>

{{-- Toast Pop-up Notifikasi Mengambang --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3 d-print-none" style="z-index: 1095;">
  <div id="adminLiveToast" class="toast border-0 shadow-lg rounded-4 overflow-hidden" role="alert" aria-live="assertive" aria-atomic="true" style="background:#064e3b; color:#fff; min-width: 320px;">
    <div class="toast-header bg-dark text-white border-0 py-2.5 px-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-1" style="font-size:0.68rem;">
          <i class="bi bi-bell-fill me-1"></i>PESANAN BARU
        </span>
        <strong class="me-auto small text-truncate" id="toastInvoice">#INV-NEW</strong>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body p-3">
      <div class="d-flex align-items-start gap-2.5 mb-2">
        <div class="rounded-circle p-2 bg-success text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
          <i class="bi bi-receipt fs-6"></i>
        </div>
        <div class="min-w-0 flex-grow-1">
          <div class="fw-bold fs-6 text-white text-truncate" id="toastCustomerName">Pelanggan</div>
          <div class="small opacity-85" id="toastCourtSchedule">Lapangan · Jam</div>
          <div class="fw-extrabold text-warning small mt-1" id="toastPrice">Rp0</div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-2 pt-2 border-top border-secondary border-opacity-50">
        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm fw-bold flex-grow-1 text-dark" id="toastActionBtn">
          <i class="bi bi-check2-circle me-1"></i> Validasi &amp; Approve
        </a>
        <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="toast">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var lastPendingCount = null;
  var lastLatestId = null;
  var lastUpdatedAt = null;
  var isAudioUnlocked = false;
  var audioCtx = null;
  var toastInstance = null;

  // Inisialisasi Audio Context
  function getAudioContext() {
    if (!audioCtx) {
      var AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        audioCtx = new AudioContextClass();
      }
    }
    return audioCtx;
  }

  // Buka kunci AudioContext saat ada interaksi pertama di browser
  function unlockAudio() {
    var ctx = getAudioContext();
    if (ctx && ctx.state === 'suspended') {
      ctx.resume().then(function() {
        isAudioUnlocked = true;
      });
    } else if (ctx && ctx.state === 'running') {
      isAudioUnlocked = true;
    }
  }

  document.addEventListener('click', unlockAudio, { once: true });
  document.addEventListener('keydown', unlockAudio, { once: true });

  // Bunyikan Lonceng Realtime (Ding-Dong Service Bell) menggunakan Web Audio API murni
  window.playLinoBell = function(force) {
    try {
      var ctx = getAudioContext();
      if (!ctx) return;
      if (ctx.state === 'suspended') {
        ctx.resume();
      }

      var now = ctx.currentTime;

      // Fungsi pembuat nada harmonik lonceng
      function triggerTone(freq, startTime, duration, gainLevel) {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, startTime);

        gain.gain.setValueAtTime(0, startTime);
        gain.gain.linearRampToValueAtTime(gainLevel || 0.4, startTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(startTime);
        osc.stop(startTime + duration);
      }

      // Nada 1: E5 (659.25 Hz)
      triggerTone(659.25, now, 0.45, 0.35);
      // Nada 2: A5 (880.00 Hz) - Ding Dong!
      triggerTone(880.00, now + 0.16, 0.85, 0.45);
      // Nada harmonik tinggi pelengkap lonceng
      triggerTone(1760.00, now + 0.18, 0.40, 0.10);
    } catch (e) {
      console.warn('[Lino Bell Audio] Error playing sound:', e);
    }
  };

  // Tampilkan Toast Pop-up Notifikasi
  function showLiveToast(booking) {
    var toastEl = document.getElementById('adminLiveToast');
    if (!toastEl) return;

    if (!toastInstance && window.bootstrap && window.bootstrap.Toast) {
      toastInstance = new bootstrap.Toast(toastEl, { delay: 10000 });
    }

    if (booking) {
      var invEl = document.getElementById('toastInvoice');
      if (invEl) invEl.textContent = booking.invoice;

      var custEl = document.getElementById('toastCustomerName');
      if (custEl) custEl.textContent = booking.customer_name + ' (' + booking.customer_phone + ')';

      var courtEl = document.getElementById('toastCourtSchedule');
      if (courtEl) courtEl.textContent = booking.court_name + ' · ' + booking.schedule_text;

      var priceEl = document.getElementById('toastPrice');
      if (priceEl) priceEl.textContent = booking.nominal + ' (' + booking.payment_method + ')';

      var actEl = document.getElementById('toastActionBtn');
      if (actEl && booking.detail_url) actEl.href = booking.detail_url;
    }

    if (toastInstance) {
      toastInstance.show();
    }
  }

  // Update Tampilan Lonceng di Topbar
  function updateBellUI(data) {
    var count = data.pending_count || 0;
    var badge = document.getElementById('adminBellBadge');
    var icon = document.getElementById('adminBellIcon');
    var list = document.getElementById('adminBellList');

    if (badge) {
      if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('d-none');
        badge.classList.add('lx-bell-pulse');
      } else {
        badge.textContent = '0';
        badge.classList.add('d-none');
        badge.classList.remove('lx-bell-pulse');
      }
    }

    var sidebarBadge = document.getElementById('sidebarPendingBadge');
    if (sidebarBadge) {
      if (count > 0) {
        sidebarBadge.textContent = count > 99 ? '99+' : count;
        sidebarBadge.classList.remove('d-none');
        sidebarBadge.classList.add('lx-bell-pulse');
      } else {
        sidebarBadge.textContent = '0';
        sidebarBadge.classList.add('d-none');
        sidebarBadge.classList.remove('lx-bell-pulse');
      }
    }

    if (icon) {
      if (count > 0) {
        icon.className = 'bi bi-bell-fill fs-6 text-warning lx-bell-swing';
      } else {
        icon.className = 'bi bi-bell fs-6';
      }
    }

    if (list && Array.isArray(data.items)) {
      if (data.items.length === 0) {
        list.innerHTML = '<div class="text-center py-4 text-muted small"><i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>Semua booking telah di-approve</div>';
      } else {
        var html = '';
        data.items.forEach(function(item) {
          html += '<a href="' + item.detail_url + '" class="lx-notif-item border-bottom mb-1">'
                + '  <div class="d-flex justify-content-between align-items-center">'
                + '    <strong class="text-dark small font-mono">' + item.invoice + '</strong>'
                + '    <span class="badge bg-warning text-dark" style="font-size:0.6rem;">' + (item.has_proof ? 'Bukti QRIS Ada' : 'Belum Bukti') + '</span>'
                + '  </div>'
                + '  <div class="fw-semibold text-dark small text-truncate">' + item.customer_name + '</div>'
                + '  <div class="text-muted d-flex justify-content-between align-items-center" style="font-size:0.7rem;">'
                + '    <span>' + item.court_name + ' · ' + item.schedule_text + '</span>'
                + '    <span class="fw-bold text-success">' + item.nominal + '</span>'
                + '  </div>'
                + '</a>';
        });
        list.innerHTML = html;
      }
    }
  }

  // Polling Real-time ke API Pending Alerts setiap 5 detik
  function checkPendingAlerts() {
    var url = '{{ route("admin.api.pending-alerts") }}';
    fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function(res) {
      if (!res.ok) throw new Error('Network error');
      return res.json();
    })
    .then(function(data) {
      if (!data || data.status !== 'ok') return;

      var currentCount = data.pending_count || 0;
      var currentLatestId = data.latest_id || 0;
      var currentUpdatedAt = data.latest_updated_at || 0;

      // Cek apakah ada booking baru atau bukti bayar yang baru diunggah
      var isFirstLoad = (lastPendingCount === null);
      var hasIncreased = !isFirstLoad && (currentCount > lastPendingCount);
      var hasNewBooking = !isFirstLoad && (currentLatestId > (lastLatestId || 0));
      var hasUpdatedProof = !isFirstLoad && (data.has_new_proof && currentUpdatedAt > (lastUpdatedAt || 0));

      if (hasIncreased || hasNewBooking || hasUpdatedProof) {
        // 1. Bunyikan lonceng audio
        window.playLinoBell();

        // 2. Tampilkan Toast Notifikasi
        showLiveToast(data.latest_booking);

        // 3. Pancarkan event agar halaman seperti Daftar Booking dapat auto-update tabelnya
        window.dispatchEvent(new CustomEvent('lino:pending-changed', { detail: data }));
      }

      // Update state
      lastPendingCount = currentCount;
      lastLatestId = currentLatestId;
      lastUpdatedAt = currentUpdatedAt;

      // Update UI Lonceng Topbar
      updateBellUI(data);
    })
    .catch(function(err) {
      // Diamkan error jaringan polling agar tidak mengganggu pengguna
    });
  }

  // Mulai polling saat DOM siap
  document.addEventListener('DOMContentLoaded', function() {
    checkPendingAlerts();
    // Poll berkala setiap 5 detik
    setInterval(checkPendingAlerts, 5000);
  });
})();
</script>
@endif
