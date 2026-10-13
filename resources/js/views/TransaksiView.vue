<template>
  <MainLayout>
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-success mb-0">
          <i class="bi bi-recycle me-2"></i> Transaksi Setoran Sampah
        </h3>
      </div>
      <p class="text-muted">Pencatatan setoran sampah nasabah untuk menghasilkan Poin Sampah.</p>

      <!-- FORM SETORAN -->
      <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="mb-3 text-success fw-bold">
          <i class="bi bi-arrow-down-left-circle me-1"></i> Form Setoran Sampah
        </h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Pilih Nasabah <span class="text-danger">*</span></label>
            <select v-model="form.user_id" class="form-select" @change="onNasabahChange">
              <option value="">-- Pilih Nasabah --</option>
              <option v-for="n in nasabah" :key="n.id" :value="n.id">
                {{ n.nama }} (Poin: {{ (n.poin_sampah ?? 0).toLocaleString() }} Sampah | {{ (n.poin_lingkungan ?? 0).toLocaleString() }} Lingkungan)
              </option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Jenis Sampah <span class="text-danger">*</span></label>
            <select v-model="form.kategori_id" class="form-select">
              <option value="">-- Pilih Jenis --</option>
              <option v-for="k in kategori" :key="k.id" :value="k.id">
                {{ k.nama }} ({{ Number(k.poin_per_kg).toLocaleString() }} poin/{{ k.satuan }})
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold">Berat (kg) <span class="text-danger">*</span></label>
            <input
              v-model.number="form.berat"
              type="number"
              class="form-control"
              min="0.1"
              step="0.1"
              placeholder="0.0"
            />
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal Setor</label>
            <input v-model="form.tanggal" type="date" class="form-control" />
          </div>

          <!-- Preview Perhitungan Poin -->
          <div class="col-12" v-if="selectedKategori && form.berat > 0">
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0 py-2 px-3">
              <div>
                <i class="bi bi-calculator me-2"></i>
                <span>Perhitungan: {{ form.berat }} kg × {{ Number(selectedKategori.poin_per_kg).toLocaleString() }} poin/kg =</span>
              </div>
              <div class="fs-5 fw-bold text-success">
                +{{ estimasiPoin.toLocaleString('id-ID') }} Poin Sampah
              </div>
            </div>
          </div>

          <div class="col-12 text-end">
            <button class="btn btn-success px-4" @click="setor" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-check-circle me-1"></i> Catat Setoran
            </button>
          </div>
        </div>
      </div>

      <!-- DETAIL NASABAH TERPILIH & RIWAYAT -->
      <div v-if="selectedNasabahData" class="mb-4">
        <!-- Info Ringkas Nasabah -->
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
              <span class="text-muted small">Nasabah Terpilih</span>
              <h5 class="fw-bold text-dark mt-1 mb-0">{{ selectedNasabahData.nama }}</h5>
              <small class="text-muted">@{{ selectedNasabahData.username }}</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
              <span class="text-muted small">Poin Sampah</span>
              <h4 class="fw-bold text-success mt-1 mb-0">
                {{ Number(selectedNasabahData.poin_sampah ?? 0).toLocaleString('id-ID') }}
              </h4>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
              <span class="text-muted small">Poin Lingkungan</span>
              <h4 class="fw-bold text-warning-emphasis mt-1 mb-0">
                {{ Number(selectedNasabahData.poin_lingkungan ?? 0).toLocaleString('id-ID') }}
              </h4>
            </div>
          </div>
        </div>

        <!-- Tabel Riwayat Setoran Nasabah -->
        <div class="card border-0 shadow-sm overflow-hidden">
          <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history me-2"></i> Riwayat Setoran Nasabah: {{ selectedNasabahData.nama }}</span>
            <span class="badge bg-white text-success">{{ riwayatSetoran.length }} Setoran</span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
              <thead class="table-light">
                <tr>
                  <th style="width: 50px;">No</th>
                  <th>Tanggal</th>
                  <th class="text-start">Jenis Sampah</th>
                  <th>Berat</th>
                  <th>Poin Didapat</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!riwayatSetoran.length">
                  <td colspan="5" class="text-muted py-4">Belum ada catatan setoran untuk nasabah ini.</td>
                </tr>
                <tr v-for="(s, i) in riwayatSetoran" :key="s.id">
                  <td>{{ i + 1 }}</td>
                  <td>{{ formatDate(s.tanggal) }}</td>
                  <td class="text-start fw-semibold">{{ s.kategori?.nama || '-' }}</td>
                  <td>{{ s.berat }} kg</td>
                  <td>
                    <span class="badge bg-success-subtle text-success px-3 py-2 fw-bold">
                      +{{ Number(s.poin).toLocaleString('id-ID') }} Poin
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-5 text-muted border rounded-3 bg-white">
        <i class="bi bi-person-check fs-1 text-secondary mb-2 d-block"></i>
        Pilih salah satu nasabah pada formulir di atas untuk melihat riwayat setoran & saldo poin mereka.
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
const nasabah = ref([])
const kategori = ref([])
const submitting = ref(false)

