/**
 * Profile Page JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  // Profile tab switching
  const tabItems = document.querySelectorAll(".profile-nav-item")
  const tabContents = document.querySelectorAll(".profile-tab-content")

  tabItems.forEach((item) => {
    item.addEventListener("click", function () {
      const tabName = this.dataset.tab

      // Remove active class from all tabs
      tabItems.forEach((tab) => tab.classList.remove("active"))
      tabContents.forEach((content) => content.classList.remove("active"))

      // Add active class to clicked tab
      this.classList.add("active")
      document.getElementById(tabName + "-tab").classList.add("active")
    })
  })
})

/**
 * Toggle follow/unfollow status
 */
async function toggleFollow(userId) {
  try {
    const response = await fetch("api/toggle_follow.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ user_id: userId }),
    })

    const result = await response.json()

    if (result.success) {
      const followBtn = document.getElementById("followBtn")
      const followersCount = document.getElementById("followersCount")

      if (result.following) {
        followBtn.textContent = "Following"
        followBtn.classList.add("following")
      } else {
        followBtn.textContent = "Follow"
        followBtn.classList.remove("following")
      }

      // Update followers count
      followersCount.textContent = formatNumber(result.followers_count)
    }
  } catch (error) {
    console.error("Error toggling follow:", error)
  }
}

/**
 * Open profile picture modal
 */
function openProfilePictureModal() {
  const modal = document.getElementById("profilePictureModal")
  modal.classList.add("active")

  // Handle file input change
  const fileInput = document.getElementById("profilePictureInput")
  fileInput.addEventListener("change", handleProfilePictureUpload)
}

/**
 * Close profile picture modal
 */
function closeProfilePictureModal() {
  const modal = document.getElementById("profilePictureModal")
  modal.classList.remove("active")
}

/**
 * Handle profile picture upload
 */
async function handleProfilePictureUpload(event) {
  const file = event.target.files[0]
  if (!file) return

  // Validate file
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
      // Update profile picture in UI
      const profilePictures = document.querySelectorAll(
        ".profile-picture-large, .nav-profile-pic, .edit-profile-picture",
      )
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

/**
 * Remove profile picture
 */
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
      // Update profile picture to default
      const profilePictures = document.querySelectorAll(
        ".profile-picture-large, .nav-profile-pic, .edit-profile-picture",
      )
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

/**
 * Toggle settings menu
 */
function toggleSettingsMenu() {
  const menu = document.getElementById("settingsMenu")
  menu.style.display = menu.style.display === "none" ? "block" : "none"

  // Close menu when clicking outside
  document.addEventListener("click", function closeMenu(e) {
    if (!e.target.closest(".settings-btn") && !e.target.closest(".settings-menu")) {
      menu.style.display = "none"
      document.removeEventListener("click", closeMenu)
    }
  })
}

/**
 * Open post modal (placeholder)
 */
function openPostModal(postId) {
  console.log("Opening post modal for post:", postId)
  // TODO: Implement post modal
}

/**
 * Open followers modal (placeholder)
 */
function openFollowersModal(userId) {
  console.log("Opening followers modal for user:", userId)
  // TODO: Implement followers modal
}

/**
 * Open following modal (placeholder)
 */
function openFollowingModal(userId) {
  console.log("Opening following modal for user:", userId)
  // TODO: Implement following modal
}

/**
 * Format number for display
 */
function formatNumber(num) {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + "M"
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + "K"
  }
  return num.toLocaleString()
}

// Close modals when clicking outside
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("modal")) {
    e.target.classList.remove("active")
  }
})
