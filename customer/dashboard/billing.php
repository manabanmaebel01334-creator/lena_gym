<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Allow even free users to access billing
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// --- ADDED/UPDATED: PayMongo Status Feedback & Transaction Fetch ---
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['fullname'] ?? 'Member';
$user_role = $_SESSION['role'] ?? 'Free Member'; 
$user_avatar_initial = $user_name[0] ?? 'M'; 

// Display transaction feedback from PayMongo redirects
$status_message = '';
if (isset($_GET['status']) && isset($_GET['message'])) {
    $status = htmlspecialchars($_GET['status']);
    // Sanitize and replace underscores for display
    $message = htmlspecialchars(str_replace('_', ' ', $_GET['message'])); 
    
    $status_classes = [
        'success' => 'text-success bg-success/20 border border-success dark:bg-green-800 dark:text-green-200',
        'error' => 'text-error bg-error/20 border border-error dark:bg-red-800 dark:text-red-200',
        'cancelled' => 'text-warning bg-warning/20 border border-warning dark:bg-yellow-800 dark:text-yellow-200'
    ];
    $class = $status_classes[$status] ?? 'text-text-muted bg-accent/50 border border-accent';
    
    // Banner HTML structure
    $status_message = "<div class='p-4 mb-6 text-sm rounded-lg $class' role='alert'>
                          <p class='font-bold'>Transaction Status: " . ucfirst($status) . "</p>
                          <p>$message</p>
                       </div>";
}

// Fetch all customer transactions (Pending and Paid)
$customer_transactions = [];
$custom_payments_pending = []; // New array for pending custom payments
$custom_payments_all = [];     // New array for all custom payments (paid/pending)

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.transaction_id, 
            t.amount, 
            t.description, 
            t.status, 
            t.payment_method,
            t.created_at,
            t.related_user_membership_id
        FROM transactions t
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $all_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_transactions as $t) {
        // Separate Membership payments from Custom (Staff-initiated) payments
        if (!empty($t['related_user_membership_id'])) {
            // This is a membership payment - add to the main history
            $customer_transactions[] = $t;
        } else {
            // This is a custom staff-initiated payment (no related_user_membership_id)
            $custom_payments_all[] = $t;
            if ($t['status'] === 'Pending') {
                $custom_payments_pending[] = $t;
            }
        }
    }
} catch (PDOException $e) {
    error_log("Billing Page DB Error: " . $e->getMessage());
}
// --- END ADDED/UPDATED PHP ---
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Lena Gym Bocaue - Membership</title>
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
      fontFamily: { display: ["Lexend", "sans-serif"] },
      boxShadow: {
        'neon': '0 0 20px rgba(255, 23, 68, 0.5), 0 0 40px rgba(255, 23, 68, 0.3)',
      }
    }
  }
}
</script>
<style>
/* ... (Your existing CSS styles here) ... */
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

/* --- Responsive Sidebar + Main Content (Flexbox) from dashboard.php --- */
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
    /* REVERTED: border to primary for consistency */
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

@keyframes fadeIn {0%{opacity:0;transform:translateY(30px);}100%{opacity:1;transform:translateY(0);} }
@keyframes slideInLeft {0%{opacity:0;transform:translateX(-60px);}100%{opacity:1;transform:translateX(0);} }
@keyframes slideInRight {0%{opacity:0;transform:translateX(60px);}100%{opacity:1;transform:translateX(0);} }
.fade-in {animation: fadeIn 1s ease forwards;}
.slide-in-left {animation: slideInLeft 1s ease forwards;}
.slide-in-right {animation: slideInRight 1s ease forwards;}
.btn-hover {transition:all 0.3s ease;}
.btn-hover:hover {transform:scale(1.05);box-shadow:0 10px 20px rgba(236,19,19,0.3);}
</style>
</head>

<body class="customer">

