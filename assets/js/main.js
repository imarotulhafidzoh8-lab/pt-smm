/**
 * PT SHAFA MARWA MULTIGUNA - MAIN JAVASCRIPT
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
  // Auto-dismiss bootstrap alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) {
        bsAlert.close();
      }
    }, 5000);
  });

  // Dynamic order subtotal calculator
  const qtyInput = document.getElementById('inputJumlahPesan');
  const priceInput = document.getElementById('inputHargaSatuan');
  const subtotalDisplay = document.getElementById('displayTotalBayar');

  if (qtyInput && priceInput && subtotalDisplay) {
    function calculateTotal() {
      const qty = parseInt(qtyInput.value) || 0;
      const price = parseFloat(priceInput.value) || 0;
      const total = qty * price;
      subtotalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
    qtyInput.addEventListener('input', calculateTotal);
    calculateTotal();
  }

  // Intercept any link/button with [data-confirm]
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    if (btn.tagName.toLowerCase() === 'form') return;

    e.preventDefault();
    const message = btn.getAttribute('data-confirm');
    const title = btn.getAttribute('data-confirm-title') || 'Konfirmasi';
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

  // Intercept form submission with [data-confirm]
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (form && form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
      e.preventDefault();
      const message = form.getAttribute('data-confirm');
      const title = form.getAttribute('data-confirm-title') || 'Konfirmasi';
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

  // Initialize Smooth Scroll & Scroll Reveal
  initScrollReveal();

  // Dismiss Preloader with smooth fade out
  const preloader = document.getElementById('site-preloader');
  if (preloader) {
    const dismissPreloader = function () {
      preloader.classList.add('preloader-hide');
      setTimeout(function () {
        if (preloader.parentNode) {
          preloader.style.display = 'none';
        }
      }, 600);
    };

    if (document.readyState === 'complete') {
      setTimeout(dismissPreloader, 350);
    } else {
      window.addEventListener('load', function () {
        setTimeout(dismissPreloader, 350);
      });
      // Safety fallback (maximum 1.5 seconds)
      setTimeout(dismissPreloader, 1500);
    }
  }
});

/**
 * Scroll Reveal Animation (Intersection Observer)
 * Smoothly reveals content as the user scrolls
 */
function initScrollReveal() {
  const autoSelectors = [
    'section',
    '.hero-dashboard-stage',
    '.stats-strip',
    '.product-item-col',
    '.service-card',
    '.why-section',
    '.promise-banner',
    '.newsletter-section',
    '.doc-card',
    '.executive-card',
    '.job-card',
    '.smm-card',
    '.trusted-strip-box',
    '.user-sidebar-card',
    '.auth-card'
  ];

  autoSelectors.forEach(function (sel) {
    document.querySelectorAll(sel).forEach(function (el, idx) {
      if (!el.classList.contains('reveal-on-scroll') && !el.closest('.hero-shopwave-wrap .col-lg-6:first-child')) {
        el.classList.add('reveal-on-scroll');
        if (el.classList.contains('product-item-col') || el.classList.contains('executive-card') || el.classList.contains('job-card')) {
          el.classList.add('reveal-delay-' + ((idx % 4) + 1));
        }
      }
    });
  });

  const targets = document.querySelectorAll('.reveal-on-scroll');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          obs.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      threshold: 0.05,
      rootMargin: '0px 0px -20px 0px'
    });

    targets.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    targets.forEach(function (el) {
      el.classList.add('is-revealed');
    });
  }

  // Smooth scroll for internal links
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href && href !== '#' && href.length > 1) {
        const targetEl = document.querySelector(href);
        if (targetEl) {
          e.preventDefault();
          targetEl.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    });
  });
}

