<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
PLACEMENT SUMMARY
=========================================*/

$sql="SELECT

COUNT(DISTINCT s.student_id) total_students,

SUM(

CASE

WHEN a.application_status='Selected'

THEN 1

ELSE 0

END

) selected_students

FROM students s

LEFT JOIN applications a

ON s.student_id=a.student_id";

$row=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
);

$totalStudents=$row['total_students'];

$totalSelected=$row['selected_students'];

$placementRate=0;

if($totalStudents>0)
{

$placementRate=

($totalSelected/$totalStudents)*100;

}

/*=========================================
BRANCH REPORT
=========================================*/

$sql="SELECT

b.branch_name,

COUNT(DISTINCT s.student_id) students,

SUM(

CASE

WHEN a.application_status='Selected'

THEN 1

ELSE 0

END

) selected

FROM branches b

LEFT JOIN students s

ON b.branch_id=s.branch_id

LEFT JOIN applications a

ON s.student_id=a.student_id

GROUP BY b.branch_id

ORDER BY b.branch_name";

$branchReport=mysqli_query($conn,$sql);

/*=========================================
COMPANY REPORT
=========================================*/

$sql="SELECT

c.company_name,

COUNT(

CASE

WHEN a.application_status='Selected'

THEN 1

END

) hires

FROM companies c

LEFT JOIN jobs j

ON c.company_id=j.company_id

LEFT JOIN applications a

ON j.job_id=a.job_id

GROUP BY c.company_id

ORDER BY hires DESC";

$companyReport=mysqli_query($conn,$sql);

/*=========================================
RECENT PLACEMENTS
=========================================*/

$sql="SELECT

u.full_name,

b.branch_name,

c.company_name,

j.job_title,

a.applied_at

FROM applications a

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN users u

ON s.user_id=u.user_id

INNER JOIN branches b

ON s.branch_id=b.branch_id

INNER JOIN jobs j

ON a.job_id=j.job_id

INNER JOIN companies c

ON j.company_id=c.company_id

WHERE

a.application_status='Selected'

ORDER BY

a.applied_at DESC

LIMIT 10";

$recentPlacements=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Reports

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

Placement Reports

</h2>

<div class="row">

<div class="col-md-4">

<div class="stat-card">

<h3><?php echo $totalStudents; ?></h3>

<p>Total Students</p>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<h3><?php echo $totalSelected; ?></h3>

<p>Placed Students</p>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<h3><?php echo number_format($placementRate,2); ?>%</h3>

<p>Placement Rate</p>

</div>

</div>

</div>

<hr class="my-5">

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Branch-wise Placement Report

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Branch</th>

<th>Total Students</th>

<th>Placed</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($branchReport))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['branch_name']); ?>

</td>

<td>

<?php echo $row['students']; ?>

</td>

<td>

<span class="badge bg-success">

<?php echo $row['selected']; ?>

</span>

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

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Company-wise Hiring Report

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Company</th>

<th>Students Hired</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($companyReport))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<span class="badge bg-primary">

<?php echo $row['hires']; ?>

</span>

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

<hr class="my-5">

<div class="dashboard-card">

<div class="d-flex justify-content-between align-items-center mb-3">

<h4>

Recent Placements

</h4>

<a

href="export_report.php"

class="btn btn-success">

<i class="bi bi-download"></i>

Export CSV

</a>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead class="table-dark">

<tr>

<th>Student</th>

<th>Branch</th>

<th>Company</th>

<th>Job Title</th>

<th>Selection Date</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($recentPlacements)>0)
{

while($row=mysqli_fetch_assoc($recentPlacements))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['branch_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['job_title']); ?>

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

<td

colspan="5"

class="text-center text-muted py-5">

No placement records found.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<style>

.stat-card{

background:#ffffff;

padding:22px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

text-align:center;

transition:.3s;

}

.stat-card:hover{

transform:translateY(-5px);

}

.dashboard-card{

background:#ffffff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

margin-bottom:20px;

}

.table{

margin-bottom:0;

}

.badge{

font-size:13px;

padding:8px 12px;

}

</style>

</div> <!-- container-fluid -->

</div> <!-- main-content -->

</div> <!-- dashboard-layout -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>

<?php

mysqli_close($conn);

?>