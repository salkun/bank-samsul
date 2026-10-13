<template>
  <div class="auth-page">
    <!-- Ambient glowing backgrounds matching reference -->
    <div class="ambient-peach" aria-hidden="true"></div>
    <div class="ambient-blue" aria-hidden="true"></div>

    <!-- Main Card Container -->
    <div class="auth-card">
      <!-- LEFT PANEL: Form Section -->
      <div class="card-form-panel">
        <div class="form-inner">
          <!-- Brand header -->
          <div class="brand-row">
            <router-link to="/" class="brand-link" title="Ke Beranda">
              <span class="brand-mark">
                <img src="/images/logo.jpeg" alt="Logo Bank Samsul" class="brand-logo-img" />
              </span>
              <span class="brand-title">bank samsul.</span>
            </router-link>
            <span class="school-tag">{{ SEKOLAH }}</span>
          </div>

          <!-- Title -->
          <div class="form-title-block">
            <h1 class="form-heading">Sign In to your Account</h1>
            <p class="form-subtext">Masuk untuk mengelola setoran, poin, dan hadiah sampah.</p>
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="alert-error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ error }}</span>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="handleLogin" class="sign-form" novalidate>
            <!-- Email / Username -->
            <div class="form-field">
              <label for="username" class="field-label">Email</label>
              <input
                id="username"
                v-model.trim="form.username"
                type="text"
                class="field-input"
                placeholder="Enter Email or Username"
                autocomplete="username"
                required
                :disabled="loading"
              />
            </div>

            <!-- Password -->
            <div class="form-field">
              <div class="label-split">
                <label for="password" class="field-label">Password</label>
                <a href="#forgot" class="forgot-link" @click.prevent="handleForgotPassword">
                  Forgot password?
                </a>
              </div>
              <div class="input-pass-wrap">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="field-input"
                  placeholder="Enter Password"
                  autocomplete="current-password"
                  required
                  :disabled="loading"
                />
                <button
                  type="button"
                  class="btn-eye"
                  :aria-label="showPassword ? 'Hide password' : 'Show password'"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <!-- Remember me checkbox -->
            <div class="remember-row">
              <label class="checkbox-container">
                <input type="checkbox" v-model="rememberMe" />
                <span class="custom-checkmark"></span>
                <span class="checkbox-label">Remember me</span>
              </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-signin" :disabled="loading">
              <span v-if="loading" class="btn-spinner" aria-hidden="true"></span>
              <span>{{ loading ? 'Signing In...' : 'SIGN IN' }}</span>
            </button>
          </form>

          <!-- Footer Prompt & Home Link -->
          <div class="form-footer">
            <p class="register-prompt">
              Not registered yet?
              <a href="#register" class="register-link" @click.prevent="handleContactAdmin">Create an account</a>
            </p>
            <router-link to="/" class="back-home-link">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              <span>Kembali ke Beranda</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- RIGHT PANEL: Vector CSS Eco Illustration (Gaya Landing Page) -->
      <div class="card-art-panel" role="img" aria-label="Ilustrasi ekosistem Bank Samsul dari vektor CSS">
        <div class="vector-art-canvas">
          <!-- Background botanical silhouettes -->
          <svg class="bg-botanical top-right" viewBox="0 0 160 160" fill="none" aria-hidden="true">
            <path d="M160 0C120 10 90 40 80 80C75 100 85 125 100 140C125 165 160 160 160 160V0Z" fill="#1b4637" opacity="0.1" />
            <path d="M160 30C130 40 110 65 105 95C100 120 120 140 140 150" stroke="#1b4637" stroke-width="2" stroke-linecap="round" opacity="0.18" />
            <path d="M130 60C145 68 155 75 160 78" stroke="#1b4637" stroke-width="2" stroke-linecap="round" opacity="0.18" />
            <path d="M118 85C135 90 150 96 158 98" stroke="#1b4637" stroke-width="2" stroke-linecap="round" opacity="0.18" />
          </svg>
          <svg class="bg-botanical bottom-left" viewBox="0 0 160 160" fill="none" aria-hidden="true">
            <path d="M0 160C40 150 70 120 80 80C85 60 75 35 60 20C35 -5 0 0 0 0V160Z" fill="#1b4637" opacity="0.1" />
            <path d="M0 130C30 120 50 95 55 65C60 40 40 20 20 10" stroke="#1b4637" stroke-width="2" stroke-linecap="round" opacity="0.18" />
            <path d="M30 100C15 92 5 85 0 82" stroke="#1b4637" stroke-width="2" stroke-linecap="round" opacity="0.18" />
          </svg>

          <!-- Top badge / label -->
          <div class="vector-art-top">
            <span class="pulse-dot"></span>
            Dari sisa, jadi berharga
          </div>

          <!-- Sun & Orbit system -->
          <div class="vector-sun" aria-hidden="true"></div>
          <div class="vector-orbit orbit-large" aria-hidden="true"></div>
          <div class="vector-orbit orbit-small" aria-hidden="true"></div>

          <!-- Network orbital glow badge nodes -->
          <div class="orbit-node node-recycle" title="Daur Ulang">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/>
            </svg>
          </div>
          <div class="orbit-node node-points" title="Poin & Tabungan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
          </div>

          <!-- Sprouting Plant & Terracotta Pot (identik gaya landing page) -->
          <div class="vector-stem" aria-hidden="true"></div>
          <div class="vector-leaf leaf-one" aria-hidden="true"></div>
          <div class="vector-leaf leaf-two" aria-hidden="true"></div>
          <div class="vector-leaf leaf-three" aria-hidden="true"></div>
          <div class="vector-leaf leaf-four" aria-hidden="true"></div>

          <div class="vector-pot-rim" aria-hidden="true"></div>
          <div class="vector-plant-pot" aria-hidden="true"></div>

          <!-- Floating Info Cards (identik landing page) -->
          <div class="vector-float-card fc-left">
            <span class="v-mini-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3h8v3l2 3v11H6V9l2-3z" />
              </svg>
            </span>
            <div class="v-card-text">
              <small>Pilah Sampah</small>
              <b>Poin Bertambah ♻</b>
            </div>
          </div>

          <div class="vector-float-card fc-right">
            <span class="v-mini-icon icon-gift">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
              </svg>
            </span>
            <div class="v-card-text">
              <small>Tukar Hadiah</small>
              <b>Apresiasi Nyata ✨</b>
            </div>
          </div>

          <!-- Bottom caption -->
          <div class="vector-art-caption">
            <span>Tumbuh bersama kebiasaan baik.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import { swal } from '../plugins/swal'

