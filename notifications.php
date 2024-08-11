<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Assumes $conn (database connection) is already established

$loggedInUserPhone = $_SESSION["user_phone"];

// Use prepared statement to fetch user-specific notifications
$query = "SELECT * FROM notifications WHERE receiver_phone = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserPhone);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error fetching user notifications: " . $conn->error);
}

// Count unread notifications
$queryUnread = "SELECT COUNT(*) as unread_count FROM notifications WHERE receiver_phone = ? AND read_status = 0";
$stmtUnread = $conn->prepare($queryUnread);
$stmtUnread->bind_param("s", $loggedInUserPhone);
$stmtUnread->execute();
$resultUnread = $stmtUnread->get_result();
$unreadCount = 0;

if ($resultUnread) {
    $rowUnread = $resultUnread->fetch_assoc();
    $unreadCount = $rowUnread["unread_count"];
}

if (isset($_POST["notification_id"])) {
    $notificationId = $_POST["notification_id"];
    
    // Use a prepared statement to update read status only if it belongs to the logged-in user
    $update_read_status_sql = "UPDATE notifications SET read_status = 1 WHERE id = ? AND receiver_phone = ?";
    $stmt = $conn->prepare($update_read_status_sql);
    $stmt->bind_param("is", $notificationId, $loggedInUserPhone);
    $stmt->execute();
    
    if (!$stmt) {
        die("Error updating read status: " . $conn->error);
    }
    
    // Reduce the unread count
    $unreadCount--;
    
    // Consider redirecting or handling post-update logic here
}

?>
