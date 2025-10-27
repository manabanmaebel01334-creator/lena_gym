<?php
session_start();
// Include the configuration file which is assumed to define the $pdo object
include_once('../../../config.php'); 

// ✅ FIX 1: ENFORCE USE OF SESSION USER ID INSTEAD OF FALLBACK
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect unauthenticated users
    header('Location: ../../index.php');
    exit();
}
$user_id = (int)$_SESSION['user_id']; 

// Set a timezone for accurate date/time comparisons
date_default_timezone_set('Asia/Manila');

// Initialize variables for displaying errors/conflicts
$error = null;
$trainer_conflict_data = null; 
$status_message = null; // NEW: Initialize status message variable

// -----------------------------------------------------------
// UTILITY FUNCTIONS (MOCK for Suggestion)
// -----------------------------------------------------------

/**
 * Utility to get trainer name from the fetched list.
 */
function getTrainerName($trainers, $trainer_id) {
    foreach($trainers as $trainer) {
        if ((int)$trainer['id'] === (int)$trainer_id) {
            return htmlspecialchars($trainer['name']);
        }
    }
    return 'The trainer';
}

/**
 * MOCK: Simulates finding the next 3 available 1-hour slots for a trainer.
 * In a live system, this would involve complex database logic to find schedule gaps.
 */
function findVacantSlots($pdo, $trainer_id, $failed_start_time) {
    $slots = [];
    $start = new DateTime($failed_start_time);
    $interval = new DateInterval('PT1H'); // 1 hour interval (session duration remains 1 hour)
    // FIX: Corrected for loop syntax: added $i to the increment part (i++)
    for ($i = 0; $i < 4; $i++) {
        $start->add($interval);
        // Add only future slots (skipping the one hour added above)
        if ($start > new DateTime(date('Y-m-d H:i:s'))) {
             $slots[] = $start->format('Y-m-d H:i');
             if (count($slots) >= 3) break;
        }
    }
    return $slots;
}


// -----------------------------------------------------------
// 1. Fetch Services
// -----------------------------------------------------------
try {
    $services_stmt = $pdo->query("SELECT service_id, name, description, price, is_class FROM services WHERE is_active = 1");
    $services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error fetching services: " . $e->getMessage();
    $services = [];
}

// -----------------------------------------------------------
// 2. Fetch Trainers
// -----------------------------------------------------------
try {
    $trainers_stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'trainer' AND id != :user_id");
    $trainers_stmt->execute([':user_id' => $user_id]);
    $trainers = $trainers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error fetching trainers: " . $e->getMessage();
    $trainers = [];
}

// -----------------------------------------------------------
// 3. Handle Form Submission Logic (MOCK/Conflict Check Only - Actual booking done via AJAX)
// -----------------------------------------------------------

