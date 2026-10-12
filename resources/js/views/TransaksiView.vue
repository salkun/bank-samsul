<template>
  <MainLayout>
    <div class="container mt-4">
      <h3>Transaksi Bank Sampah</h3>

      <!-- FORM SETORAN -->
      <div class="card mt-3 p-3 shadow-sm">
        <h5 class="mb-3 text-success">
          <i class="bi bi-arrow-up-circle me-1"></i> Form Setoran
        </h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Nasabah</label>
            <select v-model="form.user_id" class="form-select" @change="loadRiwayat">
              <option value="">-- Pilih Nasabah --</option>
              <option v-for="n in nasabah" :key="n.id" :value="n.id">
                {{ n.nama }} (Saldo: Rp {{ n.saldo.toLocaleString() }})
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Jenis Sampah</label>
            <select v-model="form.kategori_id" class="form-select">
              <option value="">-- Pilih Jenis --</option>
              <option v-for="k in kategori" :key="k.id" :value="k.id">
                {{ k.nama }} (Rp {{ k.harga_per_kg.toLocaleString() }}/kg)
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Berat (kg)</label>
            <input v-model="form.berat" type="number" class="form-control" min="0.1" step="0.1" />
          </div>

          <div class="col-md-2">
            <label class="form-label">Tanggal Setor</label>
            <input v-model="form.tanggal" type="date" class="form-control" />
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-success w-100" @click="setor">
              <i class="bi bi-check-circle me-1"></i> Setor
            </button>
          </div>
        </div>
      </div>

      <!-- FORM PENARIKAN -->
      <div class="card mt-4 p-3 shadow-sm">
        <h5 class="mb-3 text-danger">
          <i class="bi bi-arrow-down-circle me-1"></i> Form Penarikan
        </h5>
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Nasabah</label>
            <select v-model="penarikan.user_id" class="form-select" @change="loadRiwayat">
              <option value="">-- Pilih Nasabah --</option>
              <option v-for="n in nasabah" :key="n.id" :value="n.id">
                {{ n.nama }} (Saldo: Rp {{ n.saldo.toLocaleString() }})
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Nominal (Rp)</label>
            <input v-model="penarikan.jumlah" type="number" class="form-control" min="1000" step="500" />
          </div>

          <div class="col-md-3">
            <label class="form-label">Tanggal Penarikan</label>
            <input v-model="penarikan.tanggal" type="date" class="form-control" />
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-danger w-100" @click="tarik">
              <i class="bi bi-cash me-1"></i> Tarik
            </button>
          </div>
        </div>
      </div>

      <hr class="my-4" />

      <!-- RIWAYAT TRANSAKSI -->
      <h5><i class="bi bi-clock-history me-1"></i> Riwayat Transaksi Nasabah</h5>
      <table class="table table-striped mt-2 align-middle text-center">
        <thead class="table-success">
          <tr>
            <th>No</th>
            <th>Jenis</th>
            <th>Jumlah (Rp)</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!transaksi.length">
            <td colspan="4" class="text-center text-muted py-3">Belum ada transaksi</td>
          </tr>
          <tr v-for="(t, i) in transaksi" :key="i">
            <td>{{ i + 1 }}</td>
            <td>
              <span :class="t.jenis === 'Setoran' ? 'text-success' : 'text-danger'">
                {{ t.jenis === 'Setoran' ? 'Setoran' : 'Penarikan' }}
              </span>
            </td>
            <td>Rp {{ Number(t.jumlah).toLocaleString() }}</td>
            <td>{{ formatDate(t.tanggal) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'
import Swal from 'sweetalert2'

const auth = useAuthStore()
const nasabah = ref([])
const kategori = ref([])
const transaksi = ref([])

const form = ref({
  user_id: '',
  kategori_id: '',
  berat: '',
  tanggal: new Date().toISOString().substr(0, 10)
})

const penarikan = ref({
  user_id: '',
  jumlah: '',
  tanggal: new Date().toISOString().substr(0, 10)
})

// === LOAD NASABAH & KATEGORI ===
const loadData = async () => {
  const headers = { Authorization: `Bearer ${auth.token}` }
  const [n, k] = await Promise.all([
    axios.get('/api/nasabah', { headers }),
    axios.get('/api/kategori', { headers })
  ])
  nasabah.value = n.data
  kategori.value = k.data
}

// === LOAD RIWAYAT ===
const loadRiwayat = async () => {
  const userId = form.value.user_id || penarikan.value.user_id
  if (!userId) return
  try {
    const res = await axios.get(`/api/transaksi/${userId}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    transaksi.value = res.data
  } catch {
    transaksi.value = []
  }
}

// === SETORAN ===
const setor = async () => {
  if (!form.value.user_id || !form.value.kategori_id || !form.value.berat) {
    Swal.fire({
      icon: 'warning',
      title: 'Lengkapi Data!',
      text: 'Semua field setoran harus diisi.',
      confirmButtonColor: '#16a34a',
    })
    return
  }

  try {
    await axios.post('/api/transaksi/setoran', form.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    await Swal.fire({
      icon: 'success',
      title: 'Setoran Berhasil!',
      text: 'Data setoran berhasil dicatat.',
      timer: 1800,
      showConfirmButton: false
    })

    await loadData()
    await loadRiwayat()

    form.value = { user_id: '', kategori_id: '', berat: '', tanggal: new Date().toISOString().substr(0, 10) }

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat mencatat setoran.',
      confirmButtonColor: '#dc2626'
    })
  }
}

// === PENARIKAN ===
const tarik = async () => {
  if (!penarikan.value.user_id || !penarikan.value.jumlah) {
    Swal.fire({
      icon: 'warning',
      title: 'Lengkapi Data!',
      text: 'Nasabah dan jumlah penarikan wajib diisi.',
      confirmButtonColor: '#16a34a'
    })
    return
  }

  try {
    const result = await Swal.fire({
      title: 'Konfirmasi Penarikan',
      text: 'Yakin ingin mencatat penarikan ini?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Lanjutkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#16a34a',
      cancelButtonColor: '#6b7280'
    })

    if (!result.isConfirmed) return

    await axios.post('/api/transaksi/penarikan', penarikan.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    await Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Penarikan berhasil diajukan dan menunggu validasi admin.',
      timer: 2000,
      showConfirmButton: false
    })

    await loadData()
    await loadRiwayat()
    penarikan.value = { user_id: '', jumlah: '', tanggal: new Date().toISOString().substr(0, 10) }

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat mencatat penarikan.',
      confirmButtonColor: '#dc2626'
    })
  }
}

// Format tanggal ke format lokal
const formatDate = (date) => {
  const d = new Date(date)
  return d.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(loadData)
</script>

<style scoped>
h3, h5 {
  font-weight: 600;
}
.table th {
  background-color: #198754 !important;
  color: white;
}
.table td {
  vertical-align: middle;
}
</style>
