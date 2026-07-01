<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
VERIFY / UNVERIFY COMPANY
=========================================*/

if(isset($_GET['verify']) && is_numeric($_GET['verify']))
{

$id=intval($_GET['verify']);

$sql="UPDATE companies

SET verified=

CASE

WHEN verified=1

THEN 0

ELSE 1

END

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: manage_companies.php");

exit();

}

/*=========================================
BLOCK / UNBLOCK COMPANY ACCOUNT
=========================================*/

if(isset($_GET['toggle']) && is_numeric($_GET['toggle']))
{

$user=intval($_GET['toggle']);

$sql="UPDATE users

SET account_status=

CASE

WHEN account_status='active'

THEN 'blocked'

ELSE 'active'

END

WHERE user_id=?

AND role='company'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: manage_companies.php");

exit();

}

/*=========================================
DELETE COMPANY
=========================================*/

if(isset($_GET['delete']) && is_numeric($_GET['delete']))
{

$user=intval($_GET['delete']);

mysqli_begin_transaction($conn);

try
{

$sql="SELECT company_id

FROM companies

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user
);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    throw new Exception("Company not found.");
}

$company=mysqli_fetch_assoc($result);

$company_id=$company['company_id'];

mysqli_stmt_close($stmt);

/* DELETE JOBS */

$sql="DELETE FROM jobs

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* DELETE COMPANY */

$sql="DELETE FROM companies

WHERE company_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$company_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* DELETE USER */

$sql="DELETE FROM users

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

mysqli_commit($conn);

header("Location: manage_companies.php?success=Company deleted.");

exit();

}
catch(Exception $e)
{

mysqli_rollback($conn);

header("Location: manage_companies.php?error=".$e->getMessage());

exit();

}

}

/*=========================================
FILTERS
=========================================*/

$search="";

$status="";

if(isset($_GET['search']))
$search=trim($_GET['search']);

if(isset($_GET['status']))
$status=$_GET['status'];

/*=========================================
FETCH COMPANIES
=========================================*/

$sql="SELECT

c.company_id,

c.user_id,

c.company_name,

c.industry,

c.website,

c.location,

c.logo,

c.verified,

u.email,

u.phone,

u.account_status

FROM companies c

INNER JOIN users u

ON c.user_id=u.user_id

WHERE 1=1";

$params=[];

$types="";

if($search!="")
{

$sql.=" AND (

c.company_name LIKE ?

OR

c.industry LIKE ?

)";

$types.="ss";

$like="%".$search."%";

$params[]=$like;

$params[]=$like;

}

if($status!="")
{

$sql.=" AND c.verified=?";

$types.="i";

$params[]=$status;

}

$sql.="

ORDER BY

c.company_name";

$stmt=mysqli_prepare($conn,$sql);

if(count($params)>0)
{

mysqli_stmt_bind_param(

$stmt,

$types,

...$params

);

}

mysqli_stmt_execute($stmt);

$companies=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Manage Companies

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

<h2 class="mb-4">

Manage Companies

</h2>

<form

method="GET"

class="row g-3 mb-4">

<div class="col-md-5">

<input

type="text"

name="search"

class="form-control"

placeholder="Search Company or Industry"

value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-3">

<select

name="status"

class="form-select">

<option value="">

All Companies

</option>

<option

value="1"

<?php if($status==="1") echo "selected"; ?>

>

Verified

</option>

<option

value="0"

<?php if($status==="0") echo "selected"; ?>

>

Pending

</option>

</select>

</div>

<div class="col-md-2">

<button

class="btn btn-primary w-100">

Search

</button>

</div>

<div class="col-md-2">

<a

href="manage_companies.php"

class="btn btn-secondary w-100">

Reset

</a>

</div>

</form>

<div class="dashboard-card">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Company</th>

<th>Industry</th>

<th>Location</th>

<th>Status</th>

<th>Account</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($companies)>0)
{

while($row=mysqli_fetch_assoc($companies))
{

?>

<tr>

<td>

<div class="d-flex align-items-center">

<?php

if(!empty($row['logo']))
{

?>

<img

src="../<?php echo htmlspecialchars($row['logo']); ?>"

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

src="../assets/images/default-company.png"

width="55"

height="55"

class="rounded-circle me-3">

<?php

}

?>

<div>

<strong>

<?php echo htmlspecialchars($row['company_name']); ?>

</strong>

<br>

<small class="text-muted">

<?php echo htmlspecialchars($row['email']); ?>

</small>

<br>

<small class="text-muted">

<?php echo htmlspecialchars($row['phone']); ?>

</small>

</div>

</div>

</td>

<td>

<?php echo htmlspecialchars($row['industry']); ?>

</td>

<td>

<?php

echo !empty($row['location'])

? htmlspecialchars($row['location'])

: "-";

?>

</td>

<td>

<?php

if($row['verified'])
{

?>

<span class="badge bg-success">

Verified

</span>

<?php

}
else
{

?>

<span class="badge bg-warning text-dark">

Pending

</span>

<?php

}

?>

</td>

<td>

<?php

if($row['account_status']=="active")
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

<td>

<a

href="view_company.php?id=<?php echo $row['company_id']; ?>"

class="btn btn-primary btn-sm mb-1">

<i class="bi bi-eye"></i>

View

</a>

<br>

<a

href="verify_company.php?id=<?php echo $row['company_id']; ?>"

class="btn btn-success btn-sm mb-1"

onclick="return confirm('Change verification status?');">

<i class="bi bi-patch-check"></i>

<?php

if($row['verified'])

echo "Unverify";

else

echo "Verify";

?>

</a>

<br>

<a

href="manage_companies.php?toggle=<?php echo $row['user_id']; ?>"

class="btn btn-warning btn-sm mb-1"

onclick="return confirm('Change account status?');">

<i class="bi bi-lock"></i>

<?php

if($row['account_status']=="active")

echo "Block";

else

echo "Unblock";

?>

</a>

<br>

<a

href="manage_companies.php?delete=<?php echo $row['user_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this company permanently?');">

<i class="bi bi-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td

colspan="6"

class="text-center py-5 text-muted">

No companies found.

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

.dashboard-card{

background:#ffffff;

padding:25px;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

}

table td{

vertical-align:middle;

}

.badge{

font-size:13px;

padding:8px 12px;

}

img{

border:2px solid #dee2e6;

}

.btn{

min-width:95px;

margin-bottom:5px;

}

</style>

</div> <!-- container-fluid -->

</div> <!-- main-content -->

</div> <!-- dashboard-layout -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
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