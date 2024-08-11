<?php
// Improved database connection error handling and secure handling of POST data

// Database connection setup
$conn = new mysqli("localhost", "root", "", "takalagbe");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the script runs only for POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $agent_id = isset($_POST["agent_id"]) ? $_POST["agent_id"] : '';
    $phone_number = isset($_POST["phone_number"]) ? $_POST["phone_number"] : '';
    $cash_out_amount = isset($_POST["cash_out_amount"]) ? $_POST["cash_out_amount"] : 0; // Correctly use the variable meant for cash out

    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT cash_in_amount FROM agent_details WHERE agent_id = ?");
    $stmt->bind_param("s", $agent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_cash = $row["cash_in_amount"];

        // Calculate the new cash in amount after cash out
        $new_cash = $current_cash - $cash_out_amount;  // Subtract cash out amount
        if ($new_cash >= 0) {
            $update_stmt = $conn->prepare("UPDATE agent_details SET cash_in_amount = ?, phone_number = ? WHERE agent_id = ?");
            $update_stmt->bind_param("isi", $new_cash, $phone_number, $agent_id);
            if ($update_stmt->execute()) {
                echo "Cash Out is successful";
            } else {
                echo "Error updating record: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            echo "Insufficient funds to complete the transaction.";
        }
    } else {
        // Agent does not exist, hence no cash out can be processed
        echo "Agent not found. Cash Out cannot be processed.";
    }

    $stmt->close();
    $conn->close();
}

// Include frontend PHP script
include('Front_end/front_cashout.php');
?>
