// Circular JS
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCircular');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.circular-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? 'block' : 'none';
            });
        });
    }
});
