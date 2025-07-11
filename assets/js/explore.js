/**
 * Explore Page JavaScript
 */

let currentPage = 1
let isLoading = false
let hasMoreContent = true

document.addEventListener("DOMContentLoaded", () => {
  // Initialize search functionality
  initializeSearch()

  // Initialize infinite scroll
  initializeInfiniteScroll()

  // Initialize keyboard shortcuts
  initializeKeyboardShortcuts()

  // Initialize follow buttons
  initializeFollowButtons()
})

/**
 * Initialize follow buttons
 */
function initializeFollowButtons() {
  // Add event listeners to all follow buttons
  const followButtons = document.querySelectorAll(".follow-btn")
  followButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const userId = this.getAttribute("data-user-id")
      if (userId) {
        toggleFollow(userId, this)
      }
    })
  })
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
  const searchInput = document.getElementById("searchInput")
  const searchForm = document.querySelector(".search-form")

  if (!searchInput || !searchForm) return

  // Auto-focus search input on page load
  if (window.location.search.includes("q=")) {
    searchInput.focus()
  }

  // Search suggestions (debounced)
  let searchTimeout
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout)
    const query = this.value.trim()

    if (query.length >= 2) {
      searchTimeout = setTimeout(() => {
        showSearchSuggestions(query)
      }, 300)
    } else {
      hideSearchSuggestions()
    }
  })

  // Handle search form submission
  searchForm.addEventListener("submit", (e) => {
    const query = searchInput.value.trim()
    if (!query) {
      e.preventDefault()
      return
    }

    // Add loading state
    searchInput.style.opacity = "0.6"
    setTimeout(() => {
      searchInput.style.opacity = "1"
    }, 500)
  })

  // Close suggestions when clicking outside
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".search-container")) {
      hideSearchSuggestions()
    }
  })
}

/**
 * Show search suggestions
 */
