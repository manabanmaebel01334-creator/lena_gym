<?php
// schedule.php
// Staff schedule dashboard with Calendar, assigned clients, and reminders

// 1. Start session to retrieve the logged-in user's ID
session_start();

// 2. Include config for DB connection and ensure the PDO object ($pdo) is available
// **IMPORTANT: The path is set to '../../config.php'. Adjust this if necessary.**
include_once('../../config.php');

// --- User/Session Setup (Minimal Initializations) ---
// Get User ID from Session. If session is not set (e.g., direct access or testing),
// it falls back to Trainer ID 101, a valid ID from your gymratDB.sql.
$staff_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 101;
$staff_name = "Staff Member (Default)"; // Initializing variables before DB fetch
$staff_role = "Trainer";
$avatar_initial = "S";
$debug_message = "";
// --------------------------

$assigned_sessions = [];
$sessions_today = 0;

// --- Helper Functions for Calendar Logic ---

/**
 * Generates an array of dates for the current week, starting from today.
 *
 * @param int $days_ahead The number of days to display, starting from today (0).
 * @return array An array of date strings and DateTime objects.
 */
function get_current_week_dates(int $days_ahead = 7): array {
    $dates = [];
    $today = new DateTime(); // Automatically uses current server date
    for ($i = 0; $i < $days_ahead; $i++) {
        $date = clone $today;
        $date->modify("+$i day");
        $dates[] = [
            'date_str' => $date->format('Y-m-d'), // For SQL comparison
            'display_day' => $date->format('l'), // Full Day Name (e.g., Monday)
            'display_date' => $date->format('F j, Y'), // Month Day, Year (e.g., October 20, 2025)
            'is_today' => ($i === 0),
        ];
    }
    return $dates;
}

/**
 * Fetches the count of sessions for a specific trainer and a list of dates.
 *
 * @param PDO $pdo The database connection object.
 * @param int $trainer_id The ID of the trainer.
 * @param array $dates The array of date information from get_current_week_dates.
 * @return array An array with date_str as key and session count as value.
 */
