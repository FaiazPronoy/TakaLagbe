<?php
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "takalagbe";
$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have an admin authentication system in place.
// You should check if the admin is logged in here.

$sql = "SELECT u.*, a.balance FROM user u INNER JOIN account a ON u.phone = a.phone";
$result = $conn->query($sql);
?>

<?php
include('Front_end/front_admin_user.php');
?>

