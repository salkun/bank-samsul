import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null
  }),
  actions: {
    async login(email, password) {
      const res = await axios.post('/api/login', { email, password })
      this.token = res.data.token
      this.user = res.data.user
      localStorage.setItem('token', this.token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
    },
    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    }
  }
})
