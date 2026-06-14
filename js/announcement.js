document.addEventListener('DOMContentLoaded', function() {
    var filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        var selects = filterForm.querySelectorAll('select');
        selects.forEach(function(select) {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
});