const SEKOLAH = 'SMP 2 Al-Muhajirin'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)
const rememberMe = ref(true)

const form = ref({
  username: '',
  password: ''
})

const handleLogin = async () => {
  if (!form.value.username || !form.value.password) {
    error.value = 'Silakan isi username dan password Anda.'
    return
  }

  loading.value = true
  error.value = ''

  try {
    await auth.login(form.value.username, form.value.password)
    await swal.success('Login berhasil! Selamat datang di Bank Samsul 😊')

    if (auth.user?.role === 'nasabah') {
      router.push('/nasabah-dashboard')
    } else {
      router.push('/dashboard')
    }
  } catch (err) {
    const message = err.response?.data?.message || 'Login gagal. Periksa kembali username dan password.'
    error.value = message
    swal.error(message, 'Gagal Login')
  } finally {
    loading.value = false
  }
}

const handleForgotPassword = () => {
  swal.info('Lupa password? Silakan hubungi admin sekolah atau petugas bank sampah di SMP 2 Al-Muhajirin untuk verifikasi dan reset kata sandi.', 'Bantuan Password')
}

const handleContactAdmin = () => {
  window.open('https://wa.me/6285722975551', '_blank')
}
</script>

<style scoped>
/* Page Canvas with Soft Luminous Gradient */
.auth-page {
  --primary-blue: #173f32;
  --primary-hover: #28694e;
  --lime: #d5ee83;
  --dark-navy: #173f32;
  --text-main: #182d25;
  --text-muted: #65736a;
  --input-bg: #f8faf6;
  --input-border: #e2ebe0;
  --input-focus: #28694e;

  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 36px 20px;
  position: relative;
  overflow: hidden;
  font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background: radial-gradient(circle at 85% 15%, #f4ede4 0%, #f6f4ec 45%, #edf3e8 100%);
}

/* Ambient glow accents */
.ambient-peach {
  position: absolute;
  top: -100px;
  right: -80px;
  width: 520px;
  height: 520px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(239, 117, 72, 0.22) 0%, rgba(239, 117, 72, 0) 70%);
  pointer-events: none;
  filter: blur(40px);
}
.ambient-blue {
  position: absolute;
  bottom: -120px;
  left: -80px;
  width: 540px;
  height: 540px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(213, 238, 131, 0.35) 0%, rgba(213, 238, 131, 0) 70%);
  pointer-events: none;
  filter: blur(50px);
}

