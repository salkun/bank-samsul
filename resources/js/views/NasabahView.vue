<template>
    <MainLayout>
  <div class="p-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold text-success mb-0">
        <i class="bi bi-people me-2"></i> Data Nasabah
      </h4>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-success" @click="downloadTemplate">
          <i class="bi bi-file-earmark-arrow-down me-1"></i> Template Excel
        </button>
        <button class="btn btn-outline-primary" @click="openImportModal">
          <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
        </button>
        <button class="btn btn-success" @click="openAddModal">
          <i class="bi bi-person-plus me-1"></i> Tambah Nasabah
        </button>
      </div>
    </div>

    <!-- SEARCH -->
    <div class="input-group mb-3 w-50">
      <span class="input-group-text bg-success text-white">
        <i class="bi bi-search"></i>
      </span>
      <input
        v-model="search"
        type="text"
        class="form-control"
        placeholder="Cari nasabah berdasarkan nama..."
        @input="filterNasabah"
      />
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="text-center text-muted py-3">
      <div class="spinner-border text-success" role="status"></div>
      <p class="mt-2">Memuat data nasabah...</p>
    </div>

    <!-- TABLE -->
    <div v-else>
      <table class="table table-hover align-middle text-center">
        <thead class="table-success">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Poin Sampah</th>
            <th>Poin Lingkungan</th>
            <th>Total Poin</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredNasabah.length === 0">
            <td colspan="7" class="text-muted py-3">Tidak ada data ditemukan</td>
          </tr>
            <tr v-for="(n, i) in paginatedNasabah" :key="n.id">
              <td>{{ (currentPage - 1) * itemsPerPage + i + 1 }}</td>
              <td class="text-start fw-semibold">{{ n.nama }}</td>
              <td>{{ n.username }}</td>
              <td>
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold">
                  <i class="bi bi-recycle me-1"></i>{{ Number(n.poin_sampah ?? 0).toLocaleString() }}
                </span>
              </td>
              <td>
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 fw-semibold">
                  <i class="bi bi-tree me-1"></i>{{ Number(n.poin_lingkungan ?? 0).toLocaleString() }}
                </span>
              </td>
              <td>
                <span class="badge rounded-pill" style="background: linear-gradient(135deg,#059669,#10b981); color:white; font-size:0.85rem; padding: 0.45em 0.8em;">
                  <i class="bi bi-star-fill me-1"></i>{{ Number((n.poin_sampah ?? 0) + (n.poin_lingkungan ?? 0)).toLocaleString() }}
                </span>
              </td>
              <td>
                <button class="btn btn-warning btn-sm me-1" @click="openEditModal(n)" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm me-1" style="background:#f59e0b;color:white;" @click="openPoinModal(n)" title="Nilai Poin Lingkungan">
                  <i class="bi bi-star"></i>
                </button>
                <button v-if="auth.user?.role === 'admin'" class="btn btn-danger btn-sm" @click="hapusNasabah(n.id)" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
        </tbody>
      </table>

      <!-- PAGINATION -->
      <nav v-if="filteredNasabah.length > itemsPerPage" class="d-flex justify-content-center mt-3">
        <ul class="pagination">
          <li
            class="page-item"
            :class="{ disabled: currentPage === 1 }"
            @click="changePage(currentPage - 1)"
          >
            <a class="page-link">«</a>
          </li>
          <li
            v-for="page in totalPages"
            :key="page"
            class="page-item"
            :class="{ active: page === currentPage }"
            @click="changePage(page)"
          >
            <a class="page-link">{{ page }}</a>
          </li>
          <li
            class="page-item"
            :class="{ disabled: currentPage === totalPages }"
            @click="changePage(currentPage + 1)"
          >
            <a class="page-link">»</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- IMPORT MODAL -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" ref="importModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
          <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #16a34a, #15803d);">
            <h5 class="modal-title fw-bold" id="importModalLabel">
              <i class="bi bi-file-earmark-excel me-2"></i> Import Data Nasabah
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="alert border-0 mb-3" style="background: rgba(22, 163, 74, 0.08); border-radius: 10px;">
              <i class="bi bi-info-circle-fill text-success me-2"></i>
              <strong>Petunjuk:</strong> Gunakan format file Excel (<code>.xlsx</code>) dengan kolom:<br>
              <code>nama</code>, <code>username</code>, <code>password</code>, <code>saldo</code>, <code>poin</code>.<br>
              <span class="text-muted small">Kolom <strong>password</strong> opsional (default: <strong>password123</strong>).
              Kolom <strong>saldo</strong> & <strong>poin</strong> opsional (default: 0).
              Username yang sudah ada akan dilewati otomatis.</span>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Pilih File Excel</label>
              <div class="upload-area" :class="{ 'has-file': importFile }" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="handleDrop">
                <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="d-none" @change="handleFileChange" />
                <div v-if="!importFile" class="text-center py-3">
                  <i class="bi bi-cloud-upload display-5 text-success opacity-50"></i>
                  <p class="mb-0 mt-2 text-muted small">Klik atau seret file ke sini</p>
                  <p class="text-muted" style="font-size: 0.75rem;">.xlsx, .xls, .csv — maks. 2MB</p>
                </div>
                <div v-else class="d-flex align-items-center gap-3 px-3 py-2">
                  <i class="bi bi-file-earmark-spreadsheet text-success fs-2"></i>
                  <div>
                    <div class="fw-semibold">{{ importFile.name }}</div>
                    <div class="text-muted small">{{ (importFile.size / 1024).toFixed(1) }} KB</div>
                  </div>
                  <button class="btn btn-sm btn-outline-danger ms-auto" @click.stop="importFile = null">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="importResult" class="alert border-0 mt-3" :class="importResult.type === 'success' ? 'alert-success' : 'alert-danger'" style="border-radius: 10px;">
              <i :class="importResult.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'" class="me-2"></i>
              {{ importResult.message }}
            </div>
          </div>
          <div class="modal-footer border-0 p-3" style="background: #f9fafb;">
            <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button class="btn btn-success px-4" @click="importExcel" :disabled="!importFile || importing">
              <span v-if="importing" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-upload me-1"></i>
              {{ importing ? 'Mengimpor...' : 'Import Sekarang' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PENILAIAN POIN MODAL -->
    <div class="modal fade" id="poinModal" tabindex="-1" aria-labelledby="poinModalLabel" aria-hidden="true" ref="poinModalRef">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
          <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#f59e0b,#b45309);">
            <div>
              <h5 class="modal-title fw-bold mb-0" id="poinModalLabel">
                <i class="bi bi-star-fill me-2"></i>Penilaian Poin — {{ poinTarget?.nama }}
              </h5>
              <small class="opacity-75">Poin saat ini: {{ poinTarget?.poin ?? 0 }} poin</small>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">

            <!-- Tab kategori -->
            <div class="d-flex border-bottom">
              <button class="poin-tab flex-fill py-3 fw-semibold" :class="{ active: poinForm.kategori === 'kebersihan_kelas' }"
                @click="poinForm.kategori = 'kebersihan_kelas'; poinForm.rating = 0">
                <i class="bi bi-stars me-2"></i>Kebersihan Kelas
                <span class="badge ms-2" :style="poinForm.kategori === 'kebersihan_kelas' ? 'background:#f59e0b' : 'background:#e5e7eb;color:#6b7280'">1–10</span>
              </button>
              <button class="poin-tab flex-fill py-3 fw-semibold" :class="{ active: poinForm.kategori === 'pengelolaan_sampah' }"
                @click="poinForm.kategori = 'pengelolaan_sampah'; poinForm.rating = 0">
                <i class="bi bi-recycle me-2"></i>Pengelolaan Sampah
                <span class="badge ms-2" :style="poinForm.kategori === 'pengelolaan_sampah' ? 'background:#f59e0b' : 'background:#e5e7eb;color:#6b7280'">1–5</span>
              </button>
            </div>

            <div class="p-4">
              <!-- === KEBERSIHAN KELAS (1-10) === -->
              <div v-if="poinForm.kategori === 'kebersihan_kelas'">
                <p class="text-muted small mb-3">
                  <i class="bi bi-info-circle me-1"></i>
                  Nilai kondisi fisik kelas: lantai, meja, kaca, dan kerapian ruangan.
                </p>

                <!-- Rating 1-10 grid -->
                <div class="rating-grid-10 mb-3">
                  <button v-for="n in 10" :key="n"
                    class="rating-btn"
                    :class="getRatingClass(n, 'kebersihan_kelas')"
                    @click="poinForm.rating = n">
                    {{ n }}
                  </button>
                </div>

                <!-- Label deskripsi -->
                <div class="d-flex justify-content-between text-muted small px-1 mb-3">
                  <span>😫 Sangat Kotor</span>
                  <span>😐 Cukup</span>
                  <span>✨ Sangat Bersih</span>
                </div>

                <!-- Preview rating -->
                <div v-if="poinForm.rating" class="rating-preview">
                  <span class="fs-2 fw-bold" style="color:#f59e0b;">{{ poinForm.rating }}</span>
                  <span class="ms-2 text-muted">/ 10 —</span>
                  <span class="ms-2 fw-semibold">{{ getKebLabel(poinForm.rating) }}</span>
                  <span class="ms-3 badge" style="background:rgba(245,158,11,0.15);color:#92400e;">+{{ poinForm.rating }} poin</span>
                </div>
              </div>

              <!-- === PENGELOLAAN SAMPAH (1-5) === -->
              <div v-if="poinForm.kategori === 'pengelolaan_sampah'">
                <p class="text-muted small mb-3">
                  <i class="bi bi-info-circle me-1"></i>
                  Nilai ketertiban pemilahan organik/anorganik, sisa makanan, dan sampah residu.
                </p>

                <!-- Rating 1-5 sebagai kartu deskriptif -->
                <div class="d-flex flex-column gap-2 mb-3">
                  <button v-for="item in pengelolaanOptions" :key="item.value"
                    class="rating-card-btn"
                    :class="{ selected: poinForm.rating === item.value }"
                    @click="poinForm.rating = item.value">
                    <div class="d-flex align-items-center gap-3">
                      <span class="rating-number">{{ item.value }}</span>
                      <div class="text-start">
                        <div class="fw-semibold">{{ item.label }}</div>
                        <div class="small text-muted">{{ item.desc }}</div>
                      </div>
                      <span class="ms-auto badge" style="background:rgba(245,158,11,0.15);color:#92400e;">+{{ item.value }} poin</span>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Catatan -->
              <div class="mt-3">
                <label class="form-label fw-semibold text-secondary small">Catatan <span class="text-muted">(opsional)</span></label>
                <textarea v-model="poinForm.catatan" class="form-control" rows="2"
                  placeholder="Misal: Meja berdebu di sudut kanan, tong sampah sudah dipisah..."></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer border-0 p-3" style="background:#fafafa;">
            <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button class="btn fw-semibold px-4 text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);"
              @click="simpanPoin" :disabled="!poinForm.rating || savingPoin">
              <span v-if="savingPoin" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-check-circle me-1"></i>
              Simpan Penilaian{{ poinForm.rating ? ` (+${poinForm.rating} poin)` : '' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ADD / EDIT MODAL -->
    <div
      class="modal fade"
      id="nasabahModal"
      tabindex="-1"
      aria-labelledby="nasabahModalLabel"
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="nasabahModalLabel">
              {{ isEditing ? 'Edit Nasabah' : 'Tambah Nasabah' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="simpanNasabah">
              <div class="mb-3">
                <label class="form-label">Nama</label>
                <input v-model="form.nama" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input v-model="form.username" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input v-model="form.password" type="password" class="form-control" :required="!isEditing" />
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-success">
                  <i class="bi bi-check-circle me-1"></i> Simpan
                </button>
              </div>
            </form>
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
import { useAuthStore } from '../stores/useAuthStore'
import * as bootstrap from 'bootstrap'
import MainLayout from '../layouts/MainLayout.vue'
import { swal } from '../plugins/swal'

const auth = useAuthStore()
auth.setAuthHeader()
const api = auth.api

const nasabah = ref([])
const filteredNasabah = ref([])
const loading = ref(true)
const search = ref('')
const modalRef = ref(null)
const importModalRef = ref(null)
const poinModalRef = ref(null)
let modalInstance = null
let importModalInstance = null
let poinModalInstance = null
const isEditing = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)
const poinTarget = ref(null)
const savingPoin = ref(false)
const poinForm = ref({ kategori: 'kebersihan_kelas', rating: 0, catatan: '' })

// Opsi pengelolaan sampah (1-5)
const pengelolaanOptions = [
  { value: 1, label: 'Sangat Buruk', desc: 'Tidak ada pemilahan, sampah bercampur semua' },
  { value: 2, label: 'Buruk', desc: 'Pemilahan minim, banyak sampah di tempat salah' },
  { value: 3, label: 'Cukup', desc: 'Pemilahan ada tapi belum konsisten' },
  { value: 4, label: 'Baik', desc: 'Sebagian besar sampah sudah dipilah dengan benar' },
  { value: 5, label: 'Sangat Baik', desc: 'Pemilahan organik/anorganik/residu sempurna' },
]
const form = ref({
  id: null,
  nama: '',
  username: '',
  password: ''
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 5

// === Fetch Data ===
const getNasabah = async () => {
  try {
    const res = await api.get('/nasabah')
    nasabah.value = res.data
    filteredNasabah.value = res.data
  } catch (err) {
    alert('Gagal memuat nasabah: ' + (err.response?.data?.message || err.message))
  } finally {
    loading.value = false
  }
}

const filterNasabah = () => {
  const term = search.value.toLowerCase()
  filteredNasabah.value = nasabah.value.filter(n =>
    n.nama.toLowerCase().includes(term)
  )
  currentPage.value = 1
}

const totalPages = computed(() => Math.ceil(filteredNasabah.value.length / itemsPerPage))
const paginatedNasabah = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredNasabah.value.slice(start, end)
})

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

// === MODAL ===
const openAddModal = () => {
  isEditing.value = false
  form.value = { id: null, nama: '', username: '', password: '' }
  modalInstance.show()
}

const openEditModal = (n) => {
  isEditing.value = true
  form.value = { id: n.id, nama: n.nama, username: n.username, password: '' }
  modalInstance.show()
}

const simpanNasabah = async () => {
  try {
    let res
    if (isEditing.value) {
      res = await api.put(`/nasabah/${form.value.id}`, form.value)
    } else {
      res = await api.post('/nasabah', form.value)
    }

    modalInstance.hide()
    await getNasabah()

    // ✅ Notifikasi sukses
    await swal.success(
      isEditing.value
        ? 'Data nasabah berhasil diperbarui ✅'
        : 'Nasabah baru berhasil ditambahkan 🎉',
      'Sukses'
    )

  } catch (err) {
    const message = err.response?.data?.message || err.message || 'Terjadi kesalahan'

    // ❌ Notifikasi error
    swal.error('Gagal menyimpan data', message)
  }
}

const hapusNasabah = async (id) => {
  const confirm = await swal.warning('Yakin ingin menghapus nasabah ini?')
  if (!confirm.isConfirmed) return

  try {
    await api.delete(`/nasabah/${id}`)
    swal.success('Nasabah berhasil dihapus!')
    getNasabah()
  } catch (err) {
    swal.error('Gagal menghapus nasabah.')
  }
}

onMounted(() => {
  getNasabah()
  modalInstance = new bootstrap.Modal(modalRef.value)
  importModalInstance = new bootstrap.Modal(importModalRef.value)
  poinModalInstance = new bootstrap.Modal(poinModalRef.value)
})

// === KELOLA POIN ===
const openPoinModal = (n) => {
  poinTarget.value = { ...n }
  poinForm.value = { kategori: 'kebersihan_kelas', rating: 0, catatan: '' }
  poinModalInstance.show()
}

const getRatingClass = (n, kategori) => {
  if (poinForm.value.rating === n) return 'active'
  if (kategori === 'kebersihan_kelas') {
    if (n <= 3) return 'danger'
    if (n <= 6) return 'warning'
    return 'success'
  }
  return ''
}

const getKebLabel = (n) => {
  if (n <= 2) return 'Sangat Kotor'
  if (n <= 4) return 'Kotor'
  if (n <= 6) return 'Cukup Bersih'
  if (n <= 8) return 'Bersih'
  return 'Sangat Bersih'
}

const simpanPoin = async () => {
  if (!poinForm.value.rating) return
  savingPoin.value = true
  try {
    await api.post('/penilaian-poin', {
      nasabah_id: poinTarget.value.id,
      kategori:   poinForm.value.kategori,
      rating:     poinForm.value.rating,
      catatan:    poinForm.value.catatan
    })
    poinModalInstance.hide()
    await getNasabah()
    const katLabel = poinForm.value.kategori === 'kebersihan_kelas' ? 'Kebersihan Kelas' : 'Pengelolaan Sampah'
    swal.success(
      `Penilaian ${katLabel} berhasil! +${poinForm.value.rating} poin untuk ${poinTarget.value.nama} 🌟`,
      'Penilaian Disimpan'
    )
  } catch (err) {
    swal.error('Gagal menyimpan penilaian.', err.response?.data?.message || '')
  } finally {
    savingPoin.value = false
  }
}

// === IMPORT EXCEL ===
const openImportModal = () => {
  importFile.value = null
  importResult.value = null
  importModalInstance.show()
}

const handleFileChange = (e) => {
  importFile.value = e.target.files[0] || null
  importResult.value = null
}

const handleDrop = (e) => {
  importFile.value = e.dataTransfer.files[0] || null
  importResult.value = null
}

const importExcel = async () => {
  if (!importFile.value) return
  importing.value = true
  importResult.value = null
  try {
    const formData = new FormData()
    formData.append('file', importFile.value)
    const res = await api.post('/nasabah/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    importResult.value = { type: 'success', message: res.data.message }
    await getNasabah()
  } catch (err) {
    importResult.value = { type: 'error', message: err.response?.data?.message || 'Gagal mengimpor file.' }
  } finally {
    importing.value = false
  }
}

const downloadTemplate = async () => {
  try {
    const response = await axios.get('/api/nasabah/template', {
      headers: { Authorization: `Bearer ${auth.token}` },
      responseType: 'blob'
    })
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = 'template_import_nasabah.xlsx'
    link.click()
  } catch (err) {
    swal.error('Gagal mengunduh template.')
  }
}
</script>

<style scoped>
.table td,
.table th {
  vertical-align: middle;
}

.page-link {
  cursor: pointer;
  color: #198754;
}
.page-item.active .page-link {
  background-color: #198754;
  border-color: #198754;
  color: white;
}
.page-item.disabled .page-link {
  color: #ccc;
}

.upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 100px;
  background: #f9fafb;
}

.upload-area:hover,
.upload-area.has-file {
  border-color: #16a34a;
  background: rgba(22, 163, 74, 0.04);
}

.poin-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
  font-size: 1.5rem;
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

/* Poin Tab */
.poin-tab {
  background: white;
  border: none;
  border-bottom: 3px solid transparent;
  color: #6b7280;
  transition: all 0.2s ease;
}
.poin-tab:hover { background: #fef9ec; color: #d97706; }
.poin-tab.active {
  color: #d97706;
  border-bottom-color: #f59e0b;
  background: #fef9ec;
  font-weight: 700;
}

/* Rating Grid 1-10 */
.rating-grid-10 {
  display: grid;
  grid-template-columns: repeat(10, 1fr);
  gap: 6px;
}
.rating-btn {
  aspect-ratio: 1;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.15s ease;
  color: #4b5563;
}
.rating-btn:hover { transform: scale(1.1); border-color: #f59e0b; }
.rating-btn.active {
  background: #f59e0b !important;
  border-color: #f59e0b !important;
  color: white !important;
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(245,158,11,0.4);
}
.rating-btn.danger  { border-color: #fca5a5; color: #dc2626; }
.rating-btn.warning { border-color: #fde68a; color: #d97706; }
.rating-btn.success { border-color: #86efac; color: #16a34a; }

/* Rating Preview */
.rating-preview {
  background: #fef9ec;
  border: 1px solid #fde68a;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
}

/* Rating Card Buttons (1-5) */
.rating-card-btn {
  width: 100%;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: all 0.15s ease;
  text-align: left;
}
.rating-card-btn:hover { border-color: #f59e0b; background: #fef9ec; }
.rating-card-btn.selected {
  border-color: #f59e0b;
  background: linear-gradient(135deg, #fef9ec, #fff7ed);
  box-shadow: 0 2px 10px rgba(245,158,11,0.2);
}
.rating-number {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f59e0b;
  color: white;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}
</style>