<div class="page-container">
  <aside id="customer-sidebar" class="sidebar">
    <div class="flex items-center gap-3 mb-8">
        <img src="../../assets/image/logo.png" alt="Lena Gym Logo" class="logo-image w-12 h-auto">
        <h1 class="text-xl font-bold text-white">Lena Gym Fitness</h1>
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
      <a href="billing.php" class="active">
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
            <div class="user-avatar w-8 h-8 text-sm" id="customer-user-avatar-sidebar"><?= htmlspecialchars($user_avatar_initial) ?></div>
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
            <h1 class="text-2xl font-bold">Membership</h1>
        </div>
        <div class="top-bar-right flex items-center gap-4 relative">
            <div class="top-bar-user flex items-center gap-2">
                <div class="user-avatar" id="customer-top-avatar"><?= htmlspecialchars($user_avatar_initial) ?></div>
                <div id="customer-user-info" class="hidden sm:block">
                    <p class="text-sm font-medium"><?= htmlspecialchars($user_name) ?></p>
                    <p class="text-xs text-text-muted"><?= htmlspecialchars($user_role) ?></p>
                </div>
            </div>
            <button onclick="openUpgradeModal()" class="bg-primary px-4 py-2 rounded-full font-medium btn-hover flex items-center gap-1">
                <span class="material-symbols-outlined">military_tech</span>
                <span class="hidden md:inline">Upgrade Membership</span>
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

    <?php 
    // ADDED: Echo the status message banner here
    echo $status_message; 
    
    // REMOVED/COMBINED: Old hardcoded success/cancel/error messages are replaced by $status_message
    // kept the conditional checks for backward compatibility with membership success handler redirect
    if (isset($_GET['status']) && $_GET['status'] === 'success' && !isset($_GET['message'])): ?>
        <div class="bg-success/20 border border-success text-success p-4 rounded-lg mb-6">
            <p class="font-bold">Payment Success! 🎉</p>
            <p>Your membership has been successfully upgraded/renewed. Please check your profile.</p>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'cancelled' && !isset($_GET['message'])): ?>
        <div class="bg-warning/20 border border-warning text-warning p-4 rounded-lg mb-6">
            <p class="font-bold">Payment Cancelled</p>
            <p>The payment process was cancelled or failed. Please try again.</p>
        </div>
    <?php elseif (isset($_GET['error']) && !isset($_GET['message'])): ?>
        <div class="bg-error/20 border border-error text-error p-4 rounded-lg mb-6">
            <p class="font-bold">Payment Error</p>
            <p>An error occurred during payment processing. Please try again.</p>
        </div>
    <?php endif; ?>

    <div class="grid gap-6">
      <div class="card p-6">
        <h2 class="text-xl font-bold mb-4">Current Membership</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-accent p-4 rounded-lg">
            <p class="text-text-muted mb-1">Plan</p>
            <p class="font-bold">Free Member</p>
          </div>
          <div class="bg-accent p-4 rounded-lg">
            <p class="text-text-muted mb-1">Next Renewal</p>
            <p class="font-bold">None (Free Tier)</p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <h2 class="text-xl font-bold mb-4">Membership Payment History</h2>
        <div id="transaction-history-container">
          </div>
      </div>

      <div class="card p-6">
          <h2 class="text-xl font-bold mb-4">Staff-Initiated Payments (Fees, Dues, etc.)</h2>
          <div id="custom-payment-history-container">
              </div>
      </div>
    </div>

    <div id="upgradeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-card p-6 rounded-lg max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Upgrade Membership</h2>
        <p class="text-text-muted mb-6">Choose your plan to access premium features.</p>
        <div class="space-y-4">
          <button onclick="openPaymentModal(1, 'Basic', 50)" class="w-full bg-accent p-4 rounded-lg text-left hover:bg-accent/80">
            <p class="font-bold">Basic Plan</p>
            <p class="text-sm text-text-muted">₱50/month - Basic access</p>
          </button>
          <button onclick="openPaymentModal(2, 'Monthly', 999)" class="w-full bg-accent p-4 rounded-lg text-left hover:bg-accent/80">
            <p class="font-bold">Monthly Plan</p>
            <p class="text-sm text-text-muted">₱999/month - Full access</p>
          </button>
          <button onclick="openPaymentModal(3, 'Premium', 1499)" class="w-full bg-accent p-4 rounded-lg text-left hover:bg-accent/80">
            <p class="font-bold">Premium Plan</p>
            <p class="text-sm text-text-muted">₱1499/month - VIP features</p>
          </button>
        </div>
        <button onclick="closeUpgradeModal()" class="mt-4 text-sm text-text-muted hover:underline">Close</button>
      </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <form action="paymongo_checkout.php" method="POST" class="bg-card p-6 rounded-lg max-w-md w-full">
        <h2 class="text-xl font-bold mb-4">Confirm Payment</h2>
        <input type="hidden" id="paymentPlanId" name="plan_id" value="">
        <div class="mb-4">
          <label class="block text-sm text-text-muted mb-2">Selected Plan</label>
          <input id="paymentPlanDisplay" class="w-full bg-accent p-3 rounded-md text-text" readonly>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-sm font-bold text-white bg-error rounded-md hover:bg-red-700 btn-hover">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-success rounded-md hover:bg-green-700 btn-hover">
            Proceed to PayMongo
          </button>
        </div>
      </form>
    </div>

    </main>
</div>

<script>
// =======================================================================================
// === Start of JavaScript Fixes and Additions ===
// =======================================================================================
const visibleSidebar = document.getElementById('customer-sidebar');
const desktopToggleCustomer = document.getElementById('desktop-toggle-customer');
const notificationDropdown = document.getElementById('notification-dropdown-customer');
const notificationCount = document.getElementById('notification-count'); 

