// Schedule JS
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.schedule-tabs .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href');
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
            document.querySelector(target).classList.add('show', 'active');
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
