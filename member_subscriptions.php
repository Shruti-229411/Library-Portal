<?php
include('include/dbcon.php');
include('session.php');   // must be before any HTML
include('header.php');
?>


<div class="page-title">
  <div class="title_left">
    <h3><small>Home /</small> Member Subscriptions</h3>
  </div>
</div>
<div class="clearfix"></div>

<div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
  <div class="x_title">
    <h2><i class="fa fa-id-card"></i> Assign / Renew Subscription</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">

<?php
// PROCESS ASSIGN
if (isset($_POST['assign_sub'])) {
    $user_id  = (int)$_POST['user_id'];
    $plan_id  = (int)$_POST['plan_id'];
    $start    = $_POST['start_date'];

    $qplan = mysqli_query($con,"SELECT duration_days FROM subscription_plans WHERE plan_id='$plan_id'")
             or die(mysqli_error($con));
    $plan  = mysqli_fetch_assoc($qplan);
    $days  = (int)$plan['duration_days'];

    $endRes = mysqli_query($con, "SELECT DATE_ADD('$start', INTERVAL $days DAY) AS end_date");
    $endRow = mysqli_fetch_assoc($endRes);
    $end    = $endRow['end_date'];

    mysqli_query(
        $con,
        "INSERT INTO member_subscriptions (user_id,plan_id,start_date,end_date,payment_status,status)
         VALUES ('$user_id','$plan_id','$start','$end','PAID','ACTIVE')"
    ) or die(mysqli_error($con));

    echo "<div class='alert alert-success'>Subscription assigned successfully. Valid till $end.</div>";
}
?>

<!-- Select member and plan -->
<form method="post" class="form-horizontal form-label-left">
  <div class="form-group">
    <label class="control-label col-md-2">Member (Roll / Name)</label>
    <div class="col-md-4">
      <select name="user_id" class="select2_single form-control" required>
        <option value="">Select Member</option>
        <?php
        $u = mysqli_query($con,"SELECT user_id, roll_number, firstname, lastname FROM user ORDER BY firstname ASC")
             or die(mysqli_error($con));
        while ($m = mysqli_fetch_assoc($u)) {
            $label = $m['roll_number'].' - '.$m['firstname'].' '.$m['lastname'];
            echo '<option value="'.$m['user_id'].'">'.htmlspecialchars($label).'</option>';
        }
        ?>
      </select>
    </div>

    <label class="control-label col-md-2">Plan</label>
    <div class="col-md-3">
      <select name="plan_id" class="select2_single form-control" required>
        <option value="">Select Plan</option>
        <?php
        $p = mysqli_query($con,"SELECT * FROM subscription_plans ORDER BY plan_name ASC")
             or die(mysqli_error($con));
        while ($pl = mysqli_fetch_assoc($p)) {
            $label = $pl['plan_name'].' ('.$pl['duration_days'].' days)';
            echo '<option value="'.$pl['plan_id'].'">'.htmlspecialchars($label).'</option>';
        }
        ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-md-2">Start Date</label>
    <div class="col-md-2">
      <input type="date" name="start_date" class="form-control"
             value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <div class="col-md-4 col-md-offset-2">
      <button type="submit" name="assign_sub" class="btn btn-success">
        <i class="fa fa-check"></i> Assign Subscription
      </button>
    </div>
  </div>
</form>

<div class="ln_solid"></div>

<!-- List recent subscriptions -->
<h4>Recent Subscriptions</h4>
<div class="table-responsive">
  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Member</th>
        <th>Plan</th>
        <th>Start</th>
        <th>End</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $q = mysqli_query(
              $con,
              "SELECT ms.*, u.roll_number,u.firstname,u.lastname, sp.plan_name
               FROM member_subscriptions ms
               JOIN user u ON u.user_id = ms.user_id
               JOIN subscription_plans sp ON sp.plan_id = ms.plan_id
               ORDER BY ms.sub_id DESC LIMIT 50"
           ) or die(mysqli_error($con));
      while ($row = mysqli_fetch_assoc($q)) {
      ?>
      <tr>
        <td><?php echo htmlspecialchars($row['roll_number'].' - '.$row['firstname'].' '.$row['lastname']); ?></td>
        <td><?php echo htmlspecialchars($row['plan_name']); ?></td>
        <td><?php echo $row['start_date']; ?></td>
        <td><?php echo $row['end_date']; ?></td>
        <td><?php echo $row['status']; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

  </div>
</div>
</div>
</div>

<?php include('footer.php'); ?>
