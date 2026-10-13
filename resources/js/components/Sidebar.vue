<template>
  <aside class="admin-sidebar" :class="{ open: isOpen }">
    <!-- Header Brand -->
    <div class="sidebar-brand-box">
      <div class="brand-crest">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </div>
      <div class="brand-info">
        <span class="brand-title">BankSamsul</span>
        <span class="brand-console-tag">Admin Console</span>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="nav-section-label">Menu Utama</div>

      <RouterLink
        v-for="item in filteredMenu"
        :key="item.name"
        :to="item.path"
        class="nav-link-item"
        :class="{ active: route.name === item.name }"
        @click="handleMenuClick"
      >
        <div class="link-icon-wrap">
          <i :class="item.icon"></i>
        </div>
        <span class="link-label">{{ item.label }}</span>

        <!-- Notification Badge for Validation -->
        <span
          v-if="item.name === 'validasi-penukaran' && pendingCount > 0 && auth.user?.role === 'admin'"
          class="nav-alert-chip"
        >
          <span class="alert-pulse"></span>
          {{ pendingCount }}
        </span>
      </RouterLink>
    </nav>

    <!-- Sidebar Footer with User Meta & Logout -->
    <div class="sidebar-footer-box">
      <div class="user-profile-tile" v-if="auth.user">
        <div class="profile-avatar">{{ userInitial }}</div>
        <div class="profile-details">
          <span class="profile-name">{{ auth.user.nama || auth.user.username }}</span>
          <span class="profile-role-badge">{{ auth.user.role }}</span>
        </div>
      </div>

      <button class="btn-sidebar-logout" @click="handleLogout" title="Keluar dari sistem">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Keluar Sistem</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import { computed, defineProps, ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import 'bootstrap-icons/font/bootstrap-icons.css'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

defineProps({
  isOpen: { type: Boolean, default: false }
})

// === State Notifikasi Admin ===
const pendingCount = ref(0)
const prevUnreadCount = ref(null)
let pollingTimer = null

const userInitial = computed(() => {
  const name = auth.user?.nama || auth.user?.username || 'A'
  return name.charAt(0).toUpperCase()
})

// === Menu Sidebar Admin & Petugas ===
const menu = [
  { name: 'dashboard', label: 'Ringkasan Dashboard', path: '/dashboard', icon: 'bi bi-speedometer2', roles: ['admin', 'petugas'] },
  { name: 'nasabah', label: 'Data Nasabah', path: '/nasabah', icon: 'bi bi-people', roles: ['admin', 'petugas'] },
  { name: 'kategori', label: 'Jenis & Harga Sampah', path: '/kategori', icon: 'bi bi-trash3', roles: ['admin'] },
  { name: 'transaksi', label: 'Transaksi Setoran', path: '/transaksi', icon: 'bi bi-arrow-left-right', roles: ['admin', 'petugas'] },
  { name: 'peringkat', label: 'Klasemen & Juara', path: '/peringkat', icon: 'bi bi-trophy', roles: ['admin', 'petugas'] },
  { name: 'barang-redeem-admin', label: 'Katalog Barang Redeem', path: '/barang-redeem-admin', icon: 'bi bi-gift', roles: ['admin'] },
  { name: 'validasi-penukaran', label: 'Validasi Penukaran', path: '/validasi-penukaran', icon: 'bi bi-bag-check', roles: ['admin', 'petugas'] },
  { name: 'laporan', label: 'Laporan & Rekapitulasi', path: '/laporan', icon: 'bi bi-bar-chart-line', roles: ['admin', 'petugas'] },
  { name: 'survey-lingkungan', label: 'Survey Lingkungan', path: '/survey-lingkungan', icon: 'bi bi-clipboard2-check', roles: ['admin', 'petugas'] },
]

// === Filter menu berdasar role user ===
const filteredMenu = computed(() => {
  const role = auth.user?.role || 'guest'
  return menu.filter((item) => item.roles.includes(role))
})

// === Polling Notifikasi Admin ===
const checkAdminNotifications = async () => {
  if (auth.user?.role !== 'admin' || !auth.token) return
  if (typeof document !== 'undefined' && document.visibilityState !== 'visible') return

  try {
    const res = await axios.get('/api/admin/notifikasi', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    const unread = res.data.unread_count ?? 0
    const totalMenunggu = res.data.total_menunggu ?? res.data.pending_penukaran_count ?? 0

    pendingCount.value = totalMenunggu

    if (prevUnreadCount.value !== null && unread > prevUnreadCount.value) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: `Ada ${unread} penukaran baru menunggu verifikasi!`,
        showConfirmButton: false,
        timer: 4000
      })
    }
    prevUnreadCount.value = unread
  } catch (err) {
    // Abaikan jika error jaringan / unauthorized
  }
}

const onVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    checkAdminNotifications()
  }
}

onMounted(() => {
  if (auth.user?.role === 'admin') {
    checkAdminNotifications()
    pollingTimer = setInterval(checkAdminNotifications, 20000)
    document.addEventListener('visibilitychange', onVisibilityChange)
  }
})

onUnmounted(() => {
  if (pollingTimer) {
    clearInterval(pollingTimer)
    pollingTimer = null
  }
  document.removeEventListener('visibilitychange', onVisibilityChange)
})

const handleMenuClick = () => {
  if (window.innerWidth < 991) {
    const sb = document.querySelector('.admin-sidebar')
    if (sb) sb.classList.remove('open')
  }
}

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Konfirmasi Logout',
    text: 'Keluar dari sesi administrator Bank Samsul?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#173f32',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Logout',
    cancelButtonText: 'Batal'
  })
  if (result.isConfirmed) {
    if (pollingTimer) {
      clearInterval(pollingTimer)
      pollingTimer = null
    }
    await auth.logout()
    router.push('/login')
  }
}
</script>

<style scoped>
/* ==========================================================================
   ADMIN SIDEBAR - SLEEK PRO SLATE THEME (NOT OVER-GREEN, CLEAN & LIGHTWEIGHT)
   ========================================================================== */
.admin-sidebar {
  --sb-bg: #0f172a;           /* Deep Neutral Slate 900 */
  --sb-surface: #1e293b;      /* Slate 800 */
  --sb-accent: #10b981;       /* Subtle Emerald micro-accent */
  --sb-text: #94a3b8;         /* Slate 400 */
  --sb-text-active: #ffffff;  /* Crisp White */
  --sb-muted: #64748b;        /* Slate 500 */
  --sb-line: rgba(255, 255, 255, 0.07);

  width: 240px;
  height: 100vh;
  background: var(--sb-bg);
  color: var(--sb-text);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  border-right: 1px solid var(--sb-line);
  transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  z-index: 30;
  box-shadow: 2px 0 16px rgba(0, 0, 0, 0.2);
}

/* Header Brand */
.sidebar-brand-box {
  padding: 16px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid var(--sb-line);
}

.brand-crest {
  width: 34px;
  height: 34px;
  background: var(--sb-surface);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #34d399;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.brand-crest svg {
  width: 18px;
  height: 18px;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  line-height: 1.15;
}

.brand-console-tag {
  font-size: 0.64rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

/* Navigation Links */
.sidebar-nav {
  flex: 1;
  padding: 14px 10px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.nav-section-label {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--sb-muted);
  padding: 4px 8px 6px;
}

.nav-link-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 8px;
  color: var(--sb-text);
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 500;
  transition: all 0.18s ease;
  position: relative;
}

.link-icon-wrap {
  width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  color: var(--sb-muted);
  transition: color 0.18s;
}

.link-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-link-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #f1f5f9;
}

.nav-link-item:hover .link-icon-wrap {
  color: #e2e8f0;
}

.nav-link-item.active {
  background: var(--sb-surface);
  color: var(--sb-text-active);
  font-weight: 600;
  border-left: 3px solid var(--sb-accent);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.nav-link-item.active .link-icon-wrap {
  color: #34d399;
}

/* Notification Badge */
.nav-alert-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #f97316;
  color: #ffffff;
  font-size: 0.66rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 999px;
}

.alert-pulse {
  width: 4px;
  height: 4px;
  background: #ffffff;
  border-radius: 50%;
  animation: pulseDot 1.4s infinite;
}

@keyframes pulseDot {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.6); opacity: 0.4; }
  100% { transform: scale(1); opacity: 1; }
}

/* Footer Box */
.sidebar-footer-box {
  padding: 12px 10px;
  border-top: 1px solid var(--sb-line);
  background: rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.user-profile-tile {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.profile-avatar {
  width: 28px;
  height: 28px;
  background: #334155;
  color: #f8fafc;
  font-weight: 800;
  font-size: 0.78rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.profile-name {
  font-size: 0.78rem;
  font-weight: 600;
  color: #f1f5f9;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-role-badge {
  font-size: 0.62rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
}

.btn-sidebar-logout {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  padding: 7px;
  border-radius: 6px;
  font-size: 0.76rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
}

.btn-sidebar-logout svg {
  width: 13px;
  height: 13px;
}

.btn-sidebar-logout:hover {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
}

/* Responsiveness */
@media (max-width: 991px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }
  .admin-sidebar.open {
    transform: translateX(0);
  }
}
</style>
