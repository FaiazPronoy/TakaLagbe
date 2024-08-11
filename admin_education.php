<?php
session_start();

// Checking if logged in
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit();
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "takalagbe";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch education fee payment records
$query = "SELECT id, institute_id, institute_name, student_id, student_name, semester, month, amount, timestamp FROM education_fee";
$result = $conn->query($query);
?>

<?php
include('Front_end/front_admin_education.php');
?>
