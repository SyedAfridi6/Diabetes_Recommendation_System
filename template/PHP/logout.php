<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();



// Redirect back to login page after logout
header("Location: login.php");
exit();
?>
