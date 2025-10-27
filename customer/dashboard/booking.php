<?php
// booking.php
// Location: /GYMRAT/customer/dashboard/booking.php

// 1. Start session and include config for DB connection
session_start();
// Path: ../../config.php (GYMRAT/customer/dashboard/ -> GYMRAT/config.php)
include_once('../../config.php'); 

// ✅ FIX: ENFORCE USE OF LOGGED-IN USER ID AND REMOVE HARDCODED VALUE
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login if the session is not set
    header('Location: ../../login.php'); 
    exit(); 
} 

$user_id = (int)$_SESSION['user_id']; // This is the ID used for all queries below.

// Set timezone and get current date/month/year for calendar generation
date_default_timezone_set('Asia/Manila');
$current_datetime = date('Y-m-d H:i:s');
$current_year = date('Y');
$current_month = date('m');

// --- Functions to Fetch Data (Database Implementations) ---

/**
 * Fetches upcoming confirmed bookings for the user.
 */
function fetchUpcomingBookings(PDO $pdo, $user_id, $current_datetime) {
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, 
            s.name AS service_name, 
            b.start_time, 
            b.end_time, 
            t.name AS trainer_name,
            s.is_class
        FROM 
            bookings b
        JOIN 
            services s ON b.service_id = s.service_id
        LEFT JOIN 
            users t ON b.trainer_id = t.id 
        WHERE 
            b.user_id = :user_id AND 
            b.status = 'Confirmed' AND
            b.start_time > :current_datetime
        ORDER BY 
            b.start_time ASC
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':current_datetime' => $current_datetime
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ✨ FIX: NEW FUNCTION TO FETCH BOOKINGS FOR THE TRAINER'S DASHBOARD (Requirement 3 & 4)
/**
 * Fetches upcoming confirmed bookings assigned to a specific trainer.
 * This function allows the trainer to see their assigned bookings.
 * * @param PDO $pdo The database connection object.
 * @param int $trainer_id The ID of the trainer whose bookings are to be fetched.
 * @param string $current_datetime The current date/time string.
 * @return array An array of upcoming confirmed bookings for the trainer.
 */
function fetchTrainerBookings(PDO $pdo, $trainer_id, $current_datetime) {
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, 
            s.name AS service_name, 
            b.start_time, 
            b.end_time, 
            u.name AS client_name,
            u.email AS client_email,
            b.status
        FROM 
            bookings b
        JOIN 
            services s ON b.service_id = s.service_id
        JOIN 
            users u ON b.user_id = u.id -- Join to get client's details
        WHERE 
            b.trainer_id = :trainer_id AND 
            b.status = 'Confirmed' AND 
            b.start_time > :current_datetime
        ORDER BY 
            b.start_time ASC
    ");

    $stmt->execute([
        ':trainer_id' => $trainer_id, 
        ':current_datetime' => $current_datetime
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// --- END OF NEW TRAINER FUNCTION ---


/**
 * Fetches past completed sessions.
 */
function fetchPastSessions(PDO $pdo, $user_id, $current_datetime) {
     $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, 
            s.name AS service_name, 
            b.start_time, 
            b.end_time
        FROM 
            bookings b
        JOIN 
            services s ON b.service_id = s.service_id
        WHERE 
            b.user_id = :user_id AND 
            b.status = 'Completed' AND
            b.end_time < :current_datetime
        ORDER BY 
            b.start_time DESC
        LIMIT 9 
    ");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':current_datetime' => $current_datetime
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * CORE FUNCTION: Fetches all detailed bookings for the month to power the full calendar.
 * Filters strictly by $user_id AND status ('Confirmed', 'Completed').
 */
function fetchAllBookingsForMonth(PDO $pdo, $user_id, $year, $month) {
    $start_date = "{$year}-{$month}-01 00:00:00";
    $end_date = date('Y-m-t 23:59:59', strtotime($start_date));

    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id, 
            s.name AS service_name, 
            b.start_time, 
            b.end_time, 
            t.name AS trainer_name,
            s.is_class
        FROM 
            bookings b
        JOIN 
            services s ON b.service_id = s.service_id
        LEFT JOIN 
            users t ON b.trainer_id = t.id
        WHERE 
            b.user_id = :user_id AND 
            b.status IN ('Confirmed', 'Completed') AND 
            b.start_time >= :start_date AND
            b.start_time <= :end_date
        ORDER BY 
            b.start_time ASC
    ");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
    
    $raw_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_bookings = [];
    foreach ($raw_bookings as $booking) {
        $date = new DateTime($booking['start_time']);
        $day = (int)$date->format('j');
        
        if (!isset($formatted_bookings[$day])) {
            $formatted_bookings[$day] = [];
        }
        $formatted_bookings[$day][] = $booking;
    }
    
    return $formatted_bookings; 
}

// Ensure $pdo is ready
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Error: Database connection object (\$pdo) is not available.");
}

// EXECUTE Database Queries
$upcomingBookings = fetchUpcomingBookings($pdo, $user_id, $current_datetime);
$pastSessions = fetchPastSessions($pdo, $user_id, $current_datetime);

$monthlyBookingsData = fetchAllBookingsForMonth($pdo, $user_id, $current_year, $current_month); 
$bookedDates = array_keys($monthlyBookingsData); 

$current_day_of_month = (int)date('j'); 
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Service Booking</title>
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
            --error-rgb: 255, 68, 68;
            --success-rgb: 0, 255, 136;
        }
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
        .calendar-selected-day {
            box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.8), inset 0 0 5px rgba(var(--primary-rgb), 0.5);
            transform: scale(1.05);
            transition: all 0.1s ease-in-out;
        }
        .upcoming-schedule-container {
            height: 350px; 
            overflow-y: auto; 
            padding-right: 1rem; 
        }
        .upcoming-schedule-container::-webkit-scrollbar {
            width: 8px;
        }
        .upcoming-schedule-container::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 10px;
        }
        .upcoming-schedule-container::-webkit-scrollbar-track {
            background-color: var(--accent);
        }
        .confirmation-modal {
            background: rgba(var(--card-rgb), 0.9);
            border: 1px solid var(--error);
            box-shadow: 0 0 30px rgba(var(--error-rgb), 0.5);
        }
        /* Toast Notification Styling */
        #toast-notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            min-width: 300px;
            z-index: 60;
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(-100%);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        #toast-notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        #toast-notification.success {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }
        #toast-notification.error {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid var(--error);
            color: var(--error);
        }
    </style>
