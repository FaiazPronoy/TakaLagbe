<?php
// reset_password.php
session_start();

$servername = "localhost"; 
$username_db = "root"; 
$password_db = ""; 
$dbname = "TakaLagbe";

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST["phone"];
    $nid = $_POST["nid"];
    $newPassword = $_POST["newPassword"]; // The new password

    $stmt = $conn->prepare("SELECT * FROM user WHERE phone = ? AND nid = ?");
    $stmt->bind_param("ss", $phone, $nid); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User exists, proceed with updating the password
        $updateStmt = $conn->prepare("UPDATE user SET password = ? WHERE phone = ? AND nid = ?");
        $updateStmt->bind_param("sss", $newPassword, $phone, $nid); 
        $updateStmt->execute();

        $updateAccountStmt = $conn->prepare("UPDATE account SET password = ? WHERE phone = ?");
        $updateAccountStmt->bind_param("si", $newPassword, $phone); 
        $updateAccountStmt->execute();


        
        if($updateStmt->affected_rows === 1) {
            echo "Password successfully updated.";

        } else {
            if ($conn->errno) {
                echo "Failed to update password: (" . $conn->errno . ") " . $conn->error;
            } else {
                echo "Failed to update password. It may already be up to date or the new password is the same as the old.";
            }
        }

        $updateStmt->close();
        $updateAccountStmt->close();
    } else {
        echo "No user found with the provided phone number / NID.";
    }
    
    // Close the statement and the connection
    $stmt->close();
    $conn->close();
}
?>
