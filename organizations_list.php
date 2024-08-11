<?php
$servername = "localhost"; 
$username_db = "root"; 
$password_db = ""; 
$dbname = "TakaLagbe"; 
$connection = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Query the organizations table
$query = "SELECT organization_id, organization_name FROM organizations";
$result = $connection->query($query);

// Check for and display results
if ($result->num_rows > 0) {
    // Start the table
    echo '<table class="organizations_list">';
    // Header row
    echo '<tr><th>Organization ID</th><th>Organization Name</th></tr>';
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["organization_id"]. "</td><td>" . $row["organization_name"]. "</td></tr>";
    }
    echo '</table>'; // Close the table
} else {
    echo "0 results";
}

// Close the connection
$connection->close();
?>

<?php
include('Front_end/front_organizations_list.php');
?>

