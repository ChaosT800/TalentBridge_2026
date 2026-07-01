<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

if (!isset($_SESSION['user_id']))
{
    header("Location: ../login.php?error=Please login first.");
    exit();
}

/*
|--------------------------------------------------------------------------
| Usage
|--------------------------------------------------------------------------
| include("../includes/auth_check.php");
|
| requireRole("student");
|
*/

function requireRole($role)
{
    if (!isset($_SESSION['role']))
    {
        session_destroy();

        header("Location: ../login.php");
        exit();
    }

    if ($_SESSION['role'] != $role)
    {
        switch($_SESSION['role'])
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
                header("Location: ../login.php");
        }

        exit();
    }
}
?>