<?php
require_once __DIR__ . '/../config/app.php';
$query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
redirect(base_url('frontend/pages/detail-produk.php' . $query));
