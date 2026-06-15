(function () {
  'use strict';

  var API = 'meetings_handler.php';

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

  // Update platform counts
  var cards = document.querySelectorAll('.meeting-card');
  var counts = { zoom: 0, google_meet: 0, microsoft_teams: 0, other: 0 };
  cards.forEach(function (c) {
    var p = c.dataset.platform;
    if (counts[p] !== undefined) counts[p]++;
  });
  document.getElementById('zoomCount').textContent = counts.zoom;
  document.getElementById('meetCount').textContent = counts.google_meet;
  document.getElementById('teamsCount').textContent = counts.microsoft_teams;
  document.getElementById('otherCount').textContent = counts.other;

  // Open modal for add
  document.getElementById('addMeetingBtn').addEventListener('click', function () {
    document.getElementById('meetingFormTitle').textContent = 'Add Meeting Link';
    document.getElementById('meeting_submit_btn').innerHTML = '<i class="fas fa-save"></i> Save';
    document.getElementById('meetingForm').reset();
    document.getElementById('meeting_id').value = '';
    document.getElementById('meeting_action').value = 'add';
    new bootstrap.Modal(document.getElementById('meetingModal')).show();
  });

  // Submit form
  var meetingForm = document.getElementById('meetingForm');
  meetingForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = meetingForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    postForm(meetingForm).then(function (res) {
      if (res.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('meetingModal')).hide();
        return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
      }
      return showAlert('error', 'Error', res.message);
    }).catch(function () {
      return showAlert('error', 'Error', 'Something went wrong.');
    }).then(function () { btn.disabled = false; });
  });

  // Edit
  window.editMeeting = function (id) {
    var card = document.querySelector('.meeting-card[data-id="' + id + '"]');
    if (!card) return;
    document.getElementById('meeting_action').value = 'edit';
    document.getElementById('meeting_id').value = id;
    document.getElementById('meetingFormTitle').textContent = 'Edit Meeting Link';
    document.getElementById('meeting_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    document.getElementById('meeting_title').value = card.querySelector('.meeting-title').textContent.trim();
    document.getElementById('meeting_platform').value = card.dataset.platform;
    var urlEl = card.querySelector('.meeting-url');
    document.getElementById('meeting_url').value = urlEl ? urlEl.getAttribute('href') : '';
    var descEl = card.querySelector('.meeting-desc');
    document.getElementById('meeting_desc').value = descEl ? descEl.innerHTML.replace(/<br\s*\/?>/gi, '\n') : '';
    document.getElementById('meeting_date').value = '';
    new bootstrap.Modal(document.getElementById('meetingModal')).show();
  };

  // Delete
  window.confirmDeleteMeeting = function (id) {
    Swal.fire({
      title: 'Remove Link?',
      text: 'This will remove the meeting link permanently.',
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
