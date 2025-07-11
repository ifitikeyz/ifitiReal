/**
 * Photo Upload JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  const photoInput = document.getElementById("photoInput")
  const fileInputContainer = document.getElementById("fileInputContainer")
  const fileInputText = document.getElementById("fileInputText")
  const imagePreview = document.getElementById("imagePreview")
  const previewImg = document.getElementById("previewImg")
  const captionInput = document.getElementById("captionInput")
  const charCount = document.getElementById("charCount")
  const uploadBtn = document.getElementById("uploadBtn")
  const uploadForm = document.getElementById("uploadForm")

  // Handle file selection
  photoInput.addEventListener("change", (e) => {
    const file = e.target.files[0]
    if (file) {
      handleFileSelection(file)
    }
  })

  // Handle drag and drop
  fileInputContainer.addEventListener("dragover", function (e) {
    e.preventDefault()
    this.style.borderColor = "#0095f6"
    this.style.backgroundColor = "#f8f9fa"
  })

  fileInputContainer.addEventListener("dragleave", function (e) {
    e.preventDefault()
    this.style.borderColor = "#dbdbdb"
    this.style.backgroundColor = "transparent"
  })

  fileInputContainer.addEventListener("drop", function (e) {
    e.preventDefault()
    this.style.borderColor = "#dbdbdb"
    this.style.backgroundColor = "transparent"

    const files = e.dataTransfer.files
    if (files.length > 0) {
      const file = files[0]
      if (file.type.startsWith("image/")) {
        photoInput.files = files
        handleFileSelection(file)
      } else {
        alert("Please select an image file")
      }
    }
  })

  // Handle caption input
  captionInput.addEventListener("input", function () {
    const length = this.value.length
    charCount.textContent = length

    if (length > 2000) {
      charCount.style.color = "#ed4956"
    } else if (length > 1800) {
      charCount.style.color = "#ff9500"
    } else {
      charCount.style.color = "#8e8e8e"
    }

    validateForm()
  })

  // Handle form submission
  uploadForm.addEventListener("submit", (e) => {
    if (!photoInput.files[0]) {
      e.preventDefault()
      alert("Please select a photo to upload")
      return
    }

    uploadBtn.innerHTML = '<div class="spinner"></div> Uploading...'
    uploadBtn.disabled = true
  })

  /**
   * Handle file selection and preview
   */
  function handleFileSelection(file) {
    const allowedTypes = ["image/jpeg", "image/png", "image/gif"]
    if (!allowedTypes.includes(file.type)) {
      alert("Only JPEG, PNG, and GIF files are allowed")
      return
    }

    if (file.size > 5 * 1024 * 1024) {
      alert("File size must be less than 5MB")
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      previewImg.src = e.target.result
      fileInputContainer.style.display = "none"
      imagePreview.style.display = "block"
      validateForm()
    }
    reader.readAsDataURL(file)
  }

  /**
   * Change photo (show file input again)
   */
  window.changePhoto = () => {
    fileInputContainer.style.display = "block"
    imagePreview.style.display = "none"
    photoInput.value = ""
    validateForm()
  }

  /**
   * Validate form and enable/disable submit button
   */
  function validateForm() {
    const hasPhoto = photoInput.files && photoInput.files[0]
    uploadBtn.disabled = !hasPhoto
    uploadBtn.style.opacity = hasPhoto ? "1" : "0.6"
  }

  validateForm()
})
