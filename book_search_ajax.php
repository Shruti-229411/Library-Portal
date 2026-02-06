<?php
include('include/dbcon.php');

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

$sql = "SELECT book_id, book_title, author, category, status
        FROM book
        WHERE book_title LIKE ? OR author LIKE ?
        ORDER BY book_title ASC
        LIMIT 50";

$like = "%".$term."%";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ss", $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
exit;
