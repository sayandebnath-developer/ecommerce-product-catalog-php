<?php
// admin/logout.php - end the admin session (FR-08)
session_start();

$_SESSION = [];      // 1. remove all data stored in the session
session_destroy();   // 2. destroy the session on the server

header('Location: login.php');   // 3. send the user back to the login page
exit;