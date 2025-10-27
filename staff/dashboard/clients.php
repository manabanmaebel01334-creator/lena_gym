<?php
// clients.php
// Staff Clients Dashboard - NOW FETCHING DATA FROM DATABASE FOR LOGGED-IN TRAINER

session_start();
// Include the configuration file for database connection
require_once '../../config.php'; 

// --- 1. Authenticate and Authorize Logged-in Staff User (Profile Check) ---
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../../login.php");
    exit;
}

// Get the logged-in user's ID
$trainer_user_id = $_SESSION['user_id']; 

// Fetch staff details (name and role) for the header and authorization
try {
    // Assuming $pdo is correctly initialized in config.php
    $stmt = $pdo->prepare("SELECT name, role FROM users WHERE id = ?");
    $stmt->execute([$trainer_user_id]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and has the correct role for this staff-facing page
    if (!$trainer || ($trainer['role'] !== 'trainer' && $trainer['role'] !== 'admin')) {
        // Redirect unauthorized users (e.g., standard 'member' role)
        header("Location: ../../dashboard.php"); // Assuming non-staff go to member dashboard
        exit;
    }

    $staff_name = $trainer['name'];
    $staff_role = $trainer['role'];
    $avatar_initial = strtoupper(substr($staff_name, 0, 1));
} catch (PDOException $e) {
    error_log("Failed to fetch trainer details: " . $e->getMessage());
    // Fallback in case of DB error
    $staff_name = "Database Error";
    $staff_role = "Error";
    $avatar_initial = 'E';
    // In a real environment, you might want to show a maintenance page or exit;
}


// --- 2. Fetch Clients assigned to this Trainer/Admin ---
$clients = [];
$selectedClient = null;

try {
    // Base query for client data
    $sql = "
        SELECT DISTINCT
            u.id, 
            u.name,
            u.email,
            u.created_at,
            u.birth_date,   -- ADDED: Client's Birth Date
            u.height_cm,    -- ADDED: Client's Height in CM
            u.gender,       -- ADDED: Client's Gender
            um.status AS membership_status,
            um.end_date AS membership_expiry_date,
            m.name AS membership_name,
            -- Subqueries to fetch latest metrics/progress
            (SELECT T2.current_weight FROM user_metrics T2 WHERE T2.user_id = u.id ORDER BY T2.metric_date DESC LIMIT 1) AS current_weight,
            (SELECT T3.target_weight FROM user_progress T3 WHERE T3.user_id = u.id ORDER BY T3.created_at DESC LIMIT 1) AS target_weight,
            (SELECT T4.current_weight FROM user_progress T4 WHERE T4.user_id = u.id ORDER BY T4.created_at DESC LIMIT 1) AS progress_current_weight
        FROM 
            users u
        LEFT JOIN 
            bookings b ON u.id = b.user_id
        LEFT JOIN
            user_memberships um ON u.id = um.user_id AND um.status = 'Active'
        LEFT JOIN
            memberships m ON um.membership_id = m.membership_id
        WHERE 
            u.role = 'member' -- Only select actual clients (members)
    ";
    
    $params = [];
    
    // Add specific filtering if the user is a trainer, not an admin
    if ($staff_role === 'trainer') {
        // Trainers only see clients who have booked a session with them
        $sql .= " AND b.trainer_id = :trainer_user_id";
        $params['trainer_user_id'] = $trainer_user_id;
    }
    
    // Finalize query structure
    $sql .= "
        GROUP BY 
            u.id
        ORDER BY
            u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $db_clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map DB data to client structure and calculate derived fields
    foreach ($db_clients as $client_data) {
        $weight_progress = 0;
        // Simple progress simulation: (start_weight - current_weight) / (start_weight - target_weight) * 100
        // Since we don't have 'start_weight', we'll use a placeholder for 'progress_percent'
        $progress_percent = rand(30, 95); 
        $status = $client_data['membership_status'] === 'Active' ? 'Active' : 'Expired/Inactive';
        
        $expiry_date = 'N/A';
        if ($client_data['membership_expiry_date']) {
            $expiry = new DateTime($client_data['membership_expiry_date']);
            $expiry_date = $expiry->format('M d, Y');
            // Check if expiring soon (e.g., in the next 7 days)
            if ($expiry->getTimestamp() < strtotime('+7 days') && $expiry->getTimestamp() > time()) {
                 $status = 'Expiring';
            } else if ($expiry->getTimestamp() < time()) {
                $status = 'Expired';
            }
        }

        // Calculate Age from birth_date
        $age = 'N/A';
        if ($client_data['birth_date']) {
            try {
                $dob = new DateTime($client_data['birth_date']);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
            } catch (Exception $e) {
                error_log("Error calculating age for user {$client_data['id']}: " . $e->getMessage());
                // Fallback age from previous placeholder logic if birth date is invalid
                $age = (new DateTime())->diff(new DateTime($client_data['created_at']))->y + 20; 
            }
        } else {
             // Fallback age from previous placeholder logic if birth date is NULL
            $age = (new DateTime())->diff(new DateTime($client_data['created_at']))->y + 20; 
        }

        // Height in meters for display
        $height_m = 'N/A';
        if ($client_data['height_cm'] > 0) {
            $height_m = number_format($client_data['height_cm'] / 100, 2) . ' m';
        } else {
            $height_m = '1.75 m'; // Placeholder if NULL/0
        }

        $current_weight = $client_data['current_weight'] ?? $client_data['progress_current_weight'] ?? null;
        if ($current_weight) {
            $weight_display = number_format($current_weight, 1) . ' kg';
        } else {
            $weight_display = rand(60, 90) . ' kg'; // Placeholder if no metric/progress data
        }

        // Calculate BMI using actual height/weight if available, otherwise use placeholder
        $bmi = 25.0 + (rand(-30, 30) / 10); // Placeholder BMI
        if ($current_weight && $client_data['height_cm'] > 0) {
            $height_in_m = $client_data['height_cm'] / 100;
            // BMI = weight (kg) / [height (m)]^2
            $bmi = $current_weight / ($height_in_m * $height_in_m);
        }
        $bmi_display = number_format($bmi, 1);


        // Determine Goal Description
        $target_weight = $client_data['target_weight'];
        
        // FIX: Using !is_null() for explicit check, as target_weight might be fetched as NULL, 
        // which evaluates to false in loose comparison ($target_weight ? ...).
        $has_target_weight = !is_null($target_weight);

        $goal_short = $has_target_weight ? 'Weight Goal' : 'General Fitness';
        $goal_long = $has_target_weight ? "Target Weight: {$target_weight} kg" : 'Achieve general fitness and better health.';

        
        $clients[] = [
            'id' => $client_data['id'],
            'name' => $client_data['name'],
            'email' => $client_data['email'], 
            'age' => $age, // Updated age calculation
            'goal_short' => $goal_short, 
            'goal_long' => $goal_long, 
            'bmi' => $bmi_display, // Updated BMI calculation
            'membership' => $client_data['membership_name'] ?? 'No Active Plan', 
            'progress_percent' => $progress_percent, 
            'trainer' => $staff_name, 
            'status' => $status,
            'weight' => $weight_display,
            'height' => $height_m, // Updated height display
            'target_weight' => $has_target_weight ? number_format($target_weight, 1) . ' kg' : 'N/A', 
            'body_fat' => rand(10, 30) . '%', // Placeholder
            'expiry' => $expiry_date,
            'gender' => $client_data['gender'] ?? (rand(0,1) ? 'Male' : 'Female'), // Use DB gender or Placeholder
        ];
    }
    
} catch (PDOException $e) {
    error_log("[v1] Database query failed in clients.php: " . $e->getMessage());
    // Keep clients array empty to prevent fatal errors
}


// --- 3. Determine the client to display details for ---
$selectedClientId = $_GET['id'] ?? null;
$selectedClient = $clients[0] ?? [
    'id' => 0, 'name' => 'No Client Selected', 'gender' => '', 'age' => '', 'goal_short' => '', 
    'goal_long' => 'N/A', 'bmi' => '', 'membership' => '', 'progress_percent' => 0, 
    'trainer' => '', 'status' => '', 'weight' => 'N/A', 'height' => 'N/A', 
    'target_weight' => 'N/A', // ADDED TO DEFAULT
    'body_fat' => 'N/A', 'expiry' => 'N/A'
]; 

foreach ($clients as $client) {
    if ($client['id'] == $selectedClientId) {
        $selectedClient = $client;
        break;
    }
}

// If no ID is passed, default to the first client only if clients exist
if (!$selectedClientId && !empty($clients)) {
    $selectedClient = $clients[0];
}


// --- 4. Calculate Dashboard Statistics ---
$totalClients = count($clients);
$activeClients = count(array_filter($clients, fn($c) => $c['status'] === 'Active'));
$expiringClients = count(array_filter($clients, fn($c) => $c['status'] === 'Expiring'));


// --- HTML Output Remains Largely the Same ---
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Clients</title>

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
                        success: "#00ff88", // Added for consistency
                        warning: "#ffaa00", // Added for consistency
                        error: "#ff4444" // Added for consistency
                    },
                    fontFamily: {
                        display: ["Lexend", "sans-serif"]
                    },
                    boxShadow: {
                        'neon': '0 0 20px rgba(255, 23, 68, 0.5), 0 0 40px rgba(255, 23, 68, 0.3)', // Added for consistency
                        'neon-glow': '0 0 10px rgba(255, 23, 68, 0.8)', // Added for consistency
                        'neon-button': '0 0 15px rgba(255, 23, 68, 0.4), inset 0 0 10px rgba(255, 23, 68, 0.2)' // Added for consistency
                    }
                },
            },
        }
    </script>

    <style>
        /* **FIX: Copied full :root variables from staff_dashboard.php** */
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

        /* **FIX: Replaced basic body/layout styles with full staff_dashboard styles for consistency** */
        /* Page base */
        body {
            font-family: 'Lexend', sans-serif;
            background: linear-gradient(135deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--card-rgb)) 100%);
            min-height: 100vh;
            color: rgb(var(--text-rgb));
            overflow-x: hidden;
        }

        /* Page layout */
        .page-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--accent-rgb)) 100%);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(var(--primary-rgb), 0.2);
            color: rgb(var(--text-muted-rgb));
            transform: translateX(-100%); /* Mobile initially hidden */
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
        .sidebar h1 { font-size: 1.05rem; color: #ffc9c7; } /* Used #ffc9c7 in dashboard */


        /* Sidebar Nav (Using space-y-2 to match dashboard HTML structure) */
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


        /* Card look */
        .card {
            background: rgba(var(--card-rgb), 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.1);
            padding: 1.5rem; /* Ensure consistent padding with dashboard */
        }
        
        /* header top bar */
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

        /* Small utilities */
        .tiny { font-size: .85rem; color: rgb(var(--text-muted-rgb)); }
        
        .fade-up { opacity:0; transform: translateY(8px); transition: all .45s ease; }
        .fade-up.in { opacity:1; transform: translateY(0); }

        /* Button styles from Dashboard for consistency */
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
        }

        /* **FIX: Simplified .btn-primary and .btn-ghost to match dashboard button style for consistency** */
        .btn-primary { 
            background: linear-gradient(90deg,var(--primary), #ff8a9b); 
            color: white; 
            padding: 10px 16px; 
            border-radius: 999px; 
            font-weight: 600; 
            transition: all .3s ease; 
        }
        .btn-primary:hover { 
            box-shadow: 0 6px 25px rgba(var(--primary-rgb), 0.4); 
        }
        
        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(var(--primary-rgb),0.12);
            color: var(--primary);
            padding: .5rem .75rem;
            border-radius: 10px;
            transition: all .3s ease;
        }
        .btn-ghost:hover {
             background: rgba(var(--primary-rgb), 0.05);
        }
        /* Existing client-specific styles */
        .client-row { cursor: pointer; }
        .client-row.selected { background-color: rgba(255, 23, 68, 0.1) !important; border-left: 3px solid #ff1744; }
        .badge-active { background:rgba(255,23,68,0.2); color:#ffcccb; }
        .badge-expiring { background:rgba(255,193,7,0.2); color:#ffd54f; }
    </style>
</head>

<body>
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
            <a href="schedule.php">
                <span class="material-symbols-outlined text-2xl">event</span>
                <span>Schedule</span>
            </a>
            <a href="clients.php" class="active">
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

    <main class="main-content flex-grow p-6 space-y-6">
        <header class="top-bar flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <button id="desktop-toggle-staff" class="md:hidden p-2 rounded-lg bg-accent text-text" aria-label="menu toggle">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-2xl font-bold">Client Management</h1>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="staff-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="staff-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($staff_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($staff_role); ?></p>
                    </div>
                </div>

                <button class="btn-add flex items-center gap-2" onclick="alert('Open Add Client modal (implement)')">
                    <span class="material-symbols-outlined">person_add</span>
                    <span class="hidden md:inline">Add Client</span>
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

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card fade-up">
                <div class="tiny">Total Clients</div>
                <div class="text-2xl font-bold mt-2"><?= $totalClients ?></div>
            </div>
            <div class="card fade-up">
                <div class="tiny">Active Programs</div>
                <div class="text-2xl font-bold mt-2"><?= $activeClients ?></div>
            </div>
            <div class="card fade-up">
                <div class="tiny">Expiring Memberships</div>
                <div class="text-2xl font-bold mt-2"><?= $expiringClients ?></div>
            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">group</span> Client List
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-text-muted">
                        <tr class="text-center"> 
                            <th class="py-3 text-left pl-4">Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Goal</th>
                            <th>BMI</th>
                            <th>Trainer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                            <tr class="text-center">
                                <td colspan="7" class="py-4 text-text-muted">No clients assigned yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client) :
                                // Assign the correct status class
                                $status_class = 'badge-active';
                                if ($client['status'] === 'Expiring') {
                                    $status_class = 'badge-expiring';
                                } else if ($client['status'] !== 'Active') {
                                    $status_class = 'bg-gray-700 text-gray-400'; // Default for Expired/Inactive
                                }

                                $row_class = $selectedClient && $client['id'] == $selectedClient['id'] ? 'selected' : '';
                            ?>
                            <tr class="client-row hover:bg-accent/30 transition <?= $row_class ?>"
                                data-client-id="<?= $client['id'] ?>"
                                onclick="window.location.href='clients.php?id=<?= $client['id'] ?>'"
                            >
                                <td class="py-3 font-semibold pl-4"><?= htmlspecialchars($client['name']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['gender']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['age']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['goal_short']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['bmi']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['trainer']) ?></td>
                                <td class="text-center"><span class="badge <?= $status_class ?> px-2 py-0.5 rounded-full text-xs font-medium"><?= htmlspecialchars($client['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">person</span> Client Details - <span class="text-primary"><?= htmlspecialchars($selectedClient['name']) ?></span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="tiny mb-1">Membership Plan</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['membership']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Membership Expiry</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['expiry']) ?></div>
                </div>
                <div class="md:col-span-1">
                    <h3 class="tiny mb-1">Goal Progress</h3>
                    <div class="text-lg font-semibold mb-1"><?= htmlspecialchars($selectedClient['progress_percent']) ?>% Complete</div>
                    <div class="h-3 w-full bg-accent/50 rounded-full">
                        <div class="h-3 bg-primary rounded-full" style="width:<?= $selectedClient['progress_percent'] ?>%;"></div>
                    </div>
                </div>

                <div>
                    <h3 class="tiny mb-1">Age</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['age']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Weight</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['weight']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Target Weight</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['target_weight']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Height</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['height']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Body Fat</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['body_fat']) ?></div>
                </div>
                <div>
                    <h3 class="tiny mb-1">Goal Description</h3>
                    <div class="text-lg font-semibold"><?= htmlspecialchars($selectedClient['goal_long']) ?></div>
                </div>

            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">bolt</span> Quick Actions for <?= htmlspecialchars($selectedClient['name']) ?>
            </h2>
            <div class="flex flex-wrap gap-3">
                <button class="btn-ghost flex items-center gap-2">
                    <span class="material-symbols-outlined">insert_chart</span> View Progress Report
                </button>
                <button class="btn-ghost flex items-center gap-2">
                    <span class="material-symbols-outlined">mail</span> Send Message
                </button>
                <button class="btn-ghost flex items-center gap-2">
                    <span class="material-symbols-outlined">edit</span> Edit Details
                </button>
            </div>
        </section>
    </main>
</div>

<script>
    // DOM elements
    const visibleSidebar = document.getElementById('customer-sidebar');
    const desktopToggleStaff = document.getElementById('desktop-toggle-staff');
    const notificationCount = document.getElementById('notification-count');
    const notificationDropdown = document.getElementById('notification-dropdown-staff');
    const notificationToggle = document.getElementById('notification-toggle-staff');

    // Fade-up animation trigger (same as original)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
    });

    // Sidebar toggle for mobile
    if (desktopToggleStaff) {
        desktopToggleStaff.addEventListener('click', () => {
            visibleSidebar.classList.toggle('open');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleStaff && desktopToggleStaff.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
    });

    // mark active sidebar link (basic)
    document.querySelectorAll('.sidebar a[href]').forEach(link => {
        // 🛑 FIX APPLIED HERE: Skip links that are specifically for logout
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('logout')) {
            return; // Skip this link entirely
        }
        if (link.href.includes('login.php')) {
            return; // Also skip direct login links
        }

        // Clear all active classes first to prevent multiple
        link.classList.remove('active'); 
        
        // Mark active based on URL for "clients.php"
        if (link.href.includes('clients.php')) link.classList.add('active'); 
        
        // Close sidebar on link click (mobile)
        link.addEventListener('click', () => {
            if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
        });
    });

    // Notification dropdown toggle
    if (notificationToggle) {
        notificationToggle.addEventListener('click', (e) => {
            notificationDropdown.classList.toggle('hidden');
            e.stopPropagation();
        });
    }
    document.addEventListener('click', (e) => {
        if (!notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-staff') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // Initialize notification count (placeholder = 0)
    if (notificationCount) notificationCount.textContent = '0';
</script>
</body>
</html>