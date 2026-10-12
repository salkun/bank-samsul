<template>
    <MainLayout>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Data Kategori Sampah</h3>
      <button class="btn btn-success" @click="showModal = true">+ Tambah</button>
    </div>

    <table class="table table-bordered">
      <thead class="table-success">
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Kategori</th>
          <th>Satuan</th>
          <th>Harga (Rp)</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(k, i) in kategori" :key="k.id">
          <td>{{ i + 1 }}</td>
          <td>{{ k.nama }}</td>
          <td>{{ k.kategori }}</td>
          <td>{{ k.satuan }}</td>
          <td>{{ k.harga_per_kg.toLocaleString() }}</td>
          <td>
            <button class="btn btn-sm btn-warning me-2" @click="edit(k)">Edit</button>
            <button class="btn btn-sm btn-danger" @click="hapus(k.id)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal Tambah/Edit -->
    <div v-if="showModal" class="modal fade show d-block bg-dark bg-opacity-50">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editMode ? 'Edit' : 'Tambah' }} Kategori Sampah</h5>
            <button class="btn-close" @click="resetForm"></button>
          </div>
          <div class="modal-body">
            <input v-model="form.nama" class="form-control mb-2" placeholder="Nama jenis sampah">
            <select v-model="form.kategori" class="form-select mb-2">
              <option value="Organik">Organik</option>
              <option value="Anorganik">Anorganik</option>
            </select>
            <input v-model="form.satuan" class="form-control mb-2" placeholder="Satuan (kg)">
            <input v-model="form.harga_per_kg" type="number" class="form-control mb-2" placeholder="Harga per kg">
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="resetForm">Batal</button>
            <button class="btn btn-success" @click="simpan">{{ editMode ? 'Update' : 'Simpan' }}</button>
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
const form = ref({ id: null, nama: '', kategori: '', satuan: '', harga_per_kg: '' })

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
    swal.error('Gagal menyimpan data', err.response?.data?.message)
  }
}

// === Edit data ===
const edit = (k) => {
  form.value = { ...k }
  editMode.value = true
  showModal.value = true
}

// === Hapus data ===
const hapus = async (id) => {
  const confirmed = await swal.confirm('Yakin ingin menghapus kategori ini?')
  if (!confirmed) return

  try {
    await axios.delete(`/api/kategori/${id}`, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    await loadKategori()
    swal.success('Kategori berhasil dihapus ✅')
  } catch (err) {
    swal.error('Gagal menghapus kategori', err.response?.data?.message)
  }
}

const resetForm = () => {
  form.value = { id: null, nama: '', kategori: '', satuan: '', harga_per_kg: '' }
  showModal.value = false
  editMode.value = false
}

onMounted(loadKategori)
</script>