function get_session_counts_for_dates(PDO $pdo, int $trainer_id, array $dates): array {
    $counts = [];
    $date_list = array_column($dates, 'date_str');
    
    if (empty($date_list)) {
        return $counts;
    }

    // Initialize counts for all dates to 0
    foreach ($date_list as $date_str) {
        $counts[$date_str] = 0;
    }

    // Create placeholders for the prepared statement
    $in_placeholders = implode(',', array_fill(0, count($date_list), '?'));

    try {
        // SQL to count bookings per date for the trainer
        $sql = "
            SELECT 
                DATE(b.start_time) AS session_date,
                COUNT(b.booking_id) AS session_count
            FROM bookings b
            WHERE b.trainer_id = ?
            AND DATE(b.start_time) IN ({$in_placeholders})
            GROUP BY session_date
        ";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind the trainer_id
        $bind_params = [$trainer_id];
        
        // Bind the date strings
        foreach ($date_list as $date_str) {
            $bind_params[] = $date_str;
        }

        $stmt->execute($bind_params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map results to the counts array
        foreach ($results as $row) {
            $counts[$row['session_date']] = (int)$row['session_count'];
        }

    } catch (PDOException $e) {
        global $debug_message;
        $debug_message .= " DATABASE COUNT ERROR: " . $e->getMessage();
    }
    
    return $counts;
}

/**
 * Function to generate a list of staff reminders based on the day's schedule.
 */
function get_staff_reminders(array $sessions): array {
    $reminders = [];
    $reminders[] = "Check equipment for all assigned sessions today.";

    if (empty($sessions)) {
        $reminders[] = "No client sessions scheduled. Focus on admin tasks.";
    } else {
        foreach ($sessions as $session) {
            // Note: $session['start_time'] is expected to be a full datetime string from DB
            $startTime = new DateTime($session['start_time']); 
            $time = $startTime->format('H:i A');
            $client = htmlspecialchars($session['client_name']);
            $service = htmlspecialchars($session['service_name']);
            $reminders[] = "Prepare for {$service} with {$client} at {$time}.";
        }
        if (count($sessions) > 2) {
            $reminders[] = "Remember to take a 15-minute break between sessions.";
        }
    }
    $reminders[] = "Complete your monthly report on client progress by EOD.";
    return $reminders;
}

// Function to generate a single table row HTML (used for initial load)
function generate_session_row(array $session): string {
    // Note: This function assumes $session contains 'booking_id', 'start_time', 'client_name', 'service_name', 'status' 
    
    // --- START: New additions to include booking_id and action button ---
    $booking_id = htmlspecialchars($session['booking_id']); 
    $startTime = new DateTime($session['start_time']);
    $time = $startTime->format('H:i A');
    
    $status_class = match ($session['status']) {
        'Confirmed' => 'text-primary',
        'Completed' => 'text-success',
        'Cancelled' => 'text-error',
        default => 'text-warning', // Assuming 'Pending'
    };
    
    $client_name = htmlspecialchars($session['client_name']);
    $service_name = htmlspecialchars($session['service_name']);
    $status = htmlspecialchars($session['status']);

    // Generate the Action Cell HTML
    $action_cell = "";
    // Only show the cancel button if the session hasn't happened or hasn't been cancelled
    if ($session['status'] === 'Confirmed' || $session['status'] === 'Pending') {
        $action_cell = "
            <button type='button' data-id='{$booking_id}' class='btn-cancel-session px-3 py-1 text-xs rounded-full bg-error/20 text-error hover:bg-error/40 transition-colors flex items-center gap-1 leading-none'>
                <span class='material-symbols-outlined text-base leading-none'>cancel</span> Cancel
            </button>";
    } else {
        // If status is Completed/Cancelled, show a dash
        $action_cell = "—";
    }
    // --- END: New additions ---

    // NOTE: Added data-booking-id to the <tr> for easy JS targeting
    return "<tr class='border-b border-white/5 data-row' data-booking-id='{$booking_id}'>
        <td class='py-2'>{$client_name}</td>
        <td class='py-2'>{$time}</td>
        <td class='py-2'>{$service_name}</td>
        <td class='py-2'>
            <span class='session-status font-semibold {$status_class}'>{$status}</span>
        </td>
        <td class='py-2 action-cell'>{$action_cell}</td>
    </tr>";
}


// Set Timezone and get dates for the next 7 days
date_default_timezone_set('Asia/Manila'); 
$current_week_dates = get_current_week_dates(7);
$today_date = $current_week_dates[0]['date_str']; // The current date in Y-m-d format
$sessions_by_date = []; // Will store session counts for the week

// --- Database Connection and User Details/Schedule Fetching ---
$available_clients = [];
$available_services = [];
if (!isset($pdo)) {
    $debug_message = "FATAL ERROR: PDO connection object (\$pdo) is not available. Check the include path for config.php.";
} else {
    try {
        // 1. Fetch staff name and role based on session ID
        $user_sql = "SELECT name, role FROM users WHERE id = :staff_id AND role IN ('trainer', 'admin')";
        $user_stmt = $pdo->prepare($user_sql);
        $user_stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $user_stmt->execute();
        $staff_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff_user) {
            // Update display variables with real data
            $staff_name = htmlspecialchars($staff_user['name']);
            $staff_role = htmlspecialchars($staff_user['role']);
            $avatar_initial = strtoupper(substr($staff_name, 0, 1));
            
            // Fetch session counts for the whole week
            $sessions_by_date = get_session_counts_for_dates($pdo, $staff_id, $current_week_dates);
            $sessions_today = $sessions_by_date[$today_date] ?? 0; // Get today's count

            // 2. Schedule Fetching Logic (Only fetch today's sessions for the table)
            // NOTE: ADDED b.booking_id to the SELECT list
            $sql = "
                SELECT 
                    b.booking_id,             /* ADDED BOOKING ID */
                    b.start_time,
                    u.name AS client_name,
                    s.name AS service_name,
                    b.status
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN services s ON b.service_id = s.service_id
                WHERE b.trainer_id = :trainer_id
                AND DATE(b.start_time) = :today_date
                ORDER BY b.start_time ASC;
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':trainer_id', $staff_id, PDO::PARAM_INT);
            $stmt->bindParam(':today_date', $today_date, PDO::PARAM_STR);
            $stmt->execute();

            $assigned_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debugging feedback
            if ($sessions_today > 0) {
                
            } else {
                $debug_message = "INFO: Trainer {$staff_name} (ID: {$staff_id}) is logged in, but there are no bookings for today ({$today_date}).";
            }
        } else {
             // If the ID is invalid or the role is wrong
             $debug_message = "SECURITY/ERROR: User ID {$staff_id} (from session) is not a valid trainer/staff. Please log in again.";
             $staff_name = "Unauthorized Access";
             $staff_role = "Unknown";
             $avatar_initial = "?";
        }

        // --- Data Fetch for Modal Dropdowns (After successful DB connection) ---
        // Fetch Clients (Users with role 'member')
        $client_sql = "SELECT id, name FROM users WHERE role = 'member' ORDER BY name ASC";
        $client_stmt = $pdo->query($client_sql);
        $available_clients = $client_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Services
        $service_sql = "SELECT service_id, name, is_class FROM services WHERE is_active = 1 ORDER BY name ASC";
        $service_stmt = $pdo->query($service_sql);
        $available_services = $service_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $debug_message = "DATABASE QUERY ERROR: " . $e->getMessage() . ". Check database table/column names.";
    }
}
// ----------------------------------------------------

