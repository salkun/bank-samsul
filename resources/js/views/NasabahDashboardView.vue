<template>
  <div class="nasabah-app">
    <!-- TOP NAVIGATION BAR -->
    <header class="app-nav">
      <div class="nav-inner">
        <div class="nav-brand">
          <div class="brand-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
          <div class="brand-text">
            <span class="brand-name">BankSamsul</span>
            <span class="brand-badge">Portal Nasabah</span>
          </div>
        </div>

        <div class="nav-actions">
          <!-- Balance Pill -->
          <div class="nav-balance" :title="'Saldo Poin Sampah: ' + formatNum(auth.user?.poin_sampah ?? 0)">
            <span class="balance-icon">♻</span>
            <span class="balance-value">{{ formatNum(auth.user?.poin_sampah ?? 0) }}</span>
            <span class="balance-label">Poin</span>
          </div>

          <!-- User Profile Chip -->
          <div class="nav-user">
            <div class="user-avatar">{{ userInitial }}</div>
            <div class="user-meta">
              <span class="user-name">{{ auth.user?.nama || auth.user?.username || 'Siswa' }}</span>
              <span class="user-role">{{ SEKOLAH }}</span>
            </div>
          </div>

          <!-- Logout Button -->
          <button class="btn-logout" @click="handleLogout" title="Keluar dari akun">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="logout-text">Keluar</span>
          </button>
        </div>
      </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="app-main">
      <!-- HERO BENTO GRID -->
      <section class="hero-bento">
        <!-- Main Card: Poin Sampah & Action -->
        <div class="bento-card bento-primary">
          <div class="bento-glow" aria-hidden="true"></div>
          
          <div class="primary-head">
            <span class="primary-tag">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              Saldo Poin Sampah Aktif
            </span>
            <span v-if="pendingPenukaranCount > 0" class="pending-chip" @click="selectTab('penukaran')">
              <span class="pulse-dot"></span>
              {{ pendingPenukaranCount }} pesanan menunggu
            </span>
          </div>

          <div class="primary-balance">
            <div class="balance-digits">{{ formatNum(auth.user?.poin_sampah ?? 0) }}</div>
            <div class="balance-unit">POIN SAMPAH</div>
          </div>

          <p class="primary-desc">
            Kumpulkan poin dari setoran sampah terpilahmu dan tukarkan dengan berbagai hadiah menarik di katalog sekolah.
          </p>

          <div class="primary-footer">
            <button class="btn-primary-action" @click="selectTab('katalog')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
              <span>Tukar Hadiah Sekarang</span>
            </button>
            <button class="btn-secondary-action" @click="selectTab('setoran')">
              <span>Lihat Riwayat Setoran</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <!-- Side Card 1: Poin Lingkungan -->
        <div class="bento-card bento-eco" @click="selectTab('survey')">
          <div class="eco-head">
            <div class="eco-icon">🌱</div>
            <span class="card-label">Poin Lingkungan (Kelas)</span>
          </div>
          <div class="eco-value">
            <span class="value-digits">{{ formatNum(auth.user?.poin_lingkungan ?? 0) }}</span>
            <span class="value-unit">Poin</span>
          </div>
          <div class="eco-badge">
            <span class="star-icon">★</span>
            <span>Penilaian kebersihan & pemilahan kelas</span>
          </div>
          <div class="card-action-link">
            <span>Lihat Survey Lingkungan</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </div>

        <!-- Side Card 2: Total Akumulasi & Setoran -->
        <div class="bento-card bento-stat" @click="selectTab('mutasi')">
          <div class="stat-head">
            <div class="stat-icon">📊</div>
            <span class="card-label">Total Akumulasi & Aktivitas</span>
          </div>
          <div class="stat-value">
            <span class="value-digits">{{ totalPoinDisplay }}</span>
            <span class="value-unit">Total Poin</span>
          </div>
          <div class="stat-meta">
            <span class="stat-chip">{{ riwayatSetoran.length }}x Setoran</span>
            <span class="stat-chip">{{ totalBeratSetoran }} kg Sampah</span>
          </div>
          <div class="card-action-link">
            <span>Lihat Mutasi Poin</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </div>
      </section>

      <!-- INTERACTIVE RIVALRY POSITION CARD -->
      <section class="ranking-rivalry-bar" @click="selectTab('peringkat')" title="Klik untuk membuka klasemen lengkap">
        <div class="rivalry-left">
          <div class="rank-badge-box">
            <span class="trophy-emoji">🏆</span>
            <div>
              <span class="rivalry-kicker">Peringkat Kamu Saat Ini</span>
              <div class="rank-highlight">
                <span class="rank-num">#{{ myRankInfo.my_rank || '-' }}</span>
                <span class="rank-out-of">dari {{ myRankInfo.total_nasabah || 0 }} Siswa</span>
              </div>
            </div>
          </div>
        </div>

        <div class="rivalry-center">
          <!-- Rival Di Atas (Sebelumnya siapa) -->
          <div class="rival-slot rival-above" v-if="myRankInfo.sebelum">
            <span class="rival-tag tag-above">▲ Peringkat #{{ myRankInfo.sebelum.rank }} (Di Atas Kamu)</span>
            <span class="rival-name">{{ myRankInfo.sebelum.nama }}</span>
            <span class="rival-gap">Butuh <strong>+{{ formatNum(myRankInfo.sebelum.selisih_poin) }} Poin</strong> untuk menyalip</span>
          </div>
          <div class="rival-slot rival-leader" v-else>
            <span class="rival-tag tag-leader">👑 Peringkat #1 (Juara Puncak)</span>
            <span class="rival-name">Kamu Memimpin Klasemen!</span>
            <span class="rival-gap">Luar biasa! Pertahankan posisi pertamamu.</span>
          </div>

          <div class="rival-divider-icon" aria-hidden="true">⚔</div>

          <!-- Rival Di Bawah (Sesudahnya siapa) -->
          <div class="rival-slot rival-below" v-if="myRankInfo.sesudah">
            <span class="rival-tag tag-below">▼ Peringkat #{{ myRankInfo.sesudah.rank }} (Di Bawah Kamu)</span>
            <span class="rival-name">{{ myRankInfo.sesudah.nama }}</span>
            <span class="rival-gap">Jarak aman: <strong>{{ formatNum(myRankInfo.sesudah.selisih_poin) }} Poin</strong></span>
          </div>
          <div class="rival-slot rival-bottom" v-else>
            <span class="rival-tag tag-bottom">🌱 Posisi Menanjak</span>
            <span class="rival-name">Ayo kejar teman-temanmu!</span>
            <span class="rival-gap">Setorkan sampah terpilah untuk naik peringkat.</span>
          </div>
        </div>

        <div class="rivalry-right">
          <button class="btn-open-rank-tab" @click.stop="selectTab('peringkat')">
            <span>Buka Klasemen</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
      </section>

      <!-- MOBILE QUICK FEATURE TILES (Thumb-Friendly for Mobile) -->
      <section class="mobile-features-grid">
        <button
          class="m-feat-btn"
          :class="{ active: activeTab === 'katalog' }"
          @click="selectTab('katalog')"
        >
          <div class="m-feat-icon-bubble bubble-katalog">🎁</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Katalog Hadiah</span>
            <span class="m-feat-sub">{{ katalogBarang.length }} Hadiah Aktif</span>
          </div>
          <span class="m-feat-indicator" v-if="activeTab === 'katalog'">●</span>
        </button>

        <button
          class="m-feat-btn"
          :class="{ active: activeTab === 'peringkat' }"
          @click="selectTab('peringkat')"
        >
          <div class="m-feat-icon-bubble bubble-rank">🏆</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Peringkat Saya</span>
            <span class="m-feat-sub">Posisi #{{ myRankInfo.my_rank || '-' }} dari {{ myRankInfo.total_nasabah || 0 }}</span>
          </div>
          <span class="m-feat-indicator" v-if="activeTab === 'peringkat'">●</span>
        </button>

        <button
          class="m-feat-btn"
          :class="{ active: activeTab === 'penukaran' }"
          @click="selectTab('penukaran')"
        >
          <div class="m-feat-icon-bubble bubble-penukaran">⏳</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Status Pesanan</span>
            <span class="m-feat-sub" :class="{ 'text-alert': pendingPenukaranCount > 0 }">
              {{ pendingPenukaranCount > 0 ? `${pendingPenukaranCount} Menunggu` : `${riwayatPenukaran.length} Riwayat` }}
            </span>
          </div>
          <span v-if="pendingPenukaranCount > 0" class="m-feat-alert-dot"></span>
        </button>

        <button
          class="m-feat-btn"
          :class="{ active: activeTab === 'setoran' }"
          @click="selectTab('setoran')"
        >
          <div class="m-feat-icon-bubble bubble-setoran">📦</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Riwayat Setor</span>
            <span class="m-feat-sub">{{ riwayatSetoran.length }}x Penyetoran</span>
          </div>
          <span class="m-feat-indicator" v-if="activeTab === 'setoran'">●</span>
        </button>

        <button
          class="m-feat-btn"
          :class="{ active: activeTab === 'mutasi' }"
          @click="selectTab('mutasi')"
        >
          <div class="m-feat-icon-bubble bubble-mutasi">📈</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Mutasi Poin</span>
            <span class="m-feat-sub">{{ riwayatMutasi.length }} Catatan</span>
          </div>
          <span class="m-feat-indicator" v-if="activeTab === 'mutasi'">●</span>
        </button>

        <button
          class="m-feat-btn m-feat-wide"
          :class="{ active: activeTab === 'survey' }"
          @click="selectTab('survey')"
        >
          <div class="m-feat-icon-bubble bubble-survey">🌿</div>
          <div class="m-feat-texts">
            <span class="m-feat-title">Survey & Evaluasi Lingkungan</span>
            <span class="m-feat-sub">{{ riwayatSurvey.length }} Hasil Inspeksi Kelas</span>
          </div>
          <span class="m-feat-indicator" v-if="activeTab === 'survey'">●</span>
        </button>
      </section>

      <!-- DESKTOP NAVIGATION TABS -->
      <nav class="tabs-nav desktop-only-tabs" aria-label="Menu navigasi">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'katalog' }"
          @click="selectTab('katalog')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
          <span>Katalog Hadiah</span>
          <span class="tab-badge">{{ katalogBarang.length }}</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'peringkat' }"
          @click="selectTab('peringkat')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 22V12M14 22V12M6 4h12v7a6 6 0 0 1-12 0V4z"/></svg>
          <span>Peringkat & Rivalitas</span>
          <span class="tab-badge badge-rank">#{{ myRankInfo.my_rank || '-' }}</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'penukaran' }"
          @click="selectTab('penukaran')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span>Status Penukaran</span>
          <span v-if="pendingPenukaranCount > 0" class="tab-badge badge-alert">{{ pendingPenukaranCount }}</span>
          <span v-else class="tab-badge">{{ riwayatPenukaran.length }}</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'setoran' }"
          @click="selectTab('setoran')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/></svg>
          <span>Riwayat Setoran</span>
          <span class="tab-badge">{{ riwayatSetoran.length }}</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'mutasi' }"
          @click="selectTab('mutasi')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span>Mutasi Poin</span>
          <span class="tab-badge">{{ riwayatMutasi.length }}</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'survey' }"
          @click="selectTab('survey')"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          <span>Survey Lingkungan</span>
          <span class="tab-badge">{{ riwayatSurvey.length }}</span>
        </button>
      </nav>

      <!-- CONTENT PANELS -->
      <div class="content-panel" id="panel-content-area">
        <!-- Current Active Tab Header Banner on Mobile -->
        <div class="mobile-section-indicator">
          <span class="indicator-tag">Fitur Sedang Dibuka</span>
          <h2 class="indicator-title">{{ currentTabTitle }}</h2>
        </div>

        <!-- LOADING STATE -->
        <div v-if="loading" class="state-box">
          <div class="spinner"></div>
          <p>Memuat data portal nasabah...</p>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: KATALOG HADIAH                         -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'katalog'" class="tab-content">
          <!-- Filter & Search Toolbar -->
          <div class="toolbar">
            <div class="search-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input
                v-model="katalogSearch"
                type="text"
                placeholder="Cari hadiah (buku, tumbler, tempat pensil...)"
              />
              <button v-if="katalogSearch" class="btn-clear" @click="katalogSearch = ''">✕</button>
            </div>

            <div class="filter-group">
              <button
                class="filter-pill"
                :class="{ active: filterKatalog === 'all' }"
                @click="filterKatalog = 'all'"
              >
                Semua ({{ katalogBarang.length }})
              </button>
              <button
                class="filter-pill"
                :class="{ active: filterKatalog === 'affordable' }"
                @click="filterKatalog = 'affordable'"
              >
                Poin Cukup ({{ affordableCount }})
              </button>
              <button
                class="filter-pill"
                :class="{ active: filterKatalog === 'in_stock' }"
                @click="filterKatalog = 'in_stock'"
              >
                Tersedia Saja
              </button>
            </div>
          </div>

          <!-- Empty Catalog -->
          <div v-if="!filteredKatalog.length" class="empty-state">
            <div class="empty-icon">🎁</div>
            <h3>Tidak Ada Hadiah yang Cocok</h3>
            <p v-if="katalogSearch">Tidak ditemukan barang dengan kata kunci "{{ katalogSearch }}".</p>
            <p v-else>Belum ada hadiah aktif dalam katalog penukaran saat ini.</p>
            <button v-if="katalogSearch || filterKatalog !== 'all'" class="btn-reset" @click="katalogSearch = ''; filterKatalog = 'all'">
              Tampilkan Semua Hadiah
            </button>
          </div>

          <!-- Catalog Grid -->
          <div v-else class="catalog-grid">
            <div
              v-for="b in filteredKatalog"
              :key="b.id"
              class="catalog-card"
              :class="{ 'card-disabled': b.stok <= 0 }"
            >
              <div class="card-media">
                <img
                  v-if="b.foto"
                  :src="fotoUrl(b.foto)"
                  :alt="b.nama"
                  class="card-img"
                  loading="lazy"
                  @error="imageError"
                />
                <div v-else class="card-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                </div>

                <span class="stock-pill" :class="b.stok > 0 ? 'stock-ok' : 'stock-empty'">
                  {{ b.stok > 0 ? `Sisa ${b.stok}` : 'Stok Habis' }}
                </span>
              </div>

              <div class="card-info">
                <h3 class="card-title">{{ b.nama }}</h3>
                <p class="card-desc">{{ b.deskripsi || 'Hadiah menarik untuk nasabah berprestasi dalam memilah sampah.' }}</p>

                <div class="card-price-row">
                  <div class="price-badge">
                    <span class="price-num">{{ formatNum(b.harga_poin) }}</span>
                    <span class="price-txt">Poin Sampah</span>
                  </div>

                  <span v-if="canAfford(b)" class="afford-tag afford-ok">✓ Poin Cukup</span>
                  <span v-else class="afford-tag afford-no">Kurang {{ formatNum(b.harga_poin - (auth.user?.poin_sampah ?? 0)) }}</span>
                </div>

                <button
                  class="btn-redeem"
                  :disabled="b.stok <= 0 || !canAfford(b)"
                  @click="openModalRedeem(b)"
                >
                  <span v-if="b.stok <= 0">Stok Habis</span>
                  <span v-else-if="!canAfford(b)">Poin Belum Cukup</span>
                  <span v-else>Tukar Hadiah Ini</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: STATUS PENUKARAN                       -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'penukaran'" class="tab-content">
          <div class="section-header">
            <div>
              <h2 class="section-title">Status Penukaran Hadiah</h2>
              <p class="section-subtitle">Pantau permohonan penukaran barang yang telah kamu ajukan.</p>
            </div>
            
            <div class="status-filter-pills">
              <button
                class="pill-filter-sm"
                :class="{ active: filterStatusPenukaran === 'all' }"
                @click="filterStatusPenukaran = 'all'"
              >
                Semua ({{ riwayatPenukaran.length }})
              </button>
              <button
                class="pill-filter-sm"
                :class="{ active: filterStatusPenukaran === 'menunggu' }"
                @click="filterStatusPenukaran = 'menunggu'"
              >
                Menunggu ({{ countStatusPenukaran('menunggu') }})
              </button>
              <button
                class="pill-filter-sm"
                :class="{ active: filterStatusPenukaran === 'disetujui' }"
                @click="filterStatusPenukaran = 'disetujui'"
              >
                Disetujui ({{ countStatusPenukaran('disetujui') }})
              </button>
            </div>
          </div>

          <div v-if="!filteredPenukaran.length" class="empty-state">
            <div class="empty-icon">⏳</div>
            <h3>Belum Ada Riwayat Penukaran</h3>
            <p>Pilih hadiah menarik di Katalog Hadiah untuk menukarkan Poin Sampah Anda.</p>
            <button class="btn-reset" @click="selectTab('katalog')">Buka Katalog Hadiah</button>
          </div>

          <div v-else class="penukaran-list">
            <div v-for="p in filteredPenukaran" :key="p.id" class="penukaran-card">
              <div class="penukaran-thumb">
                <img
                  v-if="p.barang?.foto"
                  :src="fotoUrl(p.barang.foto)"
                  :alt="p.barang?.nama"
                  @error="imageError"
                />
                <span v-else class="penukaran-thumb-placeholder">🎁</span>
              </div>

              <div class="penukaran-details">
                <div class="penukaran-title-row">
                  <h3 class="penukaran-item-name">{{ p.barang?.nama || 'Barang Dihapus' }}</h3>
                  <span class="status-badge" :class="'badge-' + p.status">
                    {{ formatStatusText(p.status) }}
                  </span>
                </div>

                <div class="penukaran-meta-grid">
                  <div class="meta-item">
                    <span class="meta-label">Jumlah</span>
                    <span class="meta-val">{{ p.jumlah }} pcs</span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Total Poin</span>
                    <span class="meta-val highlight-val">{{ formatNum(p.total_poin) }} Poin</span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Rencana Ambil</span>
                    <span class="meta-val">{{ formatDate(p.tanggal_ambil) }}</span>
                  </div>
                  <div class="meta-item">
                    <span class="meta-label">Diajukan</span>
                    <span class="meta-val">{{ formatDateTime(p.created_at) }}</span>
                  </div>
                </div>

                <div v-if="p.catatan_admin" class="penukaran-note">
                  <span class="note-label">Catatan Admin:</span>
                  <span class="note-text">{{ p.catatan_admin }}</span>
                </div>
              </div>

              <div v-if="p.status === 'menunggu'" class="penukaran-action">
                <button class="btn-cancel-penukaran" @click="batalkanPenukaran(p.id)">
                  Batalkan
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: RIWAYAT SETORAN                        -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'setoran'" class="tab-content">
          <div class="section-header">
            <div>
              <h2 class="section-title">Riwayat Setoran Sampah</h2>
              <p class="section-subtitle">Catatan lengkap penyetoran sampah terpilah yang telah diverifikasi petugas.</p>
            </div>
            <div class="header-stat-chip">
              Total {{ riwayatSetoran.length }} Transaksi • {{ totalBeratSetoran }} kg
            </div>
          </div>

          <div v-if="!riwayatSetoran.length" class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Belum Ada Riwayat Setoran</h3>
            <p>Bawa sampah kering terpilah (kertas, botol plastik, logam) ke pos Bank Sampah sekolah untuk mendapatkan poin.</p>
          </div>

          <div v-else class="table-card">
            <!-- Desktop Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th width="8%">#</th>
                  <th width="22%">Tanggal & Waktu</th>
                  <th width="28%">Jenis Sampah</th>
                  <th width="20%">Berat</th>
                  <th width="22%">Poin Didapat</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(s, idx) in riwayatSetoran" :key="s.id">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ formatDateTime(s.created_at) }}</td>
                  <td>
                    <span class="category-chip">{{ s.kategori?.nama || s.jenis_sampah || 'Sampah Terpilah' }}</span>
                  </td>
                  <td><strong>{{ Number(s.berat).toLocaleString('id-ID') }} kg</strong></td>
                  <td>
                    <span class="poin-plus">+{{ formatNum(s.poin) }} Poin</span>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Mobile Cards -->
            <div class="mobile-card-list">
              <div v-for="(s, idx) in riwayatSetoran" :key="'mob-' + s.id" class="mobile-card">
                <div class="m-card-top">
                  <span class="category-chip">{{ s.kategori?.nama || s.jenis_sampah || 'Sampah Terpilah' }}</span>
                  <span class="poin-plus">+{{ formatNum(s.poin) }} Poin</span>
                </div>
                <div class="m-card-body">
                  <div class="m-stat">
                    <span>Berat Sampah:</span>
                    <strong>{{ Number(s.berat).toLocaleString('id-ID') }} kg</strong>
                  </div>
                  <div class="m-stat">
                    <span>Waktu Setor:</span>
                    <span>{{ formatDateTime(s.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 4: MUTASI POIN                            -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'mutasi'" class="tab-content">
          <div class="section-header">
            <div>
              <h2 class="section-title">Buku Mutasi Poin</h2>
              <p class="section-subtitle">Catatan keluar-masuk poin sampah dan poin lingkungan secara transparan.</p>
            </div>
          </div>

          <div v-if="!riwayatMutasi.length" class="empty-state">
            <div class="empty-icon">📈</div>
            <h3>Belum Ada Catatan Mutasi</h3>
            <p>Setiap penambahan atau pengurangan poin akan tercatat otomatis di sini.</p>
          </div>

          <div v-else class="table-card">
            <!-- Desktop Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th width="6%">#</th>
                  <th width="20%">Waktu</th>
                  <th width="18%">Jenis Poin</th>
                  <th width="18%">Perubahan</th>
                  <th width="16%">Sumber</th>
                  <th width="22%">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(m, idx) in riwayatMutasi" :key="m.id">
                  <td>{{ idx + 1 }}</td>
                  <td>{{ formatDateTime(m.created_at) }}</td>
                  <td>
                    <span class="point-badge" :class="m.jenis_poin === 'sampah' ? 'badge-sampah' : 'badge-eco'">
                      {{ m.jenis_poin === 'sampah' ? '♻ Poin Sampah' : '🌱 Poin Lingkungan' }}
                    </span>
                  </td>
                  <td>
                    <span :class="m.jumlah >= 0 ? 'poin-plus' : 'poin-minus'">
                      {{ m.jumlah > 0 ? '+' + formatNum(m.jumlah) : formatNum(m.jumlah) }}
                    </span>
                  </td>
                  <td><span class="source-chip">{{ m.sumber }}</span></td>
                  <td>{{ m.keterangan || '-' }}</td>
                </tr>
              </tbody>
            </table>

            <!-- Mobile Cards -->
            <div class="mobile-card-list">
              <div v-for="(m, idx) in riwayatMutasi" :key="'mob-mutasi-' + m.id" class="mobile-card">
                <div class="m-card-top">
                  <span class="point-badge" :class="m.jenis_poin === 'sampah' ? 'badge-sampah' : 'badge-eco'">
                    {{ m.jenis_poin === 'sampah' ? '♻ Sampah' : '🌱 Lingkungan' }}
                  </span>
                  <span :class="m.jumlah >= 0 ? 'poin-plus' : 'poin-minus'">
                    {{ m.jumlah > 0 ? '+' + formatNum(m.jumlah) : formatNum(m.jumlah) }} Poin
                  </span>
                </div>
                <div class="m-card-body">
                  <div class="m-stat">
                    <span>Sumber:</span>
                    <strong>{{ m.sumber }}</strong>
                  </div>
                  <div class="m-stat" v-if="m.keterangan">
                    <span>Keterangan:</span>
                    <span>{{ m.keterangan }}</span>
                  </div>
                  <div class="m-stat">
                    <span>Waktu:</span>
                    <span>{{ formatDateTime(m.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 5: SURVEY LINGKUNGAN                      -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'survey'" class="tab-content">
          <div class="section-header">
            <div>
              <h2 class="section-title">Hasil Survey & Penilaian Lingkungan</h2>
              <p class="section-subtitle">Evaluasi kebersihan kelas, pemilahan sampah, dan pemeliharaan tanaman oleh petugas.</p>
            </div>
            <div class="header-stat-chip">
              {{ riwayatSurvey.length }} Hasil Inspeksi
            </div>
          </div>

          <div v-if="!riwayatSurvey.length" class="empty-state">
            <div class="empty-icon">🌿</div>
            <h3>Belum Ada Data Survey</h3>
            <p>Hasil inspeksi dan verifikasi kebersihan kelas Anda akan muncul di sini secara berkala.</p>
          </div>

          <div v-else class="survey-grid">
            <div v-for="s in riwayatSurvey" :key="s.id" class="survey-card">
              <div class="survey-top">
                <span class="survey-cat">{{ formatKategori(s.kategori) }}</span>
                <span class="survey-date">{{ formatDate(s.tanggal) }}</span>
              </div>

              <div v-if="s.foto" class="survey-photo-box">
                <img :src="fotoUrl(s.foto)" alt="Foto survey" class="survey-photo" @error="imageError" />
              </div>

              <div class="survey-body">
                <div class="survey-status-row">
                  <span class="eval-badge" :class="isStatusPositive(s.status) ? 'eval-ok' : 'eval-bad'">
                    {{ isStatusPositive(s.status) ? '✓ ' : '✕ ' }}{{ formatStatus(s.status) }}
                  </span>
                  <span v-if="s.poin" class="survey-pts">+{{ s.poin }} Poin Lingkungan</span>
                </div>

                <p class="survey-note">{{ s.keterangan || 'Tidak ada catatan khusus dari petugas.' }}</p>

                <div class="survey-footer">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <span>{{ s.petugas?.nama || 'Petugas Lingkungan' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 6: PERINGKAT & RIVALITAS SISWA            -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'peringkat'" class="tab-content">
          <div class="section-header">
            <div>
              <h2 class="section-title">Papan Peringkat & Rivalitas Nasabah</h2>
              <p class="section-subtitle">
                Pantau posisimu saat ini, selisih poin dengan siswa di atas dan di bawahmu, serta podium juara lingkungan.
              </p>
            </div>
            <div class="header-stat-chip rank-chip-highlight">
              👑 Peringkat #{{ myRankInfo.my_rank || '-' }} dari {{ myRankInfo.total_nasabah || 0 }} Siswa
            </div>
          </div>

          <!-- MOTIVATION ENCOURAGEMENT BANNER -->
          <div class="motivation-banner" v-if="myRankInfo.motivasi">
            <div class="mot-icon">⚡</div>
            <div class="mot-content">
              <h4>Status Posisi & Misi Kamu</h4>
              <p>{{ myRankInfo.motivasi }}</p>
            </div>
          </div>

          <!-- 3-PILLAR BATTLE ARENA (SEBELUM - KAMU - SESUDAH) -->
          <div class="battle-arena-section">
            <h3 class="battle-arena-title">Zona Rivalitas Langsung (Sebelum vs Kamu vs Sesudah)</h3>
            <div class="battle-arena-grid">
              <!-- Kiri: Siswa di atas (Sebelum) -->
              <div class="arena-card card-above" :class="{ 'is-empty': !myRankInfo.sebelum }">
                <div class="arena-card-badge">
                  <span>▲ Peringkat Di Atas Kamu</span>
                </div>
                <template v-if="myRankInfo.sebelum">
                  <div class="arena-rank-num">#{{ myRankInfo.sebelum.rank }}</div>
                  <h4 class="arena-user-name">{{ myRankInfo.sebelum.nama }}</h4>
                  <div class="arena-user-handle">@{{ myRankInfo.sebelum.username }}</div>
                  <div class="arena-points">
                    <strong>{{ formatNum(myRankInfo.sebelum.total_poin) }}</strong>
                    <span>Total Poin</span>
                  </div>
                  <div class="arena-gap-box gap-warning">
                    <span class="gap-label">Kamu Tertinggal:</span>
                    <span class="gap-value">+{{ formatNum(myRankInfo.sebelum.selisih_poin) }} Poin</span>
                    <span class="gap-desc">Kumpulkan setoran baru untuk mengejar posisinya!</span>
                  </div>
                </template>
                <template v-else>
                  <div class="arena-empty-state">
                    <span class="empty-crown">👑</span>
                    <h4>Kamu Memimpin Puncak!</h4>
                    <p>Tidak ada siswa di atasmu. Kamu adalah Peringkat #1 di sekolah.</p>
                  </div>
                </template>
              </div>

              <!-- Tengah: Diri Kamu (HIGHLIGHTED CARD) -->
              <div class="arena-card card-me">
                <div class="arena-card-badge badge-me">
                  <span>⭐ Posisi Kamu Saat Ini</span>
                </div>
                <div class="arena-rank-num rank-me">#{{ myRankInfo.my_rank || '-' }}</div>
                <h4 class="arena-user-name">{{ myRankInfo.me?.nama || auth.user?.nama }}</h4>
                <div class="arena-user-handle">@{{ myRankInfo.me?.username || auth.user?.username }} (Akun Kamu)</div>
                <div class="arena-points points-me">
                  <strong>{{ formatNum(myRankInfo.me?.total_poin || totalPoinDisplay) }}</strong>
                  <span>Total Poin Akumulasi</span>
                </div>
                <div class="arena-me-stats">
                  <div class="me-stat-item">
                    <span class="stat-lbl">Poin Sampah</span>
                    <span class="stat-val">{{ formatNum(myRankInfo.me?.poin_sampah ?? auth.user?.poin_sampah) }}</span>
                  </div>
                  <div class="me-stat-item">
                    <span class="stat-lbl">Poin Lingkungan</span>
                    <span class="stat-val">{{ formatNum(myRankInfo.me?.poin_lingkungan ?? auth.user?.poin_lingkungan) }}</span>
                  </div>
                  <div class="me-stat-item">
                    <span class="stat-lbl">Total Sampah</span>
                    <span class="stat-val">{{ myRankInfo.me?.total_berat ?? totalBeratSetoran }} kg</span>
                  </div>
                </div>
              </div>

              <!-- Kanan: Siswa di bawah (Sesudah) -->
              <div class="arena-card card-below" :class="{ 'is-empty': !myRankInfo.sesudah }">
                <div class="arena-card-badge">
                  <span>▼ Peringkat Di Bawah Kamu</span>
                </div>
                <template v-if="myRankInfo.sesudah">
                  <div class="arena-rank-num">#{{ myRankInfo.sesudah.rank }}</div>
                  <h4 class="arena-user-name">{{ myRankInfo.sesudah.nama }}</h4>
                  <div class="arena-user-handle">@{{ myRankInfo.sesudah.username }}</div>
                  <div class="arena-points">
                    <strong>{{ formatNum(myRankInfo.sesudah.total_poin) }}</strong>
                    <span>Total Poin</span>
                  </div>
                  <div class="arena-gap-box gap-success">
                    <span class="gap-label">Jarak Keunggulan Kamu:</span>
                    <span class="gap-value">{{ formatNum(myRankInfo.sesudah.selisih_poin) }} Poin</span>
                    <span class="gap-desc">Tetap aktif menyetor agar posisimu tidak tersalip!</span>
                  </div>
                </template>
                <template v-else>
                  <div class="arena-empty-state">
                    <span class="empty-crown">🌱</span>
                    <h4>Ayo Semangat Menanjak!</h4>
                    <p>Belum ada siswa di bawah posisimu saat ini. Pilah sampah sekarang untuk naik peringkat!</p>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- PODIUM TOP 3 JUARA SEKOLAH -->
          <div class="rank-top3-section" v-if="myRankInfo.top_3 && myRankInfo.top_3.length">
            <h3 class="section-subheading">Podium 3 Teratas (Duta Lingkungan Sekolah)</h3>
            <div class="top3-grid">
              <div
                v-for="item in myRankInfo.top_3"
                :key="'podium-' + item.id"
                class="top3-podium-card"
                :class="[
                  `podium-${item.rank}`,
                  { 'is-current-user': item.id === (myRankInfo.me?.id || auth.user?.id) }
                ]"
              >
                <div class="podium-badge">
                  <span v-if="item.rank === 1">🥇 Juara 1</span>
                  <span v-else-if="item.rank === 2">🥈 Juara 2</span>
                  <span v-else>🥉 Juara 3</span>
                </div>
                <div class="podium-avatar">
                  {{ item.nama ? item.nama.charAt(0).toUpperCase() : 'S' }}
                </div>
                <h4 class="podium-name">
                  {{ item.nama }}
                  <span v-if="item.id === (myRankInfo.me?.id || auth.user?.id)" class="self-chip">Kamu</span>
                </h4>
                <div class="podium-pts">{{ formatNum(item.total_poin) }} Poin</div>
                <div class="podium-waste">{{ item.total_berat }} kg sampah terpilah</div>
              </div>
            </div>
          </div>

          <!-- TABEL KLASEMEN LENGKAP -->
          <div class="leaderboard-table-section">
            <div class="table-section-head">
              <div>
                <h3 class="section-subheading">Daftar Peringkat Lengkap Siswa</h3>
                <p class="section-subdesc">Seluruh siswa aktif diurutkan berdasarkan akumulasi perolehan poin.</p>
              </div>
              <div class="table-search-mini">
                <input
                  v-model="rankSearchQuery"
                  type="text"
                  placeholder="Cari siswa atau username..."
                />
              </div>
            </div>

            <!-- Table View Desktop -->
            <div class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr>
                    <th style="width: 70px;">Peringkat</th>
                    <th>Nama Siswa</th>
                    <th>Poin Sampah</th>
                    <th>Poin Lingkungan</th>
                    <th>Total Poin</th>
                    <th>Total Sampah</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="row in filteredLeaderboard"
                    :key="'lb-' + row.id"
                    :class="{ 'row-highlight-self': row.id === (myRankInfo.me?.id || auth.user?.id) }"
                  >
                    <td>
                      <span class="rank-pos-badge" :class="`pos-${row.rank}`">
                        <template v-if="row.rank === 1">🥇</template>
                        <template v-else-if="row.rank === 2">🥈</template>
                        <template v-else-if="row.rank === 3">🥉</template>
                        #{{ row.rank }}
                      </span>
                    </td>
                    <td>
                      <div class="user-cell">
                        <strong>{{ row.nama }}</strong>
                        <span class="user-sub">@{{ row.username }}</span>
                        <span v-if="row.id === (myRankInfo.me?.id || auth.user?.id)" class="self-tag-inline">Kamu</span>
                      </div>
                    </td>
                    <td>{{ formatNum(row.poin_sampah) }}</td>
                    <td>{{ formatNum(row.poin_lingkungan) }}</td>
                    <td>
                      <strong class="total-pt-bold">{{ formatNum(row.total_poin) }}</strong>
                    </td>
                    <td>{{ row.total_berat }} kg</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Mobile Card View for Leaderboard -->
            <div class="mobile-card-list">
              <div
                v-for="row in filteredLeaderboard"
                :key="'mob-lb-' + row.id"
                class="mobile-card"
                :class="{ 'card-highlight-self': row.id === (myRankInfo.me?.id || auth.user?.id) }"
              >
                <div class="m-card-top">
                  <span class="rank-pos-badge" :class="`pos-${row.rank}`">
                    #{{ row.rank }}
                  </span>
                  <strong class="total-pt-bold">{{ formatNum(row.total_poin) }} Poin</strong>
                </div>
                <div class="m-card-body">
                  <div class="m-stat">
                    <span>Nama:</span>
                    <strong>{{ row.nama }} <span v-if="row.id === (myRankInfo.me?.id || auth.user?.id)" class="self-tag-inline">Kamu</span></strong>
                  </div>
                  <div class="m-stat">
                    <span>Rincian Poin:</span>
                    <span>♻ {{ formatNum(row.poin_sampah) }} | 🌱 {{ formatNum(row.poin_lingkungan) }}</span>
                  </div>
                  <div class="m-stat">
                    <span>Sampah Terkumpul:</span>
                    <span>{{ row.total_berat }} kg</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ============================================== -->
    <!-- MOBILE FIXED BOTTOM NAVIGATION DOCK            -->
    <!-- ============================================== -->
    <nav class="mobile-bottom-nav" aria-label="Navigasi Cepat Mobile">
      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'katalog' }"
        @click="selectTab('katalog')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zm0 0h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
          <span v-if="katalogBarang.length" class="b-badge-dot">{{ katalogBarang.length }}</span>
        </div>
        <span class="b-label">Katalog</span>
      </button>

      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'peringkat' }"
        @click="selectTab('peringkat')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 22V12M14 22V12M6 4h12v7a6 6 0 0 1-12 0V4z"/></svg>
          <span class="b-badge-dot badge-rank-dot" v-if="myRankInfo.my_rank">#{{ myRankInfo.my_rank }}</span>
        </div>
        <span class="b-label">Peringkat</span>
      </button>

      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'penukaran' }"
        @click="selectTab('penukaran')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span v-if="pendingPenukaranCount > 0" class="b-badge-alert">{{ pendingPenukaranCount }}</span>
        </div>
        <span class="b-label">Pesanan</span>
      </button>

      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'setoran' }"
        @click="selectTab('setoran')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 19H4.8A2 2 0 0 1 3 16.9L4 11M17 19h2.2a2 2 0 0 0 1.8-2.1L20 11M7 19l2-4M17 19l-2-4M8.5 5h7l2 4H6.5l2-4z"/></svg>
        </div>
        <span class="b-label">Setoran</span>
      </button>

      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'mutasi' }"
        @click="selectTab('mutasi')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="b-label">Mutasi</span>
      </button>

      <button
        class="bottom-nav-item"
        :class="{ active: activeTab === 'survey' }"
        @click="selectTab('survey')"
      >
        <div class="b-icon-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <span class="b-label">Survey</span>
      </button>
    </nav>

    <!-- ============================================== -->
    <!-- MODAL PENUKARAN BARANG (BOTTOM SHEET DI HP)    -->
    <!-- ============================================== -->
    <transition name="modal-fade">
      <div v-if="showModalRedeem" class="modal-backdrop" @click.self="closeModalRedeem">
        <div class="modal-card" role="dialog" aria-modal="true">
          <!-- Mobile Pull Handle -->
          <div class="sheet-handle-bar" aria-hidden="true">
            <div class="sheet-handle"></div>
          </div>

          <div class="modal-header">
            <div class="modal-title-group">
              <div class="modal-icon">🎁</div>
              <div>
                <h3 class="modal-title">Konfirmasi Penukaran Hadiah</h3>
                <p class="modal-sub">Gunakan saldo Poin Sampah Anda</p>
              </div>
            </div>
            <button class="btn-close-modal" @click="closeModalRedeem">✕</button>
          </div>

          <form @submit.prevent="submitRedeem" class="modal-form" v-if="selectedBarang">
            <!-- Item Preview -->
            <div class="modal-item-preview">
              <div class="preview-thumb">
                <img v-if="selectedBarang.foto" :src="fotoUrl(selectedBarang.foto)" alt="Foto" @error="imageError" />
                <span v-else>🎁</span>
              </div>
              <div class="preview-info">
                <h4>{{ selectedBarang.nama }}</h4>
                <div class="preview-pts">{{ formatNum(selectedBarang.harga_poin) }} Poin / pcs</div>
                <div class="preview-stk">Tersedia: {{ selectedBarang.stok }} pcs</div>
              </div>
            </div>

            <!-- Form Inputs -->
            <div class="form-row">
              <label class="form-label">Jumlah Barang yang Ingin Ditukar</label>
              <div class="qty-stepper">
                <button
                  type="button"
                  class="btn-step"
                  :disabled="formRedeem.jumlah <= 1"
                  @click="formRedeem.jumlah = Math.max(1, formRedeem.jumlah - 1)"
                >−</button>
                <input
                  v-model.number="formRedeem.jumlah"
                  type="number"
                  min="1"
                  :max="selectedBarang.stok"
                  class="qty-input"
                  required
                />
                <button
                  type="button"
                  class="btn-step"
                  :disabled="formRedeem.jumlah >= selectedBarang.stok"
                  @click="formRedeem.jumlah = Math.min(selectedBarang.stok, formRedeem.jumlah + 1)"
                >+</button>
              </div>
            </div>

            <div class="form-row">
              <label class="form-label">Rencana Tanggal Pengambilan (Minimal H+1)</label>
              <input
                v-model="formRedeem.tanggal_ambil"
                type="date"
                :min="minTanggalAmbil"
                class="form-input"
                required
              />
              <small class="form-hint">
                Hadiah dapat diambil di pos Bank Sampah sekolah mulai tanggal yang dipilih.
              </small>
            </div>

            <!-- Live Calculation Summary -->
            <div class="calc-box">
              <div class="calc-line">
                <span>Total Poin Dibutuhkan:</span>
                <strong>{{ formatNum(totalPoinDibutuhkan) }} Poin</strong>
              </div>
              <div class="calc-line">
                <span>Saldo Poin Anda:</span>
                <strong :class="(auth.user?.poin_sampah ?? 0) >= totalPoinDibutuhkan ? 'txt-green' : 'txt-red'">
                  {{ formatNum(auth.user?.poin_sampah ?? 0) }} Poin
                </strong>
              </div>
              <div class="calc-line calc-line-total">
                <span>Sisa Poin Setelah Penukaran:</span>
                <strong v-if="(auth.user?.poin_sampah ?? 0) >= totalPoinDibutuhkan" class="txt-green">
                  {{ formatNum((auth.user?.poin_sampah ?? 0) - totalPoinDibutuhkan) }} Poin
                </strong>
                <strong v-else class="txt-red">Poin Tidak Cukup</strong>
              </div>
              <div v-if="(auth.user?.poin_sampah ?? 0) < totalPoinDibutuhkan" class="calc-error">
                Poin Sampah Anda kurang {{ formatNum(totalPoinDibutuhkan - (auth.user?.poin_sampah ?? 0)) }} poin.
              </div>
            </div>

            <!-- Modal Actions -->
            <div class="modal-actions">
              <button type="button" class="btn-cancel" @click="closeModalRedeem">Batal</button>
              <button
                type="submit"
                class="btn-submit"
                :disabled="submittingRedeem || (auth.user?.poin_sampah ?? 0) < totalPoinDibutuhkan || formRedeem.jumlah <= 0"
              >
                <span v-if="submittingRedeem">Memproses...</span>
                <span v-else>Ajukan Penukaran Sekarang</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '../stores/useAuthStore'

const SEKOLAH = 'SMP 2 Al-Muhajirin'

const router = useRouter()
const auth = useAuthStore()

// State
const activeTab = ref('katalog')
const loading = ref(true)

const katalogBarang = ref([])
const riwayatPenukaran = ref([])
const riwayatSetoran = ref([])
const riwayatMutasi = ref([])
const riwayatSurvey = ref([])

const myRankInfo = ref({
  my_rank: 0,
  total_nasabah: 0,
  me: null,
  sebelum: null,
  sesudah: null,
  motivasi: '',
  surrounding: [],
  top_3: []
})

// Filters
const katalogSearch = ref('')
const filterKatalog = ref('all')
const filterStatusPenukaran = ref('all')
const rankSearchQuery = ref('')

// Modal Redeem
const showModalRedeem = ref(false)
const selectedBarang = ref(null)
const submittingRedeem = ref(false)
const formRedeem = ref({
  jumlah: 1,
  tanggal_ambil: ''
})

// Tab Switch with smooth scrolling on mobile
const selectTab = (tabName) => {
  activeTab.value = tabName
  if (typeof window !== 'undefined' && window.innerWidth <= 768) {
    const el = document.getElementById('panel-content-area')
    if (el) {
      const topOffset = el.getBoundingClientRect().top + window.pageYOffset - 70
      window.scrollTo({ top: topOffset, behavior: 'smooth' })
    }
  }
}

// Current tab title
const currentTabTitle = computed(() => {
  const titles = {
    katalog: 'Katalog Hadiah Reward',
    penukaran: 'Status Penukaran Hadiah',
    setoran: 'Riwayat Setoran Sampah',
    mutasi: 'Buku Mutasi Poin',
    survey: 'Survey & Evaluasi Lingkungan',
    peringkat: 'Papan Peringkat & Rivalitas Siswa'
  }
  return titles[activeTab.value] || 'Portal Nasabah'
})

// Computed
const userInitial = computed(() => {
  const name = auth.user?.nama || auth.user?.username || 'S'
  return name.charAt(0).toUpperCase()
})

const totalPoinDisplay = computed(() => {
  const total = (auth.user?.poin_sampah ?? 0) + (auth.user?.poin_lingkungan ?? 0)
  return Number(total).toLocaleString('id-ID')
})

const pendingPenukaranCount = computed(() => {
  return riwayatPenukaran.value.filter(p => p.status === 'menunggu').length
})

const totalBeratSetoran = computed(() => {
  const total = riwayatSetoran.value.reduce((acc, curr) => acc + (Number(curr.berat) || 0), 0)
  return total.toLocaleString('id-ID', { maximumFractionDigits: 1 })
})

const affordableCount = computed(() => {
  const userPoin = auth.user?.poin_sampah ?? 0
  return katalogBarang.value.filter(b => userPoin >= b.harga_poin && b.stok > 0).length
})

const minTanggalAmbil = computed(() => {
  const tomorrow = new Date(Date.now() + 24 * 60 * 60 * 1000)
  const y = tomorrow.getFullYear()
  const m = String(tomorrow.getMonth() + 1).padStart(2, '0')
  const d = String(tomorrow.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
})

const totalPoinDibutuhkan = computed(() => {
  if (!selectedBarang.value) return 0
  return (formRedeem.value.jumlah || 1) * selectedBarang.value.harga_poin
})

// Filtered data
const filteredKatalog = computed(() => {
  let list = katalogBarang.value
  const userPoin = auth.user?.poin_sampah ?? 0

  if (katalogSearch.value.trim()) {
    const q = katalogSearch.value.toLowerCase()
    list = list.filter(b => b.nama.toLowerCase().includes(q) || (b.deskripsi && b.deskripsi.toLowerCase().includes(q)))
  }

  if (filterKatalog.value === 'affordable') {
    list = list.filter(b => userPoin >= b.harga_poin && b.stok > 0)
  } else if (filterKatalog.value === 'in_stock') {
    list = list.filter(b => b.stok > 0)
  }

  return list
})

const filteredPenukaran = computed(() => {
  if (filterStatusPenukaran.value === 'all') return riwayatPenukaran.value
  return riwayatPenukaran.value.filter(p => p.status === filterStatusPenukaran.value)
})

const filteredLeaderboard = computed(() => {
  const list = myRankInfo.value.leaderboard || myRankInfo.value.surrounding || []
  if (!rankSearchQuery.value.trim()) return list
  const q = rankSearchQuery.value.toLowerCase()
  return list.filter(item => 
    (item.nama && item.nama.toLowerCase().includes(q)) || 
    (item.username && item.username.toLowerCase().includes(q))
  )
})

const countStatusPenukaran = (st) => {
  return riwayatPenukaran.value.filter(p => p.status === st).length
}

// Utilities
const formatNum = (num) => Number(num || 0).toLocaleString('id-ID')
const canAfford = (b) => (auth.user?.poin_sampah ?? 0) >= b.harga_poin

const fotoUrl = (foto) => {
  if (!foto) return ''
  if (foto.startsWith('http://') || foto.startsWith('https://')) return foto
  if (foto.startsWith('/storage/')) return foto
  if (foto.startsWith('storage/')) return `/${foto}`
  return `/storage/${foto}`
}

const imageError = (e) => {
  e.target.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100"%3E%3Crect width="100" height="100" fill="%23edf4ea"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-family="sans-serif" font-size="14" fill="%23627267"%3EHadiah%3C/text%3E%3C/svg%3E'
}

const formatKategori = (kat) => {
  if (kat === 'pemilahan_sampah') return 'Pemilahan Sampah'
  if (kat === 'kebersihan_kelas') return 'Kebersihan Kelas'
  if (kat === 'perawatan_tanaman') return 'Perawatan Tanaman'
  return kat
}

const formatStatus = (status) => {
  if (!status) return '-'
  return status.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}

const formatStatusText = (st) => {
  if (st === 'menunggu') return 'Menunggu Konfirmasi'
  if (st === 'disetujui') return 'Disetujui'
  if (st === 'diambil') return 'Sudah Diambil'
  if (st === 'ditolak') return 'Ditolak'
  if (st === 'dibatalkan') return 'Dibatalkan'
  return st
}

const isStatusPositive = (status) => {
  if (!status) return false
  const s = String(status).toLowerCase()
  if (s.includes('kotor') || s.includes('buruk') || s.includes('layu') || s.includes('tidak')) return false
  return true
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Actions
const loadAllData = async () => {
  loading.value = true
  const token = auth.token || localStorage.getItem('token')
  if (!token) {
    loading.value = false
    router.push('/login')
    return
  }
  const headers = { Authorization: `Bearer ${token}` }

  try {
    const [userRes, barangRes, penukaranRes, setoranRes, mutasiRes, surveyRes, rankRes] = await Promise.all([
      axios.get('/api/me', { headers }),
      axios.get('/api/barang-redeem', { headers }),
      axios.get('/api/me/penukaran', { headers }),
      axios.get('/api/me/setoran', { headers }),
      axios.get('/api/me/riwayat-poin', { headers }),
      axios.get('/api/survey-lingkungan/saya', { headers }),
      axios.get('/api/me/peringkat', { headers }).catch(() => ({ data: {} })),
    ])

    const currentUser = userRes.data.user || userRes.data
    auth.user = currentUser
    auth.token = token
    localStorage.setItem('user', JSON.stringify(currentUser))

    katalogBarang.value = Array.isArray(barangRes.data) ? barangRes.data : (barangRes.data?.data || [])
    riwayatPenukaran.value = Array.isArray(penukaranRes.data) ? penukaranRes.data : (penukaranRes.data?.data || [])
    riwayatSetoran.value = Array.isArray(setoranRes.data) ? setoranRes.data : (setoranRes.data?.data || [])
    riwayatMutasi.value = Array.isArray(mutasiRes.data) ? mutasiRes.data : (mutasiRes.data?.data || [])
    riwayatSurvey.value = Array.isArray(surveyRes.data) ? surveyRes.data : (surveyRes.data?.data || [])
    if (rankRes.data && rankRes.data.status === 'success') {
      myRankInfo.value = rankRes.data
    }
  } catch (err) {
    if (err.response?.status === 401) {
      auth.logout()
      router.push('/login')
    } else {
      Swal.fire('Gagal!', err.response?.data?.message || 'Tidak dapat memuat data nasabah.', 'error')
    }
  } finally {
    loading.value = false
  }
}

const openModalRedeem = (b) => {
  selectedBarang.value = b
  formRedeem.value = {
    jumlah: 1,
    tanggal_ambil: minTanggalAmbil.value
  }
  showModalRedeem.value = true
}

const closeModalRedeem = () => {
  showModalRedeem.value = false
  selectedBarang.value = null
}

const submitRedeem = async () => {
  if (!selectedBarang.value) return

  if (formRedeem.value.tanggal_ambil < minTanggalAmbil.value) {
    Swal.fire('Tanggal Tidak Valid', 'Rencana tanggal ambil minimal 1 hari setelah hari ini (H+1).', 'warning')
    return
  }

  submittingRedeem.value = true
  try {
    const res = await axios.post('/api/penukaran', {
      barang_id: selectedBarang.value.id,
      jumlah: formRedeem.value.jumlah,
      tanggal_ambil: formRedeem.value.tanggal_ambil,
    }, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    Swal.fire({
      icon: 'success',
      title: 'Penukaran Diajukan!',
      text: res.data.message || 'Permintaan penukaran sedang menunggu konfirmasi admin.',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    closeModalRedeem()
    await loadAllData()
    selectTab('penukaran')
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal mengajukan penukaran barang.', 'error')
  } finally {
    submittingRedeem.value = false
  }
}

const batalkanPenukaran = async (id) => {
  const confirm = await Swal.fire({
    title: 'Batalkan Pengajuan?',
    text: 'Poin Sampah dan stok barang akan segera dikembalikan ke akun Anda.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#b91c1c',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Batalkan',
    cancelButtonText: 'Kembali'
  })

  if (!confirm.isConfirmed) return

  try {
    const res = await axios.post(`/api/me/penukaran/${id}/batal`, {}, {
      headers: { Authorization: `Bearer ${auth.token}` }
    })

    Swal.fire({
      icon: 'success',
      title: 'Dibatalkan',
      text: res.data.message || 'Poin Sampah telah dikembalikan ke saldo.',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    })

    await loadAllData()
  } catch (err) {
    Swal.fire('Gagal!', err.response?.data?.message || 'Gagal membatalkan penukaran.', 'error')
  }
}

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Konfirmasi Logout',
    text: 'Apakah Anda ingin keluar dari akun nasabah?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#173f32',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal'
  })
  if (result.isConfirmed) {
    await auth.logout()
    router.push('/login')
  }
}

onMounted(() => {
  loadAllData()
})
</script>

<style scoped>
/* ==========================================================================
   PORTAL NASABAH - MODERN, LIGHTWEIGHT, ULTRA-RESPONSIVE (Bank Samsul Design)
   ========================================================================== */
.nasabah-app {
  --forest: #173f32;
  --forest-dark: #0e271f;
  --green: #28694e;
  --green-light: #eaf3ee;
  --lime: #d5ee83;
  --lime-soft: #f4fae1;
  --bg-page: #f8faf8;
  --card-bg: #ffffff;
  --ink: #14231d;
  --muted: #64748b;
  --line: #e2e8f0;
  --line-light: #f1f5f9;
  --amber: #d97706;
  --amber-bg: #fef3c7;
  --red: #dc2626;
  --red-bg: #fee2e2;
  --blue: #2563eb;
  --blue-bg: #dbeafe;
  --radius-xl: 20px;
  --radius-lg: 14px;
  --radius-md: 10px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
  --shadow-card: 0 4px 16px -2px rgba(23, 63, 50, 0.06), 0 1px 3px rgba(23, 63, 50, 0.03);
  --shadow-hover: 0 10px 24px -4px rgba(23, 63, 50, 0.12);

  min-height: 100vh;
  background-color: var(--bg-page);
  color: var(--ink);
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  line-height: 1.5;
}

/* ==============================================
   1. TOP NAVIGATION BAR
   ============================================== */
.app-nav {
  position: sticky;
  top: 0;
  z-index: 40;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--line);
}

.nav-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  width: 36px;
  height: 36px;
  background: var(--forest);
  color: var(--lime);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-logo svg {
  width: 20px;
  height: 20px;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--forest);
  letter-spacing: -0.02em;
  line-height: 1.1;
}

.brand-badge {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.nav-balance {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--green-light);
  border: 1px solid rgba(40, 105, 78, 0.2);
  padding: 6px 14px;
  border-radius: 999px;
  cursor: default;
}

.balance-icon {
  font-size: 1rem;
}

.balance-value {
  font-weight: 800;
  color: var(--forest);
  font-size: 0.98rem;
}

.balance-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--green);
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-left: 6px;
  border-left: 1px solid var(--line);
}

