(function () {
  'use strict';

  var API = 'placement_handler.php';

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

  // Status filter
  var filters = document.querySelectorAll('.placement-filter');
  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (f) { f.classList.remove('active'); });
      btn.classList.add('active');
      var filter = btn.dataset.filter;
      document.querySelectorAll('.placement-card').forEach(function (card) {
        card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
      });
    });
  });

  // Open modal for add
  document.getElementById('addPlacementBtn').addEventListener('click', function () {
    document.getElementById('placementFormTitle').textContent = 'Add Application';
    document.getElementById('placement_submit_btn').innerHTML = '<i class="fas fa-save"></i> Save';
    document.getElementById('placementForm').reset();
    document.getElementById('placement_id').value = '';
    document.getElementById('placement_action').value = 'add';
    new bootstrap.Modal(document.getElementById('placementModal')).show();
  });

  // Submit form
  var placementForm = document.getElementById('placementForm');
  placementForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = placementForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    postForm(placementForm).then(function (res) {
      if (res.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('placementModal')).hide();
        return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
      }
      return showAlert('error', 'Error', res.message);
    }).catch(function () {
      return showAlert('error', 'Error', 'Something went wrong.');
    }).then(function () { btn.disabled = false; });
  });

  // Edit
  window.editPlacement = function (id) {
    var card = document.querySelector('.placement-card[data-id="' + id + '"]');
    if (!card) return;
    document.getElementById('placement_action').value = 'edit';
    document.getElementById('placement_id').value = id;
    document.getElementById('placementFormTitle').textContent = 'Edit Application';
    document.getElementById('placement_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    document.getElementById('placement_company').value = card.querySelector('.placement-company').textContent.trim();
    var roleEl = card.querySelector('.placement-role');
    document.getElementById('placement_role').value = roleEl ? roleEl.textContent.trim() : '';
    var badge = card.querySelector('.placement-status-badge');
    var statusText = badge ? badge.textContent.trim().toLowerCase() : 'applied';
    var statusMap = { applied: 'applied', shortlisted: 'shortlisted', interviewed: 'interviewed', selected: 'selected', rejected: 'rejected' };
    document.getElementById('placement_status').value = statusMap[statusText] || 'applied';
    var roundsEl = card.querySelector('.placement-rounds');
    document.getElementById('placement_rounds').value = roundsEl ? roundsEl.innerHTML.replace(/^.*?\u00a0/, '').replace(/<br\s*\/?>/gi, '\n') : '';
    var notesEl = card.querySelector('.placement-notes');
    document.getElementById('placement_notes').value = notesEl ? notesEl.innerHTML.replace(/^.*?\u00a0/, '').replace(/<br\s*\/?>/gi, '\n') : '';
    document.getElementById('placement_date').value = '';
    new bootstrap.Modal(document.getElementById('placementModal')).show();
  };

  // Delete
  window.confirmDeletePlacement = function (id) {
    Swal.fire({
      title: 'Remove Application?',
      text: 'This will remove the placement record permanently.',
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
