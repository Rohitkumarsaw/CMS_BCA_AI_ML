// Exam JS
document.addEventListener('DOMContentLoaded', function() {
    const viewToggle = document.getElementById('viewToggle');
    const listView = document.getElementById('listView');
    const calendarView = document.getElementById('calendarView');
    
    if (viewToggle && listView && calendarView) {
        viewToggle.addEventListener('click', function() {
            if (listView.style.display === 'none') {
                listView.style.display = 'block';
                calendarView.style.display = 'none';
                this.innerHTML = '<i class="fas fa-calendar"></i> Calendar View';
            } else {
                listView.style.display = 'none';
                calendarView.style.display = 'block';
                this.innerHTML = '<i class="fas fa-list"></i> List View';
            }
        });
    }
});