.user-avatar {
  width: 34px;
  height: 34px;
  background: var(--forest);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
}

.user-meta {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--ink);
  line-height: 1.2;
}

.user-role {
  font-size: 0.72rem;
  color: var(--muted);
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  color: #64748b;
  border: 1px solid var(--line);
  padding: 7px 12px;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-logout svg {
  width: 16px;
  height: 16px;
}

.btn-logout:hover {
  background: var(--red-bg);
  color: var(--red);
  border-color: rgba(220, 38, 38, 0.3);
}

/* ==============================================
   2. MAIN CONTENT AREA
   ============================================== */
.app-main {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 20px 60px;
}

/* ==============================================
   3. HERO BENTO GRID
   ============================================== */
.hero-bento {
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.bento-card {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  padding: 22px;
  box-shadow: var(--shadow-card);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

/* Primary Card (Poin Sampah Utama) */
.bento-primary {
  background: linear-gradient(135deg, var(--forest-dark) 0%, var(--forest) 100%);
  color: #fff;
  border-color: rgba(213, 238, 131, 0.2);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.bento-glow {
  position: absolute;
  top: -40px;
  right: -40px;
  width: 180px;
  height: 180px;
  background: radial-gradient(circle, rgba(213, 238, 131, 0.18) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}

.primary-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 12px;
}

.primary-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.12);
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--lime);
}

.primary-tag svg {
  width: 14px;
  height: 14px;
}

.pending-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(239, 117, 72, 0.25);
  border: 1px solid rgba(239, 117, 72, 0.5);
  color: #fed7aa;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.pending-chip:hover {
  opacity: 0.9;
}

