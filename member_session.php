<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* Absolutely no caching of member pages */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* If member_id is missing, never allow access */
if (!isset($_SESSION['member_id'])) {
    header("Location: member_login.php");
    exit;
}
