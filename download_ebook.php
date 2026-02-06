<?php
include('include/dbcon.php');
// include('session.php');   // admin session if needed
include('member_session.php');   // member must be logged in

// accept ?id= from URL
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('Invalid request');
}

$ebook_id = (int)$_GET['id'];

// Logged-in member id from session
$userId = $_SESSION['member_id'];   // logged-in member

// ------------------------
// 1. Load e-book details
// ------------------------
$ebRes = mysqli_query(
    $con,
    "SELECT * FROM ebooks WHERE ebook_id = $ebook_id"
) or die(mysqli_error($con));

if (mysqli_num_rows($ebRes) == 0) {
    die('E‑book not found');
}

$ebook = mysqli_fetch_assoc($ebRes);

// ------------------------
// 2. Check subscription
// ------------------------

// If ebook has NO plan (allowed_plan_id is NULL) → FREE e-book
if (empty($ebook['allowed_plan_id'])) {

    $activeSub = null;

} else {

    // E-book is subscription-only
    $subSql = "SELECT ms.*, sp.plan_name
               FROM member_subscriptions ms
               JOIN subscription_plans sp ON sp.plan_id = ms.plan_id
               WHERE ms.user_id = '$userId'
                 AND ms.payment_status = 'PAID'
                 AND ms.status = 'ACTIVE'
                 AND ms.end_date >= CURDATE()
               ORDER BY ms.end_date DESC
               LIMIT 1";

    $subRes = mysqli_query($con, $subSql) or die(mysqli_error($con));

    if (mysqli_num_rows($subRes) == 0) {
        echo "<h3 style='text-align:center;margin-top:50px;'>
                Your subscription is not active or has expired.<br>
                Please contact the library to subscribe in order to access this e‑book.
              </h3>";
        exit;
    }

    $activeSub = mysqli_fetch_assoc($subRes);

    // Check if user's plan matches the ebook's allowed_plan_id
    if ((int)$ebook['allowed_plan_id'] !== (int)$activeSub['plan_id']) {
        echo "<h3 style='text-align:center;margin-top:50px;'>
                This e‑book is not available for your current subscription plan.
              </h3>";
        exit;
    }
}

// ------------------------
// 3. Serve file for online view
// ------------------------

$file = $ebook['file_path'];   // e.g. upload/ebooks/xxxx.pdf

if (!file_exists($file)) {
    die('File missing on server.');
}

$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = ($ext == 'pdf') ? 'application/pdf' : 'application/octet-stream';

// Open inline so user can read online
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
