<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST["Phone"];
    $password = $_POST["Password"];
    $servername = "localhost"; 
    $username_db = "root"; 
    $password_db = ""; 
    $dbname = "TakaLagbe"; 
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $query = "SELECT * FROM user WHERE phone='$phone' AND password='$password'";
    $result = $conn->query($query);
    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();
        $_SESSION["user_phone"] = $user_data["phone"];
        $_SESSION["user_password"] = $user_data["password"];
        header("Location: account.php");
        exit();
    } else {
        echo "Invalid login information. Try Again";
    }
    $conn->close();
}
?>

<?php
include('Front_end/front_login.php');
?>

