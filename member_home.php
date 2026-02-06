<?php
include('include/dbcon.php');
include('member_session.php');

$member_id = $_SESSION['member_id'];

// Active subscription, if any
$sub = mysqli_query(
    $con,
    "SELECT ms.*, sp.plan_name
     FROM member_subscriptions ms
     JOIN subscription_plans sp ON sp.plan_id = ms.plan_id
     WHERE ms.user_id = '$member_id'
       AND ms.status = 'ACTIVE'
       AND ms.payment_status = 'PAID'
       AND ms.end_date >= CURDATE()
     ORDER BY ms.end_date DESC
     LIMIT 1"
) or die(mysqli_error($con));
$activeSub = mysqli_fetch_assoc($sub);

// Currently borrowed books (adjust field names if needed)
// Currently borrowed books (adjust field names if needed)
$borrow = mysqli_query(
    $con,
    "SELECT 
         b.book_title,
         br.date_borrowed,
         br.due_date,
         -- days overdue (0 if not overdue)
         GREATEST(DATEDIFF(CURDATE(), br.due_date), 0) AS overdue_days,
         -- fine = 5 per overdue day, change 5 to your rate
         GREATEST(DATEDIFF(CURDATE(), br.due_date), 0) * 5 AS fine_amount
     FROM borrow_book br
     JOIN book b ON b.book_id = br.book_id
     WHERE br.user_id = '$member_id'
       AND br.borrowed_status = 'borrowed'"
) or die(mysqli_error($con));



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Dashboard</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f5f5f5;">
<div class="container" style="margin-top:40px;">
  <div class="row">
    <div class="col-md-12">
      <h3>Welcome, <?php echo htmlspecialchars($_SESSION['member_name']); ?></h3>
      <a href="member_logout.php" class="btn btn-danger btn-xs pull-right">Logout</a>
      <hr>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <h4>Active Subscription</h4>
      <?php if ($activeSub): ?>
        <p><strong>Plan:</strong> <?php echo htmlspecialchars($activeSub['plan_name']); ?></p>
        <p><strong>Valid Till:</strong> <?php echo $activeSub['end_date']; ?></p>
      <?php else: ?>
        <p>No active subscription. Please contact library.</p>
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <h4>Currently Issued Books</h4>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Issued</th>
            <th>Due</th>
            <th>Fine</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($borrow)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['book_title']); ?></td>
            <td><?php echo $row['date_borrowed']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <!-- <td><?php echo $row['book_penalty']; ?></td> -->
             <td><?php echo $row['fine_amount']; ?></td>

          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <a href="ebooks_member.php" class="btn btn-primary">View E‑Books</a>
    </div>
  </div>
</div>
</body>
</html>
