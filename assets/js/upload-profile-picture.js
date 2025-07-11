/**
 * Upload Profile Picture JavaScript
 */

let cropper = null
let selectedFile = null

// Import Cropper.js
const Cropper = window.Cropper

document.addEventListener("DOMContentLoaded", () => {
  const dragDropArea = document.getElementById("dragDropArea")
  const cameraUpload = document.getElementById("cameraUpload")
  const fileInput = document.getElementById("fileInput")
  const cameraInput = document.getElementById("cameraInput")
  const previewSection = document.getElementById("previewSection")
  const previewImage = document.getElementById("previewImage")
  const uploadForm = document.getElementById("uploadForm")
  const uploadBtn = document.getElementById("uploadBtn")

  // Drag and drop functionality
  dragDropArea.addEventListener("click", () => fileInput.click())
  dragDropArea.addEventListener("dragover", handleDragOver)
  dragDropArea.addEventListener("dragleave", handleDragLeave)
  dragDropArea.addEventListener("drop", handleDrop)

  // Camera upload (mobile)
  if (cameraUpload) {
    cameraUpload.addEventListener("click", () => cameraInput.click())
  }

  // File input handlers
  fileInput.addEventListener("change", (e) => handleFileSelect(e.target.files[0]))
  if (cameraInput) {
    cameraInput.addEventListener("change", (e) => handleFileSelect(e.target.files[0]))
  }

  // Form submission
  uploadForm.addEventListener("submit", handleFormSubmit)

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
   * Handle file selection
   */
  function handleFileSelect(file) {
    if (!file) return

    // Validate file type
    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"]
    if (!allowedTypes.includes(file.type)) {
      showError("Only JPEG, PNG, GIF, and WebP files are allowed")
      return
    }

    // Validate file size (10MB)
    if (file.size > 10 * 1024 * 1024) {
      showError("File size must be less than 10MB")
      return
    }

    selectedFile = file
    loadImagePreview(file)
  }

  /**
   * Load image preview and initialize cropper
   */
  function loadImagePreview(file) {
    const reader = new FileReader()

    reader.onload = (e) => {
      previewImage.src = e.target.result
      previewSection.style.display = "block"

      // Scroll to preview section
      previewSection.scrollIntoView({ behavior: "smooth" })

      // Initialize cropper after image loads
      previewImage.onload = () => {
        initializeCropper()
      }
    }

    reader.readAsDataURL(file)
  }

  /**
   * Initialize cropper.js
   */
  function initializeCropper() {
    // Destroy existing cropper
    if (cropper) {
      cropper.destroy()
    }

    cropper = new Cropper(previewImage, {
      aspectRatio: 1, // Square crop
      viewMode: 1,
      dragMode: "move",
      autoCropArea: 0.8,
      restore: false,
      guides: true,
      center: true,
      highlight: false,
      cropBoxMovable: true,
      cropBoxResizable: true,
      toggleDragModeOnDblclick: false,
      minCropBoxWidth: 150,
      minCropBoxHeight: 150,
      ready() {
        // Cropper is ready
        console.log("Cropper initialized")
      },
      crop(event) {
        // Update crop data
        const cropData = cropper.getCropBoxData()
        console.log("Crop data:", cropData)
      },
    })
  }

  /**
   * Handle form submission
   */
  async function handleFormSubmit(e) {
    e.preventDefault()

    if (!selectedFile || !cropper) {
      showError("Please select an image first")
      return
    }

    // Show loading state
    uploadBtn.innerHTML = '<div class="spinner"></div> Uploading...'
    uploadBtn.disabled = true

    try {
      // Get cropped canvas
      const canvas = cropper.getCroppedCanvas({
        width: 300,
        height: 300,
        minWidth: 150,
        minHeight: 150,
        maxWidth: 1000,
        maxHeight: 1000,
        fillColor: "#fff",
        imageSmoothingEnabled: true,
        imageSmoothingQuality: "high",
      })

      // Convert canvas to blob
      canvas.toBlob(async (blob) => {
        if (!blob) {
          showError("Failed to process image")
          resetUploadButton()
          return
        }

        // Create form data
        const formData = new FormData()
        formData.append("profile_picture", blob, selectedFile.name)
        formData.append("upload_type", "upload")

        try {
          // Upload the image
          const response = await fetch("api/upload_profile_picture.php", {
            method: "POST",
            body: formData,
          })

          const result = await response.json()

          if (result.success) {
            showSuccess(result.message)
            // Update profile pictures in the page
            updateProfilePictures(result.filename)
            // Reset form after delay
            setTimeout(() => {
              window.location.href = "profile.php?username=" + getCurrentUsername()
            }, 2000)
          } else {
            showError(result.message || "Upload failed")
          }
        } catch (error) {
          console.error("Upload error:", error)
          showError("Upload failed. Please try again.")
        }

        resetUploadButton()
      }, selectedFile.type)
    } catch (error) {
      console.error("Processing error:", error)
      showError("Failed to process image")
      resetUploadButton()
    }
  }

  /**
   * Reset upload button
   */
  function resetUploadButton() {
    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Picture'
    uploadBtn.disabled = false
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
   * Get current username from session/page
   */
  function getCurrentUsername() {
    // Extract username from navigation or page data
    const profileLink = document.querySelector('a[href*="profile.php?username="]')
    if (profileLink) {
      const url = new URL(profileLink.href)
      return url.searchParams.get("username")
    }
    return ""
  }

  /**
   * Show error message
   */
  function showError(message) {
    removeExistingMessages()
    const errorDiv = document.createElement("div")
    errorDiv.className = "message error"
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`
    document.querySelector(".upload-header").after(errorDiv)

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
    document.querySelector(".upload-header").after(successDiv)
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
 * Reset crop to original image
 */
function resetCrop() {
  if (cropper) {
    cropper.reset()
  }
}

/**
 * Rotate crop area
 */
function rotateCrop(degrees) {
  if (cropper) {
    cropper.rotate(degrees)
  }
}

/**
 * Cancel upload and hide preview
 */
function cancelUpload() {
  if (cropper) {
    cropper.destroy()
    cropper = null
  }

  selectedFile = null
  document.getElementById("previewSection").style.display = "none"
  document.getElementById("fileInput").value = ""

  const cameraInput = document.getElementById("cameraInput")
  if (cameraInput) {
    cameraInput.value = ""
  }

  // Remove any error messages
  const messages = document.querySelectorAll(".message")
  messages.forEach((msg) => msg.remove())
}

/**
 * Compress image before upload
 */
function compressImage(file, maxWidth = 1000, quality = 0.8) {
  return new Promise((resolve) => {
    const canvas = document.createElement("canvas")
    const ctx = canvas.getContext("2d")
    const img = new Image()

    img.onload = () => {
      // Calculate new dimensions
      let { width, height } = img
      if (width > height) {
        if (width > maxWidth) {
          height = (height * maxWidth) / width
          width = maxWidth
        }
      } else {
        if (height > maxWidth) {
          width = (width * maxWidth) / height
          height = maxWidth
        }
      }

      canvas.width = width
      canvas.height = height

      // Draw and compress
      ctx.drawImage(img, 0, 0, width, height)
      canvas.toBlob(resolve, file.type, quality)
    }

    img.src = URL.createObjectURL(file)
  })
}
