<template>
  <MainLayout>
    <div class="dashboard-wrapper container py-4 animate-fade-in">
      <!-- Welcome Header -->
      <div class="row align-items-center mb-4">
        <div class="col-md-8">
          <h2 class="welcome-title text-dark fw-bold mb-1">
            Selamat Datang Kembali, <span class="gradient-text">{{ auth.user?.nama }}</span>!
          </h2>
          <p class="text-secondary mb-0">
            Terima kasih telah berkontribusi menjaga kebersihan lingkungan bersama BankSamsul.
          </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <button class="btn-penarikan-gradient" data-bs-toggle="modal" data-bs-target="#penarikanModal">
            <i class="bi bi-wallet2 me-2"></i> Ajukan Penarikan
          </button>
        </div>
      </div>

      <!-- Quick Stats Card Row -->
      <div class="row g-4 mb-4">
        <!-- Main Saldo Card -->
        <div class="col-lg-4">
          <div class="stat-card balance-card">
            <div class="card-glow"></div>
            <div class="card-content">
              <span class="stat-label">Total Saldo Tabungan</span>
              <h1 class="balance-value">Rp {{ saldoDisplay }}</h1>
              <div class="card-decor">
                <i class="bi bi-piggy-bank"></i>
              </div>
              <div class="user-role-badge">
                <i class="bi bi-shield-check me-1"></i> Nasabah Aktif
              </div>
            </div>
          </div>
        </div>

        <!-- Total Setoran Card -->
        <div class="col-lg-3 col-sm-6">
          <div class="stat-card sub-card setoran-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="icon-box setoran-icon">
                <i class="bi bi-arrow-down-left-circle-fill"></i>
              </div>
              <span class="badge bg-success-soft">Masuk</span>
            </div>
            <span class="stat-label text-muted">Akumulasi Setoran</span>
            <h3 class="stat-value text-success mt-1">Rp {{ totalSetoran.toLocaleString('id-ID') }}</h3>
            <p class="mb-0 text-muted small mt-2">Dari total {{ countSetoran }} kali transaksi</p>
          </div>
        </div>

        <!-- Total Penarikan Card -->
        <div class="col-lg-3 col-sm-6">
          <div class="stat-card sub-card penarikan-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="icon-box penarikan-icon">
                <i class="bi bi-arrow-up-right-circle-fill"></i>
              </div>
              <span class="badge bg-danger-soft">Keluar</span>
            </div>
            <span class="stat-label text-muted">Akumulasi Penarikan</span>
            <h3 class="stat-value text-danger mt-1">Rp {{ totalPenarikan.toLocaleString('id-ID') }}</h3>
            <p class="mb-0 text-muted small mt-2">Dari total {{ countPenarikan }} kali transaksi</p>
          </div>
        </div>

        <!-- Poin Card -->
        <div class="col-lg-2 col-sm-6">
          <div class="stat-card sub-card poin-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="icon-box poin-icon">
                <i class="bi bi-star-fill"></i>
              </div>
              <span class="badge bg-warning-soft">Poin</span>
            </div>
            <span class="stat-label text-muted">Total Poin</span>
            <h3 class="stat-value mt-1" style="color:#d97706;">{{ auth.user?.poin ?? 0 }}</h3>
            <p class="mb-0 text-muted small mt-2">Didapat dari setoran sampah</p>
          </div>
        </div>
      </div>

      <!-- Riwayat Transaksi -->
      <div class="card shadow-sm border-0 main-table-card">
        <div class="card-header-gradient d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon">
              <i class="bi bi-clock-history"></i>
            </div>
            <h5 class="mb-0 fw-bold text-white">Riwayat Transaksi Saya</h5>
          </div>
          <span class="badge bg-white-soft text-white">{{ transaksi.length }} Transaksi</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center custom-table">
              <thead>
                <tr>
                  <th width="10%">#</th>
                  <th width="30%">Jenis Transaksi</th>
                  <th width="30%">Jumlah Nominal</th>
                  <th width="30%">Waktu Transaksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="4" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Sedang memproses riwayat transaksi...</p>
                  </td>
                </tr>
                <tr v-else-if="!transaksi.length">
                  <td colspan="4" class="text-center py-5 text-muted">
                    <div class="empty-state">
                      <i class="bi bi-inbox-fill text-muted display-4"></i>
                      <p class="mt-2 fw-medium mb-0">Belum ada aktivitas transaksi</p>
                    </div>
                  </td>
                </tr>
                <tr v-for="(t, i) in transaksi" :key="i" class="table-row-animate">
                  <td class="fw-medium text-secondary">{{ i + 1 }}</td>
                  <td>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill badge-jenis"
                         :class="t.jenis === 'Setoran' ? 'badge-success-glow' : 'badge-danger-glow'">
                      <i :class="t.jenis === 'Setoran' ? 'bi bi-plus-circle-fill' : 'bi bi-dash-circle-fill'"></i>
                      <span>{{ t.jenis }}</span>
                    </div>
                  </td>
                  <td class="fw-bold fs-6" :class="t.jenis === 'Setoran' ? 'text-success' : 'text-danger'">
                    {{ t.jenis === 'Setoran' ? '+' : '-' }} Rp {{ Number(t.jumlah).toLocaleString('id-ID') }}
                  </td>
                  <td class="text-secondary small">{{ formatDate(t.tanggal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Riwayat Survey Lingkungan -->
      <div class="card shadow-sm border-0 main-table-card mt-4 mb-4">
        <div class="card-header-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e, #0f172a);">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon text-teal">
              <i class="bi bi-clipboard2-check"></i>
            </div>
            <h5 class="mb-0 fw-bold text-white">Hasil Penilaian Survey Lingkungan</h5>
          </div>
          <span class="badge bg-white-soft text-white">{{ riwayatSurvey.length }} Data</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center custom-table">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="15%">Foto</th>
                  <th width="20%">Kategori</th>
                  <th width="20%">Status Penilaian</th>
                  <th width="25%">Keterangan & Petugas</th>
                  <th width="15%">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Sedang memuat riwayat survey...</p>
                  </td>
                </tr>
                <tr v-else-if="!riwayatSurvey.length">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <div class="empty-state">
                      <i class="bi bi-clipboard-x text-muted display-4"></i>
                      <p class="mt-2 fw-medium mb-0">Belum ada penilaian survey lingkungan untuk kelas Anda</p>
                    </div>
                  </td>
                </tr>
                <tr v-for="(s, i) in riwayatSurvey" :key="s.id" class="table-row-animate">
                  <td class="fw-medium text-secondary">{{ i + 1 }}</td>
                  <td>
                    <img :src="`/storage/${s.foto}`" alt="Foto" class="survey-thumbnail" @error="imageError" />
                  </td>
                  <td class="fw-bold text-dark">{{ formatKategori(s.kategori) }}</td>
                  <td>
                    <span class="badge px-3 py-2 rounded-pill badge-jenis"
                          :class="isStatusPositive(s.status) ? 'badge-success-glow' : 'badge-danger-glow'">
                      <i :class="isStatusPositive(s.status) ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                      {{ formatStatus(s.status) }}
                    </span>
                  </td>
                  <td class="text-secondary small text-start">
                    <div>{{ s.keterangan || '-' }}</div>
                    <div class="mt-1 opacity-75"><i class="bi bi-person-badge"></i> {{ s.petugas?.nama || 'Petugas' }}</div>
                  </td>
                  <td class="text-secondary small">{{ formatDate(s.tanggal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Riwayat Penilaian Poin -->
      <div class="card shadow-sm border-0 main-table-card mt-4 mb-4">
        <div class="card-header-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #b45309, #1c1917);">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon"><i class="bi bi-star-fill" style="color:#fbbf24;"></i></div>
            <h5 class="mb-0 fw-bold text-white">Riwayat Penilaian Poin</h5>
          </div>
          <span class="badge bg-white-soft text-white">{{ riwayatPenilaian.length }} Penilaian</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center custom-table">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="25%">Kategori</th>
                  <th width="15%">Rating</th>
                  <th width="15%">Poin</th>
                  <th width="25%">Catatan & Penilai</th>
                  <th width="15%">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="6" class="text-center py-5">
                    <div class="spinner-border" style="color:#f59e0b;" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat riwayat penilaian...</p>
                  </td>
                </tr>
                <tr v-else-if="!riwayatPenilaian.length">
                  <td colspan="6" class="text-center py-5 text-muted">
                    <div class="empty-state">
                      <i class="bi bi-star text-muted display-4"></i>
                      <p class="mt-2 fw-medium mb-0">Belum ada penilaian poin untuk kelas Anda</p>
                    </div>
                  </td>
                </tr>
                <tr v-for="(p, i) in riwayatPenilaian" :key="p.id" class="table-row-animate">
                  <td class="fw-medium text-secondary">{{ i + 1 }}</td>
                  <td>
                    <span class="badge px-3 py-2 rounded-pill fw-semibold"
                      :style="p.kategori === 'kebersihan_kelas'
                        ? 'background:rgba(99,102,241,0.12);color:#4338ca'
                        : 'background:rgba(16,185,129,0.12);color:#047857'">
                      <i :class="p.kategori === 'kebersihan_kelas' ? 'bi bi-stars' : 'bi bi-recycle'" class="me-1"></i>
                      {{ p.kategori_label }}
                    </span>
                  </td>
                  <td>
                    <!-- Rating stars visual -->
                    <div class="d-flex align-items-center justify-content-center gap-1">
                      <span class="fw-bold fs-5" style="color:#f59e0b;">{{ p.rating }}</span>
                      <span class="text-muted small">/ {{ p.kategori === 'kebersihan_kelas' ? 10 : 5 }}</span>
                    </div>
                    <div class="small text-muted">{{ p.rating_label }}</div>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-3 py-2 fw-bold" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;">
                      +{{ p.rating }} poin
                    </span>
                  </td>
                  <td class="text-secondary small text-start">
                    <div>{{ p.catatan || '-' }}</div>
                    <div class="mt-1 opacity-75"><i class="bi bi-person-badge"></i> {{ p.penilai || 'Penilai' }}</div>
                  </td>
                  <td class="text-secondary small">{{ formatDate(p.tanggal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal Penarikan -->
      <div class="modal fade" id="penarikanModal" tabindex="-1" aria-labelledby="penarikanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg modal-premium">
            <div class="modal-header bg-gradient-danger text-white border-0 py-3">
              <h5 class="modal-title fw-bold" id="penarikanModalLabel">
                <i class="bi bi-bank me-2"></i> Ajukan Penarikan Saldo
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
              <div class="alert alert-warning-soft mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                Setiap pengajuan penarikan memerlukan persetujuan dari admin/petugas terlebih dahulu.
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Nominal Penarikan (Rp)</label>
                <div class="input-group-premium">
                  <span class="currency-prefix">Rp</span>
                  <input v-model="jumlah" type="number" min="1000" class="form-control-premium" placeholder="Minimal 1.000" />
                </div>
                <div class="form-text text-muted mt-2 d-flex justify-content-between">
                  <span>Batas minimal: Rp 1.000</span>
                  <span>Saldo tersedia: <strong>Rp {{ saldoDisplay }}</strong></span>
                </div>
              </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light-soft">
              <button class="btn btn-light-premium" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-success-premium" @click="ajukanPenarikan">
                <i class="bi bi-check2-all me-1"></i> Ajukan Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const auth = useAuthStore()
const transaksi = ref([])
const riwayatSurvey = ref([])
const riwayatPenilaian = ref([])
const saldoDisplay = ref('0')
const loading = ref(true)
const jumlah = ref('')

const formatKategori = (kat) => {
  if (kat === 'pemilahan_sampah') return 'Pemilahan Sampah'
  if (kat === 'kebersihan_kelas') return 'Kebersihan Kelas'
  if (kat === 'perawatan_tanaman') return 'Perawatan Tanaman'
  return kat
}

const formatStatus = (status) => {
  if (!status) return '-'
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const isStatusPositive = (status) => {
  return ['sesuai', 'bersih', 'disiram'].includes(status)
}

const imageError = (e) => {
  e.target.src = 'https://via.placeholder.com/80?text=No+Image'
}

// === Computed Properties for Stats ===
const totalSetoran = computed(() => {
  return transaksi.value
    .filter(t => t.jenis === 'Setoran')
    .reduce((sum, curr) => sum + Number(curr.jumlah), 0)
})

const countSetoran = computed(() => {
  return transaksi.value.filter(t => t.jenis === 'Setoran').length
})

const totalPenarikan = computed(() => {
  return transaksi.value
    .filter(t => t.jenis === 'Penarikan')
    .reduce((sum, curr) => sum + Number(curr.jumlah), 0)
})

const countPenarikan = computed(() => {
  return transaksi.value.filter(t => t.jenis === 'Penarikan').length
})

// Format tanggal
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// === Load Data Transaksi Nasabah ===
const loadData = async () => {
  try {
    const res = await axios.get(`/api/transaksi/${auth.user.id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    transaksi.value = res.data
    // ambil riwayat survey
    const surveyRes = await axios.get('/api/survey-lingkungan/saya', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    riwayatSurvey.value = surveyRes.data
    // ambil riwayat penilaian poin
    const penilaianRes = await axios.get('http://127.0.0.1:8000/api/penilaian-poin/saya', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    riwayatPenilaian.value = penilaianRes.data
    // ambil saldo terbaru
    const userRes = await axios.get(`/api/me`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    const currentUser = userRes.data.user
    auth.user = currentUser
    localStorage.setItem('user', JSON.stringify(currentUser))
    saldoDisplay.value = Number(currentUser.saldo).toLocaleString('id-ID')
  } catch (err) {
    Swal.fire('Gagal!', 'Tidak dapat memuat riwayat transaksi.', 'error')
  } finally {
    loading.value = false
  }
}

// === Ajukan Penarikan ===
const ajukanPenarikan = async () => {
  if (!jumlah.value || jumlah.value < 1000) {
    Swal.fire('Peringatan', 'Minimal penarikan adalah Rp 1.000', 'warning')
    return
  }

  try {
    await axios.post(
      '/api/transaksi/penarikan',
      { user_id: auth.user.id, jumlah: jumlah.value },
      { headers: { Authorization: `Bearer ${auth.token}` } }
    )
    Swal.fire('Berhasil!', 'Permintaan penarikan telah dikirim dan menunggu validasi admin.', 'success')
    jumlah.value = ''
    // Tutup modal manual (karena SweetAlert menginterupsi Bootstrap event)
    const modalEl = document.getElementById('penarikanModal')
    const modal = window.bootstrap.Modal.getInstance(modalEl)
    modal.hide()
    await loadData()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal mengajukan penarikan.', 'error')
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
/* Gradient Text & Title styles */
.gradient-text {
  background: linear-gradient(135deg, hsl(160, 84%, 39%), hsl(142, 71%, 45%));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 800;
}

.welcome-title {
  letter-spacing: -0.5px;
}

/* Animations */
.animate-fade-in {
  animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Premium Gradient Button */
.btn-penarikan-gradient {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  font-weight: 600;
  padding: 0.85rem 1.6rem;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
}

.btn-penarikan-gradient:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-penarikan-gradient:active {
  transform: translateY(0);
}

/* Stat Cards */
.stat-card {
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  height: 100%;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
}

/* Balance Card (Main Hero) */
.balance-card {
  background: linear-gradient(135deg, #065f46, #047857);
  color: white;
  border: none;
}

.balance-card .stat-label {
  color: rgba(255, 255, 255, 0.75);
  font-weight: 500;
  font-size: 0.95rem;
  letter-spacing: 0.5px;
}

.balance-value {
  font-size: 2.3rem;
  font-weight: 800;
  margin-top: 0.5rem;
  margin-bottom: 1.5rem;
  letter-spacing: -1px;
}

.card-glow {
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
  transform: rotate(30deg);
  pointer-events: none;
}

.card-decor {
  position: absolute;
  right: 1.5rem;
  bottom: 0.5rem;
  font-size: 5rem;
  color: rgba(255, 255, 255, 0.1);
  pointer-events: none;
}

.user-role-badge {
  display: inline-flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 0.35rem 0.8rem;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

/* Sub cards */
.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.setoran-icon {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.penarikan-icon {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.poin-icon {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.bg-warning-soft {
  background: rgba(245, 158, 11, 0.15);
  color: #92400e;
  font-weight: 600;
}

.bg-success-soft {
  background: rgba(16, 185, 129, 0.15);
  color: #065f46;
  font-weight: 600;
}

.bg-danger-soft {
  background: rgba(239, 68, 68, 0.15);
  color: #991b1b;
  font-weight: 600;
}

.stat-value {
  font-weight: 800;
  letter-spacing: -0.5px;
}

/* Custom Table Card */
.main-table-card {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}

.card-header-gradient {
  background: linear-gradient(135deg, #1f2937, #111827);
  padding: 1.25rem 1.75rem;
  border: none;
}

.header-icon {
  width: 34px;
  height: 34px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
}

.bg-white-soft {
  background: rgba(255, 255, 255, 0.15);
  font-weight: 600;
}

.custom-table th {
  background: #f9fafb;
  color: #4b5563;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.custom-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.table-row-animate {
  transition: background-color 0.2s ease;
}

.table-row-animate:hover {
  background-color: #f9fafb;
}

/* Badges for table */
.badge-jenis {
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.4rem 1rem;
}

.badge-success-glow {
  background: rgba(16, 185, 129, 0.1);
  color: #047857;
}

.badge-danger-glow {
  background: rgba(239, 68, 68, 0.1);
  color: #b91c1c;
}

/* Premium Modal */
.modal-premium {
  border-radius: 20px;
  overflow: hidden;
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.alert-warning-soft {
  background-color: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.2);
  color: #92400e;
  border-radius: 12px;
  padding: 1rem;
  font-size: 0.9rem;
}

/* Custom premium input */
.input-group-premium {
  position: relative;
  display: flex;
  align-items: center;
  background: #f3f4f6;
  border-radius: 12px;
  padding: 0.25rem;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.input-group-premium:focus-within {
  border-color: #ef4444;
  background: white;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
}

.currency-prefix {
  font-weight: 700;
  color: #4b5563;
  padding-left: 1rem;
  padding-right: 0.5rem;
}

.form-control-premium {
  border: none;
  background: transparent;
  font-size: 1.2rem;
  font-weight: 700;
  color: #111827;
  padding: 0.6rem 0.5rem;
  width: 100%;
}

.form-control-premium:focus {
  outline: none;
}

.bg-light-soft {
  background-color: #f9fafb;
}

.btn-light-premium {
  background: white;
  border: 1px solid #d1d5db;
  color: #4b5563;
  font-weight: 600;
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.btn-light-premium:hover {
  background: #f3f4f6;
}

.btn-success-premium {
  background: #10b981;
  color: white;
  border: none;
  font-weight: 600;
  padding: 0.6rem 1.5rem;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.btn-success-premium:hover {
  background: #059669;
}

.survey-thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}
.text-teal {
  color: #0f766e;
}
</style>
