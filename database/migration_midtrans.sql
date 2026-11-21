-- ============================================================
-- MIGRATION  : Tambah kolom pembayaran Midtrans ke tabel pesanan
-- PROYEK     : Sistem Informasi PT Shafa Marwa Multiguna
-- ============================================================

USE db_ptsmm;

-- 1. Tambah kolom status_pembayaran
ALTER TABLE pesanan 
    ADD COLUMN status_pembayaran ENUM('Belum Dibayar', 'Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal') 
    NOT NULL DEFAULT 'Belum Dibayar' AFTER status_pesanan;

-- 2. Tambah kolom pendukung Midtrans
ALTER TABLE pesanan 
    ADD COLUMN metode_pembayaran VARCHAR(50) NULL DEFAULT NULL AFTER status_pembayaran,
    ADD COLUMN snap_token VARCHAR(255) NULL DEFAULT NULL AFTER metode_pembayaran,
    ADD COLUMN waktu_pembayaran DATETIME NULL DEFAULT NULL AFTER snap_token,
    ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL AFTER waktu_pembayaran;

