<template>
  <MainLayout>
    <div class="admin-dashboard-deck">
      <!-- DASHBOARD TOP HEADER -->
      <section class="deck-hero-row">
        <div class="deck-intro">
          <div class="intro-badge">
            <span class="pulse-live-indicator"></span>
            <span>Pusat Kendali Operasional</span>
          </div>
          <h2 class="deck-greeting">
            Selamat Datang, <strong>{{ auth.user?.nama || 'Administrator' }}</strong>
          </h2>
          <p class="deck-subtext">
            Pantau aktivitas setoran nasabah, validasi penukaran barang, dan evaluasi capaian lingkungan secara terpadu.
          </p>
        </div>

        <div class="deck-quick-actions">
          <RouterLink to="/transaksi" class="btn-quick-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            <span>Input Setoran Sampah</span>
          </RouterLink>
          <RouterLink to="/validasi-penukaran" class="btn-quick-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span>Validasi Hadiah</span>
          </RouterLink>
        </div>
      </section>

      <!-- LOADING STATE -->
      <div v-if="loading" class="deck-loading-box">
        <div class="deck-spinner"></div>
        <p>Memuat rekapitulasi data dashboard...</p>
      </div>

      <!-- ERROR STATE -->
      <div v-else-if="error" class="deck-alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ error }}</span>
      </div>

      <!-- MAIN BENTO CONTENT -->
      <div v-else class="deck-body">
        <!-- 1. COMPACT KPI BENTO GRID (4 BALANCED CARDS) -->
        <section class="bento-command-grid">
          <!-- Card 1: Poin Setoran Bulan Ini -->
          <div class="bento-tile tile-secondary">
            <div class="tile-top-row">
              <span class="tile-label">Setoran Poin Bulan Ini</span>
              <div class="tile-icon-bubble bubble-deposit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/></svg>
              </div>
            </div>

            <div class="tile-secondary-metric">
              <span class="sec-num">{{ formatNum(stats.total_setoran) }}</span>
              <span class="sec-unit">Poin</span>
            </div>

            <div class="tile-status-chip chip-green">
              <span>Bulan Berjalan</span>
            </div>

            <RouterLink to="/transaksi" class="tile-link">
              <span>Catatan Transaksi</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </RouterLink>
          </div>

          <!-- Secondary Bento Card: Total Nasabah Terdaftar -->
          <div class="bento-tile tile-secondary">
            <div class="tile-top-row">
              <span class="tile-label">Total Nasabah Terdaftar</span>
              <div class="tile-icon-bubble bubble-users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
            </div>

            <div class="tile-secondary-metric">
              <span class="sec-num">{{ formatNum(stats.total_nasabah) }}</span>
              <span class="sec-unit">Siswa</span>
            </div>

            <div class="tile-status-chip chip-green">
              <span>✓ Akun Terverifikasi</span>
            </div>

            <RouterLink to="/nasabah" class="tile-link">
              <span>Kelola Data Siswa</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </RouterLink>
          </div>

          <!-- Secondary Bento Card: Poin Ditukar Hadiah -->
          <div class="bento-tile tile-secondary">
            <div class="tile-top-row">
              <span class="tile-label">Poin Ditukar Hadiah</span>
              <div class="tile-icon-bubble bubble-rewards">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
              </div>
            </div>

            <div class="tile-secondary-metric">
              <span class="sec-num">{{ formatNum(stats.total_penarikan) }}</span>
              <span class="sec-unit">Poin</span>
            </div>

            <div class="tile-status-chip chip-amber">
              <span>Hadiah Terdistribusi</span>
            </div>

            <RouterLink to="/validasi-penukaran" class="tile-link">
              <span>Validasi Penukaran</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </RouterLink>
          </div>

          <!-- Secondary Bento Card: Jenis Sampah Populer -->
          <div class="bento-tile tile-secondary">
            <div class="tile-top-row">
              <span class="tile-label">Kategori Paling Aktif</span>
              <div class="tile-icon-bubble bubble-star">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
            </div>

            <div class="tile-secondary-metric">
              <span class="sec-num-text">{{ stats.jenis_populer || '-' }}</span>
            </div>

            <div class="tile-status-chip chip-blue">
              <span>Volume Setoran Terbanyak</span>
            </div>

            <RouterLink to="/kategori" class="tile-link">
              <span>Lihat Kategori Sampah</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </RouterLink>
          </div>
        </section>

        <!-- 2. ANALYTICS & VISUALIZATION CHARTS -->
        <section class="charts-bento-grid">
          <!-- Line Chart: Tren Setoran -->
          <div class="chart-tile chart-line-tile">
            <div class="chart-header">
              <div class="chart-title-wrap">
                <div class="chart-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div>
                  <h3 class="chart-title">Tren Setoran Poin per Bulan</h3>
                  <p class="chart-sub">Pergerakan akumulasi poin setoran sampah sepanjang periode aktif.</p>
                </div>
              </div>
            </div>

            <div class="chart-canvas-container">
              <canvas id="lineChart" height="110"></canvas>
            </div>
          </div>

          <!-- Pie / Doughnut Chart: Komposisi Sampah -->
          <div class="chart-tile chart-pie-tile">
            <div class="chart-header">
              <div class="chart-title-wrap">
                <div class="chart-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                </div>
                <div>
                  <h3 class="chart-title">Komposisi Kategori Sampah</h3>
                  <p class="chart-sub">Distribusi jenis sampah yang disetorkan oleh nasabah.</p>
                </div>
              </div>
            </div>

            <div class="chart-canvas-container pie-container">
              <canvas id="pieChart" height="140"></canvas>
            </div>
          </div>
        </section>

        <!-- 3. RECENT TRANSACTIONS LEDGER -->
        <section class="ledger-card">
          <div class="ledger-header">
            <div class="ledger-title-wrap">
              <div class="ledger-icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <div>
                <h3 class="ledger-title">Aktivitas Transaksi Terbaru</h3>
                <p class="ledger-sub">Daftar transaksi setoran dan penukaran barang yang baru saja tercatat.</p>
              </div>
            </div>

            <RouterLink to="/transaksi" class="btn-ledger-view-all">
              <span>Buka Semua Transaksi</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </RouterLink>
          </div>

          <!-- Desktop Table -->
          <div class="ledger-table-wrap">
            <table class="ledger-table">
              <thead>
                <tr>
                  <th width="6%">#</th>
                  <th width="28%">Nama Nasabah</th>
                  <th width="20%">Tipe Transaksi</th>
                  <th width="24%">Jumlah Poin</th>
                  <th width="22%">Waktu Transaksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(t, i) in transaksi" :key="i">
                  <td class="col-center text-muted">{{ i + 1 }}</td>
                  <td>
                    <div class="user-cell">
                      <div class="user-cell-avatar">{{ (t.nama || 'N').charAt(0).toUpperCase() }}</div>
                      <span class="user-cell-name">{{ t.nama }}</span>
                    </div>
                  </td>
                  <td>
                    <span
                      class="type-chip"
                      :class="(t.jenis || '').toLowerCase() === 'setoran' ? 'chip-setoran' : 'chip-penukaran'"
                    >
                      {{ (t.jenis || '').toLowerCase() === 'setoran' ? '♻ Setoran Sampah' : '🎁 Tukar Hadiah' }}
                    </span>
                  </td>
                  <td>
                    <span
                      class="amount-chip"
                      :class="(t.jenis || '').toLowerCase() === 'setoran' ? 'amount-plus' : 'amount-minus'"
                    >
                      {{ (t.jenis || '').toLowerCase() === 'setoran' ? '+' : '-' }}{{ formatNum(t.jumlah) }} Poin
                    </span>
                  </td>
                  <td class="col-date">{{ formatDate(t.tanggal) }}</td>
                </tr>
                <tr v-if="!transaksi.length">
                  <td colspan="5" class="empty-ledger-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>Belum ada transaksi terbaru yang tercatat.</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards List -->
          <div class="ledger-mobile-list">
            <div v-for="(t, i) in transaksi" :key="'mob-' + i" class="ledger-m-card">
              <div class="m-card-header">
                <div class="user-cell">
                  <div class="user-cell-avatar">{{ (t.nama || 'N').charAt(0).toUpperCase() }}</div>
                  <span class="user-cell-name">{{ t.nama }}</span>
                </div>
                <span
                  class="type-chip"
                  :class="(t.jenis || '').toLowerCase() === 'setoran' ? 'chip-setoran' : 'chip-penukaran'"
                >
                  {{ (t.jenis || '').toLowerCase() === 'setoran' ? 'Setoran' : 'Tukar' }}
                </span>
              </div>
              <div class="m-card-meta">
                <span
                  class="amount-chip"
                  :class="(t.jenis || '').toLowerCase() === 'setoran' ? 'amount-plus' : 'amount-minus'"
                >
                  {{ (t.jenis || '').toLowerCase() === 'setoran' ? '+' : '-' }}{{ formatNum(t.jumlah) }} Poin
                </span>
                <span class="col-date">{{ formatDate(t.tanggal) }}</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Chart from 'chart.js/auto'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(true)
