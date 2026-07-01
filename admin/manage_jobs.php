<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
DELETE JOB
=========================================*/

if(isset($_GET['delete']) && is_numeric($_GET['delete']))
{

$job_id=intval($_GET['delete']);

mysqli_begin_transaction($conn);

try
{

/* Delete Interviews */

$sql="DELETE interviews

FROM interviews

INNER JOIN applications

ON interviews.application_id=applications.application_id

WHERE applications.job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete Applications */

$sql="DELETE FROM applications

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete Job Branches */

$sql="DELETE FROM job_branches

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete Job */

$sql="DELETE FROM jobs

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

mysqli_commit($conn);

header("Location: manage_jobs.php?success=Job deleted.");

exit();

}
catch(Exception $e)
{

mysqli_rollback($conn);

header("Location: manage_jobs.php?error=".$e->getMessage());

exit();

}

}

/*=========================================
FILTERS
=========================================*/

$search="";

$status="";

if(isset($_GET['search']))
$search=trim($_GET['search']);

if(isset($_GET['status']))
$status=$_GET['status'];

/*=========================================
FETCH JOBS
=========================================*/

$sql="SELECT

j.job_id,

j.job_title,

j.location,

j.job_type,

j.salary_package,

j.status,

j.application_deadline,

c.company_name

FROM jobs j

INNER JOIN companies c

ON j.company_id=c.company_id

WHERE 1=1";

$params=[];

$types="";

if($search!="")
{

$sql.=" AND (

j.job_title LIKE ?

OR

c.company_name LIKE ?

)";

$types.="ss";

$like="%".$search."%";

$params[]=$like;

$params[]=$like;

}

if($status!="")
{

$sql.=" AND j.status=?";

$types.="s";

$params[]=$status;

}

$sql.="

ORDER BY

j.created_at DESC";

$stmt=mysqli_prepare($conn,$sql);

if(count($params)>0)
{

mysqli_stmt_bind_param(

$stmt,

$types,

...$params

);

}

mysqli_stmt_execute($stmt);

$jobs=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Manage Jobs

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

Manage Jobs

</h2>

<form
method="GET"
class="row g-3 mb-4">

<div class="col-md-5">

<input

type="text"

name="search"

class="form-control"

placeholder="Search Job or Company"

value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-3">

<select

name="status"

class="form-select">

<option value="">All Jobs</option>

<option value="Open"
<?php if($status=="Open") echo "selected"; ?>>

Open

</option>

<option value="Closed"
<?php if($status=="Closed") echo "selected"; ?>>

Closed

</option>

</select>

</div>

<div class="col-md-2">

<button
class="btn btn-primary w-100">

Search

</button>

</div>

<div class="col-md-2">

<a

href="manage_jobs.php"

class="btn btn-secondary w-100">

Reset

</a>

</div>

</form>

<div class="dashboard-card">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Job</th>

<th>Company</th>

<th>Type</th>

<th>Salary</th>

<th>Status</th>

<th>Deadline</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($jobs)>0)
{

while($row=mysqli_fetch_assoc($jobs))
{

?>

<tr>

<td>

<strong>

<?php echo htmlspecialchars($row['job_title']); ?>

</strong>

<br>

<small class="text-muted">

<?php echo htmlspecialchars($row['location']); ?>

</small>

</td>

<td>

<?php echo htmlspecialchars($row['company_name']); ?>

</td>

<td>

<span class="badge bg-info">

<?php echo htmlspecialchars($row['job_type']); ?>

</span>

</td>

<td>

<?php

if(!empty($row['salary_package']))
{

echo "₹ ".number_format($row['salary_package']);

}
else
{

echo "-";

}

?>

</td>

<td>

<?php

if($row['status']=="Open")
{

?>

<span class="badge bg-success">

Open

</span>

<?php

}
else
{

?>

<span class="badge bg-secondary">

Closed

</span>

<?php

}

?>

</td>

<td>

<?php

echo date(

"d M Y",

strtotime($row['application_deadline'])

);

?>

</td>

<td>

<a

href="../company/view_job.php?id=<?php echo $row['job_id']; ?>"

class="btn btn-primary btn-sm mb-1">

<i class="bi bi-eye"></i>

View

</a>

<br>

<a

href="../company/edit_job.php?id=<?php echo $row['job_id']; ?>"

class="btn btn-warning btn-sm mb-1">

<i class="bi bi-pencil-square"></i>

Edit

</a>

<br>

<a

href="manage_jobs.php?delete=<?php echo $row['job_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this job permanently?');">

<i class="bi bi-trash"></i>

Delete

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

<td

colspan="7"

class="text-center text-muted py-5">

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

<style>

.dashboard-card{

background:#ffffff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

table td{

vertical-align:middle;

}

.badge{

font-size:13px;

padding:8px 12px;

}

.btn{

min-width:90px;

margin-bottom:5px;

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

if(isset($stmt))
{
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

?>