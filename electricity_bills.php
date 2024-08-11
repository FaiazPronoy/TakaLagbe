<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

// Database connection parameters
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

// Create a database connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check the connection for errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to hold form data
$organization_id = $amount = $meter_no = $month = "";
$error_message = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize form data
    $organization_id = mysqli_real_escape_string($conn, $_POST["organization_id"]);
    $amount = mysqli_real_escape_string($conn, $_POST["amount"]);
    $meter_no = mysqli_real_escape_string($conn, $_POST["meter_no"]);
    $month = mysqli_real_escape_string($conn, $_POST["month"]);
    $entered_password = $_POST["password"];
    $user_password = $_SESSION["user_password"];

    // Validate the user inputs
    if (empty($organization_id) || empty($amount) || empty($month) || empty($meter_no) || empty($entered_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($entered_password) != 6) {
        $error_message = "Password must be 6 digits in length";
    } elseif ($entered_password != $user_password) {
        $error_message = "Invalid password";
    } elseif ($amount <= 0) {
        $error_message = "Invalid Amount";
    } else {
        // Check if the provided organization_id exists in organizations with billtype 'electricity_bills'
        $check_organization_sql = "SELECT * FROM organizations WHERE organization_id='$organization_id' AND billtype='electricity_bills'";
        $organization_result = $conn->query($check_organization_sql);

        if ($organization_result->num_rows == 0) {
            $error_message = "Organization not found/Not applicable for electricity bills.";
        } else {
            // Retrieve the organization name based on the provided organization_id
            $organization_name_query = "SELECT organization_name FROM organizations WHERE organization_id='$organization_id'";
            $organization_name_result = $conn->query($organization_name_query);

            if ($organization_name_result->num_rows > 0) {
                $organization_row = $organization_name_result->fetch_assoc();
                $organization_name = $organization_row["organization_name"];

                // Check user's balance
                $user_phone = $_SESSION["user_phone"];
                $user_balance_sql = "SELECT balance FROM account WHERE phone='$user_phone'";
                $user_balance_result = $conn->query($user_balance_sql);

                if ($user_balance_result->num_rows > 0) {
                    $user_row = $user_balance_result->fetch_assoc();
                    $user_balance = $user_row["balance"];

                    // Check if user has enough balance for the payment
                    if ($user_balance >= $amount) {
                        // Update user's balance
                        $new_user_balance = $user_balance - $amount;
                        $update_user_balance_sql = "UPDATE account SET balance='$new_user_balance' WHERE phone='$user_phone'";

                        if ($conn->query($update_user_balance_sql) === TRUE) {
                            // Insert transaction record into history table with the organization name
                            $insertTransactionQuery = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) 
                                                    VALUES ('Electricity Bill', '$user_phone', '$organization_name', '$amount')";

                            if ($conn->query($insertTransactionQuery) === TRUE) {
                                // Insert electricity bill payment record into electricity_bills table
                                $insertelectricityBillQuery = "INSERT INTO electricity_bills (organization_id, organization_name, meter_no, amount, month) 
                                                    VALUES ('$organization_id', '$organization_name', '$meter_no', '$amount', '$month')";

                                if ($conn->query($insertelectricityBillQuery) === TRUE) {
                                    // Update the total_received_amount for the specific organization
                                    $updateTotalReceivedAmountQuery = "UPDATE organizations SET total_received_amount = total_received_amount + '$amount' 
                                                                    WHERE organization_id='$organization_id' AND billtype='electricity_bills'";
                                    
                                    if ($conn->query($updateTotalReceivedAmountQuery) === TRUE) {
                                        echo "<div class='message text-success'>Bill Paid Successfully!</div>";
                                    } else {
                                        $error_message = "Error updating total_received_amount: " . $conn->error;
                                    }
                                } else {
                                    $error_message = "Error inserting electricity bill payment record: " . $conn->error;
                                }
                            } else {
                                $error_message = "Error inserting transaction record: " . $conn->error;
                            }
                        } else {
                            $error_message = "Error updating user's balance: " . $conn->error;
                        }
                    } else {
                        $error_message = "Insufficient balance.";
                    }
                } else {
                    $error_message = "Error retrieving user's balance.";
                }
            } else {
                $error_message = "Organization name not found.";
            }
        }
    }
}

$conn->close();
?>
<?php
include('Front_end/front_electricity_bills.php');
?>
