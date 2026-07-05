/**
 * Admin Panel JavaScript
 * 
 * Handles UI interactions, confirmations, and dynamic behaviors.
 */

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete dialog
    const deleteLinks = document.querySelectorAll('.confirm-delete');
    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Image preview on file select
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
    imageInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

});
