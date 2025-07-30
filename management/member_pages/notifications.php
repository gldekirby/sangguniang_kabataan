<?php
include '../config.php';

// Verify user is logged in and active
if (!isset($_SESSION['member_id'])) {
    header("Location: login_member.php");
    exit();
}

// Count unread notifications
$member_id = $_SESSION['member_id'];
$unreadCount = 0;
$unreadQuery = "SELECT COUNT(*) as count FROM notifications WHERE member_id = ? AND is_read = 0";
$unreadStmt = $conn->prepare($unreadQuery);
$unreadStmt->bind_param("i", $member_id);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result();
if ($unreadResult->num_rows > 0) {
    $unreadCount = $unreadResult->fetch_assoc()['count'];
}
$unreadStmt->close();

// Fetch notifications
function fetchNotifications($conn, $member_id) {
    $query = "SELECT * FROM notifications WHERE member_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    return $stmt->get_result();
}

$notifications = fetchNotifications($conn, $member_id);
$conn->close();
?>

<!-- Notifications Display -->
<div class="notification-container relative">
    <button onclick="toggleNotificationPanel(event)" class="notification-button text-gray-700 focus:outline-none">
        <i class="fas fa-bell text-xl"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="notification-badge"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
    </button>
    <div id="notificationPanel" class="p-2">
        <div class="flex justify-between items-center p-2 border-b">
            <h3 class="font-bold text-lg">Notifications</h3>
            <span class="close-notification" onclick="closeNotificationPanel()">&times;</span>
        </div>
        <div id="notificationList" class="divide-y">
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="fas fa-bell mr-2 text-yellow-500"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="text-sm"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="notification-time"><?php echo date('F j, Y, g:i A', strtotime($notification['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="notification-item text-center text-gray-500 py-4">No notifications</div>
            <?php endif; ?>
        </div>
    </div>
</div>