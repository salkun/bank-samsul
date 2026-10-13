<template>
  <MainLayout>
    <div class="container py-4">
      <!-- Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
          <h3 class="fw-bold text-success mb-1">
            <i class="bi bi-gift me-2"></i> Kelola Barang Redeem
          </h3>
          <p class="text-muted mb-0">Atur katalog barang penukaran Poin Sampah untuk nasabah.</p>
        </div>
        <button class="btn btn-success shadow-sm" @click="openModalAdd">
          <i class="bi bi-plus-circle me-1"></i> Tambah Barang
        </button>
      </div>

      <!-- Quick Stat Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm p-3 h-100 stat-box">
            <span class="text-muted small">Total Barang</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">{{ items.length }} Item</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm p-3 h-100 stat-box">
            <span class="text-muted small">Barang Aktif</span>
            <h4 class="fw-bold text-success mt-1 mb-0">{{ countAktif }} Item</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm p-3 h-100 stat-box">
            <span class="text-muted small">Total Stok Tersedia</span>
            <h4 class="fw-bold text-primary mt-1 mb-0">{{ totalStok }} pcs</h4>
          </div>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-2 align-items-center">
          <div class="col-md-7">
            <div class="input-group">
              <span class="input-group-text bg-light text-muted border-end-0">
                <i class="bi bi-search"></i>
              </span>
              <input
                v-model="search"
                type="text"
                class="form-control border-start-0"
                placeholder="Cari nama barang..."
              />
            </div>
          </div>
          <div class="col-md-3">
            <select v-model="filterStatus" class="form-select">
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="col-md-2 text-md-end">
            <button class="btn btn-outline-secondary w-100" @click="resetFilters">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5 text-muted">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-2">Memuat daftar barang redeem...</p>
      </div>

      <!-- Table of Redeem Items -->
      <div v-else class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-success">
              <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 80px;">Foto</th>
                <th class="text-start">Nama Barang</th>
                <th>Harga Poin Sampah</th>
                <th>Stok</th>
                <th>Status</th>
                <th style="width: 140px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!filteredItems.length">
                <td colspan="7" class="text-muted py-4">Tidak ada barang yang sesuai.</td>
              </tr>
              <tr v-for="(item, index) in filteredItems" :key="item.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <img
                    v-if="item.foto"
                    :src="fotoUrl(item.foto)"
                    class="rounded object-fit-cover shadow-sm border"
                    style="width: 54px; height: 54px;"
                    alt="Foto Barang"
                    @error="onImgError"
                  />
                  <div
                    v-else
                    class="rounded bg-light text-muted d-flex align-items-center justify-content-center mx-auto border"
                    style="width: 54px; height: 54px;"
                  >
                    <i class="bi bi-gift fs-4 text-secondary"></i>
                  </div>
                </td>
                <td class="text-start">
                  <div class="fw-semibold text-dark">{{ item.nama }}</div>
                  <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">
                    {{ item.deskripsi || '-' }}
                  </small>
                </td>
                <td>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold fs-6">
                    <i class="bi bi-recycle me-1"></i>
                    {{ Number(item.harga_poin).toLocaleString('id-ID') }} Poin
                  </span>
                </td>
                <td>
                  <span
                    class="badge rounded-pill px-3 py-2"
                    :class="{
                      'bg-danger text-white': item.stok === 0,
                      'bg-warning text-dark': item.stok > 0 && item.stok <= 5,
                      'bg-light text-dark border': item.stok > 5
                    }"
                  >
                    {{ item.stok }} pcs
                  </span>
                </td>
                <td>
                  <span
                    class="badge px-3 py-2 text-uppercase"
                    :class="item.aktif ? 'bg-success' : 'bg-secondary'"
                  >
                    {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-warning" @click="openModalEdit(item)" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="hapusBarang(item)" title="Hapus">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Tambah/Edit Barang -->
      <div v-if="showModal" class="modal fade show d-block bg-dark bg-opacity-50" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title fw-bold">
                <i :class="isEdit ? 'bi bi-pencil-square' : 'bi bi-plus-circle'" class="me-2"></i>
                {{ isEdit ? 'Edit Barang Redeem' : 'Tambah Barang Redeem' }}
              </h5>
              <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
            </div>
            <form @submit.prevent="simpanBarang">
              <div class="modal-body p-4">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                  <input
                    v-model="form.nama"
                    type="text"
                    class="form-control"
                    placeholder="Contoh: Tumbler Minum Al-Muhajirin"
                    required
                  />
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Deskripsi</label>
                  <textarea
                    v-model="form.deskripsi"
                    rows="2"
                    class="form-control"
                    placeholder="Keterangan singkat tentang barang..."
                  ></textarea>
                </div>

                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga Poin Sampah <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        v-model.number="form.harga_poin"
                        type="number"
                        min="1"
                        class="form-control"
                        placeholder="100"
                        required
                      />
                      <span class="input-group-text bg-light text-muted small">Poin</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Stok Barang <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        v-model.number="form.stok"
                        type="number"
                        min="0"
                        class="form-control"
                        placeholder="10"
                        required
                      />
                      <span class="input-group-text bg-light text-muted small">pcs</span>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check form-switch fs-6">
                    <input
                      v-model="form.aktif"
                      class="form-check-input"
                      type="checkbox"
                      role="switch"
                      id="aktifSwitch"
                    />
                    <label class="form-check-label fw-semibold ms-2" for="aktifSwitch">
                      {{ form.aktif ? 'Status: Aktif (Bisa Ditukar)' : 'Status: Nonaktif (Sembunyikan)' }}
                    </label>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label fw-semibold">Foto Barang (Opsional)</label>
                  <input
                    type="file"
                    class="form-control"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    @change="handleFileUpload"
                  />
                  <div class="form-text small text-muted">Format: JPG, PNG, WEBP (Maksimal 2MB)</div>

                  <!-- Preview foto -->
                  <div v-if="previewFoto" class="mt-2 text-center">
                    <img :src="previewFoto" class="img-thumbnail rounded" style="max-height: 120px;" alt="Preview" />
                  </div>
                </div>
              </div>

              <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-success px-4" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                  {{ isEdit ? 'Simpan Perubahan' : 'Tambah Barang' }}
                </button>
              </div>
            </form>
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

const items = ref([])
const loading = ref(true)
const submitting = ref(false)
const search = ref('')
const filterStatus = ref('')

const showModal = ref(false)
const isEdit = ref(false)
const previewFoto = ref(null)
const fileFoto = ref(null)

const form = ref({
  id: null,
  nama: '',
  deskripsi: '',
  harga_poin: 100,
  stok: 10,
  aktif: true,
})

// === STAT COUNTERS ===
const countAktif = computed(() => items.value.filter(i => i.aktif).length)
const totalStok = computed(() => items.value.reduce((acc, curr) => acc + (Number(curr.stok) || 0), 0))

// === FILTERED ITEMS ===
const filteredItems = computed(() => {
  return items.value.filter(item => {
    const matchSearch = item.nama.toLowerCase().includes(search.value.toLowerCase()) ||
      (item.deskripsi && item.deskripsi.toLowerCase().includes(search.value.toLowerCase()))
    const matchStatus = !filterStatus.value ||
      (filterStatus.value === 'aktif' ? item.aktif : !item.aktif)
    return matchSearch && matchStatus
  })
})

const resetFilters = () => {
  search.value = ''
  filterStatus.value = ''
}

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

// === LOAD DATA ===
const loadItems = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/barang-redeem', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    items.value = res.data.data || res.data || []
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal memuat barang redeem.', 'error')
  } finally {
    loading.value = false
  }
}

