<template>
    <MainLayout>
  <div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
      <h3>Laporan Transaksi & Survey</h3>
      
      <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Quick Filters -->
        <div class="btn-group me-2 shadow-sm">
          <button type="button" class="btn btn-outline-success btn-sm" @click="setQuickFilter('hari_ini')">Hari Ini</button>
          <button type="button" class="btn btn-outline-success btn-sm" @click="setQuickFilter('minggu_ini')">Minggu Ini</button>
          <button type="button" class="btn btn-outline-success btn-sm" @click="setQuickFilter('bulan_ini')">Bulan Ini</button>
        </div>

        <!-- Custom Date Range -->
        <input type="date" v-model="startDate" class="form-control form-control-sm w-auto shadow-sm">
        <span class="text-muted fw-medium">s/d</span>
        <input type="date" v-model="endDate" class="form-control form-control-sm w-auto shadow-sm">
        
        <button class="btn btn-success btn-sm px-3 shadow-sm" @click="loadLaporan" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
          Tampilkan
        </button>
      </div>
    </div>

    <div v-if="laporan" class="mt-4">
      <div class="row g-3">
        <div class="col-6 col-md-3" v-for="(card, i) in cards" :key="i">
          <div class="card p-3 text-center shadow-sm h-100 border-0">
            <h6 class="text-muted small mb-1">{{ card.title }}</h6>
            <h4 class="fw-bold mb-0" :class="card.color">{{ card.value }}</h4>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-6">
          <div class="card p-3 shadow-sm">
            <h6 class="text-center">Berat Sampah per Jenis</h6>
            <canvas id="chartBerat"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3 shadow-sm">
            <h6 class="text-center">Transaksi per Nasabah</h6>
            <canvas id="chartTransaksi"></canvas>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md-6">
            <div class="alert alert-success shadow-sm">
            <strong>Setoran Terbanyak (Organik):</strong>
            <div v-if="laporan.top_organik">
                {{ laporan.top_organik.nama }} — <b>{{ laporan.top_organik.total_kg }} kg</b>
            </div>
            <div v-else>- Tidak ada data -</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info shadow-sm">
            <strong>Setoran Terbanyak (Anorganik):</strong>
            <div v-if="laporan.top_anorganik">
                {{ laporan.top_anorganik.nama }} — <b>{{ laporan.top_anorganik.total_kg }} kg</b>
            </div>
            <div v-else>- Tidak ada data -</div>
            </div>
        </div>
        </div>

        <div class="row mt-3">
        <div class="col-md-12">
            <div class="card p-3 shadow-sm">
            <h6 class="text-center">Setoran per Kategori (Organik vs Anorganik)</h6>
            <canvas id="chartKategori"></canvas>
            </div>
        </div>
        </div>
        <div class="row mt-4">
          <div class="col-md-12 mb-3">
            <h5 class="fw-bold text-center">Rekapitulasi Survey Lingkungan</h5>
          </div>
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 border-start border-4 border-success h-100">
              <h6 class="fw-bold"><i class="bi bi-recycle text-success me-2"></i>Pemilahan Sampah</h6>
              <div class="d-flex justify-content-between mt-3 px-2">
                <span class="text-success fw-medium"><i class="bi bi-check-circle me-1"></i> Sesuai: <b>{{ getSurveyStat('pemilahan_sampah', 'sesuai') }}</b></span>
                <span class="text-danger fw-medium"><i class="bi bi-x-circle me-1"></i> Tdk Sesuai: <b>{{ getSurveyStat('pemilahan_sampah', 'tidak_sesuai') }}</b></span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 border-start border-4 border-info h-100">
              <h6 class="fw-bold"><i class="bi bi-stars text-info me-2"></i>Kebersihan Kelas</h6>
              <div class="d-flex justify-content-between mt-3 px-2">
                <span class="text-success fw-medium"><i class="bi bi-check-circle me-1"></i> Bersih: <b>{{ getSurveyStat('kebersihan_kelas', 'bersih') }}</b></span>
                <span class="text-danger fw-medium"><i class="bi bi-x-circle me-1"></i> Tdk Bersih: <b>{{ getSurveyStat('kebersihan_kelas', 'tidak_bersih') }}</b></span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 border-start border-4 border-warning h-100">
              <h6 class="fw-bold"><i class="bi bi-flower1 text-warning me-2"></i>Perawatan Tanaman</h6>
              <div class="d-flex justify-content-between mt-3 px-2">
                <span class="text-success fw-medium"><i class="bi bi-check-circle me-1"></i> Disiram: <b>{{ getSurveyStat('perawatan_tanaman', 'disiram') }}</b></span>
                <span class="text-danger fw-medium"><i class="bi bi-x-circle me-1"></i> Tdk Disiram: <b>{{ getSurveyStat('perawatan_tanaman', 'tidak_disiram') }}</b></span>
              </div>
            </div>
          </div>
        </div>

      <div class="mt-4 text-end">
        <button class="btn btn-outline-primary me-2" @click="exportExcel">Export Excel</button>
        <button class="btn btn-outline-danger" @click="exportPDF">Export PDF</button>
      </div>
    </div>

    <div v-else-if="!loading" class="text-center mt-5 text-muted">
      <em>Belum ada data laporan yang ditampilkan</em>
    </div>
  </div>
  </MainLayout>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'
