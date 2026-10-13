<template>
  <MainLayout>
    <div class="admin-ranking-deck">
      <!-- HEADER ROW -->
      <section class="deck-hero-row">
        <div class="deck-intro">
          <div class="intro-badge">
            <span class="pulse-live-indicator"></span>
            <span>Klasemen & Peringkat Nasabah</span>
          </div>
          <h2 class="deck-greeting">
            Papan Peringkat & Juara Lingkungan
          </h2>
          <p class="deck-subtext">
            Pantau nasabah paling aktif mengumpulkan poin sampah dan poin lingkungan di Bank Samsul.
          </p>
        </div>

        <div class="deck-quick-actions">
          <button class="btn-refresh" @click="fetchLeaderboard" :disabled="loading">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            <span>Segarkan Klasemen</span>
          </button>
        </div>
      </section>

      <!-- LOADING & ERROR -->
      <div v-if="loading" class="ranking-loading">
        <div class="ranking-spinner"></div>
        <p>Memuat data klasemen nasabah...</p>
      </div>

      <div v-else-if="error" class="ranking-alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ error }}</span>
      </div>

      <!-- MAIN CONTENT -->
      <div v-else class="ranking-body">
        <!-- 1. SUMMARY STATS (4 METRIC CARDS) -->
        <section class="ranking-stats-grid">
          <div class="stat-card">
            <div class="stat-top">
              <span class="stat-label">Total Nasabah</span>
              <div class="stat-icon-wrap icon-users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
            </div>
            <div class="stat-value">{{ formatNum(summary.total_nasabah) }} <span class="stat-unit">Siswa</span></div>
            <span class="stat-chip chip-blue">Siswa Aktif</span>
          </div>

          <div class="stat-card">
            <div class="stat-top">
              <span class="stat-label">Poin Tertinggi</span>
              <div class="stat-icon-wrap icon-crown">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
            </div>
            <div class="stat-value">{{ formatNum(summary.poin_tertinggi) }} <span class="stat-unit">Poin</span></div>
            <span class="stat-chip chip-gold">Pemuncak Klasemen</span>
          </div>

          <div class="stat-card">
            <div class="stat-top">
              <span class="stat-label">Rata-Rata Poin</span>
              <div class="stat-icon-wrap icon-avg">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              </div>
            </div>
            <div class="stat-value">{{ formatNum(summary.rata_rata_poin) }} <span class="stat-unit">Poin/Siswa</span></div>
            <span class="stat-chip chip-green">Pencapaian Rata-Rata</span>
          </div>

          <div class="stat-card">
            <div class="stat-top">
              <span class="stat-label">Total Sampah Terkumpul</span>
              <div class="stat-icon-wrap icon-scale">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/></svg>
              </div>
            </div>
            <div class="stat-value">{{ summary.total_sampah_kg }} <span class="stat-unit">Kg</span></div>
            <span class="stat-chip chip-emerald">Setoran Terverifikasi</span>
          </div>
        </section>

        <!-- 2. PODIUM TOP 3 SHOWCASE -->
        <section class="podium-section" v-if="podium.length >= 2">
          <div class="podium-deck">
            <!-- JUARA 2 (PERAK) -->
            <div class="podium-card podium-silver" v-if="podium[1]">
              <div class="podium-badge">🥈 Juara 2</div>
              <div class="podium-avatar silver-avatar">{{ podium[1].nama.charAt(0).toUpperCase() }}</div>
              <h3 class="podium-name">{{ podium[1].nama }}</h3>
              <span class="podium-username">@{{ podium[1].username }}</span>
              <div class="podium-points">{{ formatNum(podium[1].total_poin) }} <span class="unit">Poin</span></div>
              <div class="podium-subinfo">
                <span>♻ {{ formatNum(podium[1].poin_sampah) }} Sampah</span>
                <span>🌱 {{ formatNum(podium[1].poin_lingkungan) }} Lingk.</span>
              </div>
            </div>

            <!-- JUARA 1 (EMAS) -->
            <div class="podium-card podium-gold" v-if="podium[0]">
              <div class="gold-crown">👑</div>
              <div class="podium-badge gold-badge">🥇 Juara 1</div>
              <div class="podium-avatar gold-avatar">{{ podium[0].nama.charAt(0).toUpperCase() }}</div>
              <h3 class="podium-name gold-name">{{ podium[0].nama }}</h3>
              <span class="podium-username">@{{ podium[0].username }}</span>
              <div class="podium-points gold-points">{{ formatNum(podium[0].total_poin) }} <span class="unit">Poin</span></div>
              <div class="podium-subinfo">
                <span>♻ {{ formatNum(podium[0].poin_sampah) }} Sampah</span>
                <span>🌱 {{ formatNum(podium[0].poin_lingkungan) }} Lingk.</span>
              </div>
            </div>

            <!-- JUARA 3 (PERUNGGU) -->
            <div class="podium-card podium-bronze" v-if="podium[2]">
              <div class="podium-badge">🥉 Juara 3</div>
              <div class="podium-avatar bronze-avatar">{{ podium[2].nama.charAt(0).toUpperCase() }}</div>
              <h3 class="podium-name">{{ podium[2].nama }}</h3>
              <span class="podium-username">@{{ podium[2].username }}</span>
              <div class="podium-points">{{ formatNum(podium[2].total_poin) }} <span class="unit">Poin</span></div>
              <div class="podium-subinfo">
                <span>♻ {{ formatNum(podium[2].poin_sampah) }} Sampah</span>
                <span>🌱 {{ formatNum(podium[2].poin_lingkungan) }} Lingk.</span>
              </div>
            </div>
          </div>
        </section>

        <!-- 3. FULL LEADERBOARD TABLE -->
        <section class="table-card">
          <div class="table-header-row">
            <div class="table-title-box">
              <div class="table-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
              </div>
              <div>
                <h3 class="table-title">Daftar Lengkap Peringkat Siswa</h3>
                <p class="table-sub">Urutan dinilai otomatis berdasarkan akumulasi total poin dan volume setoran sampah.</p>
              </div>
            </div>

            <div class="table-search-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari siswa atau username..."
                class="search-input"
              />
            </div>
          </div>

          <!-- Desktop Table -->
          <div class="table-responsive">
            <table class="ranking-table">
              <thead>
                <tr>
                  <th width="8%" class="col-center">Peringkat</th>
                  <th width="30%">Nama Nasabah</th>
                  <th width="15%" class="col-right">Poin Sampah</th>
                  <th width="15%" class="col-right">Poin Lingkungan</th>
                  <th width="16%" class="col-right">Total Poin</th>
                  <th width="16%" class="col-right">Total Berat</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in filteredLeaderboard"
                  :key="item.id"
                  :class="{ 'row-champion': item.rank <= 3 }"
                >
                  <td class="col-center">
                    <span class="rank-badge" :class="'rank-' + item.rank">
                      <span v-if="item.rank === 1">🥇 1</span>
                      <span v-else-if="item.rank === 2">🥈 2</span>
                      <span v-else-if="item.rank === 3">🥉 3</span>
                      <span v-else>#{{ item.rank }}</span>
                    </span>
                  </td>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar" :class="'avatar-rank-' + item.rank">
                        {{ item.nama.charAt(0).toUpperCase() }}
                      </div>
                      <div class="user-details">
                        <span class="user-name">{{ item.nama }}</span>
                        <span class="user-username">@{{ item.username }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="col-right font-mono">{{ formatNum(item.poin_sampah) }}</td>
                  <td class="col-right font-mono">{{ formatNum(item.poin_lingkungan) }}</td>
                  <td class="col-right">
                    <span class="total-poin-pill font-mono">
                      {{ formatNum(item.total_poin) }} Poin
                    </span>
                  </td>
                  <td class="col-right font-mono text-muted">{{ item.total_berat }} kg</td>
                </tr>
                <tr v-if="!filteredLeaderboard.length">
                  <td colspan="6" class="empty-row">
                    <span>Tidak ada nasabah yang cocok dengan pencarian.</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards -->
          <div class="mobile-ranking-list">
            <div
              v-for="item in filteredLeaderboard"
              :key="'m-' + item.id"
              class="m-rank-card"
              :class="{ 'card-champion': item.rank <= 3 }"
            >
              <div class="m-card-top">
                <span class="rank-badge" :class="'rank-' + item.rank">
                  <span v-if="item.rank === 1">🥇 #1</span>
                  <span v-else-if="item.rank === 2">🥈 #2</span>
                  <span v-else-if="item.rank === 3">🥉 #3</span>
                  <span v-else>#{{ item.rank }}</span>
                </span>
                <span class="total-poin-pill font-mono">{{ formatNum(item.total_poin) }} Poin</span>
              </div>
              <div class="m-card-user">
                <div class="user-avatar" :class="'avatar-rank-' + item.rank">
                  {{ item.nama.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h4 class="m-user-name">{{ item.nama }}</h4>
                  <span class="m-user-sub">@{{ item.username }}</span>
                </div>
              </div>
              <div class="m-card-footer">
                <span>Sampah: {{ formatNum(item.poin_sampah) }}</span>
                <span>Lingk.: {{ formatNum(item.poin_lingkungan) }}</span>
                <span>Berat: {{ item.total_berat }} kg</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/useAuthStore'
import MainLayout from '../layouts/MainLayout.vue'

const auth = useAuthStore()
const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')

const summary = ref({
  total_nasabah: 0,
  poin_tertinggi: 0,
  rata_rata_poin: 0,
  total_sampah_kg: 0,
})

const podium = ref([])
const leaderboard = ref([])

const formatNum = (num) => Number(num || 0).toLocaleString('id-ID')

const fetchLeaderboard = async () => {
  loading.value = true
  error.value = null
  const token = auth.token || localStorage.getItem('token')

  try {
    const res = await axios.get('/api/admin/peringkat', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    })
    const d = res.data || {}
    summary.value = d.summary || {}
    podium.value = Array.isArray(d.podium) ? d.podium : []
    leaderboard.value = Array.isArray(d.leaderboard) ? d.leaderboard : []
    loading.value = false
  } catch (err) {
    console.error('Fetch leaderboard error', err)
    error.value = err.response?.data?.message || 'Gagal memuat data klasemen nasabah.'
    loading.value = false
  }
}

const filteredLeaderboard = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return leaderboard.value
  return leaderboard.value.filter(
    (item) =>
      (item.nama || '').toLowerCase().includes(q) ||
      (item.username || '').toLowerCase().includes(q)
  )
})