// === MODAL ADD/EDIT ===
const openModalAdd = () => {
  isEdit.value = false
  previewFoto.value = null
  fileFoto.value = null
  form.value = {
    id: null,
    nama: '',
    deskripsi: '',
    harga_poin: 100,
    stok: 10,
    aktif: true,
  }
  showModal.value = true
}

const openModalEdit = (item) => {
  isEdit.value = true
  fileFoto.value = null
  previewFoto.value = item.foto ? fotoUrl(item.foto) : null
  form.value = {
    id: item.id,
    nama: item.nama,
    deskripsi: item.deskripsi || '',
    harga_poin: item.harga_poin,
    stok: item.stok,
    aktif: Boolean(item.aktif),
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (file.size > 2 * 1024 * 1024) {
    Swal.fire('Ukuran Terlalu Besar', 'Maksimal ukuran file foto adalah 2MB.', 'warning')
    e.target.value = ''
    return
  }

  fileFoto.value = file
  previewFoto.value = URL.createObjectURL(file)
}

// === SUBMIT ITEM ===
const simpanBarang = async () => {
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('nama', form.value.nama)
    formData.append('deskripsi', form.value.deskripsi || '')
    formData.append('harga_poin', form.value.harga_poin)
    formData.append('stok', form.value.stok)
    formData.append('aktif', form.value.aktif ? '1' : '0')

    if (fileFoto.value) {
      formData.append('foto', fileFoto.value)
    }

    const url = isEdit.value
      ? `/api/admin/barang-redeem/${form.value.id}`
      : '/api/admin/barang-redeem'

    await axios.post(url, formData, {
      headers: {
        Authorization: `Bearer ${auth.token}`,
        'Content-Type': 'multipart/form-data',
      }
    })

    Swal.fire({
      icon: 'success',
      title: isEdit.value ? 'Barang Diperbarui!' : 'Barang Ditambahkan!',
      showConfirmButton: false,
      timer: 1600,
      toast: true,
      position: 'top-end'
    })

    closeModal()
    await loadItems()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.', 'error')
  } finally {
    submitting.value = false
  }
}

// === HAPUS ITEM ===
const hapusBarang = async (item) => {
  const result = await Swal.fire({
    title: 'Hapus Barang?',
    text: `Yakin ingin menghapus "${item.nama}"? Data yang sudah dihapus tidak dapat dikembalikan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal'
  })

  if (!result.isConfirmed) return

  try {
    await axios.delete(`/api/admin/barang-redeem/${item.id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    Swal.fire({
      icon: 'success',
      title: 'Barang berhasil dihapus.',
      showConfirmButton: false,
      timer: 1600,
      toast: true,
      position: 'top-end'
    })

    await loadItems()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal menghapus barang.', 'error')
  }
}

onMounted(() => {
  loadItems()
})
</script>

<style scoped>
.stat-box {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
</style>
