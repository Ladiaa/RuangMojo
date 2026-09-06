CREATE DATABASE IF NOT EXISTS potensi_mojo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE potensi_mojo;
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(190) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS kategori (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS potensi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT UNSIGNED NOT NULL,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(150) NULL,
    alamat VARCHAR(255) NULL,
    link_maps VARCHAR(500) NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    pengelola VARCHAR(150) NULL,
    kontak VARCHAR(100) NULL,
    foto VARCHAR(255) NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_potensi_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_potensi_status (status),
    INDEX idx_potensi_kategori (kategori_id)
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS potensi_foto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    potensi_id INT UNSIGNED NOT NULL,
    foto VARCHAR(255) NOT NULL,
    urutan INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_potensi_foto_potensi FOREIGN KEY (potensi_id) REFERENCES potensi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_potensi_foto_potensi_urutan (potensi_id, urutan)
) ENGINE = InnoDB;
INSERT IGNORE INTO kategori (nama_kategori, deskripsi)
VALUES ('Kerajinan', 'Kategori kerajinan lokal.'),
    ('Pertanian', 'Kategori pertanian lokal.'),
    (
        'Produk Lokal',
        'Kategori usaha atau produk lokal.'
    ),
    ('Sumber Daya Alam', 'Kategori sumber daya alam.'),
    (
        'Lainnya',
        'Potensi lain yang ditemukan melalui pendataan.'
    );
-- Ganti hash ini dengan hasil password_hash('password-anda', PASSWORD_DEFAULT).
INSERT IGNORE INTO users (nama, username, email, password, role)
VALUES (
        'Administrator',
        'admin',
        'admin@mojo.local',
        '$2y$10$myGnUbGH9eDOw.AiSINMz.39N50uU1veYrLsZbvkTLwWQv6P/LqEy',
        'admin'
    );
CREATE TABLE IF NOT EXISTS kontak_info (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_padukuhan VARCHAR(150) NOT NULL DEFAULT 'Padukuhan Mojo',
    alamat TEXT NOT NULL,
    nama_pengelola VARCHAR(150) NULL,
    nomor_whatsapp VARCHAR(50) NULL,
    link_maps VARCHAR(500) NULL,
    deskripsi TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;
INSERT IGNORE INTO kontak_info (id, nama_padukuhan, alamat, nama_pengelola, nomor_whatsapp, link_maps, deskripsi)
VALUES (
    1,
    'Padukuhan Mojo',
    'Kalurahan Ngeposari, Kapanewon Semanu, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta',
    'Dukuh Mojo & Pengelola',
    '',
    'https://www.google.com/maps/search/?api=1&query=Padukuhan+Mojo%2C+Ngeposari%2C+Semanu%2C+Gunungkidul%2C+Yogyakarta',
    'Ingin mengenal lebih dekat potensi Mojo? Temukan informasi dan berbagai cerita tentang Mojo melalui Ruang Mojo, atau hubungi kami untuk mendapatkan informasi lebih lanjut.'
);