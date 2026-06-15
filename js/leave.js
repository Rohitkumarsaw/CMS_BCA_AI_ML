(function () {
  'use strict';

  var API = 'leave_handler.php';

  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon, title: title, text: text,
      background: '#191c24', color: '#ffffff',
      iconColor: icon === 'success' ? '#00d25b' : '#fc424a',
      confirmButtonText: 'OK',
      buttonsStyling: false,
      customClass: { confirmButton: 'swal2-confirm-ok' }
    });
  }

  function postForm(form) {
    return fetch(API, { method: 'POST', body: new FormData(form) }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  }

  function postData(data) {
    return fetch(API, { method: 'POST', body: data }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  }

  // Apply leave form
  var leaveForm = document.getElementById('leaveForm');
  if (leaveForm) {
    leaveForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = leaveForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(leaveForm).then(function (res) {
        if (res.status === 'success') {
          return showAlert('success', 'Applied', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); })
      .then(function () { btn.disabled = false; });
    });
  }

  // Edit leave
  var leaveEditForm = document.getElementById('leaveEditForm');
  if (leaveEditForm) {
    leaveEditForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = leaveEditForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(leaveEditForm).then(function (res) {
        if (res.status === 'success') {
          bootstrap.Modal.getInstance(document.getElementById('leaveEditModal')).hide();
          return showAlert('success', 'Updated', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); })
      .then(function () { btn.disabled = false; });
    });
  }

  window.editLeave = function (id) {
    var item = document.querySelector('.leave-item[data-id="' + id + '"]');
    if (!item) return;
    document.getElementById('edit_leave_id').value = id;
    document.getElementById('edit_leave_subject').value = item.dataset.subject;
    document.getElementById('edit_leave_start').value = item.dataset.start;
    document.getElementById('edit_leave_end').value = item.dataset.end;
    document.getElementById('edit_leave_reason').value = item.dataset.reason;
    new bootstrap.Modal(document.getElementById('leaveEditModal')).show();
  };

  // Approve leave
  window.confirmApproveLeave = function (id) {
    Swal.fire({
      title: 'Approve Leave?',
      text: 'This will mark the leave as approved.',
      icon: 'question', iconColor: '#10b981',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-check"></i> Yes, Approve',
      cancelButtonText: '<i class="fas fa-arrow-left"></i> Go Back',
      reverseButtons: true,
      background: '#191c24', color: '#ffffff',
      backdrop: 'rgba(0,0,0,0.7)',
      buttonsStyling: false,
      customClass: { popup: 'swal2-popup', confirmButton: 'swal2-confirm-ok', cancelButton: 'swal2-cancel' }
    }).then(function (result) {
      if (result.isConfirmed) {
        var fd = new FormData();
        fd.append('action', 'approve');
        fd.append('id', id);
        postData(fd).then(function (res) {
          if (res.status === 'success') {
            return showAlert('success', 'Approved', res.message).then(function () { location.reload(); });
          }
          return showAlert('error', 'Error', res.message);
        }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); });
      }
    });
  };

  // Reject leave with remark prompt
  window.confirmRejectLeave = function (id) {
    Swal.fire({
      title: 'Reject Leave?',
      input: 'textarea',
      inputLabel: 'Admin Remark (reason for rejection)',
      inputPlaceholder: 'Enter remark...',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-times"></i> Yes, Reject',
      cancelButtonText: '<i class="fas fa-arrow-left"></i> Go Back',
      reverseButtons: true,
      inputValidator: function (value) { if (!value) return 'Remark is required.'; },
      background: '#191c24', color: '#ffffff',
      backdrop: 'rgba(0,0,0,0.7)',
      buttonsStyling: false,
      customClass: { popup: 'swal2-popup', confirmButton: 'swal2-confirm-delete', cancelButton: 'swal2-cancel' }
    }).then(function (result) {
      if (result.isConfirmed) {
        var fd = new FormData();
        fd.append('action', 'reject');
        fd.append('id', id);
        fd.append('remark', result.value);
        postData(fd).then(function (res) {
          if (res.status === 'success') {
            return showAlert('success', 'Rejected', res.message).then(function () { location.reload(); });
          }
          return showAlert('error', 'Error', res.message);
        }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); });
      }
    });
  };

  // Cancel leave
  window.confirmCancelLeave = function (id) {
    Swal.fire({
      title: 'Cancel Leave?',
      text: 'This will cancel your pending leave application.',
      icon: 'warning', iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-times"></i> Yes, Cancel',
      cancelButtonText: '<i class="fas fa-arrow-left"></i> Go Back',
      reverseButtons: true,
      background: '#191c24', color: '#ffffff',
      backdrop: 'rgba(0,0,0,0.7)',
      buttonsStyling: false,
      customClass: { popup: 'swal2-popup', confirmButton: 'swal2-confirm-delete', cancelButton: 'swal2-cancel' }
    }).then(function (result) {
      if (result.isConfirmed) {
        var fd = new FormData();
        fd.append('action', 'cancel');
        fd.append('id', id);
        postData(fd).then(function (res) {
          if (res.status === 'success') {
            return showAlert('success', 'Cancelled', res.message).then(function () { location.reload(); });
          }
          return showAlert('error', 'Error', res.message);
        }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); });
      }
    });
  };

})();
