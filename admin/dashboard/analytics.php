<?php
// analytics.php — Admin Analytics Dashboard (consistent with dashboard.php)

$admin_name = "Admin User";
$admin_role = "Administrator";
$avatar_initial = strtoupper(substr($admin_name, 0, 1));

// Placeholder analytics data
$monthly_growth = [12, 15, 10, 18, 20, 17, 23];
$category_performance = [
    'Personal Training' => 45,
    'Memberships' => 30,
    'Supplements' => 15,
    'Classes' => 10
];
$member_satisfaction = 4.4;
$active_rate = 86;

// Notification data (consistent with dashboard.php)
$notification_count = 3;
$notification_alerts = [
    'Payment overdue — Gym Equipment Co.',
    'Shift conflict detected for 2025-10-21',
    'Member retention at risk: 2',
];

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Analytics</title>

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
                        // Added missing 'neon-glow' shadow for full consistency
                        'neon-glow': '0 0 10px rgba(255, 23, 68, 0.8)',
                        'neon-button': '0 0 15px rgba(255, 23, 68, 0.4), inset 0 0 10px rgba(255, 23, 68, 0.2)'
                    }
                },
            },
        }
    </script>

    <style>
        /* CSS Variables and body styling for consistency */
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
            /* Use CSS variables for background gradient */
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }
        .page-container { display: flex; min-height: 100vh; }

        /* Sidebar styling for mobile/desktop consistency */
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
            /* Changed from overflow-y: auto; to hidden to match members.php */
            overflow-y: hidden; 
        }
        .sidebar.open { transform: translateX(0); }

        @media (min-width: 769px) {
            .sidebar { transform: translateX(0); position: sticky; top: 0; width: 280px; height: 100vh; flex-shrink: 0; }
            .main-content { flex-grow: 1; margin-left: 0; }
        }

        .sidebar .logo-image { width: 48px; height: auto; }
        .sidebar h1 { font-size: 1.05rem; color: #ffc9c7; } /* Consistent logo text color */

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
            /* Adjusted opacity to match members.php */
            background: rgba(var(--primary-rgb), 0.2); 
            border-left: 4px solid var(--primary);
        }

        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
        }
        .top-bar { display:flex; align-items:center; justify-content:space-between; gap:1rem; }

        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff4444);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;
        }

        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        .placeholder-box { min-height: 60px; border: 1px dashed rgba(var(--primary-rgb), 0.12); border-radius:12px; display:flex; align-items:center; justify-content:center; color: rgb(var(--text-muted-rgb)); background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05)); }
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

        .btn-add { background: linear-gradient(90deg,var(--primary), #ff8a9b); color: white; padding: 10px 16px; border-radius: 999px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.25); border: none; cursor: pointer; font-weight: 600; }
        .btn-ghost { background: transparent; border: 1px solid rgba(var(--primary-rgb),0.12); color: var(--primary); padding: .5rem .75rem; border-radius: 10px; }

        .metric-value { font-size: 1.6rem; font-weight: 800; }
        .metric-sub { font-size: 0.9rem; color: rgba(var(--text-muted-rgb), 0.9); } /* Added for consistency */

        @media (max-width: 768px) { .main-content { margin-left:0; padding: 1rem; } }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin">
<div class="page-container">
    <aside id="admin-sidebar" class="sidebar"> 
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>
        <nav class="space-y-2">
            <a href="dashboard.php"><span class="material-symbols-outlined text-2xl">dashboard</span><span>Dashboard</span></a>
            <a href="finance.php"><span class="material-symbols-outlined text-2xl">account_balance_wallet</span><span>Finance</span></a>
            <a href="members.php"><span class="material-symbols-outlined text-2xl">group</span><span>Members</span></a>
            <a href="staff.php"><span class="material-symbols-outlined text-2xl">badge</span><span>Staff</span></a>
            <a href="analytics.php"><span class="material-symbols-outlined text-2xl">bar_chart</span><span>Analytics</span></a>
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
    <main id="admin-main" class="main-content p-6">
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Analytics Overview</h1>
                <div class="tiny">Reports and insights</div>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="admin-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="admin-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($admin_role); ?></p>
                    </div>
                </div>

                <button class="btn-add" onclick="alert('Open Quick Add modal (implement)')">
                    <span class="material-symbols-outlined">add_chart</span>
                    <span class="hidden md:inline">Quick Add</span>
                </button>

                <div class="relative">
                    <button id="notification-toggle-admin" class="p-3 rounded-full bg-accent text-text relative" aria-label="notifications">
                        <span class="material-symbols-outlined">notifications</span>
                        <span id="notification-count-admin" class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center"><?php echo $notification_count; ?></span>
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
        
        <section class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="card p-6 fade-up">
                <div class="tiny">Monthly Growth</div>
                <div class="metric-value mt-2">+<?php echo end($monthly_growth); ?>%</div>
                <div class="metric-sub mt-2">Compared to last month</div> </div>
            <div class="card p-6 fade-up">
                <div class="tiny">Active Members</div>
                <div class="metric-value mt-2"><?php echo $active_rate; ?>%</div>
                <div class="metric-sub mt-2">Retention Rate</div> </div>
            <div class="card p-6 fade-up">
                <div class="tiny">Member Satisfaction</div>
                <div class="metric-value mt-2"><?php echo $member_satisfaction; ?> / 5</div>
                <div class="metric-sub mt-2">Average Rating</div> </div>
            <div class="card p-6 fade-up">
                <div class="tiny">Revenue Channels</div>
                <div class="metric-value mt-2"><?php echo count($category_performance); ?></div>
                <div class="metric-sub mt-2">Tracked Categories</div> </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6 fade-up">
                <h2 class="font-bold mb-2 text-lg">Monthly Growth Trend</h2>
                <canvas id="growthChart" height="180"></canvas>
            </div>
            <div class="card p-6 fade-up">
                <h2 class="font-bold mb-2 text-lg">Category Performance</h2>
                <canvas id="categoryChart" height="180"></canvas>
            </div>
        </section>
    </main>
</div>

<script>
    // DOM elements
    const visibleSidebar = document.getElementById('admin-sidebar');
    const desktopToggleAdmin = document.getElementById('desktop-toggle-admin');
    
    // ADDED: Notification DOM elements
    const notificationDropdown = document.getElementById('notification-dropdown-admin');
    const notificationToggle = document.getElementById('notification-toggle-admin');
    // END ADDED

    // Fade-up animation trigger
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });

        // START: Updated active sidebar link logic from members.php
        // Ensure 'active' class is set on the current link
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            if (!link.classList.contains('logout-link')) {
                link.classList.remove('active');
                // Check if the current link is analytics.php
                if (link.href.includes('analytics.php')) { 
                    link.classList.add('active'); // Set current page as active
                }
            } else {
                link.classList.remove('active');
            }
        });
        // END: Updated active sidebar link logic
    });

    // Sidebar toggle for mobile (copied from dashboard.php)
    if (desktopToggleAdmin) {
        desktopToggleAdmin.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleAdmin && desktopToggleAdmin.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
        
        // ADDED: Close notification dropdown on outside click (from members.php)
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-admin') {
            notificationDropdown.classList.add('hidden');
        }
        // END ADDED
    });
    
    // ADDED: Notification Dropdown Toggle (Copied from members.php)
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    // END ADDED

    // Multi-color palette for charts (copied from dashboard.php)
    const palette = {
        red: 'rgba(255, 23, 68, 1)',
        pink: 'rgba(255, 138, 155, 1)',
        yellow: 'rgba(255, 170, 0, 1)',
        green: 'rgba(0, 255, 136, 1)',
        blue: 'rgba(66, 153, 255, 1)'
    };

    // Growth chart (consistent options/styles)
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type:'line',
        data:{
            labels:['Jan','Feb','Mar','Apr','May','Jun','Jul'],
            datasets:[{
                label:'Growth %',
                data:<?php echo json_encode($monthly_growth); ?>,
                borderColor:palette.red,
                backgroundColor:'rgba(255,23,68,0.15)',
                fill:true,
                tension:0.35, // Adjusted tension for consistency
                pointRadius:4,
                pointHoverRadius:6
            }]
        },
        options:{
            animation:{duration:900,easing:'easeOutQuart'}, // Consistent animation settings
            plugins:{
                legend:{labels:{color:'#fff'}},
                tooltip:{ mode: 'index', intersect: false } // Consistent tooltip mode
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false }, // Consistent interaction settings
            scales:{
                x:{ticks:{color:'#ffcccb'},grid:{display:false}},
                y:{ticks:{color:'#ffcccb'},grid:{color:'rgba(255,255,255,0.03)'}} // Consistent grid color
            }
        }
    });

    // Category performance (pie/doughnut) - consistent options/styles
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catLabels = <?php echo json_encode(array_keys($category_performance)); ?>;
    const catData = <?php echo json_encode(array_values($category_performance)); ?>;
    // Use a wider range of colors for better visual separation
    const catColors = [palette.red, palette.blue, palette.yellow, palette.green];
    new Chart(catCtx,{
        type:'doughnut',
        data:{labels:catLabels,datasets:[{data:catData,backgroundColor:catColors,borderWidth:0}]},
        options:{
            animation:{duration:900,easing:'easeOutQuart'}, // Consistent animation settings
            plugins:{
                legend:{labels:{color:'#fff'}},
                tooltip:{
                    callbacks:{label:ctx=>`${ctx.label}: ${ctx.parsed}%`}
                }
            },
            cutout:'65%'
        }
    });

    // Set notification count
    const notifCountEl = document.getElementById('notification-count-admin');
    if(notifCountEl) notifCountEl.textContent = '<?php echo $notification_count; ?>';
</script>
</body>
</html>