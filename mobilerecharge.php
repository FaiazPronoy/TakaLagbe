<?php
session_start();

if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

$user_phone = $_SESSION["user_phone"];
$user_password = $_SESSION["user_password"]; 

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM account WHERE phone='$user_phone'";
$result = $conn->query($query);

$balance = "N/A"; 
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $balance = $row["balance"];
}

$recharge_success = $recharge_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rechargeAmount = $_POST["rechargeAmount"];
    $rechargePassword = $_POST["rechargePassword"]; // Get the entered password
    
    // Validate the recharge amount
    if ($rechargeAmount <= 0) {
        $recharge_error = "Recharge amount must be greater than 0 taka.";
    } elseif ($rechargeAmount > 10000) {
        $recharge_error = "Recharge amount cannot exceed 10,000 taka.";
    } else {
        // Validate the password
        if ($rechargePassword === $user_password) {
            if ($balance >= $rechargeAmount) {
                $newBalance = $balance - $rechargeAmount;
                
                $updateQuery = "UPDATE account SET balance = $newBalance WHERE phone = '$user_phone'";
                if ($conn->query($updateQuery) === TRUE) {
                    $balance = $newBalance;
                    
                    $transactionType = "Mobile Recharge";
                    $transactionFrom = $user_phone;
                    $transactionTo = $_POST["rechargePhoneNumber"];
                    $transactionAmount = $rechargeAmount;

                    $insertTransactionQuery = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) 
                                              VALUES ('$transactionType', '$transactionFrom', '$transactionTo', '$transactionAmount')";

                    if ($conn->query($insertTransactionQuery) !== TRUE) {
                        $recharge_error = "Error inserting transaction record: " . $conn->error;
                    } else {
                        $recharge_success = "Mobile recharge successful!";
                    }

                } else {
                    $recharge_error = "Error updating balance: " . $conn->error;
                }
            } else {
                $recharge_error = "Insufficient balance for recharge.";
            }
        } else {
            $recharge_error = "Invalid Password";
        }
    }
}

$conn->close();
?>

<?php
include('Front_end/front_mobilerecharge.php');
?>

