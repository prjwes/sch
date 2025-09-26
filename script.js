// Theme Management
class ThemeManager {
  constructor() {
    this.theme = localStorage.getItem("theme") || "light"
    this.init()
  }

  init() {
    this.applyTheme()
    this.bindEvents()
  }

  applyTheme() {
    document.documentElement.setAttribute("data-theme", this.theme)
    const themeToggle = document.getElementById("themeToggle")
    if (themeToggle) {
      const icon = themeToggle.querySelector("i")
      if (icon) {
        icon.className = this.theme === "light" ? "fas fa-moon" : "fas fa-sun"
      }
    }
  }

  toggleTheme() {
    this.theme = this.theme === "light" ? "dark" : "light"
    localStorage.setItem("theme", this.theme)
    this.applyTheme()

    // Save to server if user is logged in
    if (window.currentUser) {
      this.saveThemeToServer()
    }
  }

  async saveThemeToServer() {
    try {
      await fetch("api/save-theme.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ theme: this.theme }),
      })
    } catch (error) {
      console.error("Failed to save theme:", error)
    }
  }

  bindEvents() {
    const themeToggle = document.getElementById("themeToggle")
    if (themeToggle) {
      themeToggle.addEventListener("click", () => this.toggleTheme())
    }
  }
}

// Form Validation
class FormValidator {
  static validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
  }

  static validatePhone(phone) {
    const re = /^[+]?[1-9][\d]{0,15}$/
    return re.test(phone.replace(/\s/g, ""))
  }

  static validatePassword(password) {
    return password.length >= 6
  }

  static showError(element, message) {
    const errorDiv = element.parentNode.querySelector(".error-message")
    if (errorDiv) {
      errorDiv.remove()
    }

    const error = document.createElement("div")
    error.className = "error-message"
    error.style.color = "var(--error-color)"
    error.style.fontSize = "0.875rem"
    error.style.marginTop = "0.25rem"
    error.textContent = message
    element.parentNode.appendChild(error)

    element.style.borderColor = "var(--error-color)"
  }

  static clearError(element) {
    const errorDiv = element.parentNode.querySelector(".error-message")
    if (errorDiv) {
      errorDiv.remove()
    }
    element.style.borderColor = "var(--border-color)"
  }
}

// API Helper
class ApiHelper {
  static async request(url, options = {}) {
    const defaultOptions = {
      headers: {
        "Content-Type": "application/json",
      },
    }

    const config = { ...defaultOptions, ...options }

    try {
      const response = await fetch(url, config)
      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || "Request failed")
      }

      return data
    } catch (error) {
      console.error("API Error:", error)
      throw error
    }
  }

  static async get(url) {
    return this.request(url)
  }

  static async post(url, data) {
    return this.request(url, {
      method: "POST",
      body: JSON.stringify(data),
    })
  }

  static async put(url, data) {
    return this.request(url, {
      method: "PUT",
      body: JSON.stringify(data),
    })
  }

  static async delete(url) {
    return this.request(url, {
      method: "DELETE",
    })
  }
}

// Alert System
class AlertSystem {
  static show(message, type = "info", duration = 5000) {
    const alertContainer = this.getOrCreateContainer()

    const alert = document.createElement("div")
    alert.className = `alert alert-${type}`
    alert.innerHTML = `
            <i class="fas fa-${this.getIcon(type)}"></i>
            <span>${message}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `

    alertContainer.appendChild(alert)

    if (duration > 0) {
      setTimeout(() => {
        if (alert.parentNode) {
          alert.remove()
        }
      }, duration)
    }
  }

