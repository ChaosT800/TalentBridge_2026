<?php

session_start();

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

if(!isset($_GET['id']))
{
    header("Location: notifications.php");
    exit();
}

$id = intval($_GET['id']);

$user_id = $_SESSION['user_id'];

$sql="UPDATE notifications

SET is_read=1

WHERE notification_id=?

AND user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ii",

$id,

$user_id

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: notifications.php");

exit();

?>