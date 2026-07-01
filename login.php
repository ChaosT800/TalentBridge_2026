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
                        Welcome Back
                    </h2>

                    <p class="auth-subtitle">
                        Login to continue your placement journey.
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

                <form action="auth/login_process.php" method="POST">

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

                            Password

                        </label>

                        <div class="password-wrapper">

                            <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control auth-input"
                            required>

                            <i
                            class="bi bi-eye-slash password-toggle"
                            id="togglePassword">
                            </i>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div class="form-check">

                            <input
                            class="form-check-input"
                            type="checkbox"
                            id="remember">

                            <label
                            class="form-check-label"
                            for="remember">

                                Remember Me

                            </label>

                        </div>

                        <a href="#" class="text-decoration-none">

                            Forgot Password?

                        </a>

                    </div>

                    <button
                    type="submit"
                    class="tb-btn tb-primary w-100">

                        Login

                    </button>

                </form>

                <div class="auth-footer">

                    Don't have an account?

                    <a href="register.php">

                        Register

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