<?php
session_start();

if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$transaction_success = false;
$transaction_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_phone = $_SESSION["user_phone"];
    $user_password = $_SESSION["user_password"]; 
    $bank = isset($_POST["bank"]) ? $_POST["bank"] : null;
    $amount = isset($_POST["amount"]) ? $_POST["amount"] : null;
    $entered_password = isset($_POST["password"]) ? $_POST["password"] : null;

    if (empty($bank) || empty($amount) || empty($entered_password)) {
        $transaction_error = "Please fill in all fields.";
    } elseif ($entered_password != $user_password) {
        $transaction_error = "Invalid password";
    } elseif ($amount <= 0) {
        $transaction_error = "Invalid amount. Please enter a valid amount to add money.";
    } elseif ($amount > 25000) {
        $transaction_error = "You cannot add money more than 25,000 taka.";
    } else {
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update user's balance
        $update_balance_sql = "UPDATE account SET balance=balance + $amount WHERE phone='$user_phone'";
        if ($conn->query($update_balance_sql)) {
            $transaction_success = true;

            // Insert transaction history
            $transactionType = "Add Money";
            $transactionFrom = $bank;
            $transactionTo = $user_phone;
            $transactionAmount = $amount;

            $insertTransactionQuery = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) 
                                      VALUES ('$transactionType', '$transactionFrom', '$transactionTo', '$transactionAmount')";
            if ($conn->query($insertTransactionQuery) !== TRUE) {
                echo "Error inserting transaction record: " . $conn->error;
            }
        } else {
            $transaction_error = "Error updating balance: " . $conn->error;
        }

        $conn->close();
    }
}
?>

<?php
include('Front_end/front_add_money.php');
?>
