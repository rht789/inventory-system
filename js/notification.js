document.addEventListener('DOMContentLoaded', () => {
    const notificationBell = document.getElementById('notification-bell');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationList = document.getElementById('notification-list');
    const unreadBadge = document.getElementById('unread-badge');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const viewAllLink = document.getElementById('view-all');

    // Toggle dropdown visibility
    notificationBell.addEventListener('click', () => {
        notificationDropdown.classList.toggle('hidden');
        if (!notificationDropdown.classList.contains('hidden')) {
            fetchNotifications();
        }
    });

    // Close dropdown if clicking outside
    document.addEventListener('click', (e) => {
        if (!notificationBell.contains(e.target) && !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Mark all as read
    markAllReadBtn.addEventListener('click', async () => {
        try {
            const response = await fetch('api/notification.php?action=mark_all_read', { method: 'POST' });
            const data = await response.json();
            if (!data.success) throw new Error(data.error || 'Failed to mark all as read');
            fetchNotifications();
        } catch (error) {
            console.error('Error marking all as read:', error);
            alert('Failed to mark all as read: ' + error.message);
        }
    });

    // Fetch notifications
    async function fetchNotifications() {
        try {
            console.log('Fetching notifications...');
            const response = await fetch('api/notification.php?action=get');
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Notifications data:', data);
            
            if (!data.success) throw new Error(data.error || 'Failed to fetch notifications');
            renderNotifications(data.notifications, data.unread_count);
        } catch (error) {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = '<p class="p-2 text-red-500">Failed to load notifications: ' + error.message + '</p>';
        }
    }

    // Render notifications in the dropdown
    function renderNotifications(notifications, unreadCount) {
        console.log('Rendering notifications:', notifications);
        console.log('Unread count:', unreadCount);
        
        notificationList.innerHTML = '';
        unreadBadge.textContent = unreadCount || '';
        unreadBadge.classList.toggle('hidden', unreadCount === 0);

        if (!Array.isArray(notifications) || notifications.length === 0) {
            notificationList.innerHTML = '<p class="p-2 text-gray-500">No notifications</p>';
            return;
        }

        notifications.forEach(notification => {
            const item = document.createElement('div');
            item.className = `p-3 border-b border-gray-100 hover:bg-gray-50 flex gap-3 ${notification.is_read ? 'opacity-75' : ''}`;
            
            // Icon and color based on notification type
            const iconClass = {
                'low_stock': 'bg-amber-100 text-amber-500',
                'sale': 'bg-green-100 text-green-500',
                'stock': 'bg-blue-100 text-blue-500',
                'audit': 'bg-purple-100 text-purple-500',
                'other': 'bg-gray-100 text-gray-500'
            }[notification.type] || 'bg-gray-100 text-gray-500';
            const iconSymbol = {
                'low_stock': '<i class="fas fa-exclamation-triangle"></i>',
                'sale': '<i class="fas fa-check-circle"></i>',
                'stock': '<i class="fas fa-truck"></i>',
                'audit': '<i class="fas fa-clipboard"></i>',
                'other': '<i class="fas fa-info-circle"></i>'
            }[notification.type] || '<i class="fas fa-info-circle"></i>';

            const relativeTime = getRelativeTime(new Date(notification.created_at));
            item.innerHTML = `
                <div class="${iconClass} rounded-md w-10 h-10 flex items-center justify-center flex-shrink-0">
                    ${iconSymbol}
                </div>
                <div>
                    <p class="text-sm text-gray-800 font-medium">${notification.title}</p>
                    <p class="text-xs text-gray-500 mt-1">${notification.message}</p>
                    <p class="text-xs text-gray-400 mt-2">${relativeTime}</p>
                </div>
            `;

            // Mark as read on click
            if (!notification.is_read) {
                item.classList.add('cursor-pointer');
                item.addEventListener('click', () => markAsRead(notification.id));
            }
            notificationList.appendChild(item);
        });
    }

    // Mark a single notification as read
    async function markAsRead(notificationId) {
        try {
            const response = await fetch(`api/notification.php?action=mark_read&id=${notificationId}`, { method: 'POST' });
            const data = await response.json();
            if (!data.success) throw new Error(data.error || 'Failed to mark as read');
            fetchNotifications();
        } catch (error) {
            console.error('Error marking as read:', error);
            alert('Failed to mark as read: ' + error.message);
        }
    }

    // Calculate relative time
    function getRelativeTime(date) {
        const now = new Date();
        const diffMs = now - date;
        const diffSeconds = Math.floor(diffMs / 1000);
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffSeconds < 60) return `${diffSeconds} seconds ago`;
        if (diffMinutes < 60) return `${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
        if (diffDays < 2) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;
        return date.toLocaleDateString();
    }

    // Initial fetch
    fetchNotifications();
});