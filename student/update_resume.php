<?php

session_start();

include("../includes/auth_check.php");
requireRole("student");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"]!="POST")
{
    header("Location: resume.php");
    exit();
}

/*==========================================
GET STUDENT
==========================================*/

$sql="SELECT
student_id,
resume
FROM students
WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$student=mysqli_fetch_assoc($result);

$student_id=$student['student_id'];

$old_resume=$student['resume'];

mysqli_stmt_close($stmt);

/*==========================================
CHECK FILE
==========================================*/

if(!isset($_FILES['resume']))
{
    header("Location: resume.php?error=Please select a file.");
    exit();
}

$file=$_FILES['resume'];

if($file['error']!=0)
{
    header("Location: resume.php?error=Upload failed.");
    exit();
}

/*==========================================
SIZE CHECK
==========================================*/

$maxSize=2*1024*1024;

if($file['size']>$maxSize)
{
    header("Location: resume.php?error=Maximum file size is 2 MB.");
    exit();
}

/*==========================================
MIME CHECK
==========================================*/

$finfo=finfo_open(FILEINFO_MIME_TYPE);

$mime=finfo_file($finfo,$file['tmp_name']);

finfo_close($finfo);

if($mime!="application/pdf")
{
    header("Location: resume.php?error=Only PDF files are allowed.");
    exit();
}

/*==========================================
CREATE DIRECTORY
==========================================*/

$uploadDir="../uploads/resumes/";

if(!is_dir($uploadDir))
{
    mkdir($uploadDir,0777,true);
}

/*==========================================
DELETE OLD RESUME
==========================================*/

if(!empty($old_resume))
{
    $oldFile="../".$old_resume;

    if(file_exists($oldFile))
    {
        unlink($oldFile);
    }
}

/*==========================================
UPLOAD
==========================================*/

$newFileName="resume_".$student_id."_".time().".pdf";

$destination=$uploadDir.$newFileName;

if(!move_uploaded_file($file['tmp_name'],$destination))
{
    header("Location: resume.php?error=Unable to upload file.");
    exit();
}

$dbPath="uploads/resumes/".$newFileName;

/*==========================================
UPDATE DATABASE
==========================================*/

$sql="UPDATE students
SET resume=?
WHERE student_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(
$stmt,
"si",
$dbPath,
$student_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/*==========================================
SUCCESS
==========================================*/

header("Location: resume.php?success=Resume uploaded successfully.");

exit();

?>