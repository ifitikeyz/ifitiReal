/**
 * Change Password JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  const passwordForm = document.getElementById("passwordForm")
  const currentPasswordInput = document.getElementById("current_password")
  const newPasswordInput = document.getElementById("new_password")
  const confirmPasswordInput = document.getElementById("confirm_password")
  const submitBtn = document.getElementById("submitBtn")

  // Password strength elements
  const strengthIndicator = document.getElementById("passwordStrength")
  const strengthFill = document.getElementById("strengthFill")
  const strengthText = document.getElementById("strengthText")
  const strengthScore = document.getElementById("strengthScore")
  const requirements = document.getElementById("passwordRequirements")
  const passwordMatch = document.getElementById("passwordMatch")

  // Requirement elements
  const reqLength = document.getElementById("req-length")
  const reqLowercase = document.getElementById("req-lowercase")
  const reqUppercase = document.getElementById("req-uppercase")
  const reqNumber = document.getElementById("req-number")
  const reqSpecial = document.getElementById("req-special")

  // Password strength checking
  newPasswordInput.addEventListener("input", function () {
    const password = this.value
    checkPasswordStrength(password)
    validatePasswordMatch()
    validateForm()
  })

  // Password confirmation checking
  confirmPasswordInput.addEventListener("input", () => {
    validatePasswordMatch()
    validateForm()
  })

  // Current password validation
  currentPasswordInput.addEventListener("input", () => {
    validateForm()
  })

  /**
   * Check password strength and update UI
   */
  function checkPasswordStrength(password) {
    if (password.length === 0) {
      strengthIndicator.style.display = "none"
      requirements.style.display = "none"
      return
    }

    strengthIndicator.style.display = "block"
    requirements.style.display = "block"

    let score = 0
    let strengthLevel = "Very Weak"
    let strengthColor = "#ff4757"

    // Check length
    if (password.length >= 8) {
      score++
      reqLength.classList.add("met")
      reqLength.querySelector("i").className = "fas fa-check"
    } else {
      reqLength.classList.remove("met")
      reqLength.querySelector("i").className = "fas fa-times"
    }

    // Check lowercase
    if (password.match(/[a-z]/)) {
      score++
      reqLowercase.classList.add("met")
      reqLowercase.querySelector("i").className = "fas fa-check"
    } else {
      reqLowercase.classList.remove("met")
      reqLowercase.querySelector("i").className = "fas fa-times"
    }

    // Check uppercase
    if (password.match(/[A-Z]/)) {
      score++
      reqUppercase.classList.add("met")
      reqUppercase.querySelector("i").className = "fas fa-check"
    } else {
      reqUppercase.classList.remove("met")
      reqUppercase.querySelector("i").className = "fas fa-times"
    }

    // Check numbers
    if (password.match(/[0-9]/)) {
      score++
      reqNumber.classList.add("met")
      reqNumber.querySelector("i").className = "fas fa-check"
    } else {
      reqNumber.classList.remove("met")
      reqNumber.querySelector("i").className = "fas fa-times"
    }

    // Check special characters
    if (password.match(/[^a-zA-Z0-9]/)) {
      score++
      reqSpecial.classList.add("met")
      reqSpecial.querySelector("i").className = "fas fa-check"
    } else {
      reqSpecial.classList.remove("met")
      reqSpecial.querySelector("i").className = "fas fa-times"
    }

    // Determine strength level and color
    switch (score) {
      case 0:
      case 1:
        strengthLevel = "Very Weak"
        strengthColor = "#ff4757"
        break
      case 2:
        strengthLevel = "Weak"
        strengthColor = "#ff6b7a"
        break
      case 3:
        strengthLevel = "Fair"
        strengthColor = "#ffa502"
        break
      case 4:
        strengthLevel = "Good"
        strengthColor = "#2ed573"
        break
      case 5:
        strengthLevel = "Strong"
        strengthColor = "#1dd1a1"
        break
    }

    // Check for common passwords
    const commonPasswords = [
      "password",
      "123456",
      "123456789",
      "qwerty",
      "abc123",
      "password123",
      "admin",
      "letmein",
      "welcome",
      "monkey",
    ]

    if (commonPasswords.includes(password.toLowerCase())) {
      score = Math.max(0, score - 2)
      strengthLevel = "Too Common"
      strengthColor = "#ff4757"
    }

    // Update UI
    strengthFill.style.width = score * 20 + "%"
    strengthFill.style.backgroundColor = strengthColor
    strengthText.textContent = strengthLevel
    strengthText.style.color = strengthColor
    strengthScore.textContent = `${score}/5`

    // Update input border color
    if (score >= 3) {
      newPasswordInput.classList.remove("error")
      newPasswordInput.classList.add("success")
    } else if (password.length > 0) {
      newPasswordInput.classList.add("error")
      newPasswordInput.classList.remove("success")
    } else {
      newPasswordInput.classList.remove("error", "success")
    }

    return score
  }

  /**
   * Validate password confirmation
   */
  function validatePasswordMatch() {
    const newPassword = newPasswordInput.value
    const confirmPassword = confirmPasswordInput.value

    if (confirmPassword.length === 0) {
      passwordMatch.style.display = "none"
      confirmPasswordInput.classList.remove("error", "success")
      return true
    }

    passwordMatch.style.display = "flex"

    if (newPassword === confirmPassword) {
      passwordMatch.classList.add("success")
      passwordMatch.classList.remove("error")
      passwordMatch.innerHTML = '<i class="fas fa-check"></i><span>Passwords match</span>'
      confirmPasswordInput.classList.remove("error")
      confirmPasswordInput.classList.add("success")
      return true
    } else {
      passwordMatch.classList.remove("success")
      passwordMatch.classList.add("error")
      passwordMatch.innerHTML = '<i class="fas fa-times"></i><span>Passwords do not match</span>'
      confirmPasswordInput.classList.add("error")
      confirmPasswordInput.classList.remove("success")
      return false
    }
  }

  /**
   * Validate entire form
   */
  function validateForm() {
    const currentPassword = currentPasswordInput.value.trim()
    const newPassword = newPasswordInput.value
    const confirmPassword = confirmPasswordInput.value

    const hasCurrentPassword = currentPassword.length > 0
    const hasNewPassword = newPassword.length >= 8
    const passwordsMatch = newPassword === confirmPassword && confirmPassword.length > 0
    const passwordStrong = checkPasswordStrength(newPassword) >= 3

    const isValid = hasCurrentPassword && hasNewPassword && passwordsMatch && passwordStrong

    submitBtn.disabled = !isValid
    submitBtn.style.opacity = isValid ? "1" : "0.6"

    return isValid
  }

  // Form submission
  passwordForm.addEventListener("submit", (e) => {
    if (!validateForm()) {
      e.preventDefault()
      alert("Please fix the errors in the form before submitting")
      return
    }

    // Show loading state
    submitBtn.innerHTML = '<div class="spinner"></div> Changing Password...'
    submitBtn.disabled = true
  })

  // Input focus/blur effects
  const inputs = document.querySelectorAll(".password-input")
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.style.borderColor = "#0095f6"
      this.style.backgroundColor = "#ffffff"
    })

    input.addEventListener("blur", function () {
      if (!this.classList.contains("error") && !this.classList.contains("success")) {
        this.style.borderColor = "#dbdbdb"
        this.style.backgroundColor = "#fafafa"
      }
    })
  })

  // Initial validation
  validateForm()
})

/**
 * Toggle password visibility
 */
function togglePassword(inputId) {
  const input = document.getElementById(inputId)
  const button = input.nextElementSibling
  const icon = button.querySelector("i")

  if (input.type === "password") {
    input.type = "text"
    icon.className = "fas fa-eye-slash"
  } else {
    input.type = "password"
    icon.className = "far fa-eye"
  }
}

/**
 * Generate secure password suggestion
 */
function generateSecurePassword() {
  const length = 12
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*"
  let password = ""

  // Ensure at least one character from each required set
  password += "abcdefghijklmnopqrstuvwxyz"[Math.floor(Math.random() * 26)]
  password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 26)]
  password += "0123456789"[Math.floor(Math.random() * 10)]
  password += "!@#$%^&*"[Math.floor(Math.random() * 8)]

  // Fill the rest randomly
  for (let i = password.length; i < length; i++) {
    password += charset[Math.floor(Math.random() * charset.length)]
  }

  // Shuffle the password
  return password
    .split("")
    .sort(() => Math.random() - 0.5)
    .join("")
}
