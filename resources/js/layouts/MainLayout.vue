<template>
  <div class="admin-layout">
    <!-- Overlay for mobile drawer -->
    <div v-if="sidebarOpen" class="layout-overlay" @click="toggleSidebar"></div>

    <!-- Admin Sidebar -->
    <Sidebar :is-open="sidebarOpen" />

    <!-- Main Content Stage -->
    <div class="layout-stage">
      <!-- Header Bar -->
      <header class="admin-header">
        <div class="header-left">
          <button class="btn-sidebar-toggle" @click="toggleSidebar" aria-label="Toggle navigasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
          
          <div class="page-title-box">
            <span class="breadcrumb-sub">Sistem Manajemen Bank Sampah</span>
            <h1 class="page-title-heading">{{ pageTitle }}</h1>
          </div>
        </div>

        <div class="header-right">
          <!-- System Status Pill -->
          <div class="system-status-chip">
            <span class="status-live-dot"></span>
            <span class="status-live-text">Server Aktif</span>
          </div>

          <!-- User Info Chip -->
          <div class="admin-user-chip" v-if="auth.user">
            <div class="admin-chip-avatar">{{ userInitial }}</div>
            <div class="admin-chip-info">
              <span class="admin-chip-name">{{ auth.user.nama || auth.user.username }}</span>
              <span class="admin-chip-role">{{ auth.user.role }}</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Page Content Slot -->
      <main class="admin-page-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue'
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'

const route = useRoute()
const auth = useAuthStore()
const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const userInitial = computed(() => {
  const name = auth.user?.nama || auth.user?.username || 'A'
  return name.charAt(0).toUpperCase()
})

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Ringkasan Dashboard',
    nasabah: 'Data Nasabah Siswa',
    kategori: 'Kategori & Tarif Sampah',
    transaksi: 'Transaksi Setoran Sampah',
    peringkat: 'Papan Peringkat & Juara Lingkungan',
    laporan: 'Laporan & Rekapitulasi',
    'barang-redeem-admin': 'Kelola Barang Redeem Hadiah',
    'validasi-penukaran': 'Validasi & Penyerahan Hadiah',
    'survey-lingkungan': 'Survey Kebersihan & Lingkungan',
    'nasabah-dashboard': 'Dashboard Nasabah',
  }
  return titles[route.name] || 'Bank Sampah Al-Muhajirin'
})
</script>

<style scoped>
/* ==========================================================================
   MAIN ADMIN LAYOUT - CLEAN, HIGH-AGENCY, DESKTOP & MOBILE RESPONSIVE
   ========================================================================== */
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f8faf8;
  position: relative;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #14231d;
}

/* Stage */
.layout-stage {
  flex: 1;
  margin-left: 240px;
  display: flex;
  flex-direction: column;
  min-width: 0;
  transition: margin-left 0.28s ease;
}

/* Header */
.admin-header {
  height: 56px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid #e2e8f0;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 20;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-sidebar-toggle {
  display: none;
  background: transparent;
  border: 1px solid #e2e8f0;
  width: 34px;
  height: 34px;
  border-radius: 7px;
  align-items: center;
  justify-content: center;
  color: #0f172a;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-sidebar-toggle svg {
  width: 18px;
  height: 18px;
}

.btn-sidebar-toggle:hover {
  background: #f1f5f9;
}

.page-title-box {
  display: flex;
  flex-direction: column;
}

.breadcrumb-sub {
  font-size: 0.64rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.page-title-heading {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.015em;
  line-height: 1.15;
}

/* Header Right */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.system-status-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 700;
  color: #065f46;
}

.status-live-dot {
  width: 5px;
  height: 5px;
  background: #10b981;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}

.admin-user-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 3px 8px 3px 3px;
  background: #f1f5f9;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
}

.admin-chip-avatar {
  width: 28px;
  height: 28px;
  background: #0f172a;
  color: #38bdf8;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.78rem;
}

.admin-chip-info {
  display: flex;
  flex-direction: column;
}

.admin-chip-name {
  font-size: 0.78rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.1;
}

.admin-chip-role {
  font-size: 0.62rem;
  font-weight: 600;
  color: #64748b;
  text-transform: capitalize;
}

/* Page Content Area */
.admin-page-content {
  flex: 1;
  padding: 16px 20px;
  background: #f8fafc;
  overflow-y: auto;
}

/* Overlay */
.layout-overlay {
  position: fixed;
  inset: 0;
  background: rgba(14, 35, 27, 0.5);
  backdrop-filter: blur(4px);
  z-index: 25;
}

/* Responsiveness */
@media (max-width: 991px) {
  .layout-stage {
    margin-left: 0;
  }
  .btn-sidebar-toggle {
    display: inline-flex;
  }
  .admin-header {
    padding: 0 16px;
  }
  .admin-page-content {
    padding: 16px 12px;
  }
  .system-status-chip {
    display: none;
  }
}
</style>