/* Main Outer Card */
.auth-card {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1040px;
  background: #ffffff;
  border-radius: 40px;
  box-shadow: 0 25px 70px rgba(23, 63, 50, 0.1), 0 6px 20px rgba(23, 63, 50, 0.04);
  display: grid;
  grid-template-columns: 1fr 1.08fr;
  align-items: stretch;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.85);
}

/* LEFT: Form Panel */
.card-form-panel {
  padding: 56px 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
}
.form-inner {
  width: 100%;
  max-width: 380px;
  display: flex;
  flex-direction: column;
}

/* Brand Row */
.brand-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-bottom: 24px;
}
.brand-link {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: var(--text-main);
}
.brand-mark {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(23, 63, 50, 0.12);
  border: 1px solid #e2ebe0;
  padding: 2px;
}
.brand-mark .brand-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.brand-mark svg {
  width: 22px;
  height: 22px;
}
.brand-title {
  font: 800 19px "Manrope", sans-serif;
  letter-spacing: -0.05em;
  color: #173f32;
}
.school-tag {
  font-size: 11px;
  font-weight: 700;
  color: #173f32;
  background: #edf5ea;
  border: 1px solid #d5e5cf;
  padding: 4px 10px;
  border-radius: 999px;
  letter-spacing: 0.02em;
}

/* Form Titles */
.form-title-block {
  text-align: center;
  margin-bottom: 24px;
}
.form-heading {
  font: 800 27px/1.2 "Manrope", sans-serif;
  letter-spacing: -0.03em;
  color: var(--text-main);
  margin: 0 0 6px;
  text-align: center;
}
.form-subtext {
  font-size: 13.5px;
  line-height: 1.5;
  color: var(--text-muted);
  margin: 0;
  text-align: center;
}

/* Error Alert */
.alert-error {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 12px;
  background: #fdf2f2;
  border: 1px solid #fbd5d5;
  color: #b91c1c;
  font-size: 13px;
  margin-bottom: 20px;
  animation: shake 0.3s ease-in-out;
}
.alert-error svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* Form Fields */
.sign-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.field-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
}
.label-split {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.forgot-link {
  font-size: 12px;
  font-weight: 600;
  color: var(--primary-blue);
  text-decoration: none;
  transition: color 0.2s;
}
.forgot-link:hover {
  color: var(--primary-hover);
  text-decoration: underline;
}

.field-input {
  width: 100%;
  padding: 13px 18px;
  font-size: 14px;
  font-family: inherit;
  color: var(--text-main);
  background: var(--input-bg);
  border: 1.5px solid var(--input-border);
  border-radius: 14px;
  outline: none;
  transition: all 0.2s ease;
}
.field-input::placeholder {
  color: #a0aec0;
  font-size: 13.5px;
}
.field-input:focus {
  background: #ffffff;
  border-color: var(--input-focus);
  box-shadow: 0 0 0 3.5px rgba(59, 80, 223, 0.12);
}

.input-pass-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input-pass-wrap .field-input {
  padding-right: 48px;
}
.btn-eye {
  position: absolute;
  right: 14px;
  background: none;
  border: none;
  padding: 4px;
  color: #94a3b8;
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: color 0.2s;
}
.btn-eye svg {
  width: 19px;
  height: 19px;
}
.btn-eye:hover {
  color: var(--text-main);
}

/* Remember me */
.remember-row {
  display: flex;
  align-items: center;
  margin-top: -2px;
}
.checkbox-container {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}
.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}
.custom-checkmark {
  width: 18px;
  height: 18px;
  background-color: var(--input-bg);
  border: 1.5px solid #cbd5e1;
  border-radius: 6px;
  display: grid;
  place-items: center;
  transition: all 0.2s;
}
.checkbox-container input:checked ~ .custom-checkmark {
  background-color: var(--primary-blue);
  border-color: var(--primary-blue);
}
.checkbox-container input:checked ~ .custom-checkmark::after {
  content: "";
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  margin-top: -2px;
}
.checkbox-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
}

