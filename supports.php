<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $message = $_POST["message"];
    $user_email=$_POST["user_email"];
    $user_phone=$_POST["user_phone"];
    $servername = "localhost"; 
    $username_db = "root"; 
    $password_db = ""; 
    $dbname = "TakaLagbe"; 

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "INSERT INTO support_messages (user_name, user_email, message,user_phone) VALUES ('$user_name', '$user_email', '$message','$user_phone')";

    if ($conn->query($query) === TRUE) {
        $message_status = "Message submitted successfully.";
    } else {
        $message_status = "Error: " . $conn->error;
    }

    $conn->close();
}
?>

<?php
include('Front_end/front_supports.php');
?>


