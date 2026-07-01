<?php

session_start();

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

if($_SERVER["REQUEST_METHOD"]!="POST")
{
    header("Location: manage_jobs.php");
    exit();
}

$user_id=$_SESSION['user_id'];

$job_id=intval($_POST['job_id']);

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
    header("Location: manage_jobs.php?error=Unauthorized.");
    exit();
}

mysqli_stmt_close($stmt);

/*=========================================
UPDATE JOB
=========================================*/

$sql="UPDATE jobs SET

job_title=?,
description=?,
salary_package=?,
minimum_cgpa=?,
maximum_backlogs=?,
graduation_year=?,
interview_mode=?,
job_type=?,
location=?,
application_deadline=?

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ssddiissssi",

$_POST['job_title'],
$_POST['description'],
$_POST['salary_package'],
$_POST['minimum_cgpa'],
$_POST['maximum_backlogs'],
$_POST['graduation_year'],
$_POST['interview_mode'],
$_POST['job_type'],
$_POST['location'],
$_POST['application_deadline'],
$job_id

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*=========================================
UPDATE SKILLS
=========================================*/

$sql="DELETE FROM job_skills
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
UPDATE BRANCHES
=========================================*/

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

if(isset($_POST['branches']))
{

foreach($_POST['branches'] as $branch)
{

$sql="INSERT INTO job_branches(

job_id,
branch_id

)

VALUES(

?,
?

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ii",

$job_id,

$branch

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

}

/*=========================================
INSERT NEW SKILLS
=========================================*/

if(isset($_POST['skills']))
{

foreach($_POST['skills'] as $skill)
{

$sql="INSERT INTO job_skills(

job_id,
skill_id

)

VALUES(

?,
?

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ii",

$job_id,

$skill

);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

}

/*=========================================
SUCCESS
=========================================*/

header("Location: manage_jobs.php?success=Job updated successfully.");

exit();

?>