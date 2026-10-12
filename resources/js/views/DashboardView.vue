<template>
  <MainLayout>
    <div class="container py-4">
      <h3 class="mb-4 fw-bold text-success">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
      </h3>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-2">Memuat dashboard...</p>
      </div>

      <div v-else>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- === STATISTICS CARDS === -->
        <div class="row g-3 mb-4">
          <div class="col-md-3" v-for="(card, i) in statCards" :key="i">
            <div class="card shadow-sm border-0 p-3 text-center h-100 stat-card">
              <div class="icon mb-2">
                <i :class="card.icon"></i>
              </div>
              <h6 class="text-muted mb-1">{{ card.title }}</h6>
              <h4 class="fw-bold text-success" v-html="card.value"></h4>
            </div>
          </div>
        </div>

        <!-- === CHART SECTION === -->
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="card shadow-sm p-3 border-0">
              <h6 class="fw-bold mb-3"><i class="bi bi-graph-up"></i> Tren Setoran per Bulan</h6>
              <div style="position:relative;min-height:200px;">
                <canvas id="lineChart" height="120"></canvas>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card shadow-sm p-3 border-0">
              <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart"></i> Komposisi Kategori Sampah</h6>
              <div style="position:relative;min-height:200px;">
                <canvas id="pieChart" height="150"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- === RECENT TRANSACTIONS === -->
        <div class="card mt-4 shadow-sm border-0">
          <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-clock-history me-2"></i> Transaksi Terbaru
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 text-center">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Nasabah</th>
                  <th>Jenis</th>
                  <th>Jumlah (Rp)</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(t, i) in transaksi" :key="i">
                  <td>{{ i + 1 }}</td>
                  <td>{{ t.nama || t.user?.nama || '-' }}</td>
                  <td>
                    <span :class="(t.jenis || '').toLowerCase() === 'setoran' ? 'text-success' : 'text-danger'">
                      {{ capitalize(t.jenis || '-') }}
                    </span>
                  </td>
                  <td>Rp {{ Number(t.jumlah || 0).toLocaleString() }}</td>
                  <td>{{ formatDate(t.tanggal) }}</td>
                </tr>
                <tr v-if="!transaksi.length">
                  <td colspan="5" class="text-muted py-3">Belum ada transaksi</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

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

const capitalize = (s) => {
  if (!s) return ''
  return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase()
}

const formatDate = (date) =>
  date ? new Date(date).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-'

const formatRupiah = (val) => {
  const n = Number(val || 0)
  return 'Rp ' + n.toLocaleString()
}

const statCards = computed(() => [
  { title: 'Total Nasabah', value: stats.value.total_nasabah ?? 0, icon: 'bi bi-people-fill text-success' },
  { title: 'Setoran Bulan Ini', value: formatRupiah(stats.value.total_setoran), icon: 'bi bi-cash-stack text-success' },
  { title: 'Penarikan Bulan Ini', value: formatRupiah(stats.value.total_penarikan), icon: 'bi bi-wallet2 text-danger' },
  { title: 'Jenis Populer', value: stats.value.jenis_populer ?? '-', icon: 'bi bi-recycle text-primary' }
])

const loadDashboard = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await axios.get('/api/laporan/dashboard', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    const d = res.data || {}

    // normalize / fallback
    stats.value = {
      total_nasabah: d.total_nasabah ?? 0,
      total_setoran: d.total_setoran ?? 0,
      total_penarikan: d.total_penarikan ?? 0,
      jenis_populer: d.jenis_populer ?? '-',
      bulan: Array.isArray(d.bulan) ? d.bulan : [],
      total_per_bulan: Array.isArray(d.total_per_bulan) ? d.total_per_bulan : [],
      kategori_labels: Array.isArray(d.kategori_labels) ? d.kategori_labels : [],
      kategori_values: Array.isArray(d.kategori_values) ? d.kategori_values : [],
      transaksi_terbaru: Array.isArray(d.transaksi_terbaru) ? d.transaksi_terbaru : []
    }

    // transaksi array: API field may vary; normalize each row to have nama, jenis, jumlah, tanggal
    transaksi.value = stats.value.transaksi_terbaru.map((t) => {
      // if t comes as object with user.nama, handle both
      return {
        nama: t.nama ?? (t.user?.nama) ?? (t.user_nama ?? '-'),
        jenis: t.jenis ?? t.type ?? '-',
        jumlah: t.jumlah ?? t.total ?? 0,
        tanggal: t.tanggal ?? t.created_at ?? null
      }
    })

    // render charts after DOM is surely ready
    await nextTickSafe()
    renderCharts()
  } catch (err) {
    console.error('Load dashboard error', err)
    error.value = err.response?.data?.message || 'Gagal memuat data dashboard. Periksa API atau token.'
  } finally {
    loading.value = false
  }
}

/** tiny helper to wait nextTick and small delay so canvas exists */
const nextTickSafe = () => new Promise((resolve) => setTimeout(resolve, 50))

const renderCharts = () => {
  // safe destructuring with fallbacks
  const labels = stats.value.bulan || []
  const totals = stats.value.total_per_bulan || []
  const kLabels = stats.value.kategori_labels || []
  const kValues = stats.value.kategori_values || []

  // ensure canvas exist
  const lineEl = document.getElementById('lineChart')
  const pieEl = document.getElementById('pieChart')
  if (!lineEl || !pieEl) {
    console.warn('Chart elements not found, skip rendering charts')
    return
  }

  // destroy existing instances
  try { if (lineChart) lineChart.destroy() } catch (e) {}
  try { if (pieChart) pieChart.destroy() } catch (e) {}

  // create line chart (if data present, otherwise provide empty)
  lineChart = new Chart(lineEl, {
    type: 'line',
    data: {
      labels: labels.length ? labels : [],
      datasets: [{
        label: 'Setoran (Rp)',
        data: totals.length ? totals : [],
        fill: true,
        borderColor: '#16a34a',
        backgroundColor: 'rgba(22, 163, 74, 0.15)',
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString() } } }
    }
  })

  // create pie chart
  pieChart = new Chart(pieEl, {
    type: 'pie',
    data: {
      labels: kLabels.length ? kLabels : [],
      datasets: [{
        data: kValues.length ? kValues : [],
        backgroundColor: ['#16a34a', '#22c55e', '#86efac', '#a3e635', '#4ade80']
      }]
    },
    options: { responsive: true }
  })
}

onMounted(loadDashboard)
</script>

<style scoped>
.stat-card {
  transition: 0.2s;
  border-left: 5px solid #16a34a;
  background: #f8fff9;
}
.stat-card:hover {
  transform: translateY(-3px);
  background: #ecfdf5;
}
.icon i { font-size: 1.8rem; }
</style>
