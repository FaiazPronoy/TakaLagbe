<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION["user_phone"])) {
    header("Location: login.php");
    exit;
}

$loggedInUserPhone = $_SESSION["user_phone"];

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $first_name
$first_name = "";
$last_name = ""; // Initialize last_name

// Use prepared statement to fetch user's first name
$query = "SELECT firstname FROM user WHERE phone = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserPhone);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching user's first name: " . $conn->error);
}

if ($result->num_rows == 1) {
    $user_data = $result->fetch_assoc();
    $first_name = $user_data["firstname"];
}

// Use prepared statement to fetch user's last name
$query = "SELECT lastname FROM user WHERE phone = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserPhone);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Error fetching user's last name: " . $conn->error);
}

if ($result->num_rows == 1) {
    $user_data = $result->fetch_assoc();
    $last_name = $user_data["lastname"];
}

include('notifications.php');


$conn->close();
?>

<?php
include('Front_end/front_account.php');
?>
