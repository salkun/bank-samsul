<template>
  <aside class="sidebar" :class="{ open: isOpen }">
    <!-- Header -->
    <div class="sidebar-header">
      <h2 class="brand">
        <i class="bi bi-recycle"></i>
        <span>BankSamsul</span>
      </h2>
    </div>

    <!-- Navigasi -->
    <nav class="menu">
      <RouterLink
        v-for="item in filteredMenu"
        :key="item.name"
        :to="item.path"
        class="menu-item"
        :class="{ active: route.name === item.name }"
        @click="handleMenuClick"
      >
        <i :class="item.icon"></i>
        <span>{{ item.label }}</span>
      </RouterLink>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
      <button class="logout-btn" @click="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import { computed, defineProps } from 'vue'
import 'bootstrap-icons/font/bootstrap-icons.css'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// toggle dari MainLayout (responsif)
const props = defineProps({
  isOpen: { type: Boolean, default: false }
})

// === Menu Sidebar ===
const menu = [
  { name: 'dashboard', label: 'Dashboard', path: '/dashboard', icon: 'bi bi-speedometer2', roles: ['admin', 'petugas', 'nasabah'] },
  { name: 'nasabah', label: 'Data Nasabah', path: '/nasabah', icon: 'bi bi-people', roles: ['admin', 'petugas'] },
  { name: 'kategori', label: 'Jenis Sampah', path: '/kategori', icon: 'bi bi-trash', roles: ['admin'] },
  { name: 'transaksi', label: 'Transaksi', path: '/transaksi', icon: 'bi bi-cash-coin', roles: ['admin', 'petugas'] },
  { name: 'laporan', label: 'Laporan', path: '/laporan', icon: 'bi bi-bar-chart-line', roles: ['admin', 'petugas'] },
  { name: 'validasi-penarikan', label: 'Validasi Penarikan', path: '/validasi-penarikan', icon: 'bi bi-cash-stack', roles: ['admin'] },
  { name: 'survey-lingkungan', label: 'Survey Lingkungan', path: '/survey-lingkungan', icon: 'bi bi-clipboard2-check', roles: ['admin', 'petugas'] },
  { name: 'riwayat', label: 'Riwayat Saya', path: '/riwayat', icon: 'bi bi-clock-history', roles: ['nasabah'] },
]

// === Filter menu berdasar role user ===
const filteredMenu = computed(() => {
  const role = auth.user?.role || 'guest'
  return menu.filter((item) => item.roles.includes(role))
})

// auto-close sidebar di hp
const handleMenuClick = () => {
  const width = window.innerWidth
  if (width < 991) document.querySelector('.sidebar').classList.remove('open')
}

// logout
const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
    width: 250px;
    height: 100vh;
    background: white;
    color: #2f3e46;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: fixed;
    left: 0;
    top: 0;
    border-right: 1px solid #e5e5e5;
    transition: transform 0.3s ease;
    z-index: 20;
}
.sidebar-header {
    padding: 1.5rem 1rem;
    text-align: center;
    border-bottom: 1px solid #e5e5e5;
}
.brand {
    font-size: 1.4rem;
    font-weight: 700;
    color: hsl(160, 84%, 39%);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.brand i {
    font-size: 1.5rem;
}
.menu {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem 0;
}
.menu-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.9rem 1.5rem;
    color: #2f3e46;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.menu-item i {
    font-size: 1.2rem;
    color: #4b5563;
}
.menu-item:hover {
    background: rgba(16, 185, 129, 0.1);
    color: hsl(160, 84%, 39%);
}
.menu-item.active {
    background: rgba(16, 185, 129, 0.15);
    color: hsl(160, 84%, 39%);
    border-left: 4px solid hsl(160, 84%, 39%);
    font-weight: 600;
}
.menu-item.active i {
    color: hsl(160, 84%, 39%);
}
/* Footer */
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e5e5e5;
}
.logout-btn {
    width: 100%;
    background: rgba(220, 38, 38, 0.1);
    border: none;
    color: #dc2626;
    font-weight: 600;
    padding: 0.8rem;
    border-radius: 0.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}
.logout-btn:hover {
    background: rgba(220, 38, 38, 0.2);
    transform: translateY(-1px);
}
/* Responsif */
@media (max-width: 991px) {
     .sidebar {
        transform: translateX(-100%);
        }
        .sidebar.open {
        transform: translateX(0);
        }
    }
</style>
