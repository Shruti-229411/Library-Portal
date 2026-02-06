<?php
include('include/dbcon.php');
include('member_session.php');

// fetch all ebooks for members
$ebooks = mysqli_query(
    $con,
    "SELECT ebook_id,
            title,
            author,
            file_path,
            cover_image,
            CASE
                WHEN allowed_plan_id IS NULL THEN 'Free'
                ELSE 'Monthly Plan (Subscription)'
            END AS access_type
     FROM ebooks"
) or die(mysqli_error($con));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Member E‑Books</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="container" style="margin-top:30px;">
    <h3>Available E‑Books</h3>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>Access Type</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while ($row = mysqli_fetch_assoc($ebooks)) {
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td>
                    <?php if (!empty($row['cover_image'])) { ?>
                        <img src="upload/ebooks_covers/<?php echo htmlspecialchars($row['cover_image']); ?>"
                             alt="Cover"
                             style="height:60px;width:auto;">
                    <?php } else { ?>
                        No image
                    <?php } ?>
                </td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo htmlspecialchars($row['access_type']); ?></td>
                <td>
                    <a href="download_ebook.php?id=<?php echo $row['ebook_id']; ?>"
                       class="btn btn-info btn-sm" target="_blank">
                        View
                    </a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
