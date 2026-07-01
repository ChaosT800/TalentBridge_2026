<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_jobs.php");
    exit();
}

$job_id = intval($_GET['id']);

/*=========================================
VERIFY COMPANY
=========================================*/

$sql = "SELECT company_id
        FROM companies
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$company = mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

if(!$company)
{
    header("Location: dashboard.php");
    exit();
}

$company_id = $company['company_id'];

/*=========================================
VERIFY JOB
=========================================*/

$sql = "SELECT job_title
        FROM jobs
        WHERE job_id=?
        AND company_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"ii",
$job_id,
$company_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: manage_jobs.php");
    exit();
}

$job = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=========================================
SEARCH
=========================================*/

$search = "";

if(isset($_GET['search']))
{
    $search = trim($_GET['search']);
}

/*=========================================
STATUS FILTER
=========================================*/

$status="";

if(isset($_GET['status']))
{
    $status=$_GET['status'];
}

/*=========================================
SUMMARY COUNTS
=========================================*/

$summary=[];

$statuses=[
"Applied",
"Shortlisted",
"Interview Scheduled",
"Selected",
"Rejected"
];

foreach($statuses as $s)
{

$sql="SELECT COUNT(*) total
FROM applications
WHERE job_id=?
AND application_status=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"is",
$job_id,
$s
);

mysqli_stmt_execute($stmt);

$row=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

$summary[$s]=$row['total'];

mysqli_stmt_close($stmt);

}

/*=========================================
FETCH APPLICANTS
=========================================*/

$sql="SELECT

a.application_id,

a.student_id,

a.application_status,

a.match_score,

u.full_name,

u.email,

u.phone,

s.roll_number,

s.cgpa,

s.backlogs,

s.resume,

s.profile_photo,

s.linkedin,

s.github,

b.branch_name

FROM applications a

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN users u

ON s.user_id=u.user_id

LEFT JOIN branches b

ON s.branch_id=b.branch_id

WHERE a.job_id=?";

$params=[];

$types="i";

$params[]=$job_id;

if($status!="")
{

$sql.=" AND a.application_status=?";

$types.="s";

$params[]=$status;

}

if($search!="")
{

$sql.=" AND (

u.full_name LIKE ?

OR

s.roll_number LIKE ?

)";

$types.="ss";

$like="%".$search."%";

$params[]=$like;

$params[]=$like;

}

$sql.="

ORDER BY

a.match_score DESC,

s.cgpa DESC";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
$types,
...$params
);

mysqli_stmt_execute($stmt);

$applicants=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Applicants</title>

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

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2>

<?php echo htmlspecialchars($job['job_title']); ?>

</h2>

<p class="text-muted">

Applicant Tracking System

</p>

</div>

<a

href="manage_jobs.php"

class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

<div class="row g-3 mb-4">

<div class="col-md-2">

<div class="stat-card">

<h3><?php echo $summary['Applied']; ?></h3>

<p>Applied</p>

</div>

</div>

<div class="col-md-2">

<div class="stat-card">

<h3><?php echo $summary['Shortlisted']; ?></h3>

<p>Shortlisted</p>

</div>

</div>

<div class="col-md-2">

<div class="stat-card">

<h3><?php echo $summary['Interview Scheduled']; ?></h3>

<p>Interview</p>

</div>

</div>

<div class="col-md-2">

<div class="stat-card">

<h3><?php echo $summary['Selected']; ?></h3>

<p>Selected</p>

</div>

</div>

<div class="col-md-2">

<div class="stat-card">

<h3><?php echo $summary['Rejected']; ?></h3>

<p>Rejected</p>

</div>

</div>

</div>

<form

method="GET"

class="row g-3 mb-4">

<input

type="hidden"

name="id"

value="<?php echo $job_id; ?>">

<div class="col-md-5">

<input

type="text"

name="search"

class="form-control"

placeholder="Search by Name or Roll Number"

value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-4">

<select

name="status"

class="form-select">

<option value="">All Status</option>

<?php

