/**
 * Authentication JavaScript for ifiti
 */

document.addEventListener("DOMContentLoaded", () => {
  initializeAuthForms()
})

/**
 * Initialize authentication forms
 */
function initializeAuthForms() {
  const loginForm = document.querySelector("#loginForm, .auth-form")
  const registerForm = document.getElementById("registerForm")

  if (loginForm && !registerForm) {
    initializeLoginForm(loginForm)
  }

  if (registerForm) {
    initializeRegisterForm(registerForm)
  }
}

/**
 * Initialize login form
 */
function initializeLoginForm(form) {
  const submitButton = form.querySelector(".auth-button")
  const inputs = form.querySelectorAll(".form-input")

  // Add input event listeners
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      validateLoginForm(form)
    })

    input.addEventListener("focus", function () {
      this.style.borderColor = "var(--accent-primary)"
    })

    input.addEventListener("blur", function () {
      this.style.borderColor = "var(--border-color)"
    })
  })

  // Handle form submission
  form.addEventListener("submit", (e) => {
    if (submitButton) {
      submitButton.innerHTML = '<div class="spinner"></div> Logging in...'
      submitButton.disabled = true
    }
  })

  // Initial validation
  validateLoginForm(form)
}

/**
 * Initialize register form
 */
function initializeRegisterForm(form) {
  const submitButton = document.getElementById("submitBtn")
  const passwordInput = document.getElementById("password")
  const confirmPasswordInput = document.getElementById("confirm_password")
  const inputs = form.querySelectorAll(".form-input")

  // Add input event listeners
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      validateRegisterForm(form)
    })

    input.addEventListener("focus", function () {
      this.style.borderColor = "var(--accent-primary)"
    })

    input.addEventListener("blur", function () {
      this.style.borderColor = "var(--border-color)"
    })
  })

  // Password strength checking
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      checkPasswordStrength(this.value)
      validatePasswordMatch()
    })
  }

  // Confirm password checking
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", () => {
      validatePasswordMatch()
    })
  }

  // Username validation
  const usernameInput = document.getElementById("username")
  if (usernameInput) {
    usernameInput.addEventListener("input", function () {
      validateUsername(this.value)
    })
  }

  // Handle form submission
  form.addEventListener("submit", (e) => {
    if (submitButton) {
      submitButton.innerHTML = '<div class="spinner"></div> Creating Account...'
      submitButton.disabled = true
    }
  })

  // Initial validation
  validateRegisterForm(form)
}

/**
 * Validate login form
 */
function validateLoginForm(form) {
  const submitButton = form.querySelector(".auth-button")
  const inputs = form.querySelectorAll(".form-input[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (input.value.trim() === "") {
      isValid = false
    }
  })

  if (submitButton) {
    submitButton.disabled = !isValid
    submitButton.style.opacity = isValid ? "1" : "0.6"
  }
}

/**
 * Validate register form
 */
function validateRegisterForm(form) {
  const submitButton = document.getElementById("submitBtn")
  const requiredInputs = form.querySelectorAll(".form-input[required]")
  const passwordInput = document.getElementById("password")
  const confirmPasswordInput = document.getElementById("confirm_password")

  let isValid = true

  // Check required fields
  requiredInputs.forEach((input) => {
    if (input.value.trim() === "") {
      isValid = false
    }
  })

  // Check password match
  if (passwordInput && confirmPasswordInput) {
    if (passwordInput.value !== confirmPasswordInput.value) {
      isValid = false
    }

    if (passwordInput.value.length < 8) {
      isValid = false
    }
  }

  // Check username format
  const usernameInput = document.getElementById("username")
  if (usernameInput && usernameInput.value) {
    const isValidUsername = /^[a-zA-Z0-9_]+$/.test(usernameInput.value)
    if (!isValidUsername) {
      isValid = false
    }
  }

  if (submitButton) {
    submitButton.disabled = !isValid
    submitButton.style.opacity = isValid ? "1" : "0.6"
  }
}

/**
 * Check password strength
 */
function checkPasswordStrength(password) {
  // This could be enhanced with a visual password strength indicator
  const minLength = password.length >= 8
  const hasLower = /[a-z]/.test(password)
  const hasUpper = /[A-Z]/.test(password)
  const hasNumber = /[0-9]/.test(password)
  const hasSpecial = /[^a-zA-Z0-9]/.test(password)

  const score = [minLength, hasLower, hasUpper, hasNumber, hasSpecial].filter(Boolean).length

  // Could add visual feedback here
  console.log("Password strength score:", score, "/5")
}

/**
 * Validate password match
 */
function validatePasswordMatch() {
  const passwordInput = document.getElementById("password")
  const confirmPasswordInput = document.getElementById("confirm_password")

  if (passwordInput && confirmPasswordInput && confirmPasswordInput.value) {
    const isMatch = passwordInput.value === confirmPasswordInput.value

    confirmPasswordInput.style.borderColor = isMatch ? "var(--success-color)" : "var(--error-color)"
  }
}

/**
 * Validate username format
 */
function validateUsername(username) {
  const usernameInput = document.getElementById("username")

  if (usernameInput && username) {
    const isValid = /^[a-zA-Z0-9_]+$/.test(username)

    usernameInput.style.borderColor = isValid ? "var(--border-color)" : "var(--error-color)"
  }
}
