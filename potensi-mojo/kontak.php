<?php
$title = 'Kontak - Ruang Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$kontak = get_kontak_info($pdo);

$waUrl = null;
if (!empty($kontak['nomor_whatsapp'])) {
    $waUrl = format_whatsapp_url($kontak['nomor_whatsapp'], 'Halo Pengelola Ruang Mojo, saya ingin menanyakan informasi seputar Padukuhan Mojo.');
}

$mapUrl = null;
if (!empty($kontak['link_maps'])) {
    $rawMap = trim((string)$kontak['link_maps']);
    if ($rawMap !== '') {
        $mapUrl = preg_match('~^https?://~i', $rawMap) ? $rawMap : 'https://' . $rawMap;
    }
}

// Map embed query
$embedMapQuery = rawurlencode('Padukuhan Mojo, Ngeposari, Semanu, Gunungkidul');
if (!empty($kontak['alamat'])) {
    $embedMapQuery = rawurlencode(($kontak['nama_padukuhan'] ?? 'Padukuhan Mojo') . ', ' . str_replace(["\r", "\n"], ' ', $kontak['alamat']));
}
$embedSrc = "https://maps.google.com/maps?q={$embedMapQuery}&t=&z=14&ie=UTF8&iwloc=&output=embed";

// Foto visual utama di hero background
$contactImage = 'public/images/kontak-mojo.jpg';
if (!file_exists(__DIR__ . '/' . $contactImage)) {
    $contactImage = 'assets/images/kontak-mojo.jpg';
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="public-site contact-page-wrapper">
  <!-- 1. Hero Section: Unified Global Template -->
  <section class="hero">
    <img src="<?= e($contactImage) ?>" alt="Kawasan Padukuhan Mojo" class="hero-image hero-image-contact" loading="eager">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">HUBUNGI MOJO</p>
      <h1 class="hero-title">Kontak</h1>
      <p class="hero-description"><?= !empty($kontak['deskripsi']) ? nl2br(e($kontak['deskripsi'])) : 'Ingin mengenal lebih dekat potensi Mojo?<br>Temukan informasi dan berbagai cerita tentang Mojo melalui Ruang Mojo, atau hubungi kami untuk mendapatkan informasi lebih lanjut.' ?></p>
    </div>
  </section>

  <!-- 2. Bagian Konten Informasi Utama (Ivory Background) -->
  <section class="contact-body-section">
    <div class="container">
      <div class="contact-main-grid">
        <!-- KOLOM KIRI (2 Cards Informasi) -->
        <div class="contact-col-left">
          <!-- Card 1: Hubungi Mojo -->
          <div class="contact-module-card">
            <div class="contact-module-icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
            </div>
            <div class="contact-module-body">
              <h2 class="contact-module-title">WhatsApp</h2>
              <p class="contact-module-phone"><?= e($kontak['nomor_whatsapp'] ?: '0812-3456-7890') ?></p>
              <?php if ($waUrl): ?>
                <a class="btn-contact-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                  </svg>
                  <span>Hubungi via WhatsApp &rarr;</span>
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Card 2: Alamat -->
          <div class="contact-module-card">
            <div class="contact-module-icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <div class="contact-module-body">
              <h2 class="contact-module-title">Alamat</h2>
              <div class="contact-module-text">
                <p class="contact-address-lead"><?= e($kontak['nama_padukuhan'] ?: 'Padukuhan Mojo') ?></p>
                <p class="contact-address-lines"><?= nl2br(e($kontak['alamat'] ?: "Kalurahan Ngeposari, Kapanewon Semanu,\nKabupaten Gunungkidul,\nDaerah Istimewa Yogyakarta")) ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN (Card Besar Lokasi Padukuhan Mojo) -->
        <div class="contact-col-right">
          <div class="contact-location-card">
            <div class="contact-location-header">
              <h2 class="contact-location-title">Lokasi Padukuhan Mojo</h2>
              <p class="contact-location-desc">Temukan lokasi Padukuhan Mojo dan rencanakan perjalanan melalui peta navigasi.</p>
            </div>

            <div class="contact-map-frame">
              <iframe 
                class="contact-map-iframe"
                title="Peta Lokasi Padukuhan Mojo"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="<?= e($embedSrc) ?>">
              </iframe>
            </div>

            <?php if ($mapUrl): ?>
              <div class="contact-location-action">
                <a class="btn-map-outline" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                  </svg>
                  <span>Buka di Google Maps &rarr;</span>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 3. CTA Card Banner (Warm Beige dengan Ilustrasi Pedesaan) -->
      <div class="contact-cta-card">
        <div class="contact-cta-illustration">
          <img src="assets/images/cta-potensi-mojo.png" alt="Ilustrasi Pedesaan Padukuhan Mojo" class="village-illustration" loading="lazy" width="480" height="240">
        </div>
        <div class="contact-cta-content-side">
          <h2 class="contact-cta-card-title">Ingin mengenal lebih dekat potensi Mojo?</h2>
          <p class="contact-cta-card-desc">Jelajahi berbagai potensi lokal yang telah dihimpun dalam Ruang Mojo.</p>
          <a class="btn-cta-explore" href="potensi.php">
            <span>Jelajahi Potensi &rarr;</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
