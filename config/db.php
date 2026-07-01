<?php

$host="localhost";
$user="root";
$password="1234";
$database="talentbridge";

$conn=mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if(!$conn)
{
    die("Database Connection Failed : ".mysqli_connect_error());
}

?>