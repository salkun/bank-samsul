<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CpanelDeployController extends Controller
{
    /**
     * Ambil secret deploy key dari .env atau fallback default.
     */
    private function getDeployKey(): string
    {
        return env('DEPLOY_KEY', 'samsul2026');
    }

    /**
     * Verifikasi otorisasi akses menggunakan token/key atau session.
     */
    private function isAuthorized(Request $request): bool
    {
        $expectedKey = $this->getDeployKey();
        $providedKey = $request->query('key') ?? $request->input('key');

        if ($providedKey && hash_equals($expectedKey, (string) $providedKey)) {
            $request->session()->put('cpanel_auth', true);
            return true;
        }

        return (bool) $request->session()->get('cpanel_auth', false);
    }

    /**
     * Tampilkan form otentikasi jika key belum dimasukkan / salah.
     */
    private function unauthorizedResponse(Request $request, ?string $action = null)
    {
        $actionUrl = $action ? url("/cpanel/{$action}") : url('/cpanel');

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dibatasi - cPanel Deployment Utility</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; max-width: 440px; width: 100%; padding: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); text-align: center; }
        .icon { font-size: 2.8rem; margin-bottom: 12px; }
        h2 { margin: 0 0 8px; font-size: 1.35rem; color: #f1f5f9; }
        p { color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0 0 20px; }
        .input-group { margin-bottom: 16px; text-align: left; }
        label { display: block; font-size: 0.78rem; font-weight: 600; color: #cbd5e1; margin-bottom: 6px; }
        input[type="password"], input[type="text"] { width: 100%; box-sizing: border-box; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; font-size: 0.95rem; outline: none; }
        input:focus { border-color: #10b981; }
        button { width: 100%; background: #10b981; color: #ffffff; border: none; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: 0.15s ease; }
        button:hover { background: #059669; }
        .hint { margin-top: 16px; font-size: 0.75rem; color: #64748b; background: #0f172a; padding: 10px; border-radius: 6px; border: 1px dashed #334155; }
        code { color: #34d399; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔒</div>
        <h2>cPanel Deployment Utility</h2>
        <p>Akses ke fitur migrasi & artisan dibatasi demi keamanan database sistem Anda.</p>
        <form method="GET" action="' . htmlspecialchars($actionUrl) . '">
            <div class="input-group">
                <label for="key">Kunci Akses (Deploy Key):</label>
                <input type="password" id="key" name="key" placeholder="Masukkan deploy key..." required autofocus>
            </div>
            <button type="submit">Buka Akses Deployment</button>
        </form>
        <div class="hint">
            Petunjuk: Gunakan key default <code>samsul2026</code> atau atur nilai <code>DEPLOY_KEY</code> pada file <code>.env</code> Anda.
        </div>
    </div>
</body>
</html>';

        return response($html, 403);
    }

    /**
     * Dashboard interaktif cPanel Deployment.
     * GET /cpanel atau GET /cpanel-deploy
     */
    public function dashboard(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request);
        }

        $key = $this->getDeployKey();

        return $this->renderConsolePage(
            title: 'cPanel Deployment & Artisan Runner',
            subtitle: 'Alat bantu otomatisasi migrasi, pembuatan storage link, dan pembersihan cache tanpa SSH terminal.',
            activeKey: $key,
            output: 'Silakan pilih tindakan yang ingin dijalankan di bawah ini.'
        );
    }

    /**
     * Jalankan php artisan migrate --force
     * GET /cpanel/migrate
     */
    public function migrate(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'migrate');
        }

        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            $status = $exitCode === 0 ? 'SUCCESS' : 'WARNING';
        } catch (\Throwable $e) {
            $output = "Terjadi kesalahan saat migrate:\n" . $e->getMessage() . "\n" . $e->getTraceAsString();
            $status = 'ERROR';
        }

        return $this->renderConsolePage(
            title: 'Hasil: php artisan migrate --force',
            subtitle: 'Menjalankan semua file migration yang belum dieksekusi ke database.',
            activeKey: $this->getDeployKey(),
            output: $output,
            status: $status,
            command: 'php artisan migrate --force'
        );
    }

    /**
     * Buat storage:link (beserta penanganan symlink cPanel)
     * GET /cpanel/storage-link
     */
    public function storageLink(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'storage-link');
        }

        $logs = [];
        $target = storage_path('app/public');
        $link = public_path('storage');

        // Pastikan folder target storage/app/public sudah ada
        if (!is_dir($target)) {
            @mkdir($target, 0755, true);
            $logs[] = "✓ Dibuat folder target: {$target}";
        }

        // Cek link yang sudah ada
        if (file_exists($link) || is_link($link)) {
            if (is_link($link)) {
                @unlink($link);
                $logs[] = "ℹ Symlink storage lama yang ada di public/storage telah dibersihkan.";
            } else {
                $logs[] = "ℹ public/storage sudah ada sebagai direktori.";
            }
        }

        try {
            $exitCode = Artisan::call('storage:link');
            $artisanOut = trim(Artisan::output());
            $logs[] = $artisanOut ?: "Artisan storage:link selesai (Exit code: {$exitCode})";
            $status = 'SUCCESS';
        } catch (\Throwable $e) {
            // Fallback manual symlink jika exec artisan diblokir shared hosting
            if (function_exists('symlink')) {
                $success = @symlink($target, $link);
                if ($success) {
                    $logs[] = "✓ Berhasil membuat symlink manual: {$target} -> {$link}";
                    $status = 'SUCCESS';
                } else {
                    $logs[] = "✕ Gagal membuat symlink manual: " . error_get_last()['message'] ?? 'Unknown error';
                    $status = 'ERROR';
                }
            } else {
                $logs[] = "✕ Eksepsi artisan: " . $e->getMessage();
                $status = 'ERROR';
            }
        }

        // Cek hasil akhir
        $isLinked = is_link($link) || (file_exists($link) && is_dir($link));
        $logs[] = $isLinked
            ? "✓ Verifikasi: public/storage siap digunakan untuk upload & foto!"
            : "✕ Peringatan: public/storage belum terhubung. Periksa permission cPanel Anda.";

        return $this->renderConsolePage(
            title: 'Hasil: php artisan storage:link',
            subtitle: 'Menghubungkan folder storage/app/public ke public/storage agar foto reward & survey dapat diakses browser.',
            activeKey: $this->getDeployKey(),
            output: implode("\n", $logs),
            status: $status,
            command: 'php artisan storage:link'
        );
    }

    /**
     * Bersihkan semua cache (optimize:clear, config, route, view, cache)
     * GET /cpanel/clear
     */
    public function clear(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'clear');
        }

        $logs = [];

        try {
            Artisan::call('optimize:clear');
            $logs[] = trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "optimize:clear error: " . $e->getMessage();
        }

        try {
            Artisan::call('cache:clear');
            $logs[] = "cache:clear -> " . trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "cache:clear error: " . $e->getMessage();
        }

        try {
            Artisan::call('config:clear');
            $logs[] = "config:clear -> " . trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "config:clear error: " . $e->getMessage();
        }

        try {
            Artisan::call('route:clear');
            $logs[] = "route:clear -> " . trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "route:clear error: " . $e->getMessage();
        }

        try {
            Artisan::call('view:clear');
            $logs[] = "view:clear -> " . trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "view:clear error: " . $e->getMessage();
        }

        $output = implode("\n", array_filter($logs));

        return $this->renderConsolePage(
            title: 'Hasil: Bersihkan Cache (Clear All)',
            subtitle: 'Membersihkan cache konfigurasi, routing, view compiled, dan application cache.',
            activeKey: $this->getDeployKey(),
            output: $output ?: 'Semua cache aplikasi berhasil dibersihkan.',
            status: 'SUCCESS',
            command: 'php artisan optimize:clear'
        );
    }

    /**
     * Jalankan Semua Tindakan Setup 1-Klik (Storage Link + Clear + Migrate)
     * GET /cpanel/setup-all
     */
    public function setupAll(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'setup-all');
        }

        $logs = [];
        $logs[] = "==================================================";
        $logs[] = "1. MENJALANKAN STORAGE:LINK";
        $logs[] = "==================================================";
        $target = storage_path('app/public');
        $link = public_path('storage');
        if (!is_dir($target)) {
            @mkdir($target, 0755, true);
        }
        if (file_exists($link) && is_link($link)) {
            @unlink($link);
        }
        try {
            Artisan::call('storage:link');
            $logs[] = trim(Artisan::output()) ?: "storage:link berhasil dieksekusi.";
        } catch (\Throwable $e) {
            $logs[] = "storage:link note: " . $e->getMessage();
        }

        $logs[] = "\n==================================================";
        $logs[] = "2. BERSIHKAN CACHE APLIKASI";
        $logs[] = "==================================================";
        try {
            Artisan::call('optimize:clear');
            $logs[] = trim(Artisan::output());
        } catch (\Throwable $e) {
            $logs[] = "optimize:clear error: " . $e->getMessage();
        }

        $logs[] = "\n==================================================";
        $logs[] = "3. MENJALANKAN DATABASE MIGRATION";
        $logs[] = "==================================================";
        try {
            Artisan::call('migrate', ['--force' => true]);
            $logs[] = trim(Artisan::output());
            $status = 'SUCCESS';
        } catch (\Throwable $e) {
            $logs[] = "migrate error: " . $e->getMessage();
            $status = 'ERROR';
        }

        $logs[] = "\n==================================================";
        $logs[] = "PROSES SETUP LENGKAP SELESAI";
        $logs[] = "==================================================";

        return $this->renderConsolePage(
            title: 'Hasil: Setup Lengkap 1-Klik',
            subtitle: 'Menjalankan Storage Link + Clear All Cache + Database Migration secara berurutan.',
            activeKey: $this->getDeployKey(),
            output: implode("\n", $logs),
            status: $status,
            command: 'storage:link && optimize:clear && migrate --force'
        );
    }

    /**
     * Jalankan db:seed --force
     * GET /cpanel/seed
     */
    public function seed(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'seed');
        }

        try {
            $exitCode = Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();
            $status = $exitCode === 0 ? 'SUCCESS' : 'WARNING';
        } catch (\Throwable $e) {
            $output = "Terjadi kesalahan saat seeder:\n" . $e->getMessage();
            $status = 'ERROR';
        }

        return $this->renderConsolePage(
            title: 'Hasil: php artisan db:seed --force',
            subtitle: 'Mengisi database dengan data master dan akun awal.',
            activeKey: $this->getDeployKey(),
            output: $output ?: 'Database seeder selesai dijalankan.',
            status: $status,
            command: 'php artisan db:seed --force'
        );
    }

    /**
     * Cek status migrasi database
     * GET /cpanel/status
     */
    public function status(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse($request, 'status');
        }

        try {
            Artisan::call('migrate:status');
            $output = Artisan::output();
            $status = 'SUCCESS';
        } catch (\Throwable $e) {
            $output = "Gagal membaca status migration:\n" . $e->getMessage();
            $status = 'ERROR';
        }

        return $this->renderConsolePage(
            title: 'Hasil: php artisan migrate:status',
            subtitle: 'Melihat daftar status migrasi (Ran / Pending).',
            activeKey: $this->getDeployKey(),
            output: $output,
            status: $status,
            command: 'php artisan migrate:status'
        );
    }

    /**
     * Render halaman konsol deployment yang rapi dan responsif.
     */
    private function renderConsolePage(
        string $title,
        string $subtitle,
        string $activeKey,
        string $output = '',
        string $status = 'INFO',
        string $command = ''
    ) {
        $keyParam = '?key=' . urlencode($activeKey);
        $urlMigrate = url('/cpanel/migrate' . $keyParam);
        $urlStorage = url('/cpanel/storage-link' . $keyParam);
        $urlClear   = url('/cpanel/clear' . $keyParam);
        $urlSetup   = url('/cpanel/setup-all' . $keyParam);
        $urlSeed    = url('/cpanel/seed' . $keyParam);
        $urlStatus  = url('/cpanel/status' . $keyParam);
        $urlHome    = url('/');

        $statusColor = match ($status) {
            'SUCCESS' => '#10b981',
            'ERROR'   => '#ef4444',
            'WARNING' => '#f59e0b',
            default   => '#38bdf8',
        };

        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - Bank Samsul cPanel</title>
    <style>
        :root {
            --bg: #0b1120;
            --card: #1e293b;
            --border: #334155;
            --text: #f8fafc;
            --muted: #94a3b8;
            --emerald: #10b981;
            --emerald-hover: #059669;
            --amber: #f59e0b;
            --blue: #3b82f6;
            --red: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 24px 16px 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            max-width: 960px;
            width: 100%;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            font-size: 1.8rem;
            width: 44px;
            height: 44px;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }
        .brand-sub {
            font-size: 0.78rem;
            color: var(--muted);
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-key {
            font-size: 0.75rem;
            background: #0f172a;
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 999px;
            color: #34d399;
            font-family: monospace;
        }
        .btn-home {
            background: #334155;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 8px;
            transition: 0.15s ease;
        }
        .btn-home:hover { background: #475569; }

        /* Actions Grid */
        .actions-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .actions-title {
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            margin-bottom: 14px;
        }
        .btn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .action-btn {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-decoration: none;
            padding: 14px 16px;
            border-radius: 10px;
            background: #0f172a;
            border: 1.5px solid var(--border);
            color: #ffffff;
            transition: all 0.18s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            border-color: #64748b;
            background: #1e293b;
        }
        .action-btn.btn-setup {
            border-color: var(--emerald);
            background: linear-gradient(135deg, #064e3b 0%, #0f172a 100%);
        }
        .action-btn.btn-setup:hover {
            border-color: #34d399;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);
        }
        .btn-lbl {
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-desc {
            font-size: 0.75rem;
            color: var(--muted);
            line-height: 1.35;
        }

        /* Console Output Box */
        .terminal-card {
            background: #020617;
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .terminal-header {
            background: #0f172a;
            border-bottom: 1px solid var(--border);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .terminal-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
        }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }
        .terminal-info {
            font-size: 0.82rem;
            color: var(--muted);
            font-family: monospace;
        }
        .terminal-status {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            color: ' . $statusColor . ';
            border: 1px solid ' . $statusColor . ';
        }
        .terminal-body {
            padding: 20px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 0.88rem;
            line-height: 1.6;
            color: #e2e8f0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 520px;
            overflow-y: auto;
        }
        .cmd-line {
            color: #38bdf8;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #1e293b;
        }

        /* Documentation & Quick Guide */
        .guide-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            margin-top: 24px;
            font-size: 0.85rem;
            color: var(--muted);
            line-height: 1.6;
        }
        .guide-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .guide-card ul {
            padding-left: 20px;
            margin-top: 6px;
        }
        .guide-card li { margin-bottom: 4px; }
        .guide-card code {
            background: #0f172a;
            color: #34d399;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <header class="header">
            <div class="brand">
                <div class="brand-icon">⚙</div>
                <div>
                    <h1 class="brand-title">cPanel Deployment Utility</h1>
                    <span class="brand-sub">Bank Samsul (SMP 2 Al-Muhajirin) - Artisan Web Runner</span>
                </div>
            </div>
            <div class="header-actions">
                <span class="badge-key">Key: ' . htmlspecialchars($activeKey) . '</span>
                <a href="' . htmlspecialchars($urlHome) . '" class="btn-home">Buka Web Utama ↗</a>
            </div>
        </header>

        <!-- ACTION BUTTONS -->
        <section class="actions-card">
            <h2 class="actions-title">Tindakan Cepat (Klik untuk Mengeksekusi)</h2>
            <div class="btn-grid">
                <!-- 1. Setup All -->
                <a href="' . htmlspecialchars($urlSetup) . '" class="action-btn btn-setup">
                    <span class="btn-lbl">⚡ Setup Lengkap 1-Klik</span>
                    <span class="btn-desc">Jalankan storage:link + clear cache + migrate --force sekaligus.</span>
                </a>

                <!-- 2. Migrate -->
                <a href="' . htmlspecialchars($urlMigrate) . '" class="action-btn">
                    <span class="btn-lbl">🚀 Jalankan Migrate</span>
                    <span class="btn-desc">Eksekusi migrasi database (<code>migrate --force</code>).</span>
                </a>

                <!-- 3. Storage Link -->
                <a href="' . htmlspecialchars($urlStorage) . '" class="action-btn">
                    <span class="btn-lbl">🔗 Storage Link</span>
                    <span class="btn-desc">Hubungkan folder upload foto (<code>storage:link</code>).</span>
                </a>

                <!-- 4. Clear Cache -->
                <a href="' . htmlspecialchars($urlClear) . '" class="action-btn">
                    <span class="btn-lbl">🧹 Bersihkan Cache</span>
                    <span class="btn-desc">Reset config, route, view & cache (<code>optimize:clear</code>).</span>
                </a>

                <!-- 5. Seeder -->
                <a href="' . htmlspecialchars($urlSeed) . '" class="action-btn">
                    <span class="btn-lbl">🌱 Jalankan Seeder</span>
                    <span class="btn-desc">Isi data awal & akun demo (<code>db:seed --force</code>).</span>
                </a>

                <!-- 6. Status Migration -->
                <a href="' . htmlspecialchars($urlStatus) . '" class="action-btn">
                    <span class="btn-lbl">📊 Cek Status DB</span>
                    <span class="btn-desc">Periksa status tabel migration (<code>migrate:status</code>).</span>
                </a>
            </div>
        </section>

        <!-- TERMINAL CONSOLE -->
        <section class="terminal-card">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <span class="dot dot-red"></span>
                    <span class="dot dot-yellow"></span>
                    <span class="dot dot-green"></span>
                </div>
                <div class="terminal-info">' . htmlspecialchars($title) . '</div>
                <div class="terminal-status">' . htmlspecialchars($status) . '</div>
            </div>
            <div class="terminal-body">';

        if ($command) {
            $html .= '<div class="cmd-line">$ ' . htmlspecialchars($command) . '</div>';
        }

        $html .= htmlspecialchars($output ?: 'Tidak ada pesan output.') . '</div>
        </section>

        <!-- GUIDE -->
        <section class="guide-card">
            <h3 class="guide-title">💡 Petunjuk Pemakaian di cPanel (Shared Hosting):</h3>
            <ul>
                <li><strong>Tanpa SSH Terminal:</strong> Cukup buka URL <code>' . url('/cpanel') . '?key=' . htmlspecialchars($activeKey) . '</code> di browser Anda kapan saja setelah upload file ke hosting.</li>
                <li><strong>Langsung Jalankan via URL Browser:</strong>
                    <ul>
                        <li>Migrate: <code>' . $urlMigrate . '</code></li>
                        <li>Storage Link: <code>' . $urlStorage . '</code></li>
                        <li>Clear Cache: <code>' . $urlClear . '</code></li>
                        <li>Setup 1-Klik: <code>' . $urlSetup . '</code></li>
                    </ul>
                </li>
                <li><strong>Keamanan:</strong> Kunci deploy dapat diubah sewaktu-waktu di file <code>.env</code> hosting dengan menambahkan parameter <code>DEPLOY_KEY=kunci_rahasia_anda</code>.</li>
            </ul>
        </section>
    </div>
</body>
</html>';

        return response($html, 200);
    }
}
