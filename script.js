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

// Data Storage Manager
class DataManager {
  constructor() {
    this.initializeStorage()
  }

  initializeStorage() {
    const storageItems = [
      "sms_users",
      "sms_students",
      "sms_exams",
      "sms_payments",
      "sms_clubs",
      "sms_notes",
      "sms_exam_marks",
      "sms_club_members",
      "sms_fee_types",
      "sms_graduated_students",
    ]

    storageItems.forEach((item) => {
      if (!localStorage.getItem(item)) {
        localStorage.setItem(item, JSON.stringify([]))
      }
    })
  }

  getUsers() {
    return JSON.parse(localStorage.getItem("sms_users") || "[]")
  }

  saveUser(user) {
    const users = this.getUsers()
    users.push({
      ...user,
      id: Date.now(),
      createdAt: new Date().toISOString(),
    })
    localStorage.setItem("sms_users", JSON.stringify(users))
  }

  findUser(username, password) {
    const users = this.getUsers()
    return users.find((user) => user.username === username && user.password === password)
  }

  userExists(username, email, phone) {
    const users = this.getUsers()
    return users.some((user) => user.username === username || user.email === email || user.phone === phone)
  }

  getRoleCount(role) {
    const users = this.getUsers()
    return users.filter((user) => user.role === role).length
  }

  getCurrentUser() {
    const userStr = sessionStorage.getItem("currentUser")
    return userStr ? JSON.parse(userStr) : null
  }

  setCurrentUser(user) {
    sessionStorage.setItem("currentUser", JSON.stringify(user))
  }

  logout() {
    sessionStorage.removeItem("currentUser")
  }

  getStudents() {
    return JSON.parse(localStorage.getItem("sms_students") || "[]")
  }

  saveStudent(student) {
    const students = this.getStudents()
    const nextAdmissionNumber = this.generateAdmissionNumber()

    students.push({
      ...student,
      id: Date.now(),
      admissionNumber: nextAdmissionNumber,
      createdAt: new Date().toISOString(),
      password: `student.${new Date().getFullYear()}`, // Default password
    })
    localStorage.setItem("sms_students", JSON.stringify(students))
    return students[students.length - 1]
  }

  generateAdmissionNumber() {
    const students = this.getStudents()
    const maxAdm = students.reduce((max, student) => {
      const admNum = Number.parseInt(student.admissionNumber) || 0
      return Math.max(max, admNum)
    }, 0)
    return String(maxAdm + 1).padStart(3, "0")
  }

  getExams() {
    return JSON.parse(localStorage.getItem("sms_exams") || "[]")
  }

  saveExam(exam) {
    const exams = this.getExams()
    exams.push({
      ...exam,
      id: Date.now(),
      createdAt: new Date().toISOString(),
    })
    localStorage.setItem("sms_exams", JSON.stringify(exams))
    return exams[exams.length - 1]
  }

  getPayments() {
    return JSON.parse(localStorage.getItem("sms_payments") || "[]")
  }

  savePayment(payment) {
    const payments = this.getPayments()
    payments.push({
      ...payment,
      id: Date.now(),
      createdAt: new Date().toISOString(),
    })
    localStorage.setItem("sms_payments", JSON.stringify(payments))
    return payments[payments.length - 1]
  }

  getClubs() {
    return JSON.parse(localStorage.getItem("sms_clubs") || "[]")
  }

  saveClub(club) {
    const clubs = this.getClubs()
    clubs.push({
      ...club,
      id: Date.now(),
      createdAt: new Date().toISOString(),
    })
    localStorage.setItem("sms_clubs", JSON.stringify(clubs))
    return clubs[clubs.length - 1]
  }

  getNotes() {
    return JSON.parse(localStorage.getItem("sms_notes") || "[]")
  }

  saveNote(note) {
    const notes = this.getNotes()
    notes.push({
      ...note,
      id: Date.now(),
      createdAt: new Date().toISOString(),
    })
    localStorage.setItem("sms_notes", JSON.stringify(notes))
    return notes[notes.length - 1]
  }
}

function showSignup() {
  ModalSystem.open("signupModal")
}

function showLogin() {
  ModalSystem.open("loginModal")
}

function showStudentLogin() {
  ModalSystem.open("studentLoginModal")
}

function closeModal(modalId) {
  ModalSystem.close(modalId)
}

function showForgotPassword() {
  ModalSystem.close("loginModal")
  // TODO: Implement forgot password modal
  AlertSystem.info("Forgot password feature will be implemented soon")
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

  // Check if user already exists
  if (dataManager.userExists(data.username, data.email, data.phone)) {
    AlertSystem.error("User with this username, email, or phone already exists")
    return
  }

  // Check role limits
  const restrictedRoles = ["Admin", "DoS_Social_Affairs", "Finance"]
  if (restrictedRoles.includes(data.role)) {
    if (dataManager.getRoleCount(data.role) >= 2) {
      AlertSystem.error(`Maximum 2 users allowed for ${data.role} role`)
      return
    }
  }

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

  try {
    // Save user
    dataManager.saveUser({
      fullName: data.fullName,
      username: data.username,
      email: data.email,
      phone: data.phone,
      role: data.role,
      password: data.password, // In real app, this should be hashed
    })

    AlertSystem.success("Account created successfully! Please login.")
    form.reset()
    setTimeout(() => {
      closeModal("signupModal")
      showLogin()
    }, 2000)
  } catch (error) {
    AlertSystem.error("Failed to create account. Please try again.")
  } finally {
    submitBtn.innerHTML = originalText
    submitBtn.disabled = false
  }
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

  try {
    const user = dataManager.findUser(data.loginUsername, data.loginPassword)

    if (user) {
      dataManager.setCurrentUser(user)
      AlertSystem.success("Login successful!")
      setTimeout(() => {
        window.location.href = "dashboard.html"
      }, 1000)
    } else {
      AlertSystem.error("Invalid username or password")
    }
  } catch (error) {
    AlertSystem.error("Login failed. Please try again.")
  } finally {
    submitBtn.innerHTML = originalText
    submitBtn.disabled = false
  }
}

function handleStudentLogin(event) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)
  const data = Object.fromEntries(formData)

  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<span class="loading"></span> Logging in...'
  submitBtn.disabled = true

  try {
    const students = JSON.parse(localStorage.getItem("sms_students") || "[]")
    const currentYear = new Date().getFullYear()
    const defaultPassword = `student.${currentYear}`

    // Find student by name
    const student = students.find((s) => s.name.toLowerCase() === data.studentName.toLowerCase())

    if (!student) {
      AlertSystem.error("Student not found. Please check your name.")
      return
    }

    // Check password (either default or custom)
    const studentPassword = student.password || defaultPassword
    if (data.studentPassword !== studentPassword) {
      AlertSystem.error("Invalid password.")
      return
    }

    AlertSystem.success("Login successful!")
    setTimeout(() => {
      window.location.href = `student-portal.html?studentId=${student.id}`
    }, 1000)
  } catch (error) {
    AlertSystem.error("Login failed. Please try again.")
  } finally {
    submitBtn.innerHTML = originalText
    submitBtn.disabled = false
  }
}

const dataManager = new DataManager()

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

  const studentLoginForm = document.getElementById("studentLoginForm")
  if (studentLoginForm) {
    studentLoginForm.addEventListener("submit", handleStudentLogin)
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
window.DataManager = DataManager
