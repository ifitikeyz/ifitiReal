/**
 * Edit Profile JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  const bioTextarea = document.getElementById("bio")
  const bioCount = document.getElementById("bioCount")

  // Bio character counter
  if (bioTextarea && bioCount) {
    bioTextarea.addEventListener("input", function () {
      const length = this.value.length
      bioCount.textContent = length

      if (length > 140) {
        bioCount.style.color = "#ed4956"
      } else if (length > 120) {
        bioCount.style.color = "#ff9500"
      } else {
        bioCount.style.color = "#8e8e8e"
      }
    })
  }

  // Form validation
  const form = document.querySelector(".edit-profile-form")
  const inputs = form.querySelectorAll("input[required]")

  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      validateField(this)
    })

    input.addEventListener("input", function () {
      if (this.classList.contains("error")) {
        validateField(this)
      }
    })
  })

  function validateField(field) {
    const value = field.value.trim()

    // Remove existing error styling
    field.classList.remove("error")

    // Check if field is empty
    if (field.hasAttribute("required") && !value) {
      field.classList.add("error")
      return false
    }

    // Validate email
    if (field.type === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(value)) {
        field.classList.add("error")
        return false
      }
    }

    // Validate username
    if (field.name === "username" && value) {
      const usernameRegex = /^[a-zA-Z0-9_]+$/
      if (!usernameRegex.test(value)) {
        field.classList.add("error")
        return false
      }
    }

    return true
  }

  // Form submission
  form.addEventListener("submit", (e) => {
    let isValid = true

    inputs.forEach((input) => {
      if (!validateField(input)) {
        isValid = false
      }
    })

    if (!isValid) {
      e.preventDefault()
      alert("Please fix the errors in the form")
    }
  })
})

/**
 * Profile picture functions (shared with profile.js)
 */
function openProfilePictureModal() {
  const modal = document.getElementById("profilePictureModal")
  modal.classList.add("active")

  const fileInput = document.getElementById("profilePictureInput")
  fileInput.addEventListener("change", handleProfilePictureUpload)
}

function closeProfilePictureModal() {
  const modal = document.getElementById("profilePictureModal")
  modal.classList.remove("active")
}

async function handleProfilePictureUpload(event) {
  const file = event.target.files[0]
  if (!file) return

  if (!file.type.startsWith("image/")) {
    alert("Please select an image file")
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    alert("File size must be less than 5MB")
    return
  }

  const formData = new FormData()
  formData.append("profile_picture", file)

  try {
    const response = await fetch("api/upload_profile_picture.php", {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      const profilePictures = document.querySelectorAll(".edit-profile-picture, .nav-profile-pic")
      profilePictures.forEach((img) => {
        img.src = "uploads/profiles/" + result.filename + "?t=" + Date.now()
      })

      closeProfilePictureModal()
    } else {
      alert(result.message || "Upload failed")
    }
  } catch (error) {
    console.error("Error uploading profile picture:", error)
    alert("Upload failed. Please try again.")
  }
}

async function removeProfilePicture() {
  if (!confirm("Are you sure you want to remove your profile picture?")) {
    return
  }

  try {
    const response = await fetch("api/remove_profile_picture.php", {
      method: "POST",
    })

    const result = await response.json()

    if (result.success) {
      const profilePictures = document.querySelectorAll(".edit-profile-picture, .nav-profile-pic")
      profilePictures.forEach((img) => {
        img.src = "uploads/profiles/default-avatar.jpg?t=" + Date.now()
      })

      closeProfilePictureModal()
    } else {
      alert(result.message || "Failed to remove profile picture")
    }
  } catch (error) {
    console.error("Error removing profile picture:", error)
    alert("Failed to remove profile picture. Please try again.")
  }
}
