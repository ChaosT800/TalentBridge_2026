<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

/* ==========================================
   FETCH OPEN JOBS
========================================== */

$sql = "SELECT
            jobs.job_id,
            jobs.job_title,
            jobs.salary_package,
            jobs.location,
            jobs.interview_mode,
            jobs.job_type,
            jobs.application_deadline,
            companies.company_name
        FROM jobs
        INNER JOIN companies
        ON jobs.company_id = companies.company_id
        WHERE jobs.status='Open'
        ORDER BY jobs.created_at DESC";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Browse Jobs</title>

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

<i class="bi bi-briefcase-fill"></i>

Browse Jobs

</h2>

<input
type="text"
id="jobSearch"
class="form-control"
placeholder="Search Job..."
style="max-width:300px;">

</div>

<div id="jobsContainer">

<?php

if(mysqli_num_rows($result)>0)
{

while($job=mysqli_fetch_assoc($result))
{

?>

<div class="job-card mb-4">

<div class="row align-items-center">

<div class="col-lg-8">

<h4>

<?php echo htmlspecialchars($job['job_title']); ?>

</h4>

<h6 class="text-primary">

<?php echo htmlspecialchars($job['company_name']); ?>

</h6>

<div class="mt-3">

<span class="me-4">

<i class="bi bi-geo-alt-fill"></i>

<?php echo htmlspecialchars($job['location']); ?>

</span>

<span class="me-4">

<i class="bi bi-cash-stack"></i>

₹<?php echo $job['salary_package']; ?> LPA

</span>

<span class="me-4">

<i class="bi bi-building"></i>

<?php echo htmlspecialchars($job['interview_mode']); ?>

</span>

<span>

<i class="bi bi-clock-fill"></i>

<?php echo htmlspecialchars($job['job_type']); ?>

</span>

</div>

</div>

<div class="col-lg-4 text-end">

<a
href="job_details.php?id=<?php echo $job['job_id']; ?>"
class="btn btn-primary">

View Details

</a>

</div>

</div>

</div>

<?php

}

}

else

{

?>

<div class="alert alert-info">

No jobs available.

</div>

<?php

}

?>

</div>

</div>

</div>

</div>

</div>

<script>

const search=document.getElementById("jobSearch");

search.addEventListener("keyup",function(){

let filter=this.value.toLowerCase();

let cards=document.querySelectorAll(".job-card");

cards.forEach(function(card){

let text=card.innerText.toLowerCase();

card.style.display=text.includes(filter)?"block":"none";

});

});

</script>

</body>

</html>