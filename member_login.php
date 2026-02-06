<?php
include('include/dbcon.php');
// include('member_session.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {

    $roll_number = trim($_POST['roll_number']);
    $contact     = trim($_POST['contact']);

    // Match roll number + contact in user table
    $stmt = mysqli_prepare(
        $con,
        "SELECT user_id, firstname, lastname, type
         FROM user
         WHERE roll_number = ? AND contact = ?"
    );
    mysqli_stmt_bind_param($stmt, "ss", $roll_number, $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        $_SESSION['member_id']   = $row['user_id'];
        $_SESSION['member_name'] = $row['firstname'].' '.$row['lastname'];
        $_SESSION['member_type'] = $row['type'];  // Student / Teacher

        header("location: member_home.php");
        exit();
    } else {
        $error_message = "Invalid Roll Number or Contact.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Login - Library</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f5f5f5;">
<div class="container" style="margin-top:90px; max-width:400px;">
  <h3 class="text-center">Member / Teacher Login</h3>
  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Roll Number / ID</label>
      <input type="text" name="roll_number" class="form-control" required>
    </div>
    <div class="form-group">
      <label>Registered Contact Number</label>
      <input type="text" name="contact" class="form-control" required maxlength="10">
    </div>
    <button type="submit" name="login" class="btn btn-success btn-block">Login</button>
    <a href="index.php" class="btn btn-link btn-block">Admin Login</a>
  </form>
</div>
</body>
</html>
