

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

// Fetch support messages
$query = "SELECT id, user_name, user_email, user_phone, message, issue_solved, timestamp FROM support_messages WHERE issue_solved = '1'";
$result = $conn->query($query);
?>

<?php
include('Front_end/front_admin_issue.php');
?>

<?php
// session_start();

// // Checking if logged in
// if (!isset($_SESSION["admin_logged_in"])) {
//     header("Location: admin_login.php");
//     exit();
// }

// $servername = "localhost:3306";
// $username_db = "teer_db_user";
// $password_db = "teer_db_pass";
// $dbname = "teer_lotto_db";

// $conn = new mysqli($servername, $username_db, $password_db, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// // Fetch admin notifications
// $query = "SELECT message, timestamp FROM admin_notifications ORDER BY timestamp DESC";
// $result = $conn->query($query);
?> 