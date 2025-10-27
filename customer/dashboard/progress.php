<?php
// progress.php - Customer's detailed fitness progress and reports.
// Note: Session data initialization is assumed to be handled elsewhere, 
// using placeholders for user info consistency.

// REMOVED: $user_name = $_SESSION['fullname'] ?? 'John Doe';
// REMOVED: $user_avatar_initial = $user_name[0] ?? 'J'; 
// PHP variables are no longer needed as data is fetched via JS/API
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Progress</title>
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
        .sidebar.open {
            transform: translateX(0);
        }
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
        
        /* Base link style: Applies to all links */
        .sidebar a {
            transition: all 0.3s ease;
            color: rgb(var(--text-muted-rgb)); /* Default color for ALL links */
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
        
        /* STANDARD HOVER AND ACTIVE STYLE: Primary Red text/border, Subtle background */
        .sidebar a:hover, .sidebar a.active {
            color: var(--primary);
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
    </style>
</head>
<body class="customer">

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
                <a href="booking.php">
                    <span class="material-symbols-outlined text-2xl">calendar_month</span>
                    <span>Service Booking</span>
                </a>
                <a href="billing.php">
                    <span class="material-symbols-outlined text-2xl">payment</span>
                    <span>Membership</span>
                </a>
                <a href="progress.php" class="active">
                    <span class="material-symbols-outlined text-2xl">bar_chart</span>
                    <span>Progress</span>
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
                    <div class="user-avatar w-8 h-8 text-sm" id="customer-user-avatar-sidebar">J</div>
                    <span>Profile</span>
                </a>
            </div>
        </aside>

        <main id="customer-main" class="main-content p-6 flex-grow">
            <header class="top-bar flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <button id="desktop-toggle-customer" class="md:hidden p-2 rounded-lg bg-accent text-text">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-2xl font-bold">Progress & Reports</h1>
                </div>
                <div class="top-bar-right flex items-center gap-4 relative">
                    <div class="top-bar-user flex items-center gap-2">
                        <div class="user-avatar" id="customer-top-avatar">J</div>
                        <div id="customer-user-info" class="hidden sm:block">
                            <p class="text-sm font-medium">John Doe</p>
                            <p class="text-xs text-text-muted">Pro Member</p>
                        </div>
                    </div>
                    <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-1" onclick="window.location.href='booking.php'">
                        <span class="material-symbols-outlined">add</span>
                        <span class="hidden md:inline">Book Class</span>
                    </button>
                    <div class="relative">
                        <button id="notification-toggle-customer" class="p-3 rounded-full bg-accent text-text relative">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center" id="notification-count">0</span>
                        </button>
                        <div id="notification-dropdown-customer" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-lg hidden"></div>
                    </div>
                </div>
            </header>
            <section class="card p-6 mb-8">
                <h2 class="text-3xl font-bold mb-2 text-primary">Your Personalized Progress</h2>
                <p class="text-text-muted mb-4">Track your key metrics and see how far you've come.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-accent p-4 rounded-lg">
                        <p class="text-sm text-text-muted">Weight Goal Progress</p>
                        <p class="text-2xl font-bold text-success">0%</p>
                    </div>
                    <div class="bg-accent p-4 rounded-lg">
                        <p class="text-sm text-text-muted">Total Workouts</p>
                        <p class="text-2xl font-bold">12</p>
                    </div>
                    <div class="bg-accent p-4 rounded-lg">
                        <p class="text-sm text-text-muted">Average Session Time</p>
                        <p class="text-2xl font-bold text-primary">60 min</p>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 card p-6">
                    <h2 class="text-xl font-bold mb-4">Workout Log</h2>
                    <div class="space-y-4">
                        <div class="bg-accent p-3 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="font-bold text-lg">Full Body Strength</p>
                                <p class="text-sm text-text-muted">Date: 2023-10-18 | Duration: 65 min</p>
                            </div>
                            <span class="material-symbols-outlined text-primary">trending_up</span>
                        </div>
                        <div class="bg-accent p-3 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="font-bold text-lg">Cardio HIIT</p>
                                <p class="text-sm text-text-muted">Date: 2023-10-16 | Duration: 40 min</p>
                            </div>
                            <span class="material-symbols-outlined text-primary">trending_up</span>
                        </div>
                    </div>
                    <a href="#" class="mt-4 block text-center text-sm text-primary hover:underline">View Detailed Workout History</a>
                </div>

                <div class="lg:col-span-1 card p-6">
                    <h2 class="text-xl font-bold mb-4">Upcoming Benchmarks</h2>
                    <p class="text-text-muted">Next progress check scheduled in 2 weeks. Prepare for your fitness test!</p>
                    <button class="bg-primary px-4 py-2 rounded-full font-bold mt-4" onclick="alert('Viewing benchmark details...')">
                        <span class="material-symbols-outlined text-base">view_timeline</span> View Details
                    </button>
                </div>
            </div>
        </main>

    </div>

    <script>
        // REMOVED PHP variable initialization:
        // const userName = '<?= htmlspecialchars($user_name) ?>';
        // const userInitial = '<?= htmlspecialchars($user_avatar_initial) ?>';

        const visibleSidebar = document.getElementById('customer-sidebar');
        const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');
        const userAvatarSidebar = document.getElementById('customer-user-avatar-sidebar');
        
        // NEW ELEMENTS for data fetching (copied from dashboard.php script)
        const nextSessionCard = document.getElementById('next-session-card'); // Not used on progress.php but needed for updateUI function
        const rewardsInfo = document.getElementById('customer-rewards-info'); // Not used on progress.php but needed for updateUI function
        const progressWeightPct = document.getElementById('progress-weight-pct'); // Not used on progress.php but needed for updateUI function
        const progressCalories = document.getElementById('progress-calories'); // Not used on progress.php but needed for updateUI function
        const notificationCount = document.getElementById('notification-count');
        const notificationDropdown = document.getElementById('notification-dropdown-customer');


        // NEW FUNCTION: fetchDashboardData (copied from dashboard.php script)
        async function fetchDashboardData(userId) {
            try {
                const response = await fetch(`../api/dashboardAPI.php${userId ? `?user_id=${userId}` : ''}`); 
                const data = await response.json();

                if (!data.success) {
                    console.error('API Error:', data.message);
                    // Use a more user-friendly error message on the actual page
                    // alert(`Failed to load user data: ${data.message}. Check database and user login.`); 
                    return { user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, next_session: null, rewards: { points: 0, tier: 'N/A' }, progress: { weight_goal_pct: 0, calories_today: 0 }, notifications: [] };
                }
                return data;

            } catch (error) {
                console.error('Fetch Error: Could not connect to dashboardAPI.php', error);
                // alert("Server connection failed. Ensure dashboardAPI.php and config.php are accessible.");
                return { user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, next_session: null, rewards: { points: 0, tier: 'N/A' }, progress: { weight_goal_pct: 0, calories_today: 0 }, notifications: [] };
            }
        }
        
        // NEW FUNCTION: updateUI (copied and modified from dashboard.php script)
        function updateUI(data) {
            const userName = data.user.name;
            const userRole = data.user.role;
            const avatarInitial = data.user.avatar_initial;
            
            // 1. User Info (Update header and sidebar)
            // document.getElementById('customer-welcome-name') is on dashboard.php, not progress.php.
            document.getElementById('customer-top-avatar').textContent = avatarInitial;
            document.getElementById('customer-user-avatar-sidebar').textContent = avatarInitial;
            document.querySelector('#customer-user-info p:first-child').textContent = userName;
            document.querySelector('#customer-user-info p:last-child').textContent = userRole || 'Member'; 

            // 5. Notifications
            const unreadCount = data.notifications.filter(n => !n.read).length;
            notificationCount.textContent = unreadCount.toString();
            
            notificationDropdown.innerHTML = '';
            if (data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    notificationDropdown.innerHTML += `
                        <div class="p-2 border-b border-accent ${n.read ? 'opacity-50' : ''}">
                            <p class="font-medium text-sm">${n.title}</p>
                            <p class="text-xs text-text-muted">${n.message}</p>
                        </div>
                    `;
                });
            } else {
                notificationDropdown.innerHTML = '<p class="text-xs text-text-muted">No new notifications.</p>';
            }
            // Update the hardcoded notification count span in the header
            document.querySelector('.top-bar-right .relative .w-4.h-4').textContent = unreadCount.toString();
        }
        
        // NEW LOAD LOGIC: (copied from dashboard.php script)
        document.addEventListener('DOMContentLoaded', async () => {
            // Attempt to retrieve a user ID from a previous login
            let userId = localStorage.getItem('gymrat_user_id'); 
            
            const data = await fetchDashboardData(userId);
            
            // If the API returned a user, store their ID for future requests
            if (data.user && data.user.id) {
                localStorage.setItem('gymrat_user_id', data.user.id);
            }
            
            updateUI(data);
        });
        
        // Original Progress.php UI logic (modified to remove PHP variable use)
        // if (userAvatarSidebar) {
        //     userAvatarSidebar.textContent = userInitial; // Removed. Replaced by updateUI(data)
        // }


        if (desktopToggleCustomer) {
            desktopToggleCustomer.addEventListener('click', () => {
                visibleSidebar.classList.toggle('open');
            });
        }
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleCustomer && desktopToggleCustomer.contains(e.target))) {
                visibleSidebar.classList.remove('open');
            }
        });
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            // Correctly set the 'Progress' link as active
            if (link.href.includes('progress.php')) link.classList.add('active'); 
            
            // This line ensures the Logout link doesn't keep the persistent background
            if (link.classList.contains('logout-link')) link.classList.remove('active');

            link.addEventListener('click', () => {
                if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
            });
        });
        
        // NEW NOTIFICATION DROPDOWN TOGGLE (copied from dashboard.php script)
        document.getElementById('notification-toggle-customer').addEventListener('click', (e) => {
             notificationDropdown.classList.toggle('hidden');
             e.stopPropagation(); 
        });
        document.addEventListener('click', (e) => {
            if (!notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-customer') {
                notificationDropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>