  static getOrCreateContainer() {
    let container = document.getElementById("alert-container")
    if (!container) {
      container = document.createElement("div")
      container.id = "alert-container"
      container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1001;
                max-width: 400px;
            `
      document.body.appendChild(container)
    }
    return container
  }

  static getIcon(type) {
    const icons = {
      success: "check-circle",
      error: "exclamation-circle",
      warning: "exclamation-triangle",
      info: "info-circle",
    }
    return icons[type] || "info-circle"
  }

  static success(message) {
    this.show(message, "success")
  }

  static error(message) {
    this.show(message, "error")
  }

  static warning(message) {
    this.show(message, "warning")
  }

  static info(message) {
    this.show(message, "info")
  }
}

// Modal System
class ModalSystem {
  static open(modalId) {
    const modal = document.getElementById(modalId)
    if (modal) {
      modal.classList.add("active")
      document.body.style.overflow = "hidden"
    }
  }

  static close(modalId) {
    const modal = document.getElementById(modalId)
    if (modal) {
      modal.classList.remove("active")
      document.body.style.overflow = ""
    }
  }

  static closeAll() {
    const modals = document.querySelectorAll(".modal.active")
    modals.forEach((modal) => {
      modal.classList.remove("active")
    })
    document.body.style.overflow = ""
  }
}

// Password Toggle Function
function togglePassword(inputId) {
  const input = document.getElementById(inputId)
  const toggle = input.parentNode.querySelector(".password-toggle i")

  if (input.type === "password") {
    input.type = "text"
    toggle.className = "fas fa-eye-slash"
  } else {
    input.type = "password"
    toggle.className = "fas fa-eye"
  }
}

// Form Handlers
function handleSignup(event) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)
  const data = Object.fromEntries(formData)

  // Validation
  let isValid = true

  // Clear previous errors
  form.querySelectorAll("input, select").forEach((input) => {
    FormValidator.clearError(input)
  })

  // Validate email
  if (!FormValidator.validateEmail(data.email)) {
    FormValidator.showError(form.email, "Please enter a valid email address")
    isValid = false
  }

  // Validate phone
  if (!FormValidator.validatePhone(data.phone)) {
    FormValidator.showError(form.phone, "Please enter a valid phone number")
    isValid = false
  }

  // Validate password
  if (!FormValidator.validatePassword(data.password)) {
    FormValidator.showError(form.password, "Password must be at least 6 characters long")
    isValid = false
  }

  // Confirm password
  if (data.password !== data.confirmPassword) {
    FormValidator.showError(form.confirmPassword, "Passwords do not match")
    isValid = false
  }

  if (!isValid) return

  // Submit form
  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<span class="loading"></span> Creating Account...'
  submitBtn.disabled = true

  ApiHelper.post("api/signup.php", data)
    .then((response) => {
      AlertSystem.success("Account created successfully! Please login.")
      setTimeout(() => {
        window.location.href = "login.html"
      }, 2000)
    })
    .catch((error) => {
      AlertSystem.error(error.message)
    })
    .finally(() => {
      submitBtn.innerHTML = originalText
      submitBtn.disabled = false
    })
}

function handleLogin(event) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)
  const data = Object.fromEntries(formData)

  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<span class="loading"></span> Logging in...'
  submitBtn.disabled = true

  ApiHelper.post("api/login.php", data)
    .then((response) => {
      AlertSystem.success("Login successful!")
      setTimeout(() => {
        window.location.href = "dashboard.html"
      }, 1000)
    })
    .catch((error) => {
      AlertSystem.error(error.message)
    })
    .finally(() => {
      submitBtn.innerHTML = originalText
      submitBtn.disabled = false
    })
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  // Initialize theme manager
  new ThemeManager()

  // Bind form handlers
  const signupForm = document.getElementById("signupForm")
  if (signupForm) {
    signupForm.addEventListener("submit", handleSignup)
  }

  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }

  // Close modals when clicking outside
  document.addEventListener("click", (event) => {
    if (event.target.classList.contains("modal")) {
      ModalSystem.close(event.target.id)
    }
  })

  // Close modals with Escape key
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      ModalSystem.closeAll()
    }
  })
})

// Export for use in other files
window.ThemeManager = ThemeManager
window.FormValidator = FormValidator
window.ApiHelper = ApiHelper
window.AlertSystem = AlertSystem
window.ModalSystem = ModalSystem
