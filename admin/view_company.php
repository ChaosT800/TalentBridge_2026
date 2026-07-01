<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: manage_companies.php");
    exit();
}

$company_id=intval($_GET['id']);

/*=========================================
FETCH COMPANY
=========================================*/

$sql="SELECT

u.full_name,

u.email,

u.phone,

u.account_status,

c.company_name,

c.industry,

c.website,

c.location,

c.description,

c.logo,

c.verified

FROM companies c

INNER JOIN users u

ON c.user_id=u.user_id

WHERE c.company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: manage_companies.php");
    exit();
}

$company=mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=========================================
COMPANY STATISTICS
=========================================*/

$sql="SELECT

COUNT(*) jobs,

(

SELECT COUNT(*)

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?

) applications,

(

SELECT AVG(match_score)

FROM applications a

INNER JOIN jobs j

ON a.job_id=j.job_id

WHERE j.company_id=?

) average_score

FROM jobs

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"iii",

$company_id,

$company_id,

$company_id

);

mysqli_stmt_execute($stmt);

$stats=mysqli_fetch_assoc(
mysqli_stmt_get_result($stmt)
);

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

Company Profile

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

<div class="row">

<div class="col-md-3 text-center">

<?php

if(!empty($company['logo']))
{

?>

<img

src="../<?php echo htmlspecialchars($company['logo']); ?>"

class="img-fluid rounded-circle"

style="width:180px;height:180px;object-fit:cover;">

<?php

}
else
{

?>

<img

src="../assets/images/default-company.png"

class="img-fluid rounded-circle"

style="width:180px;height:180px;">

<?php

}

?>

<h3 class="mt-3">

<?php echo htmlspecialchars($company['company_name']); ?>

</h3>

<span class="badge bg-<?php echo $company['verified'] ? "success":"warning"; ?>">

<?php

echo $company['verified']

? "Verified"

: "Pending";

?>

</span>

</div>

<div class="col-md-9">

<h4 class="mb-3">

Company Information

</h4>

<table class="table table-bordered">

<tr>

<th>Contact Person</th>

<td><?php echo htmlspecialchars($company['full_name']); ?></td>

</tr>

<tr>

<th>Email</th>

<td><?php echo htmlspecialchars($company['email']); ?></td>

</tr>

<tr>

<th>Phone</th>

<td><?php echo htmlspecialchars($company['phone']); ?></td>

</tr>

<tr>

<th>Industry</th>

<td><?php echo htmlspecialchars($company['industry']); ?></td>

</tr>

<tr>

<th>Website</th>

<td>

<?php

if(!empty($company['website']))
{

?>

<a

href="<?php echo htmlspecialchars($company['website']); ?>"

target="_blank">

<?php echo htmlspecialchars($company['website']); ?>

</a>

<?php

}
else
{

echo "<span class='text-muted'>Not Provided</span>";

}

?>

</td>

</tr>

<tr>

<th>Location</th>

<td>

<?php echo htmlspecialchars($company['location']); ?>

</td>

</tr>

<tr>

<th>Description</th>

<td>

<?php

echo !empty($company['description'])

? nl2br(htmlspecialchars($company['description']))

: "<span class='text-muted'>No Description Available</span>";

?>

</td>

</tr>

<tr>

<th>Account Status</th>

<td>

<?php

if($company['account_status']=="active")
{

?>

<span class="badge bg-success">

Active

</span>

<?php

}
else
{

?>

<span class="badge bg-danger">

Blocked

</span>

<?php

}

?>

</td>

</tr>

<tr>

<th>Verification</th>

<td>

<?php

if($company['verified'])
{

?>

<span class="badge bg-success">

Verified Company

</span>

<?php

}
else
{

?>

<span class="badge bg-warning text-dark">

Pending Verification

</span>

<?php

}

?>

</td>

</tr>

</table>

<div class="row mt-4">

<div class="col-md-4">

<div class="stat-card">

<h3>

<?php echo $stats['jobs']; ?>

</h3>

<p>

Jobs Posted

</p>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<h3>

<?php echo $stats['applications']; ?>

</h3>

<p>

Applications Received

</p>

</div>

</div>

<div class="col-md-4">

<div class="stat-card">

<h3>

<?php echo number_format($stats['average_score'] ?? 0,2); ?>%

</h3>

<p>

Average Match Score

</p>

</div>

</div>

</div>

<div class="mt-4">

<a

href="manage_companies.php"

class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

</div>

</div>

</div>

</div>

<style>

.dashboard-card{

background:#ffffff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.stat-card{

background:#f8f9fa;

padding:20px;

border-radius:12px;

text-align:center;

box-shadow:0 2px 8px rgba(0,0,0,.08);

}

table th{

width:180px;

}

.badge{

font-size:13px;

padding:8px 12px;

}

img{

border:3px solid #dee2e6;

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