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
              min="0.01"
              step="0.01"
              placeholder="0.00"
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
                  <th style="width: 100px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!riwayatSetoran.length">
                  <td colspan="6" class="text-muted py-4">Belum ada catatan setoran untuk nasabah ini.</td>
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
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-warning d-inline-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px; padding: 0;"
                        @click="openEditModal(s)"
                        title="Edit Setoran"
                      >
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px; padding: 0;"
                        @click="deleteSetoran(s)"
                        title="Hapus Setoran"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
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

      <!-- MODAL EDIT SETORAN -->
      <div v-if="showEditModal" class="modal fade show d-block bg-dark bg-opacity-50" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
              <h5 class="modal-title fw-bold mb-0">
                <i class="bi bi-pencil-square me-2"></i> Edit Setoran Sampah
              </h5>
              <button type="button" class="btn-close btn-close-white" @click="closeEditModal" :disabled="editSubmitting"></button>
            </div>

            <div class="modal-body p-4">
              <!-- Info Nasabah -->
              <div class="alert alert-light border d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3">
                <i class="bi bi-person-fill text-success fs-5"></i>
                <div>
                  <small class="text-muted d-block" style="font-size: 0.75rem;">Nasabah</small>
                  <span class="fw-bold text-dark">{{ selectedNasabahData?.nama }}</span>
                  <span class="text-muted small"> (@{{ selectedNasabahData?.username }})</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Sampah <span class="text-danger">*</span></label>
                <select v-model="editForm.kategori_id" class="form-select">
                  <option value="">-- Pilih Jenis --</option>
                  <option v-for="k in kategori" :key="k.id" :value="k.id">
                    {{ k.nama }} ({{ Number(k.poin_per_kg).toLocaleString() }} poin/{{ k.satuan }})
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Berat (kg) <span class="text-danger">*</span></label>
                <input
                  v-model.number="editForm.berat"
                  type="number"
                  class="form-control"
                  min="0.01"
                  step="0.01"
                  placeholder="0.00"
                />
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Setor</label>
                <input v-model="editForm.tanggal" type="date" class="form-control" />
              </div>

              <!-- Estimasi Poin Baru & Perbandingan -->
              <div v-if="editSelectedKategori && editForm.berat > 0" class="alert alert-success border-0 py-2 px-3 rounded-3 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="small"><i class="bi bi-calculator me-1"></i> Perhitungan Baru:</span>
                  <span class="fw-bold text-success">+{{ editEstimasiPoin.toLocaleString('id-ID') }} Poin</span>
                </div>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                  <span>Poin Sebelumnya: {{ editPoinLama.toLocaleString('id-ID') }} Poin</span>
                  <span :class="editSelisihPoin >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold'">
                    Selisih: {{ editSelisihPoin >= 0 ? '+' : '' }}{{ editSelisihPoin.toLocaleString('id-ID') }} Poin
                  </span>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light border-0 py-3 px-4">
              <button type="button" class="btn btn-secondary px-3" @click="closeEditModal" :disabled="editSubmitting">
                Batal
              </button>
              <button type="button" class="btn btn-success px-4" @click="saveEditSetoran" :disabled="editSubmitting">
                <span v-if="editSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check-circle me-1"></i> Simpan Perubahan
              </button>
            </div>
          </div>
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

// === EDIT & DELETE SETORAN ===
const showEditModal = ref(false)
const editSubmitting = ref(false)
const editForm = ref({
  id: null,
  kategori_id: '',
  berat: '',
  tanggal: ''
})
const editPoinLama = ref(0)

const editSelectedKategori = computed(() => {
  return kategori.value.find(k => k.id === editForm.value.kategori_id) || null
})

const editEstimasiPoin = computed(() => {
  if (!editSelectedKategori.value || !editForm.value.berat) return 0
  return Math.floor(editForm.value.berat * editSelectedKategori.value.poin_per_kg)
})

const editSelisihPoin = computed(() => {
  return editEstimasiPoin.value - (editPoinLama.value || 0)
})

const openEditModal = (item) => {
  editForm.value = {
    id: item.id,
    kategori_id: item.kategori_id,
    berat: item.berat,
    tanggal: item.tanggal ? String(item.tanggal).substring(0, 10) : ''
  }
  editPoinLama.value = item.poin || 0
  showEditModal.value = true
}

const closeEditModal = () => {
  if (editSubmitting.value) return
  showEditModal.value = false
}

const saveEditSetoran = async () => {
  if (!editForm.value.kategori_id || !editForm.value.berat || editForm.value.berat <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Lengkapi Formulir!',
      text: 'Pilih jenis sampah dan masukkan berat yang valid (minimal 0.01 kg).',
      confirmButtonColor: '#16a34a'
    })
    return
  }

  editSubmitting.value = true
  try {
    const res = await axios.put(`/api/transaksi/setoran/${editForm.value.id}`, editForm.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    await Swal.fire({
      icon: 'success',
      title: 'Setoran Diperbarui!',
      text: res.data.message || 'Setoran sampah berhasil diperbarui.',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    closeEditModal()
    await loadData()
    await onNasabahChange()
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memperbarui!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat memperbarui setoran.',
      confirmButtonColor: '#dc2626'
    })
  } finally {
    editSubmitting.value = false
  }
}

const deleteSetoran = async (item) => {
  const result = await Swal.fire({
    title: 'Hapus Setoran Ini?',
    html: `Apakah Anda yakin ingin menghapus setoran <b>${item.kategori?.nama || 'sampah'} (${item.berat} kg)</b>?<br><br><span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Poin nasabah sebanyak <b>${item.poin} Poin</b> akan dikurangkan secara otomatis.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
    cancelButtonText: 'Batal'
  })

  if (!result.isConfirmed) return

  try {
    const res = await axios.delete(`/api/transaksi/setoran/${item.id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    await Swal.fire({
      icon: 'success',
      title: 'Setoran Dihapus!',
      text: res.data.message || 'Setoran sampah berhasil dihapus.',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    await loadData()
    await onNasabahChange()
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Menghapus!',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menghapus setoran.',
      confirmButtonColor: '#dc2626'
    })
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
