<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*==========================================
GET COMPANY
==========================================*/

$sql="SELECT company_id

FROM companies

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$company=mysqli_fetch_assoc($result);

$company_id=$company['company_id'];

/*==========================================
JOB COUNT
==========================================*/

$sql="SELECT COUNT(*) total

FROM jobs

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$company_id);

mysqli_stmt_execute($stmt);

$totalJobs=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

/*==========================================
APPLICATION COUNT
==========================================*/

$sql="SELECT COUNT(*) total

FROM applications

INNER JOIN jobs

ON applications.job_id=jobs.job_id

WHERE jobs.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$company_id);

mysqli_stmt_execute($stmt);

$totalApplications=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

/*==========================================
INTERVIEWS
==========================================*/

$sql="SELECT COUNT(*) total

FROM interviews

INNER JOIN applications

ON interviews.application_id=applications.application_id

INNER JOIN jobs

ON applications.job_id=jobs.job_id

WHERE jobs.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$company_id);

mysqli_stmt_execute($stmt);

$totalInterviews=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Company Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<div class="row g-4">

<div class="col-lg-4">

<div class="stat-card">

<h2><?php echo $totalJobs; ?></h2>

<p>Jobs Posted</p>

</div>

</div>

<div class="col-lg-4">

<div class="stat-card">

<h2><?php echo $totalApplications; ?></h2>

<p>Applications</p>

</div>

</div>

<div class="col-lg-4">

<div class="stat-card">

<h2><?php echo $totalInterviews; ?></h2>

<p>Interviews</p>

</div>

</div>

</div>

<div class="row mt-4">

<div class="col-lg-12">

<div class="dashboard-card">

<h4>Recent Job Posts</h4>

<hr>

<?php

$sql="SELECT job_title,status,created_at

FROM jobs

WHERE company_id=?

ORDER BY created_at DESC

LIMIT 5";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$company_id);

mysqli_stmt_execute($stmt);

$jobs=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($jobs)>0)
{

while($job=mysqli_fetch_assoc($jobs))
{

?>

<div class="d-flex justify-content-between align-items-center py-2">

<div>

<strong><?php echo htmlspecialchars($job['job_title']); ?></strong>

<br>

<small class="text-muted">

<?php echo date("d M Y",strtotime($job['created_at'])); ?>

</small>

</div>

<span class="badge bg-success">

<?php echo htmlspecialchars($job['status']); ?>

</span>

</div>

<hr>

<?php

}

}
else
{

echo "<p>No jobs posted yet.</p>";

}

?>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>