// Groups JS
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchGroup');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.group-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? 'block' : 'none';
            });
        });
    }
});