const error = ref(null)

const stats = ref({
  total_nasabah: 0,
  total_setoran: 0,
  total_penarikan: 0,
  jenis_populer: '-',
  bulan: [],
  total_per_bulan: [],
  kategori_labels: [],
  kategori_values: [],
  transaksi_terbaru: []
})

const transaksi = ref([])

let lineChart = null
let pieChart = null

const formatNum = (num) => Number(num || 0).toLocaleString('id-ID')

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

const nextTickSafe = () => new Promise((resolve) => setTimeout(resolve, 80))

const loadDashboard = async () => {
  loading.value = true
  error.value = null
  const token = auth.token || localStorage.getItem('token')
  if (!token) {
    loading.value = false
    router.push('/login')
    return
  }

  try {
    const res = await axios.get('/api/laporan/dashboard', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    const d = res.data || {}

    const rawList = Array.isArray(d.setoran_terbaru)
      ? d.setoran_terbaru
      : (Array.isArray(d.transaksi_terbaru) ? d.transaksi_terbaru : [])

    stats.value = {
      total_nasabah: d.total_nasabah ?? 0,
      total_setoran: d.total_poin_masuk ?? d.total_setoran ?? 0,
      total_penarikan: d.total_poin_dipakai ?? d.total_penarikan ?? 0,
      penukaran_menunggu: d.penukaran_menunggu ?? 0,
      jenis_populer: d.jenis_populer ?? '-',
      bulan: Array.isArray(d.bulan) ? d.bulan : [],
      total_per_bulan: Array.isArray(d.total_per_bulan) ? d.total_per_bulan : [],
      kategori_labels: Array.isArray(d.kategori_labels) ? d.kategori_labels : [],
      kategori_values: Array.isArray(d.kategori_values) ? d.kategori_values : [],
      transaksi_terbaru: rawList
    }

    transaksi.value = rawList.map((t) => ({
      nama: t.nama ?? (t.user?.nama) ?? (t.user_nama ?? 'Nasabah'),
      jenis: t.jenis ?? t.type ?? 'setoran',
      jumlah: t.poin ?? t.jumlah ?? t.total ?? 0,
      tanggal: t.tanggal ?? t.created_at ?? null,
      jenis_sampah: t.jenis_sampah ?? null,
      berat: t.berat ?? null
    }))

    loading.value = false
    await nextTickSafe()
    renderCharts()
  } catch (err) {
    console.error('Load dashboard error', err)
    if (err.response?.status === 401) {
      auth.logout()
      router.push('/login')
      return
    }
    error.value = err.response?.data?.message || 'Gagal memuat data dashboard. Periksa koneksi API.'
    loading.value = false
  }
}

const renderCharts = () => {
  const labels = stats.value.bulan || []
  const totals = stats.value.total_per_bulan || []
  const kLabels = stats.value.kategori_labels || []
  const kValues = stats.value.kategori_values || []

  const lineEl = document.getElementById('lineChart')
  const pieEl = document.getElementById('pieChart')
  if (!lineEl || !pieEl) return

  try { if (lineChart) lineChart.destroy() } catch (e) {}
  try { if (pieChart) pieChart.destroy() } catch (e) {}

  // 1. Line Chart with sleek spline and emerald area
  const ctx = lineEl.getContext('2d')
  let gradientFill = 'rgba(5, 150, 105, 0.08)'
  if (ctx) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 200)
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.22)')
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0.01)')
    gradientFill = gradient
  }

  lineChart = new Chart(lineEl, {
    type: 'line',
    data: {
      labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
      datasets: [{
        label: 'Setoran Poin',
        data: totals.length ? totals : [0, 0, 0, 0, 0, 0],
        fill: true,
        borderColor: '#059669',
        borderWidth: 2,
        backgroundColor: gradientFill,
        tension: 0.35,
        pointBackgroundColor: '#0f172a',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        pointRadius: 3,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f172a',
          titleFont: { size: 11, weight: 'bold' },
          bodyFont: { size: 11 },
          padding: 8,
          cornerRadius: 6,
          callbacks: {
            label: (ctx) => ` ${Number(ctx.raw || 0).toLocaleString('id-ID')} Poin`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#64748b', font: { size: 10, weight: '600' } }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#f1f5f9' },
          ticks: {
            color: '#64748b',
            font: { size: 10 },
            callback: (v) => Number(v).toLocaleString('id-ID')
          }
        }
      }
    }
  })

  // 2. Doughnut Chart for Waste Categories
  pieChart = new Chart(pieEl, {
    type: 'doughnut',
    data: {
      labels: kLabels.length ? kLabels : ['Plastik', 'Kertas', 'Logam', 'Kaca', 'Organik'],
      datasets: [{
        data: kValues.length ? kValues : [35, 25, 20, 12, 8],
        backgroundColor: [
          '#0f172a',
          '#059669',
          '#0284c7',
          '#f59e0b',
          '#8b5cf6',
          '#ec4899'
        ],
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverOffset: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 10,
            boxHeight: 10,
            padding: 10,
            font: { size: 10, weight: '600' },
            color: '#475569'
          }
        },
        tooltip: {
          backgroundColor: '#0f172a',
          padding: 8,
          cornerRadius: 6
        }
      }
    }
  })
}

