// src/stores/ui.js
import { defineStore } from 'pinia'

let toastId = 1

export const useUiStore = defineStore('ui', {
  state: () => ({
    // mobile sidebar state
    sidebarOpen: false,

    // theme: 'light' | 'dark'
    theme: 'light',

    // array of { id, type, title, message }
    toasts: [],
  }),

  getters: {
    isDark(state) {
      return state.theme === 'dark'
    },
  },

  actions: {
    // SIDEBAR
    openSidebar() {
      this.sidebarOpen = true
    },
    closeSidebar() {
      this.sidebarOpen = false
    },
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen
    },

    // THEME
    initTheme() {
      const saved = localStorage.getItem('sm_theme')
      if (saved === 'light' || saved === 'dark') {
        this.theme = saved
      } else {
        // fallback: system preference
        const prefersDark = window.matchMedia &&
          window.matchMedia('(prefers-color-scheme: dark)').matches
        this.theme = prefersDark ? 'dark' : 'light'
      }
      this.applyThemeClass()
    },

    setTheme(mode) {
      if (!['light', 'dark'].includes(mode)) return
      this.theme = mode
      localStorage.setItem('sm_theme', mode)
      this.applyThemeClass()
    },

    toggleTheme() {
      this.setTheme(this.theme === 'light' ? 'dark' : 'light')
    },

    applyThemeClass() {
      const root = document.documentElement
      if (this.theme === 'dark') {
        root.classList.add('dark')
      } else {
        root.classList.remove('dark')
      }
    },

    // TOASTS
    pushToast({ type = 'success', title = '', message = '' }) {
      const id = toastId++
      this.toasts.push({ id, type, title, message })

      // auto-hide after 4s
      setTimeout(() => {
        this.removeToast(id)
      }, 4000)
    },

    removeToast(id) {
      this.toasts = this.toasts.filter((t) => t.id !== id)
    },
  },
})
