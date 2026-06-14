document.addEventListener('DOMContentLoaded', function() {
    var photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    e.target.value = '';
                    return;
                }
                var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, PNG, GIF files are allowed');
                    e.target.value = '';
                    return;
                }
            }
        });
    }
});
