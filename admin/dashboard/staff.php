<?php
// staff.php
// Admin Staff Management Dashboard — same system design as admin-dashboard.php

$admin_name = "Admin User";
$admin_role = "Administrator";
$avatar_initial = strtoupper(substr($admin_name, 0, 1));

// --- ADDED: Notification Data (Copied from members.php/finance.php) ---
$admin_notification_count = 3;
$notification_alerts = [
    'Payment overdue — Gym Equipment Co.',
    'Shift conflict detected for 2025-10-21',
    'Member retention at risk: 2',
];
// -----------------------------------------------------------------------

// Placeholder Data
$staff_roster = [
    ['name'=>'Coach Lena','role'=>'Head Trainer'],
    ['name'=>'John Santos','role'=>'Personal Trainer'],
    ['name'=>'Mary Cruz','role'=>'Yoga Instructor'],
    ['name'=>'Alex Reyes','role'=>'Receptionist'],
    ['name'=>'Ella Lim','role'=>'Maintenance']
];

// Note: Dashboard.php staff roster uses 'shift' like '08:00-16:00'
$shift_schedules = [
    ['name'=>'Coach Lena','shift'=>'08:00-16:00','conflict'=>false],
    ['name'=>'John Santos','shift'=>'13:00-21:00','conflict'=>true],
    ['name'=>'Mary Cruz','shift'=>'06:00-14:00','conflict'=>false],
    ['name'=>'Alex Reyes','shift'=>'09:00-17:00','conflict'=>false],
    ['name'=>'Ella Lim','shift'=>'16:00-00:00','conflict'=>true]
];

