(function () {
  'use strict';

  // Create Roadmap
  document.getElementById('roadmapForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    window.postData('roadmap_handler.php', fd).then(function (res) {
      if (res.status === 'success') {
        Swal.fire({ icon: 'success', title: 'Created', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
        location.reload();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
      }
    });
  });

  // Add Item via Modal
  window.showAddItem = function (roadmapId) {
    document.getElementById('addItemRoadmapId').value = roadmapId;
    var modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();
  };

  document.getElementById('addItemForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    window.postData('roadmap_handler.php', fd).then(function (res) {
      if (res.status === 'success') {
        var modalEl = document.getElementById('addItemModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        form.reset();
        // Refresh the page to show new item
        location.reload();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
      }
    });
  });

  // Get CSRF token from any hidden field on the page
  function getCSRFToken() {
    var el = document.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
  }

  // Toggle Checklist Item
  window.toggleItem = function (checkbox, itemId) {
    var completed = checkbox.checked ? 1 : 0;
    var fd = new FormData();
    fd.append('csrf_token', getCSRFToken());
    fd.append('action', 'toggle_item');
    fd.append('item_id', itemId);
    fd.append('completed', completed);
    window.postData('roadmap_handler.php', fd).then(function (res) {
      if (res.status === 'success') {
        var item = checkbox.closest('.roadmap-checklist-item');
        if (completed) { item.classList.add('completed'); } else { item.classList.remove('completed'); }
        // Update progress bar in parent card
        var card = checkbox.closest('.roadmap-card');
        var fill = card.querySelector('.roadmap-progress-fill');
        var pctLabel = card.querySelector('.roadmap-pct');
        var compLabel = card.querySelector('.roadmap-completed');
        var totalLabel = card.querySelector('.roadmap-total');
        var total = parseInt(res.total_nodes);
        var comp = parseInt(res.completed_nodes);
        var pct = total > 0 ? Math.round((comp / total) * 100) : 0;
        if (fill) fill.style.width = pct + '%';
        if (pctLabel) pctLabel.textContent = pct + '%';
        if (compLabel) compLabel.textContent = comp;
        if (totalLabel) totalLabel.textContent = total;
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
        checkbox.checked = !checkbox.checked;
      }
    });
  };

  // Edit Item
  window.editItem = function (itemId) {
    var itemDiv = document.querySelector('.roadmap-checklist-item[data-item-id="' + itemId + '"]');
    if (!itemDiv) return;
    var titleEl = itemDiv.querySelector('.roadmap-item-title');
    if (!titleEl) return;
    document.getElementById('editItemId').value = itemId;
    document.getElementById('editItemTitle').value = titleEl.textContent.trim();
    var modal = new bootstrap.Modal(document.getElementById('editItemModal'));
    modal.show();
  };

  document.getElementById('editItemForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    window.postData('roadmap_handler.php', fd).then(function (res) {
      if (res.status === 'success') {
        var modalEl = document.getElementById('editItemModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        location.reload();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
      }
    });
  });

  // Delete Item
  window.deleteItem = function (itemId) {
    Swal.fire({
      title: 'Delete Item?',
      text: 'This item will be removed from the checklist.',
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
      var itemDiv = document.querySelector('.roadmap-checklist-item[data-item-id="' + itemId + '"]');
      if (!itemDiv) return;
      var card = itemDiv.closest('.roadmap-card');
      itemDiv.remove();
      var fd = new FormData();
      fd.append('csrf_token', getCSRFToken());
      fd.append('action', 'delete_item');
      fd.append('item_id', itemId);
      window.postData('roadmap_handler.php', fd).then(function (res) {
        if (res.status === 'success') {
          // Update progress bar in parent card
          var fill = card.querySelector('.roadmap-progress-fill');
          var pctLabel = card.querySelector('.roadmap-pct');
          var compLabel = card.querySelector('.roadmap-completed');
          var totalLabel = card.querySelector('.roadmap-total');
          var total = parseInt(res.total_nodes);
          var comp = parseInt(res.completed_nodes);
          var pct = total > 0 ? Math.round((comp / total) * 100) : 0;
          if (fill) fill.style.width = pct + '%';
          if (pctLabel) pctLabel.textContent = pct + '%';
          if (compLabel) compLabel.textContent = comp;
          if (totalLabel) totalLabel.textContent = total;
          // Empty state if no items left
          var remaining = card.querySelectorAll('.roadmap-checklist-item').length;
          if (remaining === 0) {
            var checklist = card.querySelector('.roadmap-checklist');
            if (checklist) checklist.innerHTML = '<div style="text-align:center;padding:10px;font-size:0.78rem;color:#8f94a8;">No items yet. Click + to add.</div>';
          }
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
        }
      });
    });
  };

  // Delete Roadmap
  window.deleteRoadmap = function (id) {
    Swal.fire({
      title: 'Delete Roadmap?',
      text: 'All items inside will also be removed.',
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
      fd.append('csrf_token', getCSRFToken());
      fd.append('action', 'delete_roadmap');
      fd.append('id', id);
      window.postData('roadmap_handler.php', fd).then(function (res) {
        if (res.status === 'success') {
          location.reload();
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
        }
      });
    });
  };
})();