$staff_reminders = get_staff_reminders($assigned_sessions);

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Schedule</title>

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
        /* CSS styles (omitted for brevity, assume previous styles are included) */
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

        /* MODIFIED: Changed selector to match dashboard.php's nav structure */
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
            /* FIX: Add internal padding to push content away from the edges */
            padding: 1.25rem; /* Equivalent to p-5 in Tailwind CSS */
            
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
        }

        /* --- MODAL STYLES --- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 100;
            display: none; /* Controlled by JS */
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.open {
            display: flex;
            opacity: 1;
        }
        .modal-content {
            background: var(--card);
            border: 1px solid rgba(var(--primary-rgb), 0.6);
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 40px rgba(var(--primary-rgb), 0.3);
            transform: scale(0.9);
            transition: transform 0.3s ease;
            overflow-y: auto;
            max-height: 90vh;
        }
        .modal-overlay.open .modal-content {
            transform: scale(1);
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .form-input, .form-select {
            /* REVERTED: Removed 'appearance: none' to use the default browser dropdown icon */
            width: 100%;
            padding: 0.75rem 1rem;
            /* FIX: Use !important to override browser defaults for background and color, ensuring transparent look */
            background-color: rgba(var(--sidebar-rgb), 0.4) !important; 
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 8px;
            color: var(--text) !important;
            transition: border-color 0.3s;
            line-height: normal; /* Fix for time/date inputs */
        }
        
        /* Ensures dropdown options are also dark */
        .form-select option {
            background-color: var(--card) !important;
            color: var(--text);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(var(--primary-rgb), 0.5);
        }
        
        /* FINAL FIXES FOR DATE/TIME INPUTS (to eliminate white background in icons) */
        input[type="date"].form-input,
        input[type="time"].form-input {
            color: var(--text) !important; /* Ensures the text is white */
        }
        /* Targets the internal calendar/clock icon to ensure it doesn't use a white background */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            background: none;
            color: var(--text); /* Makes the icon color white */
            filter: invert(1); /* Invert filter helps force the icon to contrast against dark input */
            cursor: pointer;
        }

        /* REMOVED: .select-wrapper and .select-wrapper::after CSS entirely to ensure only default icon is used */

        /* --- END MODAL STYLES --- */

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
        .placeholder-box { 
            min-height: 40px; 
            border: 1px dashed rgba(var(--primary-rgb), 0.12);
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            color: rgb(var(--text-muted-rgb));
            background: linear-gradient(180deg, rgba(var(--card-rgb),0.15), rgba(0,0,0,0.05));
            text-align: left; 
            padding: 0 1rem;
        }
        .data-row td {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

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
            transition: all .3s ease; 
        }
        .btn-add:hover { 
             box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.6);
        }

        @media (max-width: 768px) {
            .main-content { margin-left:0; padding: 1rem; }
        }

        /* --- CALENDAR STYLES (FIXED) --- */
        .calendar-day {
            padding:1rem;
            border-radius:10px;
            background:rgba(var(--primary-rgb),0.05); 
            border:1px solid rgba(var(--primary-rgb),0.1); 
            text-align:center;
            transition:.3s;
            cursor: pointer; 
        }
        .calendar-day:hover {
            background:rgba(var(--primary-rgb),0.15); 
            border-color:rgba(var(--primary-rgb),0.3); 
        }
        
        /* FIX: Remove default highlight from .today, so it only gets highlighted when it is also .active-day */
        .today {
            border-color: rgba(var(--primary-rgb), 0.1); 
            box-shadow: none; 
            background: rgba(var(--primary-rgb),0.05); 
        }
        
        /* Style for the currently selected day (any day clicked) */
        .active-day {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.3), rgba(var(--primary-rgb), 0.2)); 
            border-color: rgba(var(--primary-rgb), 0.8); 
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.6); 
        }
        
        /* Special style: If TODAY is the ACTIVE-DAY, apply the stronger original highlight. */
        .today.active-day { 
            background:rgba(var(--primary-rgb),0.3); 
            border-color:var(--primary); 
            box-shadow: var(--neon-glow);
        }
        /* --- END CALENDAR STYLES --- */
        
        .calendar-grid {
             display:grid;
             grid-template-columns: repeat(2, 1fr); 
             gap:8px;
        }
        @media (min-width: 640px) { 
             .calendar-grid { grid-template-columns: repeat(4, 1fr); }
        }
        @media (min-width: 769px) {
             .calendar-grid { grid-template-columns: repeat(7, 1fr); }
        }
    </style>
