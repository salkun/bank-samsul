<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <h1>♻️ Bank Samsul</h1>
        <p>Kelola sampah, wujudkan lingkungan bersih dan sehat.</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            placeholder="Masukkan username"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Masukkan password"
            required
          />
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>

        <p v-if="error" class="error-msg">{{ error }}</p>
      </form>

      <div class="back-home">
        <router-link to="/" class="back-link">← Kembali ke Beranda</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import { swal } from '../plugins/swal'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(false)
const error = ref('')
const form = ref({
  username: '',
  password: ''
})

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await auth.login(form.value.username, form.value.password)

    // ✅ tampilkan notifikasi sukses
    await swal.success('Login berhasil! Selamat datang di Bank Samsul 😊')

    // redirect setelah notifikasi
    router.push('/dashboard')

  } catch (err) {
    const message = err.response?.data?.message || 'Login gagal. Periksa kembali username dan password.'
    error.value = message

    // ❌ tampilkan notifikasi error
    swal.error(message, 'Gagal Login')

  } finally {
    loading.value = false
  }
}
</script>

<style>
:root {
  --primary: hsl(160, 84%, 39%);
  --secondary: hsl(142, 71%, 45%);
  --muted: hsl(142, 20%, 96%);
  --background: #f9fafb;
}

.login-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #333;
  padding: 2rem;
}

.login-container {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  padding: 3rem 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.8s ease-out;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.login-header p {
  color: #555;
  font-size: 1rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-group label {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.25rem;
  display: block;
}

.form-group input {
  width: 100%;
  padding: 0.8rem;
  border-radius: 0.5rem;
  border: 1px solid #ccc;
  font-size: 1rem;
  outline: none;
  transition: all 0.3s ease;
}

.form-group input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.btn-login {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  padding: 0.9rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-login:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-msg {
  color: red;
  text-align: center;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.back-home {
  text-align: center;
  margin-top: 1.5rem;
}

.back-link {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.back-link:hover {
  color: var(--secondary);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
