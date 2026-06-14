document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-subject-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editSubjectId').value = this.dataset.id;
            document.getElementById('editSubjectName').value = this.dataset.name;
            new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
        });
    });

    document.querySelectorAll('.delete-subject-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('deleteSubjectId').value = this.dataset.id;
            document.getElementById('deleteSubjectName').textContent = this.dataset.name;
            new bootstrap.Modal(document.getElementById('deleteSubjectModal')).show();
        });
    });
});
