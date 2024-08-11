<?php
session_start(); // Make sure to start the session

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_phone = $_SESSION["user_phone"];

$select_history_sql = "SELECT * FROM history WHERE transaction_from = '$user_phone' OR transaction_to = '$user_phone' ORDER BY transaction_time DESC";
$history_result = $conn->query($select_history_sql);

$conn->close();
?>

<?php
include('Front_end/front_history.php');
?>

