let siteInitialized = false;

function initializeSite() {
  if (siteInitialized) return;
  siteInitialized = true;
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  navToggle && navToggle.addEventListener('click', function(){
    const isOpen = mainNav.classList.toggle('open');
    this.classList.toggle('opened', isOpen);
    this.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
  });

  // close menu when a nav link is clicked
  mainNav && mainNav.querySelectorAll('a').forEach(function(link){
    link.addEventListener('click', function(){
      mainNav.classList.remove('open');
      navToggle && navToggle.classList.remove('opened');
      navToggle && navToggle.setAttribute('aria-label', 'Buka menu');
    });
  });

  // close menu on resize
  window.addEventListener('resize', function(){
    if(window.innerWidth > 700){
      if (mainNav) mainNav.classList.remove('open');
      if (navToggle) navToggle.classList.remove('opened');
    }
  });

  // scroll effect for header
  const header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', function(){
      const current = window.pageYOffset;
      if(current > 60){
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  // Simple client-side search (phase 1, dummy)
  const searchInput = document.getElementById('searchInput');
  const filterCategory = document.getElementById('filterCategory');
  const filterCards = function(){
    const q = searchInput ? searchInput.value.toLowerCase() : '';
    const category = filterCategory ? filterCategory.value.toLowerCase() : '';
    document.querySelectorAll('#list .card').forEach(card=>{
      const title = card.querySelector('.card-title').textContent.toLowerCase();
      const cardCategory = card.querySelector('.card-body').dataset.category || '';
      card.style.display = title.includes(q) && (!category || cardCategory === category) ? '' : 'none';
    });
  };
  if(searchInput){
    searchInput.addEventListener('input', filterCards);
  }
  if(filterCategory) filterCategory.addEventListener('change', filterCards);

  // Custom File Upload Label Updater
  document.querySelectorAll('.custom-file-input').forEach(function(input) {
    input.addEventListener('change', function() {
      const container = this.closest('.custom-file-upload');
      if (!container) return;
      const nameSpan = container.querySelector('.custom-file-name');
      if (!nameSpan) return;

      const files = Array.from(this.files || []);
      if (files.length === 0) {
        nameSpan.textContent = nameSpan.dataset.emptyText || 'Belum ada foto dipilih';
        nameSpan.classList.remove('has-file');
      } else if (files.length === 1) {
        nameSpan.textContent = files[0].name;
        nameSpan.classList.add('has-file');
      } else {
        nameSpan.textContent = files.length + ' foto dipilih: ' + files.map(function(f) { return f.name; }).join(', ');
        nameSpan.classList.add('has-file');
      }
    });
  });

  document.querySelectorAll('[data-photo-preview], [data-gallery-preview]').forEach(function(input){
    input.addEventListener('change', function(){
      const target = document.querySelector(this.dataset.photoPreview || this.dataset.galleryPreview);
      if(!target) return;
      target.innerHTML = '';
      Array.from(this.files || []).forEach(function(file){
        if(!file.type.startsWith('image/')) return;
        const image = document.createElement('img');
        image.alt = file.name;
        image.src = URL.createObjectURL(file);
        target.appendChild(image);
      });
    });
  });

  // Dynamic Map Link Preview Button Updater
  const linkMapsInput = document.getElementById('linkMapsInput');
  const btnTestMapLink = document.getElementById('btnTestMapLink');
  if (linkMapsInput && btnTestMapLink) {
    const updateMapTestBtn = function() {
      let val = linkMapsInput.value.trim();
      if (val !== '') {
        if (!/^https?:\/\//i.test(val)) {
          val = 'https://' + val;
        }
        btnTestMapLink.href = val;
        btnTestMapLink.style.display = 'inline-flex';
      } else {
        btnTestMapLink.href = '#';
        btnTestMapLink.style.display = 'none';
      }
    };
    linkMapsInput.addEventListener('input', updateMapTestBtn);
    linkMapsInput.addEventListener('change', updateMapTestBtn);
  }

  document.querySelectorAll('[data-carousel]').forEach(function(carousel){
    const image = carousel.querySelector('[data-carousel-image]');
    const counter = carousel.querySelector('[data-carousel-counter]');
    const data = carousel.querySelector('[data-carousel-images]');
    if(!image || !data) return;
    const images = JSON.parse(data.textContent);
    let current = 0;
    const render = function(index){
      current = (index + images.length) % images.length;
      image.src = images[current];
      if(counter) counter.textContent = (current + 1) + ' / ' + images.length;
      carousel.querySelectorAll('[data-carousel-thumb]').forEach(function(thumb, thumbIndex){ thumb.classList.toggle('active', thumbIndex === current); });
    };
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    previous && previous.addEventListener('click', function(){ render(current - 1); });
    next && next.addEventListener('click', function(){ render(current + 1); });
    carousel.querySelectorAll('[data-carousel-thumb]').forEach(function(thumb){ thumb.addEventListener('click', function(){ render(Number(this.dataset.carouselThumb)); }); });
    carousel.addEventListener('keydown', function(event){ if(event.key === 'ArrowLeft') render(current - 1); if(event.key === 'ArrowRight') render(current + 1); });
    let touchStartX = null;
    carousel.addEventListener('touchstart', function(event){
      touchStartX = event.changedTouches[0].clientX;
    }, {passive: true});
    carousel.addEventListener('touchend', function(event){
      if(touchStartX === null) return;
      const distance = event.changedTouches[0].clientX - touchStartX;
      if(Math.abs(distance) >= 40) render(current + (distance < 0 ? 1 : -1));
      touchStartX = null;
    }, {passive: true});
    carousel.tabIndex = 0;
    render(0);
  });

  document.querySelectorAll('[data-confirm]').forEach(function(link){
    link.addEventListener('click', function(event){
      if(!window.confirm(this.dataset.confirm)) event.preventDefault();
    });
  });

  // Admin Mobile Drawer Navigation
  const adminBurgerBtn = document.getElementById('adminBurgerBtn');
  const adminSidebar = document.getElementById('adminSidebar');
  const adminBackdrop = document.getElementById('adminBackdrop');
  const adminSidebarClose = document.getElementById('adminSidebarClose');

  const openAdminDrawer = function() {
    if (adminSidebar) adminSidebar.classList.add('open');
    if (adminBackdrop) adminBackdrop.classList.add('active');
    document.body.classList.add('admin-drawer-open');
    if (adminBurgerBtn) adminBurgerBtn.setAttribute('aria-expanded', 'true');
  };

  const closeAdminDrawer = function() {
    if (adminSidebar) adminSidebar.classList.remove('open');
    if (adminBackdrop) adminBackdrop.classList.remove('active');
    document.body.classList.remove('admin-drawer-open');
    if (adminBurgerBtn) adminBurgerBtn.setAttribute('aria-expanded', 'false');
  };

  if (adminBurgerBtn) {
    adminBurgerBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      if (adminSidebar && adminSidebar.classList.contains('open')) {
        closeAdminDrawer();
      } else {
        openAdminDrawer();
      }
    });
  }

  if (adminSidebarClose) {
    adminSidebarClose.addEventListener('click', function(e) {
      e.preventDefault();
      closeAdminDrawer();
    });
  }

  if (adminBackdrop) {
    adminBackdrop.addEventListener('click', closeAdminDrawer);
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && adminSidebar && adminSidebar.classList.contains('open')) {
      closeAdminDrawer();
    }
  });

  // Admin Live Search & Filter (Instant Debounced Search + Category Filter)
  const adminSearchInput = document.getElementById('adminSearchInput');
  const adminFilterCategory = document.getElementById('adminFilterCategory');
  const adminTable = document.querySelector('.table-wrap table');
  const adminSearchClear = document.getElementById('adminSearchClear');
  const adminToolbarForm = document.querySelector('.admin-toolbar');

  if ((adminSearchInput || adminFilterCategory) && adminTable) {
    const tbody = adminTable.querySelector('tbody');
    const rows = tbody ? Array.from(tbody.querySelectorAll('tr:not(.empty-search-row)')) : [];
    let emptyRow = tbody ? tbody.querySelector('.empty-search-row') : null;
    const emptyMessage = (adminSearchInput && adminSearchInput.dataset.emptyMessage) ? adminSearchInput.dataset.emptyMessage : 'Data tidak ditemukan.';
    let searchDebounceTimer = null;

    const performFilter = function() {
      const query = adminSearchInput ? adminSearchInput.value.trim().toLowerCase() : '';
      const selectedCategory = adminFilterCategory ? adminFilterCategory.value.trim().toLowerCase() : '';
      let visibleCount = 0;

      if (adminSearchClear) {
        adminSearchClear.style.display = query !== '' ? 'block' : 'none';
      }

      rows.forEach(function(row) {
        const rowText = (row.innerText || row.textContent).toLowerCase();
        const rowCategory = (row.dataset.category || '').toLowerCase();
        const rowCategoryId = (row.dataset.categoryId || '').toLowerCase();

        const matchesQuery = query === '' || rowText.includes(query);
        const matchesCategory = selectedCategory === '' || rowCategory === selectedCategory || rowCategoryId === selectedCategory;

        if (matchesQuery && matchesCategory) {
          row.classList.remove('is-hidden');
          row.style.display = '';
          visibleCount++;
        } else {
          row.classList.add('is-hidden');
          row.style.display = 'none';
        }
      });

      if (visibleCount === 0 && rows.length > 0) {
        if (!emptyRow) {
          const colCount = adminTable.querySelectorAll('thead th').length || 6;
          emptyRow = document.createElement('tr');
          emptyRow.className = 'empty-search-row';
          emptyRow.innerHTML = '<td colspan="' + colCount + '" style="text-align: center; padding: 2.5rem 1.5rem; color: #7A695A;">' + emptyMessage + '</td>';
          tbody.appendChild(emptyRow);
        } else {
          emptyRow.classList.remove('is-hidden');
          emptyRow.style.display = '';
          const td = emptyRow.querySelector('td');
          if (td) td.textContent = emptyMessage;
        }
      } else if (emptyRow) {
        emptyRow.classList.add('is-hidden');
        emptyRow.style.display = 'none';
      }
    };

    if (adminSearchInput) {
      adminSearchInput.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(performFilter, 120);
      });
    }

    if (adminFilterCategory) {
      adminFilterCategory.addEventListener('change', performFilter);
      adminFilterCategory.addEventListener('input', performFilter);
    }

    if (adminSearchClear) {
      adminSearchClear.addEventListener('click', function(e) {
        e.preventDefault();
        if (adminSearchInput) adminSearchInput.value = '';
        if (adminFilterCategory) adminFilterCategory.value = '';
        performFilter();
        if (adminSearchInput) adminSearchInput.focus();
      });
    }

    if (adminToolbarForm) {
      adminToolbarForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performFilter();
      });
    }
  }

  // Admin Logout Confirmation Modal
  let logoutModalBackdrop = document.getElementById('adminLogoutModal');
  let currentLogoutUrl = '';

  const closeLogoutModal = function() {
    if (!logoutModalBackdrop) {
      logoutModalBackdrop = document.getElementById('adminLogoutModal');
    }
    if (logoutModalBackdrop) {
      logoutModalBackdrop.classList.remove('is-active');
    }
    document.body.classList.remove('admin-modal-open');
  };

  const bindLogoutModalEvents = function(modal) {
    if (!modal || modal.dataset.eventsBound === 'true') return;
    modal.dataset.eventsBound = 'true';

    const btnCancel = modal.querySelector('#btnLogoutModalCancel');
    const btnClose = modal.querySelector('#btnLogoutModalClose');
    const btnConfirm = modal.querySelector('#btnLogoutModalConfirm');

    if (btnCancel) {
      btnCancel.addEventListener('click', function(e) {
        e.preventDefault();
        closeLogoutModal();
      });
    }

    if (btnClose) {
      btnClose.addEventListener('click', function(e) {
        e.preventDefault();
        closeLogoutModal();
      });
    }

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeLogoutModal();
      }
    });

    if (btnConfirm) {
      btnConfirm.addEventListener('click', function(e) {
        if (currentLogoutUrl) {
          window.location.href = currentLogoutUrl;
        }
      });
    }
  };

  const createLogoutModal = function() {
    let modal = document.getElementById('adminLogoutModal');
    if (modal) {
      bindLogoutModalEvents(modal);
      return modal;
    }

    modal = document.createElement('div');
    modal.id = 'adminLogoutModal';
    modal.className = 'admin-modal-backdrop';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'logoutModalTitle');
    modal.setAttribute('aria-describedby', 'logoutModalDesc');
    modal.innerHTML = `
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
    `;
    document.body.appendChild(modal);
    bindLogoutModalEvents(modal);
    return modal;
  };

  // Bind immediately if already in DOM
  logoutModalBackdrop = document.getElementById('adminLogoutModal');
  if (logoutModalBackdrop) {
    bindLogoutModalEvents(logoutModalBackdrop);
  }

  const openLogoutModal = function(url) {
    currentLogoutUrl = url || 'logout.php';
    if (!logoutModalBackdrop) {
      logoutModalBackdrop = createLogoutModal();
    } else {
      bindLogoutModalEvents(logoutModalBackdrop);
    }
    const confirmBtn = logoutModalBackdrop.querySelector('#btnLogoutModalConfirm');
    if (confirmBtn) {
      confirmBtn.href = currentLogoutUrl;
    }
    if (typeof closeAdminDrawer === 'function') {
      closeAdminDrawer();
    }
    logoutModalBackdrop.classList.add('is-active');
    document.body.classList.add('admin-modal-open');
    const cancelBtn = logoutModalBackdrop.querySelector('#btnLogoutModalCancel');
    if (cancelBtn) {
      cancelBtn.focus();
    }
  };

  // Intercept any logout trigger in admin area using event delegation
  document.addEventListener('click', function(e) {
    const logoutLink = e.target.closest('a[href$="logout.php"], a[href*="logout.php"], .admin-logout-trigger, [data-logout-trigger]');
    if (logoutLink && !logoutLink.closest('#adminLogoutModal')) {
      e.preventDefault();
      const targetUrl = logoutLink.getAttribute('href') || 'logout.php';
      openLogoutModal(targetUrl);
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && logoutModalBackdrop && logoutModalBackdrop.classList.contains('is-active')) {
      closeLogoutModal();
    }
  });

  window.addEventListener('resize', function() {
    if (window.innerWidth > 768 && adminSidebar && adminSidebar.classList.contains('open')) {
      closeAdminDrawer();
    }
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeSite, { once: true });
} else {
  initializeSite();
}

window.addEventListener('load', initializeSite, { once: true });
