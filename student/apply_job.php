<?php

session_start();

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");
require_once("../services/MatchEngine.php");

/* ==========================================
   VALIDATE JOB ID
========================================== */

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: browse_jobs.php?error=Invalid job.");
    exit();
}

$job_id = intval($_GET['id']);

$user_id = $_SESSION['user_id'];

/* ==========================================
   GET STUDENT ID
========================================== */

$sql = "SELECT student_id
        FROM students
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: dashboard.php?error=Student profile not found.");
    exit();
}

$student = mysqli_fetch_assoc($result);

$student_id = $student['student_id'];

mysqli_stmt_close($stmt);

/* ==========================================
   CHECK JOB EXISTS
========================================== */

$sql = "SELECT
            application_deadline,
            status
        FROM jobs
        WHERE job_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$job_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: browse_jobs.php?error=Job not found.");
    exit();
}

$job = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* ==========================================
   JOB STATUS
========================================== */

if($job['status']!="Open")
{
    header("Location: browse_jobs.php?error=This job is closed.");
    exit();
}

/* ==========================================
   DEADLINE CHECK
========================================== */

if(strtotime($job['application_deadline']) < strtotime(date("Y-m-d")))
{
    header("Location: browse_jobs.php?error=Application deadline has passed.");
    exit();
}

/* ==========================================
   DUPLICATE CHECK
========================================== */

$sql = "SELECT application_id
        FROM applications
        WHERE student_id=?
        AND job_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"ii",$student_id,$job_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt)>0)
{
    mysqli_stmt_close($stmt);

    header("Location: browse_jobs.php?error=You have already applied for this job.");

    exit();
}

mysqli_stmt_close($stmt);

/* ==========================================
   CALCULATE MATCH SCORE
========================================== */

$match = MatchEngine::calculate(
    $conn,
    $student_id,
    $job_id
);

$match_score = $match['score'];

mysqli_begin_transaction($conn);

try
{

/* ==========================================
   INSERT APPLICATION
========================================== */

$sql = "INSERT INTO applications(

student_id,
job_id,
application_status,
match_score

)

VALUES(

?,
?,
'Applied',
?

)";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"iid",
$student_id,
$job_id,
$match_score
);

if(!mysqli_stmt_execute($stmt))
{
    throw new Exception("Unable to submit application.");
}

$application_id = mysqli_insert_id($conn);

mysqli_stmt_close($stmt);

/* ==========================================
   GET COMPANY USER
========================================== */

$sql = "SELECT

u.user_id,
u.full_name,
j.job_title

FROM jobs j

INNER JOIN companies c
ON j.company_id = c.company_id

INNER JOIN users u
ON c.user_id = u.user_id

WHERE j.job_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$job_id);

mysqli_stmt_execute($stmt);

$company = mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

if(!$company)
{
    throw new Exception("Company not found.");
}
mysqli_stmt_close($stmt);

/* ==========================================
   GET STUDENT NAME
========================================== */

$sql = "SELECT full_name
FROM users
WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$studentUser = mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

if(!$studentUser)
{
    throw new Exception("Student not found.");
}

mysqli_stmt_close($stmt);

/* ==========================================
   COMPANY NOTIFICATION
========================================== */

$title = "New Job Application";

$message = $studentUser['full_name'] .
           " applied for " .
           $company['job_title'];

$sql = "INSERT INTO notifications(

user_id,
application_id,
job_id,
notification_type,
title,
message

)

VALUES(

?,
?,
?,
'Application',
?,
?

)";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"iiiss",

$company['user_id'],

$application_id,

$job_id,

$title,

$message

);

if(!mysqli_stmt_execute($stmt))
{
    throw new Exception("Unable to send notification.");
}

mysqli_stmt_close($stmt);

mysqli_commit($conn);

header("Location: browse_jobs.php?success=Application submitted successfully.");

exit();

}

catch(Exception $e)
{

    mysqli_rollback($conn);

    header("Location: browse_jobs.php?error=".$e->getMessage());

    exit();

}

?>