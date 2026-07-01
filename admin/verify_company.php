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
VERIFY / UNVERIFY
=========================================*/

if(isset($_POST['action']))
{

$verified=($_POST['action']=="verify") ? 1 : 0;

$sql="UPDATE companies
SET verified=?
WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"ii",
$verified,
$company_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: manage_companies.php?success=Company verification updated.");

exit();

}

/*=========================================
FETCH COMPANY
=========================================*/

$sql="SELECT

c.*,

u.full_name,

u.email,

u.phone,

u.account_status

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

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>

Verify Company

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

<div class="container mt-4">

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

<?php echo $company['verified'] ? "Verified" : "Pending"; ?>

</span>

</div>

<div class="col-md-9">

<h4 class="mb-3">

Company Verification

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
<td><?php echo htmlspecialchars($company['website']); ?></td>
</tr>

<tr>
<th>Location</th>
<td><?php echo htmlspecialchars($company['location']); ?></td>
</tr>

<tr>
<th>Description</th>
<td><?php echo nl2br(htmlspecialchars($company['description'])); ?></td>
</tr>

</table>

<form method="POST" class="mt-4">

<button

type="submit"

name="action"

value="verify"

class="btn btn-success">

<i class="bi bi-patch-check-fill"></i>

Verify Company

</button>

<button

type="submit"

name="action"

value="reject"

class="btn btn-danger ms-2">

<i class="bi bi-x-circle-fill"></i>

Reject Verification

</button>

<a

href="manage_companies.php"

class="btn btn-secondary ms-2">

<i class="bi bi-arrow-left"></i>

Back

</a>

</form>

</div>

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

table th{

width:180px;

background:#f8f9fa;

}

.badge{

font-size:13px;

padding:8px 12px;

}

img{

border:3px solid #dee2e6;

}

.btn{

min-width:140px;

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