</head>

<body class="staff">

<div class="page-container">

    <aside id="staff-sidebar" class="sidebar">
        <div class="flex items-center gap-3 mb-8">
            <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
            <h1 class="text-xl font-bold">Lena Gym Fitness</h1>
        </div>

        <nav class="space-y-2">
            <a href="dashboard.php">
                <span class="material-symbols-outlined text-2xl">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="schedule.php" class="active">
                <span class="material-symbols-outlined text-2xl">event</span>
                <span>Schedule</span>
            </a>
            <a href="clients.php">
                <span class="material-symbols-outlined text-2xl">group</span>
                <span>Clients</span>
            </a>
            <a href="payments.php">
                <span class="material-symbols-outlined text-2xl">payments</span>
                <span>Payments</span>
            </a>
            <a href="reports.php">
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
    <main id="staff-main" class="main-content p-6 flex-grow space-y-6">

        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="mobile-toggle-staff" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Schedule Overview</h1>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="staff-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="staff-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($staff_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($staff_role); ?></p>
                    </div>
                </div>

                <button class="btn-add" id="open-schedule-modal-btn">
                    <span class="material-symbols-outlined">event</span>
                    <span class="hidden md:inline">Add New Schedule</span>
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
        
        <?php if (!empty($debug_message)): ?>
        <div class="p-3 bg-error/20 border border-error text-error rounded-lg text-sm font-semibold fade-up" style="--fade-delay: -0.1s;">
            <?php echo htmlspecialchars($debug_message); ?>
        </div>
        <?php endif; ?>
        
        <section class="card fade-up" style="--fade-delay: 0s;"> 
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="material-symbols-outlined">calendar_month</span> Upcoming Week
            </h2>
            <p class="tiny mb-4">Click a day to view its assigned sessions.</p>
            <div class="calendar-grid text-sm">
                <?php foreach ($current_week_dates as $day): 
                    $date_str = $day['date_str'];
                    $session_count = $sessions_by_date[$date_str] ?? 0;
                    // Class 'active-day' is added by JS on load/click, but 'today' is for initial state
                    $is_today_class = $day['is_today'] ? 'today' : ''; 
                    $display_day_name = $day['is_today'] ? 'Today' : $day['display_day'];
                    $session_text = $session_count > 0 ? "{$session_count} Sessions" : "—";
                ?>
                    <div class="calendar-day calendar-day-item <?php echo $is_today_class; ?>" 
                         data-date="<?php echo $date_str; ?>"
                         data-day-name="<?php echo htmlspecialchars($display_day_name); ?>"
                         title="<?php echo "{$day['display_date']} ({$session_count} sessions)"; ?>">
                        <div class="font-bold text-primary text-base"><?php echo htmlspecialchars($display_day_name); ?></div>
                        <div class="tiny text-xs mt-1 text-text-muted"><?php echo htmlspecialchars($day['display_date']); ?></div>
                        <div class="font-medium text-sm mt-2"><?php echo $session_text; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="card fade-up" style="--fade-delay: 0.1s;">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="material-symbols-outlined">group</span> Assigned Sessions (<span id="session-day-title">Today</span>)
            </h2>
            <table class="w-full text-sm">
                <thead class="text-text-muted tiny border-b border-primary/20">
                    <tr>
                        <th class="text-left py-2">Client Name</th>
                        <th class="text-left py-2">Time</th>
                        <th class="text-left py-2">Type</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-left py-2">Actions</th> </tr>
                </thead>
                <tbody id="sessions-table-body">
                    <?php 
                        if (empty($assigned_sessions)) {
                            // Placeholder rows for empty state (updated colspan to 5)
                            echo "<tr class='border-b border-white/5'>
                                <td colspan='5' class='py-4 text-center'>
                                    <div class='placeholder-box min-h-[30px]' style='justify-content:center;'>No sessions found today</div>
                                </td>
                            </tr>";
                        } else {
                            foreach ($assigned_sessions as $session) {
                                echo generate_session_row($session);
                            }
                        }
                    ?>
                </tbody>
            </table>
        </section>

        <section class="card fade-up" style="--fade-delay: 0.2s;">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-3">
                <span class="material-symbols-outlined">notifications_active</span> Staff Reminders
            </h2>
            <ul class="space-y-3 text-sm">
                <?php foreach ($staff_reminders as $reminder): ?>
                    <li class="placeholder-box min-h-[40px]" style="justify-content:flex-start;"><?php echo htmlspecialchars($reminder); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</div>

<div id="schedule-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex justify-between items-center pb-4 border-b border-primary/20 mb-6">
                <h3 id="modal-title" class="text-xl font-bold flex items-center gap-2 text-primary">
                    <span class="material-symbols-outlined">event</span> Add New Schedule
                </h3>
                <button id="close-schedule-modal" class="text-text-muted hover:text-primary transition-colors p-2" aria-label="Close modal">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
            </div>

            <form action="process_schedule_add.php" method="POST" class="space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="client_id" class="form-label">Client Name</label>
                        <select name="client_id" id="client_id" class="form-select" required>
                            <option value="">— Select a Client —</option>
                            <?php foreach ($available_clients as $client): ?>
                                <option value="<?php echo htmlspecialchars($client['id']); ?>">
                                    <?php echo htmlspecialchars($client['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="service_id" class="form-label">Service/Class Type</label>
                        <select name="service_id" id="service_id" class="form-select" required>
                            <option value="">— Select a Service —</option>
                            <?php foreach ($available_services as $service): 
                                $type = $service['is_class'] ? ' (Class)' : ' (PT)';
                            ?>
                                <option value="<?php echo htmlspecialchars($service['service_id']); ?>">
                                    <?php echo htmlspecialchars($service['name']) . $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="form-label">Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div>
                        <label for="start_time" class="form-label">Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-input" required value="<?php echo date('H:i'); ?>">
                    </div>
                </div>
                
                <div>
                    <label for="status" class="form-label">Status</label>
                    <div id="status-display" class="form-input bg-accent/50 border-warning/50 font-semibold text-warning" style="cursor: default;">
                        Pending (Default)
                    </div>
                    <input type="hidden" name="status" id="status" value="Pending">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeScheduleModal()" class="px-5 py-2 rounded-full bg-accent text-text-muted hover:bg-accent/70 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="btn-add">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // Javascript for sidebar and notifications (unchanged)
    // NOTE: Changed sidebar ID reference to 'staff-sidebar' (from 'customer-sidebar' in dashboard.php)
    const visibleSidebar = document.getElementById('staff-sidebar');
    const mobileToggleStaff = document.getElementById('mobile-toggle-staff'); 
    const notificationCount = document.getElementById('notification-count');
    const notificationDropdown = document.getElementById('notification-dropdown-staff');
    const notificationToggle = document.getElementById('notification-toggle-staff');

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
        
        // --- INITIAL ACTIVE STATE FOR TODAY ---
        const initialToday = document.querySelector('.calendar-day-item.today');
        if (initialToday) {
            initialToday.classList.add('active-day');
        }
        
        // Attach listeners for cancel buttons on initial load
        attachCancelListeners(); 
    });

    if (mobileToggleStaff) {
        mobileToggleStaff.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(mobileToggleStaff && mobileToggleStaff.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
    });

    // MODIFIED: Simplified active class logic to match dashboard.php's original logic
    document.querySelectorAll('.sidebar a[href]').forEach(link => {
        // 🛑 FIX APPLIED HERE: Skip links that are specifically for logout
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('logout')) {
            return; // Skip this link entirely
        }
        if (link.href.includes('login.php')) {
            return; // Also skip direct login links
        }
        
        // Simple way to check the current page and mark it active
        const urlPath = window.location.pathname;
        const fileName = urlPath.substring(urlPath.lastIndexOf('/') + 1);

        // Remove active from all links first to ensure only one is active
        link.classList.remove('active');


        // Check if the link's href matches the current file or if it's the specific page being loaded
        if (link.href.includes(fileName)) {
            link.classList.add('active');
        } else if (fileName === '' && link.href.includes('dashboard.php')) {
            // Edge case for index/root, ensure dashboard is active if no specific file is in URL
        }

        link.addEventListener('click', () => {
            if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
        });
    });

    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    document.addEventListener('click', (e) => {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-staff') {
            notificationDropdown.classList.add('hidden');
        }
    });

    if (notificationCount) notificationCount.textContent = '0';
    
    // =======================================================
    // === JAVASCRIPT FOR CLICKABLE CALENDAR AND AJAX ===
    // =======================================================
    const sessionDayTitle = document.getElementById('session-day-title');
    const sessionsTableBody = document.getElementById('sessions-table-body');
    const calendarDayItems = document.querySelectorAll('.calendar-day-item');

    /**
     * Generates the HTML string for the table body based on session data.
     * @param {Array} sessions - Array of session objects from the AJAX endpoint.
     * @returns {string} - The HTML content for the tbody.
     */
    function generateTableBodyHTML(sessions) {
        if (sessions.length === 0) {
            // Note: Colspan is 5 now
            return `
                <tr class="border-b border-white/5">
                    <td colspan="5" class="py-4 text-center">
                        <div class="placeholder-box min-h-[30px]" style="justify-content:center;">No sessions found for this day</div>
                    </td>
                </tr>
            `;
        }

        return sessions.map(session => {
            let statusClass = 'text-warning';
            switch(session.status) {
                case 'Confirmed': statusClass = 'text-primary'; break;
                case 'Completed': statusClass = 'text-success'; break;
                case 'Cancelled': statusClass = 'text-error'; break;
                // Pending is default/warning
            }
            
            // Define action cell HTML
            let actionHtml = '—';
            // Only show button if status is Confirmed or Pending
            if (session.status === 'Confirmed' || session.status === 'Pending') {
                 actionHtml = `
                    <button type='button' data-id='${session.booking_id}' class='btn-cancel-session px-3 py-1 text-xs rounded-full bg-error/20 text-error hover:bg-error/40 transition-colors flex items-center gap-1 leading-none'>
                        <span class='material-symbols-outlined text-base leading-none'>cancel</span> Cancel
                    </button>`;
            }


            return `
                <tr class="border-b border-white/5 data-row" data-booking-id="${session.booking_id}">
                    <td class="py-2">${session.client_name}</td>
                    <td class="py-2">${session.time}</td>
                    <td class="py-2">${session.service_name}</td>
                    <td class="py-2">
                        <span class="session-status font-semibold ${statusClass}">
                            ${session.status}
                        </span>
                    </td>
                    <td class="py-2 action-cell">${actionHtml}</td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Handles the click event for the cancel button and initiates AJAX request.
     */
    async function cancelSession(bookingId, buttonElement) {
        if (!confirm(`Are you sure you want to cancel session ID ${bookingId}? This will mark the session as 'Cancelled' and cannot be easily undone.`)) {
            return;
        }

        const originalHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.classList.add('opacity-50');
        buttonElement.innerHTML = `<span class="material-symbols-outlined text-base leading-none animate-spin">sync</span>`;

        try {
            // Send booking_id to a new PHP file for database update
            const response = await fetch('./cancel_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `booking_id=${bookingId}`
            });
            const data = await response.json();

            // Find the table row for this booking
            const row = buttonElement.closest('tr');
            // Find the status span element (it's the 4th td, and the only span inside it)
            const statusCellSpan = row.querySelector('.session-status'); 
            // Find the action cell
            const actionCell = row.querySelector('.action-cell');


            if (data.success) {
                // Update UI for success
                statusCellSpan.textContent = 'Cancelled';
                statusCellSpan.classList.remove('text-primary', 'text-warning', 'text-success');
                statusCellSpan.classList.add('text-error');
                
                // Remove the button and show the new status text
                actionCell.innerHTML = '—'; // Replace button with a dash
                
            } else {
                alert('Cancellation failed: ' + (data.error || 'Unknown server error.'));
                // Restore button state
                buttonElement.disabled = false;
                buttonElement.classList.remove('opacity-50');
                buttonElement.innerHTML = originalHtml;
            }
        } catch (error) {
            alert('Network error during cancellation. Check console for details.');
            // Restore button state
            buttonElement.disabled = false;
            buttonElement.classList.remove('opacity-50');
            buttonElement.innerHTML = originalHtml;
            console.error('Cancellation Fetch Error:', error);
        }
    }

    /**
     * Function to attach listeners to all existing cancel buttons.
     */
    function attachCancelListeners() {
        // We use event delegation on the table body since new rows can be loaded via AJAX
        sessionsTableBody.removeEventListener('click', handleCancelDelegation);
        sessionsTableBody.addEventListener('click', handleCancelDelegation);
    }

    /**
     * Helper for delegated event listener.
     */
    function handleCancelDelegation(e) {
        const button = e.target.closest('.btn-cancel-session');
        if (button) {
            e.preventDefault();
            const bookingId = button.getAttribute('data-id');
            cancelSession(bookingId, button);
        }
    }


    /**
     * Fetches and displays sessions for a given date using AJAX.
     * @param {string} date - The date in YYYY-MM-DD format.
     * @param {string} dayName - The display name for the date (e.g., Today, Monday).
     * @param {HTMLElement} element - The calendar day element that was clicked.
     */
    async function fetchSessions(date, dayName, element) {
        // --- ACTION: Remove active style from ALL elements, and apply to clicked element ---
        calendarDayItems.forEach(item => item.classList.remove('active-day'));
        element.classList.add('active-day');
        
        // Update the table title
        sessionDayTitle.textContent = dayName;

        // Visual loading state (updated colspan to 5)
        sessionsTableBody.innerHTML = `
            <tr class="data-row">
                <td colspan="5" class="text-center py-4 text-primary/70">
                    <span class="material-symbols-outlined animate-spin align-middle mr-2">progress_activity</span>
                    Loading schedule for ${dayName}...
                </td>
            </tr>
        `;


        try {
            // Note: The fetch_sessions.php script must also return the booking_id now
            const response = await fetch(`schedule-process/fetch_sessions.php?date=${date}`); 
            const data = await response.json();

            if (data.success) {
                // Update the table content
                sessionsTableBody.innerHTML = generateTableBodyHTML(data.sessions);
                // Listeners are attached via delegation, so no need for explicit re-attach in this flow
            } else {
                // Handle errors (updated colspan to 5)
                sessionsTableBody.innerHTML = `
                    <tr class="data-row">
                        <td colspan="5" class="text-center py-4 text-error">
                            <span class="material-symbols-outlined align-middle mr-2">error</span>
                            Error fetching schedule: ${data.error || 'An unknown error occurred.'}
                        </td>
                    </tr>
                `;
                console.error('API Error:', data.error);
            }
        } catch (error) {
            // Handle network/parsing errors (updated colspan to 5)
            sessionsTableBody.innerHTML = `
                <tr class="data-row">
                    <td colspan="5" class="text-center py-4 text-error">
                        <span class="material-symbols-outlined align-middle mr-2">warning</span>
                        Network/Server error. Please check 'fetch_sessions.php' file path.
                    </td>
                </tr>
            `;
            console.error('Fetch Error:', error);
        }
    }


    // Attach event listeners
    calendarDayItems.forEach(item => {
        item.addEventListener('click', function() {
            // Get data from the attributes set by PHP
            const date = this.getAttribute('data-date');
            const dayName = this.getAttribute('data-day-name');
            // Call the function passing the clicked element for immediate styling
            fetchSessions(date, dayName, this); 
        });
    });
    
    
    // =======================================================
    // === MODAL LOGIC (UNCHANGED) ===
    // =======================================================
    const scheduleModal = document.getElementById('schedule-modal');
    const btnAddNewSchedule = document.getElementById('open-schedule-modal-btn');
    const modalCloseBtn = document.getElementById('close-schedule-modal');

    function openScheduleModal() {
        scheduleModal.classList.add('open');
        document.body.style.overflow = 'hidden'; // Prevent scrolling the body underneath
    }

    function closeScheduleModal() {
        scheduleModal.classList.remove('open');
        document.body.style.overflow = ''; // Restore body scrolling
    }

    // Hook up the button
    if (btnAddNewSchedule) {
        btnAddNewSchedule.addEventListener('click', openScheduleModal); 
    }

    // Close modal via the close button (inside modal)
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeScheduleModal);
    }

    // Close modal by clicking outside the content (on the overlay)
    if (scheduleModal) {
        scheduleModal.addEventListener('click', (e) => {
            if (e.target === scheduleModal) {
                closeScheduleModal();
            }
        });
    }
</script>
</body>
</html>