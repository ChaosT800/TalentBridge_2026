<?php

session_start();

require_once("../config/db.php");

/* ===========================
   CHECK REQUEST
=========================== */

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    header("Location: ../login.php");
    exit();
}

/* ===========================
   GET DATA
=========================== */

$email = trim($_POST['email']);
$password = $_POST['password'];

/* ===========================
   VALIDATION
=========================== */

if(empty($email) || empty($password))
{
    header("Location: ../login.php?error=Please enter email and password.");
    exit();
}

/* ===========================
   FETCH USER
=========================== */

$sql = "SELECT
            user_id,
            full_name,
            email,
            password,
            role,
            account_status
        FROM users
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) != 1)
{
    header("Location: ../login.php?error=Invalid email or password.");
    exit();
}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* ===========================
   CHECK ACCOUNT STATUS
=========================== */

if($user['account_status'] != "active")
{
    header("Location: ../login.php?error=Your account is inactive.");
    exit();
}

/* ===========================
   VERIFY PASSWORD
=========================== */

if(!password_verify($password, $user['password']))
{
    header("Location: ../login.php?error=Invalid email or password.");
    exit();
}

/* ===========================
   CREATE SESSION
=========================== */

session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['name']    = $user['full_name'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

/* ===========================
   ROLE REDIRECT
=========================== */

switch($user['role'])
{

    case "student":

        header("Location: ../student/dashboard.php");
        break;

    case "company":

        header("Location: ../company/dashboard.php");
        break;

    case "admin":

        header("Location: ../admin/dashboard.php");
        break;

    default:

        session_destroy();

        header("Location: ../login.php?error=Invalid user role.");

}

exit();

?>