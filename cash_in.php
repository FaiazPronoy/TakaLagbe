<?php
// Improved security with prepared statements and corrected logic for cash calculations

// Initialize database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "takalagbe";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $agent_id = $_POST["agent_id"];
    $phone_number = $_POST["phone_number"];
    $cash_in_amount = $_POST["cash_in_amount"];
    $cash_out_amount = $_POST["cash_out_amount"]; // Corrected to get cash_out_amount from $_POST

    // Check if agent exists
    $stmt = $conn->prepare("SELECT cash_in_amount FROM agent_details WHERE agent_id = ?");
    $stmt->bind_param("s", $agent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_cash = $row["cash_in_amount"];

        // Calculate new cash in amount (assuming adding cash_in_amount to current)
        $new_cash = $current_cash + $cash_in_amount;

        // Update agent details
        $update_stmt = $conn->prepare("UPDATE agent_details SET cash_in_amount = ?, phone_number = ? WHERE agent_id = ?");
        $update_stmt->bind_param("isi", $new_cash, $phone_number, $agent_id);
        if ($update_stmt->execute()) {
            echo "Cash In is successful";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        // Insert new agent
        $insert_stmt = $conn->prepare("INSERT INTO agent_details (agent_id, phone_number, cash_in_amount) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("ssi", $agent_id, $phone_number, $cash_in_amount);
        $insert_history_sql = "INSERT INTO history (transaction_type, transaction_from, transaction_to, amount) VALUES ('Cash_In', '$agent_id', '$phone_number', $cash_in_amount)";
        $conn->query($insert_history_sql);

        if ($insert_stmt->execute()) {
            echo "Cash In is successful";
        } else {
            echo "Error inserting record: " . $conn->error;
        }
    }

    // Close connections
    $stmt->close();
    $insert_stmt->close();
    $update_stmt->close();
    $conn->close();
}

// Include front end page
include('Front_end/front_cashin.php');
?>
