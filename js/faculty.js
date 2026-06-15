(function () {
  'use strict';

  var API = 'faculty_handler.php';

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

  // Open modal for add
  var addBtn = document.getElementById('addFacultyBtn');
  if (addBtn) {
    addBtn.addEventListener('click', function () {
      document.getElementById('facultyFormTitle').textContent = 'Add Faculty';
      document.getElementById('faculty_submit_btn').innerHTML = '<i class="fas fa-save"></i> Save';
      document.getElementById('facultyForm').reset();
      document.getElementById('faculty_id').value = '';
      document.getElementById('faculty_action').value = 'add';
      var modal = new bootstrap.Modal(document.getElementById('facultyModal'));
      modal.show();
    });
  }

  // Submit form
  var facultyForm = document.getElementById('facultyForm');
  if (facultyForm) {
    facultyForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = facultyForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(facultyForm).then(function (res) {
        if (res.status === 'success') {
          bootstrap.Modal.getInstance(document.getElementById('facultyModal')).hide();
          return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () {
        return showAlert('error', 'Error', 'Something went wrong.');
      }).then(function () { btn.disabled = false; });
    });
  }

  // Edit
  window.editFaculty = function (id) {
    var card = document.querySelector('.faculty-card[data-id="' + id + '"]');
    if (!card) return;

    document.getElementById('faculty_action').value = 'edit';
    document.getElementById('faculty_id').value = id;
    document.getElementById('facultyFormTitle').textContent = 'Edit Faculty';
    document.getElementById('faculty_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    document.getElementById('faculty_name').value = card.querySelector('.faculty-name').textContent.trim();
    document.getElementById('faculty_department').value = card.querySelector('.faculty-dept') ? card.querySelector('.faculty-dept').textContent.trim() : '';
    document.getElementById('faculty_subjects').value = card.querySelector('.faculty-subjects') ? card.querySelector('.faculty-subjects').textContent.trim() : '';
    var emailEl = card.querySelector('.faculty-contact a[href^="mailto:"]');
    document.getElementById('faculty_email').value = emailEl ? emailEl.textContent.trim() : '';
    var phoneEl = card.querySelector('.faculty-contact a[href^="tel:"]');
    document.getElementById('faculty_phone').value = phoneEl ? phoneEl.textContent.trim() : '';
    new bootstrap.Modal(document.getElementById('facultyModal')).show();
  };

  // Delete
  window.confirmDeleteFaculty = function (id) {
    Swal.fire({
      title: 'Remove Faculty?',
      text: 'This will remove the faculty member permanently.',
      icon: 'warning', iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, Remove',
      cancelButtonText: '<i class="fas fa-times"></i> Cancel',
      reverseButtons: true,
      background: '#191c24', color: '#ffffff',
      backdrop: 'rgba(0,0,0,0.7)',
      buttonsStyling: false,
      customClass: { popup: 'swal2-popup', confirmButton: 'swal2-confirm-delete', cancelButton: 'swal2-cancel' }
    }).then(function (result) {
      if (result.isConfirmed) {
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        postData(fd).then(function (res) {
          if (res.status === 'success') {
            return showAlert('success', 'Deleted', res.message).then(function () { location.reload(); });
          }
          return showAlert('error', 'Error', res.message);
        }).catch(function () { return showAlert('error', 'Error', 'Something went wrong.'); });
      }
    });
  };

})();
