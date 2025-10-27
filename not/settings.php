<?php
// settings.php - Settings page
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
            /* Mobile: Hidden off-screen by default */
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
        }
        .sidebar.open {
            transform: translateX(0);
            box-shadow: 5px 0 20px rgba(var(--primary-rgb), 0.3);
            animation: bounce-in 0.6s ease-out;
        }
        /* Desktop: Always visible */
        @media (min-width: 769px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
                width: 280px;
                height: auto;
                z-index: auto;
                padding: 1rem;
            }
            .main-content {
                margin-left: 280px;
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
        .setting-item {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .setting-item:hover {
            background: rgba(var(--primary-rgb), 0.05);
            border-left-color: var(--primary);
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.1);
            transform: translateX(5px);
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
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }
        .user-avatar:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.8);
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
        /* Button Styles - Enhanced Neon */
        button, a[href] {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
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
        /* Notification dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(var(--card-rgb), 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 12px;
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) scale(0.95);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.2);
        }
        .notification-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            animation: slide-in 0.3s ease-out;
        }
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        .notification-item:hover {
            background: rgba(var(--primary-rgb), 0.05);
            transform: translateX(5px);
        }
        .notification-item.unread {
            background: rgba(var(--primary-rgb), 0.1);
            box-shadow: inset 0 0 5px rgba(var(--primary-rgb), 0.2);
            border-left: 3px solid var(--primary);
        }
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .bottom-section {
            padding-top: 1rem;
            border-top: 1px solid rgba(var(--text-muted-rgb), 0.3);
            margin-top: 1rem;
        }
        .bottom-section a {
            color: rgb(var(--text-muted-rgb));
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }
        .bottom-section a:hover {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.1);
            transform: translateX(5px);
        }
        .bottom-section .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }
        /* Mobile adjustments - Enhanced for better top bar layout */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 0.75rem;
                animation: fade-slide 0.4s ease-out;
            }
            .top-bar {
                padding: 0.75rem;
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
                justify-content: flex-start;
                animation: slide-in 0.5s ease-out;
            }
            .top-bar .flex.items-center.gap-4 {
                order: 1;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                gap: 0.25rem;
            }
            .top-bar h1 {
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                flex: 1;
                margin: 0;
                text-align: left;
                padding-left: 0.5rem;
            }
            .top-bar-right {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
                width: 100%;
                order: 2;
            }
            .top-bar-user {
                order: 1;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-shrink: 0;
                padding: 0.5rem 0;
                background: rgba(var(--accent-rgb), 0.3);
                border-radius: 8px;
                animation: fade-slide 0.4s ease-out;
            }
            .top-bar-user .flex.items-center.gap-2 {
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
            }
            .top-bar-user p {
                margin: 0;
                text-align: left;
            }
            .search-container {
                order: 2;
                flex: 1;
                max-width: none;
                width: 100%;
                animation: fade-slide 0.4s ease-out 0.1s both;
            }
            .top-bar-buttons {
                order: 3;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: flex-end;
                flex-wrap: nowrap;
                width: auto;
                flex-shrink: 0;
                animation: fade-slide 0.4s ease-out 0.2s both;
            }
            .top-bar-buttons button {
                flex: none;
                padding: 0.5rem;
                font-size: 0.875rem;
                min-height: 44px;
                min-width: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            .top-bar-buttons button:hover {
                transform: scale(1.05) rotate(5deg);
                animation: bounce-in 0.3s ease-out;
            }
            .notification-toggle {
                padding: 0.5rem;
                min-width: 44px;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .welcome-hero {
                padding: 1.5rem 1rem;
                margin-bottom: 1.5rem;
                border-radius: 12px;
                animation: bounce-in 0.6s ease-out;
            }
            .welcome-hero h2 {
                font-size: 1.75rem;
            }
            .notification-dropdown {
                position: fixed;
                top: 0;
                right: 0;
                width: 100vw;
                height: 100vh;
                max-height: none;
                border-radius: 0;
                transform: translateX(100%);
                backdrop-filter: blur(10px);
                animation: slide-in 0.3s ease-out;
            }
            .notification-dropdown.open {
                transform: translateX(0);
                animation: bounce-in 0.4s ease-out;
            }
            .search-input {
                min-height: 44px;
                width: 100%;
                padding: 0.75rem 1rem;
                transition: all 0.3s ease;
            }
            .search-input:focus {
                transform: scale(1.02);
                box-shadow: var(--neon-glow);
            }
            .setting-item {
                flex-direction: column !important;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem;
                animation: fade-slide 0.4s ease-out;
            }
            .setting-item .flex.items-center.gap-3 {
                width: 100%;
                flex-wrap: wrap;
                gap: 1rem;
            }
            .setting-item .text-right {
                align-self: flex-end;
                width: 100%;
                text-align: right;
            }
            .setting-item .text-right span {
                padding: 0.25rem 0.75rem;
                font-size: 0.75rem;
                transition: all 0.3s ease;
            }
            .setting-item .text-right span:hover {
                transform: scale(1.1);
                animation: pulse-glow 0.5s ease-out;
            }
            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.875rem;
                animation: float 2s ease-in-out infinite;
            }
            button, .p-2, .p-3, .p-4, a[href] {
                min-height: 44px;
                min-width: 44px;
                padding: 0.75rem;
                font-size: 0.875rem;
                transition: all 0.3s ease;
            }
            button:hover, a[href]:hover {
                transform: translateY(-2px) scale(1.05);
                animation: bounce-in 0.3s ease-out;
            }
            .logo-image {
                width: 40px;
                animation: bounce 1.5s infinite;
            }
            .sidebar h1 {
                font-size: 1.125rem;
            }
            .sidebar a {
                padding: 1rem;
                font-size: 0.875rem;
                justify-content: flex-start;
                animation: fade-slide 0.4s ease-out;
            }
            .sidebar a:hover {
                transform: translateX(10px) scale(1.02);
            }
            .sidebar a span.material-symbols-outlined {
                font-size: 1.5rem;
                min-width: 24px;
            }
            .bottom-section a {
                margin-top: 0.5rem;
                padding: 1rem;
                animation: fade-slide 0.4s ease-out;
            }
            .activity-name {
                margin-bottom: 0.25rem;
                font-size: 1rem;
            }
            .grid-cols-3 {
                grid-template-columns: 1fr !important;
            }
            .lg\:col-span-2 {
                grid-column: span 1 !important;
            }
            /* Fix for top bar toggle buttons (hamburger menu) alignment */
            .top-bar [id*="toggle"] {
                padding: 0.5rem !important;
                min-height: 44px !important;
                min-width: 44px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                box-shadow: none !important;
                transition: all 0.3s ease;
            }
            .top-bar [id*="toggle"]:hover {
                transform: rotate(90deg) scale(1.1);
                animation: spin-slow 0.5s ease-out;
            }
            /* Specific fix for top bar buttons alignment in mobile */
            .top-bar-buttons .bg-primary {
                padding: 0.5rem !important;
                min-width: 44px !important;
                min-height: 44px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                animation: pulse-glow 2s infinite;
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
        /* Hide/show classes */
        .admin-only { display: none; }
        .customer-only { display: none; }
        .admin .admin-only { display: block; }
        .customer .customer-only { display: block; }
        .admin .customer-only { display: none; }
        .customer .admin-only { display: none; }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }
        .modal-content {
            background: rgba(var(--card-rgb), 0.95);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--neon);
            animation: bounce-in 0.5s ease-out;
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
        .close {
            color: var(--text-muted);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .close:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 8px;
            background: rgba(var(--card-rgb), 0.5);
            color: var(--text);
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: var(--neon-glow);
            transform: scale(1.02);
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn-cancel {
            background: rgba(var(--accent-rgb), 0.5);
            color: var(--text);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
        .btn-cancel:hover {
            background: rgba(var(--primary-rgb), 0.1);
            border-color: var(--primary);
        }
        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 1.5rem;
            }
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Sidebar -->
    <aside id="admin-sidebar" class="sidebar fixed inset-y-0 left-0 z-50 p-4 overflow-y-auto md:block hidden admin-only">
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
            <a href="booking.php" class="flex items-center gap-3 rounded-lg">
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
            <a href="settings.php" class="flex items-center gap-3 rounded-lg active">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-lg" onclick="logout()">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="bottom-section space-y-1">
            <a href="#" class="flex items-center gap-3" onclick="openProfile()">
                <div class="user-avatar" id="admin-user-avatar">A</div>
                <span>Account</span>
            </a>
            <a href="#" class="flex items-center gap-3">
                <span class="material-symbols-outlined">help</span>
                <span>Help</span>
            </a>
        </div>
    </aside>

    <!-- Customer Sidebar -->
    <aside id="customer-sidebar" class="sidebar fixed inset-y-0 left-0 z-50 p-4 overflow-y-auto md:block hidden customer-only">
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
            <a href="settings.php" class="flex items-center gap-3 p-3 rounded-lg active">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span>Settings</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg" onclick="logout()">
                <span class="material-symbols-outlined text-2xl">logout</span>
                <span>Logout</span>
            </a>
        </nav>
        <div class="mt-8 pt-8 border-t border-text-muted/30 space-y-2">
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg" onclick="openProfile()">
                <div class="user-avatar" id="customer-user-avatar">U</div>
                <span>Profile</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg">
                <span class="material-symbols-outlined text-2xl">help</span>
                <span>Help</span>
            </a>
        </div>
    </aside>

    <!-- Admin Main Content -->
    <main id="admin-main" class="main-content min-h-screen p-6 admin-only">
        <!-- Top Bar Admin -->
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-admin" class="md:hidden p-2 rounded-lg bg-accent text-text shadow-neon">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Settings</h1>
            </div>
            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="admin-top-avatar">L</div>
                    <div id="admin-user-info">
                        <p class="text-sm font-medium">Lena Admin</p>
                        <p class="text-xs text-text-muted">Gym Owner</p>
                    </div>
                </div>
                <div class="search-container relative md:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-text-muted">search</span>
                    <input type="text" placeholder="Search settings..." class="search-input pl-10 pr-4 py-2 bg-card rounded-full text-sm w-full" onkeyup="searchSettings(this.value)">
                </div>
                <div class="top-bar-buttons flex items-center gap-2">
                    <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-2 shadow-neon" onclick="saveAllSettings()">
                        <span class="material-symbols-outlined">save</span>
                        Save All
                    </button>
                    <!-- Notification Bell Admin -->
                    <div class="relative">
                        <button id="notification-toggle-admin" class="notification-toggle p-3 rounded-full bg-accent text-text relative shadow-neon">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="notification-badge" id="admin-notification-badge">5</span>
                        </button>
                        <div id="notification-dropdown-admin" class="notification-dropdown">
                            <div class="p-4 border-b border-text-muted/30">
                                <h3 class="font-bold text-sm">Notifications</h3>
                            </div>
                            <div id="admin-notification-list" class="notification-list">
                                <!-- Dynamic notifications will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Welcome Section Admin -->
        <section class="welcome-hero">
            <h2 class="text-3xl font-bold mb-2" id="admin-welcome-name">Manage Settings, Admin!</h2>
            <p class="text-text-muted mb-4">Customize your gym's configuration and preferences.</p>
            <button class="bg-primary px-6 py-3 rounded-full font-bold text-sm hover:bg-red-600 transition-colors shadow-neon" onclick="quickSettingsOverview()">
                Quick Overview
            </button>
        </section>

        <!-- Dashboard Content Admin -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Settings Grid Admin -->
            <div class="lg:col-span-2">
                <div class="card p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Gym Settings</h2>
                        <a href="#" class="text-primary text-sm font-medium" onclick="viewAllSettings()">See All</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="setting-item p-4 rounded-lg bg-accent/20">
                            <p class="font-medium">Gym Name: Lena Gym Bocaue</p>
                            <p class="text-xs text-text-muted">Update gym branding</p>
                        </div>
                        <div class="setting-item p-4 rounded-lg bg-accent/20">
                            <p class="font-medium">Operating Hours: 6AM - 10PM</p>
                            <p class="text-xs text-text-muted">Set daily schedule</p>
                        </div>
                        <div class="setting-item p-4 rounded-lg bg-accent/20">
                            <p class="font-medium">Max Capacity: 100</p>
                            <p class="text-xs text-text-muted">Member limit per session</p>
                        </div>
                        <div class="setting-item p-4 rounded-lg bg-accent/20">
                            <p class="font-medium">Default Payment: GCash</p>
                            <p class="text-xs text-text-muted">Preferred method</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Control Admin -->
            <div>
                <div class="card p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Access Control</h2>
                        <a href="user.php" class="text-primary text-sm font-medium">View All</a>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Admin Users</p>
                            <p class="font-bold text-2xl">2</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Role Permissions</p>
                            <p class="font-bold text-success">Full</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Backup Schedule</p>
                            <p class="font-bold text-primary">Weekly</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Security Level</p>
                            <p class="font-bold text-warning">High</p>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences Admin -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Notifications</h2>
                        <a href="#" class="text-primary text-sm font-medium" onclick="manageNotifications()">Manage</a>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-text-muted mb-2">Email Alerts</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%;"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">Enabled</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted mb-2">SMS Alerts</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">Disabled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Changes Admin -->
        <div class="card p-6 mt-6">
            <h2 class="text-xl font-bold mb-6">Recent Changes</h2>
            <div class="space-y-4">
                <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-success rounded-full flex items-center justify-center text-white font-bold text-sm">E</div>
                        <div>
                            <p class="font-medium">Updated gym hours</p>
                            <p class="text-xs text-text-muted">2 hours ago</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-success/20 text-success text-xs rounded-full">Edit</span>
                </div>
                <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-warning rounded-full flex items-center justify-center text-white font-bold text-sm">N</div>
                        <div>
                            <p class="font-medium">Enabled email notifications</p>
                            <p class="text-xs text-text-muted">1 day ago</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-warning/20 text-warning text-xs rounded-full">Notify</span>
                </div>
                <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold text-sm">B</div>
                        <div>
                            <p class="font-medium">Set backup schedule</p>
                            <p class="text-xs text-text-muted">3 days ago</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-primary/20 text-primary text-xs rounded-full">Backup</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Customer Main Content -->
    <main id="customer-main" class="main-content min-h-screen p-6 customer-only">
        <!-- Top Bar Customer -->
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-customer" class="md:hidden p-2 rounded-lg bg-accent text-text">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Settings</h1>
            </div>
            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="customer-top-avatar">J</div>
                    <div id="customer-user-info">
                        <p class="text-sm font-medium">John Doe</p>
                        <p class="text-xs text-text-muted">Basic Member</p>
                    </div>
                </div>
                <div class="search-container relative md:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-text-muted">search</span>
                    <input type="text" placeholder="Search profile..." class="search-input pl-10 pr-4 py-2 bg-card rounded-full text-sm w-full" onkeyup="searchProfile(this.value)">
                </div>
                <div class="top-bar-buttons flex items-center gap-2">
                    <button class="bg-primary rounded-full font-medium flex items-center gap-1 shadow-neon md:px-4 md:py-2 md:text-sm md:gap-2" onclick="updateProfile()">
                        <span class="material-symbols-outlined">edit</span>
                        <span class="hidden md:inline">Update Profile</span>
                        <span class="md:hidden">Update</span>
                    </button>
                    <!-- Notification Bell Customer -->
                    <div class="relative">
                        <button id="notification-toggle-customer" class="notification-toggle p-2 md:p-3 rounded-full bg-accent text-text relative shadow-neon">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="notification-badge" id="customer-notification-badge">3</span>
                        </button>
                        <div id="notification-dropdown-customer" class="notification-dropdown">
                            <div class="p-4 border-b border-text-muted/30">
                                <h3 class="font-bold text-sm">Notifications</h3>
                            </div>
                            <div id="customer-notification-list" class="notification-list">
                                <!-- Dynamic notifications will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Welcome Section Customer -->
        <section class="welcome-hero">
            <h2 class="text-3xl font-bold mb-2" id="customer-welcome-name">Update Profile, John!</h2>
            <p class="text-text-muted mb-4">Personalize your account and preferences.</p>
            <button class="bg-primary px-6 py-3 rounded-full font-bold text-sm hover:bg-red-600 transition-colors" onclick="quickProfileOverview()">
                Quick Overview
            </button>
        </section>

        <!-- Dashboard Content Customer -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Overview Customer -->
            <div class="lg:col-span-2">
                <div class="card p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Profile Info</h2>
                        <a href="#" class="text-primary text-sm font-medium" onclick="viewFullProfile()">View All</a>
                    </div>
                    <div class="space-y-4">
                        <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold text-sm">N</div>
                                <div>
                                    <p class="font-medium">Name: John Doe</p>
                                    <p class="text-xs text-text-muted">Full name</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-primary/20 text-primary text-xs rounded-full">Edit</span>
                        </div>
                        <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-success rounded-full flex items-center justify-center text-white font-bold text-sm">E</div>
                                <div>
                                    <p class="font-medium">Email: john.doe@example.com</p>
                                    <p class="text-xs text-text-muted">Contact email</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-success/20 text-success text-xs rounded-full">Verified</span>
                        </div>
                        <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-warning rounded-full flex items-center justify-center text-white font-bold text-sm">P</div>
                                <div>
                                    <p class="font-medium">Phone: +63 912 345 6789</p>
                                    <p class="text-xs text-text-muted">Mobile number</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-warning/20 text-warning text-xs rounded-full">Update</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fitness Goals Customer -->
            <div>
                <div class="card p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Fitness Goals</h2>
                        <a href="#" class="text-primary text-sm font-medium" onclick="manageGoals()">Manage</a>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Goal</p>
                            <p class="font-bold text-primary">Weight Loss</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Target Weight</p>
                            <p class="font-bold text-warning">75kg</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Sessions/Week</p>
                            <p class="font-bold text-success">4</p>
                        </div>
                        <div class="bg-accent p-4 rounded-lg">
                            <p class="text-xs text-text-muted mb-1">Progress</p>
                            <p class="font-bold text-success">70%</p>
                        </div>
                    </div>
                </div>

                <!-- Preferences Customer -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Preferences</h2>
                        <a href="#" class="text-primary text-sm font-medium" onclick="managePreferences()">Details</a>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-text-muted mb-2">Class Reminders</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%;"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">Enabled</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted mb-2">Progress Emails</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;"></div>
                            </div>
                            <p class="text-xs text-text-muted mt-1">Disabled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Security Customer -->
        <div class="card p-6 mt-6">
            <h2 class="text-xl font-bold mb-6">Account Security</h2>
            <div class="space-y-4">
                <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-success rounded-full flex items-center justify-center text-white font-bold text-sm">P</div>
                        <div>
                            <p class="font-medium">Password Strength: Strong</p>
                            <p class="text-xs text-text-muted">Last changed 2 months ago</p>
                        </div>
                    </div>
                    <button class="bg-accent px-3 py-1 rounded text-xs hover:bg-primary transition-colors">Change</button>
                </div>
                <div class="setting-item flex items-center justify-between p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-warning rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                        <div>
                            <p class="font-medium">2FA Status: Disabled</p>
                            <p class="text-xs text-text-muted">Enable for extra security</p>
                        </div>
                    </div>
                    <button class="bg-warning px-3 py-1 rounded text-xs hover:bg-primary transition-colors">Enable</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Settings Modal -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <span id="close-settings-btn" class="close">&times;</span>
            <h2 id="modal-title" class="text-2xl font-bold mb-4 text-text">Settings</h2>
            <div id="modal-body">
                <!-- Dynamic content loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Determine role from localStorage
        const role = localStorage.getItem('role') || 'admin';
        const userName = localStorage.getItem('userName') || 'Lena Admin';
        document.documentElement.classList.add(role);

        // Dynamic user info updates
        function updateUserInfo() {
            // Admin updates
            const adminWelcome = document.getElementById('admin-welcome-name');
            if (adminWelcome) adminWelcome.textContent = `Manage Settings, ${userName}!`;
            const adminUserInfo = document.getElementById('admin-user-info');
            if (adminUserInfo) adminUserInfo.innerHTML = `<p class="text-sm font-medium">${userName}</p><p class="text-xs text-text-muted">Gym Owner</p>`;
            const adminTopAvatar = document.getElementById('admin-top-avatar');
            if (adminTopAvatar) adminTopAvatar.textContent = userName.charAt(0);
            const adminUserAvatar = document.getElementById('admin-user-avatar');
            if (adminUserAvatar) adminUserAvatar.textContent = userName.charAt(0);

            // Customer updates
            const customerWelcome = document.getElementById('customer-welcome-name');
            if (customerWelcome) customerWelcome.textContent = `Update Profile, ${userName}!`;
            const customerUserInfo = document.getElementById('customer-user-info');
            if (customerUserInfo) customerUserInfo.innerHTML = `<p class="text-sm font-medium">${userName}</p><p class="text-xs text-text-muted">Basic Member</p>`;
            const customerTopAvatar = document.getElementById('customer-top-avatar');
            if (customerTopAvatar) customerTopAvatar.textContent = userName.charAt(0);
            const customerUserAvatar = document.getElementById('customer-user-avatar');
            if (customerUserAvatar) customerUserAvatar.textContent = userName.charAt(0);
        }

        updateUserInfo();

        // Show/hide based on role
        const adminSidebar = document.getElementById('admin-sidebar');
        const customerSidebar = document.getElementById('customer-sidebar');
        const adminMain = document.getElementById('admin-main');
        const customerMain = document.getElementById('customer-main');

        if (role === 'admin') {
            adminSidebar.style.display = 'block';
            customerSidebar.style.display = 'none';
            adminMain.style.display = 'block';
            customerMain.style.display = 'none';
        } else {
            adminSidebar.style.display = 'none';
            customerSidebar.style.display = 'block';
            adminMain.style.display = 'none';
            customerMain.style.display = 'block';
        }

        const visibleSidebar = role === 'admin' ? adminSidebar : customerSidebar;

        // Highlight active menu item
        function highlightActiveMenu() {
            const currentPage = window.location.pathname.split('/').pop() || 'settings.php';
            const menuLinks = visibleSidebar.querySelectorAll('nav a[href]');
            menuLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        }
        highlightActiveMenu();

        // Notification system integrated with localStorage
        function loadNotifications() {
            const notifications = JSON.parse(localStorage.getItem('notifications')) || [];
            const filteredNotifications = notifications.filter(notif => notif.recipient === (role === 'admin' ? 'all' : 'customer'));
            const unreadCount = filteredNotifications.filter(notif => notif.unread).length;
            const notificationList = role === 'admin' ? document.getElementById('admin-notification-list') : document.getElementById('customer-notification-list');
            const badge = role === 'admin' ? document.getElementById('admin-notification-badge') : document.getElementById('customer-notification-badge');

            if (badge) badge.textContent = unreadCount || '';
            if (notificationList) {
                notificationList.innerHTML = filteredNotifications.map(notif => `
                    <div class="notification-item ${notif.unread ? 'unread' : ''}" onclick="markAsRead(${notif.id})">
                        <p class="font-medium text-sm">${notif.message}</p>
                        <p class="text-xs text-text-muted">${notif.date}</p>
                    </div>
                `).join('');
            }
        }

        function addNotification(type, message) {
            const newNotif = {
                id: Date.now(),
                type,
                message,
                date: new Date().toLocaleDateString(),
                unread: true,
                recipient: role === 'admin' ? 'all' : 'customer'
            };
            let notifications = JSON.parse(localStorage.getItem('notifications')) || [];
            notifications.unshift(newNotif);
            localStorage.setItem('notifications', JSON.stringify(notifications));
            loadNotifications();
        }

        function markAsRead(id) {
            let notifications = JSON.parse(localStorage.getItem('notifications')) || [];
            const notif = notifications.find(n => n.id === id);
            if (notif) {
                notif.unread = false;
                localStorage.setItem('notifications', JSON.stringify(notifications));
                loadNotifications();
            }
        }

        // Initialize notifications
        loadNotifications();

        // Utility functions
        function searchSettings(query) {
            // Simulate filtering
            addNotification('info', 'Settings filtered: ' + query);
            alert('Searching settings: ' + query);
        }

        function saveAllSettings() {
            // Simulate save
            addNotification('confirmation', 'All settings saved');
            alert('Saving all settings...');
        }

        function quickSettingsOverview() {
            openSettingsModal();
        }

        function viewAllSettings() {
            // Simulate view
            addNotification('info', 'All settings viewed');
            alert('Viewing all settings...');
        }

        function manageNotifications() {
            // Simulate manage
            addNotification('info', 'Notifications managed');
            alert('Managing notifications...');
        }

        // Customer functions
        function searchProfile(query) {
            // Simulate search
            addNotification('info', 'Profile searched: ' + query);
            alert('Searching profile: ' + query);
        }

        function updateProfile() {
            // Simulate update
            addNotification('confirmation', 'Profile updated');
            alert('Updating profile...');
        }

        function quickProfileOverview() {
            openSettingsModal();
        }

        function viewFullProfile() {
            // Simulate view
            addNotification('info', 'Full profile viewed');
            alert('Viewing full profile...');
        }

        function manageGoals() {
            // Simulate manage
            addNotification('info', 'Goals managed');
            alert('Managing goals...');
        }

        function managePreferences() {
            // Simulate manage
            addNotification('info', 'Preferences managed');
            alert('Managing preferences...');
        }

        // Modal functions
        function loadModalContent() {
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            if (role === 'admin') {
                modalTitle.textContent = 'Quick Gym Settings';
                modalBody.innerHTML = `
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">Gym Name</label>
                        <input type="text" value="Lena Gym Fitness" class="w-full p-3 bg-card/50 border border-primary/30 rounded-lg text-text focus:border-primary focus:shadow-neon-glow transition-all duration-300">
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">Default Payment Method</label>
                        <select class="w-full p-3 bg-card/50 border border-primary/30 rounded-lg text-text focus:border-primary focus:shadow-neon-glow transition-all duration-300">
                            <option>Cash</option>
                            <option>Card</option>
                            <option>GCash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">Email Notifications</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <button class="w-full bg-primary py-3 rounded-lg font-medium text-sm shadow-neon-button hover:bg-red-500 hover:shadow-neon transition-all duration-300 flex items-center justify-center gap-2 mt-4" onclick="saveSettings()">
                        <span class="material-symbols-outlined">save</span>
                        Save Changes
                    </button>
                `;
            } else {
                modalTitle.textContent = 'Quick Profile Settings';
                modalBody.innerHTML = `
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">Full Name</label>
                        <input type="text" value="${userName}" class="w-full p-3 bg-card/50 border border-primary/30 rounded-lg text-text focus:border-primary focus:shadow-neon-glow transition-all duration-300">
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">Preferred Plan</label>
                        <select class="w-full p-3 bg-card/50 border border-primary/30 rounded-lg text-text focus:border-primary focus:shadow-neon-glow transition-all duration-300">
                            <option>Basic</option>
                            <option>Premium</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium mb-2 text-text">SMS Notifications</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    <button class="w-full bg-primary py-3 rounded-lg font-medium text-sm shadow-neon-button hover:bg-red-500 hover:shadow-neon transition-all duration-300 flex items-center justify-center gap-2 mt-4" onclick="saveSettings()">
                        <span class="material-symbols-outlined">save</span>
                        Save Changes
                    </button>
                `;
            }
        }

        function openSettingsModal() {
            loadModalContent();
            const modal = document.getElementById('settingsModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            if (navigator.vibrate) navigator.vibrate(50);
        }

        function closeSettingsModal() {
            const modal = document.getElementById('settingsModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            if (navigator.vibrate) navigator.vibrate(50);
        }

        function saveSettings() {
            // Simulate save
            addNotification('confirmation', 'Settings saved successfully');
            alert('Settings saved successfully!');
            closeSettingsModal();
        }

        // Profile & logout functions
        function openProfile() {
            alert('Profile opened!');
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.clear();
                window.location.href = 'index.php';
            }
        }

        // Desktop toggle (role-specific) - handles mobile menu button in top-bar
        const desktopToggleAdmin = document.getElementById('desktop-toggle-admin');
        const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');
        if (role === 'admin' && desktopToggleAdmin) {
            desktopToggleAdmin.addEventListener('click', () => {
                visibleSidebar.classList.toggle('open');
                // Add vibration effect for mobile (if supported)
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            });
        } else if (role === 'customer' && desktopToggleCustomer) {
            desktopToggleCustomer.addEventListener('click', () => {
                visibleSidebar.classList.toggle('open');
                // Add vibration effect for mobile (if supported)
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            });
        }

        // Close sidebar on outside click (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && 
                !(desktopToggleAdmin && desktopToggleAdmin.contains(e.target)) && 
                !(desktopToggleCustomer && desktopToggleCustomer.contains(e.target))) {
                visibleSidebar.classList.remove('open');
            }
        });

        // Notification toggle (role-specific)
        let notificationToggle, notificationDropdown;
        if (role === 'admin') {
            notificationToggle = document.getElementById('notification-toggle-admin');
            notificationDropdown = document.getElementById('notification-dropdown-admin');
        } else {
            notificationToggle = document.getElementById('notification-toggle-customer');
            notificationDropdown = document.getElementById('notification-dropdown-customer');
        }
        if (notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('open');
                // Vibration feedback
                if (window.innerWidth < 769 && navigator.vibrate) {
                    navigator.vibrate(100);
                }
            });
        }

        // Close dropdown on outside click
        document.addEventListener('click', (e) => {
            if (notificationToggle && notificationDropdown && !notificationToggle.contains(e.target)) {
                notificationDropdown.classList.remove('open');
            }
        });

        // Mark as read simulation (now functional)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const item = e.target.closest('.notification-item');
                const id = parseInt(item.getAttribute('onclick').match(/markAsRead\((\d+)\)/)?.[1]);
                if (id) markAsRead(id);
            }
        });

        // Prevent body scroll when sidebar or notification is open on mobile
        function preventBodyScroll(open) {
            if (open) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'auto';
            }
        }

        if (visibleSidebar) {
            visibleSidebar.addEventListener('transitionend', () => {
                if (window.innerWidth < 769) {
                    preventBodyScroll(visibleSidebar.classList.contains('open'));
                }
            });
        }

        if (notificationDropdown) {
            notificationDropdown.addEventListener('transitionend', () => {
                if (window.innerWidth < 769 && !notificationDropdown.classList.contains('open')) {
                    preventBodyScroll(false);
                }
            });
        }

        // Add particle effect or additional JS animations if needed
        // For example, add a simple confetti on button click (optional)
        document.querySelectorAll('button.bg-primary').forEach(btn => {
            btn.addEventListener('click', () => {
                // Simple JS effect: create a glow pulse
                btn.style.animation = 'pulse-glow 0.5s ease-in-out';
                setTimeout(() => {
                    btn.style.animation = '';
                }, 500);
                // Vibration on click for mobile
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            });
        });

        // Enhanced mobile swipe gesture for sidebar close
        let startX = 0;
        let startY = 0;
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (window.innerWidth < 769 && visibleSidebar.classList.contains('open')) {
                const deltaX = e.touches[0].clientX - startX;
                const deltaY = Math.abs(e.touches[0].clientY - startY);
                if (deltaX < -50 && deltaY < 20) { // Swipe left to close
                    visibleSidebar.classList.remove('open');
                    if (navigator.vibrate) {
                        navigator.vibrate(50);
                    }
                }
            }
        }, { passive: true });

        // Event listeners for modal
        document.addEventListener('DOMContentLoaded', function() {
            const openBtnAdmin = document.querySelector('#admin-main [onclick*="openSettingsModal"]') || document.getElementById('settings-btn');
            const openBtnCustomer = document.querySelector('#customer-main [onclick*="openSettingsModal"]') || document.getElementById('customer-settings-btn');
            const closeBtn = document.getElementById('close-settings-btn');
            
            if (openBtnAdmin) {
                openBtnAdmin.addEventListener('click', openSettingsModal);
            }
            if (openBtnCustomer) {
                openBtnCustomer.addEventListener('click', openSettingsModal);
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', closeSettingsModal);
            }
            
            // Close on outside click
            const modal = document.getElementById('settingsModal');
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeSettingsModal();
                }
            });
            
            // Keyboard escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeSettingsModal();
                }
            });
        });
    </script>
</body>
</html>