async function showSearchSuggestions(query) {
  try {
    const response = await fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`)
    const data = await response.json()

    if (data.success && data.suggestions.length > 0) {
      displaySearchSuggestions(data.suggestions)
    } else {
      hideSearchSuggestions()
    }
  } catch (error) {
    console.error("Search suggestions error:", error)
    hideSearchSuggestions()
  }
}

/**
 * Display search suggestions
 */
function displaySearchSuggestions(suggestions) {
  // Remove existing suggestions
  hideSearchSuggestions()

  const searchContainer = document.querySelector(".search-container")
  const suggestionsDiv = document.createElement("div")
  suggestionsDiv.className = "search-suggestions"
  suggestionsDiv.id = "searchSuggestions"

  suggestions.forEach((suggestion) => {
    const item = document.createElement("a")
    item.className = "suggestion-item"
    item.href = `explore.php?q=${encodeURIComponent(suggestion.query)}&type=${suggestion.type}`

    item.innerHTML = `
      <div class="suggestion-icon">
        <i class="fas fa-${suggestion.type === "user" ? "user" : suggestion.type === "hashtag" ? "hashtag" : "search"}"></i>
      </div>
      <div class="suggestion-content">
        <div class="suggestion-text">${suggestion.display}</div>
        <div class="suggestion-meta">${suggestion.meta}</div>
      </div>
    `

    suggestionsDiv.appendChild(item)
  })

  searchContainer.appendChild(suggestionsDiv)
}

/**
 * Hide search suggestions
 */
function hideSearchSuggestions() {
  const suggestions = document.getElementById("searchSuggestions")
  if (suggestions) {
    suggestions.remove()
  }
}

/**
 * Initialize infinite scroll
 */
function initializeInfiniteScroll() {
  window.addEventListener("scroll", () => {
    if (isLoading || !hasMoreContent) return

    const scrollPosition = window.innerHeight + window.scrollY
    const documentHeight = document.documentElement.offsetHeight

    // Load more when 200px from bottom
    if (scrollPosition >= documentHeight - 200) {
      loadMore()
    }
  })
}

/**
 * Load more content
 */
async function loadMore() {
  if (isLoading || !hasMoreContent) return

  isLoading = true
  const loadMoreBtn = document.querySelector(".load-more-btn")

  // Show loading state
  if (loadMoreBtn) {
    loadMoreBtn.innerHTML = '<div class="spinner"></div> Loading...'
    loadMoreBtn.disabled = true
  }

  try {
    const urlParams = new URLSearchParams(window.location.search)
    urlParams.set("page", currentPage + 1)

    const response = await fetch(`explore.php?${urlParams.toString()}`, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })

    if (response.ok) {
      const html = await response.text()
      const parser = new DOMParser()
      const doc = parser.parseFromString(html, "text/html")

      // Extract new content
      const newPosts = doc.querySelectorAll(".post-grid-item")
      const newUsers = doc.querySelectorAll(".user-card, .suggested-user")

      if (newPosts.length > 0 || newUsers.length > 0) {
        // Append new content
        const postsGrid = document.querySelector(".posts-grid")
        const usersGrid = document.querySelector(".users-grid, .suggested-users")

        if (postsGrid && newPosts.length > 0) {
          newPosts.forEach((post) => {
            postsGrid.appendChild(post.cloneNode(true))
          })
        }

        if (usersGrid && newUsers.length > 0) {
          newUsers.forEach((user) => {
            usersGrid.appendChild(user.cloneNode(true))
          })
        }

        currentPage++

        // Re-initialize follow buttons for new content
        initializeFollowButtons()

        // Animate new items
        setTimeout(() => {
          const newItems = document.querySelectorAll(
            ".post-grid-item:nth-last-child(-n+" + (newPosts.length + newUsers.length) + ")",
          )
          newItems.forEach((item, index) => {
            setTimeout(() => {
              item.style.opacity = "0"
              item.style.transform = "translateY(20px)"
              item.style.transition = "all 0.3s ease"

              setTimeout(() => {
                item.style.opacity = "1"
                item.style.transform = "translateY(0)"
              }, 50)
            }, index * 50)
          })
        }, 100)
      } else {
        hasMoreContent = false
        if (loadMoreBtn) {
          loadMoreBtn.style.display = "none"
        }
      }
    }
  } catch (error) {
    console.error("Load more error:", error)
  }

  // Reset loading state
  isLoading = false
  if (loadMoreBtn) {
    loadMoreBtn.innerHTML = '<i class="fas fa-plus"></i> Load More'
    loadMoreBtn.disabled = false
  }
}

/**
 * Toggle follow status with enhanced debugging
 */
async function toggleFollow(userId, button) {
  console.log("toggleFollow called with:", { userId, button })

  if (!userId || !button) {
    console.error("Missing userId or button:", { userId, button })
    showNotification("Invalid follow request", "error")
    return
  }

  // Check if user is trying to follow themselves
  const currentUserId = getCurrentUserId()
  if (userId == currentUserId) {
    showNotification("You cannot follow yourself", "error")
    return
  }

  const originalText = button.textContent.trim()
  const originalClasses = button.className

  // Show loading state
  button.disabled = true
  button.textContent = "..."
  button.style.opacity = "0.6"

  try {
    console.log("Sending follow request for user:", userId)

    const response = await fetch("api/toggle_follow.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ user_id: Number.parseInt(userId) }),
    })

    console.log("Response status:", response.status)
    console.log("Response headers:", [...response.headers.entries()])

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const contentType = response.headers.get("content-type")
    if (!contentType || !contentType.includes("application/json")) {
      const text = await response.text()
      console.error("Non-JSON response:", text)
      throw new Error("Server returned invalid response")
    }

    const result = await response.json()
    console.log("Follow result:", result)

    if (result.success) {
      if (result.following) {
        button.textContent = "Following"
        button.classList.add("following")
        button.classList.remove("follow-btn-primary")
        showNotification("Now following user", "success")
      } else {
        button.textContent = "Follow"
        button.classList.remove("following")
        button.classList.add("follow-btn-primary")
        showNotification("Unfollowed user", "info")
      }

      // Update follower counts if visible
      updateFollowerCounts(userId, result.followers_count)

      // Update any other follow buttons for the same user
      updateAllFollowButtons(userId, result.following)
    } else {
      console.error("Follow failed:", result.message)
      button.textContent = originalText
      button.className = originalClasses
      showNotification(result.message || "Failed to update follow status", "error")
    }
  } catch (error) {
    console.error("Follow error:", error)
    button.textContent = originalText
    button.className = originalClasses

    if (error.name === "TypeError" && error.message.includes("fetch")) {
      showNotification("Network error - please check your connection", "error")
    } else {
      showNotification("An error occurred: " + error.message, "error")
    }
  } finally {
    // Reset button state
    button.disabled = false
    button.style.opacity = "1"
  }
}

/**
 * Update all follow buttons for a specific user
 */
function updateAllFollowButtons(userId, isFollowing) {
  const followButtons = document.querySelectorAll(`[data-user-id="${userId}"]`)

  followButtons.forEach((btn) => {
    if (isFollowing) {
      btn.textContent = "Following"
      btn.classList.add("following")
      btn.classList.remove("follow-btn-primary")
    } else {
      btn.textContent = "Follow"
      btn.classList.remove("following")
      btn.classList.add("follow-btn-primary")
    }
  })
}

/**
 * Get current user ID from session/page data
 */
function getCurrentUserId() {
  // Try to get from a data attribute
  const userIdElement = document.querySelector("[data-current-user-id]")
  if (userIdElement) {
    return Number.parseInt(userIdElement.dataset.currentUserId)
  }

  // Try to get from body data attribute
  if (document.body.dataset.currentUserId) {
    return Number.parseInt(document.body.dataset.currentUserId)
  }

  return null
}

/**
 * Update follower counts in the UI
 */
function updateFollowerCounts(userId, newCount) {
  const followerElements = document.querySelectorAll(`[data-user-id="${userId}"] .follower-count`)
  followerElements.forEach((element) => {
    element.textContent = formatNumber(newCount) + " followers"
  })
}

/**
 * Open post modal
 */
async function openPostModal(postId) {
  const modal = document.getElementById("postModal")
  const modalBody = document.getElementById("postModalBody")

  if (!modal || !modalBody) return

  // Show modal with loading state
  modalBody.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>'
  modal.style.display = "flex"
  document.body.style.overflow = "hidden"

  try {
    const response = await fetch(`api/get_post.php?id=${postId}`)
    const result = await response.json()

    if (result.success) {
      modalBody.innerHTML = result.html

      // Initialize post interactions
      initializePostModal(postId)
    } else {
      modalBody.innerHTML = '<div class="error-message">Failed to load post</div>'
    }
  } catch (error) {
    console.error("Post modal error:", error)
    modalBody.innerHTML = '<div class="error-message">Network error occurred</div>'
  }
}

/**
 * Close post modal
 */
function closePostModal() {
  const modal = document.getElementById("postModal")
  if (modal) {
    modal.style.display = "none"
    document.body.style.overflow = "auto"
  }
}

/**
 * Initialize post modal interactions
 */
function initializePostModal(postId) {
  // Add event listeners for like, comment, etc.
  const likeBtn = document.querySelector(`#postModal .like-btn`)
  if (likeBtn) {
    likeBtn.addEventListener("click", () => toggleLike(postId))
  }

  const commentForm = document.querySelector(`#postModal .comment-form`)
  if (commentForm) {
    commentForm.addEventListener("submit", (e) => submitComment(e, postId))
  }
}

/**
 * Initialize keyboard shortcuts
 */
function initializeKeyboardShortcuts() {
  document.addEventListener("keydown", (e) => {
    // ESC to close modal
    if (e.key === "Escape") {
      closePostModal()
      hideSearchSuggestions()
    }

    // Ctrl/Cmd + K to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault()
      const searchInput = document.getElementById("searchInput")
      if (searchInput) {
        searchInput.focus()
        searchInput.select()
      }
    }
  })
}