</head>
<body class="customer">

    <div id="toast-notification" class="hidden" role="alert">
        <span id="toast-icon" class="material-symbols-outlined mr-3 text-2xl"></span>
        <div id="toast-message" class="font-medium"></div>
    </div>
    
    <div class="desktop-wrapper"> 
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
                <a href="booking.php" class="active">
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
                    <h1 class="text-2xl font-bold">Service Booking</h1>
                </div>
                <div class="top-bar-right flex items-center gap-4 relative">
                    <div class="top-bar-user flex items-center gap-2">
                        <div class="user-avatar" id="customer-top-avatar">J</div>
                        <div id="customer-user-info" class="hidden sm:block">
                            <p class="text-sm font-medium">Loading...</p>
                            <p class="text-xs text-text-muted">Loading...</p>
                        </div>
                    </div>
                    <button class="bg-primary px-4 py-2 rounded-full font-medium flex items-center gap-1" onclick="handleNewBooking()">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span class="hidden md:inline">New Booking</span>
                    </button>
                    <div class="relative">
                        <button id="notification-toggle-customer" class="p-3 rounded-full bg-accent text-text relative">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="absolute top-0 right-0 bg-primary w-4 h-4 rounded-full text-xs flex items-center justify-center" id="notification-count">0</span>
                        </button>
                        <div id="notification-dropdown-customer" class="absolute top-12 right-0 bg-card p-4 rounded-lg shadow-lg hidden">
                            <p class="text-xs text-text-muted">Loading notifications...</p>
                        </div>
                    </div>
                </div>
            </header>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <div class="lg:col-span-2 card p-6 space-y-4">
                    <h2 class="text-xl font-bold flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined">event_note</span> Upcoming Schedule
                    </h2>
                    
                    <div class="upcoming-schedule-container space-y-4"> 
                        <?php if (count($upcomingBookings) > 0): ?>
                            <?php foreach ($upcomingBookings as $booking): ?>
                                <?php 
                                    $start_dt = new DateTime($booking['start_time']);
                                    $end_dt = new DateTime($booking['end_time']);
                                    $type = $booking['is_class'] ? 'Group Class' : 'Personal Training';
                                    $trainer_info = $booking['trainer_name'] ? " with Coach " . htmlspecialchars($booking['trainer_name']) : "";
                                    $time_info = $start_dt->format('D, M j, Y') . ' | ' . $start_dt->format('g:i A') . ' - ' . $end_dt->format('g:i A') . $trainer_info;
                                ?>
                                <div id="booking-card-<?= $booking['booking_id'] ?>" class="bg-accent p-4 rounded-lg flex justify-between items-center border border-primary/50">
                                    <div>
                                        <p class="font-bold text-lg text-text"><?= htmlspecialchars($type . ': ' . $booking['service_name']) ?></p>
                                        <p class="text-sm text-text-muted"><?= $time_info ?></p>
                                    </div>
                                    <button id="cancel-btn-<?= $booking['booking_id'] ?>" class="bg-warning text-sidebar px-3 py-1 rounded-full text-sm font-bold" 
                                        onclick="cancelBooking(<?= $booking['booking_id'] ?>, '<?= htmlspecialchars($booking['service_name']) ?>')">
                                        Cancel
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="bg-accent p-8 rounded-lg text-center text-text-muted h-full flex flex-col justify-center items-center">
                                <p class="text-lg">You have no upcoming confirmed bookings. Time to book a session! 🏋️</p>
                                <button class="bg-primary px-4 py-2 rounded-full font-medium mt-4" onclick="handleNewBooking()">Book Now</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-1 card p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-warning">today</span> Booking Calendar
                    </h2>
                    <div class="bg-sidebar p-4 rounded-lg text-center">
                        <p class="text-text-muted mb-2"><?= date('F Y') ?></p>
                        <div class="grid grid-cols-7 text-xs font-bold text-text-muted/70 mb-2">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="grid grid-cols-7 gap-1" id="monthly-calendar-snapshot">
                            <?php
                            $firstDayOfMonth = new DateTime(date('Y-m-01'));
                            $daysInMonth = (int)$firstDayOfMonth->format('t');
                            $startDayOfWeek = (int)$firstDayOfMonth->format('w'); // 0 (for Sunday) through 6 (for Saturday)

                            // Print empty cells for days before the 1st
                            for ($i = 0; $i < $startDayOfWeek; $i++) {
                                echo '<span class="py-1"></span>';
                            }

                            // Print days
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $day_str = (string)$day;
                                $isBooked = in_array($day_str, $bookedDates); // Checks if day is in the $monthlyBookingsData keys
                                $isToday = ($day === $current_day_of_month);
                                
                                $class = 'py-1 text-text-muted';
                                
                                if ($isBooked) {
                                    $class = 'py-1 text-primary font-bold bg-accent rounded-full';
                                }
                                
                                if ($isToday) {
                                    $class = $isBooked ? 'py-1 text-primary font-bold bg-accent rounded-full border-2 border-primary' : 'py-1 text-text font-bold bg-primary/80 rounded-full';
                                }

                                echo '<span class="' . $class . '">' . $day . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <button class="w-full bg-primary px-4 py-2 rounded-full font-bold mt-4" onclick="handleViewFullMonth()">
                        View Full Month
                    </button>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-xl font-bold mb-4">Past Sessions</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php if (count($pastSessions) > 0): ?>
                        <?php foreach ($pastSessions as $session): ?>
                            <?php 
                                $start_dt = new DateTime($session['start_time']);
                                $end_dt = new DateTime($session['end_time']);
                                $duration_interval = $start_dt->diff($end_dt);
                                $duration = $duration_interval->i + ($duration_interval->h * 60);
                                $mock_rating = str_repeat('⭐️', rand(3, 5));
                            ?>
                            <div class="bg-accent p-4 rounded-lg border border-success/30">
                                <p class="font-bold text-lg text-text mb-1"><?= htmlspecialchars($session['service_name']) ?></p>
                                <div class="flex items-center gap-3 text-text-muted mb-2">
                                    <span class="material-symbols-outlined text-base">schedule</span>
                                    <p class="text-sm"><?= $duration ?> mins | <?= $start_dt->format('M j, Y') ?></p>
                                </div>
                                <p class="text-sm text-text-muted">Rating: <?= $mock_rating ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="md:col-span-3 bg-accent p-4 rounded-lg text-center text-text-muted">
                            <p>No completed sessions found in your history. Get sweating! 💪</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <div id="fullCalendarModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="card w-full max-w-4xl p-6 relative">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-warning">today</span> <span id="modalMonthTitle"><?= date('F Y') ?></span> Schedule
                </h2>
                <button class="absolute top-4 right-4 text-text-muted hover:text-primary" onclick="closeModal('fullCalendarModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div id="modalCalendarContent" class="bg-sidebar p-4 rounded-lg text-center grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-1">
                        <p class="text-text-muted mb-2">Click a marked day for details.</p>
                        <div class="grid grid-cols-7 text-xs font-bold text-text-muted/70 mb-2">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="grid grid-cols-7 gap-1" id="modal-full-calendar">
                            <?php
                            // Re-render the full month calendar view for the modal
                            $firstDayOfMonth = new DateTime(date('Y-m-01'));
                            $daysInMonth = (int)$firstDayOfMonth->format('t');
                            $startDayOfWeek = (int)$firstDayOfMonth->format('w'); // 0 (for Sunday) through 6 (for Saturday)

                            for ($i = 0; $i < $startDayOfWeek; $i++) {
                                echo '<span class="py-2"></span>';
                            }

                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                $day_str = (string)$day;
                                $isBooked = in_array($day_str, $bookedDates);
                                $isToday = ($day === $current_day_of_month);
                                
                                $class = 'py-2 cursor-pointer transition duration-300';
                                $bookingData = isset($monthlyBookingsData[$day_str]) ? htmlspecialchars(json_encode($monthlyBookingsData[$day_str])) : '[]';
                                
                                if ($isBooked) {
                                    $class .= ' bg-primary/20 text-primary font-bold rounded-lg hover:bg-primary/50';
                                } else {
                                    $class .= ' text-text-muted hover:bg-accent';
                                }

                                if ($isToday) {
                                    $class .= ' calendar-selected-day';
                                }

                                echo '<span 
                                        class="' . $class . '"
                                        data-day="' . $day . '"
                                        data-bookings=\'' . $bookingData . '\'
                                        onclick="showDayDetails(' . $day . ', this)">' . $day . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="md:col-span-1 p-4 bg-accent rounded-lg text-left">
                        <h3 class="text-xl font-bold mb-4" id="dayDetailsTitle">Day Details</h3>
                        <div id="dayDetailsContent" class="space-y-4">
                            <p class="text-text-muted">Select a day on the calendar to see scheduled sessions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="newBookingModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div id="bookingModalContent" class="card w-full max-w-lg p-6 relative">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_circle</span> New Service Booking
                </h2>
                <button class="absolute top-4 right-4 text-text-muted hover:text-primary" onclick="closeModal('newBookingModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div id="bookingFormContainer" class="space-y-4">
                    <p class="text-text-muted text-center py-4">Loading booking form...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm hidden z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="confirmation-modal w-full max-w-md p-6 relative rounded-xl">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined text-error">warning</span> Confirm Booking Cancellation
                </h2>
                <button class="absolute top-4 right-4 text-text-muted hover:text-error" onclick="closeModal('confirmationModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div id="confirmation-body">
                    <p class="text-text-muted mb-4">Are you sure you want to cancel your session for **<span id="cancellation-service-name" class="text-text"></span>**?</p>
                    <p class="text-sm text-error mb-6">This action cannot be undone.</p>
                </div>
                <div class="flex justify-between gap-4">
                    <button class="flex-1 bg-accent border border-text-muted/30 px-4 py-3 rounded-full font-medium text-text-muted hover:border-text" onclick="closeModal('confirmationModal')">
                        Keep Session
                    </button>
                    <button class="flex-1 bg-error px-4 py-3 rounded-full font-bold text-sidebar" id="confirm-cancellation-btn">
                        Confirm Cancellation
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const visibleSidebar = document.getElementById('customer-sidebar');
        const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');
        const fullCalendarModal = document.getElementById('fullCalendarModal');
        const dayDetailsContent = document.getElementById('dayDetailsContent');
        const dayDetailsTitle = document.getElementById('dayDetailsTitle');
        let currentCancellationId = null;
        
        // NEW ELEMENTS FOR HEADER DATA FETCHING
        const notificationCount = document.getElementById('notification-count');
        const notificationDropdown = document.getElementById('notification-dropdown-customer');


        // --- New Data Fetching Logic ---

        async function fetchDashboardData(userId) {
            try {
                // Assuming dashboardAPI.php is in the same folder as this file's API folder structure (../api/)
                const response = await fetch(`../api/dashboardAPI.php${userId ? `?user_id=${userId}` : ''}`); 
                const data = await response.json();

                if (!data.success) {
                    console.error('API Error:', data.message);
                    // Return minimal user data if API fails
                    return { user: { name: 'Member', role: 'Member', avatar_initial: 'M', id: null }, notifications: [] };
                }
                return { 
                    user: data.user, 
                    notifications: data.notifications || [],
                };

            } catch (error) {
                console.error('Fetch Error: Could not connect to dashboardAPI.php', error);
                return { user: { name: 'Member', role: 'Member', avatar_initial: 'M', id: null }, notifications: [] };
            }
        }
        
        function updateUI(data) {
            const userName = data.user.name;
            const userRole = data.user.role;
            const avatarInitial = data.user.avatar_initial;
            
            // 1. User Info (Sidebar and Top Bar)
            document.getElementById('customer-top-avatar').textContent = avatarInitial;
            document.getElementById('customer-user-avatar-sidebar').textContent = avatarInitial;
            
            // Update the user info block in the top bar
            const userInfoContainer = document.getElementById('customer-user-info');
            if (userInfoContainer) {
                userInfoContainer.innerHTML = `
                    <p class="text-sm font-medium">${userName}</p>
                    <p class="text-xs text-text-muted">${userRole || 'Member'}</p>
                `;
            }

            // 2. Notifications
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
        }

        // --- Original Logic (Modified) ---

        function showToast(type, message) {
            const toast = document.getElementById('toast-notification');
            const icon = document.getElementById('toast-icon');
            const msg = document.getElementById('toast-message');

            toast.className = 'hidden'; // Reset classes
            toast.classList.add(type);
            icon.textContent = type === 'success' ? 'check_circle' : 'error';
            msg.textContent = message;

            setTimeout(() => {
                toast.classList.remove('hidden');
                toast.classList.add('show');
            }, 10); // Small delay to force reflow and transition

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.classList.add('hidden'), 500);
            }, 4000);
        }

        async function loadBookingForm() {
            const container = document.getElementById('bookingFormContainer');
            try {
                // Fetch the content of services.php into the modal
                const response = await fetch('booking-process/services.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const html = await response.text();
                container.innerHTML = html;
                
                attachBookingFormHandler();
            } catch (error) {
                console.error('Error loading booking form:', error);
                container.innerHTML = '<p class="text-error">Failed to load booking form. Please try again.</p>';
                showToast('error', 'Failed to load booking form. Please try again.');
            }
        }

        function attachBookingFormHandler() {
            const form = document.querySelector('#bookingFormContainer form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Map form field names ('date', 'time') to the API's expected keys 
                const data = {
                    service_id: formData.get('service_id'),
                    trainer_id: formData.get('trainer_id') || null,
                    booking_date: formData.get('date'), 
                    booking_time: formData.get('time')  
                };

                try {
                    // Note: bookService.php is now expecting application/json data.
                    const response = await fetch('booking-process/api/bookService.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        showToast('success', 'Booking confirmed! Your session has been added.');
                        closeModal('newBookingModal');
                        // Reload the page to show the new booking in the Upcoming Schedule list
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('error', result.message || 'Booking failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Booking submission error:', error);
                    showToast('error', 'An error occurred. Please try again.');
                }
            });
        }

        function handleNewBooking() {
            document.getElementById('newBookingModal').classList.remove('hidden');
            loadBookingForm();
        }

        function cancelBooking(bookingId, serviceName) {
            currentCancellationId = bookingId;
            document.getElementById('cancellation-service-name').textContent = serviceName;
            document.getElementById('confirmationModal').classList.remove('hidden');

            // Attach final confirm action
            const confirmBtn = document.getElementById('confirm-cancellation-btn');
            confirmBtn.onclick = async () => {
                try {
                    const response = await fetch('booking-process/api/cancel_booking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ booking_id: currentCancellationId })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        showToast('error', `Booking #${currentCancellationId} cancelled successfully.`);
                        
                        // Optimistically remove the card visually
                        const card = document.getElementById(`booking-card-${currentCancellationId}`);
                        if (card) {
                            card.style.opacity = '0';
                            card.style.height = '0';
                            card.style.margin = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                        
                        closeModal('confirmationModal');
                        // Refresh the page to show the updated list from the database
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('error', result.message || 'Cancellation failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Cancellation error:', error);
                    showToast('error', 'An error occurred. Please try again.');
                }
                
                currentCancellationId = null;
            };
        }

        function showDayDetails(day, element) {
            document.querySelectorAll('#modal-full-calendar > span').forEach(el => {
                el.classList.remove('calendar-selected-day');
            });
            element.classList.add('calendar-selected-day');

            const bookings = JSON.parse(element.dataset.bookings);
            const monthName = document.getElementById('modalMonthTitle').textContent.split(' ')[0];
            dayDetailsTitle.textContent = `${monthName} ${day} Sessions`;
            dayDetailsContent.innerHTML = '';

            if (bookings.length === 0) {
                dayDetailsContent.innerHTML = '<p class="text-text-muted">No sessions scheduled for this day.</p>';
                return;
            }

            bookings.forEach(booking => {
                const startTime = new Date(booking.start_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                const endTime = new Date(booking.end_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                const trainer = booking.trainer_name ? `(${booking.trainer_name})` : '';
                
                dayDetailsContent.innerHTML += `
                    <div class="bg-card p-3 rounded-lg border-l-4 border-primary/50">
                        <p class="font-bold text-text">${booking.service_name}</p>
                        <p class="text-sm text-text-muted">${startTime} - ${endTime} ${trainer}</p>
                        <p class="text-xs text-warning">${booking.status || 'N/A'}</p>
                    </div>
                `;
            });
        }

        function handleViewFullMonth() {
            document.getElementById('fullCalendarModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            // Reset confirmation button if we close the modal without clicking confirm
            if (modalId === 'confirmationModal') {
                currentCancellationId = null; 
            }
        }

        // --- Event Listeners and Initial Load ---
        
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

        if (desktopToggleCustomer) {
            desktopToggleCustomer.addEventListener('click', () => {
                visibleSidebar.classList.toggle('open');
            });
        }
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleCustomer && desktopToggleCustomer.contains(e.target))) {
                visibleSidebar.classList.remove('open');
            }
            if (e.target === fullCalendarModal) {
                closeModal('fullCalendarModal');
            }
            if (e.target === document.getElementById('confirmationModal')) {
                closeModal('confirmationModal');
            }
            if (e.target === document.getElementById('newBookingModal')) {
                closeModal('newBookingModal');
            }
        });
        
        document.querySelectorAll('.sidebar a[href]').forEach(link => {
            // If it's a regular navigation link (not logout)
            if (!link.classList.contains('logout-link')) {
                link.classList.remove('active');
                if (link.href.includes('booking.php')) {
                    link.classList.add('active'); // Set current page as active
                }
            } else {
                // Explicitly remove active class from logout link so only :hover applies
                link.classList.remove('active');
            }
            
            link.addEventListener('click', () => {
                if (window.innerWidth < 769) {
                    visibleSidebar.classList.remove('open');
                }
            });
        });
        
        // Notification Dropdown Toggle (Copied from Dashboard)
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