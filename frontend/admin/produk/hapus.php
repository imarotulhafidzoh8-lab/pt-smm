<?php
// Pointer to backend/admin/proses-produk.php?aksi=hapus
require_once __DIR__ . '/../../../config/app.php';
$id = (int)($_GET['id'] ?? 0);
redirect(base_url('backend/admin/proses-produk.php?aksi=hapus&id=' . $id));

