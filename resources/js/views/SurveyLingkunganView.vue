<template>
  <MainLayout>
    <div class="dashboard-wrapper container py-4 animate-fade-in">
      <div class="row align-items-center mb-4">
        <div class="col-12">
          <h2 class="welcome-title text-dark fw-bold mb-1">
            <i class="bi bi-clipboard2-check text-success me-2"></i> Survey Lingkungan
          </h2>
          <p class="text-secondary mb-0">
            Dokumentasi dan penilaian kondisi kebersihan serta lingkungan sekolah.
          </p>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <ul class="nav nav-pills custom-tabs mb-4 gap-2" id="surveyTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="pemilahan-tab" data-bs-toggle="pill" data-bs-target="#pemilahan" type="button" role="tab" @click="currentTab = 'pemilahan_sampah'">
            <i class="bi bi-recycle me-2"></i> Pemilahan Sampah
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="kebersihan-tab" data-bs-toggle="pill" data-bs-target="#kebersihan" type="button" role="tab" @click="currentTab = 'kebersihan_kelas'">
            <i class="bi bi-stars me-2"></i> Kebersihan Kelas
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tanaman-tab" data-bs-toggle="pill" data-bs-target="#tanaman" type="button" role="tab" @click="currentTab = 'perawatan_tanaman'">
            <i class="bi bi-flower1 me-2"></i> Perawatan Tanaman
          </button>
        </li>
      </ul>

      <!-- Form Tambah Survey -->
      <div class="card shadow-sm border-0 mb-4 form-card">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4 text-dark">
            <i class="bi bi-plus-circle-fill text-success me-2"></i> Buat Survey Baru
          </h5>
          <form @submit.prevent="submitSurvey">
            <div class="row g-3">
              <!-- Pilih Kelas/Nasabah -->
              <div class="col-md-6">
                <label class="form-label fw-semibold">Pilih Kelas / Lokasi (Nasabah)</label>
                <select class="form-select form-control-premium" v-model="form.nasabah_id" required>
                  <option value="" disabled>-- Pilih Kelas / Nasabah --</option>
                  <option v-for="n in nasabahList" :key="n.id" :value="n.id">
                    {{ n.nama }} ({{ n.username }})
                  </option>
                </select>
              </div>

              <!-- Status -->
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status Penilaian</label>
                <div class="d-flex gap-3 mt-2" v-if="currentTab === 'pemilahan_sampah'">
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="sesuai" value="sesuai" v-model="form.status" required>
                    <label class="form-check-label badge-success-glow" for="sesuai">
                      <i class="bi bi-check-circle-fill"></i> Sesuai
                    </label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="tidak_sesuai" value="tidak_sesuai" v-model="form.status" required>
                    <label class="form-check-label badge-danger-glow" for="tidak_sesuai">
                      <i class="bi bi-x-circle-fill"></i> Tidak Sesuai
                    </label>
                  </div>
                </div>

                <div class="d-flex gap-3 mt-2" v-if="currentTab === 'kebersihan_kelas'">
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="bersih" value="bersih" v-model="form.status" required>
                    <label class="form-check-label badge-success-glow" for="bersih">
                      <i class="bi bi-check-circle-fill"></i> Bersih
                    </label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="tidak_bersih" value="tidak_bersih" v-model="form.status" required>
                    <label class="form-check-label badge-danger-glow" for="tidak_bersih">
                      <i class="bi bi-x-circle-fill"></i> Tidak Bersih
                    </label>
                  </div>
                </div>

                <div class="d-flex gap-3 mt-2" v-if="currentTab === 'perawatan_tanaman'">
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="disiram" value="disiram" v-model="form.status" required>
                    <label class="form-check-label badge-success-glow" for="disiram">
                      <i class="bi bi-check-circle-fill"></i> Disiram
                    </label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="status" id="tidak_disiram" value="tidak_disiram" v-model="form.status" required>
                    <label class="form-check-label badge-danger-glow" for="tidak_disiram">
                      <i class="bi bi-x-circle-fill"></i> Tidak Disiram
                    </label>
                  </div>
                </div>
              </div>

              <!-- Upload Foto -->
              <div class="col-md-6">
                <label class="form-label fw-semibold">Foto Bukti</label>

                <!-- Pilihan Upload/Kamera -->
                <div v-if="!cameraActive && !imagePreview" class="d-flex gap-2 mb-3">
                  <button type="button" class="btn btn-outline-success flex-grow-1" @click="startCamera">
                    <i class="bi bi-camera-fill me-2"></i> Buka Kamera
                  </button>
                  <button type="button" class="btn btn-outline-secondary flex-grow-1" @click="triggerFileInput">
                    <i class="bi bi-upload me-2"></i> Upload File
                  </button>
                  <input type="file" ref="fileInputRef" class="d-none" accept="image/*" @change="handleFileUpload">
                </div>

                <!-- Tampilan Kamera Live -->
                <div v-show="cameraActive && !imagePreview" class="camera-container rounded shadow-sm overflow-hidden mb-3 position-relative bg-dark">
                  <video ref="videoElement" class="w-100" autoplay playsinline></video>
                  <button type="button" class="btn btn-danger capture-btn position-absolute bottom-0 start-50 translate-middle-x mb-3 rounded-pill px-4 shadow" @click="takePhoto">
                    <i class="bi bi-camera me-2"></i> Ambil Foto
                  </button>
                  <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2" @click="stopCamera">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>

                <!-- Canvas Tersembunyi untuk mengambil gambar -->
                <canvas ref="canvasElement" class="d-none"></canvas>

                <!-- Image Preview -->
                <div v-if="imagePreview" class="mt-3 image-preview-container">
                  <img :src="imagePreview" class="img-fluid rounded shadow-sm w-100" alt="Preview Foto">
                  <button type="button" class="btn btn-sm btn-danger remove-image-btn" @click="removeImage">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </div>
              </div>

              <!-- Keterangan -->
              <div class="col-md-6">
                <label class="form-label fw-semibold">Keterangan Tambahan (Opsional)</label>
                <textarea class="form-control form-control-premium" rows="3" v-model="form.keterangan" placeholder="Tambahkan catatan..."></textarea>
              </div>

              <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-success-premium" :disabled="submitting">
                  <i v-if="submitting" class="spinner-border spinner-border-sm me-2"></i>
                  <i v-else class="bi bi-send-fill me-2"></i>
                  {{ submitting ? 'Menyimpan...' : 'Simpan Survey' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Riwayat Survey Berdasarkan Tab Aktif -->
      <div class="card shadow-sm border-0 main-table-card">
        <div class="card-header-gradient d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon">
              <i class="bi bi-list-check"></i>
            </div>
            <h5 class="mb-0 fw-bold text-white">Riwayat Survey: {{ getActiveTabName() }}</h5>
          </div>
          <span class="badge bg-white-soft text-white">{{ filteredSurvey.length }} Data</span>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-table">
              <thead>
                <tr>
                  <th width="5%">No</th>
                  <th width="15%">Foto</th>
                  <th width="20%">Kelas / Lokasi</th>
                  <th width="15%">Status</th>
                  <th width="25%">Keterangan</th>
                  <th width="10%">Tanggal</th>
                  <th width="10%">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Memuat data survey...</p>
                  </td>
                </tr>
                <tr v-else-if="!filteredSurvey.length">
                  <td colspan="7" class="text-center py-5 text-muted">
                    <div class="empty-state">
                      <i class="bi bi-inbox-fill text-muted display-4"></i>
                      <p class="mt-2 fw-medium mb-0">Belum ada data survey untuk kategori ini.</p>
                    </div>
                  </td>
                </tr>
                <tr v-for="(s, i) in filteredSurvey" :key="s.id" class="table-row-animate">
                  <td class="text-center fw-medium text-secondary">{{ i + 1 }}</td>
                  <td class="text-center">
                    <img :src="`/storage/${s.foto}`" alt="Foto" class="survey-thumbnail" @error="imageError" />
                  </td>
                  <td class="fw-bold text-dark">{{ s.nasabah?.nama || '-' }}</td>
                  <td>
                    <span class="badge px-3 py-2 rounded-pill badge-jenis"
                          :class="isStatusPositive(s.status) ? 'badge-success-glow' : 'badge-danger-glow'">
                      <i :class="isStatusPositive(s.status) ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill'"></i>
                      {{ formatStatus(s.status) }}
                    </span>
                  </td>
                  <td class="text-secondary small">{{ s.keterangan || '-' }}</td>
                  <td class="text-secondary small">{{ formatDate(s.tanggal) }}</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger" @click="hapusSurvey(s.id)" title="Hapus Data">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const auth = useAuthStore()

// State
const currentTab = ref('pemilahan_sampah')
const nasabahList = ref([])
const surveyList = ref([])
const loading = ref(true)
const submitting = ref(false)

// Kamera State
const cameraActive = ref(false)
const videoElement = ref(null)
const canvasElement = ref(null)
const fileInputRef = ref(null)
let stream = null

// Form State
const form = ref({
  nasabah_id: '',
  status: '',
  keterangan: '',
  fotoFile: null
})
const imagePreview = ref(null)

// Load Data
const loadNasabah = async () => {
  try {
    const res = await axios.get('/api/nasabah', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    nasabahList.value = res.data
  } catch (err) {
    console.error('Failed to load nasabah:', err)
  }
}

const loadSurvey = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/survey-lingkungan', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    surveyList.value = res.data
  } catch (err) {
    console.error('Failed to load survey:', err)
  } finally {
    loading.value = false
  }
}

// Computed & Helpers
const filteredSurvey = computed(() => {
  return surveyList.value.filter(s => s.kategori === currentTab.value)
})

const getActiveTabName = () => {
  const names = {
    'pemilahan_sampah': 'Pemilahan Sampah',
    'kebersihan_kelas': 'Kebersihan Kelas',
    'perawatan_tanaman': 'Perawatan Tanaman'
  }
  return names[currentTab.value]
}

const isStatusPositive = (status) => {
  return ['sesuai', 'bersih', 'disiram'].includes(status)
}

const formatStatus = (status) => {
  if (!status) return '-'
  return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

// Watch tab change to reset form status
watch(currentTab, () => {
  form.value.status = ''
})

// File Upload & Camera
const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click()
  }
}

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return
  processFile(file)
}

