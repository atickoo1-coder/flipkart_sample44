<?php
/**
 * Admin Footer
 * 
 * Contains closing body tags and JavaScript includes.
 * To be included at the bottom of every admin page.
 */
?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Admin Custom JS -->
    <script src="<?php echo rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); ?>/admin/assets/js/script.js"></script>
</body>
</html>