const selectedNasabahData = ref(null)
const riwayatSetoran = ref([])

const form = ref({
  user_id: '',
  kategori_id: '',
  berat: '',
  tanggal: new Date().toISOString().substring(0, 10)
})

const selectedKategori = computed(() => {
  return kategori.value.find(k => k.id === form.value.kategori_id) || null
})

const estimasiPoin = computed(() => {
  if (!selectedKategori.value || !form.value.berat) return 0
  return Math.floor(form.value.berat * selectedKategori.value.poin_per_kg)
})

// === LOAD NASABAH & KATEGORI ===
const loadData = async () => {
  const headers = { Authorization: `Bearer ${auth.token}` }
  try {
    const [n, k] = await Promise.all([
      axios.get('/api/nasabah', { headers }),
      axios.get('/api/kategori', { headers })
    ])
    nasabah.value = n.data
    kategori.value = k.data
  } catch (err) {
    Swal.fire('Gagal!', 'Gagal memuat data awal.', 'error')
  }
}

// === PILIH NASABAH & LOAD RIWAYAT ===
const onNasabahChange = async () => {
  if (!form.value.user_id) {
    selectedNasabahData.value = null
    riwayatSetoran.value = []
    return
  }

  try {
    const res = await axios.get(`/api/transaksi/${form.value.user_id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    selectedNasabahData.value = res.data.nasabah
    riwayatSetoran.value = res.data.setoran || []
  } catch {
    selectedNasabahData.value = null
    riwayatSetoran.value = []
  }
}

// === SUBMIT SETORAN ===
const setor = async () => {
  if (!form.value.user_id || !form.value.kategori_id || !form.value.berat || form.value.berat <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Lengkapi Formulir!',
      text: 'Pilih nasabah, jenis sampah, dan masukkan berat sampah yang valid.',
      confirmButtonColor: '#16a34a',
    })
    return
  }

  submitting.value = true
  try {
    const res = await axios.post('/api/transaksi/setoran', form.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    const poinDapat = res.data.poin_dapat ?? estimasiPoin.value

    await Swal.fire({
      icon: 'success',
      title: 'Setoran Berhasil!',
      text: `Setoran berhasil dicatat (+${poinDapat} Poin Sampah untuk nasabah).`,
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    const currentUserId = form.value.user_id
    form.value.kategori_id = ''
    form.value.berat = ''
    form.value.tanggal = new Date().toISOString().substring(0, 10)

    await loadData()
    form.value.user_id = currentUserId
    await onNasabahChange()

  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat mencatat setoran.',
      confirmButtonColor: '#dc2626'
    })
  } finally {
    submitting.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.form-label {
  font-size: 0.9rem;
}
</style>