/* Submit Button */
.btn-signin {
  margin-top: 8px;
  width: 100%;
  padding: 14px 20px;
  background: var(--primary-blue);
  color: #ffffff;
  border: none;
  border-radius: 14px;
  font: 800 13.5px "Manrope", sans-serif;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  box-shadow: 0 10px 24px rgba(59, 80, 223, 0.35);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.25s ease;
}
.btn-signin:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(59, 80, 223, 0.42);
}
.btn-signin:active:not(:disabled) {
  transform: translateY(0);
}
.btn-signin:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Form Footer */
.form-footer {
  margin-top: 26px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}
.register-prompt {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}
.register-link {
  color: var(--primary-blue);
  font-weight: 700;
  text-decoration: none;
  transition: color 0.2s;
}
.register-link:hover {
  text-decoration: underline;
}
.back-home-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  text-decoration: none;
  transition: all 0.2s;
}
.back-home-link svg {
  width: 14px;
  height: 14px;
  transition: transform 0.2s;
}
.back-home-link:hover {
  color: var(--primary-blue);
  transform: translateX(-2px);
}

/* RIGHT: Vector Eco Canvas (Gaya Landing Page Bank Samsul) */
.card-art-panel {
  position: relative;
  background: #e5edda;
  border-top-left-radius: 280px;
  border-bottom-left-radius: 280px;
  overflow: hidden;
  isolation: isolate;
  display: flex;
  align-items: stretch;
  justify-content: center;
  box-shadow: inset 12px 0 35px rgba(23, 63, 50, 0.08);
}

.vector-art-canvas {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 520px;
  overflow: hidden;
}

/* Background botanical silhouettes */
.bg-botanical {
  position: absolute;
  width: 150px;
  height: 150px;
  pointer-events: none;
  z-index: 1;
}
.bg-botanical.top-right {
  top: 0;
  right: 0;
}
.bg-botanical.bottom-left {
  bottom: 0;
  left: 0;
}

/* Top label */
.vector-art-top {
  position: absolute;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  z-index: 4;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #45624c;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.pulse-dot {
  width: 7px;
  height: 7px;
  background: #28694e;
  border-radius: 50%;
  animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { transform: scale(1); opacity: 0.9; }
  50% { transform: scale(1.4); opacity: 0.5; }
}

/* Sun */
.vector-sun {
  position: absolute;
  width: 210px;
  height: 210px;
  background: #d5ee83;
  border-radius: 50%;
  right: -30px;
  top: 40px;
  opacity: 0.95;
  box-shadow: 0 0 50px rgba(213, 238, 131, 0.35);
  z-index: 1;
}

/* Concentric Orbits */
.vector-orbit {
  position: absolute;
  border: 1px dashed #b0c2a9;
  border-radius: 50%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2;
  pointer-events: none;
}
.vector-orbit.orbit-large {
  width: 330px;
  height: 330px;
  top: 80px;
}
.vector-orbit.orbit-small {
  width: 240px;
  height: 240px;
  top: 125px;
  border-style: dotted;
  border-color: #9cb494;
}

