<?php
session_start();


if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

$user_phone = $_SESSION["user_phone"];

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$query = "SELECT balance FROM account WHERE phone='$user_phone'";
$result = $conn->query($query);

$balance = "N/A"; 
if ($result->num_rows ==1) {
    $row = $result->fetch_assoc();
    $balance = $row["balance"];
}

$conn->close();
?>

<?php
include('Front_end/front_checkbalance.php');
?>
