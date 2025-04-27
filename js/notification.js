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
            
            // Visual feedback - mark all items as read immediately
            const allNotifications = notificationList.querySelectorAll('.notification-item');
            allNotifications.forEach(item => {
                item.classList.add('opacity-75');
                item.classList.remove('cursor-pointer');
                item.removeAttribute('onclick');
            });
            unreadBadge.textContent = '0';
            unreadBadge.classList.add('hidden');
            
            // Show success message
            showToast('All notifications marked as read', 'success');
            
            // Refresh notifications
            fetchNotifications();
        } catch (error) {
            console.error('Error marking all as read:', error);
            showToast('Failed to mark all as read', 'error');
        }
    });

    // Show toast message
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white transition-opacity duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);

        // Fade out and remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Fetch notifications
    async function fetchNotifications() {
        try {
            const response = await fetch('api/notification.php?action=get');
            const data = await response.json();
            
            if (!data.success) throw new Error(data.error || 'Failed to fetch notifications');
            renderNotifications(data.notifications, data.unread_count);
        } catch (error) {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = '<p class="p-2 text-red-500">Failed to load notifications</p>';
            showToast('Failed to load notifications', 'error');
        }
    }

    // Render notifications in the dropdown
    function renderNotifications(notifications, unreadCount) {
        notificationList.innerHTML = '';
        unreadBadge.textContent = unreadCount || '';
        unreadBadge.classList.toggle('hidden', unreadCount === 0);

        if (!Array.isArray(notifications) || notifications.length === 0) {
            notificationList.innerHTML = '<p class="p-2 text-gray-500">No notifications</p>';
            return;
        }

        notifications.forEach(notification => {
            const item = document.createElement('div');
            item.className = `notification-item p-3 border-b border-gray-100 hover:bg-gray-50 flex gap-3 ${notification.is_read ? 'opacity-75' : ''}`;
            
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
                <div class="flex-1">
                    <p class="text-sm text-gray-800 font-medium">${notification.title}</p>
                    <p class="text-xs text-gray-500 mt-1">${notification.message}</p>
                    <p class="text-xs text-gray-400 mt-2">${relativeTime}</p>
                </div>
                ${!notification.is_read ? '<span class="w-2 h-2 bg-blue-500 rounded-full mt-2"></span>' : ''}
            `;

            // Mark as read on click
            if (!notification.is_read) {
                item.classList.add('cursor-pointer');
                item.addEventListener('click', async () => {
                    try {
                        const response = await fetch(`api/notification.php?action=mark_read&id=${notification.id}`, { 
                            method: 'POST' 
                        });
                        const data = await response.json();
                        if (!data.success) throw new Error(data.error || 'Failed to mark as read');
                        
                        // Visual feedback
                        item.classList.add('opacity-75');
                        item.classList.remove('cursor-pointer');
                        const unreadDot = item.querySelector('.bg-blue-500');
                        if (unreadDot) unreadDot.remove();
                        
                        // Update unread count
                        const currentCount = parseInt(unreadBadge.textContent || '0');
                        const newCount = Math.max(0, currentCount - 1);
                        unreadBadge.textContent = newCount || '';
                        unreadBadge.classList.toggle('hidden', newCount === 0);
                        
                        showToast('Notification marked as read', 'success');
                    } catch (error) {
                        console.error('Error marking as read:', error);
                        showToast('Failed to mark as read', 'error');
                    }
                });
            }
            notificationList.appendChild(item);
        });
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