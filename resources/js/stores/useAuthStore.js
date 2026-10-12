import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    api: axios.create({ baseURL: '/api' })
  }),

  actions: {
    async login(username, password) {
      const res = await this.api.post('/login', { username, password })
      this.user = res.data.user
      this.token = res.data.token

      // Simpan token & user
      localStorage.setItem('token', this.token)
      localStorage.setItem('user', JSON.stringify(res.data.user))

      // Set header untuk request berikutnya
      this.api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
    },

    logout() {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      this.user = null
      this.token = null
      delete this.api.defaults.headers.common['Authorization']
    },

    // Panggil di main.js sebelum app.mount()
    setAuthHeader() {
      if (this.token) {
        this.api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    },

    // Tambahkan auto-logout jika token invalid
    init() {
      this.setAuthHeader()
      this.api.interceptors.response.use(
        (response) => response,
        (error) => {
          if (error.response?.status === 401) {
            this.logout()
            window.location.href = '/login'
          }
          return Promise.reject(error)
        }
      )
    }
  }
})