const processFile = (file) => {
  if (file.size > 5 * 1024 * 1024) { // 5MB
    Swal.fire('File Terlalu Besar', 'Maksimal ukuran foto adalah 5MB', 'warning')
    if (fileInputRef.value) fileInputRef.value.value = ''
    return
  }

  form.value.fotoFile = file
  imagePreview.value = URL.createObjectURL(file)
}

// WebRTC Camera Logic
const startCamera = async () => {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment' }
    })
    cameraActive.value = true
    // Wait for DOM update
    setTimeout(() => {
      if (videoElement.value) {
        videoElement.value.srcObject = stream
      }
    }, 100)
  } catch (err) {
    console.error('Error accessing camera:', err)
    Swal.fire('Gagal!', 'Tidak dapat mengakses kamera. Pastikan browser Anda memberikan izin.', 'error')
  }
}

const takePhoto = () => {
  if (!videoElement.value || !canvasElement.value) return

  const video = videoElement.value
  const canvas = canvasElement.value

  canvas.width = video.videoWidth
  canvas.height = video.videoHeight

  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)

  canvas.toBlob((blob) => {
    if (blob) {
      const file = new File([blob], `survey_${Date.now()}.jpg`, { type: 'image/jpeg' })
      processFile(file)
      stopCamera()
    }
  }, 'image/jpeg', 0.8)
}