/**
 * Show notification
 */
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification ${type}`
  notification.textContent = message

  notification.style.cssText = `
    position: fixed;
    top: 80px;
    right: 20px;
    background: ${type === "error" ? "#ed4956" : type === "success" ? "#2ed573" : "#0095f6"};
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    z-index: 10000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
  `

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      notification.remove()
    }, 300)
  }, 3000)
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

/**
 * Toggle like (from main.js)
 */
async function toggleLike(postId) {
  try {
    const response = await fetch("api/toggle_like.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ post_id: postId }),
    })

    const result = await response.json()

    if (result.success) {
      // Update like button and count in modal and grid
      const likeBtns = document.querySelectorAll(`[data-post-id="${postId}"] .like-btn, #postModal .like-btn`)
      const likeCounts = document.querySelectorAll(`[data-post-id="${postId}"] .likes-count, #postModal .likes-count`)

      likeBtns.forEach((btn) => {
        const icon = btn.querySelector(".action-icon")
        if (result.liked) {
          btn.classList.add("liked")
          if (icon) {
            icon.classList.remove("far")
            icon.classList.add("fas")
          }
        } else {
          btn.classList.remove("liked")
          if (icon) {
            icon.classList.remove("fas")
            icon.classList.add("far")
          }
        }
      })

      likeCounts.forEach((count) => {
        const num = result.likes_count
        count.textContent = `${formatNumber(num)} ${num === 1 ? "like" : "likes"}`
      })
    }
  } catch (error) {
    console.error("Error toggling like:", error)
  }
}

/**
 * Submit comment (from main.js)
 */
async function submitComment(event, postId) {
  event.preventDefault()

  const form = event.target
  const input = form.querySelector(".comment-input")
  const commentText = input.value.trim()

  if (!commentText) return

  try {
    const response = await fetch("api/add_comment.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        post_id: postId,
        comment_text: commentText,
      }),
    })

    const result = await response.json()

    if (result.success) {
      input.value = ""

      // Add comment to modal if open
      const commentsContainer = document.querySelector(`#postModal .comments-container`)
      if (commentsContainer) {
        const commentElement = document.createElement("div")
        commentElement.className = "comment"
        commentElement.innerHTML = `
          <span class="username">${result.comment.username}</span>
          ${commentText}
        `
        commentsContainer.appendChild(commentElement)
      }
    }
  } catch (error) {
    console.error("Error adding comment:", error)
  }
}

// Close modal when clicking outside
document.addEventListener("click", (e) => {
  const modal = document.getElementById("postModal")
  if (e.target === modal) {
    closePostModal()
  }
})

// Make functions globally available
window.toggleFollow = toggleFollow
window.openPostModal = openPostModal
window.closePostModal = closePostModal
window.loadMore = loadMore
