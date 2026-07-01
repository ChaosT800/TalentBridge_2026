<?php

session_start();

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

/*==========================================
GET COMPANY ID
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
INSERT JOB
==========================================*/

$sql="INSERT INTO jobs(

company_id,
job_title,
description,
salary_package,
minimum_cgpa,
maximum_backlogs,
graduation_year,
interview_mode,
job_type,
location,
application_deadline

)

VALUES(

?,?,?,?,?,?,?,?,?,?,?

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"issddiissss",

$company_id,

$_POST['job_title'],

$_POST['description'],

$_POST['salary_package'],

$_POST['minimum_cgpa'],

$_POST['maximum_backlogs'],

$_POST['graduation_year'],

$_POST['interview_mode'],

$_POST['job_type'],

$_POST['location'],

$_POST['application_deadline']

);

mysqli_stmt_execute($stmt);

$job_id=mysqli_insert_id($conn);

mysqli_stmt_close($stmt);

/*==========================================
SAVE SKILLS
==========================================*/

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
SAVE BRANCHES
=========================================*/

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

header("Location: manage_jobs.php?success=Job posted successfully.");

exit();

?>

