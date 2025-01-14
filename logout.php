<?php
session_start();

session_unset();
session_destroy();

// simple logout taking user out, and finishing session.

header('Location: homepage.php');
exit();
?>
