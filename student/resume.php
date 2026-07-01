<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*==========================================
GET STUDENT
==========================================*/

$sql="SELECT

roll_number,
resume,
updated_at

FROM students

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$student=mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Resume</title>

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

<div class="dashboard-card">

<h2 class="mb-4">

<i class="bi bi-file-earmark-pdf-fill text-danger"></i>

Resume Management

</h2>

<?php

if(isset($_GET['success']))
{

?>

<div class="alert alert-success">

<?php echo $_GET['success']; ?>

</div>

<?php

}

?>

<?php

if(isset($_GET['error']))
{

?>

<div class="alert alert-danger">

<?php echo $_GET['error']; ?>

</div>

<?php

}

?>

<?php

if(!empty($student['resume']))
{

?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill"></i>

Resume Uploaded Successfully

</div>

<table class="table">

<tr>

<th width="200">

Current Resume

</th>

<td>

<?php echo basename($student['resume']); ?>

</td>

</tr>

<tr>

<th>

Last Updated

</th>

<td>

<?php

echo date(

"d M Y h:i A",

strtotime($student['updated_at'])

);

?>

</td>

</tr>

</table>

<div class="mt-4">

<a

href="../<?php echo $student['resume']; ?>"

target="_blank"

class="btn btn-primary">

<i class="bi bi-eye-fill"></i>

View Resume

</a>

<a

href="../<?php echo $student['resume']; ?>"

download

class="btn btn-success">

<i class="bi bi-download"></i>

Download

</a>

</div>

<hr>

<?php

}

else

{

?>

<div class="alert alert-warning">

No resume uploaded yet.

</div>

<?php

}

?>

<h4 class="mt-4">

Upload / Replace Resume

</h4>

<form

action="update_resume.php"

method="POST"

enctype="multipart/form-data"

>

<div class="mb-4">

<input

type="file"

name="resume"

accept=".pdf"

class="form-control"

required

>

<small class="text-muted">

Only PDF files are allowed (Maximum 2MB)

</small>

</div>

<button

class="btn btn-primary"

type="submit"

>

<i class="bi bi-upload"></i>

Upload Resume

</button>

</form>

</div>

</div>

</div>

</div>

</body>

</html>