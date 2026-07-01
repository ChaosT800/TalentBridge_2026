<?php

include("../includes/auth_check.php");
requireRole("admin");

require_once("../config/db.php");

/*=========================================
CSV DOWNLOAD
=========================================*/

$filename="placement_report_".date("Ymd_His").".csv";

header("Content-Type: text/csv");

header("Content-Disposition: attachment; filename=".$filename);

$output=fopen("php://output","w");

/*=========================================
CSV HEADERS
=========================================*/

fputcsv($output,[

"Student Name",

"Roll Number",

"Branch",

"CGPA",

"Company",

"Job Title",

"Package",

"Selection Date"

]);

/*=========================================
FETCH DATA
=========================================*/

$sql="SELECT

u.full_name,

s.roll_number,

b.branch_name,

s.cgpa,

c.company_name,

j.job_title,

j.salary_package,

a.applied_at

FROM applications a

INNER JOIN students s

ON a.student_id=s.student_id

INNER JOIN users u

ON s.user_id=u.user_id

LEFT JOIN branches b

ON s.branch_id=b.branch_id

INNER JOIN jobs j

ON a.job_id=j.job_id

INNER JOIN companies c

ON j.company_id=c.company_id

WHERE

a.application_status='Selected'

ORDER BY

u.full_name";

$result=mysqli_query($conn,$sql);

<?php

/*=========================================
WRITE CSV DATA
=========================================*/

while($row=mysqli_fetch_assoc($result))
{

fputcsv($output,[

$row['full_name'],

$row['roll_number'],

$row['branch_name'],

$row['cgpa'],

$row['company_name'],

$row['job_title'],

$row['salary_package'],

date(

"d-m-Y",

strtotime($row['applied_at'])

)

]);

}

/*=========================================
CLOSE FILE
=========================================*/

fclose($output);

mysqli_close($conn);

exit();

?>