<?php
// Pointer to backend/admin/proses-lowongan.php?aksi=hapus
require_once __DIR__ . '/../../../config/app.php';
$id = (int)($_GET['id'] ?? 0);
redirect(base_url('backend/admin/proses-lowongan.php?aksi=hapus&id=' . $id));

