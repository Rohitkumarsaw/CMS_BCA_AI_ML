// Syllabus JS
document.addEventListener('DOMContentLoaded', function() {
    const topicStatuses = document.querySelectorAll('.topic-status-select');
    topicStatuses.forEach(select => {
        select.addEventListener('change', function() {
            const topicId = this.dataset.topicId;
            const status = this.value;
            fetch('update_syllabus.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `topic_id=${topicId}&status=${status}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      location.reload();
                  }
              });
        });
    });
});
