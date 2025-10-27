<?php
// payments.php
// Staff Payments Dashboard - Shows bookings and allows recording payments.

session_start();
// Include the configuration file for database connection
require_once '../../config.php'; 

// --- 1. Authenticate and Authorize Logged-in Staff User (Profile Check) ---
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Get the logged-in user's ID
$staff_user_id = $_SESSION['user_id']; 

// Fetch staff details (name and role) for the header and authorization
try {
    $stmt = $pdo->prepare("SELECT name, role FROM users WHERE id = ?");
    $stmt->execute([$staff_user_id]);
    $staff_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and has the correct role for this staff-facing page
    if (!$staff_data || ($staff_data['role'] !== 'trainer' && $staff_data['role'] !== 'admin')) {
        // NOTE: The payments.php file already exists in the same directory as staff dashboard files
        // The original redirect was to ../../dashboard.php which implies a member dashboard
        // I will keep the original logic for security, assuming member dashboard is at root,
        // but if dashboard.php is the staff dashboard, this path should be checked.
        header("Location: ../../dashboard.php"); // Non-staff go to member dashboard
        exit;
    }

    $staff_name = $staff_data['name'];
    $staff_role = $staff_data['role'];
    $avatar_initial = strtoupper(substr($staff_name, 0, 1));
} catch (PDOException $e) {
    error_log("Failed to fetch staff details: " . $e->getMessage());
    $staff_name = "Database Error";
    $staff_role = "Error";
    $avatar_initial = 'E';
}

// --- 2. Calculate Dashboard Statistics (Placeholder for now) ---
// These will be updated via JavaScript after fetching data.
$totalBookings = 0;
$unpaidBookings = 0;
$paidBookings = 0;

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lena Gym Bocaue - Payments</title>

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
        /* CSS from clients.php for visual consistency */
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

        /* Page base and layout */
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

        /* Sidebar */
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
            .main-content { flex-grow: 1; margin-left: 0; }
        }
        
        .sidebar .logo-image { width: 48px; height: auto; }
        .sidebar h1 { font-size: 1.05rem; color: #ffc9c7; }

        /* Sidebar Nav */
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
            padding: 1.5rem;
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

        /* Small utilities and animations */
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

        /* Status Badges */
        .badge-paid { background:rgba(0,255,136,0.2); color:#00ff88; } /* Success green */
        .badge-unpaid { background:rgba(255,68,68,0.2); color:#ff4444; } /* Error red */
        .badge-pending { background:rgba(255,170,0,0.2); color:#ffaa00; } /* Warning orange */

        /* Modal Styles (Consistent with existing design principles) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background: var(--card);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 16px;
            box-shadow: var(--neon);
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.open {
            display: flex;
            opacity: 1;
        }
        .modal-overlay.open .modal-content {
            transform: translateY(0);
        }

        /* Form elements for modal consistency */
        .modal-content input, .modal-content select, .modal-content textarea {
            background: var(--accent);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            color: var(--text);
            padding: 10px;
            border-radius: 8px;
            width: 100%;
        }
        
        /* === NEW: Slide-Left Modal (Drawer) Styles === */
        #details-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 350px; /* Set a fixed width */
            max-width: 90vw;
            height: 100vh;
            background: linear-gradient(180deg, rgb(var(--sidebar-rgb)) 0%, rgb(var(--accent-rgb)) 100%);
            box-shadow: -2px 0 20px rgba(var(--primary-rgb), 0.4);
            z-index: 1001; /* Higher than payment modal */
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            padding: 2rem;
            overflow-y: auto;
        }
        
        #details-drawer.open {
            transform: translateX(0);
        }
        
        #details-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1000;
            display: none;
        }
        
        #details-overlay.open {
            display: block;
        }
        /* ============================================= */
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
            <a href="clients.php">
                <span class="material-symbols-outlined text-2xl">group</span>
                <span>Clients</span>
            </a>
            <a href="payments.php" class="active">
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
                <h1 class="text-2xl font-bold">Payments Management</h1>
            </div>

            <div class="top-bar-right flex items-center gap-4 relative">
                <div class="top-bar-user flex items-center gap-2">
                    <div class="user-avatar" id="staff-top-avatar"><?php echo $avatar_initial; ?></div>
                    <div id="staff-user-info" class="hidden sm:block">
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($staff_name); ?></p>
                        <p class="text-xs tiny"><?php echo htmlspecialchars($staff_role); ?></p>
                    </div>
                </div>

                <button class="btn-add" onclick="alert('Open Add Task modal (implement)')">
                    <span class="material-symbols-outlined">task_alt</span>
                    <span class="hidden md:inline">Add Task</span>
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
                <div class="tiny">Total Bookings</div>
                <div class="text-2xl font-bold mt-2" id="stat-total"><?= $totalBookings ?></div>
            </div>
            <div class="card fade-up">
                <div class="tiny">Unpaid Bookings</div>
                <div class="text-2xl font-bold mt-2" id="stat-unpaid"><?= $unpaidBookings ?></div>
            </div>
            <div class="card fade-up">
                <div class="tiny">Paid Transactions</div>
                <div class="text-2xl font-bold mt-2" id="stat-paid"><?= $paidBookings ?></div>
            </div>
        </section>

        <section class="card fade-up">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">receipt_long</span> Booking Payments
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-text-muted">
                        <tr> 
                            <th class="py-3 pl-4">Booking ID</th>
                            <th>Client Name</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Amount Due</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                            <th class="pr-4">Details</th> </tr>
                    </thead>
                    <tbody id="bookings-table-body">
                        <tr>
                            <td colspan="8" class="py-4 text-center text-text-muted">Loading bookings...</td> </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div id="payment-modal-overlay" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl font-bold mb-4 text-primary">Record Payment Intention (Pending)</h2> <form id="payment-form">
            <input type="hidden" id="modal-booking-id" name="booking_id">
            <input type="hidden" id="modal-transaction-id" name="transaction_id">
            
            <div class="mb-4">
                <label for="modal-client-name" class="tiny mb-1 block">Client / Service</label>
                <input type="text" id="modal-client-name" class="input-field" readonly disabled>
            </div>

            <div class="mb-4">
                <label for="modal-amount" class="tiny mb-1 block">Amount Set for Payment (₱)</label>
                <input type="number" id="modal-amount" name="amount" step="0.01" required placeholder="0.00">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-ghost" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Set as Pending</button> </div>
        </form>
        <p id="payment-message" class="text-center mt-3 text-sm hidden"></p>
    </div>
