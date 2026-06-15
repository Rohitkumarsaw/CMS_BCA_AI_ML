(function () {
  'use strict';

  var API = 'about_settings_handler.php';

  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      background: '#191c24',
      color: '#ffffff',
      iconColor: icon === 'success' ? '#00d25b' : '#fc424a',
      confirmButtonText: 'OK',
      buttonsStyling: false,
      customClass: { confirmButton: 'swal2-confirm-ok' }
    });
  }

  function postForm(form) {
    var data = new FormData(form);
    return fetch(API, { method: 'POST', body: data }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  }

  // ── Toggle edit mode ──

  function enterEdit(displayId, formId) {
    document.getElementById(displayId).classList.add('hidden');
    document.getElementById(formId).classList.add('active');
  }

  function exitEdit(displayId, formId) {
    document.getElementById(displayId).classList.remove('hidden');
    document.getElementById(formId).classList.remove('active');
  }

  // ── About edit ──

  var editAboutBtn = document.getElementById('editAboutBtn');
  if (editAboutBtn) {
    editAboutBtn.addEventListener('click', function () {
      enterEdit('aboutDisplay', 'aboutEditForm');
    });
  }

  var cancelAboutBtn = document.getElementById('cancelAboutBtn');
  if (cancelAboutBtn) {
    cancelAboutBtn.addEventListener('click', function () {
      exitEdit('aboutDisplay', 'aboutEditForm');
    });
  }

  var aboutForm = document.querySelector('#aboutEditForm form');
  if (aboutForm) {
    aboutForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = aboutForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(aboutForm).then(function (res) {
        if (res.status === 'success') {
          return showAlert('success', 'Updated', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () {
        return showAlert('error', 'Error', 'Something went wrong.');
      }).then(function () {
        btn.disabled = false;
      });
    });
  }

  // ── Partner edit ──

  var editPartnerBtn = document.getElementById('editPartnerBtn');
  if (editPartnerBtn) {
    editPartnerBtn.addEventListener('click', function () {
      enterEdit('partnerDisplay', 'partnerEditForm');
    });
  }

  var cancelPartnerBtn = document.getElementById('cancelPartnerBtn');
  if (cancelPartnerBtn) {
    cancelPartnerBtn.addEventListener('click', function () {
      exitEdit('partnerDisplay', 'partnerEditForm');
    });
  }

  var partnerForm = document.querySelector('#partnerEditForm form');
  if (partnerForm) {
    partnerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = partnerForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(partnerForm).then(function (res) {
        if (res.status === 'success') {
          return showAlert('success', 'Updated', res.message).then(function () { location.reload(); });
        }
        return showAlert('error', 'Error', res.message);
      }).catch(function () {
        return showAlert('error', 'Error', 'Something went wrong.');
      }).then(function () {
        btn.disabled = false;
      });
    });
  }

})();
