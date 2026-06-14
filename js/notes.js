// Notes JS
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const noteId = this.dataset.noteId;
            const icon = this.querySelector('i');
            fetch('notes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_favorite&note_id=${noteId}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      icon.classList.toggle('fas');
                      icon.classList.toggle('far');
                      this.classList.toggle('active');
                  }
              });
        });
    });
});