.pulse-dot {
  width: 7px;
  height: 7px;
  background: #f97316;
  border-radius: 50%;
  box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.4);
}

.primary-balance {
  margin-bottom: 8px;
}

.balance-digits {
  font-size: 2.75rem;
  font-weight: 900;
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: #fff;
}

.balance-unit {
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: var(--lime);
  margin-top: 2px;
}

.primary-desc {
  font-size: 0.88rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.45;
  margin-bottom: 18px;
  max-width: 440px;
}

.primary-footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.btn-primary-action {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--lime);
  color: var(--forest-dark);
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(213, 238, 131, 0.3);
  transition: all 0.2s;
}

.btn-primary-action svg {
  width: 17px;
  height: 17px;
}

.btn-primary-action:hover {
  background: #e2f59e;
  transform: translateY(-1px);
}

.btn-secondary-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.84rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary-action svg {
  width: 15px;
  height: 15px;
}

.btn-secondary-action:hover {
  background: rgba(255, 255, 255, 0.18);
}

/* Side Card: Eco & Stats */
.bento-eco,
.bento-stat {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
}

.bento-eco:hover,
.bento-stat:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-hover);
  border-color: rgba(40, 105, 78, 0.3);
}

.eco-head,
.stat-head {
  display: flex;
  align-items: center;
  gap: 8px;
}

