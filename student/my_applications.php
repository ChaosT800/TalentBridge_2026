<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

/* ==========================================
   GET STUDENT ID
========================================== */

$sql = "SELECT student_id
        FROM students
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

$student_id = $student['student_id'];

mysqli_stmt_close($stmt);

/* ==========================================
   FETCH APPLICATIONS
========================================== */

$sql = "SELECT

applications.application_id,
applications.application_status,
applications.applied_at,

jobs.job_title,
jobs.location,
jobs.salary_package,

companies.company_name

FROM applications

INNER JOIN jobs

ON applications.job_id=jobs.job_id

INNER JOIN companies

ON jobs.company_id=companies.company_id

WHERE applications.student_id=?

ORDER BY applications.applied_at DESC";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$student_id);

mysqli_stmt_execute($stmt);

$applications = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>My Applications</title>

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

<i class="bi bi-file-earmark-text-fill"></i>

My Applications

</h2>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-primary">

<tr>

<th>Job Title</th>

<th>Company</th>

<th>Location</th>

<th>Package</th>

<th>Status</th>

<th>Applied On</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($applications)>0)
{

while($row=mysqli_fetch_assoc($applications))
{

$status=$row['application_status'];

$badge="secondary";

if($status=="Applied")
$badge="primary";

if($status=="Shortlisted")
$badge="warning";

if($status=="Interview Scheduled")
$badge="info";

if($status=="Selected")
$badge="success";

if($status=="Rejected")
$badge="danger";

?>

<tr>

<td>

<?php echo htmlspecialchars($row['job_title']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['location']); ?>

</td>

<td>

₹<?php echo $row['salary_package']; ?> LPA

</td>

<td>

<span class="badge bg-<?php echo $badge; ?>">

<?php echo htmlspecialchars($status); ?>

</span>

</td>

<td>

<?php

echo date(
"d M Y",
strtotime($row['applied_at'])
);

?>

</td>

</tr>

<?php

}

}

else

{

?>

<tr>

<td colspan="6" class="text-center">

No applications found.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

</body>

</html>