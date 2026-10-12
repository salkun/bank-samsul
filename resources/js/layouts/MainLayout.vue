<template>
  <div class="layout">
    <!-- Overlay (muncul saat sidebar aktif di mobile) -->
    <div v-if="sidebarOpen" class="overlay" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <Sidebar :class="{ open: sidebarOpen }" />

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <header class="main-header">
        <div class="left">
          <button class="btn-toggle" @click="toggleSidebar">
            <i class="bi bi-list"></i>
          </button>
          <h2>{{ pageTitle }}</h2>
        </div>
        <div class="right">
          <div class="user-info" v-if="auth.user">
            <i class="bi bi-person-circle"></i>
            <span>{{ auth.user.username }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="page-content">
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
import 'bootstrap-icons/font/bootstrap-icons.css'

const route = useRoute()
const auth = useAuthStore()
const sidebarOpen = ref(false)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard',
    nasabah: 'Data Nasabah',
    kategori: 'Kategori Sampah',
    transaksi: 'Transaksi Sampah',
    laporan: 'Laporan',
  }
  return titles[route.name] || 'Bank Sampah GreenSaver'
})
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f5f6fa;
  position: relative;
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 250px;
  height: 100%;
  background: white;
  transition: transform 0.3s ease;
  z-index: 20;
}

/* Hide sidebar by default on mobile */
@media (max-width: 991px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.open {
    transform: translateX(0);
  }
}

/* Overlay (gelap di belakang sidebar saat aktif di HP) */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 15;
}

/* Main content area */
.main-content {
  flex: 1;
  margin-left: 250px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

/* Responsif: jika layar kecil, margin dihapus */
@media (max-width: 991px) {
  .main-content {
    margin-left: 0;
  }
}

.main-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e5e5;
  position: sticky;
  top: 0;
  z-index: 10;
}

.main-header .left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.main-header h2 {
  font-size: 1.25rem;
  color: #2f3e46;
  margin: 0;
  font-weight: 600;
}

.btn-toggle {
  background: transparent;
  border: none;
  font-size: 1.6rem;
  color: hsl(160, 84%, 39%);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-toggle:hover {
  color: hsl(142, 71%, 45%);
}

.right {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(16, 185, 129, 0.1);
  padding: 0.4rem 0.8rem;
  border-radius: 0.5rem;
  color: #2f3e46;
}

.user-info i {
  color: hsl(160, 84%, 39%);
  font-size: 1.2rem;
}

.role {
  font-size: 0.75rem;
  color: #666;
  text-transform: capitalize;
}

.page-content {
  flex: 1;
  padding: 2rem;
  background: #f9fafb;
  overflow-y: auto;
}

/* Responsive */
@media (max-width: 768px) {
  .main-header {
    padding: 0.8rem 1rem;
  }
  .page-content {
    padding: 1rem;
  }
  .user-info {
    font-size: 0.9rem;
  }
}
</style>
