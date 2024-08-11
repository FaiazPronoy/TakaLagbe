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

// Fetch merchant bills
$query = "SELECT * FROM merchant_bill";
$result = $conn->query($query);
?>

<?php
include('Front_end/front_admin_merchant.php');
?>


