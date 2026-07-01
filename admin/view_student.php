<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_students.php");
    exit();
}

$student_id=intval($_GET['id']);

/*=========================================
FETCH STUDENT
=========================================*/

$sql="SELECT

u.full_name,

u.email,

u.phone,

u.account_status,

s.roll_number,

s.cgpa,

s.backlogs,

s.graduation_year,

s.resume,

s.profile_photo,

s.about,

s.github,

s.linkedin,

b.branch_name

FROM students s

INNER JOIN users u

ON s.user_id=u.user_id

LEFT JOIN branches b

ON s.branch_id=b.branch_id

WHERE s.student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: manage_students.php");
    exit();
}

$student=mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=========================================
SKILLS
=========================================*/

$sql="SELECT

skills.skill_name,

student_skills.proficiency

FROM student_skills

INNER JOIN skills

ON student_skills.skill_id=skills.skill_id

WHERE student_skills.student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);

mysqli_stmt_execute($stmt);

$skills=mysqli_stmt_get_result($stmt);

/*=========================================
APPLICATION SUMMARY
=========================================*/

$sql="SELECT

COUNT(*) total,

SUM(application_status='Selected') selected,

SUM(application_status='Rejected') rejected,

SUM(application_status='Interview Scheduled') interviews

FROM applications

WHERE student_id=?";

$stmt2=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt2,
"i",
$student_id
);

mysqli_stmt_execute($stmt2);

$summary=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt2)
);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Student Profile

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

<div class="dashboard-card">

<div class="row">

<div class="col-md-3 text-center">

<?php

if(!empty($student['profile_photo']))
{

?>

<img

src="../<?php echo htmlspecialchars($student['profile_photo']); ?>"

class="img-fluid rounded-circle"

style="width:180px;height:180px;object-fit:cover;">

<?php

}
else
{

?>

<img

src="../assets/images/default-user.png"

class="img-fluid rounded-circle"

style="width:180px;height:180px;">

<?php

}

?>

<h3 class="mt-3">

<?php echo htmlspecialchars($student['full_name']); ?>

</h3>

<span class="badge bg-success">

<?php echo ucfirst($student['account_status']); ?>

</span>

</div>

<div class="col-md-9">

<h4 class="mb-3">

Student Information

</h4>

<table class="table table-bordered">

<tr>

<th>Email</th>

<td><?php echo htmlspecialchars($student['email']); ?></td>

</tr>

<tr>

<th>Phone</th>

<td><?php echo htmlspecialchars($student['phone']); ?></td>

</tr>

<tr>

<th>Roll Number</th>

<td><?php echo htmlspecialchars($student['roll_number']); ?></td>

</tr>

<tr>

<th>Branch</th>

<td><?php echo htmlspecialchars($student['branch_name']); ?></td>

</tr>

<tr>

<th>CGPA</th>

<td><?php echo $student['cgpa']; ?></td>

</tr>

<tr>

<th>Backlogs</th>

<td><?php echo $student['backlogs']; ?></td>

</tr>

<tr>

<th>Graduation</th>

<td><?php echo $student['graduation_year']; ?></td>

</tr>

<tr>

<th>About</th>

<td>

<?php

echo !empty($student['about'])

? nl2br(htmlspecialchars($student['about']))

: "<span class='text-muted'>Not Provided</span>";

?>

</td>

</tr>

<tr>

<th>GitHub</th>

<td>

<?php

if(!empty($student['github']))
{

?>

<a

href="<?php echo htmlspecialchars($student['github']); ?>"

target="_blank">

<?php echo htmlspecialchars($student['github']); ?>

</a>

<?php

}
else
{

echo "<span class='text-muted'>Not Provided</span>";

}

?>

</td>

</tr>

<tr>

<th>LinkedIn</th>

<td>

<?php

if(!empty($student['linkedin']))
{

?>

<a

href="<?php echo htmlspecialchars($student['linkedin']); ?>"

target="_blank">

<?php echo htmlspecialchars($student['linkedin']); ?>

</a>

<?php

}
else
{

echo "<span class='text-muted'>Not Provided</span>";

}

?>

</td>

</tr>

<tr>

<th>Resume</th>

<td>

<?php

if(!empty($student['resume']))
{

?>

<a

href="../<?php echo htmlspecialchars($student['resume']); ?>"

target="_blank"

class="btn btn-success btn-sm">

<i class="bi bi-download"></i>

Download Resume

</a>

<?php

}
else
{

echo "<span class='text-muted'>No Resume Uploaded</span>";

}

?>

</td>

</tr>

</table>

<h4 class="mt-4">

Skills

</h4>

<?php

if(mysqli_num_rows($skills)>0)
{

while($skill=mysqli_fetch_assoc($skills))
{

?>

<span class="badge bg-primary me-2 mb-2">

<?php

echo htmlspecialchars($skill['skill_name']);

?>

-

<?php

echo htmlspecialchars($skill['proficiency']);

?>

</span>

<?php

}

}
else
{

?>

<p class="text-muted">

No Skills Added

</p>

<?php

}

?>

<div class="row mt-4">

<div class="col-md-3">

<div class="stat-card">

<h4>

<?php echo $summary['total'] ?? 0; ?>

</h4>

<p>Total Applications</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h4>

<?php echo $summary['selected'] ?? 0; ?>

</h4>

<p>Selected</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h4>

<?php echo $summary['interviews'] ?? 0; ?>

</h4>

<p>Interviews</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h4>

<?php echo $summary['rejected'] ?? 0; ?>

</h4>

<p>Rejected</p>

</div>

</div>

</div>

<div class="mt-4">

<a

href="manage_students.php"

class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

</div>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#fff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.stat-card{

background:#f8f9fa;

padding:18px;

border-radius:10px;

text-align:center;

box-shadow:0 2px 8px rgba(0,0,0,.08);

}

.badge{

font-size:13px;

padding:8px 12px;

}

table th{

width:180px;

}

</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php

mysqli_stmt_close($stmt);

mysqli_stmt_close($stmt2);

mysqli_close($conn);

?>