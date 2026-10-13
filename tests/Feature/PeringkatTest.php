<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\SetoranSampah;
use App\Models\KategoriSampah;

class PeringkatTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nasabahJuara1;
    private User $nasabahJuara2;
    private User $nasabahJuara3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama'            => 'Admin Bank Sampah',
            'username'        => 'admin_test',
            'password'        => bcrypt('password'),
            'role'            => 'admin',
            'poin_sampah'     => 0,
            'poin_lingkungan' => 0,
        ]);

        $this->nasabahJuara1 = User::create([
            'nama'            => 'Siti Juara 1',
            'username'        => 'siti_1',
            'password'        => bcrypt('password'),
            'role'            => 'nasabah',
            'poin_sampah'     => 500,
            'poin_lingkungan' => 100, // Total 600
        ]);

        $this->nasabahJuara2 = User::create([
            'nama'            => 'Budi Juara 2',
            'username'        => 'budi_2',
            'password'        => bcrypt('password'),
            'role'            => 'nasabah',
            'poin_sampah'     => 300,
            'poin_lingkungan' => 100, // Total 400
        ]);

        $this->nasabahJuara3 = User::create([
            'nama'            => 'Ahmad Juara 3',
            'username'        => 'ahmad_3',
            'password'        => bcrypt('password'),
            'role'            => 'nasabah',
            'poin_sampah'     => 150,
            'poin_lingkungan' => 50, // Total 200
        ]);
    }

    public function test_admin_bisa_melihat_leaderboard_dan_podium_lengkap()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/peringkat');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'summary' => [
                    'total_nasabah'  => 3,
                    'poin_tertinggi' => 600,
                ],
            ]);

        $leaderboard = $response->json('leaderboard');
        $this->assertCount(3, $leaderboard);
        $this->assertEquals('siti_1', $leaderboard[0]['username']);
        $this->assertEquals(1, $leaderboard[0]['rank']);
        $this->assertEquals('budi_2', $leaderboard[1]['username']);
        $this->assertEquals(2, $leaderboard[1]['rank']);
        $this->assertEquals('ahmad_3', $leaderboard[2]['username']);
        $this->assertEquals(3, $leaderboard[2]['rank']);
    }

    public function test_nasabah_posisi_tengah_dapat_melihat_peringkat_sebelum_dan_sesudahnya()
    {
        // Budi Juara 2:
        // Sebelumnya: Siti Juara 1 (600 poin, gap 200 poin)
        // Sesudahnya: Ahmad Juara 3 (200 poin, gap 200 poin)
        $response = $this->actingAs($this->nasabahJuara2, 'sanctum')
            ->getJson('/api/me/peringkat');

        $response->assertStatus(200)
            ->assertJson([
                'status'        => 'success',
                'my_rank'       => 2,
                'total_nasabah' => 3,
            ]);

        $data = $response->json();

        // Cek Siswa Sebelumnya (Siti Juara 1)
        $this->assertNotNull($data['sebelum']);
        $this->assertEquals('siti_1', $data['sebelum']['username']);
        $this->assertEquals(1, $data['sebelum']['rank']);
        $this->assertEquals(200, $data['sebelum']['selisih_poin']);

        // Cek Siswa Sesudahnya (Ahmad Juara 3)
        $this->assertNotNull($data['sesudah']);
        $this->assertEquals('ahmad_3', $data['sesudah']['username']);
        $this->assertEquals(3, $data['sesudah']['rank']);
        $this->assertEquals(200, $data['sesudah']['selisih_poin']);

        // Motivasi
        $this->assertStringContainsString('Siti Juara 1', $data['motivasi']);
    }

    public function test_nasabah_peringkat_1_tidak_memiliki_siswa_sebelumnya()
    {
        $response = $this->actingAs($this->nasabahJuara1, 'sanctum')
            ->getJson('/api/me/peringkat');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'my_rank' => 1,
            ]);

        $data = $response->json();
        $this->assertNull($data['sebelum']);
        $this->assertNotNull($data['sesudah']);
        $this->assertEquals('budi_2', $data['sesudah']['username']);
        $this->assertStringContainsString('Puncak', $data['motivasi']);
    }

    public function test_nasabah_peringkat_terakhir_tidak_memiliki_siswa_sesudahnya()
    {
        $response = $this->actingAs($this->nasabahJuara3, 'sanctum')
            ->getJson('/api/me/peringkat');

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'my_rank' => 3,
            ]);

        $data = $response->json();
        $this->assertNotNull($data['sebelum']);
        $this->assertNull($data['sesudah']);
    }
}
