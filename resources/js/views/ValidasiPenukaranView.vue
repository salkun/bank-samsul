<template>
  <MainLayout>
    <div class="container py-4">
      <!-- Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
          <h3 class="fw-bold text-success mb-1">
            <i class="bi bi-bag-check me-2"></i> Validasi Penukaran Barang
          </h3>
          <p class="text-muted mb-0">Kelola dan proses pengajuan penukaran poin nasabah.</p>
        </div>
        <div class="d-flex gap-2">
          <button
            v-if="auth.user?.role === 'admin' && pendingCount > 0"
            class="btn btn-outline-success btn-sm"
            @click="tandaiSemuaDibaca"
          >
            <i class="bi bi-check2-all me-1"></i> Tandai Dibaca
          </button>
          <button class="btn btn-outline-success btn-sm" @click="loadPenukaran">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Quick Summary Status Pills -->
      <div class="row g-2 mb-4">
        <div class="col-6 col-md-2" v-for="st in statusPills" :key="st.key">
          <div
            class="card border-0 shadow-sm p-3 text-center cursor-pointer stat-tab"
            :class="{ 'active-tab': activeFilter === st.key }"
            @click="activeFilter = st.key"
          >
            <span class="small text-muted">{{ st.label }}</span>
            <h5 class="fw-bold mb-0 mt-1" :class="st.colorClass">{{ st.count }}</h5>
          </div>
        </div>
      </div>

      <!-- Search Input -->
      <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0 text-muted">
            <i class="bi bi-search"></i>
          </span>
          <input
            v-model="searchTerm"
            type="text"
            class="form-control border-start-0"
            placeholder="Cari berdasarkan nama nasabah atau nama barang..."
          />
          <button v-if="searchTerm" class="btn btn-outline-secondary" @click="searchTerm = ''">
            Clear
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5 text-muted">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-2">Memuat daftar penukaran...</p>
      </div>

      <!-- Data Table -->
      <div v-else class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-success">
              <tr>
                <th style="width: 40px;">No</th>
                <th class="text-start">Nasabah</th>
                <th class="text-start">Barang</th>
                <th>Poin Dipakai</th>
                <th>Tgl Rencana Ambil</th>
                <th>Tgl Pengajuan</th>
                <th>Status</th>
                <th style="width: 170px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!filteredPenukaran.length">
                <td colspan="8" class="text-muted py-4">Tidak ada data penukaran.</td>
              </tr>
              <tr v-for="(p, i) in filteredPenukaran" :key="p.id">
                <td>{{ i + 1 }}</td>
                <td class="text-start">
                  <div class="fw-semibold text-dark">{{ p.user?.nama || '-' }}</div>
                  <small class="text-muted">@{{ p.user?.username || '-' }}</small>
                </td>
                <td class="text-start">
                  <div class="d-flex align-items-center gap-2">
                    <img
                      v-if="p.barang?.foto"
                      :src="fotoUrl(p.barang.foto)"
                      class="rounded object-fit-cover border"
                      style="width: 42px; height: 42px;"
                      alt="Barang"
                      @error="onImgError"
                    />
                    <div
                      v-else
                      class="rounded bg-light text-muted d-flex align-items-center justify-content-center border"
                      style="width: 42px; height: 42px;"
                    >
                      <i class="bi bi-gift fs-5 text-secondary"></i>
                    </div>
                    <div>
                      <div class="fw-semibold text-dark">{{ p.barang?.nama || 'Barang Dihapus' }}</div>
                      <small class="text-muted">{{ p.jumlah }} item</small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold fs-6">
                    <i class="bi bi-recycle me-1"></i>{{ Number(p.poin_dipakai).toLocaleString('id-ID') }} Poin
                  </span>
                  <small class="text-muted d-block mt-1">Poin Sampah</small>
                </td>
                <td>
                  <div class="fw-semibold">{{ formatDate(p.tanggal_ambil) }}</div>
                </td>
                <td>
                  <small class="text-muted">{{ formatDateTime(p.created_at) }}</small>
                </td>
                <td>
                  <span
                    class="badge px-3 py-2 text-uppercase"
                    :class="{
                      'bg-warning text-dark': p.status === 'menunggu',
                      'bg-primary': p.status === 'disetujui',
                      'bg-success': p.status === 'diambil',
                      'bg-danger': p.status === 'ditolak',
                      'bg-secondary': p.status === 'dibatalkan',
                    }"
                  >
                    {{ p.status }}
                  </span>
                  <div v-if="p.catatan_admin" class="text-danger small mt-1 fst-italic" style="max-width: 150px;">
                    "{{ p.catatan_admin }}"
                  </div>
                </td>
                <td>
                  <!-- Aksi saat status MENUNGGU -->
                  <div v-if="p.status === 'menunggu'">
                    <template v-if="auth.user?.role === 'admin'">
                      <button
                        class="btn btn-sm btn-success me-1"
                        @click="prosesPenukaran(p.id, 'disetujui')"
                        title="Setujui Penukaran"
                      >
                        <i class="bi bi-check-lg"></i> Setujui
                      </button>
                      <button
                        class="btn btn-sm btn-outline-danger"
                        @click="prosesPenukaran(p.id, 'ditolak')"
                        title="Tolak Penukaran"
                      >
                        <i class="bi bi-x-lg"></i> Tolak
                      </button>
                    </template>
                    <template v-else>
                      <span class="badge bg-light text-muted border">Menunggu Admin</span>
                    </template>
                  </div>

                  <!-- Aksi saat status DISETUJUI -->
                  <div v-else-if="p.status === 'disetujui'">
                    <button
                      class="btn btn-sm btn-primary"
                      @click="tandaiDiambil(p.id)"
                      title="Tandai Barang Sudah Diambil"
                    >
                      <i class="bi bi-box-seam me-1"></i> Tandai Diambil
                    </button>
                  </div>

                  <!-- Status Final Lainnya -->
                  <div v-else>
                    <span class="text-muted small">
                      <i class="bi bi-check2-circle me-1" v-if="p.status === 'diambil'"></i>
                      Selesai
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const auth = useAuthStore()

