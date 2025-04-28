<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db.php'; // Updated path to database connection

// Ensure no output before JSON response
ob_clean();

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['user_role'] ?? null;

    if (!$user_id || !$role) {
        throw new Exception('Unauthorized');
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get':
            try {
                // Fetch notifications where role matches 'all' or the user's role
                $stmt = $pdo->prepare("
                    SELECT n.id, n.type, n.title, n.message, n.created_at,
                           nr.is_read
                    FROM notifications n
                    LEFT JOIN notification_reads nr
                        ON n.id = nr.notification_id AND nr.user_id = ?
                    WHERE n.role = 'all' OR n.role = ?
                    ORDER BY n.created_at DESC
                    LIMIT 10
                ");
                $stmt->execute([$user_id, $role]);
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculate unread count
                $unread_count = 0;
                foreach ($notifications as &$notification) {
                    $notification['is_read'] = (bool) ($notification['is_read'] ?? false);
                    if (!$notification['is_read']) {
                        $unread_count++;
                    }
                }

                echo json_encode([
                    'success' => true,
                    'notifications' => $notifications,
                    'unread_count' => $unread_count
                ]);
            } catch (Exception $e) {
                throw new Exception('Failed to fetch notifications: ' . $e->getMessage());
            }
            break;

        case 'mark_read':
            $notification_id = $_GET['id'] ?? null;
            if (!$notification_id) {
                throw new Exception('Notification ID required');
            }

            try {
                // Check if already marked as read
                $stmt = $pdo->prepare("
                    SELECT id FROM notification_reads
                    WHERE notification_id = ? AND user_id = ?
                ");
                $stmt->execute([$notification_id, $user_id]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    // Update existing record
                    $stmt = $pdo->prepare("
                        UPDATE notification_reads
                        SET is_read = TRUE, read_at = NOW()
                        WHERE notification_id = ? AND user_id = ?
                    ");
                    $stmt->execute([$notification_id, $user_id]);
                } else {
                    // Insert new read record
                    $stmt = $pdo->prepare("
                        INSERT INTO notification_reads (notification_id, user_id, is_read, read_at)
                        VALUES (?, ?, TRUE, NOW())
                    ");
                    $stmt->execute([$notification_id, $user_id]);
                }

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                throw new Exception('Failed to mark as read: ' . $e->getMessage());
            }
            break;

        case 'mark_all_read':
            try {
                // Mark all notifications as read for the user
                $stmt = $pdo->prepare("
                    INSERT INTO notification_reads (notification_id, user_id, is_read, read_at)
                    SELECT n.id, ?, TRUE, NOW()
                    FROM notifications n
                    WHERE n.role = 'all' OR n.role = ?
                    ON DUPLICATE KEY UPDATE is_read = TRUE, read_at = NOW()
                ");
                $stmt->execute([$user_id, $role]);

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                throw new Exception('Failed to mark all as read: ' . $e->getMessage());
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Unauthorized' ? 401 : 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>