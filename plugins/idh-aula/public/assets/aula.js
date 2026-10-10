(function () {
  'use strict';

  var MOBILE_BREAKPOINT = 768;

  // ─── Video modal (misma lógica del prototipo) ───

  var modal = document.getElementById('videoModal');

  function isMobile() {
    return window.innerWidth <= MOBILE_BREAKPOINT ||
           /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  }

  if (modal) {
    var modalClose = document.getElementById('modalClose');
    var modalFrame = document.getElementById('modalIframe');
    var modalTitle = document.getElementById('modalTitle');

    var openModal = function (videoId, title) {
      modalFrame.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0';
      modalTitle.textContent = title;
      modal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    };

    var closeModal = function () {
      modal.classList.remove('is-open');
      // detiene el video limpiando el src al terminar la transición
      setTimeout(function () { modalFrame.src = ''; }, 200);
      document.body.style.overflow = '';
    };

    document.querySelectorAll('button[data-video-id]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var videoId = btn.dataset.videoId;
        if (isMobile()) {
          window.location.href = 'https://www.youtube.com/watch?v=' + videoId;
        } else {
          openModal(videoId, btn.dataset.videoTitle);
        }
      });
    });

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
  }

  // ─── Copiar enlace ───

  var toast;

  function showToast(text) {
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'aula-toast';
      toast.setAttribute('role', 'status');
      document.body.appendChild(toast);
    }
    toast.textContent = text;
    toast.classList.add('is-visible');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(function () { toast.classList.remove('is-visible'); }, 2200);
  }

  function fallbackCopy(text) {
    var input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();
    try { document.execCommand('copy'); } catch (e) { /* sin soporte */ }
    document.body.removeChild(input);
  }

  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var text = btn.getAttribute('data-copy');
      var done = function () {
        var label = btn.querySelector('.copy-label');
        btn.classList.add('is-copied');
        if (label) label.textContent = 'Copiado';
        showToast('Enlace copiado');
        setTimeout(function () {
          btn.classList.remove('is-copied');
          if (label) label.textContent = 'Copiar';
        }, 2000);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text); done(); });
      } else {
        fallbackCopy(text);
        done();
      }
    });
  });

  // ─── Tooltips (toque en móvil, Escape para cerrar) ───

  function closeTips(except) {
    document.querySelectorAll('.has-tip.is-open').forEach(function (tip) {
      if (tip !== except) tip.classList.remove('is-open');
    });
  }

  document.addEventListener('click', function (e) {
    var tip = e.target.closest('.has-tip');
    if (!tip) {
      closeTips(null);
      return;
    }
    // El tag puede estar dentro de la tarjeta-enlace: abrir el tooltip sin navegar.
    e.preventDefault();
    e.stopPropagation();
    closeTips(tip);
    tip.classList.toggle('is-open');
  });

  document.addEventListener('keydown', function (e) {
    var tip = document.activeElement && document.activeElement.closest ? document.activeElement.closest('.has-tip') : null;
    if (e.key === 'Escape') {
      closeTips(null);
      if (tip) tip.blur();
    } else if (tip && (e.key === 'Enter' || e.key === ' ')) {
      e.preventDefault();
      closeTips(tip);
      tip.classList.toggle('is-open');
    }
  });

  // ─── Filtro por categoría en el listado ───

  var tabs = document.querySelectorAll('.filter-tab');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var value = tab.getAttribute('data-filter');
      tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
      document.querySelectorAll('.course-card[data-category]').forEach(function (card) {
        card.hidden = value !== 'all' && card.getAttribute('data-category') !== value;
      });
    });
  });
})();
