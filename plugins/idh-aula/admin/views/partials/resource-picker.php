<?php
/**
 * Modal para elegir recursos de la biblioteca o crear uno nuevo sin salir del curso.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="idh-modal" id="idh-resource-picker" hidden>
  <div class="idh-modal-backdrop" data-close-picker></div>
  <div class="idh-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="idh-picker-title">
    <div class="idh-modal-head">
      <h2 id="idh-picker-title">Agregar video o material</h2>
      <button type="button" class="button-link" data-close-picker aria-label="Cerrar"><span class="dashicons dashicons-no-alt"></span></button>
    </div>

    <div class="idh-tabs">
      <button type="button" class="idh-tab is-active" data-picker-tab="library">Biblioteca</button>
      <button type="button" class="idh-tab" data-picker-tab="new">Nuevo recurso</button>
    </div>

    <div data-picker-panel="library">
      <div class="idh-picker-search">
        <input type="search" placeholder="Buscar por título o URL…" data-picker-search />
        <select data-picker-type>
          <option value="">Todos</option>
          <?php foreach ( IDH_Aula_Resources::TYPES as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <ul class="idh-picker-list" data-picker-results></ul>
    </div>

    <div data-picker-panel="new" hidden>
      <p class="idh-field">
        <label>Tipo</label>
        <select data-new-type>
          <?php foreach ( IDH_Aula_Resources::TYPES as $value => $label ) : ?>
            <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <p class="idh-field">
        <label>Título (lo que verá el alumno)</label>
        <input type="text" class="widefat" data-new-title placeholder="Ej.: Estrategias para la estructuración del aula" />
      </p>
      <p class="idh-field">
        <label>URL</label>
        <input type="url" class="widefat" data-new-url placeholder="https://www.youtube.com/watch?v=… o https://drive.google.com/…" />
      </p>
      <p class="idh-picker-error" data-new-error hidden></p>
      <button type="button" class="button button-primary" data-new-save>Guardar y agregar</button>
    </div>
  </div>
</div>
