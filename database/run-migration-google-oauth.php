<?php
// ============================================================
// FILE    : database/run-migration-google-oauth.php
// FUNGSI  : Migration Khusus Kolom Google OAuth (Tabel users)
//           Kompatibel dengan semua versi MySQL (tanpa IF NOT EXISTS)
// ============================================================

require_once __DIR__ . '/../config/database.php';

$results = [];
$errors  = [];

function col_exists($pdo, $table, $col) {
    $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
    return $stmt->rowCount() > 0;
}

try {
    // 1. Izinkan password NULL
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL");
        $results[] = "✅ Izinkan password NULL (untuk akun Google)";
    } catch (PDOException $e) {
        $errors[] = "❌ Gagal ubah kolom password: " . $e->getMessage();
    }

    // 2. Kolom google_id
    if (!col_exists($pdo, 'users', 'google_id')) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(100) NULL DEFAULT NULL AFTER email");
            $results[] = "✅ Tambah kolom google_id";
        } catch (PDOException $e) {
            $errors[] = "❌ Tambah kolom google_id: " . $e->getMessage();
        }
    } else {
        $results[] = "✅ Kolom google_id sudah ada";
    }

    // 3. Kolom avatar_url
    if (!col_exists($pdo, 'users', 'avatar_url')) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL DEFAULT NULL AFTER google_id");
            $results[] = "✅ Tambah kolom avatar_url";
        } catch (PDOException $e) {
            $errors[] = "❌ Tambah kolom avatar_url: " . $e->getMessage();
        }
    } else {
        $results[] = "✅ Kolom avatar_url sudah ada";
    }

    // 4. Index unique google_id
    try {
        $stmt_idx = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'idx_google_id'");
        if ($stmt_idx->rowCount() === 0) {
            $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX idx_google_id (google_id)");
            $results[] = "✅ Tambah unique index idx_google_id";
        } else {
            $results[] = "✅ Unique index idx_google_id sudah ada";
        }
    } catch (PDOException $e) {
        $errors[] = "❌ Tambah unique index google_id: " . $e->getMessage();
    }

    // Ambil struktur tabel users saat ini
    $stmt_cols = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt_cols->fetchAll();

} catch (Exception $e) {
    $errors[] = "Fatal error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migration Google OAuth — PT SMM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 760px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">🚀 Migration: Google OAuth — PT SMM</h5>
        </div>
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Hasil Migration:</h6>
            <ul class="list-group mb-4">
                <?php foreach ($results as $res): ?>
                    <li class="list-group-item text-success small"><?= htmlspecialchars($res) ?></li>
                <?php endforeach; ?>
                <?php foreach ($errors as $err): ?>
                    <li class="list-group-item text-danger small"><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (empty($errors)): ?>
                <div class="alert alert-success d-flex align-items-center">
                    <div><strong>Sukses!</strong> Kolom Google OAuth sudah aktif dan siap digunakan.</div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Ada error saat migration.</strong> Periksa pesan di atas.
                </div>
            <?php endif; ?>

            <h6 class="fw-bold mt-4 mb-2">Struktur Tabel <code>users</code> Saat Ini:</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-striped small">
                    <thead class="table-dark">
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($columns)): ?>
                            <?php foreach ($columns as $c): ?>
                                <tr class="<?= in_array($c['Field'], ['google_id', 'avatar_url']) ? 'table-warning fw-bold' : '' ?>">
                                    <td><?= htmlspecialchars($c['Field']) ?></td>
                                    <td><?= htmlspecialchars($c['Type']) ?></td>
                                    <td><?= htmlspecialchars($c['Null']) ?></td>
                                    <td><?= htmlspecialchars($c['Key']) ?></td>
                                    <td><?= htmlspecialchars((string)$c['Default']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="../frontend/auth/login.php" class="btn btn-primary px-4">→ Ke Halaman Login (Test Sekarang)</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>