const penukaran = ref([])
const loading = ref(true)
const activeFilter = ref('semua')
const searchTerm = ref('')
const pendingCount = ref(0)

const statusPills = computed(() => {
  const all = penukaran.value
  return [
    { key: 'semua', label: 'Semua', count: all.length, colorClass: 'text-dark' },
    { key: 'menunggu', label: 'Menunggu', count: all.filter(p => p.status === 'menunggu').length, colorClass: 'text-warning' },
    { key: 'disetujui', label: 'Disetujui', count: all.filter(p => p.status === 'disetujui').length, colorClass: 'text-primary' },
    { key: 'diambil', label: 'Diambil', count: all.filter(p => p.status === 'diambil').length, colorClass: 'text-success' },
    { key: 'ditolak', label: 'Ditolak', count: all.filter(p => p.status === 'ditolak').length, colorClass: 'text-danger' },
    { key: 'dibatalkan', label: 'Dibatalkan', count: all.filter(p => p.status === 'dibatalkan').length, colorClass: 'text-secondary' },
  ]
})

const filteredPenukaran = computed(() => {
  return penukaran.value.filter(p => {
    const matchFilter = activeFilter.value === 'semua' || p.status === activeFilter.value
    const term = searchTerm.value.toLowerCase()
    const matchSearch = !term ||
      (p.user?.nama && p.user.nama.toLowerCase().includes(term)) ||
      (p.user?.username && p.user.username.toLowerCase().includes(term)) ||
      (p.barang?.nama && p.barang.nama.toLowerCase().includes(term))
    return matchFilter && matchSearch
  })
})

const fotoUrl = (foto) => {
  if (!foto) return ''
  if (foto.startsWith('http://') || foto.startsWith('https://')) return foto
  if (foto.startsWith('/storage/')) return foto
  if (foto.startsWith('storage/')) return `/${foto}`
  return `/storage/${foto}`
}

