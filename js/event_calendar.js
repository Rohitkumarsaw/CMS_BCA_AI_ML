(function () {
  'use strict';

  function updateClock() {
    var now = new Date();
    var h = String(now.getHours()).padStart(2, '0');
    var m = String(now.getMinutes()).padStart(2, '0');
    var s = String(now.getSeconds()).padStart(2, '0');
    var el = document.getElementById('ecClockTime');
    if (el) el.textContent = h + ':' + m + ':' + s;

    var dateEl = document.getElementById('ecClockDate');
    if (dateEl) {
      var opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      dateEl.textContent = now.toLocaleDateString('en-US', opts);
    }
  }
  updateClock();
  setInterval(updateClock, 1000);

  window.openScheduler = function (day, month, year) {
    cancelEdit();
    var dateInput = document.getElementById('ecInputDate');
    if (dateInput) {
      var mm = String(month).padStart(2, '0');
      var dd = String(day).padStart(2, '0');
      dateInput.value = year + '-' + mm + '-' + dd;
    }
    var titleInput = document.getElementById('ecInputTitle');
    var formTitle = document.getElementById('ecFormTitle');
    if (titleInput && formTitle) {
      titleInput.value = '';
      formTitle.textContent = 'Add Event';
    }
    var card = document.getElementById('ecFormCard');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  function cancelEdit() {
    var el;
    el = document.getElementById('ecFormAction'); if (el) el.value = 'add';
    el = document.getElementById('ecInputEventId'); if (el) el.value = '';
    el = document.getElementById('ecInputTitle'); if (el) el.value = '';
    el = document.getElementById('ecInputDate'); if (el) el.value = '';
    el = document.getElementById('ecInputTime'); if (el) el.value = '';
    el = document.getElementById('ecInputType'); if (el) el.value = 'Exam';
    el = document.getElementById('ecFormTitle'); if (el) el.textContent = 'Add Event';
    el = document.getElementById('ecSubmitText'); if (el) el.textContent = 'Save Event';
    el = document.getElementById('ecCancelEdit'); if (el) el.style.display = 'none';
  }

  function getCSRFToken() {
    var input = document.querySelector('input[name="csrf_token"]');
    return input ? input.value : '';
  }

  function setFormBusy(busy) {
    var btn = document.getElementById('ecFormSubmit');
    if (btn) btn.disabled = busy;
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Edit event buttons
    document.querySelectorAll('.ec-edit-event').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id');
        if (!id) return;
        var formData = new FormData();
        formData.append('action', 'get');
        formData.append('id', id);
        formData.append('csrf_token', getCSRFToken());
        fetch('event_handler.php', { method: 'POST', body: formData })
          .then(function (r) { return r.json(); })
          .then(function (ev) {
            if (!ev || ev.status === 'error') {
              Swal.fire({ icon: 'error', title: 'Error', text: ev ? ev.message : 'Invalid response', background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
              return;
            }
            document.getElementById('ecFormAction').value = 'update';
            document.getElementById('ecInputEventId').value = ev.id;
            document.getElementById('ecInputTitle').value = ev.event_name || '';
            document.getElementById('ecInputDate').value = ev.date || '';
            document.getElementById('ecInputTime').value = ev.time || '';
            document.getElementById('ecInputType').value = ev.type || 'Exam';
            document.getElementById('ecFormTitle').textContent = 'Edit Event';
            document.getElementById('ecSubmitText').textContent = 'Update Event';
            document.getElementById('ecCancelEdit').style.display = 'inline-block';
            document.getElementById('ecFormCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
          })
          .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load event', background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
          });
      });
    });

    // Cancel edit button
    var cancelBtn = document.getElementById('ecCancelEdit');
    if (cancelBtn) cancelBtn.addEventListener('click', cancelEdit);

    // Delete event buttons — SweetAlert2
    document.querySelectorAll('.ec-delete-event').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.getAttribute('data-id');
        var title = this.getAttribute('data-title') || 'this event';
        if (!id) return;
        Swal.fire({
          title: 'Delete Event?',
          text: 'Are you sure you want to delete "' + title + '"?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel',
          background: '#191c24',
          color: '#ffffff',
          buttonsStyling: false,
          customClass: { confirmButton: 'swal2-confirm-delete', cancelButton: 'swal2-cancel' }
        }).then(function (result) {
          if (!result.isConfirmed) return;
          var fd = new FormData();
          fd.append('action', 'delete_json');
          fd.append('id', id);
          fd.append('csrf_token', getCSRFToken());
          fetch('event_handler.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' }, timer: 1500, showConfirmButton: false });
                var item = btn.closest('.ec-event-item');
                if (item) item.remove();
              } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
              }
            })
            .catch(function () {
              Swal.fire({ icon: 'error', title: 'Error', text: 'Server error', background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
            });
        });
      });
    });
  });
})();
