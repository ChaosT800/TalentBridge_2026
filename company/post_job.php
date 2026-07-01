<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

/*=========================================
FETCH SKILLS
=========================================*/

$skills=mysqli_query(

$conn,

"SELECT *

FROM skills

ORDER BY skill_name"

);

/*=========================================
FETCH BRANCHES
=========================================*/

$branches=mysqli_query(

$conn,

"SELECT *

FROM branches

ORDER BY branch_name"

);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Post New Job

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

<i class="bi bi-plus-circle-fill"></i>

Post New Job

</h2>

<form

action="save_job.php"

method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Job Title

</label>

<input

type="text"

name="job_title"

class="form-control"

required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Salary Package (LPA)

</label>

<input

type="number"

step="0.01"

name="salary_package"

class="form-control"

required>

</div>

<div class="col-md-4 mb-3">

<label>

Minimum CGPA

</label>

<input

type="number"

step="0.01"

min="0"

max="10"

name="minimum_cgpa"

class="form-control"

required>

</div>

<div class="col-md-4 mb-3">

<label>

Maximum Backlogs

</label>

<input

type="number"

min="0"

name="maximum_backlogs"

class="form-control"

required>

</div>

<div class="col-md-4 mb-3">

<label>

Graduation Year

</label>

<input

type="number"

name="graduation_year"

class="form-control"

required>

</div>

<div class="col-md-6 mb-3">

<label>

Interview Mode

</label>

<select

name="interview_mode"

class="form-select">

<option>Offline</option>

<option>Online</option>

<option>Hybrid</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>

Job Type

</label>

<select

name="job_type"

class="form-select">

<option>Full-Time</option>

<option>Internship</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>

Location

</label>

<input

type="text"

name="location"

class="form-control"

required>

</div>

<div class="col-md-6 mb-3">

<label>

Application Deadline

</label>

<input

type="date"

name="application_deadline"

class="form-control"

required>

</div>

<div class="col-12 mb-4">

<label>

Job Description

</label>

<textarea

name="description"

rows="6"

class="form-control"

required></textarea>

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

id="skill<?php echo $skill['skill_id']; ?>">

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

id="branch<?php echo $branch['branch_id']; ?>">

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

Publish Job

</button>

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

padding:8px 0;

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