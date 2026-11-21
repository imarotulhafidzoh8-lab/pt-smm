<?php
// ============================================================
// FILE    : frontend/admin/dashboard.php
// FUNGSI  : Dashboard Utama Administrator PT SMM
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../config/app.php';
cek_admin();

$page_title = 'Dashboard Utama';
$admin_page = 'dashboard';

// Hitung Statistik
$stat_produk        = 0;
$stat_pesanan       = 0;
$stat_pesanan_lunas = 0;
$pendapatan_hari_ini = 0;
$total_pendapatan = 0;
$stat_perlu_dikirim = 0;
$stat_lowongan      = 0;
$stat_users         = 0;
$stat_pengunjung    = 0;
$stat_pengunjung_today = 0;
$grafik_periode = ['mingguan' => ['labels' => [], 'data' => [], 'produk' => []], 'bulanan' => ['labels' => [], 'data' => [], 'produk' => []]];
$grafik_status = [];
$grafik_pengunjung = ['labels' => [], 'data' => []];
$tanggal_awal_pengunjung = (new DateTimeImmutable('today'))->modify('-89 days');
for ($i = 0; $i < 90; $i++) {
    $hari = $tanggal_awal_pengunjung->modify('+' . $i . ' days');
    $grafik_pengunjung['labels'][] = $hari->format('d') . ' ' . ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][(int)$hari->format('n') - 1];
    $grafik_pengunjung['data'][$hari->format('Y-m-d')] = 0;
}
$label_bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$grafik_configs = [
    'mingguan' => ['mulai' => (new DateTimeImmutable('monday this week'))->modify('-11 weeks'), 'jumlah' => 12, 'format' => '%x-%v', 'key' => 'o-W'],
    'bulanan' => ['mulai' => (new DateTimeImmutable('first day of this month'))->modify('-11 months'), 'jumlah' => 12, 'format' => '%Y-%m', 'key' => 'Y-m'],
];
foreach ($grafik_configs as $mode => $config) {
    for ($i = 0; $i < $config['jumlah']; $i++) {
        $tanggal = $config['mulai']->modify('+' . $i . ($mode === 'mingguan' ? ' weeks' : ' months'));
        $grafik_periode[$mode]['labels'][] = $mode === 'mingguan'
            ? $tanggal->format('d') . ' ' . $label_bulan[(int)$tanggal->format('n') - 1]
            : $label_bulan[(int)$tanggal->format('n') - 1] . ' ' . $tanggal->format('Y');
        $grafik_periode[$mode]['data'][$tanggal->format($config['key'])] = ['pesanan' => 0, 'pendapatan' => 0, 'unit' => 0];
    }
}

