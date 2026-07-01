<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
TOTAL STUDENTS
=========================================*/

$sql="SELECT COUNT(*) total
FROM students";

$totalStudents=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

/*=========================================
PLACED STUDENTS
=========================================*/

$sql="SELECT COUNT(DISTINCT student_id) total

FROM applications

WHERE application_status='Selected'";

$totalPlaced=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
)['total'];

$placementPercentage=0;

if($totalStudents>0)
{

$placementPercentage=

($totalPlaced/$totalStudents)*100;

}

/*=========================================
AVERAGE CGPA OF PLACED STUDENTS
=========================================*/

$sql="SELECT

AVG(s.cgpa) avgcgpa

FROM students s

INNER JOIN applications a

ON s.student_id=a.student_id

WHERE

a.application_status='Selected'";

$row=mysqli_fetch_assoc(
mysqli_query($conn,$sql)
);

$averageCGPA=number_format(
$row['avgcgpa'] ?? 0,
2
);

/*=========================================
BRANCH PLACEMENTS
=========================================*/

$sql="SELECT

b.branch_name,

COUNT(DISTINCT s.student_id) total

FROM branches b

LEFT JOIN students s

ON b.branch_id=s.branch_id

LEFT JOIN applications a

ON s.student_id=a.student_id

AND a.application_status='Selected'

GROUP BY b.branch_id

ORDER BY total DESC";

$branchStats=mysqli_query($conn,$sql);

/*=========================================
COMPANY HIRING
=========================================*/

$sql="SELECT

c.company_name,

COUNT(*) total

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

INNER JOIN companies c

ON j.company_id=c.company_id

WHERE

a.application_status='Selected'

GROUP BY c.company_id

ORDER BY total DESC";

$companyStats=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Placement Statistics

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<h2 class="mb-4">

Placement Statistics

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

<h3><?php echo $totalPlaced; ?></h3>

<p>Placed Students</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo number_format($placementPercentage,2); ?>%</h3>

<p>Placement Rate</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $averageCGPA; ?></h3>

<p>Average CGPA</p>

</div>

</div>

</div>

<hr class="my-5">

<?php

/*=========================================
PREPARE CHART DATA
=========================================*/

$branchLabels=[];
$branchValues=[];

mysqli_data_seek($branchStats,0);

while($row=mysqli_fetch_assoc($branchStats))
{

$branchLabels[]=$row['branch_name'];

$branchValues[]=$row['total'];

}

$companyLabels=[];
$companyValues=[];

mysqli_data_seek($companyStats,0);

while($row=mysqli_fetch_assoc($companyStats))
{

$companyLabels[]=$row['company_name'];

$companyValues[]=$row['total'];

}

mysqli_data_seek($branchStats,0);
mysqli_data_seek($companyStats,0);

?>

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Branch-wise Placements

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Branch</th>

<th>Placed Students</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($branchStats))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['branch_name']); ?>

</td>

<td>

<span class="badge bg-success">

<?php echo $row['total']; ?>

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

Company-wise Hiring

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

while($row=mysqli_fetch_assoc($companyStats))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<span class="badge bg-primary">

<?php echo $row['total']; ?>

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

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Placements by Branch

</h4>

<canvas id="branchChart"></canvas>

</div>

</div>

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Company Hiring Distribution

</h4>

<canvas id="companyChart"></canvas>

</div>

</div>

</div>

<?php

if(empty($branchLabels))
{
    $branchLabels=["No Data"];
    $branchValues=[0];
}

if(empty($companyLabels))
{
    $companyLabels=["No Data"];
    $companyValues=[0];
}

?>

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

canvas{

max-height:330px;

}

</style>

<script>

const branchChart=new Chart(

document.getElementById("branchChart"),

{

type:"bar",

data:{

labels:<?php echo json_encode($branchLabels); ?>,

datasets:[{

label:"Placed Students",

data:<?php echo json_encode($branchValues); ?>,

backgroundColor:"#198754",

borderColor:"#157347",

borderWidth:1

}]

},

options:{

responsive:true,

plugins:{

legend:{

display:false

}

}

}

}

);

const companyChart=new Chart(

document.getElementById("companyChart"),

{

type:"doughnut",

data:{

labels:<?php echo json_encode($companyLabels); ?>,

datasets:[{

data:<?php echo json_encode($companyValues); ?>,

backgroundColor:[

"#0d6efd",

"#198754",

"#ffc107",

"#dc3545",

"#6f42c1",

"#20c997",

"#fd7e14",

"#6c757d",

"#6610f2",

"#0dcaf0"

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