document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach(function(row, index) {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        setTimeout(function() {
            row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
