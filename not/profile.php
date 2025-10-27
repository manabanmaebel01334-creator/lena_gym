<?php
// profile.php - Profile Page
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
                    },
                    animation: {
                        'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                        'float': 'float 3s ease-in-out infinite',
                        'spin-slow': 'spin 3s linear infinite',
                        'slide-in': 'slide-in 0.5s ease-out',
                        'bounce-in': 'bounce-in 0.6s ease-out',
                        'fade-slide': 'fade-slide 0.4s ease-out'
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
            -webkit-overflow-scrolling: touch;
            display: none;
        }
        .sidebar.open {
            transform: translateX(0);
            box-shadow: 5px 0 20px rgba(var(--primary-rgb), 0.3);
            animation: bounce-in 0.6s ease-out;
            display: block !important;
        }
        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
                width: 280px;
                height: auto;
                z-index: auto;
                padding: 1rem;
                display: block !important;
            }
            .main-content {
                margin-left: 280px;
            }
        }
        .sidebar a {
            transition: all 0.3s ease;
            color: rgb(var(--text-muted-rgb));
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            cursor: pointer;
            touch-action: manipulation;
        }
        .sidebar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 23, 68, 0.1), transparent);
            transition: left 0.5s;
        }
        .sidebar a:hover::before, .sidebar a.active::before {
            left: 100%;
        }
        .sidebar a span.material-symbols-outlined {
            color: rgb(var(--text-muted-rgb));
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.2);
            border-left: 4px solid var(--primary);
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.3);
            transform: translateX(5px);
        }
        .sidebar a.active span.material-symbols-outlined {
            color: var(--primary);
            animation: pulse-glow 1s ease-in-out infinite;
        }
        .sidebar h1 {
            color: rgb(var(--text-rgb));
            text-shadow: 0 0 10px rgba(255, 23, 68, 0.5);
        }
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 23, 68, 0.05), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }
        .card:hover::before {
            opacity: 1;
            transform: rotate(45deg) translateX(20px) translateY(20px);
        }
        .card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.2), inset 0 0 10px rgba(var(--primary-rgb), 0.1);
            border-color: var(--primary);
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #ff4444);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 2rem;
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
            cursor: pointer;
            touch-action: manipulation;
        }
        .user-avatar.sidebar-avatar {
            width: 32px;
            height: 32px;
            font-size: 1rem;
            animation: none;
        }
        .user-avatar:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.8);
        }
        .top-avatar {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
            animation: none;
        }
        .top-avatar:hover {
            transform: scale(1.05) rotate(360deg);
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.6);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        .logo-image {
            width: 50px;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            animation: bounce 2s infinite, spin-slow 20s linear infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-5px); }
            60% { transform: translateY(-3px); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px var(--primary); }
            50% { box-shadow: 0 0 20px var(--primary); }
        }
        .welcome-hero {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.2), rgba(255, 68, 68, 0.1));
            border: 1px solid rgba(var(--primary-rgb), 0.4);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out;
            box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.3);
            position: relative;
            overflow: hidden;
        }
        .welcome-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 23, 68, 0.1) 50%, transparent 70%);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        button, a[href] {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            touch-action: manipulation;
        }
        button::before, a[href]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        button:hover::before, a[href]:hover::before {
            left: 100%;
        }
        .bg-primary {
            background: var(--primary) !important;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.4);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
        .bg-primary:hover {
            background: #ff3366 !important;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.6), var(--neon-button);
        }
        .bg-accent {
            background: rgb(var(--accent-rgb)) !important;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
        }
        .bg-accent:hover {
            background: rgba(var(--primary-rgb), 0.1) !important;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.2);
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(var(--accent-rgb), 0.5);
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        .progress-fill {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
            border-radius: 4px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(var(--accent-rgb), 0.5);
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: var(--primary);
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .editable-field {
            background: rgba(var(--card-rgb), 0.5);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            color: var(--text);
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .editable-field:focus {
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
            transform: scale(1.02);
        }
        .profile-pic-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            z-index: 10;
            touch-action: manipulation;
        }
        .quick-access-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: rgba(var(--accent-rgb), 0.2);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            touch-action: manipulation;
        }
        .quick-access-btn:hover {
            background: rgba(var(--primary-rgb), 0.2);
            color: var(--primary);
            transform: translateX(5px);
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(var(--text-muted-rgb), 0.3);
        }
        th {
            background: rgba(var(--accent-rgb), 0.3);
            font-weight: 500;
        }
        /* Mobile adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 0.75rem;
            }
            .top-bar {
                flex-direction: column;
                gap: 0.5rem;
            }
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            .sidebar a {
                padding: 0.75rem;
                min-height: 44px;
                display: flex;
                align-items: center;
            }
            .quick-access-btn {
                padding: 0.75rem;
                font-size: 0.875rem;
                min-height: 44px;
                display: flex;
                align-items: center;
            }
            button {
                min-height: 44px;
                padding: 0.75rem;
            }
        }
        @media (min-width: 769px) {
            .quick-access-btn {
                padding: 1rem;
            }
        }
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes fade-slide {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .admin-only { display: none; }
        .customer-only { display: none; }
        .admin .admin-only { display: block; }
        .customer .customer-only { display: block; }
        .admin .customer-only { display: none; }
        .customer .admin-only { display: none; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <aside id="admin-sidebar" class="sidebar fixed inset-y-0 left-0 z-50 p-4 overflow-y-auto admin-only">
        <div class="flex items-center gap-3 mb-8">
            <img src="logo.png" alt="Lena Gym Logo" class="logo-image">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>
        <nav class="space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="user.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">groups</span>
                <span>User Management</span>
            </a>
            <a href="booking.php" class="flex items-center gap-3 rounded-lg active">
                <span class="material-symbols-outlined">calendar_month</span>
                <span>Service Booking & Calendar</span>
            </a>
            <a href="billing.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">payment</span>
                <span>Transaction & Billing</span>
            </a>
            <a href="reports.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">bar_chart</span>
                <span>Reports & Analytics</span>
            </a>
            <a href="settings.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="bottom-section pt-4 border-t border-text-muted/30 space-y-1">
            <a href="profile.php" class="flex items-center gap-3 rounded-lg active">
                <div class="user-avatar sidebar-avatar" id="admin-user-avatar">L</div>
                <span>Account</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-lg">
                <span class="material-symbols-outlined">help</span>
                <span>Help</span>
            </a>
        </div>
    </aside>

    <!-- Customer Sidebar -->
    <aside id="customer-sidebar" class="sidebar fixed inset-y-0 left-0 z-50 p-4 overflow-y-auto customer-only">
        <div class="flex items-center gap-3 mb-8">
            <img src="logo.png" alt="Lena Gym Logo" class="logo-image">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>
        <nav class="space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="user.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">person</span>
                <span>User Management</span>
            </a>
            <a href="booking.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">calendar_month</span>
                <span>Service Booking & Calendar</span>
            </a>
            <a href="billing.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">payment</span>
                <span>Transaction & Billing</span>
            </a>
            <a href="reports.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">bar_chart</span>
                <span>Reports & Analytics</span>
            </a>
            <a href="settings.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">logout</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="mt-8 pt-8 border-t border-text-muted/30 space-y-2">
            <a href="profile.php" class="flex items-center gap-3 p-3 rounded-lg active">
                <div class="user-avatar sidebar-avatar" id="customer-user-avatar">U</div>
                <span>Profile</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">help</span>
                <span>Help</span>
            </a>
        </div>
    </aside>

    <!-- Top Bar (shared for both) -->
    <header class="top-bar flex items-center justify-between mb-4 p-3 bg-accent/20 rounded-lg">
        <div class="flex items-center gap-3">
            <button id="mobile-toggle" class="md:hidden p-2 rounded-lg bg-accent text-text">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-xl font-bold">Profile</h1>
        </div>
        <div class="flex items-center gap-2">
            <div class="user-avatar top-avatar" id="top-avatar">L</div>
            <p id="top-user-name" class="text-sm font-medium">Lena Admin</p>
        </div>
    </header>

    <!-- Customer Profile Content -->
    <main id="customer-main" class="main-content min-h-screen p-6 customer-only profile-grid grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Header -->
        <section class="card p-6 col-span-full">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-4 relative">
                <div class="relative">
                    <div class="user-avatar" id="profile-pic">J</div>
                    <label class="profile-pic-upload material-symbols-outlined" for="pic-upload">edit</label>
                    <input type="file" id="pic-upload" class="hidden" accept="image/*" onchange="handlePicUpload(event)">
                </div>
                <div class="text-center md:text-left flex-1">
                    <h2 id="full-name" class="text-2xl font-bold mb-1">John Doe</h2>
                    <p class="text-primary mb-1">Basic Member</p>
                    <p class="text-text-muted mb-1">Member ID: LG-001</p>
                    <p class="text-text-muted">Joined: Jan 15, 2024 | Expires: Jan 15, 2025</p>
                </div>
                <button id="edit-info-btn" class="bg-primary px-4 py-2 rounded-full font-medium">Edit Info</button>
            </div>
        </section>

        <!-- Personal Information -->
        <section class="card p-6">
            <h3 class="text-xl font-bold mb-4">Personal Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Birthdate</label>
                    <input type="date" id="birthdate" class="editable-field w-full" value="1990-05-20" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Gender</label>
                    <select id="gender" class="editable-field w-full" disabled>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Contact Number</label>
                    <input type="tel" id="phone" class="editable-field w-full" value="+63 912 345 6789" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" class="editable-field w-full" value="john.doe@example.com" disabled>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Address</label>
                    <textarea id="address" class="editable-field w-full" rows="3" disabled>Bocaue, Bulacan</textarea>
                </div>
            </div>
        </section>

        <!-- Fitness Information -->
        <section class="card p-6">
            <h3 class="text-xl font-bold mb-4">Fitness Information</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Weight (kg)</label>
                        <input type="number" id="weight" class="editable-field w-full" value="80" step="0.1" onchange="calculateBMI()" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Height (m)</label>
                        <input type="number" id="height" class="editable-field w-full" value="1.75" step="0.01" onchange="calculateBMI()" disabled>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">BMI</label>
                    <p id="bmi" class="editable-field p-2">26.1 (Overweight)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Fitness Goal</label>
                    <select id="goal" class="editable-field w-full" disabled>
                        <option>Lose Weight</option>
                        <option>Build Muscle</option>
                        <option>Maintain Health</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Assigned Trainer</label>
                    <input type="text" id="trainer" class="editable-field w-full" value="Coach Anna" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Preferred Schedule</label>
                    <input type="text" id="schedule" class="editable-field w-full" value="Mon, Wed, Fri 6PM" disabled>
                </div>
            </div>
        </section>

        <!-- Account Settings -->
        <section class="card p-6 col-span-full">
            <h3 class="text-xl font-bold mb-4">Account Settings</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium mb-2">Change Password</h4>
                    <input type="password" placeholder="Current Password" class="editable-field w-full mb-2">
                    <input type="password" placeholder="New Password" class="editable-field w-full mb-2">
                    <input type="password" placeholder="Confirm New Password" class="editable-field w-full">
                    <button class="bg-warning px-4 py-2 rounded-full mt-2">Change Password</button>
                </div>
                <div class="space-y-2">
                    <h4 class="font-medium mb-2">Notification Preferences</h4>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="email-notif" class="toggle-switch" checked>
                        <span class="slider"></span>
                        Email Alerts
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="sms-notif" class="toggle-switch">
                        <span class="slider"></span>
                        SMS Alerts
                    </label>
                </div>
            </div>
        </section>

        <!-- Progress Summary -->
        <section class="card p-6 col-span-full">
            <h3 class="text-xl font-bold mb-4">Progress Summary</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-text-muted mb-2">Attendance Rate</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%;"></div>
                    </div>
                    <p class="text-xs text-success">75% (15/20 sessions)</p>
                </div>
                <div>
                    <p class="text-sm text-text-muted mb-2">Goal Progress</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 60%;"></div>
                    </div>
                    <p class="text-xs text-warning">60% to target</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Admin Profile Content -->
    <main id="admin-main" class="main-content min-h-screen p-6 admin-only">
        <!-- Admin Overview -->
        <section class="card p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="user-avatar" id="admin-profile-pic">L</div>
                <div>
                    <h2 id="admin-full-name" class="text-2xl font-bold">Lena Admin</h2>
                    <p class="text-primary">Gym Manager</p>
                    <p class="text-text-muted">Last Login: Oct 12, 2025 10:30 AM</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Admin Account Settings -->
            <section class="card p-6">
                <h3 class="text-xl font-bold mb-4">Admin Account Settings</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium mb-2">Change Password</h4>
                        <input type="password" id="current-password" placeholder="Current Password" class="editable-field w-full mb-2">
                        <input type="password" id="new-password" placeholder="New Password" class="editable-field w-full">
                        <button id="update-password-btn" class="bg-primary px-4 py-2 rounded-full mt-2 w-full">Update Password</button>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Update Email</h4>
                        <input type="email" id="admin-email" class="editable-field w-full" value="admin@lenagym.com">
                        <button id="update-email-btn" class="bg-primary px-4 py-2 rounded-full mt-2 w-full">Update Email</button>
                    </div>
                    <div class="pt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="dark-mode" class="toggle-switch" checked>
                            <span class="slider"></span>
                            Dark Mode
                        </label>
                    </div>
                </div>
            </section>

            <!-- System Management Quick Access -->
            <section class="card p-6">
                <h3 class="text-xl font-bold mb-4">System Management</h3>
                <div class="space-y-2">
                    <a href="user.php" class="quick-access-btn">
                        <span class="material-symbols-outlined">groups</span>
                        User Management
                    </a>
                    <a href="billing.php" class="quick-access-btn">
                        <span class="material-symbols-outlined">payment</span>
                        Transaction & Billing
                    </a>
                    <a href="reports.php" class="quick-access-btn">
                        <span class="material-symbols-outlined">bar_chart</span>
                        Reports & Analytics
                    </a>
                    <a href="booking.php" class="quick-access-btn">
                        <span class="material-symbols-outlined">calendar_month</span>
                        Service Booking & Calendar
                    </a>
                </div>
            </section>
        </div>

        <!-- Security Logs -->
        <section class="card p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Security Logs</h3>
                <button class="bg-success px-4 py-2 rounded-full" onclick="exportLogs()">Export CSV</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Activity</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="logs-table">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </section>

        <!-- Support Section -->
        <section class="card p-6 mt-6">
            <h3 class="text-xl font-bold mb-4">Support</h3>
            <form id="support-form" class="space-y-4">
                <textarea placeholder="Report an issue or contact developer..." class="editable-field w-full" rows="4"></textarea>
                <button type="submit" class="bg-primary px-6 py-3 rounded-full font-bold">Submit Report</button>
            </form>
        </section>
    </main>

    <script>
        // Role detection
        const role = localStorage.getItem('role') || 'admin';
        const userName = localStorage.getItem('userName') || 'Lena Admin';
        document.documentElement.classList.add(role);

        // Update user info
        function updateUserInfo() {
            document.getElementById('top-user-name').textContent = userName;
            document.getElementById('top-avatar').textContent = userName.charAt(0);
            if (role === 'admin') {
                document.getElementById('admin-full-name').textContent = userName;
                document.getElementById('admin-profile-pic').textContent = userName.charAt(0);
                document.getElementById('admin-user-avatar').textContent = userName.charAt(0);
            } else {
                document.getElementById('full-name').textContent = userName;
                document.getElementById('profile-pic').textContent = userName.charAt(0);
                document.getElementById('customer-user-avatar').textContent = userName.charAt(0);
            }
        }
        updateUserInfo();

        // Show/hide based on role - control sidebar display
        const adminSidebar = document.getElementById('admin-sidebar');
        const customerSidebar = document.getElementById('customer-sidebar');
        const adminMain = document.getElementById('admin-main');
        const customerMain = document.getElementById('customer-main');
        const visibleSidebar = role === 'admin' ? adminSidebar : customerSidebar;
        const hiddenSidebar = role === 'admin' ? customerSidebar : adminSidebar;
        visibleSidebar.style.display = 'block';
        hiddenSidebar.style.display = 'none';

        if (role === 'admin') {
            adminMain.style.display = 'block';
            customerMain.style.display = 'none';
        } else {
            customerMain.style.display = 'block';
            adminMain.style.display = 'none';
        }

        const isMobile = window.matchMedia('(max-width: 768px)').matches;

        // Mobile toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                if (isMobile) {
                    visibleSidebar.classList.toggle('open');
                    document.body.style.overflow = visibleSidebar.classList.contains('open') ? 'hidden' : 'auto';
                }
            });
        }

        // Top avatar toggle for mobile
        const topAvatar = document.getElementById('top-avatar');
        topAvatar.addEventListener('click', () => {
            if (isMobile) {
                visibleSidebar.classList.toggle('open');
                document.body.style.overflow = visibleSidebar.classList.contains('open') ? 'hidden' : 'auto';
            }
        });

        // Auto-close sidebar on mobile after clicking link
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile) {
                    visibleSidebar.classList.remove('open');
                    document.body.style.overflow = 'auto';
                }
                // Update active state for profile
                if (link.getAttribute('href') && link.getAttribute('href').includes('profile.php')) {
                    document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
                    link.classList.add('active');
                }
            });
        });

        // Close sidebar on outside click
        document.addEventListener('click', (e) => {
            if (isMobile && visibleSidebar && !visibleSidebar.contains(e.target) && !mobileToggle?.contains(e.target) && !topAvatar.contains(e.target)) {
                visibleSidebar.classList.remove('open');
                document.body.style.overflow = 'auto';
            }
        });

        // Customer-specific JS
        if (role !== 'admin') {
            let isEditing = false;
            const editBtn = document.getElementById('edit-info-btn');
            const saveBtn = document.createElement('button');
            saveBtn.textContent = 'Save Changes';
            saveBtn.className = 'bg-success px-4 py-2 rounded-full font-medium';

            editBtn.addEventListener('click', () => {
                isEditing = !isEditing;
                const fields = document.querySelectorAll('#customer-main .editable-field');
                fields.forEach(field => field.disabled = !isEditing);
                if (isEditing) {
                    editBtn.textContent = 'Cancel';
                    editBtn.classList.replace('bg-primary', 'bg-warning');
                    const header = editBtn.parentElement;
                    header.appendChild(saveBtn);
                } else {
                    editBtn.textContent = 'Edit Info';
                    editBtn.classList.replace('bg-warning', 'bg-primary');
                    if (saveBtn.parentElement) saveBtn.remove();
                }
            });

            saveBtn.addEventListener('click', () => {
                if (confirm('Save changes?')) {
                    alert('Changes saved!');
                    // Save to localStorage
                    localStorage.setItem('profileData', JSON.stringify({
                        birthdate: document.getElementById('birthdate').value,
                        gender: document.getElementById('gender').value,
                        phone: document.getElementById('phone').value,
                        email: document.getElementById('email').value,
                        address: document.getElementById('address').value,
                        weight: document.getElementById('weight').value,
                        height: document.getElementById('height').value,
                        goal: document.getElementById('goal').value,
                        trainer: document.getElementById('trainer').value,
                        schedule: document.getElementById('schedule').value
                    }));
                    isEditing = false;
                    editBtn.textContent = 'Edit Info';
                    editBtn.classList.replace('bg-warning', 'bg-primary');
                    if (saveBtn.parentElement) saveBtn.remove();
                    const fields = document.querySelectorAll('#customer-main .editable-field');
                    fields.forEach(field => field.disabled = true);
                }
            });

            // Load from localStorage
            const savedData = JSON.parse(localStorage.getItem('profileData')) || {};
            Object.keys(savedData).forEach(key => {
                const el = document.getElementById(key);
                if (el) el.value = savedData[key];
            });

            // BMI calculation
            function calculateBMI() {
                const weight = parseFloat(document.getElementById('weight').value);
                const height = parseFloat(document.getElementById('height').value);
                if (weight && height) {
                    const bmi = weight / (height * height);
                    const bmiEl = document.getElementById('bmi');
                    bmiEl.textContent = bmi.toFixed(1) + ' ';
                    if (bmi < 18.5) bmiEl.textContent += '(Underweight)';
                    else if (bmi < 25) bmiEl.textContent += '(Normal)';
                    else if (bmi < 30) bmiEl.textContent += '(Overweight)';
                    else bmiEl.textContent += '(Obese)';
                }
            }
            calculateBMI();

            // Pic upload mock - made more visible and functional
            function handlePicUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profile-pic').style.backgroundImage = `url(${e.target.result})`;
                        document.getElementById('profile-pic').textContent = '';
                    };
                    reader.readAsDataURL(file);
                    alert('Photo uploaded and preview updated!');
                }
            }

            // Toggles
            document.getElementById('email-notif').addEventListener('change', (e) => {
                alert(e.target.checked ? 'Email notifications enabled' : 'Email notifications disabled');
            });
            document.getElementById('sms-notif').addEventListener('change', (e) => {
                alert(e.target.checked ? 'SMS notifications enabled' : 'SMS notifications disabled');
            });

            // Password change mock
            document.querySelector('#customer-main .bg-warning').addEventListener('click', () => {
                if (confirm('Change password?')) alert('Password changed! (Mock)');
            });
        }

        // Admin-specific JS
        if (role === 'admin') {
            // Dark mode toggle
            document.getElementById('dark-mode').addEventListener('change', (e) => {
                document.documentElement.classList.toggle('dark', e.target.checked);
                localStorage.setItem('darkMode', e.target.checked);
                alert(e.target.checked ? 'Dark mode enabled' : 'Light mode enabled');
            });

            // Update Password with validation (no confirm as per screenshot)
            const updatePwBtn = document.getElementById('update-password-btn');
            updatePwBtn.addEventListener('click', () => {
                const currentPw = document.getElementById('current-password').value;
                const newPw = document.getElementById('new-password').value;

                if (!currentPw) {
                    alert('Please enter your current password.');
                    return;
                }
                if (!newPw || newPw.length < 6) {
                    alert('New password must be at least 6 characters long.');
                    return;
                }
                // Mock: Assume current password is always correct
                if (confirm('Are you sure you want to update the password?')) {
                    alert('Password updated successfully!');
                    // Clear fields
                    document.getElementById('current-password').value = '';
                    document.getElementById('new-password').value = '';
                    localStorage.setItem('userPasswordUpdated', Date.now());
                }
            });

            // Update Email with validation
            const updateEmailBtn = document.getElementById('update-email-btn');
            updateEmailBtn.addEventListener('click', () => {
                const email = document.getElementById('admin-email').value;
                if (!email || !email.includes('@') || !email.includes('.')) {
                    alert('Please enter a valid email address.');
                    return;
                }
                if (confirm('Are you sure you want to update the email?')) {
                    alert('Email updated successfully!');
                    localStorage.setItem('userEmail', email);
                }
            });

            // Security logs
            const logs = [
                { date: 'Oct 12, 2025 10:30 AM', activity: 'Login', details: 'Successful login from IP 192.168.1.1' },
                { date: 'Oct 11, 2025 3:45 PM', activity: 'Edit User', details: 'Updated John Doe profile' },
                { date: 'Oct 10, 2025 9:20 AM', activity: 'Delete Log', details: 'Removed old transaction record' }
            ];
            const tableBody = document.getElementById('logs-table');
            tableBody.innerHTML = logs.map(log => `
                <tr>
                    <td>${log.date}</td>
                    <td>${log.activity}</td>
                    <td>${log.details}</td>
                </tr>
            `).join('');

            // Export mock
            window.exportLogs = function() {
                alert('Logs exported to CSV! (Mock download)');
            }

            // Support form mock
            document.getElementById('support-form').addEventListener('submit', (e) => {
                e.preventDefault();
                const textarea = e.target.querySelector('textarea');
                if (textarea.value.trim()) {
                    alert('Report submitted! (Mock)');
                    textarea.value = '';
                } else {
                    alert('Please enter a message.');
                }
            });

            // Pic upload for admin profile (if needed)
            const adminPicUpload = document.createElement('input');
            adminPicUpload.type = 'file';
            adminPicUpload.accept = 'image/*';
            adminPicUpload.onchange = function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('admin-profile-pic').style.backgroundImage = `url(${e.target.result})`;
                        document.getElementById('admin-profile-pic').textContent = '';
                    };
                    reader.readAsDataURL(file);
                    alert('Admin photo updated!');
                }
            };
            document.getElementById('admin-profile-pic').addEventListener('click', () => adminPicUpload.click());
        }

        // Common: Vibration on mobile interactions
        if (navigator.vibrate) {
            document.querySelectorAll('button, a[href], .user-avatar, .profile-pic-upload').forEach(el => {
                el.addEventListener('click', () => navigator.vibrate(50));
            });
        }

        // Highlight active menu (for profile)
        const profileLinks = visibleSidebar.querySelectorAll('a[href*="profile.php"]');
        profileLinks.forEach(link => link.classList.add('active'));
    </script>
</body>
</html>