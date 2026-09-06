<?php
/**
 * Modal Konfirmasi Logout Admin - Ruang Mojo
 * Dialog konfirmasi keluar dengan opsi Batal dan Ya, Keluar.
 */
?>
<div class="admin-modal-backdrop" id="adminLogoutModal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalDesc">
  <div class="admin-modal-dialog">
    <button type="button" class="admin-modal-close" id="btnLogoutModalClose" aria-label="Tutup dialog konfirmasi">&times;</button>
    <div class="admin-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
    </div>
    <h2 class="admin-modal-title" id="logoutModalTitle">Konfirmasi Keluar</h2>
    <p class="admin-modal-message" id="logoutModalDesc">Apakah Anda yakin ingin keluar dari halaman admin?</p>
    <div class="admin-modal-actions">
      <button type="button" class="btn-modal-cancel" id="btnLogoutModalCancel">Batal</button>
      <a href="#" class="btn-modal-confirm" id="btnLogoutModalConfirm">Ya, Keluar</a>
    </div>
  </div>
</div>
