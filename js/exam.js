document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.exam-status-toggle').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var id = this.dataset.id;
            var status = this.value;
            var row = this.closest('tr');
            var badge = row.querySelector('.exam-status-badge');
            var formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
            fetch('exam_actions.php', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        badge.className = 'exam-status-badge exam-status-' + status;
                        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        Swal.fire({ icon: 'success', title: 'Updated', text: data.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' }, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message, background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
                    }
                })
                .catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Server error', background: '#191c24', color: '#ffffff', confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm-ok' } });
                });
        });
    });
});
