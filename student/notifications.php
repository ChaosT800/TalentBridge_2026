<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

/*==========================================
GET NOTIFICATIONS
==========================================*/

$sql="SELECT *

FROM notifications

WHERE user_id=?

ORDER BY created_at DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$notifications=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Notifications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<div class="dashboard-card">

<h2 class="mb-4">

<i class="bi bi-bell-fill"></i>

Notifications

</h2>

<?php

if(mysqli_num_rows($notifications)>0)
{

while($row=mysqli_fetch_assoc($notifications))
{

?>

<div class="notification-item <?php echo $row['is_read']?'':'unread'; ?>">

<div class="d-flex justify-content-between">

<div>

<h5>

<?php

echo htmlspecialchars($row['title']);

?>

</h5>

<p>

<?php

echo htmlspecialchars($row['message']);

?>

</p>

<small class="text-muted">

<?php

echo date(

"d M Y h:i A",

strtotime($row['created_at'])

);

?>

</small>

</div>

<div>

<?php

if(!$row['is_read'])

{

?>

<a

href="mark_notification_read.php?id=<?php echo $row['notification_id']; ?>"

class="btn btn-sm btn-primary">

Mark Read

</a>

<?php

}

?>

</div>

</div>

</div>

<hr>

<?php

}

}

else

{

?>

<div class="alert alert-info">

No notifications available.

</div>

<?php

}

?>

</div>

</div>

</div>

</div>

</body>

</html>