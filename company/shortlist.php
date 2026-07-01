<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_jobs.php");
    exit();
}

$application_id=intval($_GET['id']);

$user_id=$_SESSION['user_id'];

/*=========================================
VERIFY COMPANY
=========================================*/

$sql="SELECT company_id
FROM companies
WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$company=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt));

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

a.student_id,

a.job_id,

j.company_id,

j.job_title,

s.user_id

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

INNER JOIN students s

ON a.student_id=s.student_id

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

$data=mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if($data['company_id']!=$company_id)
{
    header("Location: manage_jobs.php");
    exit();
}

mysqli_begin_transaction($conn);

try
{

/*=========================================
UPDATE STATUS
=========================================*/

$sql="UPDATE applications

SET application_status='Shortlisted'

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
NOTIFICATION
=========================================*/

$title="Application Shortlisted";

$message="Congratulations! You have been shortlisted for the job: ".$data['job_title'].".";

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

$data['user_id'],

$title,

$message

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

mysqli_commit($conn);

header("Location:view_applicants.php?id=".$data['job_id']."&success=Applicant shortlisted successfully.");

exit();

}
catch(Exception $e)
{

mysqli_rollback($conn);

header("Location:view_applicants.php?id=".$data['job_id']."&error=Unable to shortlist applicant.");

exit();

}

?>