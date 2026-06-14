(function () {
  'use strict';

  function toggleSidebar() {
    var s = document.getElementById('sidebar');
    var o = document.getElementById('sidebarOverlay');
    if (s) s.classList.toggle('show');
    if (o) o.classList.toggle('show');
  }

  function closeSidebar() {
    var s = document.getElementById('sidebar');
    var o = document.getElementById('sidebarOverlay');
    if (s) s.classList.remove('show');
    if (o) o.classList.remove('show');
  }

  window.toggleSidebar = toggleSidebar;
  window.closeSidebar = closeSidebar;

  var _deleteUrl = '';

  function showDeleteConfirm(label, url) {
    _deleteUrl = url;
    var overlay = document.getElementById('confirmDeleteOverlay');
    document.getElementById('confirmTitle').textContent = 'Delete ' + label + '?';
    overlay.classList.add('show');
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn) {
      e.preventDefault();
      showDeleteConfirm(btn.getAttribute('data-confirm'), btn.getAttribute('href'));
    }
  });

  document.addEventListener('click', function (e) {
    if (e.target.id === 'confirmDeleteBtn' && _deleteUrl) {
      window.location.href = _deleteUrl;
    }
    if (e.target.id === 'confirmCancelBtn' || e.target.classList.contains('confirm-overlay')) {
      document.getElementById('confirmDeleteOverlay').classList.remove('show');
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var o = document.getElementById('confirmDeleteOverlay');
      if (o && o.classList.contains('show')) o.classList.remove('show');
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    var overlay = document.getElementById('sidebarOverlay');
    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.querySelectorAll('.table-clickable tbody tr').forEach(function (row) {
      row.style.cursor = 'pointer';
      row.addEventListener('click', function () {
        var link = this.getAttribute('data-href');
        if (link) window.location.href = link;
      });
    });
  });
})();
