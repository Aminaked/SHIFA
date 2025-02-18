
<?php

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in a session variable named 'formData'
    $_SESSION['formData'] = $_POST;
}

// Clear session data when leaving the page
if (isset($_SESSION['formData'])) {
    unset($_SESSION['formData']);
}

