<?php

session_start();

require_once("../config/db.php");

/* ===========================
   CHECK REQUEST METHOD
=========================== */

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    header("Location: ../register.php");
    exit();
}

/* ===========================
   GET FORM DATA
=========================== */

$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];

/* ===========================
   BASIC VALIDATION
=========================== */

if(
    empty($full_name) ||
    empty($email) ||
    empty($phone) ||
    empty($password) ||
    empty($confirm_password) ||
    empty($role)
)
{
    header("Location: ../register.php?error=Please fill all fields.");
    exit();
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL))
{
    header("Location: ../register.php?error=Invalid email address.");
    exit();
}

if($password != $confirm_password)
{
    header("Location: ../register.php?error=Passwords do not match.");
    exit();
}

if(strlen($password) < 8)
{
    header("Location: ../register.php?error=Password must be at least 8 characters.");
    exit();
}

if($role != "student" && $role != "company")
{
    header("Location: ../register.php?error=Invalid role selected.");
    exit();
}

/* ===========================
   CHECK EMAIL EXISTS
=========================== */

$sql = "SELECT user_id FROM users WHERE email=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) > 0)
{
    mysqli_stmt_close($stmt);

    header("Location: ../register.php?error=Email already registered.");
    exit();
}

mysqli_stmt_close($stmt);

/* ===========================
   HASH PASSWORD
=========================== */

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* ===========================
   INSERT USER
=========================== */

$sql = "INSERT INTO users(full_name,email,password,role,phone)
        VALUES(?,?,?,?,?)";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"sssss",
$full_name,
$email,
$hashed_password,
$role,
$phone
);

if(!mysqli_stmt_execute($stmt))
{
    die("Registration Failed.");
}

$user_id = mysqli_insert_id($conn);

mysqli_stmt_close($stmt);

/* ===========================
   CREATE PROFILE
=========================== */

if($role=="student")
{

    $sql="INSERT INTO students(user_id)
          VALUES(?)";

    $stmt=mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

}

else
{

    $sql="INSERT INTO companies(user_id)
          VALUES(?)";

    $stmt=mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

}

/* ===========================
   SUCCESS
=========================== */

header("Location: ../login.php?success=Account created successfully. Please login.");

exit();

?>