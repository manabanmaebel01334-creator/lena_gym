<?php
// profile.php - Customer profile and contact details.
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Profile</title>
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
        /* Mobile Sidebar: Fixed and hidden off-screen */
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
        
        /* Desktop Layout Fix (Flexbox) */
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
        /* FIX: Exclude the logout-link from being permanently 'active' */
        .sidebar a:hover:not(.logout-link), 
        .sidebar a.active:not(.logout-link) { 
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.2);
            border-left: 4px solid var(--primary);
        }
        /* Ensure Logout link stays the muted color unless hovered */
        .sidebar a.logout-link {
            color: rgb(var(--text-muted-rgb)); 
            border-left: none;
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

    <div class="desktop-wrapper"> <aside id="customer-sidebar" class="sidebar">
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
                <a href="progress.php">
                    <span class="material-symbols-outlined text-2xl">bar_chart</span>
                    <span>Progress</span>
                </a>
                <a href="settings.php">
                    <span class="material-symbols-outlined text-2xl">settings</span>
                    <span>Settings</span>
                </a>
                <a href="#" class="logout-link" onclick="if(confirm('Are you sure you want to logout?')) { localStorage.clear(); window.location.href = 'index.php'; }">
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

        <main id="customer-main" class="main-content p-6"> <header class="top-bar flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <button id="desktop-toggle-customer" class="md:hidden p-2 rounded-lg bg-accent text-text">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h1 class="text-2xl font-bold">Profile & Account Details</h1>
                </div>
                <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-1" onclick="alert('Proceeding to profile edit...')">
                    <span class="material-symbols-outlined">edit</span>
                    <span class="hidden md:inline">Edit Profile</span>
                </button>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="lg:col-span-1 card p-6 text-center space-y-4">
                    <div class="user-avatar w-24 h-24 text-4xl mx-auto mb-4" id="customer-main-avatar">JD</div>
                    <h2 class="text-2xl font-bold text-text">John Doe</h2>
                    <p class="text-text-muted">Pro Annual Member</p>
                    <div class="space-y-2 text-left">
                        <p class="text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">mail</span> <span class="text-text">john.doe@example.com</span>
                        </p>
                        <p class="text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">phone</span> <span class="text-text">+1 (555) 123-4567</span>
                        </p>
                        <p class="text-sm flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">calendar_month</span> <span class="text-text">Member Since: Jan 1, 2024</span>
                        </p>
                    </div>
                </div>

                <div class="lg:col-span-2 card p-6">
                    <h2 class="text-xl font-bold mb-4">Personal Information</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-text-muted mb-1">First Name</label>
                                <input type="text" id="first_name" value="John" class="w-full p-2 bg-accent/70 border border-accent rounded-lg text-text focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-text-muted mb-1">Last Name</label>
                                <input type="text" id="last_name" value="Doe" class="w-full p-2 bg-accent/70 border border-accent rounded-lg text-text focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-text-muted mb-1">Address</label>
                            <input type="text" id="address" value="123 Gym Road, Bocaue, Bulacan" class="w-full p-2 bg-accent/70 border border-accent rounded-lg text-text focus:border-primary focus:ring-primary">
                        </div>
                        <h3 class="text-lg font-bold pt-2 border-t border-accent mt-4">Emergency Contact</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ec_name" class="block text-sm font-medium text-text-muted mb-1">Name</label>
                                <input type="text" id="ec_name" value="Jane Doe (Wife)" class="w-full p-2 bg-accent/70 border border-accent rounded-lg text-text focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label for="ec_phone" class="block text-sm font-medium text-text-muted mb-1">Phone</label>
                                <input type="text" id="ec_phone" value="+1 (555) 987-6543" class="w-full p-2 bg-accent/70 border border-accent rounded-lg text-text focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                    </div>
                    <button class="bg-primary px-6 py-3 rounded-full font-bold mt-6" onclick="alert('Profile updated successfully!')">
                        Update Details
                    </button>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-xl font-bold mb-4">Other Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <a href="nutrition.php" class="bg-accent p-4 flex flex-col items-center justify-center hover:shadow-neon transition rounded-lg">
                        <span class="material-symbols-outlined text-success text-3xl mb-1">nutrition</span>
                        <p class="font-medium text-sm">Nutrition Plan</p>
                    </a>
                    <a href="progress.php" class="bg-accent p-4 flex flex-col items-center justify-center hover:shadow-neon transition rounded-lg">
                        <span class="material-symbols-outlined text-primary text-3xl mb-1">trending_up</span>
                        <p class="font-medium text-sm">View Progress</p>
                    </a>
                    <a href="billing.php" class="bg-accent p-4 flex flex-col items-center justify-center hover:shadow-neon transition rounded-lg">
                        <span class="material-symbols-outlined text-primary text-3xl mb-1">credit_card</span>
                        <p class="font-medium text-sm">Billing Info</p>
                    </a>
                    <a href="settings.php" class="bg-accent p-4 flex flex-col items-center justify-center hover:shadow-neon transition rounded-lg">
                        <span class="material-symbols-outlined text-warning text-3xl mb-1">lock</span>
                        <p class="font-medium text-sm">Security Settings</p>
                    </a>
                </div>
            </div>
        </main>

    </div> <script>
        const userName = 'John Doe';
        const visibleSidebar = document.getElementById('customer-sidebar');
        const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');

        function updateUserInfo() {
            document.getElementById('customer-user-avatar-sidebar').textContent = userName.charAt(0);
            document.getElementById('customer-main-avatar').textContent = userName.split(' ').map(n => n[0]).join('');
        }
        updateUserInfo();

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
        
        // Active link logic
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            if (!link.classList.contains('logout-link')) { // Exclude logout
                // Special case: Profile link in the footer should be active on profile.php
                if (link.href.includes('profile.php')) { 
                    link.classList.add('active');
                }
            }
            link.addEventListener('click', () => {
                if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
            });
        });
    </script>
</body>
</html>