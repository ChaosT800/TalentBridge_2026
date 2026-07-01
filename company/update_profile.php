<?php

session_start();

include("../includes/auth_check.php");
requireRole("company");

require_once("../config/db.php");

$user_id = $_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST")
{
    header("Location: complete_profile.php");
    exit();
}

/*=================================================
GET COMPANY
=================================================*/

$sql = "SELECT company_id,logo
        FROM companies
        WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    header("Location: dashboard.php");
    exit();
}

$company = mysqli_fetch_assoc($result);

$company_id = $company['company_id'];

$old_logo = $company['logo'];

mysqli_stmt_close($stmt);

/*=================================================
GET FORM DATA
=================================================*/

$company_name = trim($_POST['company_name']);
$industry     = trim($_POST['industry']);
$website      = trim($_POST['website']);
$location     = trim($_POST['location']);
$description  = trim($_POST['description']);

$logo_path = $old_logo;

/*=================================================
UPLOAD LOGO
=================================================*/

if(isset($_FILES['logo']) && $_FILES['logo']['error']==0)
{

    $allowed = [
        "image/jpeg",
        "image/png"
    ];

    $mime = mime_content_type($_FILES['logo']['tmp_name']);

    if(!in_array($mime,$allowed))
    {
        header("Location: complete_profile.php?error=Only JPG and PNG images are allowed.");
        exit();
    }

    if($_FILES['logo']['size'] > (2*1024*1024))
    {
        header("Location: complete_profile.php?error=Maximum logo size is 2 MB.");
        exit();
    }

    if(!is_dir("../uploads/company_logos"))
    {
        mkdir("../uploads/company_logos",0777,true);
    }

    if(!empty($old_logo))
    {
        $old="../".$old_logo;

        if(file_exists($old))
        {
            unlink($old);
        }
    }

    $extension = strtolower(pathinfo($_FILES['logo']['name'],PATHINFO_EXTENSION));

    $filename = "company_".$company_id."_".time().".".$extension;

    $destination = "../uploads/company_logos/".$filename;

    if(move_uploaded_file($_FILES['logo']['tmp_name'],$destination))
    {
        $logo_path = "uploads/company_logos/".$filename;
    }

}

/*=================================================
UPDATE COMPANY
=================================================*/

$sql="UPDATE companies SET

company_name=?,
industry=?,
website=?,
location=?,
description=?,
logo=?

WHERE user_id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"ssssssi",

$company_name,
$industry,
$website,
$location,
$description,
$logo_path,
$user_id

);

if(mysqli_stmt_execute($stmt))
{

    header("Location: complete_profile.php?success=Company profile updated successfully.");

}
else
{

    header("Location: complete_profile.php?error=Unable to update profile.");

}

mysqli_stmt_close($stmt);

exit();

?>