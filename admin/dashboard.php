<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*=========================================
TOTAL STUDENTS
=========================================*/

$sql="SELECT COUNT(*) total
FROM users
WHERE role='student'";

$totalStudents=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
TOTAL COMPANIES
=========================================*/

$sql="SELECT COUNT(*) total
FROM companies";

$totalCompanies=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
VERIFIED COMPANIES
=========================================*/

$sql="SELECT COUNT(*) total
FROM companies
WHERE verified=1";

$totalVerified=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
TOTAL JOBS
=========================================*/

$sql="SELECT COUNT(*) total
FROM jobs";

$totalJobs=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
OPEN JOBS
=========================================*/

$sql="SELECT COUNT(*) total
FROM jobs
WHERE status='Open'";

$totalOpenJobs=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
APPLICATIONS
=========================================*/

$sql="SELECT COUNT(*) total
FROM applications";

$totalApplications=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
SELECTED STUDENTS
=========================================*/

$sql="SELECT COUNT(*) total
FROM applications
WHERE application_status='Selected'";

$totalSelected=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
INTERVIEWS
=========================================*/

$sql="SELECT COUNT(*) total
FROM interviews";

$totalInterviews=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
LATEST STUDENTS
=========================================*/

$sql="SELECT

u.full_name,

u.email,

b.branch_name,

u.created_at

FROM students s

INNER JOIN users u

ON s.user_id=u.user_id

LEFT JOIN branches b

ON s.branch_id=b.branch_id

ORDER BY

u.created_at DESC

LIMIT 5";

$latestStudents=mysqli_query($conn,$sql);

/*=========================================
LATEST COMPANIES
=========================================*/

$sql="SELECT

company_name,

industry,

verified,

created_at

FROM companies

ORDER BY

created_at DESC

LIMIT 5";

$latestCompanies=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Admin Dashboard

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/dist/font/bootstrap-icons.css"
rel="stylesheet">

<link
rel="stylesheet"
href="../assets/css/dashboard.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<h2 class="mb-4">

Administrator Dashboard

</h2>

<div class="row g-4">

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalStudents; ?></h3>

<p>Total Students</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalCompanies; ?></h3>

<p>Total Companies</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalVerified; ?></h3>

<p>Verified Companies</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalJobs; ?></h3>

<p>Total Jobs</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalOpenJobs; ?></h3>

<p>Open Jobs</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalApplications; ?></h3>

<p>Applications</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalSelected; ?></h3>

<p>Selected Students</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalInterviews; ?></h3>

<p>Interviews</p>

</div>

</div>

</div>

<hr class="my-5">

<?php

/*=========================================
LATEST JOBS
=========================================*/

$sql="SELECT

j.job_title,

c.company_name,

j.status,

j.created_at

FROM jobs j

INNER JOIN companies c

ON j.company_id=c.company_id

ORDER BY

j.created_at DESC

LIMIT 5";

$latestJobs=mysqli_query($conn,$sql);

/*=========================================
APPLICATIONS PER MONTH
=========================================*/

$sql="SELECT

MONTH(applied_at) month_no,

MONTHNAME(applied_at) month_name,

COUNT(*) total

FROM applications

GROUP BY MONTH(applied_at)

ORDER BY MONTH(applied_at)";

$result=mysqli_query($conn,$sql);

$monthLabels=[];

$monthValues=[];

while($row=mysqli_fetch_assoc($result))
{

$monthLabels[]=$row['month_name'];

$monthValues[]=$row['total'];

}

/*=========================================
BRANCH DISTRIBUTION
=========================================*/

$sql="SELECT

b.branch_name,

COUNT(s.student_id) total

FROM branches b

LEFT JOIN students s

ON b.branch_id=s.branch_id

GROUP BY b.branch_id

ORDER BY total DESC";

$result=mysqli_query($conn,$sql);

$branchLabels=[];

$branchValues=[];

while($row=mysqli_fetch_assoc($result))
{

$branchLabels[]=$row['branch_name'];

$branchValues[]=$row['total'];

}

?>

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Latest Students

</h4>

<div class="table-responsive">

<table class="table table-hover">

<thead class="table-dark">

<tr>

<th>Name</th>

<th>Branch</th>

<th>Joined</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($latestStudents))
{

?>

<tr>

<td>

<strong>

<?php echo htmlspecialchars($row['full_name']); ?>

</strong>

<br>

<small>

<?php echo htmlspecialchars($row['email']); ?>

</small>

</td>

<td>

<?php echo htmlspecialchars($row['branch_name']); ?>

</td>

<td>

<?php echo date("d M Y",strtotime($row['created_at'])); ?>

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

Latest Companies

</h4>

<div class="table-responsive">

<table class="table table-hover">

<thead class="table-dark">

<tr>

<th>Company</th>

<th>Industry</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($latestCompanies))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['industry']); ?>

</td>

<td>

<?php

if($row['verified'])
{

echo "<span class='badge bg-success'>Verified</span>";

}
else
{

echo "<span class='badge bg-warning text-dark'>Pending</span>";

}

?>

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

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Latest Jobs

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Job</th>

<th>Company</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($latestJobs))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['job_title']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<span class="badge bg-<?php echo ($row['status']=="Open") ? "success" : "secondary"; ?>">

<?php echo $row['status']; ?>

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

System Analytics

</h4>

<canvas id="applicationsChart"></canvas>

<br>

<canvas id="branchesChart"></canvas>

</div>

</div>

</div>

<style>

.stat-card{

background:#fff;

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

background:#fff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

margin-bottom:20px;

}

.table{

margin-bottom:0;

}

canvas{

max-height:320px;

}

</style>

<?php

if(empty($monthLabels))
{
    $monthLabels=["No Data"];
    $monthValues=[0];
}

if(empty($branchLabels))
{
    $branchLabels=["No Data"];
    $branchValues=[0];
}

?>

<script>

const applicationsChart=new Chart(

document.getElementById("applicationsChart"),

{

type:"line",

data:{

labels:<?php echo json_encode($monthLabels); ?>,

datasets:[{

label:"Applications",

data:<?php echo json_encode($monthValues); ?>,

borderColor:"#0d6efd",

backgroundColor:"rgba(13,110,253,.15)",

fill:true,

tension:.35

}]

},

options:{

responsive:true,

plugins:{

legend:{

display:true

}

}

}

}

);

const branchesChart=new Chart(

document.getElementById("branchesChart"),

{

type:"doughnut",

data:{

labels:<?php echo json_encode($branchLabels); ?>,

datasets:[{

data:<?php echo json_encode($branchValues); ?>,

backgroundColor:[

"#0d6efd",

"#198754",

"#ffc107",

"#dc3545",

"#6f42c1",

"#20c997",

"#fd7e14",

"#6c757d"

]

}]

},

options:{

responsive:true

}

}

);

</script>

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