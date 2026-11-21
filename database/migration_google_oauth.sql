-- ============================================================
-- MIGRATION  : Tambah kolom google_id & avatar_url ke tabel users
-- PROYEK     : Sistem Informasi PT Shafa Marwa Multiguna
-- ============================================================

USE db_ptsmm;

-- 1. Tambah kolom google_id dan avatar_url
ALTER TABLE users 
    ADD COLUMN google_id VARCHAR(100) NULL DEFAULT NULL AFTER email,
    ADD COLUMN avatar_url VARCHAR(500) NULL DEFAULT NULL AFTER google_id;

-- 2. Buat unique index untuk google_id
ALTER TABLE users 
    ADD UNIQUE INDEX idx_google_id (google_id);

-- 3. Izinkan kolom password bernilai NULL (untuk user Google OAuth)
ALTER TABLE users 
    MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
