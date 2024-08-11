<?php
session_start();

if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

$loggedInUserPhone = $_SESSION["user_phone"];

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$transaction_success = false;
$transaction_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_phone = $_POST["sender_phone"];
    $sender_password = $_POST["sender_password"];
    $receiver_phone = $_POST["receiver_phone"];
    $amount = $_POST["amount"];

    if ($amount <= 0) {
        $transaction_error = "Invalid amount. Please enter a valid amount to send.";
    } elseif ($amount > 25000) {
        $transaction_error = "You cannot send more than 25,000 taka.";
    } elseif ($sender_phone == $receiver_phone) {
        $transaction_error = "You cannot send money to your own account.";
    } else {
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $validate_sender_sql = "SELECT * FROM account WHERE phone='$sender_phone' AND password='$sender_password'";
        $sender_result = $conn->query($validate_sender_sql);

            if ($sender_result->num_rows == 1) {
                $sender_row = $sender_result->fetch_assoc();
                $sender_balance = $sender_row["balance"];

                if ($sender_balance >= $amount) {
                    // Update sender's balance
                    $new_sender_balance = $sender_balance - $amount;
                    $update_sender_balance_sql = "UPDATE account SET balance='$new_sender_balance' WHERE phone='$sender_phone'";
                    $conn->query($update_sender_balance_sql);

                    $validate_receiver_sql = "SELECT * FROM account WHERE phone='$receiver_phone'";
                    $receiver_result = $conn->query($validate_receiver_sql);

                    if ($receiver_result->num_rows == 1) {
                        $receiver_row = $receiver_result->fetch_assoc();
                        $receiver_balance = $receiver_row["balance"];
                        $new_receiver_balance = $receiver_balance + $amount;
                        $update_receiver_balance_sql = "UPDATE account SET balance='$new_receiver_balance' WHERE phone='$receiver_phone'";
                        $conn->query($update_receiver_balance_sql);

                        // Insert transaction into history table
                        $transaction_type = "Send Money";
                        $transaction_from = $loggedInUserPhone;
                        $transaction_to = $receiver_phone;
                        $insert_history_sql = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) VALUES ('$transaction_type', '$transaction_from', '$transaction_to', $amount)";
                        $conn->query($insert_history_sql);

                        // Insert notification into the database
                        $notification_sender = $loggedInUserPhone;
                        $notification_receiver = $receiver_phone;
                        $notification_amount = $amount;
                        $insert_notification_sql = "INSERT INTO notifications (sender_phone, receiver_phone, amount) VALUES ('$notification_sender', '$notification_receiver', '$notification_amount')";
                        $conn->query($insert_notification_sql);

                        $transaction_success = true;
                    } else {
                        $transaction_error = "Receiver's phone number not found.";
                    }
                    
                } else {
                    $transaction_error = "Insufficient balance.";
                }
            } else {
                $transaction_error = "Invalid password.";
            }

        $conn->close();
    }
}
?>

<?php
include('Front_end/front_sendmoney.php');
?>

