<?php
include('include/dbcon.php');
include('header.php');

// 1. Get date range from POST or default
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    !empty($_POST['datefrom']) && !empty($_POST['dateto'])) {

    $dateFrom = $_POST['datefrom'];
    $dateTo   = $_POST['dateto'];
} else {
    // default wide range so everything shows
    $dateFrom = '2000-01-01';
    $dateTo   = date('Y-m-d');
}

// 2. Save to session for print page
$_SESSION['datefrom'] = $dateFrom;
$_SESSION['dateto']   = $dateTo;

// 3. Load ONLY borrowed-book actions in this date range
$sql = "
    SELECT r.*, b.book_title,
           u.firstname, u.middlename, u.lastname
    FROM report AS r
    LEFT JOIN book AS b ON r.book_id = b.book_id
    LEFT JOIN user AS u ON r.user_id = u.user_id
    WHERE r.date_transaction BETWEEN '$dateFrom 00:00:01' AND '$dateTo 23:59:59'
      AND r.detail_action = 'Borrowed Book'
    ORDER BY r.date_transaction DESC
";
$result = mysqli_query($con, $sql) or die(mysqli_error($con));
?>
<div class="page-title">
  <div class="title_left">
    <h3><small>Home /</small> Borrowed Books</h3>
  </div>
</div>
<div class="clearfix"></div>
<!-- zvnf, ,llnfkk
 <div input -->
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-users"></i> Book Lists</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li>
            <a href="borrowed_book_search_print.php" target="_blank" style="background:none;">
              <button type="button" class="btn btn-danger">
                <i class="fa fa-print"></i> Print
              </button>
            </a>
          </li>
        </ul>
        <div class="clearfix"></div>
        <p>
          Showing borrowed books from
          <strong><?php echo htmlspecialchars($dateFrom); ?></strong>
          to
          <strong><?php echo htmlspecialchars($dateTo); ?></strong>
        </p>
      </div>

      <div class="x_content">
        <div class="table-responsive">
          <table cellpadding="0" cellspacing="0" border="0"
                 class="table table-striped table-bordered" id="example">
            <thead>
              <tr>
                <th>Member Name</th>
                <th>Book Title</th>
                <th>Task</th>
                <th>Person In Charge</th>
                <th>Date Transaction</th>
              </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) {
              $name = trim($row['firstname'].' '.$row['middlename'].' '.$row['lastname']);
            ?>
              <tr>
                <td><?php echo htmlspecialchars($name); ?></td>
                <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                <td><?php echo htmlspecialchars($row['detail_action']); ?></td>
                <td><?php echo htmlspecialchars($row['admin_name']); ?></td>
                <td><?php echo date('M d, Y h:i:s a', strtotime($row['date_transaction'])); ?></td>
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