onMounted(loadDashboard)
</script>

<style scoped>
/* ==========================================================================
   ADMIN DASHBOARD DECK - COMPACT PRO SAAS DASHBOARD (CLEAN & BALANCED)
   ========================================================================== */
.admin-dashboard-deck {
  --navy: #0f172a;
  --navy-light: #1e293b;
  --emerald: #059669;
  --emerald-bg: #ecfdf5;
  --ink: #0f172a;
  --muted: #64748b;
  --line: #e2e8f0;
  --card-bg: #ffffff;
  --radius: 10px;
  --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);

  max-width: 1280px;
  margin: 0 auto;
}

/* ==============================================
   1. HERO INTRODUCTION ROW
   ============================================== */
.deck-hero-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.intro-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--emerald-bg);
  border: 1px solid #a7f3d0;
  padding: 2px 7px;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  color: #065f46;
  margin-bottom: 3px;
}

.pulse-live-indicator {
  width: 5px;
  height: 5px;
  background: #10b981;
  border-radius: 50%;
  animation: pulseLight 1.5s infinite;
}

@keyframes pulseLight {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.5); opacity: 0.4; }
  100% { transform: scale(1); opacity: 1; }
}

.deck-greeting {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.02em;
  margin: 0 0 2px;
  line-height: 1.2;
}

