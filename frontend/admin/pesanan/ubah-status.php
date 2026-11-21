<?php
// Pointer to detail.php
require_once __DIR__ . '/../../../config/app.php';
$id = (int)($_GET['id'] ?? 0);
redirect(base_url('frontend/admin/pesanan/detail.php?id=' . $id));

