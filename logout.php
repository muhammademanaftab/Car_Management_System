<?php
session_start();

// Destroy session to log out
session_unset();
session_destroy();

header('Location: homepage.php');
exit();
?>
