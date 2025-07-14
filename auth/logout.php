<?php
require_once '../includes/config.php';
session_destroy();
$_SESSION['message'] = "Logged out successfully.";
$_SESSION['message_type'] = "success";
header("Location: login.php");
exit;