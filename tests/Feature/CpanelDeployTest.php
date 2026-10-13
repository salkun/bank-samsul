<?php

namespace Tests\Feature;

use Tests\TestCase;

class CpanelDeployTest extends TestCase
{
    public function test_akses_tanpa_key_ditolak_dengan_status_403()
    {
        $response = $this->get('/cpanel');
        $response->assertStatus(403);
        $response->assertSee('Akses Dibatasi');
    }

    public function test_akses_dengan_key_salah_ditolak()
    {
        $response = $this->get('/cpanel?key=kuncisalah123');
        $response->assertStatus(403);
    }

    public function test_dashboard_dapat_dibuka_dengan_deploy_key_yang_benar()
    {
        $response = $this->get('/cpanel?key=samsul2026');
        $response->assertStatus(200);
        $response->assertSee('cPanel Deployment &amp; Artisan Runner', false);
        $response->assertSee('Jalankan Migrate');
        $response->assertSee('Storage Link');
        $response->assertSee('Bersihkan Cache');
    }

    public function test_route_clear_cache_berhasil_dijalankan()
    {
        $response = $this->get('/cpanel/clear?key=samsul2026');
        $response->assertStatus(200);
        $response->assertSee('Hasil: Bersihkan Cache (Clear All)');
    }

    public function test_route_storage_link_berhasil_dijalankan()
    {
        $response = $this->get('/cpanel/storage-link?key=samsul2026');
        $response->assertStatus(200);
        $response->assertSee('Hasil: php artisan storage:link');
    }

    public function test_route_status_migration_berhasil_dijalankan()
    {
        $response = $this->get('/cpanel/status?key=samsul2026');
        $response->assertStatus(200);
        $response->assertSee('Hasil: php artisan migrate:status');
    }
}
