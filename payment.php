<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $merchant_id = $_POST['merchant_id'];
    session_start();


    if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
        header("Location: login.php");
        exit;
    }
    
    $user_phone = $_SESSION["user_phone"];
    
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "takalagbe";
    
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
        if($amount > $balance){
            echo "Payment of \$$amount to merchant $merchant_id failed. Insufficient balance. ";
        }else{
            $current_time = date("Y-m-d H:i:s");
            $newBalance = $balance - $amount;
            $query ="UPDATE account SET balance = $newBalance WHERE phone = '$user_phone'";
            $result = $conn->query($query);
            $query ="INSERT INTO history (transaction_from, transaction_to, transaction_type, transaction_time, amount) VALUES ('$user_phone', '$merchant_id', 'PAYMENT', '$current_time', '$amount')";
            $result = $conn->query($query);
            echo "Payment of \$$amount to merchant $merchant_id success. New balance $newBalance ";
        }
    }
    
    $conn->close();
}
else {
    header('Location: payment.php');
    exit();
}

?>