</div>

<div id="details-overlay" class="modal-overlay" onclick="closeDetailsDrawer()"></div>
<div id="details-drawer">
    <button class="absolute top-4 right-4 text-primary hover:opacity-75" onclick="closeDetailsDrawer()" aria-label="Close details">
        <span class="material-symbols-outlined text-3xl">close</span>
    </button>
    
    <h2 class="text-2xl font-bold mb-6 mt-2 flex items-center gap-2 text-primary">
        <span class="material-symbols-outlined">info</span> Payment Details
    </h2>
    
    <div class="space-y-4 text-sm">
        <div class="card p-4">
            <div class="tiny">Client Name</div>
            <div class="font-medium" id="detail-client-name"></div>
        </div>
        <div class="card p-4">
            <div class="tiny">Client Email</div>
            <div class="font-medium" id="detail-client-email"></div>
        </div>
        <div class="card p-4">
            <div class="tiny">Payment Method</div>
            <div class="font-medium" id="detail-payment-method"></div>
        </div>
        <div class="card p-4">
            <div class="tiny">Payment Status</div>
            <div class="font-medium" id="detail-payment-status"></div>
        </div>
        <div class="card p-4">
            <div class="tiny">Amount (₱)</div>
            <div class="text-lg font-bold text-success" id="detail-paid-amount"></div>
        </div>
        <div class="card p-4 hidden" id="detail-remarks-container">
            <div class="tiny">Remarks</div>
            <div class="font-normal" id="detail-remarks"></div>
        </div>
    </div>
</div>

