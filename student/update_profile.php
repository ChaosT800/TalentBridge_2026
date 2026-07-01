<?php

session_start();

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    header("Location: complete_profile.php");
    exit();
}

/* ==========================================
   GET STUDENT ID
========================================== */

$sql = "SELECT student_id
        FROM students
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

$student_id = $student['student_id'];

mysqli_stmt_close($stmt);

/* ==========================================
   GET FORM DATA
========================================== */

$roll_number = trim($_POST['roll_number']);
$branch_id = intval($_POST['branch_id']);
$cgpa = floatval($_POST['cgpa']);
$graduation_year = intval($_POST['graduation_year']);
$backlogs = intval($_POST['backlogs']);
$about = trim($_POST['about']);
$linkedin = trim($_POST['linkedin']);
$github = trim($_POST['github']);

$resume_path = null;
$photo_path = null;

/* ==========================================
   RESUME UPLOAD
========================================== */

if(isset($_FILES['resume']) && $_FILES['resume']['error']==0)
{

    $extension = strtolower(pathinfo($_FILES['resume']['name'],PATHINFO_EXTENSION));

    if($extension=="pdf")
    {

        $resume_name = "resume_".$student_id."_".time().".pdf";

        $resume_path = "../uploads/resumes/".$resume_name;

        move_uploaded_file(
            $_FILES['resume']['tmp_name'],
            $resume_path
        );

        $resume_path = "uploads/resumes/".$resume_name;

    }

}

/* ==========================================
   PROFILE PHOTO
========================================== */

if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error']==0)
{

    $extension = strtolower(pathinfo($_FILES['profile_photo']['name'],PATHINFO_EXTENSION));

    if(
        $extension=="jpg" ||
        $extension=="jpeg" ||
        $extension=="png"
    )
    {

        $photo_name = "profile_".$student_id."_".time().".".$extension;

        $photo_path = "../uploads/profiles/".$photo_name;

        move_uploaded_file(
            $_FILES['profile_photo']['tmp_name'],
            $photo_path
        );

        $photo_path = "uploads/profiles/".$photo_name;

    }

}

/* ==========================================
   UPDATE STUDENT
========================================== */

$sql = "UPDATE students SET

roll_number=?,
branch_id=?,
cgpa=?,
graduation_year=?,
backlogs=?,
about=?,
linkedin=?,
github=?";

if($resume_path!=null)
{
    $sql .= ",resume=?";
}

if($photo_path!=null)
{
    $sql .= ",profile_photo=?";
}

$sql .= " WHERE user_id=?";

/* ==========================================
   PREPARE
========================================== */

$stmt = mysqli_prepare($conn,$sql);

$params = [
    $roll_number,
    $branch_id,
    $cgpa,
    $graduation_year,
    $backlogs,
    $about,
    $linkedin,
    $github
];

$types = "sidiisss";

if($resume_path!=null)
{
    $types.="s";
    $params[]=$resume_path;
}

if($photo_path!=null)
{
    $types.="s";
    $params[]=$photo_path;
}

$types.="i";
$params[]=$user_id;

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* ==========================================
   SAVE SKILLS
========================================== */

mysqli_query(
$conn,
"DELETE FROM student_skills
WHERE student_id=".$student_id
);

if(isset($_POST['skills']))
{

foreach($_POST['skills'] as $skill)
{

$skill = intval($skill);

$sql="INSERT INTO student_skills(

student_id,
skill_id,
proficiency

)

VALUES(

?,
?,
'Beginner'

)";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"ii",
$student_id,
$skill
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

}

}

/* ==========================================
   SUCCESS
========================================== */

header("Location: complete_profile.php?success=Profile updated successfully.");

exit();

?>