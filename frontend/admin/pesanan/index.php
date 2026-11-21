<?php
// ============================================================
// FILE    : frontend/admin/pesanan/index.php
// FUNGSI  : Kelola Pesanan Pelanggan (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Kelola Pesanan';
$admin_page = 'pesanan';

// Filter Status & Search
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$filter_pembayaran = isset($_GET['pembayaran']) ? sanitize($_GET['pembayaran']) : '';
$keyword       = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (empty($_SESSION['bulk_biteship_csrf'])) {
    $_SESSION['bulk_biteship_csrf'] = bin2hex(random_bytes(32));
}

// Query Pesanan
$pesanan_list = [];
try {
    $sql = "
        SELECT p.*, u.nama_lengkap AS nama_user_akun, u.email AS email_user_akun,
               COUNT(d.id_detail) AS total_item,
               SUM(d.jumlah) AS total_qty
        FROM pesanan p
        JOIN users u ON p.id_user = u.id_user
        LEFT JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filter_status)) {
        $sql .= " AND p.status_pesanan = ?";
        $params[] = $filter_status;
    }

    if (!empty($filter_pembayaran)) {
        $sql .= " AND p.status_pembayaran = ?";
        $params[] = $filter_pembayaran;
    }

    if (!empty($keyword)) {
        $sql .= " AND (p.kode_pesanan LIKE ? OR p.nama_penerima LIKE ? OR p.no_hp_penerima LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }

    $sql .= " GROUP BY p.id_pesanan ORDER BY p.tanggal_pesanan DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pesanan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $pesanan_list = [];
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-navy mb-0">Daftar Transaksi Pesanan</h4>
        <small class="text-muted">Pantau dan ubah tahapan status pesanan pelanggan PT SMM</small>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-card mb-4">
    <div class="p-3">
        <form action="<?= base_url('frontend/admin/pesanan/index.php') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Cari kode pesanan, nama penerima, no HP..." value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status Pesanan --</option>
                    <option value="Menunggu Konfirmasi" <?= ($filter_status === 'Menunggu Konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="Diproses" <?= ($filter_status === 'Diproses') ? 'selected' : '' ?>>Diproses</option>
                    <option value="Dikirim" <?= ($filter_status === 'Dikirim') ? 'selected' : '' ?>>Dikirim</option>
                    <option value="Selesai" <?= ($filter_status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="Dibatalkan" <?= ($filter_status === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="pembayaran" class="form-select">
                    <option value="">-- Semua Pembayaran --</option>
                    <option value="Lunas" <?= ($filter_pembayaran === 'Lunas') ? 'selected' : '' ?>>Sudah Dibayar</option>
                    <option value="Menunggu Pembayaran" <?= ($filter_pembayaran === 'Menunggu Pembayaran') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <?php if (!empty($filter_status) || !empty($filter_pembayaran) || !empty($keyword)): ?>
                    <a href="<?= base_url('frontend/admin/pesanan/index.php') ?>" class="btn btn-outline-secondary" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php
$jumlah_bisa_kirim = count(array_filter($pesanan_list, static function ($pes) {
    return ($pes['status_pembayaran'] ?? '') === 'Lunas'
        && empty($pes['biteship_order_id'])
        && !empty($pes['kode_pos_tujuan'])
        && !in_array($pes['status_pesanan'], ['Dikirim', 'Selesai', 'Dibatalkan'], true);
}));
?>

<style>
.bulk-orders-toolbar { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
.bulk-orders-table { width:100%; min-width:0; table-layout:fixed; }
.bulk-orders-table th:nth-child(1), .bulk-orders-table td:nth-child(1) { width:135px; }
.bulk-orders-table th:nth-child(2), .bulk-orders-table td:nth-child(2) { width:110px; }
.bulk-orders-table th:nth-child(3), .bulk-orders-table td:nth-child(3) { width:130px; }
.bulk-orders-table th:nth-child(4), .bulk-orders-table td:nth-child(4) { width:95px; }
.bulk-orders-table th:nth-child(5), .bulk-orders-table td:nth-child(5) { width:70px; }
.bulk-orders-table th:nth-child(6), .bulk-orders-table td:nth-child(6) { width:110px; }
.bulk-orders-table th:nth-child(7), .bulk-orders-table td:nth-child(7) { width:200px; }
.bulk-orders-table th:nth-child(8), .bulk-orders-table td:nth-child(8) { width:90px; }
.bulk-orders-table td:nth-child(2) { overflow-wrap:anywhere; }
.bulk-orders-table td:nth-child(3) { overflow-wrap:anywhere; }
.bulk-orders-table td:nth-child(7) > div { flex-wrap:wrap; }
.bulk-orders-table td:nth-child(7) .badge { white-space:normal; text-align:left; }
.bulk-orders-table td:nth-child(1) .badge { display:inline-block; white-space:normal; text-align:center; }
@media (max-width: 1199.98px) {
    .bulk-orders-intro { align-items:flex-start !important; }
    .bulk-orders-intro > div:last-child { text-align:left; margin-left:51px; margin-top:-8px; }
    .bulk-orders-toolbar { align-items:stretch; }
    .bulk-orders-toolbar .form-check { width:100%; }
    .bulk-orders-toolbar > button { width:100%; min-height:42px; }
    .bulk-orders-table, .bulk-orders-table tbody { display:block; width:100%; min-width:0; }
    .bulk-orders-table thead { display:none; }
    .bulk-orders-table tr {
        display:flex; flex-wrap:wrap; align-items:stretch; gap:0;
        margin:12px; width:calc(100% - 24px); overflow:hidden;
        border:1px solid #e3eaf2; border-radius:16px; background:#fff;
        box-shadow:0 5px 16px rgba(15,39,68,.06);
    }
    .bulk-orders-table td {
        display:flex; flex:0 0 100%; width:100% !important; max-width:100%; min-width:0; box-sizing:border-box;
        align-items:center; justify-content:space-between;
        gap:10px; padding:10px 14px; border-bottom:1px solid #f0f3f7;
        text-align:left !important; white-space:normal;
    }
    .bulk-orders-table td::before {
        content:attr(data-label); flex:0 0 auto; color:#64748b;
        font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.045em;
    }
    .bulk-orders-table td:nth-child(1) {
        order:0; justify-content:flex-start; min-height:44px;
        padding:10px 14px; background:#f7f9fc; border-bottom:1px solid #e9eef4;
    }
    .bulk-orders-table td:nth-child(1)::before { content:none; }
    .bulk-orders-table td:nth-child(2) {
        order:1; flex:1 1 60%; width:auto !important; max-width:60%; align-items:flex-start; justify-content:center;
        padding:12px 8px 12px 14px; border-bottom:0; font-size:13px; line-height:1.45;
    }
    .bulk-orders-table td:nth-child(2)::before { content:none; }
    .bulk-orders-table td:nth-child(3) {
        order:3; display:block; padding:12px 14px;
        background:#fff; border-top:1px solid #f0f3f7;
    }
    .bulk-orders-table td:nth-child(3)::before { display:block; margin-bottom:5px; }
    .bulk-orders-table td:nth-child(3) > div { min-width:0; overflow-wrap:anywhere; font-size:14px; }
    .bulk-orders-table td:nth-child(3) small { display:block; margin-top:3px; white-space:nowrap; }
    .bulk-orders-table td:nth-child(4) { order:4; flex:0 0 50%; width:50% !important; max-width:50%; display:block; padding:10px 8px 10px 14px; }
    .bulk-orders-table td:nth-child(5) { order:5; flex:0 0 50%; width:50% !important; max-width:50%; display:block; padding:10px 14px 10px 8px; }
    .bulk-orders-table td:nth-child(4)::before, .bulk-orders-table td:nth-child(5)::before { display:block; margin-bottom:5px; }
    .bulk-orders-table td:nth-child(6) {
        order:6; padding:12px 14px;
        background:#f8fafc; border-top:1px solid #edf1f6;
    }
    .bulk-orders-table td:nth-child(6) > span { font-size:15px; }
    .bulk-orders-table td:nth-child(7) {
        order:7; align-items:center; flex-wrap:wrap;
        justify-content:flex-start; gap:8px; padding:11px 14px;
    }
    .bulk-orders-table td:nth-child(7)::before { display:none; }
    .bulk-orders-table td:nth-child(8) {
        order:2; flex:0 0 40%; width:40% !important; max-width:40%; align-self:stretch; justify-content:flex-end;
        border-bottom:0; padding:8px 14px 8px 4px;
    }
    .bulk-orders-table td:nth-child(8)::before { content:none; }
    .bulk-orders-table td:nth-child(8) .btn { white-space:nowrap; border-radius:999px; padding:6px 11px; }
    .bulk-orders-table td:nth-child(7) > div { display:flex; align-items:center; gap:5px; margin:0 !important; }
    .bulk-orders-table td:nth-child(7) > div > small { width:auto !important; font-size:10px; }
    .bulk-orders-table td:nth-child(7) .badge { font-size:10px; }
    .bulk-orders-table td:last-child { border-bottom:0; }
    .bulk-orders-table td[colspan] { display:block; flex:0 0 100%; max-width:100%; }
    .bulk-orders-table td[colspan]::before { content:none; }
}
</style>

<!-- Pengiriman massal -->
<div class="admin-card mb-3">
    <div class="bulk-orders-intro p-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:38px;height:38px;flex:0 0 38px;"><i class="fas fa-shipping-fast"></i></span>
            <div>
                <div class="fw-bold text-navy">Kirim pesanan ke Biteship</div>
                <small class="text-muted d-block">Pilih pesanan lunas yang belum dikirim dan memiliki kode pos.</small>
                <small class="text-muted d-block">Setiap pesanan akan didaftarkan sebagai pengiriman ke kurir. Maksimal 20 sekali proses.</small>
            </div>
        </div>
        <div class="text-md-end"><div class="fw-bold text-navy"><?= $jumlah_bisa_kirim ?></div><small class="text-muted">pesanan siap dikirim</small></div>
    </div>
</div>

<!-- Tabel Pesanan -->
<div class="admin-card">
    <form id="formKirimMassal" action="<?= base_url('backend/admin/proses-kirim-biteship-bulk.php') ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['bulk_biteship_csrf']) ?>">
    <div class="bulk-orders-toolbar p-3 border-bottom">
        <label class="form-check mb-0 d-flex align-items-center gap-2">
            <input class="form-check-input m-0" type="checkbox" id="pilihSemuaPesanan">
            <span class="small fw-semibold text-navy">Pilih hingga 20 pesanan siap dikirim</span>
        </label>
        <button type="submit" id="tombolKirimMassal" class="btn btn-primary btn-sm" disabled>
            <i class="fas fa-shipping-fast me-1"></i>Buat pengiriman (<span id="jumlahPilihan">0</span>/20)
        </button>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle bulk-orders-table">
            <thead>
                <tr>
                    <th class="text-center" style="width:100px;">Kirim</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Penerima</th>
                    <th>Tanggal Transaksi</th>
                    <th>Jumlah Barang</th>
                    <th>Total Pembayaran</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pesanan_list)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-3 d-block opacity-50"></i>
                            Tidak ada pesanan yang sesuai kriteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pesanan_list as $pes): ?>
                        <?php $bisa_kirim_biteship = ($pes['status_pembayaran'] ?? '') === 'Lunas'
                            && empty($pes['biteship_order_id'])
                            && !empty($pes['kode_pos_tujuan'])
                            && !in_array($pes['status_pesanan'], ['Dikirim', 'Selesai', 'Dibatalkan'], true); ?>
                        <tr>
                            <td data-label="Pengiriman">
                                <?php if ($bisa_kirim_biteship): ?>
                                    <input class="form-check-input order-biteship-check" type="checkbox" name="id_pesanan[]" value="<?= (int)$pes['id_pesanan'] ?>" aria-label="Pilih pesanan <?= e($pes['kode_pesanan']) ?>">
                                    <small class="d-block text-success" style="font-size:10px;">Siap dikirim</small>
                                <?php else: ?>
                                    <?php
                                    if (($pes['status_pembayaran'] ?? '') !== 'Lunas') $alasan_kirim = 'Belum lunas';
                                    elseif (!empty($pes['biteship_order_id'])) $alasan_kirim = 'Sudah diserahkan ke kurir';
                                    elseif (empty($pes['kode_pos_tujuan'])) $alasan_kirim = 'Kode pos kosong';
                                    else $alasan_kirim = 'Tidak perlu dikirim';
                                    ?>
                                    <span class="badge bg-light text-muted border" style="font-size:10px;"><?= e($alasan_kirim) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Kode pesanan">
                                <strong class="text-navy"><?= e($pes['kode_pesanan']) ?></strong>
                            </td>
                            <td data-label="Penerima">
                                <div class="fw-bold text-navy"><?= e($pes['nama_penerima']) ?></div>
                                <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?= e($pes['no_hp_penerima']) ?></small>
                            </td>
                            <td data-label="Tanggal">
                                <div class="small"><?= date('d/m/Y', strtotime($pes['tanggal_pesanan'])) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($pes['tanggal_pesanan'])) ?> WIB</small>
                            </td>
                            <td data-label="Jumlah barang">
                                <span class="badge bg-light text-navy border">
                                    <?= (int)$pes['total_qty'] ?> Item
                                </span>
                            </td>
                            <td data-label="Total pembayaran">
                                <span class="fw-bold text-navy"><?= format_rupiah($pes['total_harga']) ?></span>
                            </td>
                            <td data-label="Status">
                                <div class="d-flex align-items-center gap-2 mb-1"><small class="text-muted" style="width:60px;">Pesanan</small><?= status_badge_pesanan($pes['status_pesanan']) ?></div>
                                <div class="d-flex align-items-center gap-2"><small class="text-muted" style="width:60px;">Bayar</small><?= status_badge_pembayaran($pes['status_pembayaran'] ?? 'Belum Dibayar') ?></div>
                            </td>
                            <td class="text-end" data-label="Aksi">
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
    </form>
</div>

<script>
(function () {
    const checks = Array.from(document.querySelectorAll('.order-biteship-check'));
    const selectAll = document.getElementById('pilihSemuaPesanan');
    const submit = document.getElementById('tombolKirimMassal');
    const count = document.getElementById('jumlahPilihan');
    if (!checks.length || !selectAll || !submit || !count) return;

    function updateSelection() {
        const selected = checks.filter((check) => check.checked);
        count.textContent = selected.length;
        submit.disabled = selected.length === 0;
        selectAll.checked = checks.length <= 20 && selected.length === checks.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checks.length;
    }

    selectAll.addEventListener('change', function () {
        checks.forEach((check, index) => { check.checked = selectAll.checked && index < 20; });
        updateSelection();
    });
    checks.forEach((check) => check.addEventListener('change', updateSelection));
    const form = document.getElementById('formKirimMassal');
    form.addEventListener('submit', function (event) {
        const selected = checks.filter((check) => check.checked).length;
        event.preventDefault();
        if (selected < 1) return;
        if (selected > 20) {
            alert('Maksimal 20 pesanan per proses. Batalkan beberapa pilihan lalu coba lagi.');
            return;
        }

        const lanjutkan = function () {
            submit.disabled = true;
            HTMLFormElement.prototype.submit.call(form);
        };

        if (typeof Swal === 'undefined') {
            if (window.confirm('Atur pengiriman ' + selected + ' pesanan ke kurir sekarang?')) {
                lanjutkan();
            }
            return;
        }

        Swal.fire({
            title: 'Atur pengiriman?',
            text: selected + ' pesanan dipilih. Setiap pesanan akan didaftarkan ke kurir melalui Biteship.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1e3a5f',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-shipping-fast me-1"></i> Ya, Atur Pengiriman',
            cancelButtonText: 'Periksa Lagi',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({
                title: 'Mengirim ke Biteship…',
                text: 'Memproses ' + selected + ' pesanan.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            lanjutkan();
        });
    });
    updateSelection();
})();
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

