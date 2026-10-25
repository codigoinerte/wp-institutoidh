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
  // ─── Recordar DNI y fecha de nacimiento en este dispositivo ───
  // Se guarda solo en el navegador (localStorage), nunca en el servidor. Al enviar el formulario
  // queda "pendiente" y se confirma recién cuando el ingreso fue correcto (ya no hay formulario).

  var REMEMBER_KEY = 'idh_aula_remember';
  var PENDING_KEY = 'idh_aula_remember_pending';

  function store(type) {
    try { return window[type]; } catch (e) { return null; }
  }

  function readJSON(storage, key) {
    try { return storage ? JSON.parse(storage.getItem(key) || 'null') : null; } catch (e) { return null; }
  }

  function setItem(storage, key, value) {
    try { if (storage) storage.setItem(key, value); } catch (e) { /* sin almacenamiento */ }
  }

  function removeItem(storage, key) {
    try { if (storage) storage.removeItem(key); } catch (e) { /* sin almacenamiento */ }
  }

  var local = store('localStorage');
  var session = store('sessionStorage');
  var loginForm = document.querySelector('[data-login-form]');

  if (!loginForm) {
    var pending = readJSON(session, PENDING_KEY);
    if (pending) {
      setItem(local, REMEMBER_KEY, JSON.stringify(pending));
      removeItem(session, PENDING_KEY);
    }
  } else {
    removeItem(session, PENDING_KEY); // el intento anterior no prosperó
    var docInput = loginForm.querySelector('[name="doc_number"]');

    // DNI enmascarado: se muestran los 4 primeros dígitos y el resto como asteriscos.
    // El valor real vive en el campo oculto; el ojo muestra u oculta el DNI completo.
    var docDisplay = loginForm.querySelector('[data-doc-display]');
    var docEye = loginForm.querySelector('[data-doc-eye]');
    var docRevealed = false;
    var MASK_FROM = 4;

    function renderDoc() {
      var real = docInput.value;
      docDisplay.value = docRevealed ? real : real.slice(0, MASK_FROM) + real.slice(MASK_FROM).replace(/./g, '*');
    }

    function setDoc(value) {
      docInput.value = String(value || '').replace(/\D/g, '').slice(0, 8);
      renderDoc();
    }

    docDisplay.addEventListener('input', function () {
      var typed = docDisplay.value;
      var prev = docInput.value;
      var next = '';
      for (var i = 0; i < typed.length; i++) {
        var ch = typed.charAt(i);
        if (ch === '*') {
          next += prev.charAt(i); // posición enmascarada: conserva el dígito anterior
        } else if (/\d/.test(ch)) {
          next += ch;
        }
      }
      setDoc(next);
      var end = docDisplay.value.length;
      docDisplay.setSelectionRange(end, end);
    });

    docEye.addEventListener('click', function () {
      docRevealed = !docRevealed;
      docEye.setAttribute('aria-pressed', docRevealed ? 'true' : 'false');
      docEye.setAttribute('aria-label', docRevealed ? 'Ocultar DNI' : 'Mostrar DNI');
      renderDoc();
      docDisplay.focus();
    });

    setDoc(docInput.value);
    var birthInput = loginForm.querySelector('[name="birth_date"]');
    var remember = loginForm.querySelector('[data-remember]');
    var hint = loginForm.querySelector('[data-remember-hint]');
    var saved = readJSON(local, REMEMBER_KEY);

    if (saved && saved.doc) {
      remember.checked = true;
      if (hint) hint.hidden = false;
      if (!docInput.value) {
        setDoc(saved.doc);
        birthInput.value = saved.birth || '';
      }
    }

    var forget = loginForm.querySelector('[data-forget]');
    if (forget) {
      forget.addEventListener('click', function () {
        removeItem(local, REMEMBER_KEY);
        remember.checked = false;
        setDoc('');
        birthInput.value = '';
        hint.hidden = true;
        docDisplay.focus();
      });
    }

    loginForm.addEventListener('submit', function () {
      if (remember.checked) {
        setItem(session, PENDING_KEY, JSON.stringify({ doc: docInput.value.trim(), birth: birthInput.value }));
      } else {
        removeItem(local, REMEMBER_KEY);
      }
    });
  }
})();