const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(track => track.stop())
    stream = null
  }
  cameraActive.value = false
}

const removeImage = () => {
  form.value.fotoFile = null
  imagePreview.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const imageError = (e) => {
  e.target.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="80" height="80"%3E%3Crect width="80" height="80" fill="%23e0e0e0"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-size="10" fill="%23999"%3ENo Image%3C/text%3E%3C/svg%3E'
}

// Submit Form
const submitSurvey = async () => {
  if (!form.value.fotoFile) {
    Swal.fire('Peringatan', 'Silakan upload foto bukti terlebih dahulu!', 'warning')
    return
  }
  if (!form.value.status) {
    Swal.fire('Peringatan', 'Pilih status penilaian!', 'warning')
    return
  }

  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('nasabah_id', form.value.nasabah_id)
    formData.append('kategori', currentTab.value)
    formData.append('status', form.value.status)
    formData.append('foto', form.value.fotoFile)
    if (form.value.keterangan) {
      formData.append('keterangan', form.value.keterangan)
    }

    await axios.post('/api/survey-lingkungan', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${auth.token}`
      }
    })

    Swal.fire({
      title: 'Berhasil!',
      text: 'Data survey telah disimpan.',
      icon: 'success',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000
    })

    // Reset Form
    form.value.nasabah_id = ''
    form.value.status = ''
    form.value.keterangan = ''
    removeImage()

    // Reload list
    await loadSurvey()

  } catch (err) {
    console.error(err)
    Swal.fire('Gagal!', err.response?.data?.message || 'Terjadi kesalahan saat menyimpan survey.', 'error')
  } finally {
    submitting.value = false
  }
}

// Delete Survey
const hapusSurvey = async (id) => {
  const result = await Swal.fire({
    title: 'Hapus Data?',
    text: "Data survey dan foto akan dihapus permanen!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!'
  })

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/survey-lingkungan/${id}`, {
        headers: { Authorization: `Bearer ${auth.token}` }
      })
      Swal.fire({
        title: 'Terhapus!',
        text: 'Data survey telah dihapus.',
        icon: 'success',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
      })
      await loadSurvey()
    } catch (err) {
      Swal.fire('Gagal!', 'Tidak dapat menghapus data.', 'error')
    }
  }
}