const auth = useAuthStore()
const today = new Date()
const yyyy = today.getFullYear()
const mm = String(today.getMonth() + 1).padStart(2, '0')
const dd = String(today.getDate()).padStart(2, '0')
const firstDay = `${yyyy}-${mm}-01`
const currentDay = `${yyyy}-${mm}-${dd}`

const startDate = ref(firstDay)
const endDate = ref(currentDay)
const laporan = ref(null)
const loading = ref(false)

const getSurveyStat = (kategori, status) => {
  if (!laporan.value || !laporan.value.survey_lingkungan) return 0
  const item = laporan.value.survey_lingkungan.find(s => s.kategori === kategori && s.status === status)
  return item ? item.total : 0
}

const cards = ref([])
let chart1 = null
let chart2 = null
let chart3 = null
// === Load laporan ===
const loadLaporan = async () => {
  if (startDate.value > endDate.value) {
    alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!')
    return
  }

  loading.value = true
  try {
    const url = `/api/laporan/bulan?start_date=${startDate.value}&end_date=${endDate.value}`
    const res = await axios.get(url, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    laporan.value = res.data

    cards.value = [
      { title: 'Poin Sampah Masuk', value: `${Number(laporan.value.poin_sampah_masuk ?? laporan.value.saldo_masuk ?? 0).toLocaleString()} Poin`, color: 'text-success' },
      { title: 'Poin Ditukar Barang', value: `${Number(laporan.value.poin_dipakai ?? laporan.value.saldo_keluar ?? 0).toLocaleString()} Poin`, color: 'text-danger' },
      { title: 'Total Penukaran Barang', value: `${Number(laporan.value.total_penukaran ?? 0)} Penukaran`, color: 'text-primary' },
      { title: 'Jumlah Nasabah Aktif', value: `${laporan.value.transaksi_per_nasabah?.length ?? 0} Orang`, color: 'text-info' }
    ]

    // Tunggu DOM selesai render sebelum menggambar chart
    await nextTick()
    renderCharts()
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.message || 'Gagal memuat laporan')
  } finally {
    loading.value = false
  }
}

const setQuickFilter = (type) => {
  const t = new Date()
  const format = (d) => {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  }
  
  if (type === 'hari_ini') {
    startDate.value = format(t)
    endDate.value = format(t)
  } else if (type === 'minggu_ini') {
    const lastWeek = new Date(t)
    lastWeek.setDate(t.getDate() - 6) // 7 days including today
    startDate.value = format(lastWeek)
    endDate.value = format(t)
  } else if (type === 'bulan_ini') {
    startDate.value = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-01`
    endDate.value = format(t)
  }
  
  loadLaporan()
}

// === Render Chart ===
const renderCharts = () => {
  const ctx1 = document.getElementById('chartBerat')
  const ctx2 = document.getElementById('chartTransaksi')
const ctx3 = document.getElementById('chartKategori')
  if (!ctx1 || !ctx2 || !laporan.value) return

  // Hapus chart lama (agar tidak duplikat)
  if (chart1) chart1.destroy()
  if (chart2) chart2.destroy()
  if (chart3) chart3.destroy()

  chart1 = new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: laporan.value.berat_per_jenis.map(j => j.jenis),
      datasets: [{
        label: 'Total Berat (kg)',
        data: laporan.value.berat_per_jenis.map(j => j.total_kg),
        backgroundColor: '#10b981'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      }
    }
  })

  chart2 = new Chart(ctx2, {
    type: 'pie',
    data: {
      labels: laporan.value.transaksi_per_nasabah.map(n => n.nasabah),
      datasets: [{
        label: 'Jumlah Transaksi',
        data: laporan.value.transaksi_per_nasabah.map(n => n.total_transaksi),
        backgroundColor: ['#34d399', '#60a5fa', '#fbbf24', '#f87171', '#a78bfa']
      }]
    }
  })
  // === Chart 3: Setoran per Kategori ===
  chart3 = new Chart(ctx3, {
    type: 'doughnut',
    data: {
      labels: laporan.value.setoran_per_kategori.map(c => c.kategori),
      datasets: [{
        label: 'Total Berat (kg)',
        data: laporan.value.setoran_per_kategori.map(c => c.total_kg),
        backgroundColor: ['#10b981', '#3b82f6']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  })
}

// === Export ===
const exportExcel = async () => {
  try {
    const url = `/api/laporan/export-excel?start_date=${startDate.value}&end_date=${endDate.value}`
    const response = await axios.get(url, {
      headers: { Authorization: `Bearer ${auth.token}` },
      responseType: 'blob'
    })
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Laporan_BankSampah_${new Date().getTime()}.xlsx`
    link.click()
  } catch (error) {
    alert("Gagal mengunduh file Excel.")
  }
}

const exportPDF = async () => {
  try {
    const url = `/api/laporan/export-pdf?start_date=${startDate.value}&end_date=${endDate.value}`
    const response = await axios.get(url, {
      headers: { Authorization: `Bearer ${auth.token}` },
      responseType: 'blob'
    })
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Laporan_BankSampah_${new Date().getTime()}.pdf`
    link.click()
  } catch (error) {
    alert("Gagal mengunduh file PDF.")
  }
}
</script>
