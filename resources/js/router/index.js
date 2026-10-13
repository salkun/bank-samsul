import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/useAuthStore'
import LandingView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import NasabahView from '../views/NasabahView.vue'
import KategoriView from '../views/KategoriView.vue'
import TransaksiView from '../views/TransaksiView.vue'
import LaporanView from '../views/LaporanView.vue'
import BarangRedeemView from '../views/BarangRedeemView.vue'
import ValidasiPenukaranView from '../views/ValidasiPenukaranView.vue'
import NasabahDashboardView from '../views/NasabahDashboardView.vue'
import SurveyLingkunganView from '../views/SurveyLingkunganView.vue'
import PeringkatView from '../views/PeringkatView.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/nasabah', name: 'nasabah', component: NasabahView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/kategori', name: 'kategori', component: KategoriView, meta: { roles: ['admin'] } },
  { path: '/transaksi', name: 'transaksi', component: TransaksiView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/peringkat', name: 'peringkat', component: PeringkatView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/laporan', name: 'laporan', component: LaporanView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/barang-redeem-admin', name: 'barang-redeem-admin', component: BarangRedeemView, meta: { roles: ['admin'] } },
  { path: '/validasi-penukaran', name: 'validasi-penukaran', component: ValidasiPenukaranView, meta: { roles: ['admin', 'petugas'] } },
  { path: '/nasabah-dashboard', name: 'nasabah-dashboard', component: NasabahDashboardView, meta: { roles: ['nasabah'] } },
  { path: '/survey-lingkungan', name: 'survey-lingkungan', component: SurveyLingkunganView, meta: { roles: ['admin', 'petugas'] } },
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

  // Jika sudah login dan membuka login/landing → arahkan sesuai role
  if (auth.token && (to.name === 'login' || to.name === 'landing')) {
    if (auth.user?.role === 'nasabah') {
      next({ name: 'nasabah-dashboard' })
    } else {
      next({ name: 'dashboard' })
    }
    return
  }

  // Cek otorisasi peran berdasarkan meta.roles
  if (to.meta.roles && auth.user) {
    if (!to.meta.roles.includes(auth.user.role)) {
      if (auth.user.role === 'nasabah') {
        next({ name: 'nasabah-dashboard' })
      } else {
        next({ name: 'dashboard' })
      }
      return
    }
  }

  // Redirect otomatis default
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
