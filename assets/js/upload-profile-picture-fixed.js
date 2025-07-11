/**
 * Fixed Upload Profile Picture JavaScript
 * Enhanced error handling and debugging
 */

let selectedFile = null
let cropper = null

// Import Cropper.js
const Cropper = window.Cropper

document.addEventListener("DOMContentLoaded", () => {
  const dragDropArea = document.getElementById("dragDropArea")
  const fileInput = document.getElementById("fileInput")
  const previewSection = document.getElementById("previewSection")
  const previewImage = document.getElementById("previewImage")
  const uploadForm = document.getElementById("uploadForm")
  const uploadBtn = document.getElementById("uploadBtn")

  // Check if elements exist
  if (!dragDropArea || !fileInput) {
    console.error("Required elements not found")
    return
  }

  // Drag and drop functionality
  dragDropArea.addEventListener("click", () => fileInput.click())
  dragDropArea.addEventListener("dragover", handleDragOver)
  dragDropArea.addEventListener("dragleave", handleDragLeave)
  dragDropArea.addEventListener("drop", handleDrop)

  // File input handler
  fileInput.addEventListener("change", (e) => {
    if (e.target.files.length > 0) {
      handleFileSelect(e.target.files[0])
    }
  })

  // Form submission
  if (uploadForm) {
    uploadForm.addEventListener("submit", handleFormSubmit)
  }

  /**
   * Handle drag over event
   */
  function handleDragOver(e) {
    e.preventDefault()
    dragDropArea.classList.add("dragover")
  }

  /**
   * Handle drag leave event
   */
  function handleDragLeave(e) {
    e.preventDefault()
    dragDropArea.classList.remove("dragover")
  }

  /**
   * Handle drop event
   */
  function handleDrop(e) {
    e.preventDefault()
    dragDropArea.classList.remove("dragover")

    const files = e.dataTransfer.files
    if (files.length > 0) {
      handleFileSelect(files[0])
    }
  }

  /**
   * Handle file selection with validation
   */
  function handleFileSelect(file) {
    console.log("File selected:", file)

    if (!file) {
      showError("No file selected")
      return
    }

    // Validate file type
    const allowedTypes = ["image/jpeg", "image/png", "image/gif"]
    if (!allowedTypes.includes(file.type)) {
      showError("Only JPEG, PNG, and GIF files are allowed")
      return
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      showError("File size must be less than 5MB")
      return
    }

    selectedFile = file
    loadImagePreview(file)
  }

  /**
   * Load image preview
   */
  function loadImagePreview(file) {
    const reader = new FileReader()

    reader.onload = (e) => {
      if (previewImage && previewSection) {
        previewImage.src = e.target.result
        previewSection.style.display = "block"

        // Scroll to preview
        previewSection.scrollIntoView({ behavior: "smooth" })

        // Initialize cropper if available
        if (Cropper) {
          initializeCropper()
        } else {
          console.warn("Cropper.js not loaded, using simple preview")
        }
      }
    }

    reader.onerror = () => {
      showError("Failed to read file")
    }

    reader.readAsDataURL(file)
  }

  /**
   * Initialize cropper (if available)
   */
  function initializeCropper() {
    if (cropper) {
      cropper.destroy()
    }

    try {
      cropper = new Cropper(previewImage, {
        aspectRatio: 1,
        viewMode: 1,
        dragMode: "move",
        autoCropArea: 0.8,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        minCropBoxWidth: 50,
        minCropBoxHeight: 50,
      })
    } catch (error) {
      console.error("Failed to initialize cropper:", error)
    }
  }

  /**
   * Handle form submission with better error handling
   */
  async function handleFormSubmit(e) {
    e.preventDefault()

    if (!selectedFile) {
      showError("Please select an image first")
      return
    }

    // Show loading state
    if (uploadBtn) {
      uploadBtn.innerHTML = '<div class="spinner"></div> Uploading...'
      uploadBtn.disabled = true
    }

    try {
      let fileToUpload = selectedFile

      // If cropper is available, get cropped image
      if (cropper) {
        try {
          const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            minWidth: 50,
            minHeight: 50,
            maxWidth: 1000,
            maxHeight: 1000,
            fillColor: "#fff",
            imageSmoothingEnabled: true,
            imageSmoothingQuality: "high",
          })

          // Convert to blob
          const blob = await new Promise((resolve) => {
            canvas.toBlob(resolve, selectedFile.type, 0.9)
          })

          if (blob) {
            fileToUpload = new File([blob], selectedFile.name, {
              type: selectedFile.type,
            })
          }
        } catch (cropError) {
          console.warn("Cropping failed, using original file:", cropError)
        }
      }

      // Create form data
      const formData = new FormData()
      formData.append("profile_picture", fileToUpload)

      console.log("Uploading file:", fileToUpload)

      // Upload the image
      const response = await fetch("api/upload_profile_picture_fixed.php", {
        method: "POST",
        body: formData,
      })

      console.log("Response status:", response.status)

      // Check if response is JSON
      const contentType = response.headers.get("content-type")
      if (!contentType || !contentType.includes("application/json")) {
        const text = await response.text()
        console.error("Non-JSON response:", text)
        throw new Error("Server returned invalid response")
      }

      const result = await response.json()
      console.log("Upload result:", result)

      if (result.success) {
        showSuccess(result.message)
        updateProfilePictures(result.filename)

        // Redirect after success
        setTimeout(() => {
          window.location.href = "profile.php"
        }, 2000)
      } else {
        showError(result.message || "Upload failed")
        if (result.debug) {
          console.log("Debug info:", result.debug)
        }
      }
    } catch (error) {
      console.error("Upload error:", error)
      showError("Upload failed: " + error.message)
    }

    // Reset button
    if (uploadBtn) {
      uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Picture'
      uploadBtn.disabled = false
    }
  }

  /**
   * Update profile pictures in the page
   */
  function updateProfilePictures(filename) {
    const timestamp = Date.now()
    const profilePictures = document.querySelectorAll(".current-picture, .nav-profile-pic")

    profilePictures.forEach((img) => {
      img.src = `uploads/profiles/${filename}?t=${timestamp}`
    })
  }

  /**
   * Show error message
   */
  function showError(message) {
    removeExistingMessages()
    const errorDiv = document.createElement("div")
    errorDiv.className = "message error"
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`

    const header = document.querySelector(".upload-header")
    if (header) {
      header.after(errorDiv)
    } else {
      document.body.prepend(errorDiv)
    }

    // Auto-remove after 5 seconds
    setTimeout(() => errorDiv.remove(), 5000)
  }

  /**
   * Show success message
   */
  function showSuccess(message) {
    removeExistingMessages()
    const successDiv = document.createElement("div")
    successDiv.className = "message success"
    successDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`

    const header = document.querySelector(".upload-header")
    if (header) {
      header.after(successDiv)
    } else {
      document.body.prepend(successDiv)
    }
  }

  /**
   * Remove existing messages
   */
  function removeExistingMessages() {
    const existingMessages = document.querySelectorAll(".message")
    existingMessages.forEach((msg) => msg.remove())
  }
})

/**
 * Cancel upload
 */
function cancelUpload() {
  if (cropper) {
    cropper.destroy()
    cropper = null
  }

  selectedFile = null
  const previewSection = document.getElementById("previewSection")
  if (previewSection) {
    previewSection.style.display = "none"
  }

  const fileInput = document.getElementById("fileInput")
  if (fileInput) {
    fileInput.value = ""
  }

  removeExistingMessages()
}

/**
 * Reset crop
 */
function resetCrop() {
  if (cropper) {
    cropper.reset()
  }
}

/**
 * Rotate crop
 */
function rotateCrop(degrees) {
  if (cropper) {
    cropper.rotate(degrees)
  }
}

/**
 * Remove existing messages
 */
function removeExistingMessages() {
  const existingMessages = document.querySelectorAll(".message")
  existingMessages.forEach((msg) => msg.remove())
}
