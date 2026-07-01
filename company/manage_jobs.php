<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

/*==================================================
GET COMPANY ID
==================================================*/

$sql = "SELECT company_id
        FROM companies
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$company = mysqli_fetch_assoc($result);

$company_id = $company['company_id'];

mysqli_stmt_close($stmt);

/*==================================================
FETCH JOBS
==================================================*/

$sql = "SELECT
            j.job_id,
            j.job_title,
            j.location,
            j.job_type,
            j.status,
            j.application_deadline,

            COUNT(a.application_id) AS applications

        FROM jobs j

        LEFT JOIN applications a

        ON j.job_id=a.job_id

        WHERE j.company_id=?

        GROUP BY j.job_id

        ORDER BY j.created_at DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$company_id);

mysqli_stmt_execute($stmt);

$jobs=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Manage Jobs</title>

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

<div class="dashboard-card">

<div class="d-flex justify-content-between mb-4">

<h2>

<i class="bi bi-briefcase-fill"></i>

Manage Jobs

</h2>

<a
href="post_job.php"
class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Post New Job

</a>

</div>

<?php

if(isset($_GET['success']))
{

?>

<div class="alert alert-success">

<?php echo htmlspecialchars($_GET['success']); ?>

</div>

<?php

}

?>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-primary">

<tr>

<th>Job</th>

<th>Location</th>

<th>Type</th>

<th>Applications</th>

<th>Status</th>

<th>Deadline</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($jobs)>0)
{

while($job=mysqli_fetch_assoc($jobs))
{

?>

<tr>

<td>

<strong>

<?php echo htmlspecialchars($job['job_title']); ?>

</strong>

</td>

<td>

<?php echo htmlspecialchars($job['location']); ?>

</td>

<td>

<?php echo htmlspecialchars($job['job_type']); ?>

</td>

<td>

<span class="badge bg-primary">

<?php echo $job['applications']; ?>

</span>

</td>

<td>

<span class="badge bg-success">

<?php echo htmlspecialchars($job['status']); ?>

</span>

</td>

<td>

<?php

echo date(

"d M Y",

strtotime($job['application_deadline'])

);

?>

</td>

<td>

<a

href="edit_job.php?id=<?php echo $job['job_id']; ?>"

class="btn btn-warning btn-sm">

<i class="bi bi-pencil"></i>

</a>

<a

href="delete_job.php?id=<?php echo $job['job_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this job?');">

<i class="bi bi-trash"></i>

</a>

<a

href="view_applicants.php?id=<?php echo $job['job_id']; ?>"

class="btn btn-success btn-sm">

<i class="bi bi-people-fill"></i>

</a>

</td>

</tr>

<?php

}

}

else

{

?>

<tr>

<td colspan="7" class="text-center">

No jobs found.

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