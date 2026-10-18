<template>
  <div class="landing">
    <!-- NAVBAR -->
    <header class="lp-nav" :class="{ scrolled }">
      <div class="wrap nav-inner">
        <a class="brand" href="#top" aria-label="Bank Samsul, beranda">
          <span class="brand-mark"><img src="/images/logo.jpeg" alt="Logo Bank Samsul" class="brand-logo-img" /></span>
          bank samsul.
        </a>
        <nav id="menu" class="nav-links" :class="{ open: menuOpen }" aria-label="Navigasi utama">
          <a v-for="l in navLinks" :key="l.href" :href="l.href" @click="menuOpen = false">{{ l.label }}</a>
        </nav>
        <router-link class="lp-btn" to="/login">Masuk <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" /></svg></router-link>
        <button class="burger" :aria-label="menuOpen ? 'Tutup menu' : 'Buka menu'" :aria-expanded="menuOpen" aria-controls="menu" @click="menuOpen = !menuOpen">
          <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
      </div>
    </header>

    <main id="top">
      <!-- HERO -->
      <section class="hero">
        <div class="wrap">
          <div class="hero-grid">
            <div class="hero-copy">
              <span class="eyebrow">Gerakan kecil, dampak nyata</span>
              <h1>Sampahmu punya <em>cerita baru.</em></h1>
              <p>Di Bank Samsul, barang yang sudah tak terpakai bisa jadi sesuatu yang berarti. Pilah, setor, kumpulkan poin, lalu tukarkan dengan hadiah pilihanmu.</p>
              <div class="hero-actions">
                <router-link class="lp-btn" to="/login">Mulai dari sini <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" /></svg></router-link>
                <a class="lp-btn secondary" href="#panduan">Lihat Panduan</a>
              </div>
              <div class="tiny-note"><span></span> Program bank sampah {{ SEKOLAH }}</div>
            </div>

            <div class="hero-art" role="img" aria-label="Ilustrasi tanaman yang tumbuh dari pot daur ulang">
              <div class="art-top">Dari sisa, jadi berharga</div>
              <div class="sun"></div><div class="orbit"></div>
              <div class="stem"></div>
              <div class="leaf one"></div><div class="leaf two"></div><div class="leaf three"></div><div class="leaf four"></div>
              <div class="pot-rim"></div><div class="plant-pot"></div>
              <div class="float-card fc-one">
                <span class="mini-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3h8v3l2 3v11H6V9l2-3z" /></svg></span>
                <small>{{ kategoriTeratas?.nama || 'Setiap setoran' }}</small>
                <b>{{ kategoriTeratas ? `${fmt(kategoriTeratas.poin_per_kg)} poin/kg` : 'Poin bertambah' }}</b>
              </div>
              <div class="float-card fc-two">
                <small>Jenis sampah diterima</small>
                <b>{{ kategori.length || '—' }} jenis ♻</b>
              </div>
              <div class="art-caption">Tumbuh bersama kebiasaan baik.</div>
            </div>
          </div>

          <div class="hero-bottom">
            <p>Lebih dari sekadar membuang sampah.</p>
            <div class="mini-steps">
              <span><i>01</i> Pilah</span><b>—</b><span><i>02</i> Setor</span><b>—</b><span><i>03</i> Tukar</span>
            </div>
          </div>
        </div>
      </section>

      <!-- STATISTIK (dari database) -->
      <div class="stats">
        <div class="wrap stats-grid">
          <div v-for="s in statistik" :key="s.label" class="stat">
            <strong>{{ s.value }}</strong><span>{{ s.label }}</span>
          </div>
        </div>
      </div>

      <!-- TENTANG -->
      <section id="tentang" class="section">
        <div class="wrap about-grid">
          <div v-reveal class="about-copy reveal">
            <span class="eyebrow">Kenalan dengan kami</span>
            <h2>Sekolah bersih dimulai dari kebiasaan kecil.</h2>
            <p>Bank Samsul membantu siswa mencatat setoran sampah dan mengubahnya menjadi poin. Bukan cuma soal hadiah—ini cara sederhana untuk belajar menjaga lingkungan bersama.</p>
          </div>
          <div class="principles">
            <article v-for="(p, i) in prinsip" :key="p.no" v-reveal class="principle reveal" :class="`delay-${i}`">
              <span class="principle-no">{{ p.no }}</span>
              <div><h3>{{ p.judul }}</h3><p>{{ p.isi }}</p></div>
            </article>
          </div>
        </div>
      </section>

      <!-- PANDUAN PENYETORAN SAMPAH BANK SAMSUL -->
      <section id="panduan" class="section guide">
        <span id="cara" class="anchor-alias"></span>
        <div class="wrap">
          <div class="guide-top">
            <div v-reveal class="section-head reveal">
              <span class="eyebrow">Panduan untuk Siswa / Nasabah</span>
              <h2>Panduan Penyetoran Sampah Bank Samsul</h2>
              <p>Pelajari panduan lengkap mulai dari pemilahan sampah, penyetoran di loket, perhitungan poin, hingga penukaran barang.</p>
            </div>
            <div v-reveal class="guide-action-box reveal delay-1">
              <a href="/panduan-bank-samsul.pdf" target="_blank" download="Panduan-Bank-Samsul.pdf" class="lp-btn secondary pdf-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                <span>Unduh PDF Panduan</span>
              </a>
            </div>
          </div>

          <!-- 6 KARTU LANGKAH PANDUAN -->
          <div class="guide-grid">
            <article v-for="(p, i) in panduanLangkah" :key="p.no" v-reveal class="guide-card reveal" :class="`delay-${(i % 3) + 1}`">
              <div class="guide-card-head">
                <span class="guide-step-badge">{{ p.no }}</span>
                <span class="guide-step-tag">Langkah {{ p.no }}</span>
              </div>
              <h3 class="guide-card-title">{{ p.judul }}</h3>
              <p v-if="p.deskripsi" class="guide-card-desc">{{ p.deskripsi }}</p>

              <div v-if="p.linkText" class="guide-link-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <a :href="p.linkHref" target="_blank" rel="noopener">{{ p.linkText }}</a>
              </div>

              <p v-if="p.instruksi" class="guide-card-subdesc">{{ p.instruksi }}</p>

              <ul class="guide-checklist">
                <li v-for="(item, idx) in p.poinList" :key="idx">
                  <span class="check-bullet">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  </span>
                  <span>{{ item }}</span>
                </li>
              </ul>

              <!-- Catatan khusus jika ada -->
              <div v-if="p.catatan" class="guide-note-box">
                <div class="note-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="note-text">
                  <strong>Penting:</strong> {{ p.catatan }}
                </div>
              </div>
            </article>
          </div>

          <!-- BANNER MOTTO PENUTUP -->
          <div v-reveal class="guide-motto-banner reveal delay-2">
            <div class="motto-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <div class="motto-content">
              <p>Mari pilah sampah dengan benar, kumpulkan poin, dan berpartisipasi aktif dalam menjaga kebersihan serta kelestarian lingkungan sekolah!</p>
            </div>
          </div>
        </div>
      </section>

      <!-- DUA JENIS POIN -->
      <section id="poin" class="section">
        <div class="wrap">
          <div v-reveal class="section-head reveal">
            <span class="eyebrow">Poinmu, kontribusimu</span>
            <h2>Ada dua cara untuk berkontribusi.</h2>
            <p>Setiap aksi baik punya nilai. Kenali dua jenis poin yang bisa kamu kumpulkan di Bank Samsul.</p>
          </div>
          <div class="points-grid">
            <article v-reveal class="point-card reveal">
              <span class="eyebrow">01 / Dari setoran</span>
              <div class="point-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3h8v3l2 3v11H6V9l2-3z" /><path d="M8 6h8M6 13h12" /></svg></div>
              <h3>Poin sampah</h3>
              <p>Didapat dari sampah yang kamu setorkan, dihitung dari berat × poin per kg. Poin ini yang bisa ditukar dengan hadiah.</p>
              <ul class="rate-list">
                <li v-for="k in kategori" :key="k.id">
                  <span>{{ k.nama }} <small>{{ k.kategori }}</small></span>
                  <b>{{ fmt(k.poin_per_kg) }} / {{ k.satuan || 'kg' }}</b>
                </li>
                <li v-if="!kategori.length" class="muted">{{ loading ? 'Memuat jenis sampah…' : 'Belum ada jenis sampah.' }}</li>
              </ul>
            </article>

            <article v-reveal class="point-card featured reveal delay-1">
              <span class="eyebrow">02 / Dari aksi harian</span>
              <div class="point-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z" /><path d="m9 12 2 2 4-4" /></svg></div>
              <h3>Poin lingkungan</h3>
              <p>Diberikan saat petugas mengisi survei lingkungan kelas. Nilai rating langsung menjadi poin:</p>
              <ul class="rate-list">
                <li v-for="s in skalaLingkungan" :key="s.nama"><span>{{ s.nama }}</span><b>+{{ s.skala }} poin</b></li>
              </ul>
            </article>
          </div>
        </div>
      </section>

      <!-- KATALOG HADIAH (dari database) -->
      <section id="hadiah" class="section rewards">
        <div class="wrap">
          <div class="rewards-top">
            <div v-reveal class="section-head reveal">
              <span class="eyebrow">Sedikit apresiasi</span>
              <h2>Hadiah untuk langkah baikmu.</h2>
              <p>Kumpulkan poin sampah dan tukarkan dengan barang yang bermanfaat.</p>
            </div>
            <p>Katalog di bawah diambil langsung dari daftar barang aktif yang dikelola admin.</p>
          </div>

          <div class="reward-grid">
            <article v-for="(b, i) in barang" :key="b.id" v-reveal class="reward reveal" :class="`delay-${i % 4}`">
              <div class="reward-art" :style="{ background: ART_BG[i % ART_BG.length] }">
                <img v-if="b.foto" :src="fotoUrl(b.foto)" :alt="b.nama" loading="lazy" @error="onImgError(b)" />
                <svg v-else viewBox="0 0 100 100" fill="none" stroke="#28694e" stroke-width="2.5" stroke-linejoin="round"><path d="M22 42h56v40H22z" fill="#d5ee83" /><path d="M18 30h64v12H18zM50 30v52" fill="#fffef9" /><path d="M50 30c-8-14-24-10-18 0M50 30c8-14 24-10 18 0" stroke="#ef7548" /></svg>
              </div>
              <div class="reward-info">
                <h3>{{ b.nama }}</h3>
                <span>{{ fmt(b.harga_poin) }} poin · stok {{ b.stok }}</span>
              </div>
            </article>
          </div>
          <p v-if="!barang.length" class="reward-note">{{ loading ? 'Memuat katalog…' : 'Belum ada barang yang tersedia saat ini.' }}</p>
          <p v-else class="reward-note">* Poin penukaran dan stok dapat berubah sesuai pengaturan admin.</p>
        </div>
      </section>

      <!-- PERAN -->
      <section class="section">
        <div class="wrap roles-layout">
          <div v-reveal class="section-head reveal">
            <span class="eyebrow">Kerja bareng</span>
            <h2>Setiap peran bikin sistem berjalan.</h2>
            <p>Bank sampah berjalan karena siswa, petugas, dan admin saling membantu.</p>
          </div>
          <div v-reveal class="role-box reveal delay-1">
            <div class="role-tabs" role="tablist" aria-label="Pilih peran">
              <button v-for="r in peran" :id="`tab-${r.key}`" :key="r.key" role="tab" :aria-selected="peranAktif === r.key" :aria-controls="`role-${r.key}`" @click="peranAktif = r.key">{{ r.label }}</button>
            </div>
            <template v-for="r in peran" :key="r.key">
              <div v-if="peranAktif === r.key" :id="`role-${r.key}`" class="role-panel" role="tabpanel" :aria-labelledby="`tab-${r.key}`">
                <h3>{{ r.judul }}</h3><p>{{ r.isi }}</p>
                <ul><li v-for="t in r.tugas" :key="t">{{ t }}</li></ul>
              </div>
            </template>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section id="faq" class="section faq">
        <div class="wrap faq-layout">
          <div v-reveal class="section-head reveal">
            <span class="eyebrow">Masih penasaran?</span>
            <h2>Yang sering ditanyakan.</h2>
            <p>Informasi singkat sebelum kamu mulai menyetor.</p>
          </div>
          <div v-reveal class="faq-list reveal delay-1">
            <div v-for="(f, i) in faq" :key="f.q" class="faq-item">
              <button class="faq-q" :aria-expanded="faqBuka === i" @click="faqBuka = faqBuka === i ? null : i"><span>{{ f.q }}</span><span>+</span></button>
              <div class="faq-answer"><div><p>{{ f.a }}</p></div></div>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="cta">
        <div class="wrap cta-inner">
          <div>
            <span class="eyebrow">Aksi baik dimulai sekarang</span>
            <h2>Barang bekasmu bisa berarti banyak.</h2>
            <p>Mulai pilah sampah, lalu jadikan kebiasaan baik sebagai gerakan bersama.</p>
          </div>
          <router-link class="lp-btn" to="/login">Masuk ke Bank Samsul <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" /></svg></router-link>
        </div>
      </section>
    </main>

    <!-- FOOTER -->
    <footer>
      <div class="wrap">
        <div class="footer-grid">
          <!-- Brand & Visi -->
          <div class="footer-col brand-col">
            <a class="brand footer-brand" href="#top">
              <span class="brand-mark">
                <img src="/images/logo.jpeg" alt="Logo Bank Samsul" class="brand-logo-img" />
              </span>
              bank samsul.
            </a>
            <p class="footer-desc">
              Program bank sampah digital di {{ SEKOLAH }}. Membiasakan siswa memilah sampah, menjaga kebersihan lingkungan sekolah, dan menukar poin dengan hadiah bermanfaat.
            </p>
            <div class="footer-badge">
              <span class="badge-dot"></span>
              <span>Layanan Aktif Siswa & Guru</span>
            </div>
          </div>

          <!-- Navigasi -->
          <div class="footer-col">
            <h4>Navigasi</h4>
            <ul class="footer-links">
              <li v-for="l in navLinks" :key="l.href">
                <a :href="l.href">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 5l7 7-7 7" /></svg>
                  {{ l.label }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Layanan -->
          <div class="footer-col">
            <h4>Layanan</h4>
            <ul class="footer-links">
              <li>
                <router-link to="/login">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" /></svg>
                  Masuk ke Akun
                </router-link>
              </li>
              <li>
                <a href="#poin">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M16 10l-4-4-4 4" /></svg>
                  Kategori Sampah
                </a>
              </li>
              <li>
                <a href="#hadiah">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z" /></svg>
                  Katalog Hadiah
                </a>
              </li>
              <li>
                <a href="#panduan">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" /></svg>
                  Panduan Penyetoran
                </a>
              </li>
            </ul>
          </div>

          <!-- Kontak & Jam Layanan -->
          <div class="footer-col contact-col">
            <h4>Kontak & Layanan</h4>
            <ul class="footer-contact">
              <li>
                <span class="contact-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" /></svg>
                </span>
                <div>
                  <small>Lokasi</small>
                  <span>{{ SEKOLAH }}</span>
                </div>
              </li>
              <li>
                <span class="contact-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
                </span>
                <div>
                  <small>WhatsApp / Telepon</small>
                  <a href="https://wa.me/62881027655734" target="_blank" rel="noopener">+62 881-0276-55734</a>
                </div>
              </li>
              <li>
                <span class="contact-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" /></svg>
                </span>
                <div>
                  <small>Email Resmi</small>
                  <a href="mailto:Banksamsul32@gmail.com">Banksamsul32@gmail.com</a>
                </div>
              </li>
              <li>
                <span class="contact-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
                </span>
                <div>
                  <small>Jam Operasional</small>
                  <span>Senin & Rabu, 12:30 – 13:10 WIB</span>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
          <p>© {{ new Date().getFullYear() }} Bank Samsul · {{ SEKOLAH }}. Seluruh hak cipta dilindungi.</p>
          <div class="footer-bottom-right">
            <span class="eco-badge">🌱 Daur Ulang Jadi Nilai Kebaikan</span>
            <a href="#top" class="back-to-top" aria-label="Kembali ke atas">
              <span>Ke atas</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7" /></svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const SEKOLAH = 'SMPS 2 Al-Muhajirin Purwakarta'
const ART_BG = ['#e9eee3', '#f1e6d7', '#f4e7df', '#e6e9dc']

// === Konten statis (mengikuti aturan sistem) ===
const navLinks = [
  { href: '#tentang', label: 'Tentang' },
  { href: '#panduan', label: 'Panduan' },
  { href: '#poin', label: 'Poin' },
  { href: '#hadiah', label: 'Hadiah' },
  { href: '#faq', label: 'FAQ' },
]

const prinsip = [
  { no: '01 / REDUCE', judul: 'Kurangi yang tak perlu.', isi: 'Mulai dengan mengurangi barang sekali pakai dan sampah yang dibawa setiap hari.' },
  { no: '02 / REUSE', judul: 'Gunakan lagi.', isi: 'Botol, wadah, dan kertas mungkin masih punya kegunaan sebelum masuk tempat sampah.' },
  { no: '03 / RECYCLE', judul: 'Pilah untuk didaur ulang.', isi: 'Setorkan sampah terpilah kepada petugas agar bisa dikelola dengan lebih tepat.' },
]

const panduanLangkah = [
  {
    no: '01',
    judul: 'Persiapan Sampah',
    deskripsi: 'Pastikan sampah yang akan disetorkan sudah dipilah dan memenuhi kriteria berikut:',
    poinList: [
      'Kumpulkan sampah yang dapat ditabung, seperti botol plastik, gelas plastik (cup), tutup botol, wadah katering, dan kardus.',
      'Pastikan sampah dalam keadaan kosong dan bersih dari air maupun sisa makanan.',
      'Lepaskan label, stiker, dan selotip yang masih menempel, jika ada.',
      'Pisahkan tutup botol dari botolnya.'
    ]
  },
  {
    no: '02',
    judul: 'Penyetoran Sampah',
    deskripsi: 'Proses penyerahan sampah terpilah kepada petugas Bank Samsul:',
    poinList: [
      'Bawa sampah yang telah dipilah ke petugas Bank Samsul sesuai dengan jadwal penyetoran yang berlaku.',
      'Petugas akan menimbang sampah serta mencatat jenis dan berat sampah yang disetorkan.',
      'Pastikan jenis dan berat sampah telah dicatat dengan benar sebelum proses penyetoran selesai.'
    ]
  },
  {
    no: '03',
    judul: 'Perolehan Poin Sampah',
    deskripsi: 'Konversi berat sampah menjadi poin tabungan siswa/nasabah:',
    poinList: [
      'Sampah yang disetorkan akan dikonversi menjadi Poin Sampah berdasarkan jenis dan beratnya.',
      'Poin akan ditambahkan ke akun masing-masing siswa/nasabah setelah setoran selesai diproses.'
    ],
    catatan: 'Poin Sampah berbeda dengan Poin Lingkungan. Poin Sampah diperoleh melalui penyetoran sampah, sedangkan Poin Lingkungan diperoleh berdasarkan hasil penilaian kebersihan kelas dan pengelolaan sampah.'
  },
  {
    no: '04',
    judul: 'Pemantauan Poin',
    deskripsi: 'Akses portal resmi untuk memeriksa saldo dan riwayat aktivitas:',
    linkText: 'bank.smp2almuhajirin.sch.id',
    linkHref: 'https://bank.smp2almuhajirin.sch.id',
    instruksi: 'Masuk dengan username dan password yang telah diberikan petugas untuk memantau:',
    poinList: [
      'Jumlah Poin Sampah.',
      'Jumlah Poin Lingkungan.',
      'Riwayat penyetoran sampah beserta poin yang diperoleh.',
      'Riwayat perubahan poin.'
    ]
  },
  {
    no: '05',
    judul: 'Penukaran Poin dengan Barang',
    deskripsi: 'Manfaatkan poin yang telah terkumpul untuk ditukar dengan barang bermanfaat:',
    poinList: [
      'Buka menu Redeem untuk melihat daftar barang yang tersedia.',
      'Pilih barang yang diinginkan serta jenis poin yang sesuai.',
      'Tentukan jumlah barang dan tanggal pengambilan. Tanggal pengambilan paling cepat adalah 2 hari setelah permintaan diajukan.',
      'Kirim permintaan penukaran dan tunggu persetujuan dari admin.',
      'Apabila permintaan disetujui, ambil barang sesuai dengan tanggal yang telah dipilih.',
      'Pantau status permintaan penukaran melalui akun Bank Samsul.'
    ]
  },
  {
    no: '06',
    judul: 'Menjaga Kebersihan Lingkungan',
    deskripsi: 'Partisipasi aktif menjaga area sekolah tetap bersih, rapi, dan nyaman:',
    poinList: [
      'Sapu sampah yang berserakan dan pastikan lantai bersih dari kotoran.',
      'Bersihkan noda pada lantai menggunakan alat pel hingga bersih dan kering.',
      'Buang sampah yang tidak dapat ditabung ke tempat sampah yang sesuai.',
      'Rapikan kembali area penyetoran dan kembalikan alat kebersihan ke tempatnya.'
    ]
  }
]

const skalaLingkungan = [
  { nama: 'Kebersihan kelas', skala: '1–10' },
  { nama: 'Pemilahan sampah', skala: '1–5' },
  { nama: 'Perawatan tanaman', skala: '1–5' },
]

const peran = [
  { key: 'nasabah', label: 'Nasabah', judul: 'Mulai dari kebiasaanmu.', isi: 'Pantau aktivitas dan manfaatkan poin yang sudah dikumpulkan.', tugas: ['Lihat riwayat setoran dan poin', 'Pantau poin sampah & poin lingkungan', 'Ajukan atau batalkan penukaran hadiah'] },
  { key: 'petugas', label: 'Petugas', judul: 'Catat aksi baik di lapangan.', isi: 'Petugas memastikan setoran dan kegiatan lingkungan tercatat.', tugas: ['Catat setoran sampah', 'Isi survei lingkungan kelas', 'Validasi pengajuan penukaran'] },
  { key: 'admin', label: 'Admin', judul: 'Jaga semuanya tetap rapi.', isi: 'Admin mengelola katalog, pengajuan, dan rekap kegiatan bank sampah.', tugas: ['Kelola jenis sampah & poin per kg', 'Kelola barang redeem dan stok', 'Lihat dan unduh laporan Excel atau PDF'] },
]

const faq = [
  { q: 'Sampah apa saja yang diterima?', a: 'Sampah terpilah sesuai jenis yang terdaftar di bagian Poin. Daftar jenis dan poin per kg dikelola oleh admin.' },
  { q: 'Apa bedanya poin sampah dan poin lingkungan?', a: 'Poin sampah berasal dari setoran dan bisa ditukar dengan hadiah. Poin lingkungan berasal dari survei kebersihan kelas, pemilahan sampah, dan perawatan tanaman.' },
  { q: 'Kapan hadiah bisa diambil?', a: 'Tanggal pengambilan bisa dipilih mulai hari berikutnya agar petugas sempat menyiapkan barang dan memeriksa stok.' },
  { q: 'Bagaimana cara mendapatkan akun?', a: 'Akun dibuat oleh admin atau petugas sekolah. Hubungi petugas Bank Samsul untuk mendapatkan akunmu.' },
]

// === Data dari database ===
const loading = ref(true)
const stat = ref(null)
const kategori = ref([])
const barang = ref([])

const fmt = (n) => Number(n || 0).toLocaleString('id-ID')
const kategoriTeratas = computed(() => kategori.value[0])

const fotoUrl = (foto) => {
  if (!foto) return ''
  if (foto.startsWith('http://') || foto.startsWith('https://')) return foto
  if (foto.startsWith('/storage/')) return foto
  if (foto.startsWith('storage/')) return `/${foto}`
  return `/storage/${foto}`
}

const onImgError = (b) => {
  b.foto = null
}

const statistik = computed(() => [
  { value: stat.value ? fmt(stat.value.nasabah) : '—', label: 'Nasabah terdaftar' },
  { value: stat.value ? `${fmt(stat.value.berat_sampah)} kg` : '—', label: 'Sampah terkumpul' },
  { value: stat.value ? fmt(stat.value.hadiah_ditukar) : '—', label: 'Hadiah ditukarkan' },
])

const loadData = async () => {
  try {
    const { data } = await axios.get('/api/landing')
    stat.value = data.data.statistik
    kategori.value = data.data.kategori
    barang.value = data.data.barang
  } catch (err) {
    console.error('Gagal memuat data landing:', err)
  } finally {
    loading.value = false
  }
}

// === UI state ===
const scrolled = ref(false)
const menuOpen = ref(false)
const peranAktif = ref('nasabah')
const faqBuka = ref(null)

const onScroll = () => { scrolled.value = window.scrollY > 8 }
watch(menuOpen, (open) => document.body.classList.toggle('overflow-hidden', open))

// Animasi muncul saat discroll; directive dipakai karena katalog dirender setelah fetch
const observer = new IntersectionObserver((entries) => {
  entries.forEach((e) => {
    if (!e.isIntersecting) return
    e.target.classList.add('visible')
    observer.unobserve(e.target)
  })
}, { threshold: 0.14 })
const vReveal = { mounted: (el) => observer.observe(el) }

onMounted(() => {
  document.title = 'Bank Samsul — Sampah Jadi Berarti'
  document.documentElement.style.scrollBehavior = 'smooth'
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
  loadData()
})

onBeforeUnmount(() => {
  observer.disconnect()
  window.removeEventListener('scroll', onScroll)
  document.documentElement.style.scrollBehavior = ''
  document.body.classList.remove('overflow-hidden')
})
</script>

<style scoped>
.landing {
  --paper: #f6f4ec; --white: #fffef9; --forest: #173f32; --green: #28694e; --lime: #d5ee83;
  --orange: #ef7548; --ink: #182d25; --muted: #65736a; --line: #d9ded2;
  --shadow: 0 18px 50px #173f3210; --radius: 24px;
  background: var(--paper); color: var(--ink);
  font: 400 16px/1.65 "DM Sans", sans-serif; overflow-x: hidden;
}
.landing a { color: inherit; }
.landing ::selection { background: var(--lime); color: var(--forest); }
section { scroll-margin-top: 90px; }

/* === Dasar === */
.wrap { width: min(1160px, calc(100% - 48px)); margin: auto; }
.eyebrow { display: inline-flex; align-items: center; gap: 9px; text-transform: uppercase; letter-spacing: .13em; font-size: 11px; font-weight: 700; color: var(--green); }
.eyebrow::before { content: ""; width: 23px; height: 2px; background: var(--orange); }
h1, h2, h3, p { margin-top: 0; }
h1, h2, h3 { font-family: Manrope, sans-serif; letter-spacing: -.055em; font-weight: 800; }
h2 { font-size: clamp(28px, 4vw, 44px); line-height: 1.15; margin-bottom: 14px; }
h3 { letter-spacing: -.035em; line-height: 1.2; }
p { color: var(--muted); }
.section { padding: 64px 0; }
.section-head { max-width: 660px; margin-bottom: 28px; }
.section-head p { max-width: 520px; font-size: 16px; }
.muted { color: var(--muted); }

.lp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 11px; padding: 13px 22px; border: 1px solid var(--forest); border-radius: 999px; background: var(--forest); color: #fff !important; text-decoration: none; font-weight: 700; font-size: 14px; transition: transform .25s, background .25s, box-shadow .25s; }
.lp-btn:hover { transform: translateY(-3px); background: var(--green); box-shadow: 0 9px 22px #173f3220; }
.lp-btn svg { width: 17px; height: 17px; transition: transform .25s; }
.lp-btn:hover svg { transform: translateX(3px); }
.lp-btn.secondary { background: transparent; color: var(--forest) !important; border-color: var(--line); }
.lp-btn.secondary:hover { background: var(--white); border-color: var(--forest); box-shadow: none; }
.lp-btn:focus-visible, .burger:focus-visible, .faq-q:focus-visible, .role-tabs button:focus-visible { outline: 2px solid var(--orange); outline-offset: 3px; }

/* === Navbar === */
.lp-nav { position: sticky; top: 0; z-index: 50; background: #f6f4ecef; backdrop-filter: blur(16px); border-bottom: 1px solid transparent; transition: border-color .3s, box-shadow .3s; }
.lp-nav.scrolled { border-color: var(--line); box-shadow: 0 4px 24px #173f3208; }
.nav-inner { height: 64px; display: flex; align-items: center; gap: 32px; }
.brand { display: flex; align-items: center; gap: 10px; text-decoration: none; font: 800 19px Manrope, sans-serif; letter-spacing: -.06em; color: var(--forest) !important; white-space: nowrap; }
.brand-mark { width: 36px; height: 36px; background: #ffffff; border: 1px solid rgba(23,63,50,0.12); border-radius: 10px; display: grid; place-items: center; overflow: hidden; box-shadow: 0 2px 8px rgba(23,63,50,0.08); padding: 2px; }
.brand-mark .brand-logo-img { width: 100%; height: 100%; object-fit: contain; }
.brand-mark svg { width: 23px; height: 23px; }
.nav-links { display: flex; align-items: center; gap: 28px; margin-left: auto; }
.nav-links a { text-decoration: none; color: #53635a; font-size: 13px; font-weight: 600; position: relative; padding: 7px 0; }
.nav-links a::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: var(--orange); transform: scaleX(0); transform-origin: right; transition: transform .25s; }
.nav-links a:hover::after { transform: scaleX(1); transform-origin: left; }
.lp-nav .lp-btn { padding: 10px 18px; font-size: 13px; }
.burger { display: none; background: none; border: 1px solid var(--line); border-radius: 50%; width: 42px; height: 42px; color: var(--forest); }

/* === Hero === */
.hero { padding: 24px 0 36px; position: relative; }
.hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; align-items: center; gap: 36px; }
.hero-copy { padding: 0 0 12px; }
.hero h1 { font-size: clamp(40px, 5.2vw, 64px); line-height: 1.05; max-width: 660px; margin: 14px 0 18px; }
.hero h1 em { font-style: normal; color: var(--green); position: relative; z-index: 0; white-space: nowrap; }
.hero h1 em::after { content: ""; position: absolute; left: 0; right: 2%; bottom: -4px; height: 9px; background: var(--lime); z-index: -1; border-radius: 99px; transform: scaleX(0); transform-origin: left; animation: underline .65s .9s ease forwards; }
.hero-copy > p { font-size: 16px; max-width: 480px; line-height: 1.7; margin-bottom: 24px; }
.hero-actions { display: flex; align-items: center; gap: 13px; flex-wrap: wrap; }
.tiny-note { font-size: 12px; margin-top: 18px; display: flex; align-items: center; gap: 8px; color: #68766b; }
.tiny-note span { width: 8px; height: 8px; background: var(--orange); border-radius: 50%; }

.hero-art { position: relative; min-height: 440px; border-radius: 28px; background: #e5edda; overflow: hidden; isolation: isolate; }
.art-top { position: absolute; left: 28px; top: 27px; z-index: 2; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #45624c; }
.sun { position: absolute; width: 230px; height: 230px; background: var(--lime); border-radius: 50%; right: -38px; top: 45px; opacity: .95; }
.orbit { position: absolute; width: 340px; height: 340px; border: 1px solid #a8bea0; border-radius: 50%; top: 70px; left: 50%; transform: translateX(-50%); }
.orbit::before, .orbit::after { content: ""; position: absolute; border: 1px dashed #b0c2a9; border-radius: 50%; inset: 25px; }
.orbit::after { inset: 57px; }
.plant-pot { position: absolute; left: 50%; bottom: 46px; transform: translateX(-50%); width: 205px; height: 155px; background: #d17c55; clip-path: polygon(7% 0, 93% 0, 79% 100%, 21% 100%); z-index: 3; }
.plant-pot::after { content: ""; position: absolute; inset: 0; background: linear-gradient(105deg, #ffffff15, transparent 48%, #763b271c); }
.pot-rim { position: absolute; left: calc(50% - 116px); bottom: 185px; width: 232px; height: 26px; background: #b96645; border-radius: 7px; z-index: 4; }
.stem { position: absolute; bottom: 197px; left: 50%; width: 8px; height: 190px; background: var(--green); border-radius: 8px; transform: rotate(4deg); transform-origin: bottom; z-index: 2; }
.leaf { position: absolute; z-index: 3; width: 105px; height: 55px; background: #347957; border-radius: 100% 0 100% 0; }
.leaf.one { bottom: 300px; left: calc(50% - 98px); transform: rotate(15deg); }
.leaf.two { bottom: 345px; left: calc(50% - 2px); transform: rotate(74deg) scale(.92); }
.leaf.three { bottom: 255px; left: calc(50% - 2px); transform: rotate(70deg) scale(.8); background: #5a9668; }
.leaf.four { bottom: 390px; left: calc(50% - 75px); transform: rotate(5deg) scale(.62); background: #5a9668; }
.leaf::after { content: ""; position: absolute; left: 15%; top: 50%; width: 80%; height: 1px; background: #d5ee83a8; transform: rotate(-23deg); }
.float-card { position: absolute; z-index: 5; max-width: 210px; background: var(--white); border: 1px solid #ffffffa8; box-shadow: 0 12px 35px #254b3316; border-radius: 17px; padding: 14px 16px; animation: float 5s ease-in-out infinite; }
.float-card small { display: block; font-size: 10px; color: var(--muted); font-weight: 600; }
.float-card b { font: 800 18px Manrope, sans-serif; color: var(--forest); }
.fc-one { left: 22px; bottom: 78px; }
.fc-two { right: 20px; top: 155px; animation-delay: -2.4s; }
.mini-icon { display: grid; place-items: center; width: 27px; height: 27px; border-radius: 9px; background: #f7e1d5; color: #a34b2c; margin-bottom: 8px; }
.mini-icon svg { width: 16px; }
.art-caption { position: absolute; bottom: 19px; right: 22px; font-size: 10px; color: #6d806d; letter-spacing: .04em; }

.hero-bottom { display: flex; align-items: center; justify-content: space-between; gap: 20px; border-top: 1px solid var(--line); margin-top: 32px; padding-top: 18px; }
.hero-bottom p { font-size: 12px; margin: 0; }
.mini-steps { display: flex; align-items: center; gap: 13px; font-size: 12px; font-weight: 700; color: var(--forest); }
.mini-steps span { display: flex; align-items: center; gap: 7px; }
.mini-steps i { display: grid; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: var(--lime); font-style: normal; font-size: 10px; }
.mini-steps b { color: #a9b3a6; }

/* === Statistik === */
.stats { background: var(--forest); color: #fff; padding: 24px 0; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
.stat { padding: 8px 25px; border-left: 1px solid #ffffff30; }
.stat:first-child { border-left: 0; padding-left: 0; }
.stat strong { display: block; font: 800 clamp(26px, 3.5vw, 40px)/1.1 Manrope, sans-serif; letter-spacing: -.06em; color: var(--lime); }
.stat span { font-size: 12px; color: #d3dfd5; }

/* === Tentang === */
.about-grid { display: grid; grid-template-columns: .85fr 1.15fr; gap: 54px; align-items: start; }
.about-copy { position: sticky; top: 100px; }
.about-copy h2 { margin-top: 14px; }
.about-copy p { font-size: 15.5px; line-height: 1.75; }
.principles { border-top: 1px solid var(--line); }
.principle { display: grid; grid-template-columns: 56px 1fr; gap: 16px; padding: 18px 0; border-bottom: 1px solid var(--line); transition: padding .25s; }
.principle:hover { padding-left: 8px; }
.principle-no { font: 700 12px Manrope, sans-serif; color: var(--orange); padding-top: 5px; }
.principle h3 { font-size: 20px; margin: 0 0 6px; }
.principle p { font-size: 13.5px; margin: 0; max-width: 480px; }

/* === Panduan Penyetoran Sampah === */
.guide { background: #e9eee3; position: relative; }
.anchor-alias { position: absolute; top: -90px; }
.guide-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-bottom: 30px; flex-wrap: wrap; }
.guide-top .section-head { margin-bottom: 0; }
.guide-action-box { display: flex; align-items: center; }
.pdf-btn { background: var(--white); border-color: var(--line); color: var(--forest) !important; padding: 11px 20px; font-size: 13px; font-weight: 700; gap: 9px; box-shadow: 0 4px 14px rgba(23,63,50,0.05); }
.pdf-btn:hover { background: var(--forest); color: #fff !important; border-color: var(--forest); box-shadow: 0 8px 22px rgba(23,63,50,0.18); }
.pdf-btn svg { width: 16px; height: 16px; }

.guide-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
.guide-card {
  background: var(--white);
  border: 1px solid var(--line);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  transition: transform .3s, box-shadow .3s, border-color .3s;
}
.guide-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 36px rgba(23,63,50,0.08);
  border-color: #b7c7b0;
}
.guide-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.guide-step-badge {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: var(--forest);
  color: var(--lime);
  font: 800 14px Manrope, sans-serif;
  letter-spacing: -.02em;
}
.guide-step-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--green);
  background: #eef3e8;
  padding: 4px 10px;
  border-radius: 99px;
}
.guide-card-title {
  font-size: 18px;
  margin: 0 0 8px;
  color: var(--forest);
}
.guide-card-desc {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 12px;
  line-height: 1.5;
}
.guide-link-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 6px 12px;
  background: #f1f6ec;
  border: 1px solid #d4ded0;
  border-radius: 8px;
  font-size: 12px;
  color: var(--forest);
  font-weight: 600;
  margin-bottom: 10px;
}
.guide-link-pill svg { width: 14px; height: 14px; color: var(--green); flex-shrink: 0; }
.guide-link-pill a { color: var(--forest); text-decoration: none; word-break: break-all; }
.guide-link-pill a:hover { text-decoration: underline; color: var(--green); }
.guide-card-subdesc {
  font-size: 12.5px;
  color: var(--ink);
  font-weight: 600;
  margin: 0 0 10px;
}
.guide-checklist {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 13px;
  line-height: 1.55;
  color: #314238;
}
.guide-checklist li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}
.check-bullet {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #e1edd8;
  color: var(--green);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 2px;
}
.check-bullet svg { width: 11px; height: 11px; }

.guide-note-box {
  margin-top: 14px;
  padding: 12px 14px;
  background: #fdf6ec;
  border: 1px solid #f3d7ba;
  border-radius: 12px;
  display: flex;
  gap: 10px;
  align-items: flex-start;
  font-size: 12px;
  line-height: 1.5;
  color: #7a431d;
}
.note-icon {
  width: 18px;
  height: 18px;
  color: #d96924;
  flex-shrink: 0;
  margin-top: 1px;
}
.note-icon svg { width: 100%; height: 100%; }

.guide-motto-banner {
  margin-top: 36px;
  background: var(--forest);
  color: #fff;
  border-radius: 20px;
  padding: 22px 30px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 14px 34px rgba(23,63,50,0.14);
}
.motto-icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  background: var(--lime);
  color: var(--forest);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}
.motto-icon-wrap svg { width: 24px; height: 24px; }
.motto-content p {
  margin: 0;
  font-size: 15px;
  line-height: 1.6;
  color: #e5ede7;
  font-weight: 500;
}

/* === Poin === */
.points-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 24px; }
.point-card { padding: 26px 28px; border-radius: var(--radius); background: var(--white); border: 1px solid var(--line); transition: transform .3s, box-shadow .3s; }
.point-card:hover { transform: translateY(-5px); box-shadow: var(--shadow); }
.point-card.featured { background: var(--forest); color: #fff; border-color: var(--forest); }
.point-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 14px; background: var(--lime); color: var(--forest); margin: 8px 0 18px; }
.point-icon svg { width: 22px; height: 22px; }
.point-card h3 { font-size: 23px; margin-bottom: 8px; }
.point-card p { font-size: 13.5px; }
.featured p { color: #d1ded4; }
.featured .eyebrow { color: var(--lime); }
.rate-list { list-style: none; padding: 0; margin: 14px 0 0; font-size: 13.5px; }
.rate-list li { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; padding: 8px 0; border-top: 1px solid var(--line); }
.rate-list small { display: block; font-size: 11px; color: var(--muted); }
.rate-list b { color: var(--green); white-space: nowrap; }
.featured .rate-list li { border-color: #ffffff26; color: #e5ede6; }
.featured .rate-list b { color: var(--lime); }

/* === Hadiah === */
.rewards { background: var(--white); }
.rewards-top { display: flex; align-items: end; justify-content: space-between; gap: 30px; }
.rewards-top .section-head { margin-bottom: 0; }
.rewards-top > p { max-width: 280px; font-size: 13px; }
.reward-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-top: 24px; }
.reward { border: 1px solid var(--line); border-radius: 18px; overflow: hidden; background: var(--paper); transition: transform .3s, border-color .3s; }
.reward:hover { transform: translateY(-5px); border-color: #a8b99f; }
.reward-art { height: 155px; display: grid; place-items: center; position: relative; overflow: hidden; }
.reward-art::before { content: ""; position: absolute; width: 115px; height: 115px; border: 1px solid #c5d3bf; border-radius: 50%; left: 50%; top: 50%; transform: translate(-50%, -50%); }
.reward-art img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; }
.reward-art svg { width: 74px; height: 74px; position: relative; z-index: 1; transition: transform .4s; }
.reward:hover .reward-art svg { transform: rotate(-7deg) scale(1.08); }
.reward-info { padding: 14px; }
.reward-info h3 { font-size: 15px; margin: 0 0 6px; }
.reward-info span { font-size: 11px; color: var(--green); font-weight: 700; }
.reward-note { font-size: 12px; margin: 16px 0 0; }

/* === Peran === */
.roles-layout { display: grid; grid-template-columns: .7fr 1.3fr; gap: 54px; }
.roles-layout .section-head { margin-bottom: 0; }
.role-box { background: var(--white); border: 1px solid var(--line); border-radius: 20px; padding: 22px; }
.role-tabs { display: flex; gap: 6px; padding: 5px; background: var(--paper); border-radius: 99px; margin-bottom: 20px; }
.role-tabs button { flex: 1; border: 0; background: transparent; border-radius: 99px; padding: 10px 8px; font-size: 12px; font-weight: 700; color: var(--muted); cursor: pointer; transition: background .25s, color .25s; }
.role-tabs button[aria-selected="true"] { background: var(--forest); color: #fff; }
.role-panel { animation: panel-in .3s ease; }
.role-panel h3 { font-size: 21px; margin-bottom: 8px; }
.role-panel p { font-size: 13.5px; }
.role-panel ul { list-style: none; padding: 0; margin: 16px 0 0; }
.role-panel li { padding: 10px 0; border-top: 1px solid var(--line); font-size: 13.5px; display: flex; gap: 10px; }
.role-panel li::before { content: "↗"; color: var(--orange); font-weight: 700; }

/* === FAQ === */
.faq { background: #e9eee3; }
.faq-layout { display: grid; grid-template-columns: .7fr 1.3fr; gap: 54px; }
.faq-layout .section-head { margin-bottom: 0; }
.faq-list { border-top: 1px solid #cbd5c6; }
.faq-item { border-bottom: 1px solid #cbd5c6; }
.faq-q { display: flex; justify-content: space-between; align-items: center; gap: 18px; width: 100%; padding: 17px 0; border: 0; background: transparent; text-align: left; color: var(--ink); font: 700 15px Manrope, sans-serif; cursor: pointer; }
.faq-q span:last-child { font-size: 20px; font-weight: 400; transition: transform .25s; color: var(--green); }
.faq-q[aria-expanded="true"] span:last-child { transform: rotate(45deg); }
.faq-answer { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s; }
.faq-answer > div { overflow: hidden; }
.faq-answer p { font-size: 13.5px; max-width: 590px; margin: 0; }
.faq-q[aria-expanded="true"] + .faq-answer { grid-template-rows: 1fr; }
.faq-q[aria-expanded="true"] + .faq-answer p { padding: 0 24px 18px 0; }

/* === CTA & Footer === */
.cta { padding: 56px 0; background: var(--lime); position: relative; overflow: hidden; }
.cta::after { content: ""; position: absolute; width: 320px; height: 320px; border: 1px solid #9eb967; border-radius: 50%; right: 9%; top: -170px; box-shadow: 0 0 0 35px #9eb96720, 0 0 0 70px #9eb96712; }
.cta-inner { display: flex; justify-content: space-between; align-items: center; gap: 30px; position: relative; z-index: 1; }
.cta h2 { font-size: clamp(28px, 4.2vw, 48px); max-width: 670px; margin: 0; }
.cta p { color: #405b3e; margin: 10px 0 0; font-size: 15px; }
.cta .lp-btn { white-space: nowrap; }

footer {
  background: #112d23;
  color: #fff;
  padding: 56px 0 28px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.footer-grid {
  display: grid;
  grid-template-columns: 1.35fr 0.75fr 0.8fr 1.25fr;
  gap: 44px;
}
.footer-col h4 {
  font: 800 12px Manrope, sans-serif;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: var(--lime);
  margin: 0 0 18px;
}
.brand-col .footer-brand {
  color: #fff !important;
  margin-bottom: 14px;
}
.footer-desc {
  font-size: 13.5px;
  line-height: 1.7;
  color: #b5c7ba;
  margin: 0 0 18px;
  max-width: 320px;
}
.footer-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(213, 238, 131, 0.1);
  border: 1px solid rgba(213, 238, 131, 0.22);
  color: var(--lime);
  font-size: 11.5px;
  font-weight: 600;
}
.badge-dot {
  width: 7px;
  height: 7px;
  background: var(--lime);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--lime);
  animation: pulse-dot 2s infinite ease-in-out;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .4; transform: scale(.8); }
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.footer-links a {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #c9ded0;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  transition: color .2s, transform .2s;
}
.footer-links a svg {
  width: 13px;
  height: 13px;
  color: var(--lime);
  opacity: .7;
  transition: transform .2s, opacity .2s;
}
.footer-links a:hover {
  color: #fff;
  transform: translateX(3px);
}
.footer-links a:hover svg {
  opacity: 1;
  transform: translateX(2px);
}

.footer-contact {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.footer-contact li {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.contact-icon {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: grid;
  place-items: center;
  color: var(--lime);
  flex-shrink: 0;
}
.contact-icon svg {
  width: 15px;
  height: 15px;
}
.footer-contact small {
  display: block;
  font-size: 10.5px;
  color: #8da493;
  text-transform: uppercase;
  letter-spacing: .06em;
  font-weight: 600;
  margin-bottom: 2px;
}
.footer-contact span,
.footer-contact a {
  font-size: 13px;
  color: #dbe7dd;
  text-decoration: none;
  line-height: 1.4;
  word-break: break-word;
  transition: color .2s;
}
.footer-contact a:hover {
  color: var(--lime);
}

.footer-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 22px;
  margin-top: 42px;
  font-size: 12px;
  color: #9cb2a2;
}
.footer-bottom p {
  margin: 0;
  color: inherit;
}
.footer-bottom-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.eco-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #c0d3c5;
  background: rgba(255, 255, 255, 0.05);
  padding: 5px 11px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.back-to-top {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #fff;
  text-decoration: none;
  font-size: 11px;
  font-weight: 700;
  transition: background .2s, color .2s, transform .2s;
}
.back-to-top svg {
  width: 12px;
  height: 12px;
  transition: transform .2s;
}
.back-to-top:hover {
  background: var(--lime);
  color: var(--forest);
  transform: translateY(-2px);
}
.back-to-top:hover svg {
  transform: translateY(-2px);
}

/* === Animasi === */
.reveal { opacity: 0; transform: translateY(22px); transition: opacity .7s ease, transform .7s cubic-bezier(.2, .7, .2, 1); }
.reveal.visible { opacity: 1; transform: none; }
.delay-1 { transition-delay: .1s; }
.delay-2 { transition-delay: .2s; }
.delay-3 { transition-delay: .3s; }
@keyframes underline { to { transform: scaleX(1); } }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
@keyframes panel-in { from { opacity: 0; transform: translateY(7px); } to { opacity: 1; transform: none; } }

/* === Responsif === */
@media (max-width: 900px) {
  .nav-inner { height: 60px; gap: 16px; }
  .nav-links { gap: 16px; }
  .nav-links a { font-size: 12px; }
  .hero { padding: 20px 0 28px; }
  .hero-grid { grid-template-columns: 1fr .9fr; gap: 24px; }
  .hero h1 { font-size: clamp(34px, 5vw, 48px); }
  .hero-art { min-height: 380px; }
  .orbit { width: 280px; height: 280px; }
  .sun { width: 170px; height: 170px; }
  .plant-pot { width: 160px; height: 120px; }
  .pot-rim { width: 180px; left: calc(50% - 90px); bottom: 145px; }
  .stem { height: 150px; bottom: 158px; }
  .leaf { width: 80px; height: 44px; }
  .leaf.one { bottom: 240px; left: calc(50% - 75px); }
  .leaf.two { bottom: 275px; left: calc(50% - 1px); }
  .leaf.three { bottom: 210px; }
  .leaf.four { bottom: 310px; }
  .about-grid, .roles-layout, .faq-layout { gap: 36px; }
  .guide-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .guide-top { align-items: flex-start; flex-direction: column; gap: 16px; }
  .reward-grid { grid-template-columns: repeat(2, 1fr); }
  .section { padding: 52px 0; }
  .footer-grid { grid-template-columns: 1fr 1fr; gap: 36px; }
}

@media (max-width: 680px) {
  .wrap { width: min(100% - 32px, 500px); }
  .section { padding: 40px 0; }
  .nav-inner { height: 56px; }
  .nav-links { display: none; position: fixed; top: 56px; left: 0; right: 0; padding: 14px 20px 20px; background: var(--paper); border-bottom: 1px solid var(--line); flex-direction: column; align-items: stretch; gap: 0; }
  .nav-links.open { display: flex; }
  .nav-links a { padding: 11px 0; font-size: 14px; }
  .nav-links a::after { display: none; }
  .lp-nav .lp-btn { display: none; }
  .burger { display: block; margin-left: auto; width: 40px; height: 40px; }
  .hero { padding: 16px 0 24px; }
  .hero-grid { grid-template-columns: 1fr; gap: 24px; }
  .hero-copy { padding: 0; }
  .hero h1 { font-size: clamp(30px, 8.5vw, 42px); line-height: 1.12; margin: 10px 0 12px; }
  .hero-copy > p { font-size: 14.5px; margin-bottom: 18px; line-height: 1.6; }
  .hero-actions .lp-btn { padding: 12px 18px; font-size: 13px; }
  .tiny-note { margin-top: 14px; font-size: 11px; }
  .hero-art { min-height: 340px; border-radius: 20px; }
  .orbit { width: 250px; height: 250px; }
  .sun { width: 140px; height: 140px; right: -20px; top: 30px; }
  .plant-pot { width: 140px; height: 105px; bottom: 35px; }
  .pot-rim { width: 156px; left: calc(50% - 78px); bottom: 130px; height: 22px; }
  .stem { height: 130px; bottom: 140px; }
  .leaf { width: 70px; height: 38px; }
  .leaf.one { bottom: 210px; left: calc(50% - 65px); }
  .leaf.two { bottom: 240px; left: calc(50% - 1px); }
  .leaf.three { bottom: 180px; }
  .leaf.four { bottom: 270px; left: calc(50% - 50px); }
  .float-card { padding: 10px 12px; border-radius: 14px; }
  .float-card b { font-size: 15px; }
  .fc-one { left: 14px; bottom: 45px; }
  .fc-two { right: 14px; top: 90px; }
  .hero-bottom { align-items: flex-start; flex-direction: column; margin-top: 22px; padding-top: 14px; gap: 10px; }
  .mini-steps { gap: 8px; font-size: 10px; }
  .stats { padding: 18px 0; }
  .stats-grid { gap: 8px; }
  .stat { padding: 6px 10px; }
  .stat:first-child { padding-left: 0; }
  .stat strong { font-size: 24px; }
  .stat span { font-size: 10px; line-height: 1.35; display: block; margin-top: 4px; }
  .about-grid, .roles-layout, .faq-layout { grid-template-columns: 1fr; gap: 24px; }
  .about-copy { position: static; }
  .guide-grid { grid-template-columns: 1fr; gap: 14px; }
  .guide-card { padding: 18px; border-radius: 16px; }
  .guide-card-title { font-size: 17px; }
  .guide-motto-banner { padding: 18px; flex-direction: column; align-items: flex-start; gap: 14px; }
  .motto-content p { font-size: 13.5px; }
  .principle { grid-template-columns: 36px 1fr; gap: 10px; padding: 14px 0; }
  .principle h3 { font-size: 18px; }
  .principle p { font-size: 13px; }
  .points-grid { grid-template-columns: 1fr; gap: 12px; margin-top: 16px; }
  .point-card { padding: 18px; }
  .point-card h3 { font-size: 20px; }
  .point-icon { width: 38px; height: 38px; margin: 4px 0 14px; }
  .rewards-top { display: block; }
  .rewards-top > p { margin-top: 10px; font-size: 12px; }
  .reward-grid { gap: 10px; margin-top: 16px; }
  .reward-art { height: 130px; }
  .reward-art svg { width: 58px; height: 58px; }
  .reward-info { padding: 10px; }
  .reward-info h3 { font-size: 13px; }
  .role-box { padding: 16px; }
  .role-tabs { margin-bottom: 16px; }
  .role-tabs button { font-size: 11px; padding: 8px 4px; }
  .role-panel h3 { font-size: 18px; }
  .role-panel li { padding: 8px 0; font-size: 13px; }
  .faq-q { padding: 14px 0; font-size: 14px; }
  .faq-answer p { font-size: 13px; }
  .cta { padding: 40px 0; }
  .cta-inner { align-items: flex-start; flex-direction: column; gap: 20px; }
  .cta h2 { font-size: clamp(24px, 7vw, 34px); }
  .cta::after { right: -190px; }
  footer { padding: 40px 0 24px; }
  .footer-grid { grid-template-columns: 1fr; gap: 28px; }
  .footer-desc { max-width: 100%; }
  .footer-bottom { flex-direction: column; align-items: flex-start; gap: 14px; margin-top: 28px; padding-top: 18px; }
  .footer-bottom-right { width: 100%; justify-content: space-between; }
  .section-head { margin-bottom: 20px; }
}

@media (prefers-reduced-motion: reduce) {
  .landing *, .landing *::before, .landing *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
  .reveal { opacity: 1; transform: none; }
}
</style>
