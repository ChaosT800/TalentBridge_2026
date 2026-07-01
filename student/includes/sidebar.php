<div class="sidebar">

    <div class="sidebar-top">

        <div class="sidebar-logo">

            <i class="bi bi-mortarboard-fill"></i>

            <span>TalentBridge</span>

        </div>

    </div>

    <div class="sidebar-menu">

        <a href="dashboard.php" class="active">

            <i class="bi bi-grid-fill"></i>

            <span>Dashboard</span>

        </a>

        <a href="complete_profile.php">

            <i class="bi bi-person-circle"></i>

            <span>Complete Profile</span>

        </a>

        <a href="browse_jobs.php">

            <i class="bi bi-briefcase-fill"></i>

            <span>Browse Jobs</span>

        </a>

        <a href="applications.php">

            <i class="bi bi-file-earmark-text-fill"></i>

            <span>Applications</span>

        </a>

        <a href="resume.php">

            <i class="bi bi-file-earmark-person-fill"></i>

            <span>Resume</span>

        </a>

        <a href="notifications.php">

            <i class="bi bi-bell-fill"></i>

            <span>Notifications</span>

            <?php

$count=mysqli_query(

$conn,

"SELECT COUNT(*) total

FROM notifications

WHERE user_id=".$_SESSION['user_id']."

AND is_read=0"

);

$badge=mysqli_fetch_assoc($count);

?>

<small class="badge-count">

<?php echo $badge['total']; ?>

</small>

        </a>

    </div>

    <div class="sidebar-bottom">

        <div class="profile-box">

            <img
            src="../assets/images/default-user.png"
            alt="Profile">

            <div>

                <strong>

                    <?php echo $_SESSION['name']; ?>

                </strong>

                <br>

                <small>

                    Student

                </small>

            </div>

        </div>

        <a href="../auth/logout.php" class="logout-btn">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>