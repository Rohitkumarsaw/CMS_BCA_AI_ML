// Exam Prep JS
document.addEventListener('DOMContentLoaded', function() {
    const progressInputs = document.querySelectorAll('.progress-input');
    progressInputs.forEach(input => {
        input.addEventListener('change', function() {
            const prepId = this.dataset.prepId;
            const progress = this.value;
            fetch('exam_prep.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_progress&prep_id=${prepId}&progress=${progress}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      location.reload();
                  }
              });
        });
    });
});
