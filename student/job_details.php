<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: browse_jobs.php");
    exit();
}

$job_id = intval($_GET['id']);

$sql = "SELECT
            jobs.*,
            companies.company_name,
            companies.industry,
            companies.website,
            companies.location AS company_location,
            companies.description AS company_description
        FROM jobs
        INNER JOIN companies
        ON jobs.company_id = companies.company_id
        WHERE jobs.job_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$job_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Job not found.");
}

$job = mysqli_fetch_assoc($result);

/* ==========================
   FETCH REQUIRED SKILLS
========================== */

$sql = "SELECT skills.skill_name
        FROM job_skills
        INNER JOIN skills
        ON job_skills.skill_id = skills.skill_id
        WHERE job_skills.job_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$job_id);

mysqli_stmt_execute($stmt);

$skills = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title><?php echo htmlspecialchars($job['job_title']); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<div class="dashboard-card">

<div class="d-flex justify-content-between align-items-center">

<div>

<h2>

<?php echo htmlspecialchars($job['job_title']); ?>

</h2>

<h5 class="text-primary">

<?php echo htmlspecialchars($job['company_name']); ?>

</h5>

</div>

<a
href="apply_job.php?id=<?php echo $job_id; ?>"
class="btn btn-success btn-lg">

Apply Now

</a>

</div>

<hr>

<div class="row mt-4">

<div class="col-md-6">

<p>

<strong>Location:</strong>

<?php echo htmlspecialchars($job['location']); ?>

</p>

<p>

<strong>Package:</strong>

₹<?php echo $job['salary_package']; ?> LPA

</p>

<p>

<strong>Interview Mode:</strong>

<?php echo htmlspecialchars($job['interview_mode']); ?>

</p>

<p>

<strong>Job Type:</strong>

<?php echo htmlspecialchars($job['job_type']); ?>

</p>

</div>

<div class="col-md-6">

<p>

<strong>Minimum CGPA:</strong>

<?php echo $job['minimum_cgpa']; ?>

</p>

<p>

<strong>Maximum Backlogs:</strong>

<?php echo $job['maximum_backlogs']; ?>

</p>

<p>

<strong>Graduation Year:</strong>

<?php echo $job['graduation_year']; ?>

</p>

<p>

<strong>Deadline:</strong>

<?php echo $job['application_deadline']; ?>

</p>

</div>

</div>

<hr>

<h4>Job Description</h4>

<p>

<?php echo nl2br(htmlspecialchars($job['description'])); ?>

</p>

<hr>

<h4>Required Skills</h4>

<div class="mt-3">

<?php

if(mysqli_num_rows($skills)>0)
{

while($skill=mysqli_fetch_assoc($skills))
{

?>

<span class="badge bg-primary me-2 mb-2">

<?php echo htmlspecialchars($skill['skill_name']); ?>

</span>

<?php

}

}
else
{

echo "<span class='text-muted'>No specific skills mentioned.</span>";

}

?>

</div>

<hr>

<h4>About Company</h4>

<p>

<strong>Industry:</strong>

<?php echo htmlspecialchars($job['industry']); ?>

</p>

<p>

<strong>Location:</strong>

<?php echo htmlspecialchars($job['company_location']); ?>

</p>

<p>

<strong>Website:</strong>

<a
href="<?php echo htmlspecialchars($job['website']); ?>"
target="_blank">

<?php echo htmlspecialchars($job['website']); ?>

</a>

</p>

<p>

<?php echo nl2br(htmlspecialchars($job['company_description'])); ?>

</p>

</div>

</div>

</div>

</div>

</body>

</html>