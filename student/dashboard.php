<?php

session_start();

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

/* ===========================================================
   GET STUDENT DETAILS
=========================================================== */

$user_id = $_SESSION['user_id'];

$sql = "SELECT student_id,
               roll_number,
               branch_id,
               cgpa,
               graduation_year,
               resume,
               profile_photo
        FROM students
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Student profile not found.");
}

$student=mysqli_fetch_assoc($result);

$student_id=$student['student_id'];

/* ===========================================================
   PROFILE COMPLETION
=========================================================== */

$completed=0;

if(!empty($student['roll_number'])) $completed++;
if(!empty($student['branch_id'])) $completed++;
if(!empty($student['cgpa'])) $completed++;
if(!empty($student['graduation_year'])) $completed++;
if(!empty($student['resume'])) $completed++;
if(!empty($student['profile_photo'])) $completed++;

$profileCompletion=round(($completed/6)*100);

/* ===========================================================
   APPLICATION COUNT
=========================================================== */

$sql="SELECT COUNT(*) total
      FROM applications
      WHERE student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$student_id);

mysqli_stmt_execute($stmt);

$applications=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$totalApplications=$applications['total'];

/* ===========================================================
   INTERVIEW COUNT
=========================================================== */

$sql="SELECT COUNT(*) total

FROM interviews i

INNER JOIN applications a

ON i.application_id=a.application_id

WHERE a.student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$student_id);

mysqli_stmt_execute($stmt);

$interviews=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$totalInterviews=$interviews['total'];

/* ===========================================================
   OFFERS
=========================================================== */

$sql="SELECT COUNT(*) total

FROM applications

WHERE student_id=?

AND application_status='Selected'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$student_id);

mysqli_stmt_execute($stmt);

$offers=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$totalOffers=$offers['total'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/dashboard.css">

</head>

<body>

<div class="dashboard-layout">

<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/topbar.php"); ?>

<div class="container-fluid mt-4">

<div class="row g-4">

<div class="col-lg-3">

<div class="stat-card">

<h2><?php echo $profileCompletion; ?>%</h2>

<p>Profile Completion</p>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<h2><?php echo $totalApplications; ?></h2>

<p>Applications</p>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<h2><?php echo $totalInterviews; ?></h2>

<p>Interviews</p>

</div>

</div>

<div class="col-lg-3">

<div class="stat-card">

<h2><?php echo $totalOffers; ?></h2>

<p>Offers</p>

</div>

</div>

</div>

<div class="row mt-4">

<div class="col-lg-8">

<div class="dashboard-card">

<h4>

<i class="bi bi-briefcase-fill"></i>

Latest Opportunities

</h4>

<hr>

<?php

$sql="SELECT

jobs.job_title,

jobs.location,

jobs.salary_package,

jobs.interview_mode,

companies.company_name

FROM jobs

INNER JOIN companies

ON jobs.company_id=companies.company_id

WHERE jobs.status='Open'

ORDER BY jobs.created_at DESC

LIMIT 5";

$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)>0)
{

while($job=mysqli_fetch_assoc($result))
{

?>

<div class="job-item">

<div>

<h5>

<?php echo htmlspecialchars($job['job_title']); ?>

</h5>

<p>

<?php echo htmlspecialchars($job['company_name']); ?>

</p>

</div>

<div class="job-meta">

<span>

<i class="bi bi-geo-alt-fill"></i>

<?php echo htmlspecialchars($job['location']); ?>

</span>

<span>

<i class="bi bi-cash-stack"></i>

₹<?php echo htmlspecialchars($job['salary_package']); ?> LPA

</span>

<span>

<i class="bi bi-building"></i>

<?php echo htmlspecialchars($job['interview_mode']); ?>

</span>

<a href="browse_jobs.php"

class="btn btn-primary btn-sm">

View

</a>

</div>

</div>

<hr>

<?php

}

}

else

{

?>

<div class="alert alert-info">

No jobs have been posted yet.

</div>

<?php

}

?>

</div>

</div>

<div class="col-lg-4">

<div class="dashboard-card">

<h4>

<i class="bi bi-bell-fill"></i>

Notifications

</h4>

<hr>

<p>

No new notifications.

</p>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>