<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_jobs.php");
    exit();
}

$application_id=intval($_GET['id']);

/*=========================================
VERIFY COMPANY
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
VERIFY APPLICATION
=========================================*/

$sql="SELECT

a.application_id,

a.student_id,

a.job_id,

u.user_id,

u.full_name,

j.job_title,

j.company_id

FROM applications a

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN users u

ON s.user_id=u.user_id

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE a.application_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$application_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: manage_jobs.php");
    exit();
}

$app=mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if($app['company_id']!=$company_id)
{
    header("Location: manage_jobs.php");
    exit();
}

/*=========================================
CHECK EXISTING INTERVIEW
=========================================*/

$sql="SELECT *

FROM interviews

WHERE application_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$application_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$editing=false;

$interview=[];

if(mysqli_num_rows($result)>0)
{

$editing=true;

$interview=mysqli_fetch_assoc($result);

}

mysqli_stmt_close($stmt);

/*=========================================
SAVE INTERVIEW
=========================================*/

if($_SERVER['REQUEST_METHOD']=="POST")
{

$date=$_POST['interview_date'];

$time=$_POST['interview_time'];

$mode=$_POST['mode'];

$venue=trim($_POST['venue']);

$link=trim($_POST['meeting_link']);

$remarks=trim($_POST['remarks']);

mysqli_begin_transaction($conn);

try
{

if($editing)
{

$sql="UPDATE interviews

SET

interview_date=?,

interview_time=?,

mode=?,

venue=?,

meeting_link=?,

remarks=?

WHERE application_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ssssssi",

$date,

$time,

$mode,

$venue,

$link,

$remarks,

$application_id

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}
else
{

$sql="INSERT INTO interviews(

application_id,

interview_date,

interview_time,

mode,

venue,

meeting_link,

remarks

)

VALUES(

?,

?,

?,

?,

?,

?,

?

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"issssss",

$application_id,

$date,

$time,

$mode,

$venue,

$link,

$remarks

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

/*=========================================
UPDATE APPLICATION STATUS
=========================================*/

$sql="UPDATE applications

SET application_status='Interview Scheduled'

WHERE application_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"i",

$application_id

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*=========================================
SEND NOTIFICATION
=========================================*/

$title="Interview Scheduled";

$message="Your interview for '".$app['job_title']."' has been scheduled. Please check your interview details from your dashboard.";

$sql="INSERT INTO notifications(

user_id,

title,

message

)

VALUES(

?,

?,

?

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"iss",

$app['user_id'],

$title,

$message

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

mysqli_commit($conn);

header(

"Location:view_applicants.php?id=".$app['job_id']."&success=Interview scheduled successfully."

);

exit();

}
catch(Exception $e)
{

mysqli_rollback($conn);

header(

"Location:view_applicants.php?id=".$app['job_id']."&error=Unable to schedule interview."

);

exit();

}

}

/*=========================================
HTML
=========================================*/

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>

Schedule Interview

</title>

<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

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

<?php

if($editing)

echo "Update Interview";

else

echo "Schedule Interview";

?>

</h2>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Interview Date

</label>

<input

type="date"

name="interview_date"

class="form-control"

required

value="<?php echo $editing ? $interview['interview_date'] : ''; ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Interview Time

</label>

<input

type="time"

name="interview_time"

class="form-control"

required

value="<?php echo $editing ? $interview['interview_time'] : ''; ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Mode

</label>

<select

name="mode"

class="form-select"

required>

<option

value="Offline"

<?php

if($editing && $interview['mode']=="Offline")

echo "selected";

?>

>

Offline

</option>

<option

value="Online"

<?php

if($editing && $interview['mode']=="Online")

echo "selected";

?>

>

Online

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Venue

</label>

<input

type="text"

name="venue"

class="form-control"

placeholder="Interview Venue"

value="<?php echo $editing ? htmlspecialchars($interview['venue']) : ''; ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Meeting Link

</label>

<input

type="url"

name="meeting_link"

class="form-control"

placeholder="https://meet.google.com/..."

value="<?php echo $editing ? htmlspecialchars($interview['meeting_link']) : ''; ?>">

</div>

<div class="col-12 mb-3">

<label class="form-label">

Remarks

</label>

<textarea

name="remarks"

class="form-control"

rows="4"

placeholder="Interview instructions..."><?php

echo $editing ? htmlspecialchars($interview['remarks']) : '';

?></textarea>

</div>

</div>

<div class="d-flex gap-2">

<button

type="submit"

class="btn btn-primary">

<?php

if($editing)

echo "Update Interview";

else

echo "Schedule Interview";

?>

</button>

<a

href="view_applicants.php?id=<?php echo $app['job_id']; ?>"

class="btn btn-secondary">

Cancel

</a>

</div>

</form>

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

label{

font-weight:600;

}

</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php

if(isset($stmt))
{
}

mysqli_close($conn);

?>