<script>
    // Global array to hold booking data for modal lookup
    let allBookings = [];

    // --- Sidebar and UI Setup (Adapted from dashboard.php for full consistency) ---
    const visibleSidebar = document.getElementById('customer-sidebar');
    const desktopToggleStaff = document.getElementById('desktop-toggle-staff');
    const notificationCount = document.getElementById('notification-count');
    const notificationDropdown = document.getElementById('notification-dropdown-staff');
    const notificationToggle = document.getElementById('notification-toggle-staff');
    const paymentModalOverlay = document.getElementById('payment-modal-overlay');
    const paymentForm = document.getElementById('payment-form');
    const bookingsTableBody = document.getElementById('bookings-table-body');
    const paymentMessage = document.getElementById('payment-message');
    // NEW ELEMENTS
    const detailsDrawer = document.getElementById('details-drawer');
    const detailsOverlay = document.getElementById('details-overlay');


    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-up').forEach((el, idx) => {
            setTimeout(() => el.classList.add('in'), 80 * idx);
        });
        fetchBookings(); // Fetch data on load
    });

    // Sidebar toggle for mobile
    if (desktopToggleStaff) {
        desktopToggleStaff.addEventListener('click', () => visibleSidebar.classList.toggle('open'));
    }
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 769 && visibleSidebar && !visibleSidebar.contains(e.target) && !(desktopToggleStaff && desktopToggleStaff.contains(e.target))) {
            visibleSidebar.classList.remove('open');
        }
    });

    // Mark active sidebar link (Fixed logic to exclude links without a proper href, like the Logout button)
    document.querySelectorAll('.sidebar nav.space-y-2 a[href]').forEach(link => {
        // Only proceed if the href is a proper link (not just '#')
        if (link.href && !link.href.endsWith('#')) {
            // Remove existing active class
            link.classList.remove('active');
            
            // Check if the current link's href contains the current page name
            if (link.href.includes('payments.php')) {
                link.classList.add('active');
            }
        }
        
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
    // Placeholder notification count
    if (notificationCount) notificationCount.textContent = '0';

    // --- Core Payment Dashboard Logic ---

    // Function to fetch bookings via AJAX
    async function fetchBookings() {
        bookingsTableBody.innerHTML = '<tr><td colspan="8" class="py-4 text-center text-text-muted">Fetching data...</td></tr>'; // colspan adjusted
        try {
            const response = await fetch('./paymentAPI.php?action=fetchBookings');
            const data = await response.json();

            if (data.success) {
                allBookings = data.bookings;
                renderBookingsTable(allBookings);
                updateStats(allBookings);
            } else {
                bookingsTableBody.innerHTML = `<tr><td colspan="8" class="py-4 text-center text-error">Error: ${data.error}</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching bookings:', error);
            bookingsTableBody.innerHTML = '<tr><td colspan="8" class="py-4 text-center text-error">Network error. Could not load data.</td></tr>';
        }
    }

    // Function to render the bookings table
    function renderBookingsTable(bookings) {
        if (bookings.length === 0) {
            bookingsTableBody.innerHTML = '<tr><td colspan="8" class="py-4 text-center text-text-muted">No pending or active bookings found.</td></tr>';
            return;
        }

        bookingsTableBody.innerHTML = bookings.map(booking => {
            let statusClass = 'badge-unpaid';
            if (booking.payment_status === 'Paid') {
                statusClass = 'badge-paid';
            } else if (booking.payment_status === 'Pending') {
                statusClass = 'badge-pending';
            }

            // MODIFIED: The Action Button now only opens the modal
            const actionButton = `
                <button type="button" class="btn-ghost p-1 record-payment-btn" 
                    data-booking-id="${booking.booking_id}"
                    data-transaction-id="${booking.transaction_id || 0}"
                    title="Set Payment Intention">
                    <span class="material-symbols-outlined text-xl text-primary">
                        payments
                    </span>
                </button>
            `;
            
            // NEW: Details Button
            const detailsButton = `
                <button type="button" class="btn-ghost p-1 detail-payment-btn" 
                    data-booking-id="${booking.booking_id}"
                    title="View Details">
                    <span class="material-symbols-outlined text-xl text-text">
                        info
                    </span>
                </button>
            `;

            return `
                <tr class="hover:bg-accent/30 transition">
                    <td class="py-3 pl-4 font-semibold">${booking.booking_id}</td>
                    <td>${booking.client_name}</td>
                    <td>${booking.service_name}</td>
                    <td>${booking.datetime}</td>
                    <td>₱${booking.amount_due}</td>
                    <td><span class="badge ${statusClass} px-2 py-0.5 rounded-full text-xs font-medium">${booking.payment_status}</span></td>
                    <td>${actionButton}</td>
                    <td>${detailsButton}</td> </tr>
            `;
        }).join('');

        // Attach event listeners to the new buttons
        document.querySelectorAll('.record-payment-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookingId = e.currentTarget.dataset.bookingId;
                openPaymentModal(bookingId);
            });
        });
        
        // NEW: Attach event listeners for Details buttons
        document.querySelectorAll('.detail-payment-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookingId = e.currentTarget.dataset.bookingId;
                openDetailsDrawer(bookingId);
            });
        });
    }

    // Function to update dashboard stats
    function updateStats(bookings) {
        const total = bookings.length;
        const paid = bookings.filter(b => b.payment_status === 'Paid').length;
        // MODIFIED: Unpaid now includes strictly Unpaid AND Pending
        const unpaid = bookings.filter(b => b.payment_status === 'Unpaid' || b.payment_status === 'Pending').length;
        
        document.getElementById('stat-total').textContent = total;
        document.getElementById('stat-unpaid').textContent = unpaid;
        document.getElementById('stat-paid').textContent = paid;
    }

    // --- EXISTING Modal Functions (MODIFIED) ---

    function openPaymentModal(bookingId) {
        const booking = allBookings.find(b => b.booking_id == bookingId);
        if (!booking) {
            alert('Booking details not found.');
            return;
        }

        // Reset form state
        paymentForm.reset();
        paymentMessage.classList.add('hidden');
        paymentMessage.textContent = '';
        
        // Populate modal fields
        document.getElementById('modal-booking-id').value = booking.booking_id;
        // Important: If a transaction exists, we want to update it. Otherwise, it's a new insert.
        document.getElementById('modal-transaction-id').value = booking.transaction_id || '';
        document.getElementById('modal-client-name').value = `${booking.client_name} - ${booking.service_name}`;
        
        // Pre-fill with booking amount due, or the paid amount if editing an existing pending/paid transaction
        const amountField = document.getElementById('modal-amount');
        // Use paid_amount if it's set (meaning there's a transaction) otherwise use amount_due
        const amountValue = (booking.paid_amount && booking.paid_amount !== '0.00') ? booking.paid_amount : booking.amount_due;
        amountField.value = amountValue.replace('₱', '').replace(/,/g, ''); // Remove currency symbol and commas
        
        // Open the modal
        paymentModalOverlay.classList.add('open');
    }

    function closePaymentModal() {
        paymentModalOverlay.classList.remove('open');
        paymentForm.reset();
    }
    
    // Close modal when clicking outside
    paymentModalOverlay.addEventListener('click', (e) => {
        if (e.target.id === 'payment-modal-overlay') {
            closePaymentModal();
        }
    });


    // --- NEW Details Drawer Functions ---
    function openDetailsDrawer(bookingId) {
        const booking = allBookings.find(b => b.booking_id == bookingId);
        if (!booking) {
            alert('Booking details not found for the drawer.');
            return;
        }
        
        // Populate drawer fields
        document.getElementById('detail-client-name').textContent = booking.client_name;
        document.getElementById('detail-client-email').textContent = booking.client_email || 'N/A'; // New field from API
        document.getElementById('detail-payment-method').textContent = booking.payment_method || 'N/A (Unpaid/Pending)';
        document.getElementById('detail-payment-status').textContent = booking.payment_status;
        
        // Display Paid Amount, or Amount Due if Unpaid
        const displayAmount = (booking.payment_status !== 'Unpaid' && booking.paid_amount) ? booking.paid_amount : booking.amount_due;
        document.getElementById('detail-paid-amount').textContent = `₱${displayAmount}`;
        
        // Handle Remarks visibility
        const remarksContainer = document.getElementById('detail-remarks-container');
        const remarksContent = document.getElementById('detail-remarks');
        if (booking.payment_remarks) {
            remarksContent.textContent = booking.payment_remarks;
            remarksContainer.classList.remove('hidden');
        } else {
            remarksContainer.classList.add('hidden');
        }

        // Open the drawer
        detailsOverlay.classList.add('open');
        detailsDrawer.classList.add('open');
    }

    function closeDetailsDrawer() {
        detailsDrawer.classList.remove('open');
        detailsOverlay.classList.remove('open');
    }

    // --- Form Submission Handler (MODIFIED) ---
    paymentForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'savePayment');
        // The API now forces payment_method/remarks to 'Pending' defaults internally, so we don't need these here

        // Disable button and show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        paymentMessage.classList.remove('hidden');
        paymentMessage.classList.remove('text-success', 'text-error');
        paymentMessage.textContent = 'Processing payment intention...';

        try {
            const response = await fetch('./paymentAPI.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                paymentMessage.textContent = data.message;
                paymentMessage.classList.add('text-success');
                
                // Refresh data after successful save
                await fetchBookings(); 
                
                // Close modal after a short delay
                setTimeout(closePaymentModal, 1500); 

            } else {
                paymentMessage.textContent = `Error: ${data.error}`;
                paymentMessage.classList.add('text-error');
            }
        } catch (error) {
            paymentMessage.textContent = 'A network error occurred.';
            paymentMessage.classList.add('text-error');
            console.error('Save Payment Error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Set as Pending';
        }
    });

</script>
</body>
</html>