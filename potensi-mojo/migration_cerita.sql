-- Migration: Cerita Mojo & Cerita Foto
USE potensi_mojo;

CREATE TABLE IF NOT EXISTS cerita (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NULL,
    kategori_cerita VARCHAR(100) NULL DEFAULT 'Kegiatan',
    tanggal DATE NOT NULL,
    ringkasan TEXT NULL,
    deskripsi TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cerita_status (status),
    INDEX idx_cerita_tanggal (tanggal)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cerita_foto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cerita_id INT UNSIGNED NOT NULL,
    foto VARCHAR(255) NOT NULL,
    urutan INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cerita_foto_cerita FOREIGN KEY (cerita_id) REFERENCES cerita(id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_cerita_foto_urutan (cerita_id, urutan)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample initial stories if table is empty
INSERT INTO cerita (judul, kategori_cerita, tanggal, ringkasan, deskripsi, foto, status)
SELECT 
    'Pendataan Potensi Padukuhan Mojo',
    'Kegiatan',
    '2026-08-20',
    'Dokumentasi kegiatan pendataan dan pengenalan berbagai potensi lokal masyarakat Padukuhan Mojo.',
    'Kegiatan pendataan potensi lokal di Padukuhan Mojo dilaksanakan untuk mendokumentasikan serta memetakan berbagai sektor unggulan masyarakat, mulai dari kerajinan tangan, pertanian, olahan produk rumah tangga, hingga keindahan bentang alam lokal.\n\nMelalui program ini, informasi yang terkumpul disusun secara terstruktur dalam platform Ruang Mojo agar dapat diakses secara terbuka oleh masyarakat luas dan pihak yang berkepentingan.',
    NULL,
    'aktif'
WHERE NOT EXISTS (SELECT 1 FROM cerita WHERE judul = 'Pendataan Potensi Padukuhan Mojo');

INSERT INTO cerita (judul, kategori_cerita, tanggal, ringkasan, deskripsi, foto, status)
SELECT 
    'Pemberdayaan Produk dan Kerajinan Lokal',
    'Masyarakat',
    '2026-08-24',
    'Mengenal lebih dekat geliat para pelaku usaha mikro dan perajin lokal di wilayah Mojo.',
    'Masyarakat Padukuhan Mojo memiliki keterampilan dan tradisi karya yang kaya. Dari anyaman hingga produk olahan pangan lokal, setiap karya mencerminkan dedikasi dan kearifan lokal yang terus diwariskan antar generasi.\n\nUpaya penguatan dan digitalisasi informasi potensi ini diharapkan dapat membuka peluang jejaring yang lebih luas bagi para perajin dan pelaku usaha di Padukuhan Mojo.',
    NULL,
    'aktif'
WHERE NOT EXISTS (SELECT 1 FROM cerita WHERE judul = 'Pemberdayaan Produk dan Kerajinan Lokal');
