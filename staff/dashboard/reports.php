<?php
// reports.php
// Staff Reports page — design copied from customer dashboard
// Placeholders for data; replace with PHP/DB where needed
$staff_name = "Staff Member";
$staff_role = "Trainer";
$avatar_initial = strtoupper(substr($staff_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Staff Reports</title>

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

        body {
            font-family: 'Lexend', sans-serif;
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }

        .page-container {
            display: flex;
            min-height: 100vh;
        }

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

        .sidebar .logo-image { width: 48px; height: auto; }
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

        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
            padding: 1.5rem;
        }

        .top-bar { display:flex; align-items:center; justify-content:space-between; gap:1rem; }

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

        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

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
        
        .placeholder-box { 
            min-height: 40px; 
            border: 1px dashed rgba(var(--primary-rgb), 0.12);
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            color: rgb(var(--text-muted-rgb));
            background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05));
            text-align: center; 
            padding: 0 1rem;
        }

    </style>
</head>

<body>
<div class="page-container">
    
    <aside id="customer-sidebar" class="sidebar">
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>

        <nav class="space-y-2">
            <a href="dashboard.php">
                <span class="material-symbols-outlined text-2xl">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="schedule.php">
                <span class="material-symbols-outlined text-2xl">event</span>
                <span>Schedule</span>
            </a>
            <a href="clients.php">
                <span class="material-symbols-outlined text-2xl">group</span>
                <span>Clients</span>
            </a>
            <a href="reports.php" class="active">
                <span class="material-symbols-outlined text-2xl">bar_chart</span>
                <span>Reports</span>
            </a>
            <a href="settings.php">
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
                <div class="user-avatar w-8 h-8 text-sm" id="staff-user-avatar-sidebar"><?php echo $avatar_initial; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </aside>
    <main class="main-content flex-grow p-6 space-y-6">
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-staff" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Staff Reports</h1>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="staff-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="staff-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($staff_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($staff_role); ?></p>
                    </div>
                </div>

                <button class="btn-add flex items-center gap-2" onclick="alert('Open New Report modal (implement)')">
                    <span class="material-symbols-outlined">add_chart</span>
                    <span class="hidden md:inline">Generate Report</span>
                </button>

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
                <span class="material-symbols-outlined">paid</span> Financial Overview (Last 30 Days)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="p-4 bg-accent/50 rounded-lg">
                    <div class="tiny">Total Revenue</div>
                    <div class="text-2xl font-bold mt-1 text-success">₱150,000</div>
                </div>
                <div class="p-4 bg-accent/50 rounded-lg">
                    <div class="tiny">Membership Sales</div>
                    <div class="text-2xl font-bold mt-1 text-primary">₱90,000</div>
                </div>
                <div class="p-4 bg-accent/50 rounded-lg">
                    <div class="tiny">Personal Training</div>
                    <div class="text-2xl font-bold mt-1 text-warning">₱60,000</div>
                </div>
            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">trending_up</span> Client & Booking Trends
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-accent/50 rounded-lg">
                    <div class="tiny">New Clients</div>
                    <div class="text-3xl font-bold text-primary">24 <span class="text-sm text-success font-normal ml-2">(+5% MoM)</span></div>
                    <div class="placeholder-box mt-3 text-sm">Chart Placeholder: New Client Sign-ups</div>
                </div>
                <div class="p-4 bg-accent/50 rounded-lg">
                    <div class="tiny">Total Bookings</div>
                    <div class="text-3xl font-bold text-primary">315 <span class="text-sm text-error font-normal ml-2">(-2% MoM)</span></div>
                    <div class="placeholder-box mt-3 text-sm">Chart Placeholder: Session Booking Volume</div>
                </div>
            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span> Trainer Performance (Placeholder Data)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-text-muted">
                        <tr> 
                            <th class="py-3 text-left pl-4">Trainer</th>
                            <th class="text-center">Total Sessions</th>
                            <th class="text-center">Avg. Client Rating</th>
                            <th class="text-center">Cancellation Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-accent/30 transition">
                            <td class="py-3 font-semibold pl-4">John Dela Cruz</td>
                            <td class="text-center">120</td>
                            <td class="text-center text-success">4.9</td>
                            <td class="text-center text-error">5%</td>
                        </tr>
                         <tr class="hover:bg-accent/30 transition">
                            <td class="py-3 font-semibold pl-4">Mary Jane</td>
                            <td class="text-center">95</td>
                            <td class="text-center text-success">4.7</td>
                            <td class="text-center text-error">8%</td>
                        </tr>
                    </tbody>
                </table>
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

    // Fade-up animation trigger
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
    });

    // Sidebar toggle for mobile
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

    // mark active sidebar link (basic)
    document.querySelectorAll('.sidebar a[href]').forEach(link => {
        // 🛑 FIX APPLIED HERE: Exclude links with onclick attribute for logout or those pointing to login.php
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('logout')) {
            return; // Skip this link entirely
        }
        if (link.href.includes('login.php')) {
            return; // Also skip direct login links
        }
        
        // Clear active class from all links
        link.classList.remove('active');
        
        // Add active class if the link href matches the current file
        if (link.href.includes('reports.php')) {
            link.classList.add('active');
        }
        
        link.addEventListener('click', () => {
            if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
        });
    });

    // Notification dropdown toggle
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    document.addEventListener('click', (e) => {
        if (!notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-staff') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Initialize notification count (placeholder = 0)
    if (notificationCount) notificationCount.textContent = '0';
</script>
</body>
</html>