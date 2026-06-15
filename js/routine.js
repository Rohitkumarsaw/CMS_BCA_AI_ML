(function () {
  'use strict';

  var API = 'routine_handler.php';

  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      background: '#191c24',
      color: '#ffffff',
      iconColor: icon === 'success' ? '#00d25b' : '#ef4444',
      confirmButtonText: 'OK',
      buttonsStyling: false,
      customClass: {
        confirmButton: 'swal2-confirm-ok'
      }
    });
  }

  function postForm(form) {
    var data = new FormData(form);
    return fetch(API, { method: 'POST', body: data }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  }

  // ── Add Task ──
  var addForm = document.getElementById('addTaskForm');
  if (addForm) {
    addForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = addForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(addForm).then(function (res) {
        if (res.status === 'success') {
          return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () {
        return showAlert('error', 'Error', 'Something went wrong.');
      }).then(function () {
        btn.disabled = false;
      });
    });
  }

  // ── Edit Task ──
  var editForm = document.getElementById('editTaskForm');
  if (editForm) {
    editForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = editForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(editForm).then(function (res) {
        if (res.status === 'success') {
          return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () {
        return showAlert('error', 'Error', 'Something went wrong.');
      }).then(function () {
        btn.disabled = false;
      });
    });
  }

  // ── Toggle Task ──
  window.toggleTask = function (id) {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('id', id);
    fetch(API, { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(function (res) {
      if (res.status === 'success') {
        return showAlert('success', 'Updated', res.message).then(function () { location.reload(); });
      }
      return showAlert('error', 'Error', res.message);
    }).catch(function () {
      return showAlert('error', 'Error', 'Something went wrong.');
    });
  };

  // ── Edit (fill form) ──
  window.editTask = function (id) {
    var slot = document.querySelector('.routine-slot[data-id="' + id + '"]');
    if (!slot) return;
    var nameEl = slot.querySelector('.routine-task-title');
    var timeEls = slot.querySelectorAll('.routine-task-time');
    var badgeEl = slot.querySelector('.routine-badge');
    if (!nameEl || !timeEls.length) return;
    var name = nameEl.textContent.trim();
    var timeText = timeEls[0].textContent.trim();
    var parts = timeText.split('—');
    var start = parts[0] ? parts[0].trim() : '';
    var end = parts[1] ? parts[1].trim() : '';
    var category = 'study';
    if (badgeEl) {
      var cat = badgeEl.textContent.trim().toLowerCase();
      if (['study', 'coding', 'fitness', 'break'].indexOf(cat) >= 0) category = cat;
    }
    document.getElementById('editId').value = id;
    document.getElementById('editTaskName').value = name;
    document.getElementById('editStartTime').value = convertTo24hr(start);
    document.getElementById('editEndTime').value = convertTo24hr(end);
    document.getElementById('editCategory').value = category;
    document.getElementById('editFormCard').style.display = 'block';
    document.getElementById('editFormCard').scrollIntoView({ behavior: 'smooth' });
  };

  window.cancelEdit = function () {
    document.getElementById('editFormCard').style.display = 'none';
  };

  // ── Delete Task ──
  window.confirmDelete = function (id) {
    Swal.fire({
      title: 'Delete Task?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, Delete',
      cancelButtonText: '<i class="fas fa-times"></i> Cancel',
      reverseButtons: true,
      background: '#191c24',
      color: '#ffffff',
      backdrop: 'rgba(0,0,0,0.7)',
      buttonsStyling: false,
      customClass: {
        popup: 'swal2-popup',
        confirmButton: 'swal2-confirm-delete',
        cancelButton: 'swal2-cancel'
      }
    }).then(function (result) {
      if (result.isConfirmed) {
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        fetch(API, { method: 'POST', body: fd }).then(function (r) { return r.json(); }).then(function (res) {
          if (res.status === 'success') {
            return showAlert('success', 'Deleted', res.message).then(function () { location.reload(); });
          }
          return showAlert('error', 'Error', res.message);
        }).catch(function () {
          return showAlert('error', 'Error', 'Something went wrong.');
        });
      }
    });
  };

  function convertTo24hr(str) {
    str = str.trim().toUpperCase();
    var match = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/);
    if (!match) return str;
    var h = parseInt(match[1], 10);
    var m = match[2];
    var p = match[3];
    if (p === 'PM' && h !== 12) h += 12;
    if (p === 'AM' && h === 12) h = 0;
    return (h < 10 ? '0' : '') + h + ':' + m;
  }

})();
