# 📘 Dokumentasi Alur Sistem: Bank Samsul (Bank Sampah Digital)
### SMP 2 Al-Muhajirin Purwakarta

Dokumen ini menjelaskan secara menyeluruh arsitektur alur kerja, logika bisnis, dan siklus transaksi yang berjalan pada sistem **Bank Samsul** (*GreenSaver*).

---

## 📑 Daftar Isi
1. [Ringkasan Eksekutif & Entitas Sistem](#1-ringkasan-eksekutif--entitas-sistem)
2. [Matriks Peran & Wewenang (Role Matrix)](#2-matriks-peran--wewenang-role-matrix)
3. [Peta Alur Kerja Global (Global Business Flow)](#3-peta-alur-kerja-global-global-business-flow)
4. [Alur 1: Autentikasi & Navigasi Antarmuka](#4-alur-1-autentikasi--navigasi-antarmuka)
5. [Alur 2: Manajemen & Onboarding Data Nasabah](#5-alur-2-manajemen--onboarding-data-nasabah)
6. [Alur 3: Manajemen Master Kategori & Tarif Sampah](#6-alur-3-manajemen-master-kategori--tarif-sampah)
7. [Alur 4: Siklus Transaksi Setoran Sampah (Deposit)](#7-alur-4-siklus-transaksi-setoran-sampah-deposit)
8. [Alur 5: Siklus Penarikan Saldo & Workflow Validasi (Withdrawal)](#8-alur-5-siklus-penarikan-saldo--workflow-validasi-withdrawal)
9. [Alur 6: Monitoring & Survey Lingkungan Sekolah](#9-alur-6-monitoring--survey-lingkungan-sekolah)
10. [Alur 7: Sistem Gamifikasi & Penilaian Poin Kebersihan](#10-alur-7-sistem-gamifikasi--penilaian-poin-kebersihan)
11. [Alur 8: Pelaporan, Analitik & Ekspor Dokumen](#11-alur-8-pelaporan-analitik--ekspor-dokumen)
12. [Integritas Data & Database Transaction](#12-integritas-data--database-transaction)

---

## 1. Ringkasan Eksekutif & Entitas Sistem

Sistem **Bank Samsul** dirancang untuk mengelola dua pilar utama dalam lingkungan sekolah:
1. **Pilar Finansial & Edukasi Lingkungan (Bank Sampah)**: Menampung setoran sampah bernilai ekonomis, mengonversinya menjadi saldo tabungan riil, serta memberikan apresiasi berupa poin gamifikasi.
2. **Pilar Monitoring & Tata Kelola Lingkungan Hidup**: Memantau kebersihan ruang kelas, pemilahan sampah harian, dan perawatan tanaman sekolah berbasis dokumentasi foto dan sistem skoring.

### Tiga Entitas Utama:
- **👑 Administrator**: Pemegang kendali penuh operasional, validasi finansial, konfigurasi data master, penilaian poin, serta audit pelaporan.
- **👮 Petugas**: Pelaksana lapangan yang melayani penimbangan setoran sampah nasabah, registrasi nasabah, dan pelaksanaan survey lingkungan di kelas.
- **🎓 Nasabah (Siswa / Perwakilan Kelas)**: Pihak yang menyetorkan sampah, menabung saldo, mengumpulkan poin prestasi lingkungan, dan memantau status kebersihan kelasnya.

---

## 2. Matriks Peran & Wewenang (Role Matrix)

| Fitur / Modul | Admin | Petugas | Nasabah |
| :--- | :---: | :---: | :---: |
| Akses Landing Page Publik & Informasi 3R | ✅ | ✅ | ✅ |
| Autentikasi Login & Logout | ✅ | ✅ | ✅ |
| Akses Dashboard Statistik Global | ✅ | ✅ | ❌ |
| Akses Portal Mandiri Nasabah | ❌ *(Redirect)* | ❌ *(Redirect)* | ✅ |
| Tambah / Edit / Hapus Nasabah | ✅ | ✅ | ❌ |
| Impor Nasabah Massal (Excel) & Download Template | ✅ | ✅ | ❌ |
| Kelola Kategori & Tarif Sampah (CRUD) | ✅ | ❌ | ❌ |
| Input Transaksi Setoran Sampah | ✅ | ✅ | ❌ |
| Penarikan Saldo Langsung (di Loket) | ✅ | ✅ | ❌ |
| Pengajuan Penarikan Mandiri (Status Pending) | ❌ | ❌ | ✅ |
| Validasi Permohonan Penarikan (Setujui / Tolak) | ✅ | ❌ | ❌ |
| Input Survey Lingkungan + Upload Foto | ✅ | ✅ | ❌ |
| Hapus Data Survey Lingkungan | ✅ | ✅ | ❌ |
| Lihat Riwayat Survey Lingkungan Kelas Sendiri | ❌ | ❌ | ✅ |
| Input Penilaian Poin Kebersihan | ✅ | ✅ | ❌ |
| Analitik Laporan & Visualisasi Grafik | ✅ | ✅ | ❌ |
| Ekspor Laporan ke Format Excel (.xlsx) | ✅ | ✅ | ❌ |
| Ekspor Laporan ke Format PDF Resmi | ✅ | ✅ | ❌ |

---

## 3. Peta Alur Kerja Global (Global Business Flow)

```mermaid
flowchart TD
    Start([Mulai Operasional]) --> Auth[Login Pengguna via Sanctum Token]
    
    Auth --> RoleCheck{Cek Role Pengguna}
    
    %% Alur Nasabah
    RoleCheck -- Nasabah --> PortalNasabah[Buka /nasabah-dashboard]
    PortalNasabah --> ViewStat[Cek Saldo, Poin & Riwayat Transaksi]
    PortalNasabah --> TarikMandiri[Ajukan Penarikan Saldo]
    TarikMandiri --> PendingStatus[(Tersimpan: Status 'pending')]
    PortalNasabah --> ViewSurvey[Cek Riwayat Survey Kelas Sendiri]

    %% Alur Petugas
    RoleCheck -- Petugas --> PetugasDash[Akses Menu Operasional]
    PetugasDash --> SetorSampah[Catat Setoran Sampah Timbangan]
    PetugasDash --> SurveyAct[Input Survey Lingkungan + Upload Foto]
    PetugasDash --> NilaiPoin[Input Penilaian Poin Kebersihan]

    %% Alur Admin
    RoleCheck -- Admin --> AdminDash[Akses Penuh /dashboard]
    AdminDash --> MasterKat[Kelola Kategori & Harga Sampah]
    AdminDash --> UserMgmt[Manajemen Nasabah & Impor Excel]
    AdminDash --> ValidasiTarik{Validasi Penarikan Pending}
    ValidasiTarik -- Setujui --> PotongSaldo[Kurangi Saldo & Catat Riwayat Mutasi]
    ValidasiTarik -- Tolak --> BatalTarik[Tolak Permohonan Saldo Utuh]
    AdminDash --> Analitik[Pantau Analitik Grafik & Ekspor Excel/PDF]

    %% Interaksi Setoran ke Nasabah
    SetorSampah --> HitungNilai[Hitung: Berat x Harga/Kg]
    HitungNilai --> HitungPoin[Hitung: 1 Poin per 1 Kg]
    HitungPoin --> MutasiDB[(Update Saldo & Poin Nasabah)]
    MutasiDB -.-> ViewStat

    NilaiPoin --> UpdatePoin[(Tambah Poin Rating ke Nasabah)]
    UpdatePoin -.-> ViewStat
```

---

## 4. Alur 1: Autentikasi & Navigasi Antarmuka

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna
    participant View as Vue Frontend (LoginView)
    participant Pinia as useAuthStore (State)
    participant API as Laravel API (/api/login)
    participant Router as Vue Router Guard

    User->>View: Masukkan Username & Password
    View->>API: POST /api/login { username, password }
    API-->>API: Verifikasi Hash Password (Bcrypt)
    alt Kredensial Valid
        API-->>View: 200 OK + Bearer Token + Data User
        View->>Pinia: Simpan Token & Data User di LocalStorage
        View->>Router: Redirect ke rute '/dashboard'
        Router->>Router: Evaluasi Role Guard:
        Note over Router: Jika role == 'nasabah' -> redirect ke '/nasabah-dashboard'<br/>Jika role == 'admin'/'petugas' -> tetap di '/dashboard'
        Router-->>User: Tampilkan Halaman Sesuai Hak Akses
    else Kredensial Salah
        API-->>View: 401 Unauthorized / Pesan Error
        View-->>User: SweetAlert2 Error Notifikasi
    end
```

### Mekanisme Pengamanan:
- Setiap permintaan HTTP berikutnya menyertakan header:  
  `Authorization: Bearer <token>`
- **Axios Response Interceptor**: Jika backend mengembalikan respons HTTP `401 Unauthorized` (misal token kadaluwarsa), store secara otomatis menghapus sesi lokal dan me-redirect antarmuka ke halaman `/login`.

---

## 5. Alur 2: Manajemen & Onboarding Data Nasabah

Ada dua metode registrasi nasabah baru:

```mermaid
flowchart LR
    subgraph Metode 1: Manual
        A1[Buka Modal Tambah Nasabah] --> A2[Input Nama, Username, Password]
        A2 --> A3[POST /api/nasabah]
        A3 --> A4[(Disimpan ke Database dengan Saldo=0, Poin=0)]
    end

    subgraph Metode 2: Impor Excel Massal
        B1[Unduh Template Excel: GET /api/nasabah/template] --> B2[Isi Data Siswa di Spreadsheet]
        B2 --> B3[Unggah Berkas .xlsx/.csv: POST /api/nasabah/import]
        B3 --> B4[Class NasabahImport Membaca Baris]
        B4 --> B5{Username Sudah Ada?}
        B5 -- Ya / Kosong --> B6[Lewati Baris / Skipped Count]
        B5 -- Tidak --> B7[Buat User Nasabah Baru / Imported Count]
    end
```

- **Pencarian & Pagination**: Data nasabah pada tabel admin dilengkapi penyaringan nama secara *real-time* (*client-side reactive filter*) serta pembagian halaman (*pagination*) agar tetap ringan saat memuat ratusan data siswa.

---

## 6. Alur 3: Manajemen Master Kategori & Tarif Sampah

Kategori sampah menjadi acuan nilai konversi saat transaksi setoran:
1. **Admin** membuka menu **Jenis Sampah** (`/kategori`).
2. Menentukan atribut jenis sampah:
   - **Nama Sampah**: Contoh *Kardus Bekas*, *Botol Plastik PET*, *Kertas HVS*, *Sisa Makanan*.
   - **Kategori**: `Organik` atau `Anorganik`.
   - **Satuan**: Standar berat (`kg`).
   - **Harga per Satuan (Rp)**: Tarif pembelian per 1 kilogram.
3. Data tersimpan di tabel `kategori_sampah` dan otomatis menjadi opsi pilihan pada form setoran transaksi.

---

## 7. Alur 4: Siklus Transaksi Setoran Sampah (Deposit)

Ini adalah proses inti konversi sampah fisik menjadi aset tabungan digital dan perolehan poin prestasi.

```mermaid
sequenceDiagram
    autonumber
    actor Siswa as Siswa / Nasabah
    actor Petugas as Petugas Bank Sampah
    participant Frontend as TransaksiView (Vue)
    participant Controller as TransaksiController (/transaksi/setoran)
    participant DB as MySQL Database

    Siswa->>Petugas: Membawa sampah terpilah ke bank sampah
    Petugas->>Petugas: Menimbang berat sampah fisik (misal: 3.5 kg)
    Petugas->>Frontend: Pilih Nama Nasabah, Jenis Sampah & Masukkan Berat
    Frontend->>Controller: POST /api/transaksi/setoran { user_id, kategori_id, berat }
    
    activate Controller
    Controller->>DB: Ambil harga_per_kg dari KategoriSampah
    Note over Controller: Rumus Saldo = berat * harga_per_kg<br/>Rumus Poin = floor(berat) (1 Poin / 1 Kg)
    
    Controller->>DB: BEGIN TRANSACTION
    Controller->>DB: 1. INSERT ke tabel `setoran_sampah`
    Controller->>DB: 2. UPDATE `users` (saldo += total, poin += poinDapat)
    Controller->>DB: 3. INSERT ke tabel `riwayat_transaksi` (jenis: 'Setoran')
    Controller->>DB: COMMIT TRANSACTION
    deactivate Controller

    Controller-->>Frontend: 200 OK { status: 'success', total: Rp XXX }
    Frontend-->>Petugas: SweetAlert2 Konfirmasi Berhasil
    Petugas-->>Siswa: Saldo bertambah & poin tercatat langsung di akun
```

---

## 8. Alur 5: Siklus Penarikan Saldo & Workflow Validasi (Withdrawal)

Sistem membedakan alur penarikan menjadi 2 skenario:

```mermaid
stateDiagram-v2
    [*] --> InisiasiPenarikan

    state InisiasiPenarikan {
        PilihanA: Di Loket oleh Petugas/Admin
        PilihanB: Mandiri oleh Nasabah via Portal
    }

    PilihanA --> CekSaldoA: Validasi Saldo Cukup?
    CekSaldoA --> DisetujuiLangsung: Ya
    CekSaldoA --> GagalSaldo: Tidak (Error 400)

    DisetujuiLangsung --> PotongSaldoOtomatis: Status 'disetujui'
    PotongSaldoOtomatis --> CatatRiwayat: Masuk RiwayatTransaksi
    CatatRiwayat --> Selesai

    PilihanB --> CekSaldoB: Validasi Saldo Cukup?
    CekSaldoB --> MasukPending: Ya (Status 'pending')
    CekSaldoB --> GagalSaldo: Tidak (Error 400)

    MasukPending --> ReviewAdmin: Tampil di /validasi-penarikan
    ReviewAdmin --> KeputusanAdmin{Admin Menilai}
    
    KeputusanAdmin --> DisetujuiAdmin: Klik 'Setujui'
    DisetujuiAdmin --> PotongSaldoOtomatis
    
    KeputusanAdmin --> DitolakAdmin: Klik 'Tolak'
    DitolakAdmin --> StatusDitolak: Status 'ditolak' (Saldo tidak berubah)
    StatusDitolak --> Selesai
```

### Rincian Skenario Penarikan:
1. **Penarikan Langsung (Petugas / Admin)**:
   - Digunakan saat siswa datang langsung ke loket dan mencairkan uang tunai di tempat.
   - Saldo langsung terpotong saat transaksi dikirim, status penarikan langsung tercatat `disetujui`, dan riwayat mutasi otomatis terbit.
2. **Pengajuan Mandiri (Nasabah)**:
   - Siswa menekan tombol **Ajukan Penarikan** pada `NasabahDashboardView`.
   - Menginput nominal yang diinginkan (minimal Rp 1.000).
   - Record penarikan dibuat dengan status `pending` (saldo belum terpotong).
   - Pengajuan muncul pada menu **Validasi Penarikan** (`ValidasiPenarikanView`) milik Admin.
3. **Eksekusi Validasi oleh Admin**:
   - Jika **Disetujui**: Saldo nasabah didebet sebesar nominal penarikan, status penarikan diubah menjadi `disetujui`, dan data mutasi dicatat pada `riwayat_transaksi`.
   - Jika **Ditolak**: Status diubah menjadi `ditolak`, saldo nasabah tetap utuh tanpa potongan.

---

## 9. Alur 6: Monitoring & Survey Lingkungan Sekolah

Selain tabungan sampah, sistem berfungsi sebagai pengawas sanitasi dan kebersihan kelas.

```mermaid
flowchart TD
    A[Petugas/Admin Melakukan Inspeksi Kelas] --> B[Buka Halaman /survey-lingkungan]
    B --> C[Pilih Kelas/Nasabah Target]
    C --> D[Pilih Kategori Indikator]
    
    D --> E1[Kategori: Pemilahan Sampah]
    D --> E2[Kategori: Kebersihan Kelas]
    D --> E3[Kategori: Perawatan Tanaman]

    E1 --> F1{Status Penilaian}
    F1 -->|Sesuai| S1[Pilih Status: sesuai]
    F1 -->|Tidak Sesuai| S2[Pilih Status: tidak_sesuai]

    E2 --> F2{Status Penilaian}
    F2 -->|Bersih| S3[Pilih Status: bersih]
    F2 -->|Kotor| S4[Pilih Status: tidak_bersih]

    E3 --> F3{Status Penilaian}
    F3 -->|Disiram| S5[Pilih Status: disiram]
    F3 -->|Kering/Layu| S6[Pilih Status: tidak_disiram]

    S1 & S2 & S3 & S4 & S5 & S6 --> G[Ambil Foto Bukti Lapangan]
    G --> H[Kirim Form + Upload Berkas Foto max 5MB]
    H --> I[Backend Simpan Foto ke public/storage/survey]
    I --> J[(Database Simpan Record survey_lingkungan)]
    J --> K[Otomatis Tampil di Rekapitulasi Laporan & Dashboard Siswa]
```

- **Transparansi Siswa**: Siswa/kelas dapat melihat foto dan status hasil survey kelas mereka sendiri melalui menu riwayat di dashboard nasabah.

---

## 10. Alur 7: Sistem Gamifikasi & Penilaian Poin Kebersihan

Untuk memotivasi kepedulian lingkungan, sistem menerapkan sistem gamifikasi berupa akumulasi poin:

### 1. Sumber Perolehan Poin:
- **Setoran Sampah**: Setiap kelipatan 1 kg sampah menghasilkan **+1 Poin**.
- **Penilaian Kebersihan Kelas**: Rating berkala (skala 1 - 10) dari tim penilai.
- **Penilaian Pengelolaan Sampah**: Rating berkala (skala 1 - 5) dari tim penilai.

### 2. Logika Pemrosesan Penilaian:
```text
Admin/Petugas memasukkan rating ->
Sistem membuat record di tabel `penilaian_poin` ->
Sistem menambahkan poin sebesar angka rating ke kolom `users.poin` (dalam DB Transaction) ->
Siswa dapat melihat total poin reward di kartu dashboard nasabah
```

---

## 11. Alur 8: Pelaporan, Analitik & Ekspor Dokumen

Modul Laporan (`LaporanView`) menyediakan wawasan menyeluruh terhadap performa lingkungan dan sirkulasi kas bank sampah.

```mermaid
flowchart TD
    A[Buka Halaman /laporan] --> B[Pilih Periode Laporan]
    B --> B1[Preset: Hari Ini / Minggu Ini / Bulan Ini]
    B --> B2[Kustom Tanggal: start_date s/d end_date]
    
    B1 & B2 --> C[GET /api/laporan/bulan?start_date=...&end_date=...]
    C --> D[Backend Mengolah Agregasi Query Database]
    
    D --> E1[Kartu Metrik: Total Kas Masuk, Total Kas Keluar, Nasabah Aktif]
    D --> E2[Chart.js 1: Grafik Batang Total Berat per Jenis Sampah]
    D --> E3[Chart.js 2: Grafik Lingkaran Proporsi Transaksi per Nasabah]
    D --> E4[Chart.js 3: Grafik Komparasi Sampah Organik vs Anorganik]
    D --> E5[Leaderboard: Pemenang Setoran Terbanyak Organik & Anorganik]
    D --> E6[Rekapitulasi Survey Lingkungan per Kategori]

    A --> F{Tindakan Ekspor}
    F -- Klik Export Excel --> G[Download File .xlsx via Maatwebsite Excel]
    F -- Klik Export PDF --> H[Download Berkas PDF Resmi via DomPDF]
```

---

## 12. Integritas Data & Database Transaction

Untuk menjamin tidak terjadinya ketidakcocokan saldo (*inconsistency data*), operasi finansial dan mutasi poin di backend dibungkus dalam blok `DB::transaction`:

1. **Setoran Sampah**:
   - Jika insert record setoran gagal, penambahan saldo dan poin dibatalkan (*rollback*).
   - Jika pencatatan mutasi riwayat transaksi gagal, seluruh setoran dibatalkan (*rollback*).
2. **Validasi Penarikan Saldo**:
   - Pengecekan saldo dilakukan kembali saat validasi disetujui (mencegah penarikan melebihi sisa saldo terkini).
   - Pemotongan saldo dan insert riwayat mutasi diproses secara atomik dalam satu transaksi database.
3. **Penilaian Poin**:
   - Pencatatan log penilaian dan kenaikan poin nasabah diproses secara atomik.

---

*Dokumen ini merupakan referensi resmi operasional dan pengembangan arsitektur sistem **Bank Samsul - SMP 2 Al-Muhajirin Purwakarta**.*
