<template>
  <div>
    <!-- Hero Section -->
    <section class="hero" id="hero">
      <div class="container">
        <div class="hero-content">
          <div class="hero-badge">
            <span>♻️</span>
            <span>SMPS2 Almuhajirin Purwakarta</span>
          </div>

          <h1 class="hero-title">Bank Samsul</h1>

          <p class="hero-description">
            Kelola sampah dengan bijak, wujudkan lingkungan sekolah yang bersih dan sehat.
            Bersama kita ciptakan masa depan yang lebih hijau!
          </p>

          <div class="hero-buttons">
            <a href="#about" class="btn btn-primary">Mulai Sekarang</a>
            <a href="#benefits" class="btn btn-outline">Pelajari Lebih Lanjut</a>
            <router-link to="/login" class="btn btn-outline-light">Login</router-link>
          </div>
        </div>
      </div>
      <div class="scroll-indicator" @click="scrollToSection('about')">↓</div>
    </section>

    <!-- About -->
    <section class="about" id="about">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Apa itu Bank Samsul?</h2>
          <p class="section-description">
            Bank Sampah (Bank Samsul) adalah program pengelolaan sampah di SMPS2 Almuhajirin Purwakarta
            yang mengajarkan siswa untuk memilah, mengumpulkan, dan menabung sampah yang memiliki nilai ekonomis.
          </p>
        </div>

        <div class="features-grid">
          <div class="feature-card" v-for="f in features" :key="f.title">
            <div class="feature-icon">{{ f.icon }}</div>
            <h3 class="feature-title">{{ f.title }}</h3>
            <p class="feature-description">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits -->
    <section class="benefits" id="benefits">
      <div class="container">
        <div class="benefits-content">
          <div>
            <h2 class="section-title" style="text-align: left;">Manfaat Bank Sampah</h2>
            <p class="section-description" style="text-align: left; margin-bottom: 2rem;">
              Bank Samsul memberikan banyak manfaat tidak hanya bagi lingkungan,
              tetapi juga bagi perkembangan karakter dan pengetahuan siswa.
            </p>

            <ul class="benefits-list">
              <li v-for="b in manfaat" :key="b" class="benefit-item">
                <span class="check-icon">✓</span>
                <span>{{ b }}</span>
              </li>
            </ul>
          </div>

          <div class="benefits-visual">
            <div class="recycle-visual">
              <div class="recycle-icon">♻️</div>
              <div class="recycle-text">
                Reduce<br />
                Reuse<br />
                Recycle
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta" id="cta">
      <div class="container">
        <div class="cta-card">
          <h2 class="cta-title">Mari Bergabung dengan Bank Samsul!</h2>
          <p class="cta-description">
            Jadilah bagian dari gerakan peduli lingkungan di SMPS2 Almuhajirin Purwakarta.
            Bersama kita ciptakan masa depan yang lebih hijau dan berkelanjutan.
          </p>
          <div class="cta-buttons">
            <router-link to="/login" class="btn btn-white">Login Sekarang</router-link>
            <a href="#footer" class="btn btn-outline">Hubungi Kami</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="footer">
      <div class="container">
        <div class="footer-grid">
          <div>
            <div class="footer-title">
              <span>♻️</span>
              <span>Bank Samsul</span>
            </div>
            <p class="footer-text">
              Bank Sampah SMPS2 Almuhajirin Purwakarta - Wujudkan lingkungan sekolah yang bersih dan sehat.
            </p>
          </div>

          <div>
            <h4 class="footer-title">Kontak</h4>
            <p class="footer-text">
              📍 SMPS2 Almuhajirin, Purwakarta<br />
              📞 +62 857-2297-5551<br />
              ✉️ banksamsul@smps2almuhajirin.sch.id
            </p>
          </div>

          <div>
            <h4 class="footer-title">Jam Operasional</h4>
            <p class="footer-text">
              Senin - Jumat<br />
              <strong>07:00 - 15:00 WIB</strong><br />
              <small>Sesuai jam sekolah SMPS2 Almuhajirin Purwakarta</small>
            </p>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; 2025 Bank Samsul - SMPS2 Almuhajirin Purwakarta. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from "vue";

const features = ref([
  { icon: "🌿", title: "Ramah Lingkungan", desc: "Mengurangi sampah yang berakhir di TPA dan menciptakan lingkungan sekolah yang lebih bersih." },
  { icon: "👥", title: "Edukatif", desc: "Mengajarkan siswa tentang pentingnya pengelolaan sampah dan tanggung jawab lingkungan." },
  { icon: "📈", title: "Berkelanjutan", desc: "Mengubah sampah menjadi nilai ekonomis sambil menjaga kelestarian lingkungan." },
]);

const manfaat = ref([
  "Mengurangi volume sampah yang dibuang ke TPA",
  "Menciptakan lingkungan sekolah yang lebih bersih dan sehat",
  "Mengajarkan siswa tentang tanggung jawab lingkungan",
  "Mengubah sampah menjadi tabungan yang bermanfaat",
  "Membangun karakter peduli lingkungan sejak dini",
  "Berkontribusi dalam pelestarian lingkungan hidup",
]);

const scrollToSection = (id) => {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: "smooth" });
};
</script>

<style>
@import "../assets/landing.css";
</style>
