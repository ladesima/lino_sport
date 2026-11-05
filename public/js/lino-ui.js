/* ==========================================================================
   Lino UI — Interaksi global (toast, konfirmasi, loading state)
   ========================================================================== */
(function () {
  'use strict';

  var ICONS = {
    success: 'bi-check-lg',
    error: 'bi-exclamation-octagon',
    warning: 'bi-exclamation-triangle',
    info: 'bi-info-lg'
  };
  var TITLES = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Info' };

  function stack() {
    var el = document.querySelector('.lx-toast-stack');
    if (!el) {
      el = document.createElement('div');
      el.className = 'lx-toast-stack';
      el.setAttribute('aria-live', 'polite');
      document.body.appendChild(el);
    }
    return el;
  }

  function dismiss(toast) {
    if (!toast || toast.classList.contains('hide')) return;
    toast.classList.add('hide');
    setTimeout(function () { toast.remove(); }, 220);
  }

  function arm(toast) {
    var btn = toast.querySelector('.lx-toast-close');
    if (btn) btn.addEventListener('click', function () { dismiss(toast); });
    var type = toast.getAttribute('data-type');
    // Error & warning bertahan lebih lama agar sempat dibaca
    var ms = (type === 'error' || type === 'warning') ? 9000 : 5000;
    var timer = setTimeout(function () { dismiss(toast); }, ms);
    toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
    toast.addEventListener('mouseleave', function () { timer = setTimeout(function () { dismiss(toast); }, 2500); });
  }

  /** API publik: LxToast('success', 'Pesan', 'Judul opsional') */
  window.LxToast = function (type, message, title) {
    type = ICONS[type] ? type : 'info';
    var t = document.createElement('div');
    t.className = 'lx-toast lx-toast-' + type;
    t.setAttribute('role', 'status');
    t.setAttribute('data-type', type);
    t.innerHTML =
      '<span class="lx-toast-icon"><i class="bi ' + ICONS[type] + '"></i></span>' +
      '<div class="flex-grow-1"><div class="lx-toast-title"></div><div class="lx-toast-msg"></div></div>' +
      '<button type="button" class="lx-toast-close" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>';
    t.querySelector('.lx-toast-title').textContent = title || TITLES[type];
    t.querySelector('.lx-toast-msg').textContent = message;
    stack().appendChild(t);
    arm(t);
  };

  /* ---------- Dialog konfirmasi (pengganti confirm() bawaan) ---------- */
  var confirmModal, confirmResolve;
  function ensureConfirmModal() {
    if (confirmModal) return confirmModal;
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="modal fade" id="lxConfirm" tabindex="-1" aria-hidden="true">' +
      '  <div class="modal-dialog modal-dialog-centered" style="max-width:420px">' +
      '    <div class="modal-content">' +
      '      <div class="modal-body p-4">' +
      '        <div class="d-flex gap-3">' +
      '          <span class="lx-toast-icon lx-confirm-icon" style="width:40px;height:40px;font-size:1.1rem"></span>' +
      '          <div><h6 class="fw-bold mb-1 lx-confirm-title">Konfirmasi</h6><p class="text-muted mb-0 small lx-confirm-msg"></p></div>' +
      '        </div>' +
      '      </div>' +
      '      <div class="modal-footer border-0 pt-0 px-4 pb-4">' +
      '        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>' +
      '        <button type="button" class="btn lx-confirm-ok">Ya, lanjutkan</button>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>';
    document.body.appendChild(wrap.firstChild);
    var el = document.getElementById('lxConfirm');
    confirmModal = { el: el, bs: new bootstrap.Modal(el) };
    el.querySelector('.lx-confirm-ok').addEventListener('click', function () {
      var r = confirmResolve; confirmResolve = null;
      confirmModal.bs.hide();
      if (r) r(true);
    });
    el.addEventListener('hidden.bs.modal', function () {
      if (confirmResolve) { var r = confirmResolve; confirmResolve = null; r(false); }
    });
    return confirmModal;
  }

  window.LxConfirm = function (opts) {
    return new Promise(function (resolve) {
      if (typeof bootstrap === 'undefined') { resolve(window.confirm(opts.message)); return; }
      var m = ensureConfirmModal();
      var danger = opts.variant === 'danger';
      m.el.querySelector('.lx-confirm-title').textContent = opts.title || 'Konfirmasi';
      m.el.querySelector('.lx-confirm-msg').textContent = opts.message || 'Lanjutkan tindakan ini?';
      var ok = m.el.querySelector('.lx-confirm-ok');
      ok.textContent = opts.ok || (danger ? 'Ya, hapus' : 'Ya, lanjutkan');
      ok.className = 'btn lx-confirm-ok ' + (danger ? 'btn-danger' : 'btn-primary');
      var icon = m.el.querySelector('.lx-confirm-icon');
      icon.className = 'lx-toast-icon lx-confirm-icon ' + (danger ? 'lx-toast-error' : 'lx-toast-info');
      icon.style.background = danger ? '#fef2f2' : '#ecfdf5';
      icon.style.color = danger ? '#dc2626' : '#059669';
      icon.innerHTML = '<i class="bi ' + (danger ? 'bi-trash3' : 'bi-question-lg') + '"></i>';
      confirmResolve = resolve;
      m.bs.show();
    });
  };

  /* ---------- Loading state & anti double-submit ---------- */
  function setLoading(form, submitter) {
    if (form.hasAttribute('data-no-loading') || form.target === '_blank' || (form.method || '').toLowerCase() === 'get') return;
    // Pertahankan name/value tombol submit (mis. pay_cash=1) karena tombol yang di-disable tidak ikut terkirim
    if (submitter && submitter.name && !form.querySelector('input[type=hidden][data-lx-submitter]')) {
      var h = document.createElement('input');
      h.type = 'hidden'; h.name = submitter.name; h.value = submitter.value; h.setAttribute('data-lx-submitter', '');
      form.appendChild(h);
    }
    setTimeout(function () {
      form.querySelectorAll('button[type=submit], button:not([type]), input[type=submit]').forEach(function (b) {
        b.disabled = true;
      });
      if (submitter && submitter.tagName === 'BUTTON') {
        submitter.classList.add('is-loading');
        submitter.setAttribute('data-lx-html', submitter.innerHTML);
        submitter.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
          (submitter.getAttribute('data-loading-text') || 'Memproses...');
      }
    }, 0);
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (e.defaultPrevented) return; // dibatalkan oleh onsubmit="return confirm(...)" lama
    var msg = form.getAttribute('data-confirm');
    if (msg && !form.__lxConfirmed) {
      e.preventDefault();
      var submitter = e.submitter;
      LxConfirm({
        message: msg,
        title: form.getAttribute('data-confirm-title'),
        ok: form.getAttribute('data-confirm-ok'),
        variant: form.getAttribute('data-confirm-variant')
      }).then(function (yes) {
        if (!yes) return;
        form.__lxConfirmed = true;
        if (submitter && submitter.name) {
          var h = document.createElement('input');
          h.type = 'hidden'; h.name = submitter.name; h.value = submitter.value; h.setAttribute('data-lx-submitter', '');
          form.appendChild(h);
        }
        setLoading(form, submitter);
        HTMLFormElement.prototype.submit.call(form);
      });
      return;
    }
    setLoading(form, e.submitter);
  });

  // Kembali via tombol back browser: pulihkan tombol
  window.addEventListener('pageshow', function (ev) {
    if (!ev.persisted) return;
    document.querySelectorAll('.is-loading').forEach(function (b) {
      b.classList.remove('is-loading');
      if (b.hasAttribute('data-lx-html')) b.innerHTML = b.getAttribute('data-lx-html');
    });
    document.querySelectorAll('button[disabled], input[type=submit][disabled]').forEach(function (b) { b.disabled = false; });
    document.querySelectorAll('[data-lx-submitter]').forEach(function (h) { h.remove(); });
  });

  /* ---------- Baris tabel yang bisa diklik ---------- */
  document.addEventListener('click', function (e) {
    var row = e.target.closest('[data-href]');
    if (!row || e.target.closest('a, button, form, input, select, label')) return;
    window.location = row.getAttribute('data-href');
  });

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.lx-toast').forEach(arm);
  });
})();
