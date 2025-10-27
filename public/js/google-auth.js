/**
 * google-auth.js
 * Handles Google Sign-In button initialization and response handling
 * Manages the flow: verify -> existing user (OTP) or new user (signup with pre-fill)
 */

// Declare necessary variables
let google
let showNotification
let switchTab

// Initialize Google Sign-In
function initGoogleSignIn() {
  const clientId =
    document.querySelector('meta[name="google-client-id"]')?.content ||
    '<?php echo defined("GOOGLE_CLIENT_ID") ? GOOGLE_CLIENT_ID : "YOUR_GOOGLE_CLIENT_ID"; ?>'

  google.accounts.id.initialize({
    client_id: clientId,
    callback: handleGoogleCredentialResponse,
  })

  // Render button for Register tab
  const registerBtnContainer = document.getElementById("google-register-btn")
  if (registerBtnContainer) {
    google.accounts.id.renderButton(registerBtnContainer, {
      theme: "filled_black",
      size: "large",
      text: "continue_with",
      shape: "pill",
      width: "100%",
    })
  }

  // Render button for Login tab
  const loginBtnContainer = document.getElementById("google-login-btn")
  if (loginBtnContainer) {
    google.accounts.id.renderButton(loginBtnContainer, {
      theme: "filled_black",
      size: "large",
      text: "signin_with",
      shape: "pill",
      width: "100%",
    })
  }

  // Optional: Show One-Tap prompt
  google.accounts.id.prompt()
}

// Handle Google credential response
async function handleGoogleCredentialResponse(response) {
  console.log("[v0] Google Sign-In response received")

  const token = response.credential
  const activeTab = document.querySelector(".tab-content.active")?.id || "login-tab"
  const notificationId = activeTab === "register-tab" ? "register-notification" : "login-notification"

  showNotification(notificationId, "Verifying your Google account...", false)

  try {
    const verifyResponse = await fetch("auth/google-login-handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token: token }),
    })

    const result = await verifyResponse.json()
    console.log("[v0] Google verification result:", result)

    if (result.success) {
      if (result.redirect_to_otp_login) {
        // Existing user - redirect to OTP verification
        console.log("[v0] Existing user detected, redirecting to OTP")
        showNotification(notificationId, result.message, true)
        setTimeout(() => {
          window.location.href = "otp_dashboard.php"
        }, 800)
      } else if (result.action === "signup" && result.google_data) {
        // New user - show signup form with pre-filled data
        console.log("[v0] New user detected, showing signup form with pre-filled data")
        handleNewGoogleUser(result.google_data)
      }
    } else {
      showNotification(notificationId, result.message || "Google verification failed", false)
    }
  } catch (error) {
    console.error("[v0] Google authentication error:", error)
    showNotification(notificationId, "An error occurred during Google authentication", false)
  }
}

// Handle new Google user - switch to signup and pre-fill form
function handleNewGoogleUser(googleData) {
  console.log("[v0] Handling new Google user:", googleData)

  // Switch to register tab
  const registerBtn = document.querySelector('.tab-btn[data-tab-name="register"]')
  switchTab("register", registerBtn)

  // Wait for DOM to update, then pre-fill the form
  setTimeout(() => {
    prefilGoogleSignupForm(googleData)
  }, 300)
}

// Pre-fill signup form with Google data
function prefilGoogleSignupForm(googleData) {
  console.log("[v0] Pre-filling signup form with Google data")

  const emailInput = document.getElementById("register-email")
  const nameInput = document.getElementById("register-name")
  const passwordInput = document.getElementById("register-password")
  const rePasswordInput = document.getElementById("register-re-password")
  const providerHidden = document.getElementById("register-provider-id-hidden")

  // Pre-fill email (read-only)
  if (emailInput) {
    emailInput.value = googleData.email
    emailInput.readOnly = true
    emailInput.style.background = "rgba(255, 255, 255, 0.7)"
    emailInput.style.cursor = "default"
    emailInput.title = "Your Google account email - cannot be changed"
    console.log("[v0] Email pre-filled:", googleData.email)
  }

  // Pre-fill name (editable)
  if (nameInput) {
    nameInput.value = googleData.name
    nameInput.focus()
    console.log("[v0] Name pre-filled:", googleData.name)
  }

  // Clear password fields
  if (passwordInput) {
    passwordInput.value = ""
    passwordInput.placeholder = "Create a password for your account"
  }
  if (rePasswordInput) {
    rePasswordInput.value = ""
    rePasswordInput.placeholder = "Confirm your password"
  }

  // Store provider ID
  if (providerHidden) {
    providerHidden.value = googleData.provider_id
  }

  // Show success notification
  showNotification(
    "register-notification",
    "Welcome! Your Google account details have been pre-filled. Please create a password to complete your registration.",
    true,
  )
}

// Export functions for use in main login.php
window.initGoogleSignIn = initGoogleSignIn
window.handleGoogleCredentialResponse = handleGoogleCredentialResponse
