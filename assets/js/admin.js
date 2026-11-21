/**
 * PT SHAFA MARWA MULTIGUNA - ADMIN JAVASCRIPT
 * Modern Minimalist SweetAlert2 Integration
 */

// Override standard window.alert to SweetAlert2
if (typeof Swal !== 'undefined') {
  window.alert = function (message) {
    Swal.fire({
      title: 'Pemberitahuan',
      text: String(message),
      icon: 'info',
      confirmButtonText: 'Tutup'
    });
  };
}

document.addEventListener('DOMContentLoaded', function () {
  // 1. Sidebar toggle on mobile
  const toggleBtn = document.getElementById('adminSidebarToggle');
  const sidebar = document.querySelector('.admin-sidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
      if (window.innerWidth < 992) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
          sidebar.classList.remove('show');
        }
      }
    });
  }

  // 2. Intercept any delete button (.btn-confirm-delete)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-confirm-delete');
    if (!btn) return;

    e.preventDefault();
    const message = btn.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
    const href = btn.getAttribute('href');

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Konfirmasi Hapus',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#71717a',
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          if (href && href !== '#' && !href.startsWith('javascript:')) {
            window.location.href = href;
          } else if (btn.form) {
            btn.form.submit();
          }
        }
      });
    } else {
      if (confirm(message)) {
        if (href) window.location.href = href;
      }
    }
  });

  // 3. Intercept any action link/button with [data-confirm] (excluding delete)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-confirm]:not(.btn-confirm-delete)');
    if (!btn) return;
    if (btn.tagName.toLowerCase() === 'form') return;

    e.preventDefault();
    const message = btn.getAttribute('data-confirm');
    const title = btn.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
    const icon = btn.getAttribute('data-confirm-icon') || 'question';
    const confirmBtn = btn.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
    const href = btn.getAttribute('href');

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: title,
        text: message,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmBtn,
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          if (href && href !== '#' && !href.startsWith('javascript:')) {
            window.location.href = href;
          } else if (btn.form) {
            btn.form.submit();
          }
        }
      });
    } else {
      if (confirm(message)) {
        if (href) window.location.href = href;
      }
    }
  });

  // 4. Intercept form submission with [data-confirm]
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form && form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
      e.preventDefault();
      const message = form.getAttribute('data-confirm');
      const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Pengiriman';
      const icon = form.getAttribute('data-confirm-icon') || 'question';
      const btnText = form.getAttribute('data-confirm-btn') || 'Ya, Kirim';

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: title,
          text: message,
          icon: icon,
          showCancelButton: true,
          confirmButtonText: btnText,
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            Swal.fire({
              title: 'Sedang memproses...',
              text: 'Mohon tunggu beberapa saat',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            form.submit();
          }
        });
      } else {
        if (confirm(message)) {
          form.dataset.confirmed = 'true';
          form.submit();
        }
      }
    }
  });
});
