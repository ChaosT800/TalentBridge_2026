<div class="sidebar">

    <div class="sidebar-top">

        <div class="sidebar-logo">

            <i class="bi bi-building-fill"></i>

            <span>TalentBridge</span>

        </div>

    </div>

    <div class="sidebar-menu">

        <a href="dashboard.php" class="active">

            <i class="bi bi-grid-fill"></i>

            <span>Dashboard</span>

        </a>

        <a href="complete_profile.php">

            <i class="bi bi-building"></i>

            <span>Company Profile</span>

        </a>

        <a href="post_job.php">

            <i class="bi bi-plus-circle-fill"></i>

            <span>Post Job</span>

        </a>

        <a href="manage_jobs.php">

            <i class="bi bi-briefcase-fill"></i>

            <span>Manage Jobs</span>

        </a>

        <a href="view_applicants.php">

            <i class="bi bi-people-fill"></i>

            <span>Applicants</span>

        </a>

        <a href="interviews.php">

            <i class="bi bi-calendar-event-fill"></i>

            <span>Interviews</span>

        </a>

        <a href="notifications.php">

            <i class="bi bi-bell-fill"></i>

            <span>Notifications</span>

        </a>

    </div>

    <div class="sidebar-bottom">

        <div class="profile-box">

            <img src="../assets/images/default-company.png" alt="Company">

            <div>

                <strong>

                    <?php echo htmlspecialchars($_SESSION['name']); ?>

                </strong>

                <br>

                <small>

                    Recruiter

                </small>

            </div>

        </div>

        <a href="../auth/logout.php" class="logout-btn">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>