$performance = [
    'Coach Lena'=>92,
    'John Santos'=>85,
    'Mary Cruz'=>90,
    'Alex Reyes'=>88,
    'Ella Lim'=>80
];
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Staff Management</title>

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
        /* CSS Variables: Added for consistency and dynamic theming */
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

        /* Body and container styles (using variables) */
        body {
            font-family: 'Lexend', sans-serif;
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }
        body.overflow-hidden {
            overflow: hidden;
        }
        .desktop-wrapper { display: flex; min-height: 100vh; }
        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff4444);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;
        }
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

        /* START: COPIED SIDEBAR STYLES FROM finance.php */
        .sidebar {
            background: linear-gradient(180deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--accent-rgb)) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(var(--primary-rgb), 0.2);
            color: rgb(var(--text-muted-rgb));
            transform: translateX(-100%); /* Mobile off-screen */
            transition: transform 0.3s ease;
            position: fixed;
            inset: 0;
            z-index: 50;
            width: 80vw;
            max-width: 300px;
            height: 100vh;
            padding: 1rem;
            overflow-y: hidden; /* REMOVED SCROLLER */
        }
        .sidebar.open {
            transform: translateX(0);
        }

        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
                position: sticky; top: 0;
                width: 280px; height: 100vh;
                flex-shrink: 0;
            }
            .main-content {
                flex-grow: 1;
                margin-left: 0;
            }
        }

        .sidebar a {
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
        .sidebar a:hover,
        .sidebar a.active {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.2);
            border-left: 4px solid var(--primary);
        }
        /* END: COPIED SIDEBAR STYLES FROM finance.php */


        /* Card styles (using variables) */
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
            padding: 1.5rem; /* Ensure consistent padding */
        }
        /* ADDED: Placeholder box style from members.php/finance.php for notifications */
        .placeholder-box { min-height: 60px; border: 1px dashed rgba(var(--primary-rgb), 0.12); border-radius:12px; display:flex; align-items:center; justify-content:center; color: rgb(var(--text-muted-rgb)); background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05)); }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="desktop-wrapper"> <aside id="admin-sidebar" class="sidebar"> 
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold text-[#ffc9c7]">Lena Gym Fitness</h1>
        </div>
        <nav class="space-y-2">
            <a href="dashboard.php"><span class="material-symbols-outlined text-2xl">dashboard</span><span>Dashboard</span></a>
            <a href="finance.php"><span class="material-symbols-outlined text-2xl">account_balance_wallet</span><span>Finance</span></a>
            <a href="members.php"><span class="material-symbols-outlined text-2xl">group</span><span>Members</span></a>
            <a href="staff.php" class="active"><span class="material-symbols-outlined text-2xl">badge</span><span>Staff</span></a> <a href="analytics.php"><span class="material-symbols-outlined text-2xl">bar_chart</span><span>Analytics</span></a>
            <a href="settings.php"><span class="material-symbols-outlined text-2xl">settings</span><span>Settings</span></a>
            <a href="../../logout.php" class="logout-link" onclick="if(confirm('Are you sure you want to logout?')) { localStorage.clear(); window.location.href = '../../login.php'; }">
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
    <main class="main-content flex-grow p-6">
        <header class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Staff Management</h1>
            </div>

            <div class="top-bar-user flex items-center gap-4 relative"> <div class="user-avatar"><?php echo $avatar_initial; ?></div>
                <div id="admin-user-info" class="hidden sm:block">
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                    <p class="text-xs tiny"><?php echo htmlspecialchars($admin_role); ?></p>
                </div>

                <div class="relative">
                    <button id="notification-toggle-admin" class="p-3 rounded-full bg-accent text-text relative"> 
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center" id="notification-count-admin">
                            <?php echo $admin_notification_count; ?>
                        </span> 
                    </button>
                    <div id="notification-dropdown-admin" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-lg hidden w-72 z-40" aria-hidden="true">
                        <div class="tiny mb-2">Notifications</div>
                        <ul class="text-sm space-y-2">
                            <?php foreach($notification_alerts as $alert): ?>
                            <li class="placeholder-box h-auto p-2 text-left text-xs"><?php echo $alert; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    </div>

            </div>
        </header>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 fade-up">
            <div class="card lg:col-span-1">
                <h2 class="text-lg font-bold mb-3">Staff Roster</h2>
                <ul class="text-sm space-y-2">
                    <?php foreach($staff_roster as $staff): ?>
                    <li class="p-3 card flex items-center justify-between !border-none !shadow-none !bg-accent/50">
                        <div>
                            <div class="font-bold"><?php echo htmlspecialchars($staff['name']); ?></div>
                            <div class="tiny"><?php echo htmlspecialchars($staff['role']); ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card lg:col-span-1">
                <h2 class="text-lg font-bold mb-3">Shift Schedules</h2>
                <ul class="text-sm space-y-2">
                    <?php foreach($shift_schedules as $shift): ?>
                    <li class="p-3 card flex justify-between items-center !border-none !shadow-none !bg-accent/50">
                        <div>
                            <span class="font-medium"><?php echo htmlspecialchars($shift['name']); ?></span><br>
                            <span class="text-xs tiny"><?php echo htmlspecialchars($shift['shift']); ?></span>
                        </div>
                        <?php if($shift['conflict']): ?>
                          <span class="text-xs bg-error/30 border border-error text-error px-2 py-1 rounded-full">Conflict</span>
                        <?php else: ?>
                          <span class="text-xs bg-success/20 border border-success text-success px-2 py-1 rounded-full">OK</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card lg:col-span-1">
                <h2 class="text-lg font-bold mb-3">Performance Metrics</h2>
                <canvas id="performanceChart" height="160"></canvas>
            </div>
        </section>
    </main>
</div>

<script>
    // Consistent DOM element references (IDs updated for admin)
    const visibleSidebar = document.getElementById('admin-sidebar');
    const desktopToggleAdmin = document.getElementById('desktop-toggle-admin');
    const notificationDropdown = document.getElementById('notification-dropdown-admin');
    const notificationToggle = document.getElementById('notification-toggle-admin');

    // Fade-up animation trigger (copied from dashboard.php)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });

        // START: COPIED Active link and logout logic from finance.php
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            if (!link.classList.contains('logout-link')) {
                link.classList.remove('active');
                if (link.href.includes('staff.php')) { // Targetting staff.php
                    link.classList.add('active'); 
                }
            } else {
                link.classList.remove('active');
            }
        });
        // END: COPIED Active link and logout logic
    });

    // Sidebar Toggle Logic (Consistent with finance.php)
    if (desktopToggleAdmin) {
        desktopToggleAdmin.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        // Close sidebar on outside click on mobile (from finance.php logic)
        if (window.innerWidth < 769 && visibleSidebar && visibleSidebar.classList.contains('open') && !visibleSidebar.contains(e.target) && !(desktopToggleAdmin && desktopToggleAdmin.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
        
        // Close notification dropdown on outside click (from finance.php logic)
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-admin') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Notification Dropdown Toggle (Copied from finance.php)
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }


    // Multi-color palette for charts (copied from dashboard.php for consistency)
    const palette = {
        red: 'rgba(255, 23, 68, 1)',
        pink: 'rgba(255, 138, 155, 1)',
        yellow: 'rgba(255, 170, 0, 1)',
        green: 'rgba(0, 255, 136, 1)',
        blue: 'rgba(66, 153, 255, 1)'
    };

    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceLabels = <?php echo json_encode(array_keys($performance)); ?>;
    const performanceData = <?php echo json_encode(array_values($performance)); ?>;

    // Create color array cycling through the palette (similar to attendance chart)
    const barColors = performanceData.map((_, i) => {
        const list = [palette.red, palette.yellow, palette.green, palette.blue, palette.pink];
        return list[i % list.length];
    });

    new Chart(ctx,{
        type:'bar',
        data:{
            labels: performanceLabels,
            datasets:[{
                label:'Performance (%)',
                data: performanceData,
                backgroundColor: barColors, /* Use defined palette colors */
                borderColor: 'transparent', /* Changed for better neon aesthetic */
                borderWidth: 0,
                borderRadius: 8 /* Consistent with dashboard.php attendance chart */
            }]
        },
        options:{
            animation:{duration:900,easing:'easeOutQuart'},
            plugins:{legend:{labels:{color:'#fff'}}},
            scales:{
                x:{ticks:{color:'#ffcccb'},grid:{display:false}},
                y:{
                    ticks:{color:'#ffcccb'},
                    grid:{color:'rgba(255,255,255,0.03)'}, /* Consistent grid color */
                    beginAtZero:true,
                    max:100
                }
            }
        }
    });
</script>
</body>
</html>