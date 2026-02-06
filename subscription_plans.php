<?php
include('header.php');
include('include/dbcon.php');
?>

<div class="page-title">
  <div class="title_left">
    <h3><small>Home /</small> Subscription Plans</h3>
  </div>
</div>
<div class="clearfix"></div>

<div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
  <div class="x_title">
    <h2><i class="fa fa-list"></i> Manage Subscription Plans</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">

    <!-- Add / Edit Form -->
    <?php
    // HANDLE INSERT
    if (isset($_POST['add_plan'])) {
        $plan_name     = mysqli_real_escape_string($con, $_POST['plan_name']);
        $duration_days = (int)$_POST['duration_days'];
        $price         = (float)$_POST['price'];
        $description   = mysqli_real_escape_string($con, $_POST['description']);

        if ($plan_name != '' && $duration_days > 0) {
            mysqli_query(
                $con,
                "INSERT INTO subscription_plans (plan_name,duration_days,price,description)
                 VALUES ('$plan_name','$duration_days','$price','$description')"
            ) or die(mysqli_error($con));
            echo "<div class='alert alert-success'>Plan added successfully.</div>";
        }
    }

    // HANDLE UPDATE
    if (isset($_POST['update_plan'])) {
        $edit_id       = (int)$_POST['edit_id'];
        $plan_name     = mysqli_real_escape_string($con, $_POST['plan_name']);
        $duration_days = (int)$_POST['duration_days'];
        $price         = (float)$_POST['price'];
        $description   = mysqli_real_escape_string($con, $_POST['description']);

        mysqli_query(
            $con,
            "UPDATE subscription_plans
             SET plan_name='$plan_name',
                 duration_days='$duration_days',
                 price='$price',
                 description='$description'
             WHERE plan_id='$edit_id'"
        ) or die(mysqli_error($con));
        echo "<div class='alert alert-info'>Plan updated successfully.</div>";
    }

    // If edit is requested, load row
    $edit_row = null;
    if (isset($_GET['edit_id'])) {
        $eid      = (int)$_GET['edit_id'];
        $res_edit = mysqli_query($con,"SELECT * FROM subscription_plans WHERE plan_id='$eid'") or die(mysqli_error($con));
        $edit_row = mysqli_fetch_assoc($res_edit);
    }
    ?>

    <form method="post" class="form-horizontal form-label-left">
      <input type="hidden" name="edit_id" value="<?php echo $edit_row ? $edit_row['plan_id'] : ''; ?>">
      <div class="form-group">
        <label class="control-label col-md-2">Plan Name<span style="color:red">*</span></label>
        <div class="col-md-4">
          <input type="text" name="plan_name" class="form-control"
                 required value="<?php echo $edit_row ? htmlspecialchars($edit_row['plan_name']) : ''; ?>">
        </div>
        <label class="control-label col-md-2">Duration (days)<span style="color:red">*</span></label>
        <div class="col-md-2">
          <input type="number" name="duration_days" min="1" class="form-control"
                 required value="<?php echo $edit_row ? (int)$edit_row['duration_days'] : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-2">Price</label>
        <div class="col-md-2">
          <input type="number" step="0.01" name="price" class="form-control"
                 value="<?php echo $edit_row ? htmlspecialchars($edit_row['price']) : '0.00'; ?>">
        </div>
        <label class="control-label col-md-2">Description</label>
        <div class="col-md-4">
          <input type="text" name="description" class="form-control"
                 value="<?php echo $edit_row ? htmlspecialchars($edit_row['description']) : ''; ?>">
        </div>
      </div>

      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <?php if ($edit_row) { ?>
            <button type="submit" name="update_plan" class="btn btn-success">
              <i class="fa fa-save"></i> Update Plan
            </button>
            <a href="subscription_plans.php" class="btn btn-default">Cancel</a>
          <?php } else { ?>
            <button type="submit" name="add_plan" class="btn btn-success">
              <i class="fa fa-plus-square"></i> Add Plan
            </button>
          <?php } ?>
        </div>
      </div>
    </form>

    <div class="ln_solid"></div>

    <!-- Plans Table -->
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Plan Name</th>
            <th>Duration (days)</th>
            <th>Price</th>
            <th>Description</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = mysqli_query($con,"SELECT * FROM subscription_plans ORDER BY plan_id DESC") or die(mysqli_error($con));
          while($row = mysqli_fetch_assoc($res)) {
          ?>
          <tr>
            <td><?php echo $row['plan_id']; ?></td>
            <td><?php echo htmlspecialchars($row['plan_name']); ?></td>
            <td><?php echo $row['duration_days']; ?></td>
            <td><?php echo $row['price']; ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td>
              <a href="subscription_plans.php?edit_id=<?php echo $row['plan_id']; ?>"
                 class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</a>
            </td>
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
