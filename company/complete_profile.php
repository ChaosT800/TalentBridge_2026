<?php

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id=$_SESSION['user_id'];

$sql="SELECT *
FROM companies
WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$company=mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Company Profile</title>

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

<h2 class="mb-4">

<i class="bi bi-building"></i>

Company Profile

</h2>

<?php
if(isset($_GET['success']))
{
?>
<div class="alert alert-success">
<?php echo htmlspecialchars($_GET['success']); ?>
</div>
<?php
}

if(isset($_GET['error']))
{
?>
<div class="alert alert-danger">
<?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php
}
?>

<form
action="update_profile.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Company Name

</label>

<input
type="text"
name="company_name"
class="form-control"
required
value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Industry

</label>

<input
type="text"
name="industry"
class="form-control"
value="<?php echo htmlspecialchars($company['industry'] ?? ''); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Website

</label>

<input
type="url"
name="website"
class="form-control"
value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Location

</label>

<input
type="text"
name="location"
class="form-control"
value="<?php echo htmlspecialchars($company['location'] ?? ''); ?>">

</div>

<div class="col-12 mb-3">

<label class="form-label">

Company Description

</label>

<textarea
name="description"
rows="6"
class="form-control"><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>

</div>

<div class="col-md-6 mb-4">

<label class="form-label">

Company Logo

</label>

<input
type="file"
name="logo"
accept=".jpg,.jpeg,.png"
class="form-control">

</div>

<div class="col-md-6">

<label class="form-label">

Verification Status

</label>

<input
type="text"
class="form-control"
readonly
value="<?php echo ($company['verified']) ? 'Verified ✅' : 'Pending Verification'; ?>">

</div>

<?php

if(!empty($company['logo']))
{

?>

<div class="col-12 mb-4">

<img
src="../<?php echo htmlspecialchars($company['logo']); ?>"
style="height:120px;border-radius:12px;">

</div>

<?php

}

?>

<div class="col-12">

<button
type="submit"
class="btn btn-primary btn-lg">

<i class="bi bi-check-circle-fill"></i>

Save Profile

</button>

</div>

</div>

</form>

</div>

</div>

</div>

</div>

</body>

</html>