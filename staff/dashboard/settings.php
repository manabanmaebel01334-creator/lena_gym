<?php
// settings.php
// Staff Settings Dashboard
// Placeholders for data; replace with PHP/DB where needed
$staff_name = "Staff Member"; // Added for consistency with dashboard
$staff_role = "Trainer"; // Added for consistency with dashboard
$avatar_initial = strtoupper(substr($staff_name, 0, 1)); // Added for consistency
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
                        success: "#00ff88", // Added missing colors
                        warning: "#ffaa00", // Added missing colors
                        error: "#ff4444" // Added missing colors
                    },
                    fontFamily: {
                        display: ["Lexend", "sans-serif"]
                    },
                    boxShadow: {
                        'neon': '0 0 20px rgba(255, 23, 68, 0.5), 0 0 40px rgba(255, 23, 68, 0.3)', // Consistent neon glow
                        'neon-glow': '0 0 10px rgba(255, 23, 68, 0.8)',
                        'neon-button': '0 0 15px rgba(255, 23, 68, 0.4), inset 0 0 10px rgba(255, 23, 68, 0.2)'
                    }
                },
            },
        }
    </script>

    <style>
        /* ADDED: CSS variables definition for consistency */
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

        /* Page base - UPDATED to use variables */
        body {
            font-family: 'Lexend', sans-serif;
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }

        /* Page layout */
        .page-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar - UPDATED to use variables and mobile/desktop logic */
        .sidebar {
            background: linear-gradient(180deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--accent-rgb)) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(var(--primary-rgb), 0.2);
            color: rgb(var(--text-muted-rgb));
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed;
            inset: 0;
            z-index: 50;
            width: 80vw;
            max-width: 300px;
            height: 100vh;
            padding: 1rem;
            overflow-y: auto;
        }
        .sidebar.open { transform: translateX(0); }

        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
                position: sticky;
                top: 0;
                width: 280px;
                height: 100vh;
                flex-shrink: 0;
            }
            .main-content {
                flex-grow: 1;
                margin-left: 0;
            }
        }

        .sidebar .logo-image { width: 48px; height: auto; } /* Added from dashboard */
        .sidebar h1 { font-size: 1.05rem; color: #ffc9c7; } /* Consistent logo color */


        /* Sidebar Nav Links - UPDATED to use variables and classes */
        .sidebar nav a {
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
        .sidebar nav a:hover,
        .sidebar nav a.active {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.12);
            border-left: 4px solid var(--primary);
        }

        /* Card look - UPDATED to use variables */
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
            padding: 1.5rem; /* kept 1.5rem default padding */
        }
        
        /* header top bar - ADDED from dashboard */
        .top-bar { display:flex; align-items:center; justify-content:space-between; gap:1rem; }

        /* user avatar - ADDED from dashboard */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff4444);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Small utilities - ADDED from dashboard */
        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

        /* Button styles - ADDED from dashboard */
        .btn-add {
            background: linear-gradient(90deg,var(--primary), #ff8a9b);
            color: white;
            padding: 10px 16px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.25);
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(var(--primary-rgb),0.12);
            color: var(--primary);
            padding: .5rem .75rem;
            border-radius: 10px;
        }
        /* Button generic (renamed from .btn for clarity/consistency) */
        .button-generic {
            display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            border-radius:10px; padding:.6rem 1rem; transition:.3s;
        }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-primary:hover { background: #e0143d; }
        .btn-outline {
            border:1px solid rgba(var(--primary-rgb),0.3); color:var(--primary); background:transparent;
        }
        .btn-outline:hover { background:rgba(var(--primary-rgb),0.1); }
        
        /* Form Input - UPDATED for better transparency and subtlety */
        .form-input {
            width:100%; padding:.75rem; border-radius:8px;
            /* Subtly transparent background (2% opacity) */
            background:transparent; 
            /* Subtle border (8% opacity) */
            border:1px solid rgba(var(--text-rgb),0.08);
            color:var(--text); outline:none; transition:.3s;
        }
        .form-input:focus {
            /* Primary color border on focus */
            border-color:var(--primary); 
            /* Slight increase in background opacity on focus */
            background:transparent; 
        }

        /* Toggle - RETAINED for script compatibility, but unused in HTML */
        .toggle {
            width:50px; height:24px; border-radius:20px; background:rgba(var(--text-rgb),0.2);
            position:relative; cursor:pointer; transition:.3s;
        }
        .toggle-ball {
            width:20px; height:20px; border-radius:50%;
            background:var(--text); position:absolute; top:2px; left:2px; transition:.3s;
        }
        .toggle.active { background:var(--primary); }
        .toggle.active .toggle-ball { transform:translateX(26px); background:var(--text); }
        
        /* responsive tweaks */
        @media (max-width: 768px) {
            .main-content { margin-left:0; padding: 1rem; }
        }
    </style>
</head>

<body class="customer"> <div class="page-container">
    <aside id="customer-sidebar" class="sidebar">
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>

        <nav class="space-y-2"> <a href="dashboard.php">
                <span class="material-symbols-outlined text-2xl">dashboard</span>
                <span>Dashboard</span> </a>
            <a href="schedule.php">
                <span class="material-symbols-outlined text-2xl">event</span>
                <span>Schedule</span> </a>
            <a href="clients.php">
                <span class="material-symbols-outlined text-2xl">group</span>
                <span>Clients</span> </a>
            <a href="reports.php">
                <span class="material-symbols-outlined text-2xl">bar_chart</span>
                <span>Reports</span> </a>
            <a href="settings.php" class="active">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span>Settings</span> </a>

            <a href="#" onclick="if(confirm('Are you sure you want to logout?')) { localStorage.clear(); window.location.href = '../../login.php'; }">
                <span class="material-symbols-outlined text-2xl">logout</span>
                <span>Logout</span>
            </a>
        </nav>
        
        <div class="mt-8 pt-4 border-t border-text-muted/30 space-y-2">
            <a href="profile.php" class="flex items-center gap-3">
                <div class="user-avatar w-8 h-8 text-sm" id="staff-user-avatar-sidebar"><?php echo $avatar_initial; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </aside>

    <main id="staff-main" class="main-content p-6 flex-grow space-y-6"> <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-staff" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Settings</h1>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="staff-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="staff-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($staff_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($staff_role); ?></p>
                    </div>
                </div>

                <div class="relative">
                    <button id="notification-toggle-staff" class="p-3 rounded-full bg-accent text-text relative" aria-label="notifications">
                        <span class="material-symbols-outlined">notifications</span>
                        <span id="notification-count" class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center">0</span>
                    </button>
                    <div id="notification-dropdown-staff" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-lg hidden" aria-hidden="true"></div>
                </div>
            </div>
        </header>


        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span> Profile Settings
            </h2>
            <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-text-muted mb-1">Full Name</label>
                    <input type="text" class="form-input bg-transparent" placeholder="Juan Dela Cruz" />
                </div>
                <div>
                    <label class="block text-sm text-text-muted mb-1">Email</label>
                    <input type="email" class="form-input bg-transparent" placeholder="juan@example.com" />
                </div>
                <div>
                    <label class="block text-sm text-text-muted mb-1">New Password</label>
                    <input type="password" class="form-input bg-transparent" placeholder="••••••••" />
                </div>
                <div>
                    <label class="block text-sm text-text-muted mb-1">Confirm Password</label>
                    <input type="password" class="form-input bg-transparent" placeholder="••••••••" />
                </div>
            </form>
            <button class="btn-add mt-6" onclick="alert('Save Changes (implement)')">
                <span class="material-symbols-outlined">save</span>
                <span>Save Changes</span>
            </button>

        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">lock</span> Account Controls
            </h2>
            <div class="flex flex-wrap gap-3">
                <button class="button-generic btn-outline flex items-center gap-2">
                    <span class="material-symbols-outlined">delete</span> Deactivate Account
                </button>
                <button class="button-generic btn-outline flex items-center gap-2" onclick="if(confirm('WARNING: Deleting your account is permanent. Are you absolutely sure you want to delete your account?')) { alert('Account Deletion Requested (implement)'); }">
                    <span class="material-symbols-outlined">delete</span> Delete Account
                </button>
            </div>
        </section>
    </main>
</div>

<script>
    // DOM elements
    const visibleSidebar = document.getElementById('customer-sidebar');
    const desktopToggleStaff = document.getElementById('desktop-toggle-staff');
    const notificationCount = document.getElementById('notification-count');
    const notificationDropdown = document.getElementById('notification-dropdown-staff');
    const notificationToggle = document.getElementById('notification-toggle-staff');
    
    // Theme toggle elements (RETAINED but unused, as the Theme Settings section was removed)
    const themeToggle = document.getElementById("themeToggle");
    const html = document.documentElement;

    // Fade-up animation trigger (same as original)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
        
        // --- Theme logic initialization (RETAINED for consistency with 'dark' class default) ---
        const storedTheme = localStorage.getItem("theme");

        // If the stored theme is "light", remove the 'dark' class and activate the toggle
        if (storedTheme === "light") {
            html.classList.remove("dark");
            // themeToggle.classList.add("active"); // Removed as themeToggle element is gone
        } else {
             // Default to dark if no theme or "dark" is stored, and ensure toggle is off
             html.classList.add("dark");
             // themeToggle.classList.remove("active"); // Removed as themeToggle element is gone
        }
    });

    // Sidebar toggle for mobile (copied from dashboard)
    if (desktopToggleStaff) {
        desktopToggleStaff.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleStaff && desktopToggleStaff.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
    });

    // mark active sidebar link (basic) - UPDATED to handle settings.php
    document.querySelectorAll('.sidebar a[href]').forEach(link => {
        // Remove existing active class
        link.classList.remove('active'); 
        // Logic to activate link based on current file name
        if (window.location.pathname.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
        
        link.addEventListener('click', () => {
            if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
        });
    });

    // Notification dropdown toggle (copied from dashboard)
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    document.addEventListener('click', (e) => {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-staff') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Initialize notification count (placeholder = 0)
    if (notificationCount) notificationCount.textContent = '0';
    
    // Theme toggle logic (REMOVED as the theme toggle element is no longer in the HTML)
</script>
</body>
</html>