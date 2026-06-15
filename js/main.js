/* ===== Global shared utilities — loaded on every page ===== */

(function () {
  'use strict';

  // ── Sidebar toggle (called from inline onclick) ──
  window.toggleSidebar = function () {
    var s = document.getElementById('sidebar');
    var o = document.getElementById('sidebarOverlay');
    if (s) s.classList.toggle('show');
    if (o) o.classList.toggle('show');
  };

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

  // ── Global delete confirmation via data-confirm attribute ──
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (!el) return;
    var label = el.dataset.confirm || 'this item';
    var href = el.getAttribute('href');
    if (!href) return;

    e.preventDefault();
    e.stopPropagation();

    Swal.fire({
      title: 'Are you sure?',
      text: 'Do you want to delete ' + label + '?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      cancelButtonText: 'Cancel',
      background: '#191c24',
      color: '#ffffff',
      buttonsStyling: false,
      customClass: { confirmButton: 'swal2-confirm-delete', cancelButton: 'swal2-cancel' }
    }).then(function (result) {
      if (result.isConfirmed) {
        window.location.href = href;
      }
    });
  });

  // ── Per‑module instant section search ──
  window.initSectionSearch = function () {
    document.querySelectorAll('.custom-section-search').forEach(function (input) {
      input.addEventListener('input', function () {
        var selector = this.dataset.target;
        if (!selector) return;
        var target = document.querySelector(selector);
        if (!target) return;
        var filter = this.value.toLowerCase().trim();

        // Determine what to filter — direct children only
        var items;
        if (target.tagName === 'TBODY') {
          items = target.children;
        } else if (target.tagName === 'TABLE') {
          items = target.querySelectorAll('tbody > tr');
        } else {
          items = target.children;
        }

        Array.from(items).forEach(function (item) {
          if (item.matches && item.matches('tr, [class*="card"], [class*="item"], [class*="task"], [class*="event"], [class*="tile"], li, [class*="col-"]')) {
            var text = item.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? '' : 'none';
          }
        });
      });
    });
  };

  // Auto‑init once DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initSectionSearch);
  } else {
    window.initSectionSearch();
  }
})();
