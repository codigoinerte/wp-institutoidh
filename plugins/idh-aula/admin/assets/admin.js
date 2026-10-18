/* global jQuery, wp, IDHAula */
(function ($) {
  'use strict';

  // ─── Utilidades generales ───

  $(document).on('click', '[data-confirm]', function (e) {
    if (!window.confirm($(this).data('confirm'))) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  });

  $(document).on('click', '[data-toggle-target]', function (e) {
    e.preventDefault();
    var $target = $($(this).data('toggle-target'));
    var show = $target.prop('hidden');
    $target.prop('hidden', !show);
    $(this).attr('aria-expanded', show ? 'true' : 'false');
    if (show && $target.length) {
      $target.find('input, select, textarea').filter(':visible').first().trigger('focus');
    }
  });

  // Abre la matrícula enlazada por #matricula-ID
  if (window.location.hash.indexOf('#matricula-') === 0) {
    var $enr = $(window.location.hash);
    $enr.find('.idh-enrollment-body').prop('hidden', false);
    $enr.addClass('is-highlighted');
  }

  $(document).on('change', '[data-check-all]', function () {
    $(this).closest('table').find('tbody input[type=checkbox]').prop('checked', this.checked);
  });

  $(document).on('click', '[data-requires-selection]', function (e) {
    var $form = $(this).closest('form');
    if (!$form.find('tbody input[type=checkbox]:checked').length) {
      e.preventDefault();
      window.alert('Selecciona al menos un alumno de la lista.');
    }
  });

  $(document).on('click', '[data-copy]', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var text = $btn.data('copy');
    var done = function () {
      var original = $btn.data('label') || $btn.text();
      $btn.data('label', original).text('¡Copiado!');
      setTimeout(function () { $btn.text(original); }, 1800);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done);
    } else {
      var $tmp = $('<textarea readonly>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
      $tmp[0].select();
      document.execCommand('copy');
      $tmp.remove();
      done();
    }
  });

  // ─── Certificado: mostrar campos según el modo ───

  function syncCertMode($fieldset) {
    var mode = $fieldset.find('[data-cert-mode]:checked').val();
    $fieldset.find('[data-cert-for]').each(function () {
      $(this).toggle($(this).data('cert-for') === mode);
    });
  }

  $('.idh-cert').each(function () { syncCertMode($(this)); });
  $(document).on('change', '[data-cert-mode]', function () { syncCertMode($(this).closest('.idh-cert')); });

  // ─── Período del reporte ───

  function syncPeriod() {
    var period = $('[data-period-select]').val();
    $('[data-period]').each(function () {
      $(this).toggle(String($(this).data('period')).split(' ').indexOf(period) !== -1);
    });
  }

  if ($('[data-period-select]').length) {
    syncPeriod();
    $('[data-period-select]').on('change', syncPeriod);
  }

  // ─── Toggles en línea (listado de matrículas) ───

  $(document).on('change', '[data-inline-toggle]', function () {
    var $input = $(this);
    var field = $input.data('inline-toggle');
    var value = $input.is(':checkbox') ? ($input.is(':checked') ? '1' : '0') : $input.val();
    var $row = $input.closest('[data-enrollment]');
    $input.prop('disabled', true);

    $.post(IDHAula.ajaxUrl, {
      action: 'idh_aula_toggle_enrollment',
      nonce: IDHAula.nonce,
      id: $row.data('enrollment'),
      field: field,
      value: value
    }).done(function (res) {
      if (!res.success) {
        window.alert(res.data && res.data.message ? res.data.message : 'No se pudo guardar.');
        return;
      }
      if (field === 'locked') {
        $input.next('span').text(value === '1' ? '🔒 Bloqueado' : 'Con acceso');
      } else {
        $input.removeClass('idh-pay-paid idh-pay-pending').addClass('idh-pay-' + value);
      }
      $row.addClass('idh-flash');
      setTimeout(function () { $row.removeClass('idh-flash'); }, 900);
    }).fail(function () {
      window.alert('No se pudo guardar. Revisa tu conexión.');
    }).always(function () {
      $input.prop('disabled', false);
    });
  });

  // ─── Portada del curso (biblioteca de medios) ───

  var coverFrame;
  $(document).on('click', '[data-cover-select]', function (e) {
    e.preventDefault();
    if (!coverFrame) {
      coverFrame = wp.media({ title: 'Portada del curso', button: { text: 'Usar esta imagen' }, library: { type: 'image' }, multiple: false });
      coverFrame.on('select', function () {
        var att = coverFrame.state().get('selection').first().toJSON();
        var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
        $('[data-cover-input]').val(att.id);
        $('[data-cover-preview]').html($('<img alt="">').attr('src', url));
        $('[data-cover-remove]').prop('hidden', false);
      });
    }
    coverFrame.open();
  });

  $(document).on('click', '[data-cover-remove]', function (e) {
    e.preventDefault();
    $('[data-cover-input]').val('0');
    $('[data-cover-preview]').empty();
    $(this).prop('hidden', true);
  });

  // ─── Fecha opcional ───

  $(document).on('click', '[data-clear-date]', function (e) {
    e.preventDefault();
    $($(this).data('clear-date')).val('');
    $(this).prop('hidden', true);
  });

  $(document).on('input change', 'input[type="date"]', function () {
    $('[data-clear-date="#' + this.id + '"]').prop('hidden', !this.value);
  });

  // ─── Repetidor de clases ───

  var $sessions = $('[data-sessions]');
  var sessionCounter = $sessions.children().length;

  function initSortables() {
    if (!$.fn.sortable) return;
    $sessions.sortable({ handle: '.idh-session-handle', axis: 'y' });
    $('[data-resource-list]').sortable({
      items: '> li',
      update: function () { syncIds($(this).closest('[data-session]')); }
    });
  }

  function syncIds($session) {
    var ids = $session.find('[data-resource-list] > li').map(function () { return $(this).data('id'); }).get();
    $session.find('[data-resource-ids]').val(ids.join(','));
  }

  $(document).on('click', '[data-add-session]', function (e) {
    e.preventDefault();
    var html = $('#idh-session-template').html().replace(/__i__/g, 'n' + (sessionCounter++));
    var $new = $(html).appendTo($sessions);
    var number = $sessions.children().length;
    $new.find('.idh-session-title').val('Sesión ' + (number < 10 ? '0' : '') + number).trigger('focus');
    initSortables();
  });

  $(document).on('click', '[data-remove-session]', function (e) {
    e.preventDefault();
    if (window.confirm('¿Quitar esta clase? Los recursos seguirán en la biblioteca.')) {
      $(this).closest('[data-session]').remove();
    }
  });

  $(document).on('click', '.idh-chip-remove', function (e) {
    e.preventDefault();
    var $session = $(this).closest('[data-session]');
    $(this).closest('li').remove();
    syncIds($session);
  });

  if ($sessions.length) {
    initSortables();
    if (!sessionCounter) {
      $('[data-add-session]').trigger('click');
    }
  }

  // ─── Selector de recursos ───

  var $picker = $('#idh-resource-picker');
  var $targetSession = null;
  var searchTimer;

  function escapeHtml(str) {
    return $('<div>').text(str == null ? '' : String(str)).html();
  }

  function addChip(resource) {
    if (!$targetSession) return;
    var $list = $targetSession.find('[data-resource-list]');
    if ($list.find('[data-id="' + resource.id + '"]').length) return;
    $list.append(
      '<li class="idh-chip idh-chip--' + escapeHtml(resource.type) + '" data-id="' + parseInt(resource.id, 10) + '">' +
        '<span class="idh-chip-type">' + escapeHtml(resource.label) + '</span>' +
        '<span class="idh-chip-title">' + escapeHtml(resource.title) + '</span>' +
        '<button type="button" class="idh-chip-remove" aria-label="Quitar">×</button>' +
      '</li>'
    );
    syncIds($targetSession);
  }

  function loadResources() {
    var $results = $picker.find('[data-picker-results]').html('<li class="idh-picker-empty">Cargando…</li>');
    $.get(IDHAula.ajaxUrl, {
      action: 'idh_aula_search_resources',
      nonce: IDHAula.nonce,
      q: $picker.find('[data-picker-search]').val(),
      type: $picker.find('[data-picker-type]').val()
    }).done(function (res) {
      if (!res.success || !res.data.length) {
        $results.html('<li class="idh-picker-empty">No hay recursos. Crea uno en la pestaña «Nuevo recurso».</li>');
        return;
      }
      var selected = $targetSession ? $targetSession.find('[data-resource-ids]').val().split(',') : [];
      $results.html(res.data.map(function (r) {
        var added = selected.indexOf(String(r.id)) !== -1;
        return '<li><button type="button" class="idh-picker-item' + (added ? ' is-added' : '') + '" data-resource=\'' + escapeHtml(JSON.stringify(r)).replace(/'/g, '&#39;') + '\'>' +
          '<span class="idh-chip-type idh-chip--' + escapeHtml(r.type) + '">' + escapeHtml(r.label) + '</span>' +
          '<span class="idh-picker-text"><strong>' + escapeHtml(r.title) + '</strong><small>' + escapeHtml(r.url) + '</small></span>' +
          '<span class="idh-picker-action">' + (added ? 'Agregado' : 'Agregar') + '</span>' +
        '</button></li>';
      }).join(''));
    });
  }

  function switchTab(name) {
    $picker.find('[data-picker-tab]').each(function () { $(this).toggleClass('is-active', $(this).data('picker-tab') === name); });
    $picker.find('[data-picker-panel]').each(function () { $(this).prop('hidden', $(this).data('picker-panel') !== name); });
  }

  function closePicker() {
    $picker.prop('hidden', true);
    $targetSession = null;
  }

  $(document).on('click', '[data-open-picker]', function (e) {
    e.preventDefault();
    $targetSession = $(this).closest('[data-session]');
    $picker.prop('hidden', false);
    switchTab('library');
    $picker.find('[data-new-error]').prop('hidden', true);
    loadResources();
    $picker.find('[data-picker-search]').trigger('focus');
  });

  $picker.on('click', '[data-close-picker]', closePicker);
  $(document).on('keydown', function (e) {
    if (e.key === 'Escape' && !$picker.prop('hidden')) closePicker();
  });
  $picker.on('click', '[data-picker-tab]', function () { switchTab($(this).data('picker-tab')); });
  $picker.on('input', '[data-picker-search]', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadResources, 250);
  });
  $picker.on('change', '[data-picker-type]', loadResources);

  $picker.on('click', '.idh-picker-item', function () {
    var resource = $(this).data('resource');
    addChip(resource);
    $(this).addClass('is-added').find('.idh-picker-action').text('Agregado');
  });

  $picker.on('click', '[data-new-save]', function () {
    var $btn = $(this).prop('disabled', true);
    var $error = $picker.find('[data-new-error]').prop('hidden', true);
    $.post(IDHAula.ajaxUrl, {
      action: 'idh_aula_create_resource',
      nonce: IDHAula.nonce,
      resource: {
        type: $picker.find('[data-new-type]').val(),
        title: $picker.find('[data-new-title]').val(),
        url: $picker.find('[data-new-url]').val()
      }
    }).done(function (res) {
      if (!res.success) {
        $error.text(res.data.message).prop('hidden', false);
        return;
      }
      addChip(res.data);
      $picker.find('[data-new-title], [data-new-url]').val('');
      closePicker();
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  // ─── Matrícula individual: buscar alumno por DNI o nombre ───

  (function () {
    var $form = $('[data-student-enroll]');
    if (!$form.length) return;
    var $q = $form.find('[data-student-search]');
    var $results = $form.find('[data-student-results]');
    var $selected = $form.find('[data-student-selected]');
    var $course = $form.find('[data-enroll-course]');
    var timer, xhr;

    function esc(str) { return $('<div>').text(str == null ? '' : String(str)).html(); }

    function refreshSubmit() {
      $form.find('[data-student-submit]').prop('disabled', !$selected.children().length);
    }

    function search() {
      var q = $.trim($q.val());
      if (xhr) xhr.abort();
      if (q.length < 2) { $results.prop('hidden', true).empty(); return; }
      $results.prop('hidden', false).html('<li class="idh-picker-empty">Buscando…</li>');
      xhr = $.get(IDHAula.ajaxUrl, { action: 'idh_aula_search_students', nonce: IDHAula.nonce, q: q, course_id: $course.val() })
        .done(function (res) {
          if (!res.success || !res.data.length) {
            $results.html('<li class="idh-picker-empty">No se encontraron alumnos con «' + esc(q) + '».</li>');
            return;
          }
          $results.html(res.data.map(function (s) {
            var added = $selected.find('[data-id="' + s.id + '"]').length > 0;
            var status = s.enrolled ? 'Ya matriculado' : (added ? 'Agregado' : 'Agregar');
            return '<li><button type="button" class="idh-picker-item' + (added || s.enrolled ? ' is-added' : '') + '"' + (s.enrolled ? ' disabled' : '') +
              ' data-student=\'' + esc(JSON.stringify(s)).replace(/'/g, '&#39;') + '\'>' +
              '<span class="idh-chip-type">' + esc(s.doc) + '</span>' +
              '<span class="idh-picker-text"><strong>' + esc(s.name) + '</strong>' +
                (s.blocked ? '<small class="idh-picker-error">Alumno bloqueado</small>' : '') + '</span>' +
              '<span class="idh-picker-action">' + status + '</span>' +
            '</button></li>';
          }).join(''));
        });
    }

    $q.on('input', function () { clearTimeout(timer); timer = setTimeout(search, 250); });
    $q.on('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); $results.find('.idh-picker-item:not([disabled])').first().trigger('click'); }
      if (e.key === 'Escape') { $results.prop('hidden', true); }
    });
    $course.on('change', function () { if (!$results.prop('hidden')) search(); });

    $results.on('click', '.idh-picker-item', function () {
      var s = $(this).data('student');
      if (!$selected.find('[data-id="' + s.id + '"]').length) {
        $selected.append(
          '<li class="idh-chip" data-id="' + parseInt(s.id, 10) + '">' +
            '<span class="idh-chip-type">' + esc(s.doc) + '</span>' +
            '<span class="idh-chip-title">' + esc(s.name) + '</span>' +
            '<input type="hidden" name="student_ids[]" value="' + parseInt(s.id, 10) + '" />' +
            '<button type="button" class="idh-chip-remove" aria-label="Quitar">×</button>' +
          '</li>'
        );
      }
      $q.val('').trigger('focus');
      $results.prop('hidden', true).empty();
      refreshSubmit();
    });

    $selected.on('click', '.idh-chip-remove', function () {
      $(this).closest('.idh-chip').remove();
      refreshSubmit();
    });

    $(document).on('click', function (e) {
      if (!$(e.target).closest('.idh-student-search').length) $results.prop('hidden', true);
    });
  })();
})(jQuery);
