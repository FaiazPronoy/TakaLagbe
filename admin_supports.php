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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST["issue_solved"] as $id => $value) {
        // Update the "Issue Solved" status in the database
        $updateQuery = "UPDATE support_messages SET issue_solved = '$value' WHERE id = '$id'";
        $conn->query($updateQuery);

        // Check if the issue was marked as solved
        // if ($value == 1) {
        //     // Fetch the user's phone number from the support_messages table
        //     $fetchUserPhoneQuery = "SELECT user_phone FROM support_messages WHERE id = '$id'";
        //     $userPhoneResult = $conn->query($fetchUserPhoneQuery);
        //     $userPhoneRow = $userPhoneResult->fetch_assoc();
        //     $userPhone = $userPhoneRow["user_phone"];

        //     // Create a notification in the admin_notifications table
        //     $notificationMessage = "Issue of Account No: $userPhone has been solved by the admin.";
        //     $insertNotificationQuery = "INSERT INTO admin_notifications (message, user_phone) VALUES ('$notificationMessage', '$userPhone')";
        //     $conn->query($insertNotificationQuery);
        // }
        // if ($value == 0) {
        //     // Fetch the user's phone number from the support_messages table
        //     $fetchUserPhoneQuery = "SELECT user_phone FROM support_messages WHERE id = '$id'";
        //     $userPhoneResult = $conn->query($fetchUserPhoneQuery);
        //     $userPhoneRow = $userPhoneResult->fetch_assoc();
        //     $userPhone = $userPhoneRow["user_phone"];

        //     // Create a notification in the admin_notifications table
        //     $notificationMessage = "Issue of Account No: $userPhone has  not been solved by the admin.";
        //     $insertNotificationQuery = "INSERT INTO admin_notifications (message, user_phone) VALUES ('$notificationMessage', '$userPhone')";
        //     $conn->query($insertNotificationQuery);
        // }
    }
}

// Fetch support messages
$query = "SELECT id, user_name, user_email, user_phone, message, issue_solved, timestamp FROM support_messages WHERE issue_solved = '0'";
$result = $conn->query($query);
?>

<?php
include('Front_end/front_admin_supports.php');
?>
