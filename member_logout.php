<?php
// member_logout.php
session_start();
unset($_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_type'], $_SESSION['member_last_activity']);
session_destroy();
header("Location: member_login.php");
exit;
