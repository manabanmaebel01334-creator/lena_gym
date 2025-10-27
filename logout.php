<?php
// logout.php

// 1. Start the session to access $_SESSION variables
session_start();

// 2. Destroy the session: This removes all data stored for the current session.
session_destroy();

// 3. Clear the session cookie: This ensures the browser forgets the session ID.
// Note: This is an optional but robust step.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Redirect the user back to the homepage (index.php) or login page
header('Location: index.php');
exit;
?>