<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

$message="";

/*=========================================
UPDATE PROFILE
=========================================*/

if($_SERVER['REQUEST_METHOD']=="POST")
{

$full_name=trim($_POST['full_name']);

$email=trim($_POST['email']);

$phone=trim($_POST['phone']);

$sql="UPDATE users

SET

full_name=?,
email=?,
phone=?

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"sssi",

$full_name,

$email,

$phone,

$user_id

);

if(mysqli_stmt_execute($stmt))
{

$message="Profile updated successfully.";

}
else
{

$message="Unable to update profile.";

}

mysqli_stmt_close($stmt);

}

/*=========================================
FETCH ADMIN
=========================================*/

$sql="SELECT

full_name,

email,

phone,

role,

account_status,

created_at

FROM users

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"i",

$user_id

);

mysqli_stmt_execute($stmt);

$admin=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Admin Profile

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

<div class="container mt-4">

<div class="dashboard-card">

<h2 class="mb-4">

My Profile

</h2>

<?php

if($message!="")
{

?>

<div class="alert alert-success">

<?php echo htmlspecialchars($message); ?>

</div>

<?php

}

?>

<form method="POST">

<div class="mb-3">

<label class="form-label">

Full Name

</label>

<input

type="text"

name="full_name"

class="form-control"

required

value="<?php echo htmlspecialchars($admin['full_name']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

Email

</label>

<input

type="email"

name="email"

class="form-control"

required

value="<?php echo htmlspecialchars($admin['email']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

Phone

</label>

<input

type="text"

name="phone"

class="form-control"

value="<?php echo htmlspecialchars($admin['phone']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

Role

</label>

<input

type="text"

class="form-control"

readonly

value="<?php echo ucfirst($admin['role']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

Account Status

</label>

<input

type="text"

class="form-control"

readonly

value="<?php echo ucfirst($admin['account_status']); ?>">

</div>

<div class="mb-3">

<label class="form-label">

Member Since

</label>

<input

type="text"

class="form-control"

readonly

value="<?php echo date("d M Y",strtotime($admin['created_at'])); ?>">

</div>

<button

class="btn btn-primary">

<i class="bi bi-check-circle"></i>

Update Profile

</button>

</form>

</div>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#ffffff;

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