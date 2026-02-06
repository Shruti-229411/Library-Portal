<?php
include('include/dbcon.php');
include('session.php');   // admin session, no HTML output

if (!isset($_GET['id'])) {
    header('Location: ebooks.php');
    exit;
}

$id = (int)$_GET['id'];

// fetch existing row
$res   = mysqli_query($con, "SELECT * FROM ebooks WHERE ebook_id = $id") or die(mysqli_error($con));
$ebook = mysqli_fetch_assoc($res);
if (!$ebook) {
    header('Location: ebooks.php');
    exit;
}

// handle update BEFORE any HTML
if (isset($_POST['update_ebook'])) {
    $title    = mysqli_real_escape_string($con, $_POST['title']);
    $author   = mysqli_real_escape_string($con, $_POST['author']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $plan_id  = $_POST['allowed_plan_id'] !== '' ? (int)$_POST['allowed_plan_id'] : null;

    $setCover = "";
    if (!empty($_FILES['cover_image']['name'])) {
        $cTmp  = $_FILES['cover_image']['tmp_name'];
        $cName = basename($_FILES['cover_image']['name']);
        $cExt  = strtolower(pathinfo($cName, PATHINFO_EXTENSION));
        if (in_array($cExt, ['jpg','jpeg','png','gif'])) {
            $cNew  = time() . '_cover_' . preg_replace('/[^A-Za-z0-9_.-]/','_',$cName);
            $cPath = 'upload/ebooks_covers/' . $cNew;
            if (move_uploaded_file($cTmp, $cPath)) {
                if (!empty($ebook['cover_image'])) {
                    $old = 'upload/ebooks_covers/' . $ebook['cover_image'];
                    if (file_exists($old)) {
                        @unlink($old);
                    }
                }
                $safeCover = mysqli_real_escape_string($con, $cNew);
                $setCover  = ", cover_image = '$safeCover'";
            }
        }
    }

    $planField = $plan_id ? $plan_id : 'NULL';

    $sql = "
        UPDATE ebooks
        SET title = '$title',
            author = '$author',
            category = '$category',
            allowed_plan_id = $planField
            $setCover
        WHERE ebook_id = $id
    ";
    mysqli_query($con, $sql) or die(mysqli_error($con));

    header('Location: ebooks.php');
    exit;
}

// after ALL header() calls, now include HTML header
include('header.php');
?>

<div class="page-title">
  <div class="title_left">
    <h3><small>Home /</small> Edit E‑Book</h3>
  </div>
</div>
<div class="clearfix"></div>

<!-- rest of your HTML form using $ebook values -->


<div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
  <div class="x_title">
    <h2>Edit E‑Book</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">

    <form method="post" enctype="multipart/form-data" class="form-horizontal form-label-left">
      <div class="form-group">
        <label class="control-label col-md-2">Title<span style="color:red">*</span></label>
        <div class="col-md-4">
          <input type="text" name="title" class="form-control"
                 value="<?php echo htmlspecialchars($ebook['title']); ?>" required>
        </div>
        <label class="control-label col-md-2">Author</label>
        <div class="col-md-3">
          <input type="text" name="author" class="form-control"
                 value="<?php echo htmlspecialchars($ebook['author']); ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-2">Category</label>
        <div class="col-md-3">
          <input type="text" name="category" class="form-control"
                 value="<?php echo htmlspecialchars($ebook['category']); ?>">
        </div>
        <label class="control-label col-md-2">Allowed Plan</label>
        <div class="col-md-3">
          <select name="allowed_plan_id" class="select2_single form-control">
            <option value="">All Plans (Free)</option>
            <?php
            $p = mysqli_query($con,"SELECT * FROM subscription_plans ORDER BY plan_name ASC")
                 or die(mysqli_error($con));
            while ($pl = mysqli_fetch_assoc($p)) {
                $sel = ($ebook['allowed_plan_id'] == $pl['plan_id']) ? 'selected' : '';
                echo '<option value="'.$pl['plan_id'].'" '.$sel.'>'.
                        htmlspecialchars($pl['plan_name']).
                     '</option>';
            }
            ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-2">Current Cover</label>
        <div class="col-md-4">
          <?php if (!empty($ebook['cover_image'])) { ?>
            <img src="upload/ebooks_covers/<?php echo htmlspecialchars($ebook['cover_image']); ?>"
                 style="height:80px;width:auto;">
          <?php } else { ?>
            No cover uploaded
          <?php } ?>
        </div>
        <label class="control-label col-md-2">New Cover (optional)</label>
        <div class="col-md-3">
          <input type="file" name="cover_image" class="form-control">
        </div>
      </div>

      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <button type="submit" name="update_ebook" class="btn btn-success">
            Save Changes
          </button>
          <a href="ebooks.php" class="btn btn-default">Cancel</a>
        </div>
      </div>
    </form>

  </div>
</div>
</div>
</div>

<?php include('footer.php'); ?>