onMounted(fetchLeaderboard)
</script>

<style scoped>
/* ==========================================================================
   ADMIN RANKING DECK - COMPACT PRO SAAS (CLEAN, SLATE & ACCENTED)
   ========================================================================== */
.admin-ranking-deck {
  --navy: #0f172a;
  --navy-light: #1e293b;
  --emerald: #059669;
  --gold: #d97706;
  --silver: #64748b;
  --bronze: #b45309;
  --muted: #64748b;
  --line: #e2e8f0;
  --card-bg: #ffffff;
  --radius: 10px;
  --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);

  max-width: 1280px;
  margin: 0 auto;
}

/* 1. Header Row */
.deck-hero-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.intro-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #fef3c7;
  border: 1px solid #fde68a;
  padding: 2px 7px;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  color: #92400e;
  margin-bottom: 3px;
}

.pulse-live-indicator {
  width: 5px;
  height: 5px;
  background: #f59e0b;
  border-radius: 50%;
}

.deck-greeting {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.02em;
  margin: 0 0 2px;
  line-height: 1.2;
}

.deck-subtext {
  font-size: 0.8rem;
  color: var(--muted);
  margin: 0;
}

.btn-refresh {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff;
  color: var(--navy);
  border: 1px solid var(--line);
  padding: 7px 12px;
  border-radius: 7px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
}

