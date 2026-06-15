(function () {
  'use strict';

  var API = 'achievements_handler.php';

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

  // Category filter
  var filters = document.querySelectorAll('.achievement-filter');
  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filters.forEach(function (f) { f.classList.remove('active'); });
      btn.classList.add('active');
      var filter = btn.dataset.filter;
      document.querySelectorAll('.achievement-card').forEach(function (card) {
        card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
      });
    });
  });

  // Open modal for add
  document.getElementById('addAchievementBtn').addEventListener('click', function () {
    document.getElementById('achievementFormTitle').textContent = 'Add Achievement';
    document.getElementById('achievement_submit_btn').innerHTML = '<i class="fas fa-save"></i> Save';
    document.getElementById('achievementForm').reset();
    document.getElementById('achievement_id').value = '';
    document.getElementById('achievement_action').value = 'add';
    new bootstrap.Modal(document.getElementById('achievementModal')).show();
  });

  // Submit form
  var achievementForm = document.getElementById('achievementForm');
  achievementForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = achievementForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    postForm(achievementForm).then(function (res) {
      if (res.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('achievementModal')).hide();
        return showAlert('success', 'Success', res.message).then(function () { location.reload(); });
      }
      return showAlert('error', 'Error', res.message);
    }).catch(function () {
      return showAlert('error', 'Error', 'Something went wrong.');
    }).then(function () { btn.disabled = false; });
  });

  // Edit
  window.editAchievement = function (id) {
    var card = document.querySelector('.achievement-card[data-id="' + id + '"]');
    if (!card) return;
    document.getElementById('achievement_action').value = 'edit';
    document.getElementById('achievement_id').value = id;
    document.getElementById('achievementFormTitle').textContent = 'Edit Achievement';
    document.getElementById('achievement_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    var cat = card.querySelector('.achievement-category');
    var catText = cat ? cat.textContent.trim().toLowerCase() : 'other';
    var catMap = { award: 'award', hackathon: 'hackathon', extracurricular: 'extracurricular', other: 'other' };
    document.getElementById('achievement_title').value = card.querySelector('.achievement-title').textContent.trim();
    document.getElementById('achievement_category').value = catMap[catText.split(' ').pop()] || 'other';
    var issuerEl = card.querySelector('.achievement-issuer');
    document.getElementById('achievement_issuer').value = issuerEl ? issuerEl.textContent.replace(/^.*?\u00a0/, '').trim() : '';
    var dateEl = card.querySelector('.achievement-date');
    document.getElementById('achievement_date').value = dateEl ? dateEl.textContent.trim() : '';
    var descEl = card.querySelector('.achievement-desc');
    document.getElementById('achievement_desc').value = descEl ? descEl.innerHTML.replace(/<br\s*\/?>/gi, '\n') : '';
    var linkEl = card.querySelector('.achievement-link');
    document.getElementById('achievement_link').value = linkEl ? linkEl.getAttribute('href') : '';
    new bootstrap.Modal(document.getElementById('achievementModal')).show();
  };

  // Delete
  window.confirmDeleteAchievement = function (id) {
    Swal.fire({
      title: 'Remove Achievement?',
      text: 'This will remove the achievement permanently.',
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
