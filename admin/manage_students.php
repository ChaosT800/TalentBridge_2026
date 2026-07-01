<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
BLOCK / UNBLOCK
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

AND role='student'";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$user
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: manage_students.php");

exit();

}

/*=========================================
DELETE
=========================================*/

if(isset($_GET['delete']) && is_numeric($_GET['delete']))
{

$user=intval($_GET['delete']);

mysqli_begin_transaction($conn);

try
{

$sql="SELECT student_id

FROM students

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
    throw new Exception("Student not found.");
}

$student=mysqli_fetch_assoc($result);

$student_id=$student['student_id'];

mysqli_stmt_close($stmt);

/* Delete Applications */

$sql="DELETE FROM applications

WHERE student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete Skills */

$sql="DELETE FROM student_skills

WHERE student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete Student */

$sql="DELETE FROM students

WHERE student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$student_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Delete User */

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

header("Location: manage_students.php?success=Student deleted.");

exit();

}
catch(Exception $e)
{

mysqli_rollback($conn);

header("Location: manage_students.php?error=".$e->getMessage());

exit();

}

}

/*=========================================
FILTERS
=========================================*/

$search="";

$branch="";

$year="";

if(isset($_GET['search']))
$search=trim($_GET['search']);

if(isset($_GET['branch']))
$branch=$_GET['branch'];

if(isset($_GET['year']))
$year=$_GET['year'];

/*=========================================
BRANCH LIST
=========================================*/

$branches=mysqli_query($conn,
"SELECT *
FROM branches
ORDER BY branch_name");

/*=========================================
FETCH STUDENTS
=========================================*/

$sql="SELECT

u.user_id,

u.full_name,

u.email,

u.phone,

u.account_status,

s.student_id,

s.roll_number,

s.cgpa,

s.backlogs,

s.graduation_year,

s.profile_photo,

b.branch_name

FROM students s

INNER JOIN users u

ON s.user_id=u.user_id

LEFT JOIN branches b

ON s.branch_id=b.branch_id

WHERE 1=1";

$params=[];

$types="";

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

if($branch!="")
{

$sql.=" AND b.branch_id=?";

$types.="i";

$params[]=$branch;

}

if($year!="")
{

$sql.=" AND s.graduation_year=?";

$types.="s";

$params[]=$year;

}

$sql.="

ORDER BY

u.full_name";

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

$students=mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Manage Students

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

Manage Students

</h2>

<form
method="GET"
class="row g-3 mb-4">

<div class="col-md-4">

<input

type="text"

name="search"

class="form-control"

placeholder="Search Name / Roll Number"

value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-3">

<select

name="branch"

class="form-select">

<option value="">

All Branches

</option>

<?php

mysqli_data_seek($branches,0);

while($b=mysqli_fetch_assoc($branches))
{

?>

<option

value="<?php echo $b['branch_id']; ?>"

<?php

if($branch==$b['branch_id'])

echo "selected";

?>

>

<?php echo htmlspecialchars($b['branch_name']); ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-3">

<input

type="number"

name="year"

class="form-control"

placeholder="Graduation Year"

value="<?php echo htmlspecialchars($year); ?>">

</div>

<div class="col-md-2">

<button

class="btn btn-primary w-100">

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

<th>Roll No</th>

<th>Branch</th>

<th>CGPA</th>

<th>Backlogs</th>

<th>Status</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($students)>0)
{

while($row=mysqli_fetch_assoc($students))
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

class="rounded-circle me-3">

<?php

}

?>

<div>

<strong>

<?php echo htmlspecialchars($row['full_name']); ?>

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

<?php echo htmlspecialchars($row['roll_number']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['branch_name']); ?>

</td>

<td>

<span class="badge bg-info">

<?php echo number_format($row['cgpa'],2); ?>

</span>

</td>

<td>

<?php

if($row['backlogs']==0)
{

?>

<span class="badge bg-success">

No Backlogs

</span>

<?php

}
else
{

?>

<span class="badge bg-danger">

<?php echo $row['backlogs']; ?>

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

href="view_student.php?id=<?php echo $row['student_id']; ?>"

class="btn btn-primary btn-sm mb-1">

<i class="bi bi-eye"></i>

View

</a>

<br>

<a

href="manage_students.php?toggle=<?php echo $row['user_id']; ?>"

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

href="manage_students.php?delete=<?php echo $row['user_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this student permanently?');">

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

colspan="7"

class="text-center text-muted py-5">

No students found.

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

min-width:90px;

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