// Specific elements for billing.php
const currentPlanDisplay = document.querySelector('.card:first-child .bg-accent:first-of-type p.font-bold'); 
const nextRenewalDisplay = document.querySelector('.card:first-child .bg-accent:last-of-type p.font-bold'); 
// Original Membership History Container
const membershipTransContainer = document.getElementById('transaction-history-container'); 
// NEW: Custom Payment History Container
const customPaymentContainer = document.getElementById('custom-payment-history-container');

// NEW: Pass the PHP data to JavaScript
const membershipTransactions = <?= json_encode($customer_transactions); ?>;
const customPayments = <?= json_encode($custom_payments_all); ?>;


async function fetchDashboardData(userId) {
    try {
        // Fetch data from the updated API endpoint
        const response = await fetch(`../api/dashboardAPI.php?user_id=${userId}`); 
        const data = await response.json();

        if (!data.success) {
            console.error('API Error:', data.message);
            // ADDED: Default data for robustness
            return { success: false, user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, notifications: [], current_membership: { name: 'Unknown', end_date: 'N/A' }, transactions: [] }; 
        }
        return data;

    } catch (error) {
        console.error('Fetch Error: Could not connect to dashboardAPI.php', error);
        // ADDED: Default data for robustness
        return { success: false, user: { name: 'Guest', role: 'Unknown', avatar_initial: 'G' }, notifications: [], current_membership: { name: 'Free Member', end_date: 'None (Free Tier)' }, transactions: [] };
    }
}

