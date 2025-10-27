<?php
// dashboard.php - Admin Dashboard

// 1. Start session and include config for DB connection (Copied structure from booking.php)
session_start();
// Path: ../../config.php (Adjust path if needed based on actual file location)
include_once('../../config.php'); 

// --- PHP Data (Preserved from original finance.php & merged with dashboard.php) ---
$admin_name = "Admin User";
$admin_role = "Administrator";
$avatar_initial = strtoupper(substr($admin_name, 0, 1));

// Key metrics (placeholders)
$total_revenue_this_month = 45230.75;
$member_count_active = 420;
$member_count_inactive = 38;
$member_count_new = 24;
$occupancy_rate = 72.5; // percent
$churn_rate = 3.2; // percent

// Financial overview (placeholders) - MERGED & ADJUSTED
$upcoming_payments = [
    ['name'=>'Juan Dela Cruz','amount'=>1200,'due'=>'2025-10-20'],
    ['name'=>'Maria Santos','amount'=>1500,'due'=>'2025-10-22'],
    // Added corporate plan and kept gym equipment co from finance.php for consistency
    ['name'=>'Gym Equipment Co.','amount'=>8000,'due'=>'2025-10-30'], 
    ['name'=>'Corporate Plan','amount'=>5600,'due'=>'2025-11-02'], 
];

// Expenses summary - MERGED & ADJUSTED to be consistent with finance.php
$expenses_summary = [
    'Rent' => 12000,
    'Salaries' => 34000,
    'Utilities' => 4200,
    'Maintenance' => 2800,
    'Marketing' => 1600,
    'Miscellaneous' => 900,
];

// Staff roster (placeholders) - from original dashboard.php
$staff_roster = [
    ['name'=>'Carlos Reyes','role'=>'Head Trainer','shift'=>'08:00-16:00'],
    ['name'=>'Anna Lim','role'=>'Trainer','shift'=>'14:00-22:00'],
    ['name'=>'Pedro Cruz','role'=>'Reception','shift'=>'07:00-15:00'],
    ['name'=>'Liza Ramos','role'=>'Trainer','shift'=>'09:00-17:00'],
];

// Shift conflicts example: overlapping time windows (placeholder)
$shift_conflicts = [
    ['staff1'=>'Carlos Reyes','staff2'=>'Liza Ramos','time'=>'09:00-11:00'],
];

// Member analytics (placeholders)
$attendance_last_7_days = [45, 52, 47, 60, 58, 65, 62]; // sample counts Mon..Sun
$feedback_rating_avg = 4.3; // out of 5
$retention_alerts = [
    ['member'=>'Tony Stark','last_visit'=>'2025-07-03','risk'=>'High'],
    ['member'=>'Natasha Romanoff','last_visit'=>'2025-08-20','risk'=>'Medium'],
];

