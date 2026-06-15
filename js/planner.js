(function () {
  'use strict';

  var API = 'planner_handler.php';

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

  function postData(data) {
    return fetch(API, { method: 'POST', body: data }).then(function (r) {
      if (!r.ok) throw new Error('Server error');
      return r.json();
    });
  }

  function parseDisplayDate(str) {
    // "05 Jun 2026" → "2026-06-05"
    var months = {Jan:'01',Feb:'02',Mar:'03',Apr:'04',May:'05',Jun:'06',Jul:'07',Aug:'08',Sep:'09',Oct:'10',Nov:'11',Dec:'12'};
    var m = str.match(/(\d{1,2})\s+(\w{3})\s+(\d{4})/);
    if (!m) return '';
    var day = m[1].padStart(2,'0');
    var mon = months[m[2]] || '01';
    return m[3] + '-' + mon + '-' + day;
  }

  // ── Tab switching ──

  var tabs = document.querySelectorAll('.planner-tab');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      document.querySelectorAll('.planner-form-section').forEach(function (s) { s.classList.remove('active'); });
      var target = document.getElementById(tab.dataset.target);
      if (target) target.classList.add('active');
      var titleEl = document.getElementById('formTitle');
      if (titleEl) titleEl.textContent = tab.textContent.trim();
    });
  });

  // ── Shopping Form ──

  var shoppingForm = document.getElementById('shoppingForm');
  if (shoppingForm) {
    shoppingForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = shoppingForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(shoppingForm).then(function (res) {
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

  // ── Inventory Form ──

  var inventoryForm = document.getElementById('inventoryForm');
  if (inventoryForm) {
    inventoryForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = inventoryForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postForm(inventoryForm).then(function (res) {
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

  // ── Toggle purchased ──

  window.togglePurchased = function (id) {
    var fd = new FormData();
    fd.append('action', 'toggle_shopping');
    fd.append('id', id);
    postData(fd).then(function (res) {
      if (res.status === 'success') {
        return showAlert('success', 'Updated', res.message).then(function () { location.reload(); });
      }
      return showAlert('error', 'Error', res.message);
    }).catch(function () {
      return showAlert('error', 'Error', 'Something went wrong.');
    });
  };

  // ── Edit Shopping ──

  window.editShopping = function (id) {
    var tile = document.querySelector('.planner-tile[data-id="' + id + '"]');
    if (!tile) return;
    var titleEl = tile.querySelector('.planner-tile-title');
    var reasonEl = tile.querySelector('.planner-tile-reason');
    var purposeEl = tile.querySelector('.planner-tile-purpose');
    var dateEl = tile.querySelector('.planner-tile-date-text');
    if (!titleEl) return;

    document.getElementById('shop_action').value = 'edit_shopping';
    document.getElementById('shop_id').value = id;
    document.getElementById('shop_item_name').value = titleEl.textContent.trim();

    document.getElementById('shop_reason_why').value = reasonEl
      ? reasonEl.textContent.replace(/^Reason:\s*/i, '').trim()
      : '';

    document.getElementById('shop_purpose_work').value = purposeEl
      ? purposeEl.textContent.replace(/^Purpose:\s*/i, '').trim()
      : '';

    document.getElementById('shop_target_date').value = dateEl
      ? parseDisplayDate(dateEl.textContent.trim())
      : '';

    // Switch to shopping tab
    document.querySelector('.planner-tab[data-target="shoppingFormSection"]').click();
    document.getElementById('formTitle').textContent = 'Edit Shopping Item';
    document.getElementById('shop_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    document.getElementById('shop_cancel_btn').style.display = 'inline-flex';
  };

  window.cancelShoppingEdit = function () {
    resetShoppingForm();
  };

  function resetShoppingForm() {
    document.getElementById('shop_action').value = 'add_shopping';
    document.getElementById('shop_id').value = '';
    document.getElementById('shoppingForm').reset();
    document.getElementById('formTitle').textContent = 'Add Shopping Item';
    document.getElementById('shop_submit_btn').innerHTML = '<i class="fas fa-plus"></i> Add';
    document.getElementById('shop_cancel_btn').style.display = 'none';
  }

  // ── Delete Shopping ──

  window.confirmDeleteShopping = function (id) {
    Swal.fire({
      title: 'Remove Item?',
      text: 'This will remove it from your shopping list.',
      icon: 'warning',
      iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, Remove',
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
        fd.append('action', 'delete_shopping');
        fd.append('id', id);
        postData(fd).then(function (res) {
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

  // ── Edit Inventory ──

  window.editInventoryItem = function (id) {
    var itemEl = document.querySelector('.inventory-item[data-id="' + id + '"]');
    if (!itemEl) return;
    var nameEl = itemEl.querySelector('.inventory-item-name');
    var qtyEl = itemEl.querySelector('.inventory-item-qty');
    var badgeEl = itemEl.querySelector('.inventory-badge');
    if (!nameEl) return;

    document.getElementById('inv_action').value = 'edit_inventory';
    document.getElementById('inv_id').value = id;
    document.getElementById('inv_item_name').value = nameEl.textContent.trim();

    if (qtyEl) {
      var qtyMatch = qtyEl.textContent.match(/\d+/);
      document.getElementById('inv_quantity').value = qtyMatch ? qtyMatch[0] : 1;
    } else {
      document.getElementById('inv_quantity').value = 1;
    }

    if (badgeEl) {
      var t = badgeEl.textContent.trim();
      if (t === 'Available' || t === 'Running Low' || t === 'Out of Stock') {
        document.getElementById('inv_availability_status').value = t;
      }
    }

    // Switch to inventory tab
    document.querySelector('.planner-tab[data-target="inventoryFormSection"]').click();
    document.getElementById('formTitle').textContent = 'Edit Inventory Item';
    document.getElementById('inv_submit_btn').innerHTML = '<i class="fas fa-save"></i> Update';
    document.getElementById('inv_cancel_btn').style.display = 'inline-flex';
  };

  window.cancelInventoryEdit = function () {
    resetInventoryForm();
  };

  function resetInventoryForm() {
    document.getElementById('inv_action').value = 'add_inventory';
    document.getElementById('inv_id').value = '';
    document.getElementById('inventoryForm').reset();
    document.getElementById('formTitle').textContent = 'Add Inventory Item';
    document.getElementById('inv_submit_btn').innerHTML = '<i class="fas fa-plus"></i> Add';
    document.getElementById('inv_cancel_btn').style.display = 'none';
  }

  // ── Delete Inventory ──

  window.confirmDeleteInventory = function (id) {
    Swal.fire({
      title: 'Remove Item?',
      text: 'This will remove it from your inventory.',
      icon: 'warning',
      iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-trash-alt"></i> Yes, Remove',
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
        fd.append('action', 'delete_inventory');
        fd.append('id', id);
        postData(fd).then(function (res) {
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

})();