.deck-subtext {
  font-size: 0.8rem;
  color: var(--muted);
  margin: 0;
  max-width: 600px;
}

.deck-quick-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-quick-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--navy);
  color: #fff;
  border: none;
  padding: 7px 13px;
  border-radius: 7px;
  font-size: 0.78rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
  transition: all 0.18s ease;
}

.btn-quick-primary svg {
  width: 14px;
  height: 14px;
}

.btn-quick-primary:hover {
  background: #1e293b;
  transform: translateY(-1px);
}

.btn-quick-secondary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff;
  color: var(--navy);
  border: 1px solid var(--line);
  padding: 7px 13px;
  border-radius: 7px;
  font-size: 0.78rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.18s ease;
}

.btn-quick-secondary svg {
  width: 14px;
  height: 14px;
}

.btn-quick-secondary:hover {
  background: #f1f5f9;
}

/* ==============================================
   2. BENTO COMMAND GRID (4 BALANCED CARDS)
   ============================================== */
.bento-command-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 14px;
}

.bento-tile {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 13px 15px;
  box-shadow: var(--shadow-card);
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.bento-tile:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.08);
}

.tile-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  margin-bottom: 4px;
}

.tile-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.tile-icon-bubble {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.tile-icon-bubble svg {
  width: 15px;
  height: 15px;
}

.bubble-deposit { background: #ecfdf5; color: #059669; }
.bubble-users { background: #eff6ff; color: #2563eb; }
.bubble-rewards { background: #fef3c7; color: #d97706; }
.bubble-star { background: #f3e8ff; color: #7c3aed; }

.tile-secondary-metric {
  margin: 6px 0 4px;
  display: flex;
  align-items: baseline;
  gap: 5px;
}

.sec-num {
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.02em;
  line-height: 1.1;
}

.sec-unit {
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--muted);
}

.sec-num-text {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--navy);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.tile-status-chip {
  display: inline-flex;
  align-items: center;
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 0.68rem;
  font-weight: 600;
  margin-bottom: 8px;
  width: fit-content;
}

.chip-green { background: #ecfdf5; color: #065f46; }
.chip-amber { background: #fffbeb; color: #92400e; }
.chip-blue { background: #eff6ff; color: #1e40af; }

.tile-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #0284c7;
  font-size: 0.74rem;
  font-weight: 600;
  text-decoration: none;
  padding-top: 6px;
  border-top: 1px solid #f1f5f9;
  transition: gap 0.18s ease;
}

.tile-link svg {
  width: 12px;
  height: 12px;
}

.tile-link:hover {
  gap: 7px;
}

/* ==============================================
   3. CHARTS BENTO GRID
   ============================================== */
.charts-bento-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 12px;
  margin-bottom: 14px;
}

.chart-tile {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 13px 15px;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
}

.chart-header {
  margin-bottom: 10px;
}

.chart-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.chart-icon-box {
  width: 26px;
  height: 26px;
  background: #f1f5f9;
  color: var(--navy);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-icon-box svg {
  width: 14px;
  height: 14px;
}

.chart-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--navy);
  margin: 0 0 1px;
}

.chart-sub {
  font-size: 0.72rem;
  color: var(--muted);
  margin: 0;
}

.chart-canvas-container {
  position: relative;
  width: 100%;
  height: 195px;
}

.pie-container {
  height: 195px;
}

/* ==============================================
   4. RECENT TRANSACTIONS LEDGER
   ============================================== */
.ledger-card {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 13px 15px;
  box-shadow: var(--shadow-card);
}

.ledger-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.ledger-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ledger-icon-box {
  width: 26px;
  height: 26px;
  background: #ecfdf5;
  color: #059669;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.ledger-icon-box svg {
  width: 15px;
  height: 15px;
}

.ledger-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--navy);
  margin: 0 0 1px;
}

.ledger-sub {
  font-size: 0.72rem;
  color: var(--muted);
  margin: 0;
}

.btn-ledger-view-all {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #f1f5f9;
  color: var(--navy);
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.18s ease;
}

.btn-ledger-view-all svg {
  width: 12px;
  height: 12px;
}

.btn-ledger-view-all:hover {
  background: var(--navy);
  color: #fff;
}

/* Table */
.ledger-table-wrap {
  overflow-x: auto;
}

.ledger-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.78rem;
}

.ledger-table th {
  background: #f8fafc;
  color: var(--muted);
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 7px 10px;
  border-bottom: 1px solid var(--line);
}

.ledger-table td {
  padding: 8px 10px;
  border-bottom: 1px solid var(--line);
  color: var(--ink);
}

.ledger-table tr:hover td {
  background: #f8fafc;
}

.col-center {
  text-align: center;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-cell-avatar {
  width: 24px;
  height: 24px;
  background: #e2e8f0;
  color: var(--navy);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.72rem;
  font-weight: 800;
}

.user-cell-name {
  font-weight: 600;
  color: var(--ink);
}

.type-chip {
  display: inline-flex;
  align-items: center;
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
}

.chip-setoran { background: #ecfdf5; color: #065f46; }
.chip-penukaran { background: #eff6ff; color: #1e40af; }

.amount-chip {
  font-weight: 700;
  font-size: 0.78rem;
}

.amount-plus { color: #059669; }
.amount-minus { color: #2563eb; }

.col-date {
  font-size: 0.74rem;
  color: var(--muted);
}

.empty-ledger-row {
  text-align: center;
  padding: 24px 10px;
  color: var(--muted);
}

.empty-ledger-row svg {
  width: 24px;
  height: 24px;
  display: block;
  margin: 0 auto 4px;
  opacity: 0.5;
}

/* Mobile List */
.ledger-mobile-list {
  display: none;
}

/* ==============================================
   5. LOADING & STATES
   ============================================== */
.deck-loading-box {
  text-align: center;
  padding: 40px 16px;
  color: var(--muted);
}

.deck-spinner {
  width: 28px;
  height: 28px;
  border: 2px solid var(--line);
  border-top-color: var(--navy);
  border-radius: 50%;
  animation: spinDeck 0.8s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spinDeck {
  to { transform: rotate(360deg); }
}

.deck-alert-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fee2e2;
  border: 1px solid #fca5a5;
  color: #991b1b;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
}

.deck-alert-error svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* ==============================================
   6. RESPONSIVENESS
   ============================================== */
@media (max-width: 1200px) {
  .bento-command-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 991px) {
  .charts-bento-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .deck-hero-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .deck-greeting {
    font-size: 1.15rem;
  }

  .deck-quick-actions {
    width: 100%;
    flex-direction: column;
  }

  .btn-quick-primary,
  .btn-quick-secondary {
    width: 100%;
    justify-content: center;
  }

  .bento-command-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .ledger-table-wrap {
    display: none;
  }

  .ledger-mobile-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .ledger-m-card {
    background: #f8fafc;
    border: 1px solid var(--line);
    border-radius: 6px;
    padding: 8px 10px;
  }

  .m-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
  }

  .m-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
}
</style>