function updateUI(data) {
    const userName = data.user.name;
    const userRole = data.user.role; 
    const avatarInitial = data.user.avatar_initial;
    
    // 1. User Info (Top Bar & Sidebar)
    document.getElementById('customer-top-avatar').textContent = avatarInitial;
    document.getElementById('customer-user-avatar-sidebar').textContent = avatarInitial;
    document.querySelector('#customer-user-info p:first-child').textContent = userName;
    document.querySelector('#customer-user-info p:last-child').textContent = userRole || 'Member'; 

    // 2. Notifications (Placeholder logic)
    const unreadCount = data.notifications ? data.notifications.filter(n => !n.read).length : 0;
    notificationCount.textContent = unreadCount.toString();
    
    // 3. Billing/Membership Status (UPDATED)
    const currentPlanName = data.current_membership?.name || data.user.role || 'Free Member'; 
    let nextRenewal = data.current_membership?.end_date ? new Date(data.current_membership.end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'None (Free Tier)';
    
    currentPlanDisplay.textContent = currentPlanName;
    nextRenewalDisplay.textContent = nextRenewal;

    // 4. Transaction History Rendering (Membership-only history)
    renderMembershipTransactions(membershipTransactions);
    
    // 5. Custom Payment History Rendering (Includes "Pay Now" button)
    renderCustomPayments(customPayments);
}

function renderMembershipTransactions(payments) {
    if (!membershipTransContainer) return;

    if (payments.length === 0) {
        membershipTransContainer.innerHTML = '<p class="text-text-muted">No membership payment history found yet.</p>';
        return;
    }

    let html = '<div class="overflow-x-auto"><table class="min-w-full text-sm divide-y divide-accent">';
    html += '<thead><tr class="text-left text-text-muted"><th class="py-2 pr-4">Date</th><th class="py-2 pr-4">Amount</th><th class="py-2 pr-4">Description</th><th class="py-2">Status</th></tr></thead>';
    html += '<tbody class="divide-y divide-accent">';
    
    payments.forEach(t => {
        // Use created_at from PHP fetch
        const date = new Date(t.created_at).toLocaleDateString(); 
        const amount = `₱${parseFloat(t.amount).toFixed(2)}`;
        const statusClass = t.status === 'Paid' ? 'text-success' : 'text-error';
        
        html += `
            <tr class="hover:bg-accent/50">
                <td class="py-3 whitespace-nowrap">${date}</td>
                <td class="py-3 whitespace-nowrap">${amount}</td>
                <td class="py-3 whitespace-nowrap">${t.description}</td>
                <td class="py-3 whitespace-nowrap ${statusClass} font-medium">${t.status}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    membershipTransContainer.innerHTML = html;
}

// NEW FUNCTION: Handles custom staff-initiated payments
function renderCustomPayments(payments) {
    if (!customPaymentContainer) return;

    if (payments.length === 0) {
        customPaymentContainer.innerHTML = '<p class="text-text-muted">No outstanding or past staff-initiated payments found.</p>';
        return;
    }

    let html = '<div class="overflow-x-auto"><table class="min-w-full text-sm divide-y divide-accent">';
    html += '<thead><tr class="text-left text-text-muted">';
    html += '<th class="py-2 pr-4">Date</th>';
    html += '<th class="py-2 pr-4">Description</th>';
    html += '<th class="py-2 pr-4">Amount</th>';
    html += '<th class="py-2 pr-4">Status</th>';
    html += '<th class="py-2 pr-4">Method/Ref</th>';
    html += '<th class="py-2">Action</th>';
    html += '</tr></thead>';
    html += '<tbody class="divide-y divide-accent">';
    
    payments.forEach(t => {
        const date = new Date(t.created_at).toLocaleDateString();
        const amount = `₱${parseFloat(t.amount).toFixed(2)}`;
        const statusText = t.status || 'Pending';
        const statusClass = statusText === 'Paid' ? 'text-success' : (statusText === 'Pending' ? 'text-warning' : 'text-error');
        const method = t.payment_method || 'N/A';
        const reference = t.reference_id ? `Ref: ${t.reference_id.substring(0, 10)}...` : '';
        
        let actionHtml = '';
        if (statusText === 'Pending') {
            // Form submits the local transaction_id to the generic PayMongo checkout script
            actionHtml = `
                <form action="paymongo_checkout.php" method="POST" class="inline-block">
                    <input type="hidden" name="transaction_id" value="${t.transaction_id}">
                    <input type="hidden" name="type" value="custom_payment">
                    <button type="submit" class="text-sm px-3 py-1 bg-primary text-white rounded hover:bg-primary/90 transition-colors">
                        Pay Now
                    </button>
                </form>
            `;
        } else {
            actionHtml = 'N/A';
        }

        html += `
            <tr class="hover:bg-accent/50">
                <td class="py-3 whitespace-nowrap">${date}</td>
                <td class="py-3 whitespace-nowrap">${t.description}</td>
                <td class="py-3 whitespace-nowrap">${amount}</td>
                <td class="py-3 whitespace-nowrap"><span class="${statusClass} font-medium">${statusText}</span></td>
                <td class="py-3 whitespace-nowrap">${method} ${reference}</td>
                <td class="py-3 whitespace-nowrap">${actionHtml}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    customPaymentContainer.innerHTML = html;
}

// --- Main Load Logic ---
document.addEventListener('DOMContentLoaded', async () => {
    // Get user ID from the PHP variable exposed at the top
    const userId = <?= json_encode($user_id); ?>; 
    
    // We still fetch dashboard data for user info/notifications/membership status
    const data = await fetchDashboardData(userId);
    
    if (data.user && data.user.id) {
        localStorage.setItem('gymrat_user_id', data.user.id);
    }
    
    updateUI(data); // This now calls renderMembershipTransactions and renderCustomPayments

    // Sidebar Toggle Logic (kept the same)
    desktopToggleCustomer.addEventListener('click', () => {
        visibleSidebar.classList.toggle('open');
    });

    const sidebarLinks = visibleSidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        // Exclude the logout link from the 'active' style
        if (link.classList.contains('logout-link')) {
            link.classList.remove('active');
        }

        link.addEventListener('click', () => {
            if (window.innerWidth < 769) visibleSidebar.classList.remove('open');
        });
    });
    // Notification Dropdown Toggle (Copied from dashboard.php)
    document.getElementById('notification-toggle-customer').addEventListener('click', (e) => {
        notificationDropdown.classList.toggle('hidden');
        e.stopPropagation(); 
    });
    document.addEventListener('click', (e) => {
        if (!notificationDropdown.contains(e.target) && e.target.id !== 'notification-toggle-customer') {
            notificationDropdown.classList.add('hidden');
        }
    });

    // REMOVED: Old fetchTrainerPayments and updateTrainerPaymentsUI as they are now handled by renderCustomPayments
    // The previous logic for trainer payments was integrated into the new custom payments structure.
});

// Modal Logic (Membership - kept the same)
function openUpgradeModal(){document.getElementById('upgradeModal').classList.remove('hidden');}
function closeUpgradeModal(){document.getElementById('upgradeModal').classList.add('hidden');}

function openPaymentModal(id,name,price){
  closeUpgradeModal();
  const pay=document.getElementById('paymentModal');
  pay.classList.remove('hidden');
  // Set the hidden input for the backend
  document.getElementById('paymentPlanId').value=id; 
  // Display for the user
  document.getElementById('paymentPlanDisplay').value=`${name} Plan - ₱${price}`;
}
function closePaymentModal(){document.getElementById('paymentModal').classList.add('hidden');}
// =======================================================================================
// === End of JavaScript Fixes and Additions ===
// =======================================================================================
</script>
</body>
</html>