.btn-refresh svg {
  width: 13px;
  height: 13px;
}

.btn-refresh:hover {
  background: #f1f5f9;
}

/* 2. Loading & Error */
.ranking-loading {
  text-align: center;
  padding: 40px 16px;
  color: var(--muted);
}

.ranking-spinner {
  width: 28px;
  height: 28px;
  border: 2px solid var(--line);
  border-top-color: var(--navy);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.ranking-alert-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fee2e2;
  border: 1px solid #fca5a5;
  color: #991b1b;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
}

/* 3. Summary Stats Grid */
.ranking-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 14px;
}

.stat-card {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 12px 14px;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
}

.stat-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.stat-icon-wrap {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon-wrap svg {
  width: 14px;
  height: 14px;
}

.icon-users { background: #eff6ff; color: #2563eb; }
.icon-crown { background: #fef3c7; color: #d97706; }
.icon-avg { background: #ecfdf5; color: #059669; }
.icon-scale { background: #f1f5f9; color: var(--navy); }

.stat-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.02em;
  line-height: 1.1;
  margin: 4px 0 6px;
}

.stat-unit {
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--muted);
}

.stat-chip {
  display: inline-flex;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.66rem;
  font-weight: 600;
  width: fit-content;
}

.chip-blue { background: #eff6ff; color: #1d4ed8; }
.chip-gold { background: #fef3c7; color: #b45309; }
.chip-green { background: #ecfdf5; color: #065f46; }
.chip-emerald { background: #f1f5f9; color: #334155; }

/* 4. Podium Showcase */
.podium-section {
  margin-bottom: 14px;
}

.podium-deck {
  display: grid;
  grid-template-columns: 1fr 1.1fr 1fr;
  gap: 12px;
  align-items: flex-end;
}

.podium-card {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 16px 14px;
  text-align: center;
  position: relative;
  box-shadow: var(--shadow-card);
  transition: transform 0.18s ease;
}

.podium-card:hover {
  transform: translateY(-2px);
}

.podium-gold {
  background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
  border-color: #fde68a;
  box-shadow: 0 4px 16px -2px rgba(217, 119, 6, 0.12);
  padding-top: 22px;
}

.gold-crown {
  font-size: 1.5rem;
  position: absolute;
  top: -14px;
  left: 50%;
  transform: translateX(-50%);
}

.podium-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  background: #f1f5f9;
  color: var(--navy);
  margin-bottom: 8px;
}

.gold-badge {
  background: #fef3c7;
  color: #92400e;
}

.podium-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  margin: 0 auto 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.1rem;
}

.gold-avatar {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
  border: 3px solid #fef3c7;
}

.silver-avatar {
  background: #e2e8f0;
  color: #334155;
  border: 2px solid #cbd5e1;
}

.bronze-avatar {
  background: #fed7aa;
  color: #7c2d12;
  border: 2px solid #fdba74;
}

.podium-name {
  font-size: 0.92rem;
  font-weight: 800;
  color: var(--navy);
  margin: 0 0 1px;
}

.gold-name {
  font-size: 1.05rem;
  color: #78350f;
}

.podium-username {
  font-size: 0.72rem;
  color: var(--muted);
  display: block;
  margin-bottom: 8px;
}

.podium-points {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--navy);
  letter-spacing: -0.02em;
}

.gold-points {
  font-size: 1.45rem;
  color: #b45309;
}

.podium-points .unit {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--muted);
}

.podium-subinfo {
  display: flex;
  justify-content: center;
  gap: 8px;
  font-size: 0.7rem;
  color: var(--muted);
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px dashed var(--line);
}

/* 5. Full Leaderboard Table */
.table-card {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  padding: 13px 15px;
  box-shadow: var(--shadow-card);
}

.table-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.table-title-box {
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-icon-wrap {
  width: 26px;
  height: 26px;
  background: #fef3c7;
  color: #d97706;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.table-icon-wrap svg {
  width: 15px;
  height: 15px;
}

.table-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--navy);
  margin: 0 0 1px;
}

.table-sub {
  font-size: 0.72rem;
  color: var(--muted);
  margin: 0;
}

.table-search-box {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f8fafc;
  border: 1px solid var(--line);
  border-radius: 6px;
  padding: 4px 10px;
  width: 240px;
}

.table-search-box svg {
  width: 14px;
  height: 14px;
  color: var(--muted);
}

.search-input {
  border: none;
  background: transparent;
  font-size: 0.76rem;
  outline: none;
  width: 100%;
  color: var(--navy);
}

.table-responsive {
  overflow-x: auto;
}

.ranking-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.78rem;
}

.ranking-table th {
  background: #f8fafc;
  color: var(--muted);
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 7px 10px;
  border-bottom: 1px solid var(--line);
}

.ranking-table td {
  padding: 8px 10px;
  border-bottom: 1px solid var(--line);
  color: var(--navy);
}

.row-champion {
  background: rgba(254, 243, 199, 0.15);
}

.col-center { text-align: center; }
.col-right { text-align: right; }
.font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

.rank-badge {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.74rem;
  font-weight: 700;
}

.rank-1 { background: #fef3c7; color: #b45309; font-weight: 800; }
.rank-2 { background: #e2e8f0; color: #334155; }
.rank-3 { background: #fed7aa; color: #9a3412; }

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-avatar {
  width: 26px;
  height: 26px;
  border-radius: 4px;
  background: #e2e8f0;
  color: var(--navy);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.74rem;
  font-weight: 800;
}

.avatar-rank-1 { background: #f59e0b; color: #fff; }
.avatar-rank-2 { background: #94a3b8; color: #fff; }
.avatar-rank-3 { background: #d97706; color: #fff; }

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 700;
  color: var(--navy);
  line-height: 1.15;
}

.user-username {
  font-size: 0.68rem;
  color: var(--muted);
}

.total-poin-pill {
  display: inline-block;
  background: #ecfdf5;
  color: #047857;
  font-weight: 800;
  font-size: 0.8rem;
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid #a7f3d0;
}

.empty-row {
  text-align: center;
  padding: 24px;
  color: var(--muted);
}

/* Mobile cards */
.mobile-ranking-list { display: none; }

@media (max-width: 991px) {
  .ranking-stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .deck-hero-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .ranking-stats-grid {
    grid-template-columns: 1fr;
  }
  .podium-deck {
    grid-template-columns: 1fr;
  }
  .table-responsive {
    display: none;
  }
  .mobile-ranking-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .m-rank-card {
    background: #f8fafc;
    border: 1px solid var(--line);
    border-radius: 6px;
    padding: 8px 10px;
  }
  .card-champion {
    background: #fffbeb;
    border-color: #fde68a;
  }
  .m-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }
  .m-card-user {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
  }
  .m-user-name {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--navy);
    margin: 0;
  }
  .m-user-sub {
    font-size: 0.68rem;
    color: var(--muted);
  }
  .m-card-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.68rem;
    color: var(--muted);
    border-top: 1px solid var(--line);
    padding-top: 4px;
  }
}
</style>
