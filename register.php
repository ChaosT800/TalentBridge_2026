<?php
session_start();

if(isset($_SESSION['user_id']))
{
    if($_SESSION['role']=="student")
    {
        header("Location: student/dashboard.php");
        exit();
    }

    if($_SESSION['role']=="company")
    {
        header("Location: company/dashboard.php");
        exit();
    }

    if($_SESSION['role']=="admin")
    {
        header("Location: admin/dashboard.php");
        exit();
    }
}

include("includes/auth_header.php");
?>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <div class="auth-card">

                <div class="text-center mb-4">

                    <h1 class="auth-logo">
                        TalentBridge
                    </h1>

                    <h2 class="auth-title">
                        Create Your Account
                    </h2>

                    <p class="auth-subtitle">
                        Join TalentBridge and start your placement journey.
                    </p>

                </div>

                <?php

                if(isset($_GET['error']))
                {
                    echo '
                    <div class="alert alert-danger">
                        '.$_GET['error'].'
                    </div>
                    ';
                }

                if(isset($_GET['success']))
                {
                    echo '
                    <div class="alert alert-success">
                        '.$_GET['success'].'
                    </div>
                    ';
                }

                ?>

                <form
                action="auth/register_process.php"
                method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Full Name

                        </label>

                        <input
                        type="text"
                        name="full_name"
                        class="form-control auth-input"
                        required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Email Address

                        </label>

                        <input
                        type="email"
                        name="email"
                        class="form-control auth-input"
                        required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Phone Number

                        </label>

                        <input
                        type="text"
                        name="phone"
                        class="form-control auth-input"
                        maxlength="15"
                        required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Password

                        </label>

                        <div class="password-wrapper">

                            <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control auth-input"
                            required>

                            <i
                            class="bi bi-eye-slash password-toggle"
                            id="togglePassword">
                            </i>

                        </div>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">

                            Confirm Password

                        </label>

                        <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control auth-input"
                        required>

                    </div>

                    <div class="mb-4">

                        <label class="form-label">

                            Register As

                        </label>

                        <div class="form-check">

                            <input
                            class="form-check-input"
                            type="radio"
                            name="role"
                            value="student"
                            checked>

                            <label class="form-check-label">

                                Student

                            </label>

                        </div>

                        <div class="form-check">

                            <input
                            class="form-check-input"
                            type="radio"
                            name="role"
                            value="company">

                            <label class="form-check-label">

                                Company

                            </label>

                        </div>

                    </div>

                    <button
                    type="submit"
                    class="tb-btn tb-primary w-100">

                        Create Account

                    </button>

                </form>

                <div class="auth-footer">

                    Already have an account?

                    <a href="login.php">

                        Login

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

const toggle=document.getElementById("togglePassword");

const password=document.getElementById("password");

toggle.onclick=function(){

if(password.type==="password")
{
password.type="text";

toggle.classList.remove("bi-eye-slash");

toggle.classList.add("bi-eye");
}
else
{
password.type="password";

toggle.classList.remove("bi-eye");

toggle.classList.add("bi-eye-slash");
}

}

</script>

<?php
include("includes/auth_footer.php");
?>