try {
    $stat_produk        = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE status_produk = 'Aktif'")->fetchColumn();
    $stat_pesanan       = (int)$pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
    $stat_pesanan_lunas = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status_pembayaran = 'Lunas'")->fetchColumn();
    $pendapatan_hari_ini = (float)$pdo->query("SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE status_pembayaran = 'Lunas' AND tanggal_pesanan >= CURDATE() AND tanggal_pesanan < DATE_ADD(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();
    $total_pendapatan = (float)$pdo->query("SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE status_pembayaran = 'Lunas'")->fetchColumn();
    $stat_perlu_dikirim = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'Diproses'")->fetchColumn();
    $stat_lowongan      = (int)$pdo->query("SELECT COUNT(*) FROM lowongan WHERE status_lowongan = 'Dibuka'")->fetchColumn();
    $stat_users         = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Pengguna'")->fetchColumn();
    $stat_pengunjung    = (int)$pdo->query("SELECT COUNT(*) FROM pengunjung")->fetchColumn();
    $stat_pengunjung_today = (int)$pdo->query("SELECT COUNT(*) FROM pengunjung WHERE tanggal = CURDATE()")->fetchColumn();
} catch (PDOException $e) {}

// Data grafik mingguan dan bulanan: transaksi lunas dianggap sebagai penjualan terealisasi.
try {
    foreach ($grafik_configs as $mode => $config) {
        $periode_sql = $mode === 'mingguan' ? "DATE_FORMAT(p.tanggal_pesanan, '%x-%v')" : "DATE_FORMAT(p.tanggal_pesanan, '%Y-%m')";
        $stmt_grafik = $pdo->prepare("
            SELECT {$periode_sql} AS periode, COUNT(*) AS jumlah_pesanan, COALESCE(SUM(p.total_harga), 0) AS pendapatan
            FROM pesanan p
            WHERE p.status_pembayaran = 'Lunas' AND p.tanggal_pesanan >= :mulai
              AND p.tanggal_pesanan < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY {$periode_sql}
        ");
        $stmt_grafik->execute(['mulai' => $config['mulai']->format('Y-m-d')]);
        foreach ($stmt_grafik->fetchAll(PDO::FETCH_ASSOC) as $baris) {
            if (isset($grafik_periode[$mode]['data'][$baris['periode']])) {
                $grafik_periode[$mode]['data'][$baris['periode']]['pesanan'] = (int)$baris['jumlah_pesanan'];
                $grafik_periode[$mode]['data'][$baris['periode']]['pendapatan'] = (float)$baris['pendapatan'];
            }
        }

        $stmt_unit = $pdo->prepare("
            SELECT {$periode_sql} AS periode, COALESCE(SUM(d.jumlah), 0) AS unit_terjual
            FROM pesanan p JOIN detail_pesanan d ON d.id_pesanan = p.id_pesanan
            WHERE p.status_pembayaran = 'Lunas' AND p.tanggal_pesanan >= :mulai
              AND p.tanggal_pesanan < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY {$periode_sql}
        ");
        $stmt_unit->execute(['mulai' => $config['mulai']->format('Y-m-d')]);
        foreach ($stmt_unit->fetchAll(PDO::FETCH_ASSOC) as $baris) {
            if (isset($grafik_periode[$mode]['data'][$baris['periode']])) {
                $grafik_periode[$mode]['data'][$baris['periode']]['unit'] = (int)$baris['unit_terjual'];
            }
        }

        $stmt_produk = $pdo->prepare("
            SELECT d.nama_produk_saat_pesan AS nama_produk, SUM(d.jumlah) AS unit_terjual
            FROM detail_pesanan d JOIN pesanan p ON p.id_pesanan = d.id_pesanan
            WHERE p.status_pembayaran = 'Lunas' AND p.tanggal_pesanan >= :mulai
              AND p.tanggal_pesanan < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            GROUP BY d.nama_produk_saat_pesan ORDER BY unit_terjual DESC, nama_produk ASC LIMIT 5
        ");
        $stmt_produk->execute(['mulai' => $config['mulai']->format('Y-m-d')]);
        $grafik_periode[$mode]['produk'] = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt_status = $pdo->query("SELECT status_pesanan, COUNT(*) AS jumlah FROM pesanan GROUP BY status_pesanan");
    $grafik_status = $stmt_status->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // Dashboard tetap dapat dibuka bila data grafik belum tersedia.
}

try {
    $stmt_pengunjung = $pdo->prepare("
        SELECT DATE(tanggal) AS periode, COUNT(*) AS jumlah
        FROM pengunjung
        WHERE tanggal >= :mulai AND tanggal < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        GROUP BY DATE(tanggal)
    ");
    $stmt_pengunjung->execute(['mulai' => $tanggal_awal_pengunjung->format('Y-m-d')]);
    foreach ($stmt_pengunjung->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        if (array_key_exists($baris['periode'], $grafik_pengunjung['data'])) {
            $grafik_pengunjung['data'][$baris['periode']] = (int)$baris['jumlah'];
        }
    }
} catch (PDOException $e) {
    // Grafik pengunjung menampilkan nilai nol bila sumber data tidak tersedia.
}

$status_labels = ['Menunggu Pembayaran', 'Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
$status_values = array_map(static fn($status) => (int)($grafik_status[$status] ?? 0), $status_labels);
$status_aktif = array_filter($status_values, static fn($jumlah) => $jumlah > 0);

// Ambil 5 Pesanan Terbaru
$pesanan_terbaru = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, u.nama_lengkap AS nama_pemesan, u.email AS email_pemesan
        FROM pesanan p
        JOIN users u ON p.id_user = u.id_user
        ORDER BY p.tanggal_pesanan DESC
        LIMIT 5
    ");
    $pesanan_terbaru = $stmt->fetchAll();
} catch (PDOException $e) {
    $pesanan_terbaru = [];
}

require_once __DIR__ . '/../includes/admin-header.php';
?>

<!-- Welcome Banner -->
<div class="admin-welcome-card mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="admin-welcome-kicker"><i class="fas fa-shield-alt"></i> PANEL ADMINISTRATOR</span>
            <h2 class="fw-bold mb-2 text-white">Selamat datang, <?= e($admin_name) ?></h2>
            <p class="mb-0 admin-welcome-copy">
                Sistem Informasi Terpadu PT Shafa Marwa Multiguna. Pantau data produk, konfirmasi pesanan pelanggan, dan kelola informasi karir secara terpusat.
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="<?= base_url('frontend/admin/pesanan/index.php?pembayaran=Lunas') ?>" class="btn admin-welcome-action">
                <i class="fas fa-bell me-1"></i> <?= $stat_pesanan_lunas ?> Pesanan Dibayar
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards Grid -->
<style>
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 1.5rem;
}
.admin-stats-grid > div { min-width: 0; }
.admin-stats-grid .admin-stat-card {
    height: 100%;
    min-height: 112px;
    align-items: center;
}
.admin-stats-grid .admin-stat-card > div:last-child { min-width: 0; flex: 1; }
.admin-stats-grid .admin-stat-label {
    min-height: 2.3em;
    display: flex;
    align-items: flex-end;
    line-height: 1.25;
}
.admin-stats-grid .admin-stat-value {
    min-height: 2.3em;
    display: flex;
    align-items: center;
    line-height: 1.15;
    font-size: clamp(.9rem, 1.05vw, 1.2rem);
    overflow-wrap: anywhere;
}
.admin-stats-grid .admin-stat-value small { white-space: normal; }
@media (max-width: 1199.98px) {
    .admin-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .admin-stats-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
    .admin-stats-grid .admin-stat-card { min-height: 96px; padding: 12px; gap: 9px; }
    .admin-stats-grid .admin-stat-icon { width: 34px; height: 34px; flex-basis: 34px; }
    .admin-stats-grid .admin-stat-value { font-size: .95rem; }
}
.admin-main, .admin-content-body { width: 100%; min-width: 0; max-width: 100%; }
.admin-content-body { overflow-x: clip; }
.admin-chart-section .row > [class*="col-"] { min-width: 0; }
@media (max-width: 767.98px) {
    .admin-wrapper { min-width: 0; max-width: 100%; }
    .admin-topbar > div:first-child { min-width: 0; }
    .admin-topbar-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .admin-welcome-card .row { row-gap: .5rem; }
    .admin-welcome-action { width: 100%; justify-content: center; }
    .admin-chart-tools { flex-wrap: wrap; justify-content: flex-end; }
    .admin-chart-section .row { --bs-gutter-x: .75rem; --bs-gutter-y: .75rem; }
    .admin-chart-card-header > div:first-child { min-width: 0; }
    .admin-card-header > a.btn { align-self: stretch; text-align: center; }
    .admin-card-header > div { min-width: 0; }
    .admin-chart-section, .admin-stats-grid, .admin-card { max-width: 100%; }
}
</style>
<div class="admin-stats-grid">
    <div>
        <div class="admin-stat-card">
            <div class="admin-stat-icon blue">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <div class="admin-stat-label">Produk Aktif</div>
                <div class="admin-stat-value"><?= $stat_produk ?></div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-stat-card">
            <div class="admin-stat-icon amber">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <div class="admin-stat-label">Pendapatan Hari Ini</div>
                <div class="admin-stat-value"><?= format_rupiah($pendapatan_hari_ini) ?></div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-stat-card">
            <div class="admin-stat-icon green">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <div class="admin-stat-label">Total Pendapatan</div>
                <div class="admin-stat-value"><?= format_rupiah($total_pendapatan) ?></div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-stat-card">
            <div class="admin-stat-icon blue">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <div class="admin-stat-label">Total Pesanan</div>
                <div class="admin-stat-value"><?= $stat_pesanan ?></div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-stat-card">
            <div class="admin-stat-icon purple">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="admin-stat-label">Pengunjung Real-Time</div>
                <div class="admin-stat-value"><?= number_format($stat_pengunjung) ?> <small class="text-muted fw-normal" style="font-size: 11px;">(+<?= $stat_pengunjung_today ?> hari ini)</small></div>
            </div>
        </div>
    </div>
</div>

<!-- Business Overview Charts -->
<div class="admin-chart-section mb-4">
    <div class="admin-section-heading">
        <div>
            <span class="admin-chart-eyebrow">ANALISIS BISNIS</span>
            <h3 class="admin-chart-section-title">Performa Sistem</h3>
            <p class="admin-chart-section-copy">Bandingkan performa transaksi lunas berdasarkan minggu atau bulan.</p>
        </div>
        <label class="admin-chart-filter"><i class="far fa-calendar-alt"></i>
            <span class="visually-hidden">Periode grafik</span>
            <select id="chartPeriodFilter" aria-label="Pilih periode grafik">
                <option value="mingguan">Per minggu</option>
                <option value="bulanan" selected>Per bulan</option>
            </select>
        </label>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <section class="admin-chart-card h-100" aria-labelledby="revenueChartTitle">
                <div class="admin-chart-card-header">
                    <div>
                        <h4 id="revenueChartTitle">Pendapatan & Pesanan</h4>
                        <p id="revenueChartSubtitle">Tren transaksi lunas per bulan</p>
                    </div>
                    <div class="admin-chart-tools" role="group" aria-label="Pilih metrik grafik transaksi">
                        <button type="button" class="admin-chart-toggle" data-revenue-mode="all" aria-pressed="true">Gabungan</button>
                        <button type="button" class="admin-chart-toggle" data-revenue-mode="revenue" aria-pressed="false">Pendapatan</button>
                        <button type="button" class="admin-chart-toggle" data-revenue-mode="orders" aria-pressed="false">Pesanan</button>
                    </div>
                </div>
                <div class="admin-chart-canvas admin-chart-canvas-tall"><canvas id="revenueChart" aria-label="Grafik pendapatan dan jumlah pesanan per bulan"></canvas></div>
            </section>
        </div>
        <div class="col-xl-4 col-md-6">
            <section class="admin-chart-card h-100" aria-labelledby="statusChartTitle">
                <div class="admin-chart-card-header">
                    <div>
                        <h4 id="statusChartTitle">Status Pesanan</h4>
                        <p><?= number_format($stat_pesanan) ?> pesanan tercatat</p>
                    </div>
                    <span class="admin-chart-icon green"><i class="fas fa-layer-group"></i></span>
                </div>
                <div class="admin-chart-canvas admin-chart-canvas-donut"><canvas id="statusChart" aria-label="Grafik distribusi status pesanan"></canvas></div>
            </section>
        </div>
        <div class="col-xl-7 col-md-6">
            <section class="admin-chart-card h-100" aria-labelledby="salesChartTitle">
                <div class="admin-chart-card-header">
                    <div>
                        <h4 id="salesChartTitle">Produk Terlaris</h4>
                        <p>Jumlah unit terjual dari transaksi lunas</p>
                    </div>
                    <span class="admin-chart-icon amber"><i class="fas fa-boxes-stacked"></i></span>
                </div>
                <div class="admin-chart-canvas admin-chart-canvas-products"><canvas id="productChart" aria-label="Grafik lima produk terlaris"></canvas></div>
                <div class="admin-chart-empty" id="productChartEmpty" <?= !empty($grafik_periode['bulanan']['produk']) ? 'hidden' : '' ?>><i class="fas fa-chart-bar me-1"></i> Belum ada data penjualan lunas pada periode ini.</div>
            </section>
        </div>
        <div class="col-xl-5 col-md-6">
            <section class="admin-chart-card h-100" aria-labelledby="unitsChartTitle">
                <div class="admin-chart-card-header">
                    <div>
                        <h4 id="unitsChartTitle">Unit Terjual</h4>
                        <p id="unitsChartSubtitle">Volume produk yang terjual per bulan</p>
                    </div>
                    <span class="admin-chart-icon blue"><i class="fas fa-cart-shopping"></i></span>
                </div>
                <div class="admin-chart-canvas admin-chart-canvas-tall"><canvas id="unitsChart" aria-label="Grafik unit produk terjual per bulan"></canvas></div>
            </section>
        </div>
        <div class="col-12">
            <section class="admin-chart-card" aria-labelledby="visitorsChartTitle">
                <div class="admin-chart-card-header">
                    <div>
                        <h4 id="visitorsChartTitle">Kunjungan Harian</h4>
                        <p id="visitorChartSubtitle">Jumlah kunjungan website setiap hari selama 30 hari terakhir</p>
                    </div>
                    <div class="admin-chart-tools" role="group" aria-label="Pilih rentang kunjungan harian">
                        <button type="button" class="admin-chart-toggle visitor-range" data-visitor-days="7" aria-pressed="false">7 hari</button>
                        <button type="button" class="admin-chart-toggle visitor-range active" data-visitor-days="30" aria-pressed="true">30 hari</button>
                        <button type="button" class="admin-chart-toggle visitor-range" data-visitor-days="90" aria-pressed="false">90 hari</button>
                    </div>
                </div>
                <div class="admin-chart-canvas admin-chart-canvas-visitors"><canvas id="visitorsChart" aria-label="Grafik jumlah kunjungan harian selama 30 hari terakhir"></canvas></div>
            </section>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h5 class="admin-card-title">Pesanan Masuk Terbaru</h5>
            <small class="text-muted">Daftar transaksi pesanan yang baru saja dilakukan pelanggan</small>
        </div>
        <a href="<?= base_url('frontend/admin/pesanan/index.php') ?>" class="btn btn-sm btn-outline-primary">
            Lihat Semua Pesanan (<?= $stat_pesanan ?>) <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Nama Pemesan / Penerima</th>
                    <th>Waktu Pesan</th>
                    <th>Total Transaksi</th>
                    <th>Status Pesanan</th>
                    <th class="text-end">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pesanan_terbaru)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i> Belum ada pesanan yang tercatat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pesanan_terbaru as $pes): ?>
                        <tr>
                            <td>
                                <strong class="text-navy"><?= e($pes['kode_pesanan']) ?></strong>
                            </td>
                            <td>
                                <div class="fw-semibold text-navy"><?= e($pes['nama_penerima']) ?></div>
                                <small class="text-muted"><?= e($pes['no_hp_penerima']) ?></small>
                            </td>
                            <td>
                                <div class="small"><?= date('d M Y, H:i', strtotime($pes['tanggal_pesanan'])) ?> WIB</div>
                            </td>
                            <td>
                                <span class="fw-bold text-navy"><?= format_rupiah($pes['total_harga']) ?></span>
                            </td>
                            <td>
                                <?= status_badge_pesanan($pes['status_pesanan']) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('frontend/admin/pesanan/detail.php?id=' . $pes['id_pesanan']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const chartPeriods = <?= json_encode($grafik_periode, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const visitorSeries = <?= json_encode($grafik_pengunjung, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const statusLabels = <?= json_encode($status_labels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const statusValues = <?= json_encode($status_values) ?>;
    let selectedPeriod = 'bulanan';
    const currentPeriod = () => chartPeriods[selectedPeriod];
    const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
    const gridColor = 'rgba(119, 139, 161, .12)';
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 850, easing: 'easeOutQuart' },
        transitions: { active: { animation: { duration: 220 } } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 7, boxHeight: 7, padding: 16, color: '#62758a', font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } } },
            tooltip: { backgroundColor: '#142f50', padding: 11, cornerRadius: 9, titleFont: { family: 'Plus Jakarta Sans', weight: '700' }, bodyFont: { family: 'Plus Jakarta Sans' } }
        }
    };

    const revenueChart = new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: { labels: currentPeriod().labels, datasets: [
            { type: 'bar', label: 'Pesanan lunas', data: Object.values(currentPeriod().data).map(row => row.pesanan), yAxisID: 'y', backgroundColor: 'rgba(79, 141, 247, .18)', borderColor: '#4f8df7', borderWidth: 1, borderRadius: 6, maxBarThickness: 28 },
            { type: 'line', label: 'Pendapatan', data: Object.values(currentPeriod().data).map(row => row.pendapatan), yAxisID: 'yRevenue', borderColor: '#19a47b', backgroundColor: 'rgba(25, 164, 123, .1)', pointBackgroundColor: '#19a47b', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, borderWidth: 2.5, tension: .35, fill: true }
        ] },
        options: { ...baseOptions, scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } },
            y: { beginAtZero: true, grid: { color: gridColor }, border: { display: false }, ticks: { precision: 0, color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } }, title: { display: true, text: 'Pesanan', color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } },
            yRevenue: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, border: { display: false }, ticks: { color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 }, callback: value => value >= 1000000 ? 'Rp' + (value / 1000000).toFixed(1) + ' jt' : 'Rp' + Math.round(value / 1000) + ' rb' } }
        }, plugins: { ...baseOptions.plugins, tooltip: { ...baseOptions.plugins.tooltip, callbacks: { label: context => context.dataset.yAxisID === 'yRevenue' ? ' Pendapatan: ' + money(context.raw) : ' Pesanan lunas: ' + context.raw } } } }
    });

    const statusColors = ['#f2b84b', '#7c91aa', '#4f8df7', '#29a6b7', '#19a47b', '#e47777'];
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: statusLabels, datasets: [{ data: statusValues, backgroundColor: statusColors, borderColor: '#fff', borderWidth: 3, hoverOffset: 5 }] },
        options: { ...baseOptions, cutout: '68%', plugins: { ...baseOptions.plugins, legend: { position: 'bottom', labels: { ...baseOptions.plugins.legend.labels, padding: 11 } }, tooltip: baseOptions.plugins.tooltip } }
    });

    const productChart = new Chart(document.getElementById('productChart'), {
        type: 'bar',
        data: { labels: currentPeriod().produk.map(product => product.nama_produk), datasets: [{ label: 'Unit terjual', data: currentPeriod().produk.map(product => Number(product.unit_terjual)), backgroundColor: ['#315f91', '#4f8df7', '#62a4e8', '#82b7ea', '#a9ccef'], borderRadius: 5, maxBarThickness: 22 }] },
        options: { ...baseOptions, indexAxis: 'y', plugins: { ...baseOptions.plugins, legend: { display: false }, tooltip: baseOptions.plugins.tooltip }, scales: { x: { beginAtZero: true, grid: { color: gridColor }, border: { display: false }, ticks: { precision: 0, color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } }, y: { grid: { display: false }, border: { display: false }, ticks: { color: '#62758a', font: { family: 'Plus Jakarta Sans', size: 10 } } } } }
    });

    const unitsChart = new Chart(document.getElementById('unitsChart'), {
        type: 'bar',
        data: { labels: currentPeriod().labels, datasets: [{ label: 'Unit terjual', data: Object.values(currentPeriod().data).map(row => row.unit), backgroundColor: 'rgba(242, 184, 75, .28)', borderColor: '#e3aa38', borderWidth: 1, borderRadius: 6, maxBarThickness: 30 }] },
        options: { ...baseOptions, plugins: { ...baseOptions.plugins, legend: { display: false } }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } }, y: { beginAtZero: true, grid: { color: gridColor }, border: { display: false }, ticks: { precision: 0, color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } } } }
    });

    const visitorsChart = new Chart(document.getElementById('visitorsChart'), {
        type: 'line',
        data: { labels: visitorSeries.labels.slice(-30), datasets: [{
            label: 'Kunjungan', data: Object.values(visitorSeries.data).slice(-30),
            borderColor: '#4f8df7', backgroundColor: 'rgba(79, 141, 247, .14)',
            pointBackgroundColor: '#fff', pointBorderColor: '#4f8df7', pointBorderWidth: 2,
            pointRadius: 2.5, pointHoverRadius: 5, borderWidth: 2.5, tension: .35, fill: true
        }] },
        options: { ...baseOptions, interaction: { mode: 'index', intersect: false }, plugins: { ...baseOptions.plugins, legend: { display: false }, tooltip: { ...baseOptions.plugins.tooltip, callbacks: { label: context => ' ' + Number(context.raw).toLocaleString('id-ID') + ' kunjungan' } } }, scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { autoSkip: true, maxTicksLimit: 10, maxRotation: 0, color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } },
            y: { beginAtZero: true, grid: { color: gridColor }, border: { display: false }, ticks: { precision: 0, color: '#8291a3', font: { family: 'Plus Jakarta Sans', size: 10 } } }
        } }
    });

    document.querySelectorAll('[data-revenue-mode]').forEach(button => {
        button.addEventListener('click', function () {
            const mode = this.dataset.revenueMode;
            const showOrders = mode !== 'revenue';
            const showRevenue = mode !== 'orders';
            revenueChart.setDatasetVisibility(0, showOrders);
            revenueChart.setDatasetVisibility(1, showRevenue);
            revenueChart.options.scales.y.display = showOrders;
            revenueChart.options.scales.yRevenue.display = showRevenue;
            revenueChart.options.scales.yRevenue.position = showRevenue && !showOrders ? 'left' : 'right';
            revenueChart.update();
            document.querySelectorAll('[data-revenue-mode]').forEach(item => {
                const active = item === this;
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        });
    });

    document.querySelectorAll('[data-visitor-days]').forEach(button => {
        button.addEventListener('click', function () {
            const days = Number(this.dataset.visitorDays);
            visitorsChart.data.labels = visitorSeries.labels.slice(-days);
            visitorsChart.data.datasets[0].data = Object.values(visitorSeries.data).slice(-days);
            visitorsChart.update();
            document.getElementById('visitorChartSubtitle').textContent = 'Jumlah kunjungan website setiap hari selama ' + days + ' hari terakhir';
            document.querySelectorAll('[data-visitor-days]').forEach(item => {
                const active = item === this;
                item.classList.toggle('active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        });
    });

    document.getElementById('chartPeriodFilter').addEventListener('change', function () {
        selectedPeriod = this.value;
        const period = currentPeriod();
        const values = Object.values(period.data);
        revenueChart.data.labels = period.labels;
        revenueChart.data.datasets[0].data = values.map(row => row.pesanan);
        revenueChart.data.datasets[1].data = values.map(row => row.pendapatan);
        revenueChart.update();
        unitsChart.data.labels = period.labels;
        unitsChart.data.datasets[0].data = values.map(row => row.unit);
        unitsChart.update();
        productChart.data.labels = period.produk.map(product => product.nama_produk);
        productChart.data.datasets[0].data = period.produk.map(product => Number(product.unit_terjual));
        productChart.update();
        document.getElementById('revenueChartSubtitle').textContent = 'Tren transaksi lunas ' + (selectedPeriod === 'mingguan' ? 'per minggu' : 'per bulan');
        document.getElementById('unitsChartSubtitle').textContent = 'Volume produk yang terjual ' + (selectedPeriod === 'mingguan' ? 'per minggu' : 'per bulan');
        document.getElementById('productChartEmpty').hidden = period.produk.length > 0;
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>
