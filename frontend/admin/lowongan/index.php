<?php
// ============================================================
// FILE    : frontend/admin/lowongan/index.php
// FUNGSI  : Manajemen Lowongan Karir (Admin)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Kelola Lowongan Karir';
$admin_page = 'lowongan';

$lowongan_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM lowongan ORDER BY dibuat_pada DESC");
    $lowongan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $lowongan_list = [];
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-navy mb-0">Kelola Lowongan Karir</h4>
        <small class="text-muted">Publikasikan informasi rekrutmen host live streaming, staf gudang, dan posisi lainnya</small>
    </div>
    <a href="<?= base_url('frontend/admin/lowongan/tambah.php') ?>" class="btn btn-secondary-custom">
        <i class="fas fa-plus-circle me-1"></i> Buka Lowongan Baru
    </a>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Judul Lowongan</th>
                    <th>Posisi</th>
                    <th>Lokasi Penempatan</th>
                    <th>Batas Lamaran</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lowongan_list)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada lowongan pekerjaan yang diterbitkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lowongan_list as $low): ?>
                        <tr>
                            <td>
                                <strong class="text-navy"><?= e($low['judul_lowongan']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-subtle-blue text-navy"><?= e($low['posisi']) ?></span>
                            </td>
                            <td>
                                <span class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= e($low['lokasi'] ?? 'Bandar Lampung') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($low['batas_lamaran'])): ?>
                                    <span class="small"><?= date('d M Y', strtotime($low['batas_lamaran'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($low['status_lowongan'] === 'Dibuka'): ?>
                                    <span class="badge bg-success">Dibuka</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ditutup</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="<?= base_url('frontend/admin/lowongan/edit.php?id=' . $low['id_lowongan']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('backend/admin/proses-lowongan.php?aksi=hapus&id=' . $low['id_lowongan']) ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-confirm="Hapus lowongan '<?= e($low['judul_lowongan']) ?>'?" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

