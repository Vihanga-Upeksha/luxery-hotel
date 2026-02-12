<?php
require 'db_connect.php';
// Destroy session and redirect
session_unset();
session_destroy();
header('Location: index.php');
exit;
?>