/* Orbit Nodes with glowing halos */
.orbit-node {
  position: absolute;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #ffffff;
  display: grid;
  place-items: center;
  box-shadow: 0 6px 18px rgba(23, 63, 50, 0.18), 0 0 0 3px rgba(213, 238, 131, 0.6);
  z-index: 6;
  transition: transform 0.3s ease;
}
.orbit-node:hover {
  transform: scale(1.1);
}
.orbit-node svg {
  width: 20px;
  height: 20px;
}
.node-recycle {
  top: 96px;
  left: 80px;
  color: #28694e;
  animation: float-node 4.5s ease-in-out infinite;
}
.node-points {
  top: 245px;
  right: 36px;
  color: #ef7548;
  animation: float-node 5.2s ease-in-out -2.2s infinite;
}
@keyframes float-node {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-7px); }
}

/* Plant Pot (Terracotta) */
.vector-plant-pot {
  position: absolute;
  left: 50%;
  bottom: 42px;
  transform: translateX(-50%);
  width: 190px;
  height: 140px;
  background: #d17c55;
  clip-path: polygon(7% 0, 93% 0, 79% 100%, 21% 100%);
  z-index: 4;
}
.vector-plant-pot::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(105deg, rgba(255, 255, 255, 0.16), transparent 48%, rgba(118, 59, 39, 0.18));
}

/* Pot Rim */
.vector-pot-rim {
  position: absolute;
  left: calc(50% - 106px);
  bottom: 168px;
  width: 212px;
  height: 24px;
  background: #b96645;
  border-radius: 7px;
  z-index: 5;
  box-shadow: 0 4px 12px rgba(185, 102, 69, 0.25);
}

/* Stem */
.vector-stem {
  position: absolute;
  bottom: 180px;
  left: 50%;
  width: 8px;
  height: 180px;
  background: #28694e;
  border-radius: 8px;
  transform: rotate(4deg);
  transform-origin: bottom;
  z-index: 3;
}

/* Leaves */
.vector-leaf {
  position: absolute;
  z-index: 4;
  width: 95px;
  height: 50px;
  background: #347957;
  border-radius: 100% 0 100% 0;
  box-shadow: 0 4px 14px rgba(23, 63, 50, 0.12);
}
.vector-leaf::after {
  content: "";
  position: absolute;
  left: 15%;
  top: 50%;
  width: 80%;
  height: 1px;
  background: rgba(213, 238, 131, 0.65);
  transform: rotate(-23deg);
}
.vector-leaf.leaf-one {
  bottom: 275px;
  left: calc(50% - 88px);
  transform: rotate(15deg);
}
.vector-leaf.leaf-two {
  bottom: 318px;
  left: calc(50% - 2px);
  transform: rotate(74deg) scale(0.92);
}
.vector-leaf.leaf-three {
  bottom: 235px;
  left: calc(50% - 2px);
  transform: rotate(70deg) scale(0.8);
  background: #5a9668;
}
.vector-leaf.leaf-four {
  bottom: 360px;
  left: calc(50% - 68px);
  transform: rotate(5deg) scale(0.62);
  background: #5a9668;
}