.eco-icon,
.stat-icon {
  font-size: 1.25rem;
}

.card-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.eco-value,
.stat-value {
  margin: 10px 0;
}

.value-digits {
  font-size: 2.2rem;
  font-weight: 900;
  line-height: 1.1;
  color: var(--forest);
  letter-spacing: -0.02em;
}

.value-unit {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--muted);
  margin-left: 6px;
}

.eco-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #fef9c3;
  color: #854d0e;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 600;
  margin-bottom: 12px;
}

.star-icon {
  color: #eab308;
  font-size: 0.9rem;
}

.stat-meta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.stat-chip {
  background: var(--line-light);
  color: var(--ink);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
}

.card-action-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--green);
  padding-top: 8px;
  border-top: 1px solid var(--line-light);
}

.card-action-link svg {
  width: 14px;
  height: 14px;
  transition: transform 0.2s;
}

.bento-eco:hover .card-action-link svg,
.bento-stat:hover .card-action-link svg {
  transform: translateX(3px);
}

/* ==============================================
   4. MOBILE QUICK ACTION GRID (Ergonomic for Phone)
   ============================================== */
.mobile-features-grid {
  display: none;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  margin-bottom: 20px;
}

.m-feat-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid var(--line);
  padding: 12px 14px;
  border-radius: var(--radius-md);
  text-align: left;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}

