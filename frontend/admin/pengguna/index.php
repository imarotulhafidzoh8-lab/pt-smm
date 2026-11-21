<?php
// ============================================================
// FILE    : frontend/admin/pengguna/index.php
// FUNGSI  : Manajemen Pengguna & Admin (Admin Panel)
// PROYEK  : Sistem Informasi PT Shafa Marwa Multiguna
// ============================================================
require_once __DIR__ . '/../../../config/app.php';
cek_admin();

$page_title = 'Kelola Pengguna';
$admin_page = 'pengguna';

// Filter role & pencarian
$filter_role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$keyword     = isset($_GET['q']) ? sanitize($_GET['q']) : '';

$users_list = [];
try {
    $sql = "
        SELECT u.*, COUNT(p.id_pesanan) AS total_transaksi
        FROM users u
        LEFT JOIN pesanan p ON u.id_user = p.id_user
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filter_role)) {
        $sql .= " AND u.role = ?";
        $params[] = $filter_role;
    }

    if (!empty($keyword)) {
        $sql .= " AND (u.nama_lengkap LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
        $params[] = '%' . $keyword . '%';
    }

    $sql .= " GROUP BY u.id_user ORDER BY u.dibuat_pada DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $users_list = [];
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-navy mb-0">Daftar Akun Pengguna</h4>
        <small class="text-muted">Kelola akun hak akses Administrator dan akun pelanggan terdaftar</small>
    </div>
    <button type="button" class="btn btn-secondary-custom" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin">
        <i class="fas fa-user-shield me-1"></i> Tambah Administrator
    </button>
</div>

<!-- Filter Bar -->
<div class="admin-card mb-4">
    <div class="p-3">
        <form action="<?= base_url('frontend/admin/pengguna/index.php') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Cari nama, email, no telepon..." value="<?= e($keyword) ?>">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">-- Semua Hak Akses --</option>
                    <option value="Admin" <?= ($filter_role === 'Admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="Pengguna" <?= ($filter_role === 'Pengguna') ? 'selected' : '' ?>>Pengguna</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <?php if (!empty($filter_role) || !empty($keyword)): ?>
                    <a href="<?= base_url('frontend/admin/pengguna/index.php') ?>" class="btn btn-outline-secondary" title="Reset">
                        <i class="fas fa-undo"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Table Users -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Hak Akses (Role)</th>
                    <th>Kontak & Alamat</th>
                    <th class="text-center">Total Pesanan</th>
                    <th>Status Akun</th>
                    <th class="text-end" style="width: 140px;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users_list)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Tidak ada data pengguna yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users_list as $u): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="admin-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong class="text-navy"><?= e($u['nama_lengkap']) ?></strong>
                                        <div class="text-muted small"><?= e($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (strcasecmp($u['role'], 'Admin') === 0): ?>
                                    <span class="badge badge-role-admin px-3 py-1">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-role-pengguna px-3 py-1">Pengguna</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-semibold"><i class="fas fa-phone-alt me-1 text-muted"></i><?= e($u['no_hp'] ?? '-') ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;"><?= e($u['alamat'] ?? '-') ?></div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-navy border">
                                    <?= $u['total_transaksi'] ?> Transaksi
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status_akun'] === 'Aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($u['id_user'] != $_SESSION['id_user']): ?>
                                    <?php if ($u['status_akun'] === 'Aktif'): ?>
                                        <a href="<?= base_url('backend/admin/proses-pengguna.php?aksi=status&set=Nonaktif&id=' . $u['id_user']) ?>" class="btn btn-sm btn-outline-warning" title="Nonaktifkan Akun" data-confirm="Apakah Anda yakin ingin menonaktifkan akun '<?= e($u['nama_lengkap']) ?>'?" data-confirm-title="Konfirmasi Nonaktifkan" data-confirm-icon="warning" data-confirm-btn="Ya, Nonaktifkan">
                                            <i class="fas fa-user-slash me-1"></i> Nonaktifkan
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('backend/admin/proses-pengguna.php?aksi=status&set=Aktif&id=' . $u['id_user']) ?>" class="btn btn-sm btn-outline-success" title="Aktifkan Akun" data-confirm="Aktifkan kembali akun '<?= e($u['nama_lengkap']) ?>'?" data-confirm-title="Konfirmasi Aktivasi" data-confirm-icon="question" data-confirm-btn="Ya, Aktifkan">
                                            <i class="fas fa-user-check me-1"></i> Aktifkan
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Akun Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Admin -->
<div class="modal fade" id="modalTambahAdmin" tabindex="-1" aria-labelledby="modalTambahAdminLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-navy" id="modalTambahAdminLabel">
                    <i class="fas fa-user-shield me-2 text-primary"></i>Tambah Administrator Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('backend/admin/proses-pengguna.php') ?>" method="POST">
                <input type="hidden" name="aksi" value="tambah_admin">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Admin <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Admin <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="admin@shafamarwa.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Awal (Min. 6 Karakter) <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-secondary-custom">
                        <i class="fas fa-save me-1"></i> Simpan Admin Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>

