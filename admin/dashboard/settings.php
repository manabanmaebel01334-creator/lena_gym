<?php
// settings.php — Admin Settings

$admin_name = "Admin User";
$admin_role = "Administrator";
$avatar_initial = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Settings</title>

    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#ff1744",
                        sidebar: "#000000",
                        card: "#1a0000",
                        accent: "#330000",
                        text: "#ffffff",
                        'text-muted': "#ffcccb",
                        success: "#00ff88",
                        warning: "#ffaa00",
                        error: "#ff4444"
                    },
                    fontFamily: {
                        display: ["Lexend", "sans-serif"]
                    },
                    boxShadow: {
                        'neon': '0 0 20px rgba(255, 23, 68, 0.5), 0 0 40px rgba(255, 23, 68, 0.3)',
                        'neon-glow': '0 0 10px rgba(255, 23, 68, 0.8)',
                        'neon-button': '0 0 15px rgba(255, 23, 68, 0.4), inset 0 0 10px rgba(255, 23, 68, 0.2)'
                    }
                },
            },
        }
    </script>

    <style>
        /* CSS Variables */
        :root {
            --primary: #ff1744;
            --primary-rgb: 255, 23, 68;
            --sidebar: #000000;
            --sidebar-rgb: 0, 0, 0;
            --card: #1a0000;
            --card-rgb: 26, 0, 0;
            --accent: #330000;
            --accent-rgb: 51, 0, 0;
            --text: #ffffff;
            --text-rgb: 255, 255, 255;
            --text-muted: #ffcccb;
            --text-muted-rgb: 255, 204, 203;
        }
        /* Global body style */
        body {
            font-family: 'Lexend', sans-serif;
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }
        .page-container { display: flex; min-height: 100vh; }

        /* Sidebar styles */
        .sidebar {
            background: linear-gradient(180deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--accent-rgb)) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(var(--primary-rgb), 0.2);
            color: rgb(var(--text-muted-rgb));
            transform: translateX(0);
            position: sticky;
            top: 0;
            width: 280px;
            height: 100vh;
            flex-shrink: 0;
            padding: 1rem;
            overflow-y: auto;
        }

        .sidebar h1 { font-size: 1.05rem; color: #ffc9c7; }

        .sidebar nav.space-y-2 a {
            transition: all 0.3s ease;
            color: rgb(var(--text-muted-rgb));
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar nav.space-y-2 a:hover,
        .sidebar nav.space-y-2 a.active {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.12);
            border-left: 4px solid var(--primary);
        }

        /* Card style */
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
        }

        /* Utility classes */
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff4444);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;
        }
        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

        /* Button style */
        .btn-primary {
            background: linear-gradient(90deg,var(--primary), #ff8a9b);
            color: white;
            padding: 10px 16px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.25);
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            box-shadow: 0 0 25px rgba(var(--primary-rgb), 0.5);
        }

        /* Input field styling (FINAL FIX: Aggressive Transparency) */
        .settings-input {
            background-color: transparent !important; /* Forces transparency over browser defaults/plugins */
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            border-radius: 8px;
            padding: 10px 12px;
            color: var(--text);
            width: 100%;
        }

        /* Ensure options are dark for readability in the transparent select box */
        .settings-input option {
            background-color: var(--card);
            color: var(--text);
        }

        /* Target the checkbox/radio elements used in Tailwind Forms */
        .form-checkbox {
             background-color: transparent !important; /* Make the checkbox background transparent too */
        }
    </style>
</head>
<body class="admin">