// Check for existing booking (conflict simulation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'], $_POST['time'], $_POST['service_id'])) {
    
    // Get input data
    $service_id = filter_var($_POST['service_id'], FILTER_VALIDATE_INT);
    $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
    $time = filter_var($_POST['time'], FILTER_SANITIZE_STRING);
    $trainer_id = isset($_POST['trainer_id']) ? filter_var($_POST['trainer_id'], FILTER_VALIDATE_INT) : null;
    
    // UPDATED: Get height, weight, birth_date, and target_weight from POST data
    $height_cm = filter_var($_POST['height_cm'] ?? null, FILTER_VALIDATE_INT);
    $weight = filter_var($_POST['weight'] ?? null, FILTER_VALIDATE_FLOAT);
    $target_weight = filter_var($_POST['target_weight'] ?? null, FILTER_VALIDATE_FLOAT); // NEW
    $birth_date = filter_var($_POST['birth_date'] ?? null, FILTER_SANITIZE_STRING);
    
    // Construct the start time
    $start_time = $date . ' ' . $time . ':00';
    $start_datetime = new DateTime($start_time);
    
    // Determine the end time (assuming 1 hour duration for personal training/classes)
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT1H')); // Duration remains 1 hour
    $end_time = $end_datetime->format('Y-m-d H:i:s');

    // Determine if the selected service is a class
    $selected_service = array_filter($services, fn($s) => (int)$s['service_id'] === $service_id);
    $selected_service = $selected_service ? reset($selected_service) : null;
    $is_class = $selected_service ? (int)$selected_service['is_class'] : 0;
    
    // ---------------------------------------------------
    // Save client attributes to the 'users', 'user_metrics', and 'user_progress' tables
    // ---------------------------------------------------
    $insertion_success = false; 

    // Check if all necessary client data is present before attempting database operations
    if ($height_cm !== null && $birth_date && $weight !== null && $target_weight !== null) {
        try {
             // 1. Update the 'users' table 
             $update_user_stmt = $pdo->prepare("
                UPDATE users SET height_cm = :height_cm, birth_date = :birth_date WHERE id = :user_id
             ");
             $update_user_stmt->execute([
                 ':height_cm' => $height_cm,
                 ':birth_date' => $birth_date,
                 ':user_id' => $user_id
             ]);

             // 2. Insert or Update the 'user_metrics' table 
             $current_date = date('Y-m-d');
             $update_metrics_stmt = $pdo->prepare("
                 INSERT INTO user_metrics (user_id, metric_date, current_weight) 
                 VALUES (:user_id, :metric_date, :current_weight)
                 ON DUPLICATE KEY UPDATE 
                 current_weight = VALUES(current_weight)
             ");
             $update_metrics_stmt->execute([
                 ':user_id' => $user_id,
                 ':metric_date' => $current_date, 
                 ':current_weight' => $weight
             ]);

             // 3. Update or Insert 'user_progress'
             $check_progress_stmt = $pdo->prepare("SELECT id FROM user_progress WHERE user_id = :user_id");
             $check_progress_stmt->execute([':user_id' => $user_id]);
             if ($check_progress_stmt->fetch()) {
                 $update_progress_stmt = $pdo->prepare("
                     UPDATE user_progress SET 
                     target_weight = :target_weight, 
                     current_weight = :current_weight
                     WHERE user_id = :user_id
                 ");
                 $update_progress_stmt->execute([
                     ':target_weight' => $target_weight,
                     ':current_weight' => $weight,
                     ':user_id' => $user_id
                 ]);
             } else {
                 $insert_progress_stmt = $pdo->prepare("
                     INSERT INTO user_progress (user_id, target_weight, current_weight) 
                     VALUES (:user_id, :target_weight, :current_weight)
                 ");
                 $insert_progress_stmt->execute([
                     ':user_id' => $user_id,
                     ':target_weight' => $target_weight,
                     ':current_weight' => $weight
                 ]);
             }
             
             $insertion_success = true;
             $status_message = ['type' => 'success', 'message' => 'Your physical data (weight, height, age) has been successfully updated!'];

        } catch (PDOException $e) {
             error_log("Failed to update user metrics/progress: " . $e->getMessage());
             $status_message = ['type' => 'error', 'message' => 'Failed to save physical attributes due to a database error. Please try again.'];
        }

        if ($insertion_success) {
            // Clear the form inputs
            $height_cm = null;
            $weight = null;
            $target_weight = null;
            $birth_date = null;
        }
    }
    // ---------------------------------------------------
    
    // ---------------------------------------------------
    // Conflict Check Logic (Trainer/User Availability) - USES DATABASE
    // ---------------------------------------------------
    
    // Check for user's own time conflict
    $user_conflict_stmt = $pdo->prepare("
        SELECT booking_id, start_time, end_time FROM bookings 
        WHERE user_id = :user_id 
        AND status = 'Confirmed' 
        AND ((start_time < :end_time AND end_time > :start_time))
    ");
    $user_conflict_stmt->execute([
        ':user_id' => $user_id,
        ':start_time' => $start_time,
        ':end_time' => $end_time
    ]);
    if ($user_conflict_stmt->fetch(PDO::FETCH_ASSOC)) {
        $error = "You already have a confirmed booking or class scheduled for this time slot.";
    }

    // Check for trainer conflict (ONLY for non-class services)
    if (!$is_class && $trainer_id && !$error) {
        $trainer_conflict_stmt = $pdo->prepare("
            SELECT booking_id, user_id, start_time FROM bookings 
            WHERE trainer_id = :trainer_id 
            AND status = 'Confirmed' 
            AND ((start_time < :end_time AND end_time > :start_time))
        ");
        $trainer_conflict_stmt->execute([
            ':trainer_id' => $trainer_id,
            ':start_time' => $start_time,
            ':end_time' => $end_time
        ]);
        
        $trainer_conflict_data = $trainer_conflict_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($trainer_conflict_data) {
            $trainer_name = getTrainerName($trainers, $trainer_id);
            // This sets $error, which triggers the conflict message display and the button disable.
            $error = "$trainer_name is already booked for this time slot."; 
        }
    }
}


// -----------------------------------------------------------
// 4. HTML Structure for the Modal Content
// -----------------------------------------------------------
?>
<style>
    /* CSS to hide number input arrows (spinners) for a cleaner UI */
    /* For Chrome, Safari, Edge, Opera */
    .no-spinner::-webkit-outer-spin-button,
    .no-spinner::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* For Firefox */
    .no-spinner {
        -moz-appearance: textfield;
    }
</style>

<div class="space-y-4"> 
    
    <?php if ($error): ?>
        <div class="bg-error/20 border-l-4 border-error text-error p-4 mb-4 rounded-lg" role="alert">
            <p class="font-bold">Booking Conflict! <span class="material-symbols-outlined align-middle ml-1 text-lg">error</span></p>
            <p><?php echo htmlspecialchars($error); ?></p>
            
            <?php 
            // If it's a trainer conflict, suggest new times (MOCK)
            if (isset($trainer_id) && $trainer_conflict_data) {
                $suggested_slots = findVacantSlots($pdo, $trainer_id, $start_time ?? date('Y-m-d H:i:s'));
                if (!empty($suggested_slots)):
            ?>
                    <p class="mt-2 text-sm text-text">Suggested Next Available Slots for <?php echo getTrainerName($trainers, $trainer_id); ?>:</p>
                    <ul class="list-disc ml-5 mt-1 text-text-muted text-sm">
                        <?php foreach ($suggested_slots as $slot): ?>
                            <li><?php echo date('M d, g:i A', strtotime($slot)); ?></li>
                        <?php endforeach; ?>
                    </ul>
            <?php 
                endif;
            } 
            ?>
        </div>
    <?php endif; ?>

    <?php if ($status_message): 
        $alert_class = $status_message['type'] === 'success' ? 'bg-success/20 border-success text-success' : 'bg-error/20 border-error text-error';
        $icon = $status_message['type'] === 'success' ? 'check_circle' : 'error';
    ?>
        <div class="<?php echo $alert_class; ?> border-l-4 p-4 mb-4 rounded-lg" role="alert">
            <p class="font-bold"><?php echo ucfirst($status_message['type']); ?>! <span class="material-symbols-outlined align-middle ml-1 text-lg"><?php echo $icon; ?></span></p>
            <p><?php echo htmlspecialchars($status_message['message']); ?></p>
        </div>
    <?php endif; ?>
    
    <form id="bookingForm" method="POST" class="space-y-6"> <div>
            <label for="service_id" class="block text-sm font-medium text-text mb-1">Service</label>
            <select 
                id="service_id" 
                name="service_id" 
                class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                onchange="toggleTrainerField(this.value)"
                required
            >
                <option value="" class="bg-card">Select a Service...</option>
                <?php foreach ($services as $service): ?>
                    <option 
                        value="<?php echo htmlspecialchars($service['service_id']); ?>"
                        data-is-class="<?php echo htmlspecialchars($service['is_class']); ?>"
                        class="bg-card"
                        <?php echo (isset($_POST['service_id']) && (int)$_POST['service_id'] === (int)$service['service_id']) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($service['name']); ?> (<?php echo $service['is_class'] ? 'Class' : '1-on-1'; ?>) - ₱<?php echo number_format($service['price'], 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="trainer_field">
            <label for="trainer_id" class="block text-sm font-medium text-text mb-1">Trainer (for 1-on-1 sessions)</label>
            <select 
                id="trainer_id" 
                name="trainer_id" 
                class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
            >
                <option value="" class="bg-card">Any Available Trainer</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option 
                        value="<?php echo htmlspecialchars($trainer['id']); ?>"
                        class="bg-card"
                        <?php echo (isset($_POST['trainer_id']) && (int)$_POST['trainer_id'] === (int)$trainer['id']) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($trainer['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="height_cm" class="block text-sm font-medium text-text mb-1">Height (cm)</label>
                    <input 
                        type="number" 
                        id="height_cm" 
                        name="height_cm" 
                        min="50" 
                        max="300" 
                        placeholder="e.g., 175"
                        value="<?php echo htmlspecialchars($height_cm ?? ''); ?>"
                        class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm no-spinner"
                        required
                    />
                </div>
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-text mb-1">Date of Birth</label>
                    <input 
                        type="date" 
                        id="birth_date" 
                        name="birth_date" 
                        max="<?php echo date('Y-m-d'); ?>" 
                        value="<?php echo htmlspecialchars($birth_date ?? ''); ?>"
                        class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm"
                        required
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="weight" class="block text-sm font-medium text-text mb-1">Current Weight (kg)</label>
                    <input 
                        type="number" 
                        id="weight" 
                        name="weight" 
                        min="20" 
                        max="500" 
                        step="0.1" 
                        placeholder="e.g., 75.5"
                        value="<?php echo htmlspecialchars($weight ?? ''); ?>"
                        class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm no-spinner"
                        required
                    />
                </div>
                <div>
                    <label for="target_weight" class="block text-sm font-medium text-text mb-1">Target Weight (kg)</label>
                    <input 
                        type="number" 
                        id="target_weight" 
                        name="target_weight" 
                        min="20" 
                        max="500" 
                        step="0.1" 
                        placeholder="e.g., 70.0"
                        value="<?php echo htmlspecialchars($target_weight ?? ''); ?>"
                        class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm no-spinner"
                        required
                    />
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-text mb-1">Date</label>
                <input 
                    type="date" 
                    id="date" 
                    name="date" 
                    min="<?php echo date('Y-m-d'); ?>" 
                    value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>"
                    class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm input-icon-fix"
                    required
                />
            </div>
            <div>
                <label for="time" class="block text-sm font-medium text-text mb-1">Time (5 Minute Slots)</label>
                <input 
                    type="time" 
                    id="time" 
                    name="time" 
                    step="300" 
                    value="<?php echo htmlspecialchars($_POST['time'] ?? '10:00'); ?>"
                    class="block w-full bg-accent border border-primary/30 text-text rounded-lg p-3 focus:ring-primary focus:border-primary sm:text-sm input-icon-fix"
                    required
                />
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t border-accent/70">
            <button type="button" onclick="closeModal('newBookingModal')" 
                class="bg-accent border border-text-muted/30 px-4 py-3 rounded-full font-medium text-text-muted hover:border-text transition duration-300">
                Cancel
            </button>
            
            <?php if ($error): ?>
                <button type="button" disabled
                    class="bg-accent/50 text-text-muted/70 px-4 py-3 rounded-full font-bold cursor-not-allowed"
                    title="A conflict prevents booking. Please select a new time/trainer.">
                    Booking Blocked
                 </button>
            <?php else: ?>
                <button type="submit" class="bg-primary px-4 py-3 rounded-full font-bold text-sidebar transition duration-300 hover:shadow-neon-button" id="proceedToConfirmation">
                    Proceed to Confirmation
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>
    
<script>
    // JavaScript to show/hide trainer field based on service type
    const serviceSelect = document.getElementById('service_id');
    const trainerField = document.getElementById('trainer_field');
    const trainerSelect = document.getElementById('trainer_id');
    
    function toggleTrainerField(serviceId) {
        const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
        // Check if the selected service is a Class (data-is-class="1")
        const isClass = selectedOption && selectedOption.getAttribute('data-is-class') === '1';

        if (isClass) {
            trainerField.classList.add('hidden');
            trainerSelect.value = ''; // Clear value for class bookings
        } else {
            trainerField.classList.remove('hidden');
        }
    }

    // Call on load
    toggleTrainerField(serviceSelect.value);

    // Re-attach listener for trainer field visibility when service changes
    serviceSelect.addEventListener('change', () => toggleTrainerField(serviceSelect.value));
</script>
<?php 
    // This is the end of the content loaded into the modal.
    // The rest of the page (booking.php) handles the AJAX submission.
?>