/* Floating Info Cards */
.vector-float-card {
  position: absolute;
  z-index: 7;
  max-width: 200px;
  background: #fffef9;
  border: 1px solid rgba(255, 255, 255, 0.85);
  box-shadow: 0 14px 35px rgba(23, 63, 50, 0.12);
  border-radius: 16px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: float-card 5s ease-in-out infinite;
}
.fc-left {
  left: 48px;
  bottom: 64px;
}
.fc-right {
  right: 24px;
  top: 136px;
  animation-delay: -2.5s;
}
@keyframes float-card {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.v-mini-icon {
  display: grid;
  place-items: center;
  width: 30px;
  height: 30px;
  border-radius: 9px;
  background: #f7e1d5;
  color: #a34b2c;
  flex-shrink: 0;
}
.v-mini-icon.icon-gift {
  background: #dff2e1;
  color: #28694e;
}
.v-mini-icon svg {
  width: 17px;
  height: 17px;
}
.v-card-text {
  display: flex;
  flex-direction: column;
}
.v-card-text small {
  font-size: 10px;
  color: #65736a;
  font-weight: 600;
  line-height: 1.2;
}
.v-card-text b {
  font: 800 13px "Manrope", sans-serif;
  color: #173f32;
  letter-spacing: -0.02em;
}

/* Bottom caption */
.vector-art-caption {
  position: absolute;
  bottom: 18px;
  right: 22px;
  z-index: 4;
  font-size: 10px;
  color: #557257;
  letter-spacing: 0.04em;
  font-weight: 500;
}

/* RESPONSIVE DESIGN (Matching Reference Mobile Layout) */
@media (max-width: 900px) {
  .auth-page {
    padding: 18px 12px 36px;
    align-items: center;
    justify-content: flex-start;
  }
  .auth-card {
    display: flex;
    flex-direction: column;
    max-width: 420px;
    width: 100%;
    margin: 0 auto;
    border-radius: 36px;
    background: transparent;
    box-shadow: none;
    border: none;
  }
  .card-art-panel {
    order: 1;
    border-radius: 34px 34px 0 0;
    height: 290px;
    width: 100%;
    overflow: hidden;
    box-shadow: 0 14px 35px rgba(22, 38, 90, 0.12);
  }
  .vector-art-canvas {
    min-height: 290px;
  }
  .vector-sun {
    width: 140px;
    height: 140px;
    right: -20px;
    top: 25px;
  }
  .vector-orbit.orbit-large {
    width: 250px;
    height: 250px;
    top: 55px;
  }
  .vector-orbit.orbit-small {
    width: 180px;
    height: 180px;
    top: 90px;
  }
  .vector-plant-pot {
    width: 135px;
    height: 100px;
    bottom: 24px;
  }
  .vector-pot-rim {
    width: 152px;
    height: 18px;
    bottom: 114px;
    left: calc(50% - 76px);
  }
  .vector-stem {
    height: 120px;
    bottom: 122px;
  }
  .vector-leaf {
    width: 68px;
    height: 36px;
  }
  .vector-leaf.leaf-one {
    bottom: 185px;
    left: calc(50% - 62px);
  }
  .vector-leaf.leaf-two {
    bottom: 215px;
    left: calc(50% - 1px);
  }
  .vector-leaf.leaf-three {
    bottom: 160px;
    left: calc(50% - 1px);
  }
  .vector-leaf.leaf-four {
    bottom: 245px;
    left: calc(50% - 48px);
  }
  .vector-art-top {
    top: 18px;
    font-size: 10px;
  }
  .orbit-node {
    width: 34px;
    height: 34px;
  }
  .orbit-node svg {
    width: 16px;
    height: 16px;
  }
  .node-recycle {
    top: 48px;
    left: 20px;
  }
  .node-points {
    top: 125px;
    right: 16px;
  }
  .vector-float-card {
    padding: 8px 10px;
    border-radius: 12px;
    gap: 7px;
  }
  .v-mini-icon {
    width: 24px;
    height: 24px;
  }
  .v-mini-icon svg {
    width: 14px;
    height: 14px;
  }
  .v-card-text small {
    font-size: 8.5px;
  }
  .v-card-text b {
    font-size: 11px;
  }
  .fc-left {
    left: 12px;
    bottom: 46px;
  }
  .fc-right {
    right: 12px;
    top: 42px;
  }
  .card-form-panel {
    order: 2;
    border-radius: 34px;
    margin-top: -32px;
    position: relative;
    z-index: 3;
    padding: 32px 24px 28px;
    background: #ffffff;
    box-shadow: 0 20px 50px rgba(22, 38, 90, 0.16);
  }
  .brand-row {
    margin-bottom: 16px;
  }
  .form-title-block {
    margin-bottom: 20px;
  }
  .form-heading {
    font-size: 24px;
  }
}

@media (max-width: 420px) {
  .card-art-panel {
    height: 260px;
  }
  .vector-art-canvas {
    min-height: 260px;
  }
  .card-form-panel {
    padding: 28px 20px 24px;
    border-radius: 28px;
    margin-top: -28px;
  }
  .form-heading {
    font-size: 22px;
  }
  .field-input {
    padding: 12px 14px;
    font-size: 13.5px;
  }
}
</style>
