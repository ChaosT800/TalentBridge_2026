<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*=========================================
MARK AS READ
=========================================*/

if(isset($_GET['read']) && is_numeric($_GET['read']))
{

$id=intval($_GET['read']);

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

}

/*=========================================
FETCH NOTIFICATIONS
=========================================*/

$sql="SELECT

notification_id,

title,

message,

notification_type,

is_read,

created_at,

job_id,

application_id

FROM notifications

WHERE user_id=?

ORDER BY

created_at DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"i",

$user_id

);

mysqli_stmt_execute($stmt);

$notifications=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Admin Notifications

</title>

<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">

<link

href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"

rel="stylesheet">

<link

rel="stylesheet"

href="../assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<h2 class="mb-4">

Notifications

</h2>

<div class="dashboard-card">

<?php

if(mysqli_num_rows($notifications)>0)
{

while($row=mysqli_fetch_assoc($notifications))
{

?>

<div class="card mb-3 shadow-sm">

<div class="card-body">

<div class="d-flex justify-content-between">

<div>

<h5>

<i class="bi bi-bell-fill text-primary"></i>

<?php echo htmlspecialchars($row['title']); ?>

</h5>

<p>

<?php echo htmlspecialchars($row['message']); ?>

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

<div class="text-end">

<?php

if($row['is_read'])
{

?>

<span class="badge bg-success">

Read

</span>

<?php

}
else
{

?>

<span class="badge bg-danger">

Unread

</span>

<?php

}

?>

<br><br>

<?php

$typeColor="secondary";

switch($row['notification_type'])
{

case "Application":

$typeColor="primary";

break;

case "Interview":

$typeColor="warning";

break;

case "Shortlisted":

$typeColor="info";

break;

case "Rejected":

$typeColor="danger";

break;

}

?>

<span class="badge bg-<?php echo $typeColor; ?>">

<?php echo htmlspecialchars($row['notification_type']); ?>

</span>

<br><br>

<?php

if(!$row['is_read'])
{

?>

<a

href="notifications.php?read=<?php echo $row['notification_id']; ?>"

class="btn btn-outline-success btn-sm">

<i class="bi bi-check-circle"></i>

Mark as Read

</a>

<?php

}
else
{

?>

<button

class="btn btn-success btn-sm"

disabled>

<i class="bi bi-check-circle-fill"></i>

Already Read

</button>

<?php

}

?>

</div>

</div>

</div>

</div>

<?php

}

}
else
{

?>

<div class="text-center py-5">

<i class="bi bi-bell-slash fs-1 text-muted"></i>

<h4 class="mt-3">

No Notifications

</h4>

<p class="text-muted">

There are no notifications available.

</p>

</div>

<?php

}

?>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#ffffff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.card{

border:none;

border-left:5px solid #0d6efd;

transition:.25s;

}

.card:hover{

transform:translateY(-3px);

box-shadow:0 10px 25px rgba(0,0,0,.12);

}

.badge{

font-size:13px;

padding:8px 12px;

}

</style>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>

<?php

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>