<?php

session_start();

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*=========================================
GET COMPANY ID
=========================================*/

$sql="SELECT company_id

FROM companies

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{

header("Location: dashboard.php");

exit();

}

$company=mysqli_fetch_assoc($result);

$company_id=$company['company_id'];

mysqli_stmt_close($stmt);

/*=========================================
FETCH INTERVIEWS
=========================================*/

$sql="SELECT

i.interview_id,

i.interview_date,

i.interview_time,

i.mode,

i.venue,

i.meeting_link,

i.remarks,

a.application_id,

u.full_name,

j.job_title

FROM interviews i

INNER JOIN applications a

ON i.application_id=a.application_id

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN users u

ON s.user_id=u.user_id

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?

ORDER BY

i.interview_date DESC,

i.interview_time DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$interviews=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Interviews

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

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

<i class="bi bi-calendar-check"></i>

Scheduled Interviews

</h2>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Student</th>

<th>Job</th>

<th>Date</th>

<th>Time</th>

<th>Mode</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($interviews)>0)
{

while($row=mysqli_fetch_assoc($interviews))
{

?>

<tr>

<td>

<?php echo htmlspecialchars($row['full_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['job_title']); ?>

</td>

<td>

<?php

echo date(

"d M Y",

strtotime($row['interview_date'])

);

?>

</td>

<td>

<?php

echo date(

"h:i A",

strtotime($row['interview_time'])

);

?>

</td>

<td>

<?php

if($row['mode']=="Online")
{

?>

<span class="badge bg-primary">

Online

</span>

<?php

}
else
{

?>

<span class="badge bg-success">

Offline

</span>

<?php

}

?>

</td>

<td>

<button

class="btn btn-info btn-sm mb-1"

data-bs-toggle="modal"

data-bs-target="#modal<?php echo $row['interview_id']; ?>">

<i class="bi bi-eye"></i>

Details

</button>

<br>

<a

href="view_applicants.php?id=<?php echo $row['application_id']; ?>"

class="btn btn-success btn-sm">

<i class="bi bi-person"></i>

Applicant

</a>

</td>

</tr>

<div

class="modal fade"

id="modal<?php echo $row['interview_id']; ?>"

tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">

Interview Details

</h5>

<button

type="button"

class="btn-close"

data-bs-dismiss="modal">

</button>

</div>

<div class="modal-body">

<p>

<strong>Student:</strong>

<?php echo htmlspecialchars($row['full_name']); ?>

</p>

<p>

<strong>Job:</strong>

<?php echo htmlspecialchars($row['job_title']); ?>

</p>

<p>

<strong>Date:</strong>

<?php echo date("d M Y",strtotime($row['interview_date'])); ?>

</p>

<p>

<strong>Time:</strong>

<?php echo date("h:i A",strtotime($row['interview_time'])); ?>

</p>

<p>

<strong>Mode:</strong>

<?php echo htmlspecialchars($row['mode']); ?>

</p>

<p>

<strong>Venue:</strong>

<?php

echo !empty($row['venue'])

? htmlspecialchars($row['venue'])

: "-";

?>

</p>

<p>

<strong>Meeting Link:</strong>

<?php

if(!empty($row['meeting_link']))
{

?>

<a

href="<?php echo htmlspecialchars($row['meeting_link']); ?>"

target="_blank">

Join Meeting

</a>

<?php

}
else
{

echo "-";

}

?>

</p>

<p>

<strong>Remarks:</strong>

<?php

echo !empty($row['remarks'])

? nl2br(htmlspecialchars($row['remarks']))

: "-";

?>

</p>

</div>

</div>

</div>

</div>

<?php

}

}
else
{

?>

<tr>

<td

colspan="6"

class="text-center text-muted py-5">

No interviews scheduled.

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

.table td{

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

.modal-body p{

margin-bottom:12px;

}

</style>

</div> <!-- table-responsive -->

</div> <!-- dashboard-card -->

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