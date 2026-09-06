<?php
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<header class="site-header">
  <div class="nav-container container">
    <a class="brand" href="index.php" aria-label="Ruang Mojo, Website Potensi Padukuhan Mojo">
      <img class="brand-logo" src="assets/images/logo-clean.png" alt="Logo Ruang Mojo" width="48" height="48">
      <div class="brand-text">
        <span class="brand-name">Ruang</span>
        <span class="brand-name">Mojo</span>
      </div>
    </a>
    <nav class="main-nav" id="mainNav">
      <ul>
        <li><a href="index.php"<?= ($currentScript === 'index.php') ? ' class="active"' : '' ?>>Beranda</a></li>
        <li><a href="potensi.php"<?= ($currentScript === 'potensi.php' || $currentScript === 'detail.php') ? ' class="active"' : '' ?>>Potensi</a></li>
        <li><a href="tentang.php"<?= ($currentScript === 'tentang.php') ? ' class="active"' : '' ?>>Tentang Mojo</a></li>
        <li><a href="kontak.php"<?= ($currentScript === 'kontak.php') ? ' class="active"' : '' ?>>Kontak</a></li>
      </ul>
    </nav>
    <div class="nav-cta">
      <a class="btn btn-primary" href="potensi.php">Jelajahi Potensi</a>
    </div>

    <button id="navToggle" class="nav-toggle" aria-label="Buka menu">
      <span class="hamburger"></span>
    </button>
  </div>
</header>