<div class="page-container">
    <aside id="admin-sidebar" class="sidebar">
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1> </div>

        <nav class="space-y-2">
            <a href="dashboard.php">
                <span class="material-symbols-outlined text-2xl">dashboard</span>
                <span>Dashboard</span>
            </a>

            <a href="finance.php">
                <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                <span>Finance</span>
            </a>

            <a href="members.php">
                <span class="material-symbols-outlined text-2xl">group</span>
                <span>Members</span>
            </a>

            <a href="staff.php">
                <span class="material-symbols-outlined text-2xl">badge</span>
                <span>Staff</span>
            </a>

            <a href="analytics.php">
                <span class="material-symbols-outlined text-2xl">bar_chart</span>
                <span>Analytics</span>
            </a>

            <a href="settings.php" class="active">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span>Settings</span>
            </a>

            <a href="#" onclick="if(confirm('Are you sure you want to logout?')) { localStorage.clear(); window.location.href = '../../login.php'; }">
                <span class="material-symbols-outlined text-2xl">logout</span>
                <span>Logout</span>
            </a>
        </nav>

        <div class="mt-8 pt-4 border-t border-text-muted/30 space-y-2">
            <a href="profile.php" class="flex items-center gap-3">
                <div class="user-avatar w-8 h-8 text-sm" id="admin-user-avatar-sidebar"><?php echo $avatar_initial; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </aside>

    <main id="admin-main" class="main-content p-6 flex-grow">
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Settings</h1>
                <div class="tiny">System and User Configuration</div>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="admin-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="admin-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($admin_role); ?></p>
                    </div>
                </div>

                <button class="btn-primary" onclick="alert('Open Quick Add modal (implement)')">
                    <span class="material-symbols-outlined">add_chart</span>
                    <span class="hidden md:inline">Quick Add</span>
                </button>

                <div class="relative">
                    <button id="notification-toggle-admin" class="p-3 rounded-full bg-accent text-text relative" aria-label="notifications">
                        <span class="material-symbols-outlined">notifications</span>
                        <span id="notification-count-admin" class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center">3</span>
                    </button>
                    <div id="notification-dropdown-admin" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-lg hidden" aria-hidden="true">
                        <div class="tiny mb-2">Notifications</div>
                        <ul class="text-sm space-y-2">
                            <li class="placeholder-box">Notifications list placeholder</li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 fade-up">
            <div class="card p-6"> <h2 class="text-lg font-bold mb-4">Profile Settings</h2>
                <form class="space-y-4"> <div>
                        <label class="block text-sm mb-1 tiny">Full Name</label>
                        <input type="text" class="settings-input" value="<?php echo htmlspecialchars($admin_name); ?>"/>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 tiny">Email</label>
                        <input type="email" class="settings-input" value="admin@lenagym.com"/>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 tiny">Change Password</label>
                        <input type="password" class="settings-input" placeholder="••••••••"/>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <div class="card p-6"> <h2 class="text-lg font-bold mb-4">Application Preferences</h2> <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span>Enable Notifications</span>
                        <input type="checkbox" class="form-checkbox w-5 h-5 text-primary bg-accent border-primary/30 rounded" checked/>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Dark Mode</span>
                        <input type="checkbox" class="form-checkbox w-5 h-5 text-primary bg-accent border-primary/30 rounded" checked/>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Timezone</span>
                        <select class="settings-input w-1/2">
                            <option value="Asia/Manila">Asia/Manila (GMT+8)</option>
                            <option value="America/New_York">America/New York (GMT-4)</option>
                        </select>
                    </div>
                    <div class="mt-6 pt-4 border-t border-text-muted/10">
                        <button class="btn-primary">Save Preferences</button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
    // Elements
    const visibleSidebar = document.getElementById('admin-sidebar');
    const desktopToggleAdmin = document.getElementById('desktop-toggle-admin');
    const notificationDropdown = document.getElementById('notification-dropdown-admin');
    const notificationToggle = document.getElementById('notification-toggle-admin');

    // Fade-up animation trigger
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
    });

    // Sidebar toggle and notification toggle logic added for completeness
    if (desktopToggleAdmin) {
        desktopToggleAdmin.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    document.addEventListener('click', (e) => {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-admin') {
            notificationDropdown.classList.add('hidden');
        }
        // Basic mobile sidebar close on outside click (if implementing the mobile view later)
        if (window.innerWidth < 769 && visibleSidebar && visibleSidebar.classList.contains('open') && !visibleSidebar.contains(e.target) && !(desktopToggleAdmin && desktopToggleAdmin.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
    });
</script>
</body>
</html>
