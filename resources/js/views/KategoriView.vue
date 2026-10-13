<template>
  <MainLayout>
    <div class="container mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-success mb-0">
          <i class="bi bi-trash me-2"></i> Data Kategori Sampah
        </h3>
        <button class="btn btn-success shadow-sm" @click="showModal = true">
          <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
        </button>
      </div>

      <div class="card border-0 shadow-sm overflow-hidden">
        <table class="table table-hover align-middle mb-0 text-center">
          <thead class="table-success">
            <tr>
              <th style="width: 50px;">No</th>
              <th class="text-start">Nama</th>
              <th>Kategori</th>
              <th>Satuan</th>
              <th>Poin per Kg</th>
              <th style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(k, i) in kategori" :key="k.id">
              <td>{{ i + 1 }}</td>
              <td class="text-start fw-semibold text-dark">{{ k.nama }}</td>
              <td>
                <span
                  class="badge px-3 py-2 text-capitalize"
                  :class="k.kategori === 'Organik' ? 'bg-success' : 'bg-primary'"
                >
                  {{ k.kategori }}
                </span>
              </td>
              <td>{{ k.satuan }}</td>
              <td>
                <span class="fw-bold text-success fs-6">{{ Number(k.poin_per_kg).toLocaleString('id-ID') }}</span>
                <small class="text-muted ms-1">poin/kg</small>
              </td>
              <td>
                <button class="btn btn-sm btn-outline-warning me-1" @click="edit(k)" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="hapus(k.id)" title="Hapus">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="!kategori.length">
              <td colspan="6" class="text-muted py-4">Belum ada kategori sampah.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal Tambah/Edit -->
      <div v-if="showModal" class="modal fade show d-block bg-dark bg-opacity-50">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title fw-bold">
                {{ editMode ? 'Edit Kategori Sampah' : 'Tambah Kategori Sampah' }}
              </h5>
              <button class="btn-close btn-close-white" @click="resetForm"></button>
            </div>
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Jenis Sampah</label>
                <input v-model="form.nama" class="form-control" placeholder="Contoh: Kardus / Botol Plastik" />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Tipe Kategori</label>
                <select v-model="form.kategori" class="form-select">
                  <option value="Organik">Organik</option>
                  <option value="Anorganik">Anorganik</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Satuan</label>
                <input v-model="form.satuan" class="form-control" placeholder="kg" />
              </div>
              <div class="mb-2">
                <label class="form-label fw-semibold">Poin per Kg</label>
                <input
                  v-model.number="form.poin_per_kg"
                  type="number"
                  min="1"
                  class="form-control"
                  placeholder="Contoh: 1000"
                />
                <div class="form-text small text-muted">Jumlah Poin Sampah yang didapat nasabah per 1 kg.</div>
              </div>
            </div>
            <div class="modal-footer bg-light p-3">
              <button class="btn btn-secondary" @click="resetForm">Batal</button>
              <button class="btn btn-success px-4" @click="simpan">
                {{ editMode ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/useAuthStore'
import axios from 'axios'
import MainLayout from '../layouts/MainLayout.vue'
import { swal } from '../plugins/swal'

const auth = useAuthStore()
const kategori = ref([])
const showModal = ref(false)
const editMode = ref(false)
const form = ref({ id: null, nama: '', kategori: 'Anorganik', satuan: 'kg', poin_per_kg: 100 })

const loadKategori = async () => {
  const res = await axios.get('/api/kategori', {
    headers: { Authorization: `Bearer ${auth.token}` }
  })
  kategori.value = res.data
}

// === Simpan data ===
const simpan = async () => {
  try {
    const url = editMode.value
      ? `/api/kategori/${form.value.id}`
      : '/api/kategori'
    const method = editMode.value ? 'put' : 'post'

    await axios[method](url, form.value, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    await loadKategori()
    resetForm()

    swal.success(
      editMode.value ? 'Kategori berhasil diperbarui' : 'Kategori berhasil ditambahkan'
    )
  } catch (err) {
    swal.error(err.response?.data?.message || 'Gagal menyimpan data', 'Gagal')
  }
}

// === Edit data ===
const edit = (k) => {
  form.value = {
    id: k.id,
    nama: k.nama,
    kategori: k.kategori,
    satuan: k.satuan,
    poin_per_kg: k.poin_per_kg
  }
  editMode.value = true
  showModal.value = true
}

// === Hapus data ===
const hapus = async (id) => {
  const res = await swal.warning('Yakin ingin menghapus kategori sampah ini?')
  if (!res.isConfirmed) return

  try {
    await axios.delete(`/api/kategori/${id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    await loadKategori()
    swal.success('Kategori berhasil dihapus ✅')
  } catch (err) {
    swal.error(err.response?.data?.message || 'Gagal menghapus kategori', 'Gagal')
  }
}

const resetForm = () => {
  form.value = { id: null, nama: '', kategori: 'Anorganik', satuan: 'kg', poin_per_kg: 100 }
  showModal.value = false
  editMode.value = false
}

onMounted(loadKategori)
</script>
