<!-- Notification Dropdown -->
<li class="nav-item notification-dropdown">
    <a class="nav-link" href="#" onclick="toggleNotifications(event);">
        Notifications
        <span class="notification-badge"><?php echo $unreadCount; ?></span>
    </a>
    <div class="notification-list" style="display: none;">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="notification-item <?php echo $row["read_status"] ? 'read' : 'unread'; ?>">
                <h5>You received <?php echo $row["amount"]; ?> from <?php echo $row["sender_phone"]; ?></h5>
                <?php if (isset($row["message"])) { ?>
                    <p><?php echo $row["message"]; ?></p>
                <?php } ?>
                <div class="notification-time">
                    <?php 
                        $timestamp = strtotime($row["timestamp"]);
                        echo date('M d, Y H:i', $timestamp);
                    ?>
                </div>
                <?php if (!$row["read_status"]) { ?>
                    <form method="post">
                        <input type="hidden" name="notification_id" value="<?php echo $row["id"]; ?>">
                        <button type="submit" class="mark-as-read-button">Mark as Read</button>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</li>

<style>
.notification-dropdown {
    position: relative;
    display: inline-block;
}

.notification-badge {
    background-color: #ff5722;
    color: #fff;
    padding: 4px 8px;
    border-radius: 100%;
    position: absolute;
    top: 0;
    right: 0;
    font-weight: bold;
    font-size: 13px;
}

/* Styles for the notification icon */
.notification-icon {
    font-size: 18px;
    color: #3498db;
    margin-right: 4px;
}

/* Styles for the notification list container */
.notification-list {
    display: none;
    position: absolute;
    background-color: #fff;
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
    width: 200px;
    z-index: 1;
    top: calc(100% + 10px);
    right: 0;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out, visibility 0s linear 0.3s; /* Added visibility transition */
    pointer-events: none; /* Disable pointer events to prevent interaction while hidden */
}

/* Styles for each notification item */
.notification-list .notification-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    transition: background-color 0.2s ease;
}

/* Remove the border from the last notification item */
.notification-list .notification-item:last-child {
    border-bottom: none;
}

/* Show the notification list with a delay when hovering over the notification dropdown */
.notification-dropdown:hover .notification-list {
    display: block;
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0s; /* Remove the transition delay for smoother appearance */
    cursor: pointer;
    pointer-events: auto; /* Enable pointer events when visible */
}

/* Add a hover effect to the notification items */
.notification-list .notification-item:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

    // Function to toggle the visibility of the notification list
    function toggleNotifications(event) {
        event.preventDefault(); // Prevent default link action
        var notificationList = document.querySelector('.notification-list');
        var isHidden = window.getComputedStyle(notificationList).display === 'none';
        notificationList.style.display = isHidden ? 'block' : 'none'; // Toggle visibility
    }

    // Clicking outside the notifications should close the notification list
    document.addEventListener('click', function(event) {
        var notificationList = document.querySelector('.notification-list');
        var notificationDropdown = document.querySelector('.notification-dropdown');
        // If the clicked target is not within the notificationDropdown, hide the list
        if (!notificationDropdown.contains(event.target)) {
            notificationList.style.display = 'none';
        }
    });


</style>

<script>
    // Function to toggle the visibility of the notification list
    function toggleNotifications(event) {
        event.preventDefault(); // Prevent default link action
        var notificationList = document.querySelector('.notification-list');
        var isHidden = window.getComputedStyle(notificationList).display === 'none';
        notificationList.style.display = isHidden ? 'block' : 'none'; // Toggle visibility
    }

    // Clicking outside the notifications should close the notification list
    document.addEventListener('click', function(event) {
        var notificationList = document.querySelector('.notification-list');
        var notificationDropdown = document.querySelector('.notification-dropdown');
        // If the clicked target is not within the notificationDropdown, hide the list
        if (!notificationDropdown.contains(event.target)) {
            notificationList.style.display = 'none';
        }
    });
</script>
