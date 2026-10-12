<template>
  <MainLayout>
    <div class="container mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-success mb-0">💸 Validasi Penarikan</h3>
        <button class="btn btn-outline-success btn-sm" @click="loadPenarikan">
          🔄 Refresh
        </button>
      </div>

      <div v-if="loading" class="text-center py-4 text-muted">
        <div class="spinner-border text-success" role="status"></div>
        <p class="mt-2">Memuat data penarikan...</p>
      </div>

      <div v-else>
        <table class="table table-hover align-middle shadow-sm">
          <thead class="table-success text-center">
            <tr>
              <th>No</th>
              <th>Nasabah</th>
              <th>Jumlah</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, i) in penarikan" :key="p.id">
              <td>{{ i + 1 }}</td>
              <td>{{ p.user?.nama || '-' }}</td>
              <td>Rp {{ Number(p.jumlah).toLocaleString('id-ID') }}</td>
              <td>{{ new Date(p.tanggal).toLocaleString('id-ID') }}</td>
              <td class="text-center">
                <span
                  class="badge px-3 py-2 text-uppercase"
                  :class="{
                    'bg-warning text-dark': p.status === 'pending',
                    'bg-success': p.status === 'disetujui',
                    'bg-danger': p.status === 'ditolak'
                  }"
                >
                  {{ p.status }}
                </span>
              </td>
              <td class="text-center">
                <button
                  v-if="p.status === 'pending'"
                  class="btn btn-sm btn-success me-2"
                  @click="validasi(p.id, 'disetujui')"
                >
                  <i class="bi bi-check-circle"></i> Setujui
                </button>
                <button
                  v-if="p.status === 'pending'"
                  class="btn btn-sm btn-outline-danger"
                  @click="validasi(p.id, 'ditolak')"
                >
                  <i class="bi bi-x-circle"></i> Tolak
                </button>
              </td>
            </tr>

            <tr v-if="!penarikan.length">
              <td colspan="6" class="text-center text-muted py-4">
                Belum ada permintaan penarikan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const auth = useAuthStore()
const penarikan = ref([])
const loading = ref(true)

const loadPenarikan = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/penarikan', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    penarikan.value = res.data
  } catch (err) {
    alert('Gagal memuat data penarikan')
  } finally {
    loading.value = false
  }
}

const validasi = async (id, status) => {
  if (!confirm(`Yakin ingin ${status} penarikan ini?`)) return

  try {
    await axios.post(
      `/api/penarikan/validasi/${id}`,
      { status },
      { headers: { Authorization: `Bearer ${auth.token}` } }
    )
    alert(`Penarikan berhasil ${status}.`)
    await loadPenarikan()
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memvalidasi penarikan.')
  }
}

onMounted(loadPenarikan)
</script>

<style scoped>
.table td, .table th {
  vertical-align: middle;
}
</style>