.m-feat-btn.m-feat-wide {
  grid-column: span 2;
}

.m-feat-icon-bubble {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.bubble-katalog { background: #ecfdf5; }
.bubble-penukaran { background: #fff7ed; }
.bubble-setoran { background: #eff6ff; }
.bubble-mutasi { background: #f5f3ff; }
.bubble-survey { background: #f0fdf4; }

.m-feat-texts {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.m-feat-title {
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--ink);
  line-height: 1.2;
}

.m-feat-sub {
  font-size: 0.72rem;
  color: var(--muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.m-feat-sub.text-alert {
  color: #ea580c;
  font-weight: 700;
}

.m-feat-indicator {
  font-size: 0.8rem;
  color: var(--forest);
}

.m-feat-alert-dot {
  width: 8px;
  height: 8px;
  background: #ea580c;
  border-radius: 50%;
}

.m-feat-btn.active {
  background: #f1f8f4;
  border-color: var(--green);
  box-shadow: 0 4px 12px rgba(40, 105, 78, 0.08);
}

.m-feat-btn.active .m-feat-title {
  color: var(--forest);
}

/* ==============================================
   5. DESKTOP NAVIGATION TABS
   ============================================== */
.tabs-nav {
  display: flex;
  gap: 8px;
  background: #fff;
  border: 1px solid var(--line);
  padding: 6px;
  border-radius: var(--radius-lg);
  margin-bottom: 24px;
  overflow-x: auto;
  box-shadow: var(--shadow-sm);
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--muted);
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.tab-btn svg {
  width: 17px;
  height: 17px;
}

.tab-badge {
  background: var(--line);
  color: var(--muted);
  font-size: 0.74rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.tab-badge.badge-alert {
  background: #ea580c;
  color: #fff;
}

.tab-btn:hover {
  background: var(--line-light);
  color: var(--ink);
}

.tab-btn.active {
  background: var(--forest);
  color: #fff;
}

.tab-btn.active .tab-badge {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.tab-btn.active .tab-badge.badge-alert {
  background: #ea580c;
}

/* Indicator banner on mobile */
.mobile-section-indicator {
  display: none;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--line);
}

.indicator-tag {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--green);
  letter-spacing: 0.05em;
  display: block;
  margin-bottom: 2px;
}

.indicator-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--forest);
  margin: 0;
}

/* ==============================================
   6. CONTENT PANELS
   ============================================== */
.content-panel {
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  padding: 24px;
  box-shadow: var(--shadow-card);
  scroll-margin-top: 80px;
}

.section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--line);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--forest);
  margin: 0 0 4px;
}

.section-subtitle {
  font-size: 0.86rem;
  color: var(--muted);
  margin: 0;
}

.header-stat-chip {
  background: var(--green-light);
  color: var(--forest);
  font-size: 0.82rem;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 8px;
  white-space: nowrap;
}

/* Toolbar & Search */
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 260px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-box svg {
  position: absolute;
  left: 14px;
  width: 17px;
  height: 17px;
  color: var(--muted);
  pointer-events: none;
}

.search-box input {
  width: 100%;
  padding: 10px 38px 10px 40px;
  border: 1px solid var(--line);
  border-radius: 10px;
  font-size: 0.88rem;
  outline: none;
  background: var(--line-light);
  transition: all 0.2s;
}

.search-box input:focus {
  background: #fff;
  border-color: var(--green);
  box-shadow: 0 0 0 3px rgba(40, 105, 78, 0.12);
}

.btn-clear {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  color: var(--muted);
  cursor: pointer;
  font-size: 0.85rem;
}

.filter-group {
  display: flex;
  gap: 8px;
}

.filter-pill {
  background: var(--line-light);
  border: 1px solid var(--line);
  color: var(--muted);
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.filter-pill:hover {
  color: var(--ink);
  border-color: #cbd5e1;
}

.filter-pill.active {
  background: var(--forest);
  border-color: var(--forest);
  color: #fff;
}

/* ==============================================
   7. CATALOG GRID & CARDS
   ============================================== */
.catalog-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.catalog-card {
  background: #fff;
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
}

.catalog-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-hover);
  border-color: rgba(40, 105, 78, 0.3);
}

.catalog-card.card-disabled {
  opacity: 0.7;
}

.card-media {
  position: relative;
  height: 160px;
  background: #f1f5f9;
  overflow: hidden;
}

.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.card-placeholder svg {
  width: 48px;
  height: 48px;
}

.stock-pill {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.stock-ok {
  background: rgba(23, 63, 50, 0.85);
  color: var(--lime);
  backdrop-filter: blur(4px);
}

.stock-empty {
  background: rgba(220, 38, 38, 0.9);
  color: #fff;
}

.card-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0 0 6px;
  line-height: 1.3;
}

.card-desc {
  font-size: 0.82rem;
  color: var(--muted);
  margin: 0 0 14px;
  line-height: 1.4;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.price-badge {
  display: flex;
  align-items: baseline;
  gap: 4px;
}

.price-num {
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--forest);
}

.price-txt {
  font-size: 0.74rem;
  font-weight: 600;
  color: var(--muted);
}

.afford-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.afford-ok {
  background: var(--green-light);
  color: var(--green);
}

.afford-no {
  background: var(--line-light);
  color: #94a3b8;
}

.btn-redeem {
  width: 100%;
  background: var(--forest);
  color: #fff;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.86rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-redeem:hover:not(:disabled) {
  background: var(--green);
}

.btn-redeem:disabled {
  background: #cbd5e1;
  color: #64748b;
  cursor: not-allowed;
}

/* ==============================================
   8. STATUS PENUKARAN LIST
   ============================================== */
.status-filter-pills {
  display: flex;
  gap: 6px;
}

.pill-filter-sm {
  background: var(--line-light);
  border: 1px solid var(--line);
  color: var(--muted);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  cursor: pointer;
}

.pill-filter-sm.active {
  background: var(--forest);
  border-color: var(--forest);
  color: #fff;
}

.penukaran-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.penukaran-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fff;
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  padding: 14px 18px;
  transition: border-color 0.2s;
}

.penukaran-card:hover {
  border-color: #cbd5e1;
}

.penukaran-thumb {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  background: #f1f5f9;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.penukaran-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.penukaran-details {
  flex: 1;
}

.penukaran-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
  gap: 10px;
}

