<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Customer Dashboard</title>
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

        /* --- New Container for Sidebar + Main Content (Flexbox) --- */
        .page-container {
            display: flex; /* Enable Flexbox */
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
                position: sticky; /* Sticky or relative works, sticky is good for height/scrolling */
                top: 0;
                width: 280px;
                height: 100vh; /* Ensure it takes full height */
                flex-shrink: 0; /* Prevent the sidebar from shrinking */
            }
            /* Remove main-content margin-left as flexbox handles the positioning */
            .main-content {
                flex-grow: 1; /* Allow the main content to take up the rest of the space */
                margin-left: 0; /* Remove the old margin */
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
        /* Keep main-content margin-left at 0 for all screen sizes (handled by flexbox) */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
        
        /* 💡 REMOVED THE CONFLICTING CSS RULE HERE! The hover effect will now work. */
        
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
                <a href="dashboard.php" class="active">
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
                <a href="progress.php">
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

        <main id="customer-main" class="main-content p-6">
            <header class="top-bar flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <button id="desktop-toggle-customer" class="md:hidden p-2 rounded-lg bg-accent text-text">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-2xl font-bold">Dashboard Overview</h1>
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

            <section class="card p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-2" id="customer-welcome-name">Welcome back, User!</h2>
                    <p class="text-text-muted mb-4">You're currently on a **5-day streak**! Keep up the great work.</p>
                    <button class="bg-primary px-6 py-3 rounded-full font-bold text-sm" onclick="alert('Starting guided workout...')">
                        <span class="material-symbols-outlined">directions_run</span>
                        Start Quick Workout
                    </button>
                </div>
                <div class="mt-6 md:mt-0 md:ml-6 bg-accent p-4 rounded-lg min-w-[200px]">
                    <p class="text-xs text-text-muted mb-1 flex items-center gap-1"><span class="material-symbols-outlined text-sm text-success">trophy</span> Loyalty Rewards</p>
                    <p class="font-bold text-lg text-primary" id="customer-rewards-info">0 Points | Bronze</p>
                    <p class="text-xs text-text-muted mt-1">Visit **Progress** for detailed goals.</p>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 card p-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center justify-between">
                        <span>Your Next Session</span>
                        <a href="booking.php" class="text-sm text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">calendar_month</span> Full Calendar
                        </a>
                    </h2>
                    <div id="next-session-card" class="bg-primary/20 border-l-4 border-primary p-4 rounded-lg">
                        <p class="text-text-muted">No upcoming confirmed sessions found.</p>
                        <button class="mt-3 bg-primary px-4 py-2 rounded-full font-medium text-xs" onclick="window.location.href='booking.php'">
                            Book Now
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-1 card p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-success">insights</span>
                        Progress Snapshot
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-accent p-3 rounded-lg flex justify-between items-center">
                            <p class="text-sm text-text-muted">Weight Goal Progress</p>
                            <p class="font-bold text-lg text-success" id="progress-weight-pct">0%</p>
                        </div>
                        <div class="bg-accent p-3 rounded-lg flex justify-between items-center">
                            <p class="text-sm text-text-muted">Daily Calories Burned</p>
                            <p class="font-bold text-lg text-warning" id="progress-calories">0 Cal</p>
                        </div>
                        <p class="text-xs text-text-muted italic">Note: Detailed progress tracking requires a dedicated 'progress' table.</p>
                    </div>
                    <a href="progress.php" class="mt-4 block text-center text-sm text-primary hover:underline">View Detailed Progress</a>
                </div>
            </div>

            <div class="card p-6 mt-6">
                <h2 class="text-xl font-bold mb-6">Quick Links</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="booking.php" class="flex flex-col items-center justify-center p-4 bg-accent rounded-lg hover:bg-primary/30 transition duration-300">
                        <span class="material-symbols-outlined text-primary text-3xl mb-1">schedule</span>
                        <p class="font-medium text-sm">Book Session</p>
                    </a>
                    <a href="progress.php" class="flex flex-col items-center justify-center p-4 bg-accent rounded-lg hover:bg-primary/30 transition duration-300">
                        <span class="material-symbols-outlined text-success text-3xl mb-1">bar_chart</span>
                        <p class="font-medium text-sm">View Progress</p>
                    </a>
                    <a href="billing.php" class="flex flex-col items-center justify-center p-4 bg-accent rounded-lg hover:bg-primary/30 transition duration-300">
                        <span class="material-symbols-outlined text-warning text-3xl mb-1">payment</span>
                        <p class="font-medium text-sm">Renew Membership</p>
                    </a>
                    <a href="profile.php" class="flex flex-col items-center justify-center p-4 bg-accent rounded-lg hover:bg-primary/30 transition duration-300">
                        <span class="material-symbols-outlined text-text text-3xl mb-1">account_circle</span>
                        <p class="font-medium text-sm">Update Profile</p>
                    </a>
                </div>
            </div>
        </main>

    </div> 
    
    <script>
        // JS is now responsible for fetching data from the API endpoint
        const visibleSidebar = document.getElementById('customer-sidebar');
        const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');
        const nextSessionCard = document.getElementById('next-session-card');
        const rewardsInfo = document.getElementById('customer-rewards-info');
        const progressWeightPct = document.getElementById('progress-weight-pct');
        const progressCalories = document.getElementById('progress-calories');
        const notificationCount = document.getElementById('notification-count');
        const notificationDropdown = document.getElementById('notification-dropdown-customer');


        async function fetchDashboardData(userId) {
            try {
                // *** Fetching data from the new PHP backend file ***
                const response = await fetch(`../api/dashboardAPI.php${userId ? `?user_id=${userId}` : ''}`); 
                const data = await response.json();

                if (!data.success) {
                    console.error('API Error:', data.message);
                    alert(`Failed to load user data: ${data.message}. Check database and user login.`);
                    // Fallback to minimal state on API failure
                    return { user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, next_session: null, rewards: { points: 0, tier: 'N/A' }, progress: { weight_goal_pct: 0, calories_today: 0 }, notifications: [] };
                }
                return data;

            } catch (error) {
                console.error('Fetch Error: Could not connect to dashboardAPI.php', error);
                alert("Server connection failed. Ensure dashboardAPI.php and config.php are accessible.");
                // Fallback to minimal state on network/server error
                return { user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, next_session: null, rewards: { points: 0, tier: 'N/A' }, progress: { weight_goal_pct: 0, calories_today: 0 }, notifications: [] };
            }
        }

        function updateUI(data) {
            const userName = data.user.name;
            const userRole = data.user.role;
            const avatarInitial = data.user.avatar_initial;
            
            // 1. User Info
            document.getElementById('customer-welcome-name').textContent = `Welcome back, ${userName}!`;
            document.getElementById('customer-top-avatar').textContent = avatarInitial;
            document.getElementById('customer-user-avatar-sidebar').textContent = avatarInitial;
            document.querySelector('#customer-user-info p:first-child').textContent = userName;
            document.querySelector('#customer-user-info p:last-child').textContent = userRole || 'Member'; 

            // 2. Next Session
            if (data.next_session) {
                nextSessionCard.innerHTML = `
                    <p class="text-lg font-bold text-text mb-1">${data.next_session.service_name}</p>
                    <p class="text-sm text-text-muted">
                        ${new Date(data.next_session.start_time).toLocaleString('en-US', { weekday: 'long', month: 'short', day: 'numeric' })} | 
                        ${new Date(data.next_session.start_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })} - 
                        ${new Date(data.next_session.end_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })} | 
                        ${data.next_session.trainer_name || 'No Trainer Assigned'}
                    </p>
                    <button class="mt-3 bg-primary px-4 py-2 rounded-full font-medium text-xs" onclick="alert('Check-in successful for ${data.next_session.service_name}!')">
                        Check In Now
                    </button>
                `;
            } else {
                 // Already defaults to "No upcoming confirmed sessions found." in HTML
                 nextSessionCard.innerHTML = `<p class="text-text-muted">No upcoming confirmed sessions found.</p>
                        <button class="mt-3 bg-primary px-4 py-2 rounded-full font-medium text-xs" onclick="window.location.href='booking.php'">
                            Book Now
                        </button>`;
            }

            // 3. Rewards
            rewardsInfo.textContent = `${data.rewards.points.toLocaleString()} Points | ${data.rewards.tier}`;

            // 4. Progress (Uses default 0/0 from API if no dedicated table exists)
            progressWeightPct.textContent = `${data.progress.weight_goal_pct}%`;
            progressCalories.textContent = `${data.progress.calories_today.toLocaleString()} Cal`;

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

        // --- Main Load Logic ---
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


        // Sidebar and UI Logic (Original Logic retained)
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
            // Ensure the dashboard.php link still gets the 'active' class
            if (link.href.includes('dashboard.php')) link.classList.add('active'); 
            
            // This line ensures the Logout link doesn't keep the persistent background
            if (link.classList.contains('logout-link')) link.classList.remove('active');

            link.addEventListener('click', () => {
                if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
            });
        });
        
        // Notification Dropdown Toggle
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