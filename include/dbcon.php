<?php
$con = mysqli_connect("localhost", "root", "", "project_library");

if (!$con) {
    die("Database Connection Error: " . mysqli_connect_error());
}
?>
