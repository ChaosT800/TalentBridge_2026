<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*=========================================
VERIFY COMPANY
=========================================*/

$sql="SELECT

company_id,
company_name

FROM companies

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user_id
);

mysqli_stmt_execute($stmt);

$company=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

if(!$company)
{
    header("Location: dashboard.php");
    exit();
}

$company_id=$company['company_id'];

/*=========================================
TOTAL JOBS
=========================================*/

$sql="SELECT COUNT(*) total

FROM jobs

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalJobs=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
TOTAL APPLICATIONS
=========================================*/

$sql="SELECT COUNT(*) total

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalApplications=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
SHORTLISTED
=========================================*/

$sql="SELECT COUNT(*) total

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE

j.company_id=?

AND

a.application_status='Shortlisted'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalShortlisted=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
INTERVIEWS
=========================================*/

$sql="SELECT COUNT(*) total

FROM interviews i

INNER JOIN applications a

ON i.application_id=a.application_id

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalInterviews=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
SELECTED
=========================================*/

$sql="SELECT COUNT(*) total

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE

j.company_id=?

AND

a.application_status='Selected'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalSelected=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
REJECTED
=========================================*/

$sql="SELECT COUNT(*) total

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE

j.company_id=?

AND

a.application_status='Rejected'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$totalRejected=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
)['total'];

mysqli_stmt_close($stmt);

/*=========================================
AVERAGE MATCH SCORE
=========================================*/

$sql="SELECT

AVG(match_score) average_score

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$row=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

$averageScore = number_format($row['average_score'] ?? 0, 2);

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

Company Analytics

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

Analytics Dashboard

</h2>

<div class="row g-4">

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalJobs; ?></h3>

<p>Total Jobs</p>

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

<h3><?php echo $totalShortlisted; ?></h3>

<p>Shortlisted</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalInterviews; ?></h3>

<p>Interviews</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalSelected; ?></h3>

<p>Selected</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $totalRejected; ?></h3>

<p>Rejected</p>

</div>

</div>

<div class="col-md-3">

<div class="stat-card">

<h3><?php echo $averageScore; ?>%</h3>

<p>Average Match</p>

</div>

</div>

</div>

<hr class="my-5">

<?php

/*=========================================
JOB-WISE APPLICATIONS
=========================================*/

$sql="SELECT

j.job_title,

COUNT(a.application_id) total

FROM jobs j

LEFT JOIN applications a

ON j.job_id=a.job_id

WHERE j.company_id=?

GROUP BY j.job_id

ORDER BY total DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$jobStats=mysqli_stmt_get_result($stmt);

/*=========================================
BRANCH-WISE APPLICATIONS
=========================================*/

$sql="SELECT

b.branch_name,

COUNT(a.application_id) total

FROM applications a

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN branches b

ON s.branch_id=b.branch_id

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?

GROUP BY b.branch_id

ORDER BY total DESC";

$stmt2=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt2,
"i",
$company_id
);

mysqli_stmt_execute($stmt2);

$branchStats=mysqli_stmt_get_result($stmt2);

?>

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Applications Per Job

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Job Title</th>

<th>Total Applications</th>

</tr>

</thead>

<tbody>

<?php

while($row=mysqli_fetch_assoc($jobStats))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['job_title']); ?>

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

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Branch-wise Applicants

</h4>

<div class="table-responsive">

<table class="table table-striped">

<thead class="table-dark">

<tr>

<th>Branch</th>

<th>Applications</th>

</tr>

</thead>

<tbody>

<?php

$chartLabels=[];

$chartValues=[];

mysqli_data_seek($branchStats,0);

while($row=mysqli_fetch_assoc($branchStats))
{

$chartLabels[]=$row['branch_name'];

$chartValues[]=$row['total'];

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

</div>

<hr class="my-5">

<div class="row">

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Applications by Branch

</h4>

<canvas id="branchChart"></canvas>

</div>

</div>

<div class="col-lg-6">

<div class="dashboard-card">

<h4 class="mb-3">

Application Status

</h4>

<canvas id="statusChart"></canvas>

</div>

</div>

</div>

<?php

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt2);

$statusData=[
$totalShortlisted,
$totalInterviews,
$totalSelected,
$totalRejected
];

?>

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

}

</style>

<script>

const branchChart=new Chart(

document.getElementById("branchChart"),

{

type:"bar",

data:{

labels:<?php echo json_encode($chartLabels); ?>,

datasets:[{

label:"Applications",

data:<?php echo json_encode($chartValues); ?>,

backgroundColor:"#0d6efd",

borderColor:"#0a58ca",

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

const statusChart=new Chart(

document.getElementById("statusChart"),

{

type:"doughnut",

data:{

labels:[

"Shortlisted",

"Interview",

"Selected",

"Rejected"

],

datasets:[{

data:<?php echo json_encode($statusData); ?>,

backgroundColor:[
"#0d6efd",
"#ffc107",
"#198754",
"#dc3545"
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