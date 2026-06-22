<div class="app-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a href="sections.php" class="back-sections-btn" id="backSectionsBtn" style="display:none;"><i class="fas fa-arrow-left"></i> <span>Sections</span></a>
        <span class="app-navbar-brand"><i class="fas fa-graduation-cap"></i><?php echo SITE_NAME; ?></span>
    </div>

    <div class="navbar-right">
        <!-- Notification Bell -->
        <div class="dropdown me-2">
            <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-label="Notifications" id="notifBell">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge" style="display:none">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <small id="notifCount">0 new</small>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>
                </div>
                <div class="notif-footer">
                    <a href="#" id="markAllRead">Mark all as read</a>
                </div>
            </div>
        </div>
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="user-dropdown-btn" data-bs-toggle="dropdown">
                <span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></span>
                <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
.back-sections-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    background: rgba(102,126,234,0.1);
    border: 1px solid rgba(102,126,234,0.2);
    color: #667eea;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
    margin-right: 10px;
}
.back-sections-btn:hover {
    background: rgba(102,126,234,0.18);
    color: #8899ff;
    border-color: rgba(102,126,234,0.35);
}
.nav-icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid #2c2e3e;
    background: transparent;
    color: #a3a6b7;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    position: relative;
    transition: all 0.2s ease;
}
.nav-icon-btn:hover {
    background: rgba(255,255,255,0.04);
    color: #ffffff;
    border-color: #3d4352;
}
.notif-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ef4444;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 6px rgba(239,68,68,0.5);
}
.notif-dropdown {
    width: 360px;
    padding: 0;
    border: 1px solid #2c2e3e;
    border-radius: 10px;
    background: #191c24;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    max-height: 480px;
    overflow: hidden;
}
.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid #2c2e3e;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.9rem;
}
.notif-header small {
    color: #6c7293;
    font-weight: 400;
    font-size: 0.75rem;
}
.notif-list {
    max-height: 340px;
    overflow-y: auto;
}
.notif-list::-webkit-scrollbar { width: 4px; }
.notif-list::-webkit-scrollbar-thumb { background: #2c2e3e; border-radius: 4px; }
.notif-list::-webkit-scrollbar-track { background: transparent; }
.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #1e212a;
    cursor: pointer;
    transition: background 0.15s;
}
.notif-item:hover { background: rgba(255,255,255,0.02); }
.notif-item.unread { border-left: 3px solid #0090e7; }
.notif-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}
.notif-icon.icon-homework { background: rgba(0,144,231,0.12); color: #60a5fa; }
.notif-icon.icon-exam { background: rgba(239,68,68,0.12); color: #fca5a5; }
.notif-icon.icon-payment { background: rgba(16,185,129,0.12); color: #86efac; }
.notif-icon.icon-info { background: rgba(139,92,246,0.12); color: #c4b5fd; }
.notif-body { flex: 1; min-width: 0; }
.notif-body .notif-title { font-size: 0.8rem; font-weight: 600; color: #ffffff; margin-bottom: 2px; }
.notif-body .notif-msg { font-size: 0.73rem; color: #6c7293; line-height: 1.4; }
.notif-body .notif-time { font-size: 0.65rem; color: #4a4d5c; margin-top: 3px; }
.notif-empty {
    text-align: center;
    padding: 30px 20px;
    color: #6c7293;
}
.notif-empty i { font-size: 1.5rem; margin-bottom: 8px; display: block; }
.notif-empty p { font-size: 0.8rem; margin: 0; }
.notif-footer {
    border-top: 1px solid #2c2e3e;
    padding: 10px 16px;
    text-align: center;
}
.notif-footer a {
    color: #0090e7;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
}
.notif-footer a:hover { text-decoration: underline; }
</style>

<script>
(function() {
    'use strict';

    // Show Back to Sections button on module pages
    var btn = document.getElementById('backSectionsBtn');
    if (btn) {
        var exclude = ['sections', 'profile', 'change_password', 'login', 'logout', 'index', 'edit_profile'];
        var path = window.location.pathname.split('/').pop().replace('.php', '');
        if (exclude.indexOf(path) === -1 && path !== '') {
            btn.style.display = 'inline-flex';
        }
    }

    function loadNotifications() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '<?php echo SITE_URL; ?>/ajax/notifications.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    updateUI(data);
                } catch(e) {}
            }
        };
        xhr.send();
    }

    function updateUI(data) {
        var badge = document.getElementById('notifBadge');
        var list = document.getElementById('notifList');
        var count = document.getElementById('notifCount');

        var unread = data.unread || 0;
        var notifications = data.notifications || [];

        if (unread > 0) {
            badge.style.display = 'flex';
            badge.textContent = unread > 9 ? '9+' : unread;
        } else {
            badge.style.display = 'none';
        }

        count.textContent = unread + ' new';

        if (notifications.length === 0) {
            list.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>';
            return;
        }

        var html = '';
        notifications.forEach(function(n) {
            var typeIcon = n.type || 'info';
            var iconClass = 'icon-' + typeIcon;
            var isUnread = n.is_read == 0 ? 'unread' : '';
            html += '<div class="notif-item ' + isUnread + '" data-id="' + n.id + '" onclick="markNotifRead(' + n.id + ')">';
            html += '<div class="notif-icon ' + iconClass + '"><i class="fas fa-' + (typeIcon === 'homework' ? 'book' : typeIcon === 'exam' ? 'file-alt' : typeIcon === 'payment' ? 'credit-card' : 'info-circle') + '"></i></div>';
            html += '<div class="notif-body">';
            html += '<div class="notif-title">' + escapeHtml(n.title) + '</div>';
            if (n.message) html += '<div class="notif-msg">' + escapeHtml(n.message) + '</div>';
            html += '<div class="notif-time">' + escapeHtml(n.created_at) + '</div>';
            html += '</div></div>';
        });
        list.innerHTML = html;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    window.markNotifRead = function(id) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo SITE_URL; ?>/ajax/notifications.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() { loadNotifications(); };
        xhr.send('action=mark_read&id=' + id);
    };

    document.getElementById('markAllRead')?.addEventListener('click', function(e) {
        e.preventDefault();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo SITE_URL; ?>/ajax/notifications.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() { loadNotifications(); };
        xhr.send('action=mark_all_read');
    });

    loadNotifications();
    setInterval(loadNotifications, 30000);
})();
</script>
