(function () {
  'use strict';

  var page = 1;
  var loading = false;

  function getIconClass(action) {
    var a = (action || '').toLowerCase();
    if (a.indexOf('create') !== -1 || a.indexOf('add') !== -1) return 'created';
    if (a.indexOf('update') !== -1 || a.indexOf('edit') !== -1) return 'updated';
    if (a.indexOf('delet') !== -1 || a.indexOf('purge') !== -1) return 'deleted';
    if (a.indexOf('restore') !== -1) return 'restored';
    if (a.indexOf('toggle') !== -1 || a.indexOf('status') !== -1) return 'updated';
    return 'default';
  }

  function getIcon(action) {
    var a = (action || '').toLowerCase();
    if (a.indexOf('create') !== -1 || a.indexOf('add') !== -1) return 'fa-plus-circle';
    if (a.indexOf('update') !== -1 || a.indexOf('edit') !== -1) return 'fa-pen';
    if (a.indexOf('delet') !== -1 || a.indexOf('purge') !== -1) return 'fa-trash-alt';
    if (a.indexOf('restore') !== -1) return 'fa-undo-alt';
    if (a.indexOf('toggle') !== -1 || a.indexOf('status') !== -1) return 'fa-toggle-on';
    return 'fa-circle';
  }

  function renderLog(log) {
    var div = document.createElement('div');
    div.className = 'history-log';
    var iconClass = getIconClass(log.action_type);
    div.innerHTML =
      '<div class="history-log-icon ' + iconClass + '"><i class="fas ' + getIcon(log.action_type) + '"></i></div>' +
      '<div class="history-log-body">' +
        '<div class="history-log-action">' + escapeHtml(log.action_type) + '</div>' +
        '<div class="history-log-section"><span>' + escapeHtml(log.section_name) + '</span></div>' +
        (log.details ? '<div style="font-size:0.72rem;color:#8f94a8;margin-top:2px;">' + escapeHtml(log.details) + '</div>' : '') +
      '</div>' +
      '<div class="history-log-time">' + formatTime(log.logged_at) + '</div>';
    return div;
  }

  function escapeHtml(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }

  function formatTime(t) {
    if (!t) return '';
    var d = new Date(t.replace(' ', 'T'));
    var now = new Date();
    var diff = now - d;
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  window.loadMore = function () {
    if (loading) return;
    loading = true;
    var loader = document.getElementById('historyLoader');
    if (loader) loader.style.display = 'block';
    page++;

    fetch('history_handler.php?page=' + page)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (loader) loader.style.display = 'none';
        loading = false;
        if (res.status !== 'success') return;
        var list = document.getElementById('historyList');
        var empty = list.querySelector('.history-empty');
        if (empty) empty.style.display = 'none';
        res.logs.forEach(function (log) {
          list.appendChild(renderLog(log));
        });
        var btn = document.getElementById('loadMoreBtn');
        if (btn) btn.style.display = res.hasMore ? 'inline-flex' : 'none';
      })
      .catch(function () { if (loader) loader.style.display = 'none'; loading = false; });
  };

  // Initial load
  var list = document.getElementById('historyList');
  if (list) {
    fetch('history_handler.php?page=1')
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.status !== 'success' || res.logs.length === 0) return;
        list.innerHTML = '';
        res.logs.forEach(function (log) {
          list.appendChild(renderLog(log));
        });
        var btn = document.getElementById('loadMoreBtn');
        if (btn) btn.style.display = res.hasMore ? 'inline-flex' : 'none';
      });
  }
})();