.penukaran-item-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0;
}

.status-badge {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
  text-transform: capitalize;
}

.badge-menunggu { background: var(--amber-bg); color: var(--amber); }
.badge-disetujui { background: var(--blue-bg); color: var(--blue); }
.badge-diambil { background: var(--green-light); color: var(--green); }
.badge-ditolak { background: var(--red-bg); color: var(--red); }
.badge-dibatalkan { background: var(--line-light); color: var(--muted); }

.penukaran-meta-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  font-size: 0.8rem;
}

.meta-item {
  display: flex;
  gap: 4px;
}

.meta-label {
  color: var(--muted);
}

.meta-val {
  font-weight: 600;
  color: var(--ink);
}

.meta-val.highlight-val {
  color: var(--forest);
  font-weight: 700;
}

.penukaran-note {
  margin-top: 6px;
  background: var(--line-light);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.78rem;
}

.note-label {
  font-weight: 700;
  color: var(--muted);
  margin-right: 4px;
}

.btn-cancel-penukaran {
  background: transparent;
  color: var(--red);
  border: 1px solid rgba(220, 38, 38, 0.3);
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-cancel-penukaran:hover {
  background: var(--red-bg);
}

/* ==============================================
   9. TABLES & DATA LISTS (Setoran & Mutasi)
   ============================================== */
.table-card {
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.88rem;
}

.data-table th {
  background: var(--line-light);
  color: var(--muted);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 12px 14px;
  border-bottom: 1px solid var(--line);
}

.data-table td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--line);
  color: var(--ink);
}

