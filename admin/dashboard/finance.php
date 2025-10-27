<?php
// finance.php
// Admin Finance Dashboard — consistent with admin-dashboard.php design system

// --- PHP Data (Preserved from original finance.php) ---
$admin_name = "Admin User";
$admin_role = "Administrator";
$avatar_initial = strtoupper(substr($admin_name, 0, 1));

// Placeholder Data
$upcoming_payments = [
    ['name'=>'Juan Dela Cruz','amount'=>1200,'due'=>'2025-10-20'],
    ['name'=>'Maria Santos','amount'=>1500,'due'=>'2025-10-22'],
    ['name'=>'Gym Equipment Co.','amount'=>8000,'due'=>'2025-10-30'],
    ['name'=>'Corporate Plan','amount'=>5600,'due'=>'2025-11-02'],
];

$expenses_summary = [
    'Rent' => 12000,
    'Salaries' => 34000,
    'Utilities' => 4200,
    'Maintenance' => 2800,
    'Marketing' => 1600,
    'Miscellaneous' => 900,
];

$total_revenue = 45230.75;

// Notification data (Consistent with dashboard.php)
// NOTE: Renamed variable to avoid conflict with customer dashboard JS
$admin_notification_count = 3;
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
    <title>Lena Gym Bocaue - Finance Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        // TAILWIND CONFIG PRESERVED FROM booking.php
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
                }
            }
        }
    </script>

    <style>
        /* CSS Variables COPIED FROM booking.php */
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
            --error-rgb: 255, 68, 68;
            --success-rgb: 0, 255, 136;
        }

        /* Body and Desktop Wrapper COPIED FROM booking.php */
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
        .desktop-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling COPIED FROM booking.php - overflow-y: hidden; applied */
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
            /* 👇 REMOVED SCROLLER: was overflow-y: auto; */
            overflow-y: hidden;
        }
        .sidebar.open {
            transform: translateX(0);
        }

        @media (min-width: 769px) {
            .desktop-wrapper {
                display: flex;
                min-height: 100vh;
            }
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

        /* Card and Avatar Styles COPIED FROM booking.php */
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
        }
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
        
        /* Retain finance.php specific styles */
        .placeholder-box { min-height: 60px; border: 1px dashed rgba(var(--primary-rgb), 0.12); border-radius:12px; display:flex; align-items:center; justify-content:center; color: rgb(var(--text-muted-rgb)); background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05)); }
        .fade-up { opacity: 0; transform: translateY(8px); transition: all 0.45s ease; }
        .fade-up.in { opacity: 1; transform: translateY(0); }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin"> <div class="desktop-wrapper"> 
    <aside id="admin-sidebar" class="sidebar"> <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>
        <nav class="space-y-2">
            <a href="dashboard.php"><span class="material-symbols-outlined text-2xl">dashboard</span><span>Dashboard</span></a>
            <a href="finance.php" class="active"><span class="material-symbols-outlined text-2xl">account_balance_wallet</span><span>Finance</span></a>
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
            <a href="profile.php">
                <div class="user-avatar w-8 h-8 text-sm" id="admin-user-avatar-sidebar"><?php echo $avatar_initial; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </aside>

    <main id="admin-main" class="main-content p-6"> <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text"> <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Finance Dashboard</h1>
            </div>
            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="admin-top-avatar"><?php echo $avatar_initial; ?></div> <div id="admin-user-info" class="hidden sm:block"> <p class="text-sm font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                        <p class="text-xs text-text-muted"><?php echo htmlspecialchars($admin_role); ?></p>
                    </div>
                </div>
                <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-1" onclick="alert('Open Quick Add modal (implement)')">
                    <span class="material-symbols-outlined">add_chart</span>
                    <span class="hidden md:inline">New Transaction</span>
                </button>
                <div class="relative">
                    <button id="notification-toggle-admin" class="p-3 rounded-full bg-accent text-text relative"> <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center" id="notification-count-admin"><?php echo $admin_notification_count; ?></span> 
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

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 fade-up">
                <div class="tiny">Total Revenue (Month)</div>
                <div class="text-2xl font-extrabold mt-2 text-success">₱ <?php echo number_format($total_revenue, 2); ?></div>
                <div class="text-xs tiny mt-1">Target ₱50,000.00</div>
            </div>
            <div class="card p-6 fade-up">
                <div class="tiny">Total Expenses (Month)</div>
                <div class="text-2xl font-extrabold mt-2 text-error">₱ <?php echo number_format(array_sum($expenses_summary), 2); ?></div>
                <div class="text-xs tiny mt-1">Variance -<?php echo number_format(array_sum($expenses_summary) / 1000, 2); ?>k</div>
            </div>
            <div class="card p-6 fade-up">
                <div class="tiny">Net Profit (Month)</div>
                <div class="text-2xl font-extrabold mt-2 text-primary">₱ <?php echo number_format($total_revenue - array_sum($expenses_summary), 2); ?></div>
                <div class="text-xs tiny mt-1">Growth +4.5%</div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="card md:col-span-1 fade-up">
                <h2 class="text-lg font-bold mb-3">Upcoming Payments</h2>
                <table class="w-full text-left text-sm">
                    <thead class="text-text-muted">
                        <tr><th>Name</th><th class="text-right">Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($upcoming_payments as $p): ?>
                        <tr class="border-b border-primary/20">
                            <td class="py-2"><?php echo htmlspecialchars($p['name']); ?><div class="text-xs text-text-muted"><?php echo htmlspecialchars($p['due']); ?></div></td>
                            <td class="py-2 text-right">₱ <?php echo number_format($p['amount'],2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="mt-3 text-xs text-text-muted">
                    Total Pending: ₱ <?php echo number_format(array_sum(array_column($upcoming_payments,'amount')),2); ?>
                </div>
            </div>

            <div class="card md:col-span-1 fade-up">
                <h2 class="text-lg font-bold mb-3">Expense Summary</h2>
                <ul class="space-y-3 text-sm">
                    <?php foreach($expenses_summary as $label=>$value): ?>
                    <li class="flex justify-between border-b border-primary/10 pb-2">
                        <span><?php echo htmlspecialchars($label); ?></span>
                        <span class="font-medium">₱ <?php echo number_format($value,2); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-3 text-xs text-text-muted">Total Expenses: ₱ <?php echo number_format(array_sum($expenses_summary),2); ?></div>
            </div>

            <div class="card md:col-span-1 fade-up">
                <h2 class="text-lg font-bold mb-3">Profit / Loss (Last 3 Months)</h2>
                <canvas id="profitLossChart" height="160"></canvas>
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

    // Fade-up animation trigger
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, i) => {
            setTimeout(() => el.classList.add('in'), i * 80);
        });
        
        // Ensure 'active' class is set on the current link (copied from booking.php logic)
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            if (!link.classList.contains('logout-link')) {
                link.classList.remove('active');
                if (link.href.includes('finance.php')) {
                    link.classList.add('active'); // Set current page as active
                }
            } else {
                link.classList.remove('active');
            }
        });
    });

    // Sidebar Toggle Logic (Consistent with booking.php)
    if (desktopToggleAdmin) {
        desktopToggleAdmin.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        // Close sidebar on outside click on mobile (from booking.php logic)
        if (window.innerWidth < 769 && visibleSidebar && visibleSidebar.classList.contains('open') && !visibleSidebar.contains(e.target) && !(desktopToggleAdmin && desktopToggleAdmin.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
        
        // Close notification dropdown on outside click (from booking.php logic)
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-admin') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Notification Dropdown Toggle (Copied from booking.php)
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    
    // Consistent color palette (Preserved)
    const palette = {
        red: 'rgba(255, 23, 68, 1)',
        pink: 'rgba(255, 138, 155, 1)',
        yellow: 'rgba(255, 170, 0, 1)',
        green: 'rgba(0, 255, 136, 1)',
        blue: 'rgba(66, 153, 255, 1)'
    };

    // Chart Logic (Preserved)
    const ctx = document.getElementById('profitLossChart').getContext('2d');
    const months = [];
    for (let i = 2; i >= 0; i--) {
        const d = new Date();
        d.setMonth(d.getMonth() - i);
        months.push(d.toLocaleString('default', { month: 'short' }));
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Revenue',
                    data: [<?php echo $total_revenue; ?>, <?php echo $total_revenue * 0.9; ?>, <?php echo $total_revenue * 1.1; ?>],
                    borderColor: palette.red,
                    backgroundColor: 'rgba(255,23,68,0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Expenses',
                    data: [<?php echo array_sum($expenses_summary) * 0.9; ?>, <?php echo array_sum($expenses_summary) * 1.05; ?>, <?php echo array_sum($expenses_summary) * 0.95; ?>],
                    borderColor: palette.yellow,
                    backgroundColor: 'rgba(255,170,0,0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Profit',
                    data: [
                        <?php echo $total_revenue - array_sum($expenses_summary); ?>,
                        <?php echo ($total_revenue * 0.9) - (array_sum($expenses_summary) * 1.05); ?>,
                        <?php echo ($total_revenue * 1.1) - (array_sum($expenses_summary) * 0.95); ?>
                    ],
                    borderColor: palette.green,
                    backgroundColor: 'rgba(0,255,136,0.15)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            animation: { duration: 900, easing: 'easeOutQuart' },
            plugins: { legend: { labels: { color: '#fff' } } },
            scales: {
                x: { ticks: { color: '#ffcccb' }, grid: { display: false } },
                y: { ticks: { color: '#ffcccb' }, grid: { color: 'rgba(255,255,255,0.03)' } }
            }
        }
    });
</script>
</body>
</html>