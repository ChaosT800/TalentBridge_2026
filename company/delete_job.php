<?php

session_start();

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_jobs.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$job_id = intval($_GET['id']);

try
{

mysqli_begin_transaction($conn);

/*=========================================
VERIFY COMPANY
=========================================*/

$sql="SELECT company_id
FROM companies
WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$company=mysqli_fetch_assoc($result);

$company_id=$company['company_id'];

mysqli_stmt_close($stmt);

/*=========================================
VERIFY JOB OWNERSHIP
=========================================*/

$sql="SELECT job_id
FROM jobs
WHERE job_id=?
AND company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"ii",
$job_id,
$company_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt)==0)
{
    throw new Exception("Unauthorized access.");
}

mysqli_stmt_close($stmt);

/*=========================================
DELETE INTERVIEWS
=========================================*/

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

/*=========================================
DELETE APPLICATIONS
=========================================*/

$sql="DELETE
FROM applications
WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*=========================================
DELETE JOB SKILLS
=========================================*/

$sql="DELETE
FROM job_skills
WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*=========================================
DELETE JOB
=========================================*/

$sql="DELETE
FROM jobs
WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$job_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*=========================================
COMMIT
=========================================*/

mysqli_commit($conn);

header("Location: manage_jobs.php?success=Job deleted successfully.");

}
catch(Exception $e)
{

mysqli_rollback($conn);

header("Location: manage_jobs.php?error=Unable to delete job.");

}

exit();

?>