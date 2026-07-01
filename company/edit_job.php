<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_jobs.php");
    exit();
}

$job_id=intval($_GET['id']);

$user_id=$_SESSION['user_id'];

/*=========================================
GET COMPANY
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
    header("Location: manage_jobs.php");
    exit();
}

$company_id=$company['company_id'];

/*=========================================
GET JOB
=========================================*/

$sql="SELECT *

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

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{

header("Location: manage_jobs.php");

exit();

}

$job=mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=========================================
FETCH ALL SKILLS
=========================================*/

$skills=mysqli_query(

$conn,

"SELECT *

FROM skills

ORDER BY skill_name"

);

/*=========================================
FETCH ALL BRANCHES
=========================================*/

$branches=mysqli_query(

$conn,

"SELECT *

FROM branches

ORDER BY branch_name"

);

/*=========================================
SELECTED SKILLS
=========================================*/

$selectedSkills=[];

$sql="SELECT skill_id

FROM job_skills

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"i",

$job_id

);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

while($row=mysqli_fetch_assoc($result))
{

$selectedSkills[]=$row['skill_id'];

}

mysqli_stmt_close($stmt);

/*=========================================
SELECTED BRANCHES
=========================================*/

$selectedBranches=[];

$sql="SELECT branch_id

FROM job_branches

WHERE job_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"i",

$job_id

);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

while($row=mysqli_fetch_assoc($result))
{

$selectedBranches[]=$row['branch_id'];

}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Edit Job

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

<h2 class="mb-4">

<i class="bi bi-pencil-square"></i>

Edit Job

</h2>

<form

action="update_job.php"

method="POST">

<input

type="hidden"

name="job_id"

value="<?php echo $job_id; ?>">

<div class="row">

<div class="col-md-6 mb-3">

<label>Job Title</label>

<input

type="text"

name="job_title"

class="form-control"

required

value="<?php echo htmlspecialchars($job['job_title']); ?>">

</div>

<div class="col-md-6 mb-3">

<label>Salary Package (LPA)</label>

<input

type="number"

step="0.01"

name="salary_package"

class="form-control"

required

value="<?php echo $job['salary_package']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Minimum CGPA</label>

<input

type="number"

step="0.01"

min="0"

max="10"

name="minimum_cgpa"

class="form-control"

required

value="<?php echo $job['minimum_cgpa']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Maximum Backlogs</label>

<input

type="number"

min="0"

name="maximum_backlogs"

class="form-control"

required

value="<?php echo $job['maximum_backlogs']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Graduation Year</label>

<input

type="number"

name="graduation_year"

class="form-control"

required

value="<?php echo $job['graduation_year']; ?>">

</div>

<div class="col-md-6 mb-3">

<label>Interview Mode</label>

<select

name="interview_mode"

class="form-select">

<option value="Offline" <?php if($job['interview_mode']=="Offline") echo "selected"; ?>>

Offline

</option>

<option value="Online" <?php if($job['interview_mode']=="Online") echo "selected"; ?>>

Online

</option>

<option value="Hybrid" <?php if($job['interview_mode']=="Hybrid") echo "selected"; ?>>

Hybrid

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Job Type</label>

<select

name="job_type"

class="form-select">

<option value="Full-Time" <?php if($job['job_type']=="Full-Time") echo "selected"; ?>>

Full-Time

</option>

<option value="Internship" <?php if($job['job_type']=="Internship") echo "selected"; ?>>

Internship

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Location</label>

<input

type="text"

name="location"

class="form-control"

required

value="<?php echo htmlspecialchars($job['location']); ?>">

</div>

<div class="col-md-6 mb-3">

<label>Application Deadline</label>

<input

type="date"

name="application_deadline"

class="form-control"

required

value="<?php echo $job['application_deadline']; ?>">

</div>

<div class="col-12 mb-4">

<label>Job Description</label>

<textarea

name="description"

rows="6"

class="form-control"

required><?php echo htmlspecialchars($job['description']); ?></textarea>

</div>

<div class="col-12">

<hr class="my-4">

<h4 class="mb-3">

Required Skills

</h4>

<div class="row">

<?php

while($skill=mysqli_fetch_assoc($skills))
{

?>

<div class="col-md-3 mb-2">

<div class="form-check">

<input

class="form-check-input"

type="checkbox"

name="skills[]"

value="<?php echo $skill['skill_id']; ?>"

id="skill<?php echo $skill['skill_id']; ?>"

<?php

if(in_array($skill['skill_id'],$selectedSkills))

echo "checked";

?>

>

<label

class="form-check-label"

for="skill<?php echo $skill['skill_id']; ?>">

<?php echo htmlspecialchars($skill['skill_name']); ?>

</label>

</div>

</div>

<?php

}

?>

</div>

<hr class="my-4">

<h4 class="mb-3">

Eligible Branches

</h4>

<div class="row">

<?php

while($branch=mysqli_fetch_assoc($branches))
{

?>

<div class="col-md-3 mb-2">

<div class="form-check">

<input

class="form-check-input"

type="checkbox"

name="branches[]"

value="<?php echo $branch['branch_id']; ?>"

id="branch<?php echo $branch['branch_id']; ?>"

<?php

if(in_array($branch['branch_id'],$selectedBranches))

echo "checked";

?>

>

<label

class="form-check-label"

for="branch<?php echo $branch['branch_id']; ?>">

<?php echo htmlspecialchars($branch['branch_name']); ?>

</label>

</div>

</div>

<?php

}

?>

</div>

</div>

<div class="col-12 mt-4">

<button

type="submit"

class="btn btn-primary btn-lg">

<i class="bi bi-check-circle-fill"></i>

Update Job

</button>

<a

href="manage_jobs.php"

class="btn btn-secondary btn-lg ms-2">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

</div>

</form>

</div>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#ffffff;

padding:30px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.form-check{

padding:6px 0;

}

.form-check-label{

cursor:pointer;

}

h4{

color:#0d6efd;

font-weight:600;

}

textarea{

resize:vertical;

}

.btn-lg{

padding:10px 25px;

}

</style>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>

<?php

mysqli_close($conn);

?>