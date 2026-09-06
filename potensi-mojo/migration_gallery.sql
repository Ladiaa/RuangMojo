USE potensi_mojo;
CREATE TABLE IF NOT EXISTS potensi_foto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    potensi_id INT UNSIGNED NOT NULL,
    foto VARCHAR(255) NOT NULL,
    urutan INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_potensi_foto_potensi FOREIGN KEY (potensi_id) REFERENCES potensi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_potensi_foto_potensi_urutan (potensi_id, urutan)
) ENGINE = InnoDB;