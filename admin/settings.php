<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

$message="";

if($_SERVER['REQUEST_METHOD']=="POST")
{

$password=$_POST['password'];

$confirm=$_POST['confirm_password'];

if($password!=$confirm)
{

$message="Passwords do not match.";

}
elseif(strlen($password)<6)
{

$message="Password must contain at least 6 characters.";

}
else
{

$hash=password_hash($password,PASSWORD_DEFAULT);

$sql="UPDATE users

SET password=?

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"si",

$hash,

$user_id

);

if(mysqli_stmt_execute($stmt))
{

$message="Password updated successfully.";

}
else
{

$message="Unable to update password.";

}

mysqli_stmt_close($stmt);

}

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Admin Settings

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
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

<div class="container mt-4">

<div class="dashboard-card">

<h2 class="mb-4">

Settings

</h2>

<?php

if($message!="")
{

?>

<div class="alert alert-info">

<?php echo htmlspecialchars($message); ?>

</div>

<?php

}

?>

<form method="POST">

<div class="mb-3">

<label class="form-label">

New Password

</label>

<input

type="password"

name="password"

class="form-control"

required>

</div>

<div class="mb-3">

<label class="form-label">

Confirm Password

</label>

<input

type="password"

name="confirm_password"

class="form-control"

required>

</div>

<button

class="btn btn-primary">

Update Password

</button>

</form>

</div>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#fff;

padding:30px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php

mysqli_close($conn);

?>