foreach($statuses as $s)
{

?>

<option

value="<?php echo $s; ?>"

<?php

if($status==$s)
echo "selected";

?>

>

<?php echo $s; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-3">

<button

class="btn btn-primary w-100">

<i class="bi bi-search"></i>

Search

</button>

</div>

</form>

<div class="dashboard-card">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Student</th>

<th>Branch</th>

<th>CGPA</th>

<th>Match</th>

<th>Status</th>

<th>Skills</th>

<th>Resume</th>

<th>Links</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($applicants)>0)
{

while($row=mysqli_fetch_assoc($applicants))
{
    
?>

<tr>

<td>

<div class="d-flex align-items-center">

<?php

if(!empty($row['profile_photo']))
{

?>

<img

src="../<?php echo htmlspecialchars($row['profile_photo']); ?>"

width="55"

height="55"

class="rounded-circle me-3"

style="object-fit:cover;">

<?php

}
else
{

?>

<img

src="../assets/images/default-user.png"

width="55"

height="55"

class="rounded-circle me-3"

style="object-fit:cover;">

<?php

}

?>

<div>

<strong>

<?php echo htmlspecialchars($row['full_name']); ?>

</strong>

<br>

<small class="text-muted">

<?php echo htmlspecialchars($row['roll_number']); ?>

</small>

<br>

<small>

<?php echo htmlspecialchars($row['email']); ?>

</small>

</div>

</div>

</td>

<td>

<?php

echo htmlspecialchars($row['branch_name']);

?>

</td>

<td>

<span class="badge bg-info">

<?php

echo number_format($row['cgpa'],2);

?>

</span>

</td>

<td>

<?php

$score=(float)$row['match_score'];

$badge="danger";

if($score>=90)
{

$badge="success";

}
elseif($score>=75)
{

$badge="primary";

}
elseif($score>=60)
{

$badge="warning";

}

?>

<span

class="badge bg-<?php echo $badge; ?>">

<?php

echo number_format($score,2);

?>%

</span>

</td>

<td>

<?php

$statusColor="secondary";

switch($row['application_status'])
{

case "Applied":

$statusColor="secondary";

break;

case "Shortlisted":

$statusColor="primary";

break;

case "Interview Scheduled":

$statusColor="warning";

break;

case "Selected":

$statusColor="success";

break;

case "Rejected":

$statusColor="danger";

break;

}

?>

<span

class="badge bg-<?php echo $statusColor; ?>">

<?php

echo htmlspecialchars($row['application_status']);

?>

</span>

</td>

<td>

<?php

$sqlSkill="SELECT

skills.skill_name

FROM student_skills

INNER JOIN skills

ON student_skills.skill_id=skills.skill_id

WHERE student_skills.student_id=?";

$stmtSkill=mysqli_prepare($conn,$sqlSkill);

mysqli_stmt_bind_param(

$stmtSkill,

"i",

$row['student_id']

);

mysqli_stmt_execute($stmtSkill);

$skillResult=mysqli_stmt_get_result($stmtSkill);

$hasSkill = false;

while($skill=mysqli_fetch_assoc($skillResult))
{
    $hasSkill = true;

?>

<span

class="badge bg-light text-dark border me-1 mb-1">

<?php

echo htmlspecialchars($skill['skill_name']);

?>

</span>

<?php

}

if(!$hasSkill)
{
    echo "<span class='text-muted'>No Skills</span>";
}

mysqli_stmt_close($stmtSkill);

?>

</td>

<td>

<?php

if(!empty($row['resume']))
{

?>

<a

href="../uploads/resumes/<?php echo htmlspecialchars($row['resume']); ?>"

target="_blank"

class="btn btn-outline-primary btn-sm">

<i class="bi bi-file-earmark-pdf"></i>

Resume

</a>

<?php

}
else
{

echo "<span class='text-muted'>No Resume</span>";

}

?>

</td>

<td>

<div class="d-flex flex-column gap-2">

<?php

if(!empty($row['github']))
{

?>

<a

href="<?php echo htmlspecialchars($row['github']); ?>"

target="_blank"

class="btn btn-dark btn-sm">

<i class="bi bi-github"></i>

GitHub

</a>

<?php

}

?>

<?php

if(!empty($row['linkedin']))
{

?>

<a

href="<?php echo htmlspecialchars($row['linkedin']); ?>"

target="_blank"

class="btn btn-primary btn-sm">

<i class="bi bi-linkedin"></i>

LinkedIn

</a>

<?php

}

?>

</div>

</td>

<td>

<div class="d-grid gap-2">

<a

href="shortlist.php?id=<?php echo $row['application_id']; ?>"

class="btn btn-success btn-sm">

<i class="bi bi-check-circle"></i>

Shortlist

</a>

<a

href="reject.php?id=<?php echo $row['application_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Reject this applicant?');">

<i class="bi bi-x-circle"></i>

Reject

</a>

<a

href="schedule_interview.php?id=<?php echo $row['application_id']; ?>"

class="btn btn-warning btn-sm">

<i class="bi bi-calendar-event"></i>

Interview

</a>

</div>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="9" class="text-center py-5">

<i class="bi bi-people fs-1 text-muted"></i>

<h5 class="mt-3">

No Applicants Found

</h5>

<p class="text-muted">

No students have applied for this job yet.

</p>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<style>

.stat-card{

background:#fff;

border-radius:12px;

padding:20px;

text-align:center;

box-shadow:0 5px 15px rgba(0,0,0,.08);

transition:.3s;

}

.stat-card:hover{

transform:translateY(-4px);

}

.table td{

vertical-align:middle;

}

.badge{

font-size:13px;

padding:8px 10px;

}

.table img{

border:2px solid #e9ecef;

}

.dashboard-card{

background:#fff;

padding:20px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

</style>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){

const badges=document.querySelectorAll(".badge");

badges.forEach(function(badge){

badge.style.transition=".3s";

badge.addEventListener("mouseenter",function(){

this.style.transform="scale(1.08)";

});

badge.addEventListener("mouseleave",function(){

this.style.transform="scale(1)";

});

});

});

</script>

</body>

</html>

<?php

if(isset($stmt))
{
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

?>