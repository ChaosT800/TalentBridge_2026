<?php

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

/* ==========================================
   FETCH STUDENT DETAILS
========================================== */

$sql = "SELECT *
        FROM students
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

/* ==========================================
   FETCH BRANCHES
========================================== */

$branches = mysqli_query($conn,"SELECT * FROM branches ORDER BY branch_name");

/* ==========================================
   FETCH SKILLS
========================================== */

$skills = mysqli_query($conn,"SELECT * FROM skills ORDER BY skill_name");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Complete Profile</title>

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

<i class="bi bi-person-circle"></i>

Complete Your Profile

</h2>

<form
action="update_profile.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Roll Number

</label>

<input
type="text"
name="roll_number"
class="form-control"
value="<?php echo htmlspecialchars($student['roll_number'] ?? ''); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Branch

</label>

<select
name="branch_id"
class="form-select"
required>

<option value="">Select Branch</option>

<?php

while($branch=mysqli_fetch_assoc($branches))
{

?>

<option
value="<?php echo $branch['branch_id']; ?>"

<?php

if(($student['branch_id'] ?? '')==$branch['branch_id'])
echo "selected";

?>

>

<?php echo htmlspecialchars($branch['branch_name']); ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

CGPA

</label>

<input
type="number"
step="0.01"
min="0"
max="10"
name="cgpa"
class="form-control"
value="<?php echo htmlspecialchars($student['cgpa'] ?? ''); ?>"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Graduation Year

</label>

<input
type="number"
name="graduation_year"
class="form-control"
min="2024"
max="2050"
value="<?php echo htmlspecialchars($student['graduation_year'] ?? ''); ?>"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Backlogs

</label>

<input
type="number"
name="backlogs"
class="form-control"
min="0"
value="<?php echo htmlspecialchars($student['backlogs'] ?? 0); ?>">

</div>

<div class="col-12 mb-3">

<label class="form-label">

About Yourself

</label>

<textarea
name="about"
class="form-control"
rows="5"><?php echo htmlspecialchars($student['about'] ?? ''); ?></textarea>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

LinkedIn

</label>

<input
type="url"
name="linkedin"
class="form-control"
value="<?php echo htmlspecialchars($student['linkedin'] ?? ''); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

GitHub

</label>

<input
type="url"
name="github"
class="form-control"
value="<?php echo htmlspecialchars($student['github'] ?? ''); ?>">

</div>

<div class="col-md-6 mb-4">

<label class="form-label">

Upload Resume (PDF)

</label>

<input
type="file"
name="resume"
class="form-control"
accept=".pdf">

</div>

<div class="col-md-6 mb-4">

<label class="form-label">

Profile Photo

</label>

<input
type="file"
name="profile_photo"
class="form-control"
accept=".jpg,.jpeg,.png">

</div>

<div class="col-12">

<h4 class="mb-3">

Technical Skills

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
value="<?php echo $skill['skill_id']; ?>">

<label class="form-check-label">

<?php echo htmlspecialchars($skill['skill_name']); ?>

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