.data-table tr:hover td {
  background: #fbfdfa;
}

.category-chip {
  background: #ecfdf5;
  color: #065f46;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.82rem;
}

.poin-plus {
  color: #059669;
  font-weight: 800;
}

.poin-minus {
  color: var(--red);
  font-weight: 800;
}

.point-badge {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.badge-sampah {
  background: var(--green-light);
  color: var(--forest);
}

.badge-eco {
  background: #fef9c3;
  color: #854d0e;
}

.source-chip {
  background: var(--line-light);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 4px;
  color: var(--muted);
}

/* Mobile Card List (Hidden on desktop) */
.mobile-card-list {
  display: none;
}

/* ==============================================
   10. SURVEY LINGKUNGAN GRID
   ============================================== */
.survey-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.survey-card {
  background: #fff;
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.survey-top {
  padding: 12px 14px;
  background: var(--line-light);
  border-bottom: 1px solid var(--line);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.survey-cat {
  font-weight: 700;
  font-size: 0.86rem;
  color: var(--forest);
}

.survey-date {
  font-size: 0.76rem;
  color: var(--muted);
}

.survey-photo-box {
  height: 140px;
  background: #e2e8f0;
  overflow: hidden;
}

.survey-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.survey-body {
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.survey-status-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.eval-badge {
  font-size: 0.76rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.eval-ok { background: #dcfce7; color: #15803d; }
.eval-bad { background: #fee2e2; color: #b91c1c; }

.survey-pts {
  font-size: 0.8rem;
  font-weight: 700;
  color: #15803d;
}

.survey-note {
  font-size: 0.82rem;
  color: var(--muted);
  line-height: 1.4;
  margin: 0;
  flex: 1;
}

.survey-footer {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.76rem;
  color: var(--muted);
  padding-top: 6px;
  border-top: 1px solid var(--line-light);
}

.survey-footer svg {
  width: 14px;
  height: 14px;
}

/* ==============================================
   11. MOBILE BOTTOM NAVIGATION BAR (FIXED DOCK)
   ============================================== */
.mobile-bottom-nav {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-top: 1px solid var(--line);
  padding: 6px 8px calc(6px + env(safe-area-inset-bottom, 0px));
  z-index: 50;
  box-shadow: 0 -4px 20px rgba(23, 63, 50, 0.08);
  justify-content: space-around;
}

.bottom-nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3px;
  background: transparent;
  border: none;
  padding: 6px 8px;
  border-radius: 10px;
  color: var(--muted);
  cursor: pointer;
  flex: 1;
  max-width: 72px;
  transition: all 0.2s;
}

.b-icon-wrap {
  position: relative;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.b-icon-wrap svg {
  width: 22px;
  height: 22px;
  transition: transform 0.2s;
}

.b-label {
  font-size: 0.68rem;
  font-weight: 600;
  line-height: 1;
}

.b-badge-dot {
  position: absolute;
  top: -2px;
  right: -6px;
  background: var(--forest);
  color: #fff;
  font-size: 0.62rem;
  font-weight: 800;
  min-width: 15px;
  height: 15px;
  padding: 0 3px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.b-badge-alert {
  position: absolute;
  top: -2px;
  right: -6px;
  background: #ea580c;
  color: #fff;
  font-size: 0.62rem;
  font-weight: 800;
  min-width: 15px;
  height: 15px;
  padding: 0 3px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bottom-nav-item.active {
  color: var(--forest);
}

.bottom-nav-item.active .b-icon-wrap svg {
  transform: scale(1.12);
  color: var(--forest);
}

.bottom-nav-item.active .b-label {
  font-weight: 800;
  color: var(--forest);
}

/* ==============================================
   12. MODAL PENUKARAN BARANG
   ============================================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(14, 39, 31, 0.65);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 100;
}

.modal-card {
  background: #fff;
  border-radius: var(--radius-xl);
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.sheet-handle-bar {
  display: none;
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.96) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  padding: 18px 20px;
  border-bottom: 1px solid var(--line);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.modal-icon {
  font-size: 1.4rem;
}

.modal-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--forest);
  margin: 0;
}

.modal-sub {
  font-size: 0.78rem;
  color: var(--muted);
  margin: 0;
}

.btn-close-modal {
  background: transparent;
  border: none;
  font-size: 1.1rem;
  color: var(--muted);
  cursor: pointer;
}

.modal-form {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-item-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--line-light);
  padding: 12px;
  border-radius: 10px;
}

.preview-thumb {
  width: 54px;
  height: 54px;
  background: #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.preview-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-info h4 {
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0 0 2px;
}

.preview-pts {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--forest);
}

.preview-stk {
  font-size: 0.74rem;
  color: var(--muted);
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--ink);
}

.qty-stepper {
  display: flex;
  align-items: center;
  width: 140px;
  border: 1px solid var(--line);
  border-radius: 8px;
  overflow: hidden;
}

.btn-step {
  width: 38px;
  height: 38px;
  background: var(--line-light);
  border: none;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-step:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.qty-input {
  flex: 1;
  width: 100%;
  border: none;
  text-align: center;
  font-weight: 700;
  font-size: 0.95rem;
  outline: none;
}

.form-input {
  padding: 10px 12px;
  border: 1px solid var(--line);
  border-radius: 8px;
  font-size: 0.88rem;
  outline: none;
}

.form-input:focus {
  border-color: var(--green);
}

.form-hint {
  font-size: 0.74rem;
  color: var(--muted);
}

.calc-box {
  background: #f8fafc;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.84rem;
}

.calc-line {
  display: flex;
  justify-content: space-between;
}

.calc-line-total {
  padding-top: 6px;
  border-top: 1px dashed var(--line);
  font-weight: 700;
}

.txt-green { color: #15803d; }
.txt-red { color: var(--red); }

.calc-error {
  color: var(--red);
  font-size: 0.78rem;
  font-weight: 600;
  margin-top: 4px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 6px;
}

.btn-cancel {
  flex: 1;
  background: var(--line-light);
  border: 1px solid var(--line);
  color: var(--ink);
  padding: 10px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.86rem;
  cursor: pointer;
}

.btn-submit {
  flex: 2;
  background: var(--forest);
  color: #fff;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-submit:hover:not(:disabled) {
  background: var(--green);
}

.btn-submit:disabled {
  background: #cbd5e1;
  color: #64748b;
  cursor: not-allowed;
}

/* ==============================================
   13. STATES & HELPERS
   ============================================== */
.state-box {
  text-align: center;
  padding: 60px 20px;
  color: var(--muted);
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--line);
  border-top-color: var(--forest);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 48px 20px;
}

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.empty-state h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0 0 4px;
}

.empty-state p {
  font-size: 0.86rem;
  color: var(--muted);
  max-width: 380px;
  margin: 0 auto 16px;
}

.btn-reset {
  background: var(--forest);
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
}

/* Modal Fade Animation */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* ==============================================
   13. RANKING & RIVALRY STYLES (SEBELUM - SAYA - SESUDAH)
   ============================================== */
.ranking-rivalry-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: linear-gradient(135deg, #ffffff 0%, #f6fbf8 100%);
  border: 1.5px solid #d1fae5;
  border-radius: var(--radius-lg);
  padding: 14px 20px;
  margin-bottom: 24px;
  box-shadow: 0 4px 14px -2px rgba(16, 185, 129, 0.08);
  cursor: pointer;
  transition: all 0.2s ease;
}

.ranking-rivalry-bar:hover {
  transform: translateY(-2px);
  border-color: #10b981;
  box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.15);
}

.rivalry-left {
  flex-shrink: 0;
}

.rank-badge-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.trophy-emoji {
  font-size: 1.8rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  background: #fef3c7;
  border: 1px solid #fde68a;
  border-radius: 12px;
}

.rivalry-kicker {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #047857;
}

.rank-highlight {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.rank-highlight .rank-num {
  font-size: 1.45rem;
  font-weight: 800;
  color: #064e3b;
  letter-spacing: -0.02em;
}

.rank-highlight .rank-out-of {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

.rivalry-center {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  justify-content: center;
}

.rival-slot {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 8px 14px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  min-width: 170px;
}

.rival-slot.rival-above {
  border-color: #fed7aa;
  background: #fffaf5;
}

.rival-slot.rival-below {
  border-color: #bbf7d0;
  background: #f0fdf4;
}

.rival-slot.rival-leader {
  border-color: #fde68a;
  background: #fffbeb;
}

.rival-tag {
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.tag-above { color: #c2410c; }
.tag-below { color: #15803d; }
.tag-leader { color: #b45309; }
.tag-bottom { color: #475569; }

.rival-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

.rival-gap {
  font-size: 0.74rem;
  color: #64748b;
}

.rival-gap strong {
  color: #0f172a;
}

.rival-divider-icon {
  font-size: 0.95rem;
  color: #94a3b8;
  font-weight: 800;
}

.rivalry-right {
  flex-shrink: 0;
}

.btn-open-rank-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #173f32;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-open-rank-tab svg {
  width: 15px;
  height: 15px;
}

.btn-open-rank-tab:hover {
  background: #0f2b22;
  transform: translateX(2px);
}

.bubble-rank {
  background: #fef3c7;
}

.badge-rank {
  background: #10b981;
  color: #ffffff;
}

.badge-rank-dot {
  background: #059669;
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

.rank-chip-highlight {
  background: #ecfdf5;
  color: #065f46;
  border-color: #a7f3d0;
  font-weight: 700;
}

/* Motivation Banner */
.motivation-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
  border: 1.5px solid #86efac;
  border-radius: var(--radius-md);
  padding: 14px 18px;
  margin-bottom: 24px;
}

.mot-icon {
  font-size: 1.6rem;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #bbf7d0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.mot-content h4 {
  font-size: 0.88rem;
  font-weight: 700;
  color: #14532d;
  margin: 0 0 2px 0;
}

.mot-content p {
  font-size: 0.82rem;
  color: #166534;
  margin: 0;
  line-height: 1.45;
}

/* Battle Arena 3-Pillar Grid */
.battle-arena-section {
  margin-bottom: 28px;
}

.battle-arena-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 12px 0;
  letter-spacing: -0.01em;
}

.battle-arena-grid {
  display: grid;
  grid-template-columns: 1fr 1.15fr 1fr;
  gap: 16px;
}

.arena-card {
  background: #ffffff;
  border-radius: var(--radius-lg);
  border: 1.5px solid var(--line);
  padding: 18px;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.arena-card.card-above {
  border-color: #fdba74;
  background: linear-gradient(180deg, #fffaf5 0%, #ffffff 100%);
}

.arena-card.card-below {
  border-color: #86efac;
  background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
}

.arena-card.card-me {
  border: 2px solid #059669;
  background: linear-gradient(180deg, #f6fbf8 0%, #ffffff 100%);
  box-shadow: 0 8px 24px -4px rgba(5, 150, 105, 0.16);
  transform: translateY(-2px);
}

.arena-card-badge {
  display: inline-block;
  align-self: flex-start;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 4px 10px;
  border-radius: 999px;
  margin-bottom: 12px;
  background: #f1f5f9;
  color: #475569;
}

.card-above .arena-card-badge {
  background: #ffedd5;
  color: #c2410c;
}

.card-below .arena-card-badge {
  background: #dcfce7;
  color: #15803d;
}

.arena-card-badge.badge-me {
  background: #10b981;
  color: #ffffff;
}

.arena-rank-num {
  font-size: 1.5rem;
  font-weight: 800;
  color: #334155;
  margin-bottom: 4px;
}

.arena-rank-num.rank-me {
  font-size: 1.75rem;
  color: #065f46;
}

.arena-user-name {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}

.arena-user-handle {
  font-size: 0.78rem;
  color: #64748b;
  margin-bottom: 12px;
}

.arena-points {
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px dashed var(--line);
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.arena-points strong {
  font-size: 1.35rem;
  font-weight: 800;
  color: #0f172a;
}

.arena-points span {
  font-size: 0.78rem;
  color: #64748b;
}

.arena-points.points-me strong {
  color: #059669;
  font-size: 1.5rem;
}

.arena-gap-box {
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.arena-gap-box.gap-warning {
  background: #fff7ed;
  border: 1px solid #fed7aa;
}

.arena-gap-box.gap-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.gap-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
}

.gap-value {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
}

.gap-warning .gap-value {
  color: #ea580c;
}

.gap-success .gap-value {
  color: #16a34a;
}

.gap-desc {
  font-size: 0.72rem;
  color: #64748b;
}

.arena-me-stats {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  background: #f8fafc;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.me-stat-item {
  display: flex;
  flex-direction: column;
}

.me-stat-item .stat-lbl {
  font-size: 0.68rem;
  color: #64748b;
}

.me-stat-item .stat-val {
  font-size: 0.88rem;
  font-weight: 700;
  color: #0f172a;
}

.arena-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 24px 12px;
  gap: 6px;
}

.empty-crown {
  font-size: 2.2rem;
}

.arena-empty-state h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.arena-empty-state p {
  font-size: 0.78rem;
  color: #64748b;
  margin: 0;
}

/* Podium Top 3 */
.rank-top3-section {
  margin-bottom: 28px;
}

.section-subheading {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 12px 0;
}

.section-subdesc {
  font-size: 0.78rem;
  color: #64748b;
  margin: 0;
}

.top3-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.top3-podium-card {
  background: #ffffff;
  border: 1.5px solid var(--line);
  border-radius: var(--radius-lg);
  padding: 18px 14px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.2s ease;
}

.top3-podium-card:hover {
  transform: translateY(-2px);
}

.top3-podium-card.podium-1 {
  border-color: #f59e0b;
  background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
  box-shadow: 0 4px 16px -2px rgba(245, 158, 11, 0.15);
}

.top3-podium-card.podium-2 {
  border-color: #94a3b8;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
}

.top3-podium-card.podium-3 {
  border-color: #b45309;
  background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
}

.podium-badge {
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.podium-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #173f32;
  color: #ffffff;
  font-size: 1.15rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.podium-1 .podium-avatar {
  background: #f59e0b;
  color: #78350f;
}

.podium-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 4px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.self-chip {
  background: #10b981;
  color: #ffffff;
  font-size: 0.65rem;
  padding: 1px 6px;
  border-radius: 999px;
  font-weight: 600;
}

.podium-pts {
  font-size: 1.1rem;
  font-weight: 800;
  color: #059669;
}

.podium-waste {
  font-size: 0.74rem;
  color: #64748b;
}

/* Leaderboard Table Section */
.leaderboard-table-section {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
  padding: 18px;
}

.table-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 12px;
  flex-wrap: wrap;
}

.table-search-mini input {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.82rem;
  width: 240px;
  outline: none;
}

.table-search-mini input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15);
}

.rank-pos-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-weight: 800;
  font-size: 0.85rem;
  color: #334155;
}

.rank-pos-badge.pos-1 { color: #d97706; }
.rank-pos-badge.pos-2 { color: #475569; }
.rank-pos-badge.pos-3 { color: #9a3412; }

.user-cell {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.user-sub {
  font-size: 0.74rem;
  color: #64748b;
}

.self-tag-inline {
  display: inline-block;
  background: #10b981;
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 4px;
  margin-left: 6px;
}

.total-pt-bold {
  color: #059669;
  font-weight: 800;
}

.row-highlight-self {
  background: #f0fdf4 !important;
}

.card-highlight-self {
  border: 1.5px solid #10b981 !important;
  background: #f0fdf4 !important;
}

/* ==============================================
   14. MOBILE & RESPONSIVE BREAKPOINTS
   ============================================== */
@media (max-width: 992px) {
  .hero-bento {
    grid-template-columns: 1fr 1fr;
  }
  .bento-primary {
    grid-column: span 2;
  }
}

@media (max-width: 768px) {
  /* Extra bottom padding to account for fixed bottom navigation dock */
  .nasabah-app {
    padding-bottom: 84px;
  }

  .nav-inner {
    padding: 10px 14px;
  }

  .nav-user .user-meta {
    display: none;
  }

  .nav-balance .balance-label {
    display: none;
  }

  .logout-text {
    display: none;
  }

  .btn-logout {
    padding: 6px 8px;
  }

  .app-main {
    padding: 14px 12px 30px;
  }

  .hero-bento {
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 16px;
  }

  .bento-primary {
    grid-column: span 1;
    padding: 18px;
  }

  .balance-digits {
    font-size: 2.2rem;
  }

  /* Show mobile quick feature tiles */
  .mobile-features-grid {
    display: grid;
  }

  /* Hide desktop tab strip on mobile, show indicator */
  .desktop-only-tabs {
    display: none;
  }

  .mobile-section-indicator {
    display: block;
  }

  /* Show fixed bottom navigation bar */
  .mobile-bottom-nav {
    display: flex;
  }

  .content-panel {
    padding: 16px 12px;
    border-radius: var(--radius-lg);
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .filter-group {
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
  }

  .catalog-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .card-media {
    height: 120px;
  }

  .card-info {
    padding: 12px 10px;
  }

  .card-title {
    font-size: 0.88rem;
  }

  .price-num {
    font-size: 1rem;
  }

  .afford-tag {
    font-size: 0.68rem;
    padding: 2px 6px;
  }

  .btn-redeem {
    padding: 8px 6px;
    font-size: 0.8rem;
  }

  /* Table responsive switch */
  .data-table {
    display: none;
  }

  .mobile-card-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .mobile-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 12px;
  }

  .m-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }

  .m-card-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.8rem;
  }

  .m-stat {
    display: flex;
    justify-content: space-between;
    color: var(--muted);
  }

  .m-stat strong,
  .m-stat span:last-child {
    color: var(--ink);
  }

  .penukaran-card {
    flex-direction: column;
    align-items: stretch;
    padding: 12px;
  }

  .penukaran-thumb {
    width: 100%;
    height: 110px;
  }

  /* Ranking & Rivalry Mobile Styles */
  .ranking-rivalry-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 14px;
  }

  .rivalry-center {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .rival-slot {
    min-width: 0;
    width: 100%;
  }

  .rival-name {
    max-width: none;
  }

  .rival-divider-icon {
    display: none;
  }

  .btn-open-rank-tab {
    width: 100%;
    justify-content: center;
  }

  .battle-arena-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .arena-card.card-me {
    order: -1;
  }

  .arena-me-stats {
    grid-template-columns: 1fr;
    gap: 6px;
  }

  .top3-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .table-search-mini input {
    width: 100%;
  }

  /* Modal Bottom Sheet on Mobile */
  .modal-backdrop {
    align-items: flex-end;
    padding: 0;
  }

  .modal-card {
    max-width: 100%;
    border-radius: 22px 22px 0 0;
    max-height: 90vh;
    overflow-y: auto;
    animation: sheetSlideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .sheet-handle-bar {
    display: flex;
    justify-content: center;
    padding: 10px 0 2px;
  }

  .sheet-handle {
    width: 40px;
    height: 5px;
    background: #cbd5e1;
    border-radius: 999px;
  }

  @keyframes sheetSlideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
  }
}
</style>
