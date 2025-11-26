<?php
// Restrict access to admin users only
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    exit('Access denied.');
}
phpinfo();
?>