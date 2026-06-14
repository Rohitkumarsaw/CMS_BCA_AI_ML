// Reports JS
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts on the reports page
    const chartContainers = document.querySelectorAll('.chart-container canvas');
    chartContainers.forEach(canvas => {
        new Chart(canvas.getContext('2d'), {
            type: canvas.dataset.type || 'bar',
            data: JSON.parse(canvas.dataset.chartData || '{}'),
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
});
