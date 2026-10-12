import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import LandingView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import NasabahView from '../views/NasabahView.vue'
import KategoriView from '../views/KategoriView.vue'
import TransaksiView from '../views/TransaksiView.vue'
import LaporanView from '../views/LaporanView.vue'
import ValidasiPenarikanView from '../views/ValidasiPenarikanView.vue'
import NasabahDashboardView from '../views/NasabahDashboardView.vue'
import SurveyLingkunganView from '../views/SurveyLingkunganView.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/dashboard', name: 'dashboard', component: DashboardView },
  { path: '/nasabah', name: 'nasabah', component: NasabahView },
  { path: '/kategori', name: 'kategori', component: KategoriView },
  { path: '/transaksi', name: 'transaksi', component: TransaksiView },
  { path: '/laporan', name: 'laporan', component: LaporanView },
  { path: '/validasi-penarikan', name: 'validasi-penarikan', component: ValidasiPenarikanView },
  { path: '/nasabah-dashboard', name: 'nasabah-dashboard', component: NasabahDashboardView },
  { path: '/survey-lingkungan', name: 'survey-lingkungan', component: SurveyLingkunganView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  // Jika belum login → arahkan ke login
  if (!auth.token && to.name !== 'login' && to.name !== 'landing') {
    next({ name: 'login' })
    return
  }

  // Redirect otomatis sesuai role
  if (to.name === 'dashboard' && auth.user?.role === 'nasabah') {
    next({ name: 'nasabah-dashboard' })
    return
  }

  if (to.name === 'nasabah-dashboard' && auth.user?.role !== 'nasabah') {
    next({ name: 'dashboard' })
    return
  }

  next()
})


export default router
