/**
 * ifiti Real Estate - Main JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
  console.log("ifiti Real Estate loaded successfully")

  // Initialize search functionality
  initializeSearch()

  // Initialize property modals
  initializePropertyModals()

  // Initialize filters
  initializeFilters()

  // Auto-refresh expired posts
  setInterval(checkExpiredPosts, 60000) // Check every minute
})

/**
 * Initialize search functionality
 */
function initializeSearch() {
  const searchForm = document.querySelector(".search-form")
  const searchInput = document.querySelector(".search-input")

  if (searchForm && searchInput) {
    // Add search suggestions (optional enhancement)
    let searchTimeout

    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      const query = this.value.trim()

      if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
          // Could implement search suggestions here
          console.log("Searching for:", query)
        }, 300)
      }
    })

    // Handle form submission
    searchForm.addEventListener("submit", (e) => {
      const query = searchInput.value.trim()
      if (!query) {
        e.preventDefault()
        showNotification("Please enter a search term", "warning")
      }
    })
  }
}

/**
 * Initialize property modal functionality
 */
function initializePropertyModals() {
  // Close modal when clicking outside
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
      closePropertyModal()
    }
  })

  // Close modal with Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closePropertyModal()
    }
  })
}

/**
 * Open property modal and load details
 */
async function openPropertyModal(postId) {
  const modal = document.getElementById("propertyModal")
  const modalBody = document.getElementById("modalBody")

  if (!modal || !modalBody) return

  // Show modal with loading state
  modal.classList.add("active")
  modalBody.innerHTML = `
        <div class="loading-container" style="text-align: center; padding: 40px;">
            <div class="spinner"></div>
            <p style="margin-top: 16px; color: var(--text-secondary);">Loading property details...</p>
        </div>
    `

  try {
    const response = await fetch(`api/get_property.php?id=${postId}`)
    const result = await response.json()

    if (result.success) {
      displayPropertyDetails(result.property)

      // Track view
      trackPropertyView(postId)
    } else {
      modalBody.innerHTML = `
                <div class="error-container" style="text-align: center; padding: 40px; color: var(--error-color);">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Failed to load property details</p>
                </div>
            `
    }
