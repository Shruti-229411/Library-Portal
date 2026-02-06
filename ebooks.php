<?php
include('header.php');
include('include/dbcon.php');

// HANDLE UPLOAD
if (isset($_POST['upload_ebook'])) {
    $title    = mysqli_real_escape_string($con, $_POST['title']);
    $author   = mysqli_real_escape_string($con, $_POST['author']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $plan_id  = $_POST['allowed_plan_id'] !== '' ? (int)$_POST['allowed_plan_id'] : null;

    // main ebook file
    if (!empty($_FILES['ebook_file']['name'])) {
        $fileTmp  = $_FILES['ebook_file']['tmp_name'];
        $fileName = basename($_FILES['ebook_file']['name']);
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($ext != 'pdf' && $ext != 'epub') {
            echo "<div class='alert alert-danger'>Only PDF or EPUB files are allowed.</div>";
        } else {
            $newName  = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/','_',$fileName);
            $filePath = 'upload/ebooks/' . $newName;

            // optional cover image
            $coverFileName = '';
            if (!empty($_FILES['cover_image']['name'])) {
                $cTmp  = $_FILES['cover_image']['tmp_name'];
                $cName = basename($_FILES['cover_image']['name']);
                $cExt  = strtolower(pathinfo($cName, PATHINFO_EXTENSION));
                if (in_array($cExt, ['jpg','jpeg','png','gif'])) {
                    $cNew  = time() . '_cover_' . preg_replace('/[^A-Za-z0-9_.-]/','_',$cName);
                    $cPath = 'upload/ebooks_covers/' . $cNew;
                    if (move_uploaded_file($cTmp, $cPath)) {
                        $coverFileName = $cNew;    // only file name
                    }
                }
            }

            // move main file
            if (move_uploaded_file($fileTmp, $filePath)) {
                $planField = $plan_id ? $plan_id : 'NULL';
                $coverSql  = $coverFileName !== ''
                    ? "'" . mysqli_real_escape_string($con, $coverFileName) . "'"
                    : "NULL";

                $sql = "
                    INSERT INTO ebooks (title, author, category, file_path, allowed_plan_id, cover_image, uploaded_at)
                    VALUES (
                        '$title',
                        '$author',
                        '$category',
                        '" . mysqli_real_escape_string($con, $filePath) . "',
                        $planField,
                        $coverSql,
                        NOW()
                    )";

                mysqli_query($con, $sql) or die(mysqli_error($con));
                echo "<div class='alert alert-success'>E‑book uploaded successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>File upload failed.</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Please select a file.</div>";
    }
}

// HANDLE DELETE
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $res   = mysqli_query($con, "SELECT file_path, cover_image FROM ebooks WHERE ebook_id = $delId");
    if ($rowDel = mysqli_fetch_assoc($res)) {
        if (!empty($rowDel['file_path']) && file_exists($rowDel['file_path'])) {
            @unlink($rowDel['file_path']);
        }
        if (!empty($rowDel['cover_image'])) {
            $coverPath = 'upload/ebooks_covers/' . $rowDel['cover_image'];
            if (file_exists($coverPath)) {
                @unlink($coverPath);
            }
        }
    }
    mysqli_query($con, "DELETE FROM ebooks WHERE ebook_id = $delId") or die(mysqli_error($con));
    header("Location: ebooks.php");
    exit();
}
?>

<div class="page-title">
  <div class="title_left">
    <h3><small>Home /</small> E‑Books</h3>
  </div>
</div>
<div class="clearfix"></div>

<div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
  <div class="x_title">
    <h2><i class="fa fa-file-pdf-o"></i> Upload E‑Book</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">

    <!-- Upload form -->
    <form method="post" enctype="multipart/form-data" class="form-horizontal form-label-left">
      <div class="form-group">
        <label class="control-label col-md-2">Title<span style="color:red">*</span></label>
        <div class="col-md-4">
          <input type="text" name="title" class="form-control" required>
        </div>
        <label class="control-label col-md-2">Author</label>
        <div class="col-md-3">
          <input type="text" name="author" class="form-control">
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-2">Category</label>
        <div class="col-md-3">
          <input type="text" name="category" class="form-control">
        </div>
        <label class="control-label col-md-2">Allowed Plan</label>
        <div class="col-md-3">
          <select name="allowed_plan_id" class="select2_single form-control">
            <option value="">All Plans (Free)</option>
            <?php
            $p = mysqli_query($con,"SELECT * FROM subscription_plans ORDER BY plan_name ASC")
                 or die(mysqli_error($con));
            while ($pl = mysqli_fetch_assoc($p)) {
                echo '<option value="'.$pl['plan_id'].'">'.htmlspecialchars($pl['plan_name']).'</option>';
            }
            ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-2">File (PDF/EPUB)<span style="color:red">*</span></label>
        <div class="col-md-4">
          <input type="file" name="ebook_file" class="form-control" required>
        </div>
        <label class="control-label col-md-2">Cover Image</label>
        <div class="col-md-3">
          <input type="file" name="cover_image" class="form-control">
        </div>
      </div>

      <div class="form-group">
        <div class="col-md-6 col-md-offset-2">
          <button type="submit" name="upload_ebook" class="btn btn-success">
            <i class="fa fa-upload"></i> Upload
          </button>
        </div>
      </div>
    </form>

    <div class="ln_solid"></div>

    <!-- Existing E‑books -->
    <h4>Existing E‑Books</h4>
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>Access Type</th>
            <th>Uploaded</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        $q = mysqli_query(
                $con,
                "SELECT e.*, sp.plan_name
                 FROM ebooks e
                 LEFT JOIN subscription_plans sp ON sp.plan_id = e.allowed_plan_id
                 ORDER BY ebook_id DESC"
            ) or die(mysqli_error($con));
        while ($row = mysqli_fetch_assoc($q)) {
        ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td>
              <?php if (!empty($row['cover_image'])) { ?>
                <img src="upload/ebooks_covers/<?php echo htmlspecialchars($row['cover_image']); ?>"
                     style="height:50px;width:auto;">
              <?php } else { ?>
                No image
              <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td>
              <?php
                if ($row['plan_name']) {
                    echo htmlspecialchars($row['plan_name']) . ' (Subscription)';
                } else {
                    echo 'Free';
                }
              ?>
            </td>
            <td><?php echo $row['uploaded_at']; ?></td>
            <td>
              <a href="<?php echo htmlspecialchars($row['file_path']); ?>"
                 class="btn btn-primary btn-xs" target="_blank">
                 <i class="fa fa-download"></i> Open
              </a>
              <a href="ebooks_edit.php?id=<?php echo $row['ebook_id']; ?>"
                 class="btn btn-info btn-xs">
                 <i class="fa fa-pencil"></i> Edit
              </a>
              <a href="ebooks.php?delete_id=<?php echo $row['ebook_id']; ?>"
                 class="btn btn-danger btn-xs"
                 onclick="return confirm('Delete this e‑book?');">
                 <i class="fa fa-trash"></i> Delete
              </a>
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