onMounted(() => {
  loadNasabah()
  loadSurvey()
})

onUnmounted(() => {
  stopCamera()
})
</script>

<style scoped>
/* Tabs */
.custom-tabs .nav-link {
  color: #4b5563;
  font-weight: 600;
  border-radius: 50px;
  padding: 0.6rem 1.25rem;
  transition: all 0.3s ease;
  background-color: white;
  border: 1px solid #e5e7eb;
}

.custom-tabs .nav-link:hover {
  background-color: #f9fafb;
}

.custom-tabs .nav-link.active {
  background-color: #10b981;
  color: white;
  border-color: #10b981;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}

/* Animations */
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Forms */
.form-card {
  border-radius: 20px;
}

.form-control-premium {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.form-control-premium:focus {
  background: white;
  border-color: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
  outline: none;
}

/* Radio buttons styled as badges */
.custom-radio {
  padding-left: 0;
}

.custom-radio .form-check-input {
  display: none;
}

.custom-radio .form-check-label {
  cursor: pointer;
  padding: 0.5rem 1.25rem;
  border-radius: 50px;
  font-weight: 600;
  border: 2px solid transparent;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  opacity: 0.6;
  filter: grayscale(100%);
}

.custom-radio .form-check-input:checked + .form-check-label {
  opacity: 1;
  filter: grayscale(0%);
  transform: scale(1.05);
}

.custom-radio .form-check-input:checked + .form-check-label.badge-success-glow {
  border-color: #10b981;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

.custom-radio .form-check-input:checked + .form-check-label.badge-danger-glow {
  border-color: #ef4444;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
}

/* Image Preview & Camera */
.image-preview-container, .camera-container {
  position: relative;
  max-width: 100%;
}

.camera-container video {
  display: block;
  max-height: 400px;
  object-fit: cover;
}

.capture-btn {
  transition: transform 0.2s;
}
.capture-btn:active {
  transform: scale(0.95) translateX(-50%) !important;
}

.remove-image-btn {
  position: absolute;
  top: -10px;
  right: -10px;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

/* Tables */
.main-table-card {
  border-radius: 20px;
  overflow: hidden;
}

.card-header-gradient {
  background: linear-gradient(135deg, #1f2937, #111827);
  padding: 1.25rem 1.75rem;
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
}

.custom-table th {
  background: #f9fafb;
  color: #4b5563;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem;
}

.custom-table td {
  padding: 1rem;
}

.table-row-animate {
  transition: background-color 0.2s ease;
}

.table-row-animate:hover {
  background-color: #f9fafb;
}

.survey-thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid #e5e7eb;
}

/* Badges */
.badge-jenis {
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.badge-success-glow {
  background: rgba(16, 185, 129, 0.1);
  color: #047857;
}

.badge-danger-glow {
  background: rgba(239, 68, 68, 0.1);
  color: #b91c1c;
}

/* Button */
.btn-success-premium {
  background: #10b981;
  color: white;
  border: none;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.btn-success-premium:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
}

.btn-success-premium:disabled {
  opacity: 0.7;
}
</style>
