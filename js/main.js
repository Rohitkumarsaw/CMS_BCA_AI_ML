/* ===== Global shared utilities — loaded on every page ===== */

(function () {
  'use strict';

  // ── Unified alert for all modules ──
  window.showAlert = function (icon, title, text) {
    return Swal.fire({
      icon: icon, title: title, text: text,
      background: '#191c24', color: '#ffffff',
      iconColor: icon === 'success' ? '#00d25b' : '#fc424a',
      confirmButtonText: 'OK',
      buttonsStyling: false,
      customClass: { confirmButton: 'swal2-confirm-ok' }
    });
  };

  // ── Submit a form element via fetch AJAX ──
  window.postForm = function (form) {
    return fetch(form.action, { method: 'POST', body: new FormData(form) }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  };

  // ── Submit arbitrary FormData to a URL ──
  window.postData = function (url, data) {
    return fetch(url, { method: 'POST', body: data }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  };

})();
