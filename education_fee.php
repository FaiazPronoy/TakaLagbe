<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_phone"]) || !isset($_SESSION["user_password"])) {
    header("Location: login.php");
    exit;
}

// Define database connection parameters
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "TakaLagbe";

// Create a database connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables to hold form data
$institute_id = $institute_name = $student_name = $student_id = $semester = $amount = "";
$error_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize form data
    $institute_id = mysqli_real_escape_string($conn, $_POST["institute_id"]);
    $institute_name = mysqli_real_escape_string($conn, $_POST["institute_name"]);
    $student_id = mysqli_real_escape_string($conn, $_POST["student_id"]);
    $student_name = mysqli_real_escape_string($conn, $_POST["student_name"]);
    $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
    $amount = mysqli_real_escape_string($conn, $_POST["amount"]);
    $user_password = $_SESSION["user_password"];
    $entered_password = $_POST["password"];
    
    // Validate the user inputs (you can add more validation as needed)
    if (empty($institute_id) || empty($institute_name)|| empty($student_id) || empty($student_name) || empty($semester) || empty($amount) || empty($entered_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($entered_password) != 6) {
        $error_message = "Password must be 6 digits in length";
    } elseif ($entered_password != $user_password) {
        $error_message = "Invalid password";
    } elseif ($amount <= 0) {
        $error_message = "Invalid Amount";
    } else {
        $check_organization_sql = "SELECT * FROM institutes WHERE institute_id='$institute_id'";
        $organization_result = $conn->query($check_organization_sql);

        if ($organization_result->num_rows == 0) {
            $error_message = "Institute Not Found";
        } else {
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
                        // Insert transaction record into history table with the institute name
                        $transactionFrom = $user_phone;
                        $transactionTo = $institute_name; // Use the institute name
                        $transactionAmount = $amount;

                        $insertTransactionQuery = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) 
                                                  VALUES ('Education Fee', '$transactionFrom', '$transactionTo', '$transactionAmount')";

                        if ($conn->query($insertTransactionQuery) === TRUE) {
                            // Insert education fee payment record into education_fee table
                            $insertEducationfeeQuery = "INSERT INTO education_fee (institute_id, institute_name, student_id, student_name, semester, amount) 
                                                       VALUES ('$institute_id', '$institute_name', '$student_id', '$student_name', '$semester', '$amount')";

                            if ($conn->query($insertEducationfeeQuery) === TRUE) {
                                // Update the total_received_amount for the specific institute
                                $updateTotalReceivedAmountQuery = "UPDATE institutes SET total_received_amount = total_received_amount + '$amount' WHERE institute_id='$institute_id' ";
                                
                                if ($conn->query($updateTotalReceivedAmountQuery) === TRUE) {
                                    echo "<div class='message text-success'>Education Fee Paid Successfully!</div>";
                                } else {
                                    $error_message = "Error updating total_received_amount: " . $conn->error;
                                }
                            } else {
                                $error_message = "Error inserting education fee payment record: " . $conn->error;
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
                $error_message = "User balance not found.";
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<?php
include('Front_end/front_education_fee.php');
?>