const onImgError = (e) => {
  e.target.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="80" height="80"%3E%3Crect width="80" height="80" fill="%23f1f5f9"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-family="sans-serif" font-size="11" fill="%2394a3b8"%3ENo Image%3C/text%3E%3C/svg%3E'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// === LOAD DATA ===
const loadPenukaran = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/penukaran', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    penukaran.value = res.data.data || res.data || []
    pendingCount.value = penukaran.value.filter(p => p.status === 'menunggu').length

    // Tandai otomatis notifikasi dibaca jika admin membuka halaman ini
    if (auth.user?.role === 'admin') {
      await axios.post('/api/admin/notifikasi/baca', {}, {
        headers: { Authorization: `Bearer ${auth.token}` }
      }).catch(() => {})
    }
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal memuat data penukaran.', 'error')
  } finally {
    loading.value = false
  }
}

// === TANDAI SEMUA DIBACA ===
const tandaiSemuaDibaca = async () => {
  try {
    await axios.post('/api/admin/notifikasi/baca', {}, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    Swal.fire({
      icon: 'success',
      title: 'Semua notifikasi ditandai dibaca.',
      toast: true,
      position: 'top-end',
      timer: 1500,
      showConfirmButton: false
    })
  } catch (e) {}
}

// === PROSES PENUKARAN (SETUJUI / TOLAK) OLEH ADMIN ===
const prosesPenukaran = async (id, status) => {
  let catatanAdmin = ''

  if (status === 'ditolak') {
    const { value: text, isConfirmed } = await Swal.fire({
      title: 'Alasan Penolakan',
      input: 'textarea',
      inputLabel: 'Masukkan alasan penolakan untuk nasabah:',
      inputPlaceholder: 'Contoh: Stok barang habis atau sedang rusak...',
      showCancelButton: true,
      confirmButtonText: 'Tolak Penukaran',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc2626',
      inputValidator: (value) => {
        if (!value) return 'Alasan penolakan wajib diisi!'
      }
    })

    if (!isConfirmed) return
    catatanAdmin = text
  } else {
    const confirm = await Swal.fire({
      title: 'Setujui Penukaran?',
      text: 'Penukaran akan disetujui dan nasabah dapat mengambil barang sesuai jadwal.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Setujui',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#16a34a'
    })

    if (!confirm.isConfirmed) return
  }

  try {
    const res = await axios.post(
      `/api/penukaran/${id}/proses`,
      { status, catatan_admin: catatanAdmin },
      { headers: { Authorization: `Bearer ${auth.token}` } }
    )

    Swal.fire({
      icon: 'success',
      title: `Penukaran berhasil ${status}.`,
      text: res.data.message || '',
      timer: 1800,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    await loadPenukaran()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal memproses penukaran.', 'error')
  }
}

// === TANDAI DIAMBIL (ADMIN & PETUGAS) ===
const tandaiDiambil = async (id) => {
  const confirm = await Swal.fire({
    title: 'Konfirmasi Pengambilan Barang',
    text: 'Pastikan barang fisik sudah diserahkan kepada nasabah bersangkutan.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Sudah Diambil',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#2563eb'
  })

  if (!confirm.isConfirmed) return

  try {
    const res = await axios.post(
      `/api/penukaran/${id}/ambil`,
      {},
      { headers: { Authorization: `Bearer ${auth.token}` } }
    )

    Swal.fire({
      icon: 'success',
      title: 'Status diubah ke Diambil.',
      text: res.data.message || '',
      timer: 1800,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    await loadPenukaran()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal mengubah status pengambilan.', 'error')
  }
}

onMounted(() => {
  loadPenukaran()
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
.stat-tab {
  transition: all 0.2s ease;
  border: 2px solid transparent !important;
}
.stat-tab:hover {
  transform: translateY(-2px);
}
.active-tab {
  border-color: #16a34a !important;
  background-color: rgba(22, 163, 74, 0.05);
}
</style>
