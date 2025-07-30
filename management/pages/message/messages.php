<?php
// messages.php - Handles displaying success/error messages

// Display success message if set
if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php
// Display error message if set
if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>