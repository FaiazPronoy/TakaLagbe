<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_username = $_POST["username"];
    $entered_password = $_POST["password"];

    if ($entered_password == "123123" && $entered_username == "admin") {
        $_SESSION["admin_logged_in"] = true;
        header("Location: Front_end/front_admin_dashboard.php");
        exit();
    } else {
        $login_error = "Invalid password.";
    }
    return;

    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "takalagbe";

    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT id, password FROM admin_users WHERE username='$entered_username'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user_data = $result->fetch_assoc();
        if ($entered_password == $user_data["password"]) {
            $_SESSION["admin_logged_in"] = true;
            header("Location: Front_end/front_admin_dashboard.php");
            exit();
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "Admin user not found.";
    }

    $conn->close();
}
?>

<?php
include('Front_end/front_admin_login.php');
?>
