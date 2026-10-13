<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\KategoriSampah;
use App\Models\BarangRedeem;
use App\Models\Penukaran;
use App\Models\RiwayatPoin;
use App\Models\SetoranSampah;
use Carbon\Carbon;

class BankSampahPoinTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private User $nasabah1;
    private User $nasabah2;
    private KategoriSampah $kategori;
    private BarangRedeem $barang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama'            => 'Admin Test',
            'username'        => 'admin_test',
            'password'        => bcrypt('password'),
            'role'            => 'admin',
            'poin_sampah'     => 0,
            'poin_lingkungan' => 0,
        ]);

        $this->petugas = User::create([
            'nama'            => 'Petugas Test',
            'username'        => 'petugas_test',
            'password'        => bcrypt('password'),
            'role'            => 'petugas',
            'poin_sampah'     => 0,
            'poin_lingkungan' => 0,
        ]);

        $this->nasabah1 = User::create([
            'nama'            => 'Nasabah 1',
            'username'        => 'nasabah_1',
            'password'        => bcrypt('password'),
            'role'            => 'nasabah',
            'poin_sampah'     => 100,
            'poin_lingkungan' => 50,
        ]);

        $this->nasabah2 = User::create([
            'nama'            => 'Nasabah 2',
            'username'        => 'nasabah_2',
            'password'        => bcrypt('password'),
            'role'            => 'nasabah',
            'poin_sampah'     => 20,
            'poin_lingkungan' => 10,
        ]);

        $this->kategori = KategoriSampah::create([
            'nama'        => 'Kardus Bekas',
            'kategori'    => 'Anorganik',
            'satuan'      => 'kg',
            'poin_per_kg' => 10,
        ]);

        $this->barang = BarangRedeem::create([
            'nama'        => 'Tumbler Hijau',
            'deskripsi'   => 'Tumbler ramah lingkungan',
            'harga_poin'  => 50,
            'jenis_poin'  => 'sampah',
            'stok'        => 5,
            'aktif'       => true,
        ]);
    }

    /**
     * 1. Setoran menambah poin_sampah dan mencatat riwayat_poin
     */
    public function test_setoran_menambah_poin_sampah_dan_mencatat_riwayat_poin(): void
    {
        $response = $this->actingAs($this->petugas)->postJson('/api/transaksi/setoran', [
            'user_id'     => $this->nasabah1->id,
            'kategori_id' => $this->kategori->id,
            'berat'       => 3.5,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status'     => 'success',
            'poin_dapat' => 35, // floor(3.5 * 10) = 35
        ]);

        // Cek poin_sampah bertambah (100 + 35 = 135)
        $this->nasabah1->refresh();
        $this->assertEquals(135, $this->nasabah1->poin_sampah);

        // Cek riwayat_poin tercatat
        $this->assertDatabaseHas('riwayat_poin', [
            'user_id'    => $this->nasabah1->id,
            'jenis_poin' => 'sampah',
            'jumlah'     => 35,
            'sumber'     => 'setoran',
        ]);

        // Cek endpoint /api/me/setoran mengembalikan nama jenis_sampah
        $meSetoran = $this->actingAs($this->nasabah1)->getJson('/api/me/setoran');
        $meSetoran->assertStatus(200);
        $meSetoran->assertJsonFragment([
            'jenis_sampah' => 'Kardus Bekas',
        ]);
    }

    /**
     * 2. Penukaran gagal jika tanggal_ambil kurang dari H+1
     */
    public function test_penukaran_gagal_jika_tanggal_ambil_kurang_dari_h_plus_1(): void
    {
        $hariIni = Carbon::now('Asia/Jakarta')->toDateString();

        $response = $this->actingAs($this->nasabah1)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $hariIni, // Hari ini, harusnya minimal besok (H+1)
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_ambil']);
    }

    /**
     * 3. Penukaran gagal jika poin kurang
     */
    public function test_penukaran_gagal_jika_poin_kurang(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        // Nasabah 2 hanya punya 20 poin sampah, barang butuh 50
        $response = $this->actingAs($this->nasabah2)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $besok,
        ]);

        $response->assertStatus(422);
    }

    /**
     * 4. Penukaran gagal jika stok habis
     */
    public function test_penukaran_gagal_jika_stok_habis(): void
    {
        $this->barang->update(['stok' => 0]);
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        $response = $this->actingAs($this->nasabah1)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $besok,
        ]);

        $response->assertStatus(422);
    }

    /**
     * 5. Penukaran sukses mengurangi poin dan stok; penolakan mengembalikannya
     */
    public function test_penukaran_sukses_mengurangi_poin_dan_stok_serta_penolakan_mengembalikannya(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        // Nasabah 1 (100 poin, stok 5) menukar 1 barang (50 poin)
        $response = $this->actingAs($this->nasabah1)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $besok,
        ]);

        $response->assertStatus(201);
        $penukaranId = $response->json('data.id');

        // Poin dan stok terpotong
        $this->nasabah1->refresh();
        $this->barang->refresh();
        $this->assertEquals(50, $this->nasabah1->poin_sampah);
        $this->assertEquals(4, $this->barang->stok);

        // Admin menolak penukaran
        $tolakRes = $this->actingAs($this->admin)->postJson("/api/admin/penukaran/{$penukaranId}/validasi", [
            'status'        => 'ditolak',
            'catatan_admin' => 'Barang rusak',
        ]);
        $tolakRes->assertStatus(200);

        // Poin dan stok kembali utuh
        $this->nasabah1->refresh();
        $this->barang->refresh();
        $this->assertEquals(100, $this->nasabah1->poin_sampah);
        $this->assertEquals(5, $this->barang->stok);

        // Cek riwayat pembatalan tercatat
        $this->assertDatabaseHas('riwayat_poin', [
            'user_id'    => $this->nasabah1->id,
            'jenis_poin' => 'sampah',
            'jumlah'     => 50,
            'sumber'     => 'pembatalan',
        ]);
    }

    /**
     * 6. Nasabah dapat membatalkan penukaran miliknya sendiri selama status masih menunggu
     */
    public function test_nasabah_dapat_membatalkan_penukaran_sendiri_dan_poin_kembali(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        $response = $this->actingAs($this->nasabah1)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $besok,
        ]);
        $response->assertStatus(201);
        $penukaranId = $response->json('data.id');

        // Batal oleh nasabah sendiri
        $batalRes = $this->actingAs($this->nasabah1)->postJson("/api/me/penukaran/{$penukaranId}/batal");
        $batalRes->assertStatus(200);

        $this->nasabah1->refresh();
        $this->barang->refresh();
        $this->assertEquals(100, $this->nasabah1->poin_sampah);
        $this->assertEquals(5, $this->barang->stok);
    }

    /**
     * 7. Nasabah tidak bisa membuat/melihat penukaran atau riwayat milik nasabah lain
     */
    public function test_nasabah_tidak_bisa_melihat_atau_membatalkan_penukaran_milik_nasabah_lain(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        // Nasabah 1 buat penukaran
        $response = $this->actingAs($this->nasabah1)->postJson('/api/me/penukaran', [
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'tanggal_ambil' => $besok,
        ]);
        $penukaranId = $response->json('data.id');

        // Nasabah 2 mencoba membatalkan penukaran milik nasabah 1 -> gagal 404
        $batalRes = $this->actingAs($this->nasabah2)->postJson("/api/me/penukaran/{$penukaranId}/batal");
        $batalRes->assertStatus(404);

        // Nasabah 2 melihat daftar penukaran saya -> tidak ada data nasabah 1
        $listRes = $this->actingAs($this->nasabah2)->getJson('/api/me/penukaran');
        $listRes->assertStatus(200);
        $this->assertEmpty($listRes->json());

        // Nasabah mencoba akses riwayat user_id (IDOR) -> ditolak (403 Forbidden)
        $idorRes = $this->actingAs($this->nasabah1)->getJson("/api/transaksi/{$this->nasabah2->id}");
        $idorRes->assertStatus(403);
    }

    /**
     * 8. Hanya admin yang bisa mengelola barang dan memvalidasi penukaran
     */
    public function test_hanya_admin_yang_bisa_mengelola_barang_dan_memvalidasi_penukaran(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        $penukaran = Penukaran::create([
            'user_id'       => $this->nasabah1->id,
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'poin_dipakai'  => 50,
            'jenis_poin'    => 'sampah',
            'tanggal_ambil' => $besok,
            'status'        => 'menunggu',
        ]);

        // Petugas mencoba CRUD barang -> 403 Forbidden
        $tambahBarangPetugas = $this->actingAs($this->petugas)->postJson('/api/admin/barang-redeem', [
            'nama'       => 'Barang Ilegal',
            'harga_poin' => 10,
            'jenis_poin' => 'sampah',
            'stok'       => 10,
        ]);
        $tambahBarangPetugas->assertStatus(403);

        // Petugas mencoba validasi penukaran -> 403 Forbidden
        $validasiPetugas = $this->actingAs($this->petugas)->postJson("/api/admin/penukaran/{$penukaran->id}/validasi", [
            'status' => 'disetujui',
        ]);
        $validasiPetugas->assertStatus(403);

        // Admin berhasil validasi penukaran -> 200 OK
        $validasiAdmin = $this->actingAs($this->admin)->postJson("/api/admin/penukaran/{$penukaran->id}/validasi", [
            'status' => 'disetujui',
        ]);
        $validasiAdmin->assertStatus(200);

        // Petugas boleh menandai diambil setelah disetujui -> 200 OK
        $ambilPetugas = $this->actingAs($this->petugas)->postJson("/api/admin/penukaran/{$penukaran->id}/ambil");
        $ambilPetugas->assertStatus(200);
        $this->assertEquals('diambil', $penukaran->fresh()->status);
    }

    /**
     * 9. Endpoint notifikasi mengembalikan jumlah 'menunggu' yang benar
     */
    public function test_endpoint_notifikasi_mengembalikan_jumlah_menunggu_yang_benar(): void
    {
        $besok = Carbon::now('Asia/Jakarta')->addDays(2)->toDateString();

        Penukaran::create([
            'user_id'       => $this->nasabah1->id,
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'poin_dipakai'  => 50,
            'jenis_poin'    => 'sampah',
            'tanggal_ambil' => $besok,
            'status'        => 'menunggu',
            'dilihat_admin' => false,
        ]);

        Penukaran::create([
            'user_id'       => $this->nasabah2->id,
            'barang_id'     => $this->barang->id,
            'jumlah'        => 1,
            'poin_dipakai'  => 50,
            'jenis_poin'    => 'sampah',
            'tanggal_ambil' => $besok,
            'status'        => 'menunggu',
            'dilihat_admin' => false,
        ]);

        $res = $this->actingAs($this->admin)->getJson('/api/admin/notifikasi');
        $res->assertStatus(200);
        $res->assertJson([
            'status'          => 'success',
            'jumlah_menunggu' => 2,
        ]);
        $this->assertCount(2, $res->json('daftar_baru'));

        // Tandai sudah dilihat
        $bacaRes = $this->actingAs($this->admin)->postJson('/api/admin/notifikasi/baca');
        $bacaRes->assertStatus(200);

        // Setelah dibaca, daftar_baru kosong tetapi jumlah_menunggu tetap 2
        $resAfter = $this->actingAs($this->admin)->getJson('/api/admin/notifikasi');
        $this->assertEquals(2, $resAfter->json('jumlah_menunggu'));
        $this->assertCount(0, $resAfter->json('daftar_baru'));
    }

    /** @test */
    public function survey_lingkungan_menambah_poin_lingkungan_dan_mencatat_riwayat_poin(): void
    {
        $poinAwal = $this->nasabah1->poin_lingkungan;
        $foto = \Illuminate\Http\UploadedFile::fake()->image('survey.jpg');

        $res = $this->actingAs($this->admin)->postJson('/api/survey-lingkungan', [
            'nasabah_id' => $this->nasabah1->id,
            'kategori'   => 'kebersihan_kelas',
            'rating'     => 8,
            'foto'       => $foto,
            'keterangan' => 'Kelas sangat rapi dan bersih',
        ]);

        $res->assertStatus(201);
        $this->nasabah1->refresh();

        // Poin lingkungan harus bertambah +8
        $this->assertEquals($poinAwal + 8, $this->nasabah1->poin_lingkungan);

        // Harus tercatat di riwayat_poin
        $this->assertDatabaseHas('riwayat_poin', [
            'user_id'    => $this->nasabah1->id,
            'jenis_poin' => 'lingkungan',
            'jumlah'     => 8,
            'sumber'     => 'penilaian',
        ]);

        // Hapus survey -> poin harus di-rollback
        $surveyId = $res->json('data.id');
        $delRes = $this->actingAs($this->admin)->deleteJson("/api/survey-lingkungan/{$surveyId}");
        $delRes->assertStatus(200);

        $this->nasabah1->refresh();
        $this->assertEquals($poinAwal, $this->nasabah1->poin_lingkungan);
    }

    /** @test */
    public function landing_api_mengembalikan_data_publik_tanpa_autentikasi(): void
    {
        $res = $this->getJson('/api/landing');

        $res->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'statistik' => ['nasabah', 'berat_sampah', 'hadiah_ditukar'],
                    'kategori',
                    'barang',
                ],
            ]);
    }
}