// Notification data (Consistent with finance.php)
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
    <title>Lena Gym Bocaue - Admin Dashboard</title> 
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        // Copied from booking.php
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
        /* CSS Variables COPIED FROM finance.php */
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

        /* Body and Desktop Wrapper COPIED FROM finance.php */
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

        /* Sidebar Styling COPIED FROM finance.php - overflow-y: hidden; applied */
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
            overflow-y: hidden; /* From finance.php */
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

        /* Card and Avatar Styles COPIED FROM finance.php */
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
        
        /* Retain dashboard.php specific styles */
        .metric-card-vibrant {
            position: relative;
            background: rgba(var(--card-rgb), 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3); 
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 15px rgba(255, 23, 68, 0.35); 
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .metric-card-vibrant::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px; 
            background: linear-gradient(90deg, var(--primary), #ff8a9b);
            opacity: 0.8;
        }
        .metric-value { font-size: 2.5rem; font-weight: 900; line-height: 1; color: var(--primary); }
        .metric-title { font-size: 1rem; font-weight: 500; color: rgb(var(--text-muted-rgb)); }
        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        
        /* Added from finance.php styles to support notifications list styling */
        .placeholder-box { min-height: 60px; border: 1px dashed rgba(var(--primary-rgb), 0.12); border-radius:12px; display:flex; align-items:center; justify-content:center; color: rgb(var(--text-muted-rgb)); background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05)); }
        
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin">
    <div id="toast-notification" class="hidden" role="alert">
        <span id="toast-icon" class="material-symbols-outlined mr-3 text-2xl"></span>
        <div id="toast-message" class="font-medium"></div>
    </div>
    
    <div class="desktop-wrapper"> 
        <aside id="admin-sidebar" class="sidebar"> 
            <div class="flex items-center gap-3 mb-8">
                <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
                <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="active">
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
                <a href="settings.php">
                    <span class="material-symbols-outlined text-2xl">settings</span>
                    <span>Settings</span>
                </a>
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

        <main id="admin-main" class="main-content p-6 lg:p-10"> 
            
            <header class="top-bar flex items-center justify-between mb-8 pb-4 border-b border-text-muted/10">
                <div class="flex items-center gap-4">
                    <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
                    <div class="tiny hidden md:block">Overview for <?php echo date('F Y'); ?></div>
                </div>
                <div class="top-bar-right flex items-center gap-4 relative">
                    <div class="top-bar-user flex items-center gap-2">
                        <div class="user-avatar" id="admin-top-avatar"><?php echo $avatar_initial; ?></div>
                        <div id="admin-user-info" class="hidden sm:block">
                            <p class="text-sm font-medium"><?php echo htmlspecialchars($admin_name); ?></p>
                            <p class="text-xs text-text-muted"><?php echo htmlspecialchars($admin_role); ?></p>
                        </div>
                    </div>
                    <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-1" onclick="alert('Open Quick Add modal (implement)')">
                        <span class="material-symbols-outlined">add_chart</span>
                        <span class="hidden md:inline">Quick Add</span>
                    </button>
                    <div class="relative">
                        <button id="notification-toggle-admin" class="p-3 rounded-full bg-accent text-text relative hover:shadow-neon-glow transition" aria-label="notifications">
                            <span class="material-symbols-outlined">notifications</span>
                            <span id="notification-count-admin" class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center"><?php echo $admin_notification_count; ?></span>
                        </button>
                        <div id="notification-dropdown-admin" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-xl hidden z-50 min-w-[280px]" aria-hidden="true">
                            <div class="tiny mb-3 font-bold text-text">Notifications (<?php echo $admin_notification_count; ?>)</div>
                            <ul class="text-sm space-y-2">
                                <?php foreach($notification_alerts as $alert): ?>
                                <li class="p-2 rounded-md bg-accent/50 border border-red-600/50 tiny text-text-muted"><?php echo $alert; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="metric-card-vibrant p-5 fade-up col-span-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="metric-title flex items-center gap-2"><span class="material-symbols-outlined text-xl">monetization_on</span> Total Revenue <span class="tiny">(this month)</span></div>
                            <div class="mt-2 metric-value">₱ <?php echo number_format($total_revenue_this_month,2); ?></div>
                        </div>
                        <div class="text-right">
                            <span class="text-success font-bold text-xl">+12%</span>
                            <div class="metric-sub">vs. last month</div>
                        </div>
                    </div>
                </div>
                <div class="metric-card-vibrant p-5 fade-up">
                    <div class="metric-title flex items-center gap-2"><span class="material-symbols-outlined text-xl">person_add</span> Active Members</div>
                    <div class="mt-2 metric-value"><?php echo $member_count_active; ?></div>
                    <div class="metric-sub">Inactive: <?php echo $member_count_inactive; ?> · <span class="text-primary font-bold">New: <?php echo $member_count_new; ?></span></div>
                </div>
                <div class="metric-card-vibrant p-5 fade-up">
                    <div class="metric-title flex items-center gap-2"><span class="material-symbols-outlined text-xl">check_circle</span> Occupancy Rate</div>
                    <div class="mt-2 metric-value text-green-500"><?php echo $occupancy_rate; ?>%</div>
                    <div class="metric-sub">Target: <span class="font-bold">80%</span> utilization</div>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-2 card p-6 fade-up">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Profit & Loss Trend</h2>
                        <div class="tiny">Comparison of Revenue, Expenses, & Profit — Last 3 Months</div>
                    </div>
                    <div class="h-80">
                        <canvas id="profitLossChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-1 card p-6 fade-up">
                    <h2 class="text-xl font-bold mb-4">Expenses Summary</h2>
                    <div class="space-y-2">
                        <?php 
                        // Note: Keys are now Title Case (Rent, Salaries, etc.) from finance.php structure
                        $total_expenses = array_sum($expenses_summary);
                        foreach ($expenses_summary as $category => $amount): 
                            $percentage = $total_expenses > 0 ? round(($amount / $total_expenses) * 100) : 0;
                        ?>
                            <div>
                                <div class="flex justify-between tiny text-text">
                                    <span class="capitalize"><?php echo htmlspecialchars($category); ?></span>
                                    <span>₱<?php echo number_format($amount, 2); ?> (<?php echo $percentage; ?>%)</span>
                                </div>
                                <div class="w-full bg-accent rounded-full h-1.5 mt-1">
                                    <div class="h-1.5 rounded-full bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            
            <section class="grid grid-cols-1 gap-6 mb-8">
                <div class="card p-6 fade-up">
                    <h2 class="text-xl font-bold mb-4">Upcoming Payments</h2>
                    <table class="w-full text-left tiny data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                    <td class="text-right text-primary font-bold">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td class="text-right"><?php echo (new DateTime($payment['due']))->format('M j'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-2 card p-6 fade-up">
                    <h2 class="text-xl font-bold mb-4">Member Attendance (Last 7 Days)</h2>
                    <div class="h-80">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
                <div class="lg:col-span-1 space-y-6">
                    <div class="card p-6 fade-up">
                        <h2 class="text-xl font-bold mb-4">Staff Roster</h2>
                        <ul class="space-y-3">
                            <?php foreach ($staff_roster as $staff): ?>
                                <li class="flex items-center justify-between tiny border-b border-text-muted/10 pb-2">
                                    <div>
                                        <p class="font-medium text-text"><?php echo htmlspecialchars($staff['name']); ?></p>
                                        <p class="text-xs text-text-muted"><?php echo htmlspecialchars($staff['role']); ?></p>
                                    </div>
                                    <span class="text-primary font-bold"><?php echo htmlspecialchars($staff['shift']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="card p-6 fade-up">
                        <h2 class="text-xl font-bold mb-4 text-warning">Retention Alerts</h2>
                        <ul class="space-y-3">
                            <?php foreach ($retention_alerts as $alert): ?>
                                <li class="p-2 rounded-lg bg-accent/50 border border-warning/50 tiny flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-text"><?php echo htmlspecialchars($alert['member']); ?></p>
                                        <p class="text-xs text-text-muted">Last Visit: <?php echo htmlspecialchars($alert['last_visit']); ?></p>
                                    </div>
                                    <span class="font-bold text-sm text-error"><?php echo htmlspecialchars($alert['risk']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="card p-6 mb-8 fade-up">
                <h2 class="text-xl font-bold mb-4">Shift Conflicts</h2>
                <?php if (!empty($shift_conflicts)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php foreach ($shift_conflicts as $conflict): ?>
                            <div class="bg-error/10 p-3 rounded-lg border border-error flex items-center gap-3">
                                <span class="material-symbols-outlined text-error text-3xl">warning</span>
                                <div class="tiny">
                                    <p class="font-bold text-text">Conflict: <?php echo htmlspecialchars($conflict['time']); ?></p>
                                    <p class="text-text-muted"><?php echo htmlspecialchars($conflict['staff1']); ?> & <?php echo htmlspecialchars($conflict['staff2']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="tiny text-success font-medium">No shift conflicts detected today. 👍</p>
                <?php endif; ?>
            </section>
            
            <script>
                const palette = {
                    red: 'rgba(255, 23, 68, 1)', 
                    redLight: 'rgba(255, 23, 68, 0.5)',
                    gray: 'rgba(255, 255, 255, 0.2)',
                    green: 'rgba(0, 255, 136, 1)', 
                    greenLight: 'rgba(0, 255, 136, 0.5)',
                    blue: 'rgba(59, 130, 246, 1)', 
                    yellow: 'rgba(255, 204, 0, 1)', 
                    pink: 'rgba(255, 105, 180, 1)'
                };

                // Chart 1: Profit & Loss Trend
                const profitLossCtx = document.getElementById('profitLossChart').getContext('2d');
                // The dataset array data needs to be updated to match the new, more complete expense summary
                // The new expense total is now: 12000 + 34000 + 4200 + 2800 + 1600 + 900 = 55500.00
                const current_expenses_total = <?php echo array_sum($expenses_summary); ?>; 
                
                const profitLossData = {
                    labels: ['2 Months Ago', 'Last Month', 'This Month'],
                    datasets: [
                        {
                            label: 'Revenue (Actual)',
                            data: [38000, 42000, <?php echo $total_revenue_this_month; ?>],
                            backgroundColor: palette.redLight,
                            borderColor: palette.red,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 5
                        },
                        {
                            label: 'Expenses (Total)',
                            // Scaled the historical data to be more in line with the new total of 55500.00
                            data: [current_expenses_total * 0.8, current_expenses_total * 0.9, current_expenses_total],
                            backgroundColor: palette.gray,
                            borderColor: 'rgba(255, 255, 255, 0.5)',
                            tension: 0.4,
                            pointRadius: 5
                        },
                    ]
                };

                new Chart(profitLossCtx, {
                    type: 'line',
                    data: profitLossData,
                    options: {
                        maintainAspectRatio: false, 
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        plugins: { 
                            legend: { 
                                labels: { color: '#ffcccb', usePointStyle: true }
                            } 
                        },
                        scales: {
                            x: { ticks: { color: '#ffcccb' }, grid: { color: 'rgba(255,255,255,0.03)' } },
                            y: { 
                                beginAtZero: false,
                                ticks: { 
                                    color: '#ffcccb', 
                                    callback: function(value) { return '₱' + value.toLocaleString(); }
                                }, 
                                grid: { color: 'rgba(255,255,255,0.03)' } 
                            }
                        }
                    }
                });

                // Chart 2: Attendance Bar Chart
                const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
                const attendanceData = [<?php echo implode(', ', $attendance_last_7_days); ?>];
                
                (function() {
                    let labels = [];
                    for (let i = 6; i >= 0; i--) {
                        let d = new Date();
                        d.setDate(d.getDate() - i);
                        labels.push(d.toLocaleDateString(undefined, { weekday: 'short' }));
                    }
                    window.attendanceLabels = labels;
                })();

                const barColors = attendanceData.map((_,i) => {
                    const list = [palette.blue, palette.green, palette.yellow, palette.pink, palette.red];
                    return list[i % list.length];
                });

                const attendanceBarChart = new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: window.attendanceLabels,
                        datasets: [{
                            label: 'Attendance',
                            data: attendanceData,
                            backgroundColor: barColors,
                            borderRadius: 8,
                            barThickness: 18
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: '#ffcccb' }, grid: { display: false } },
                            y: { ticks: { color: '#ffcccb' }, grid: { color: 'rgba(255,255,255,0.03)' } }
                        }
                    }
                });

                // Sidebar Toggle Logic
                const sidebar = document.getElementById('admin-sidebar');
                const toggleBtn = document.getElementById('desktop-toggle-admin');

                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('open');
                    document.body.classList.toggle('overflow-hidden');
                });
                
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 769 && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
                
                // Notification Dropdown Toggle 
                const notificationToggle = document.getElementById('notification-toggle-admin');
                const notificationDropdown = document.getElementById('notification-dropdown-admin');

                if (notificationToggle) {
                    notificationToggle.addEventListener('click', (e) => {
                        notificationDropdown.classList.toggle('hidden');
                        e.stopPropagation(); 
                    });
                }

                document.addEventListener('click', (e) => {
                    // Close sidebar on outside click on mobile (from finance.php logic)
                    if (window.innerWidth < 769 && sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !(toggleBtn && toggleBtn.contains(e.target))) {
                        sidebar.classList.remove('open');
                        document.body.classList.remove('overflow-hidden');
                    }
                    // Close notification dropdown on outside click
                    if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-admin') {
                        notificationDropdown.classList.add('hidden');
                    }
                });
                
                // Fade-in animation for cards 
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('.fade-up').forEach(el => {
                    // Using the same fade-up logic from finance.php for consistency
                    setTimeout(() => el.classList.add('in'), 100); 
                });

            </script>
        </main>
    </div>
</body>
</html>