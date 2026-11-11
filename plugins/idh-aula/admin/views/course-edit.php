<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$id     = absint( $_GET['id'] ?? 0 );
$course = $id ? IDH_Aula_Courses::find( $id ) : null;
if ( $id && ! $course ) {
    echo '<div class="notice notice-error"><p>Curso no encontrado.</p></div>';
    return;
}
$old   = IDH_Aula_Admin::old();
$value = static function ( $key, $default = '' ) use ( $old, $course ) {
    if ( array_key_exists( $key, $old ) ) {
        return (string) $old[ $key ];
    }
    return $course ? (string) ( $course->$key ?? $default ) : $default;
};

// Clases: las reenviadas tras un error o las guardadas.
$sessions = [];
if ( isset( $old['_sessions'] ) ) {
    foreach ( $old['_sessions'] as $s ) {
        $sessions[] = (object) [
            'id'               => $s['id'],
            'title'            => $s['title'],
            'description'      => $s['description'],
            'session_datetime' => $s['session_datetime'],
            'resources'        => array_filter( array_map( [ 'IDH_Aula_Resources', 'find' ], $s['resource_ids'] ) ),
        ];
    }
} elseif ( $course ) {
    $sessions = IDH_Aula_Courses::sessions( $course->id );
}

$cover_id  = (int) $value( 'cover_image_id', 0 );
$cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';
$list      = idh_aula_admin_url( 'idh-aula-cursos' );

/**
 * Bloque de una clase del repetidor.
 */
$render_session = static function ( $index, $session = null ) {
    $datetime  = $session && $session->session_datetime ? strtotime( $session->session_datetime ) : 0;
    $resources = $session ? $session->resources : [];
    $ids       = implode( ',', wp_list_pluck( $resources, 'id' ) );
    ?>
    <div class="idh-session" data-session>
      <div class="idh-session-head">
        <span class="idh-session-handle dashicons dashicons-menu" title="Arrastra para reordenar"></span>
        <input type="hidden" name="sessions[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo (int) ( $session->id ?? 0 ); ?>" />
        <input type="text" class="idh-session-title" name="sessions[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $session->title ?? '' ); ?>" placeholder="Ej.: Sesión 01, Sesión complementaria" />
        <button type="button" class="button-link idh-link-danger" data-remove-session>Quitar</button>
      </div>
      <div class="idh-grid-3">
        <p class="idh-field">
          <label>Fecha</label>
          <input type="date" name="sessions[<?php echo esc_attr( $index ); ?>][date]" value="<?php echo $datetime ? esc_attr( gmdate( 'Y-m-d', $datetime ) ) : ''; ?>" />
        </p>
        <p class="idh-field">
          <label>Hora</label>
          <input type="time" name="sessions[<?php echo esc_attr( $index ); ?>][time]" value="<?php echo $datetime && '00:00' !== gmdate( 'H:i', $datetime ) ? esc_attr( gmdate( 'H:i', $datetime ) ) : ''; ?>" />
        </p>
      </div>
      <p class="idh-field">
        <label>Descripción de la clase</label>
        <textarea name="sessions[<?php echo esc_attr( $index ); ?>][description]" rows="2" class="large-text"><?php echo esc_textarea( $session->description ?? '' ); ?></textarea>
      </p>
      <div class="idh-field">
        <label>Video y materiales</label>
        <ul class="idh-chips" data-resource-list>
          <?php foreach ( $resources as $resource ) : ?>
            <li class="idh-chip idh-chip--<?php echo esc_attr( $resource->type ); ?>" data-id="<?php echo (int) $resource->id; ?>">
              <span class="idh-chip-type"><?php echo esc_html( IDH_Aula_Resources::TYPES[ $resource->type ] ?? $resource->type ); ?></span>
              <span class="idh-chip-title"><?php echo esc_html( $resource->title ); ?></span>
              <button type="button" class="idh-chip-remove" aria-label="Quitar">×</button>
            </li>
          <?php endforeach; ?>
        </ul>
        <input type="hidden" name="sessions[<?php echo esc_attr( $index ); ?>][resource_ids]" value="<?php echo esc_attr( $ids ); ?>" data-resource-ids />
        <button type="button" class="button" data-open-picker>+ Agregar video o material</button>
      </div>
    </div>
    <?php
};
?>
<h1 class="wp-heading-inline"><?php echo $course ? 'Editar curso' : 'Nuevo curso'; ?></h1>
<a href="<?php echo esc_url( $list ); ?>" class="page-title-action">← Volver a cursos</a>
<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $list ) ); ?>" class="page-title-action idh-action-new">+ Nuevo curso</a>
<?php if ( $course ) : ?>
  <a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $course->id ] ) ); ?>" class="page-title-action">Ver alumnos</a>
  <a href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_duplicate_course', [ 'id' => $course->id ] ) ); ?>" class="page-title-action">Duplicar (nueva edición)</a>
<?php endif; ?>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="idh-course-form">
  <input type="hidden" name="action" value="idh_aula_save_course" />
  <input type="hidden" name="id" value="<?php echo (int) $id; ?>" />
  <?php wp_nonce_field( 'idh_aula_save_course' ); ?>

  <div class="idh-columns-layout">
    <div class="idh-col-main">
      <div class="idh-card">
        <p class="idh-field">
          <label for="title">Título <span class="required">*</span></label>
          <input type="text" id="title" name="course[title]" value="<?php echo esc_attr( $value( 'title' ) ); ?>" class="widefat idh-input-lg" required placeholder="Ej.: Herramientas inclusivas para niños con TEA" />
        </p>
        <p class="idh-field">
          <label for="description">Descripción</label>
          <textarea id="description" name="course[description]" rows="4" class="large-text"><?php echo esc_textarea( $value( 'description' ) ); ?></textarea>
        </p>
      </div>

      <div class="idh-card">
        <div class="idh-card-head">
          <h2>Clases</h2>
          <span class="description">Generalmente 1 o 2 clases. Los videos y materiales se eligen de la <a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-recursos' ) ); ?>" target="_blank">biblioteca</a> y se pueden reutilizar en otros cursos.</span>
        </div>
        <div class="idh-sessions" data-sessions>
          <?php foreach ( $sessions as $i => $session ) { $render_session( $i, $session ); } ?>
        </div>
        <button type="button" class="button button-secondary" data-add-session>+ Agregar clase</button>
        <template id="idh-session-template"><?php $render_session( '__i__' ); ?></template>
      </div>
    </div>

    <div class="idh-col-side">
      <div class="idh-card">
        <h2>Publicación</h2>
        <p class="idh-field">
          <label for="status">Estado</label>
          <select id="status" name="course[status]">
            <option value="published" <?php selected( $value( 'status', 'published' ), 'published' ); ?>>Publicado (visible para los alumnos matriculados)</option>
            <option value="draft" <?php selected( $value( 'status', 'published' ), 'draft' ); ?>>Borrador (oculto)</option>
          </select>
        </p>
        <button class="button button-primary button-large">Guardar curso</button>
        <?php if ( $course && ! IDH_Aula_Enrollments::count_for_course( $course->id ) ) : ?>
          <p><a class="idh-link-danger" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_course', [ 'id' => $course->id ] ) ); ?>" data-confirm="¿Eliminar este curso y sus clases?">Eliminar curso</a></p>
        <?php endif; ?>
      </div>

      <div class="idh-card">
        <h2>Detalles</h2>
        <p class="idh-field">
          <label for="category">Categoría</label>
          <select id="category" name="course[category]" class="widefat">
            <option value="">— Sin categoría —</option>
            <?php foreach ( IDH_Aula_Categories::options() as $val => $label ) : ?>
              <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $value( 'category' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="description"><a href="<?php echo esc_url( IDH_Aula_Categories::manage_url() ); ?>" target="_blank">Administrar categorías</a></span>
        </p>
        <p class="idh-field">
          <label for="hours">Horas académicas</label>
          <input type="text" id="hours" name="course[hours]" value="<?php echo esc_attr( $value( 'hours' ) ); ?>" class="widefat" placeholder="Ej.: 200 hrs. académicas" />
        </p>
        <p class="idh-field">
          <label for="instructor">Docente / expositor</label>
          <input type="text" id="instructor" name="course[instructor]" value="<?php echo esc_attr( $value( 'instructor' ) ); ?>" class="widefat" />
        </p>
        <p class="idh-field">
          <label for="start_date">Fecha de inicio <span class="description">— opcional</span></label>
          <input type="date" id="start_date" name="course[start_date]" value="<?php echo esc_attr( $value( 'start_date' ) ); ?>" />
          <button type="button" class="button-link" data-clear-date="#start_date" <?php echo $value( 'start_date' ) ? '' : 'hidden'; ?>>Quitar fecha</button>
          <span class="description">Déjala vacía para certificados o cursos sin fecha fija.</span>
        </p>
        <p class="idh-field">
          <label for="price">Precio (S/) <span class="description">— privado, no se muestra al alumno</span></label>
          <input type="number" id="price" name="course[price]" value="<?php echo esc_attr( $value( 'price', '0.00' ) ); ?>" step="0.01" min="0" />
          <span class="description">Se copia a cada matrícula nueva (editable por alumno).</span>
        </p>
      </div>

      <div class="idh-card">
        <h2>Certificado generado</h2>
        <p class="idh-field">
          <label for="certificate_template">Diseño</label>
          <select id="certificate_template" name="course[certificate_template]" class="widefat">
            <?php foreach ( IDH_Aula_Certificate_Generator::TEMPLATE_CHOICES as $val => $label ) : ?>
              <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $value( 'certificate_template' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
          </select>
          <span class="description">«Según la categoría»: Curso de Especialización usa el negro y dorado; las demás, el azul.</span>
        </p>
        <?php $cert_html = IDH_Aula_Certificate_Generator::content_to_html( $value( 'certificate_content' ) ); ?>
        <div class="idh-field idh-cert-content" data-cert-content>
          <label>Contenido del reverso</label>
          <textarea id="certificate_content" name="course[certificate_content]" hidden data-cert-content-input><?php echo esc_textarea( $value( 'certificate_content' ) ); ?></textarea>
          <div class="idh-cert-content-summary" data-cert-content-summary></div>
          <button type="button" class="button" data-cert-content-open>Editar contenido del reverso</button>
          <input type="hidden" id="certificate_font_size" name="course[certificate_font_size]" value="<?php echo esc_attr( $value( 'certificate_font_size', '12' ) ?: '12' ); ?>" data-cert-size-input />
          <span class="description">Usa viñetas o listas para los temas; un párrafo suelto es un título de módulo (en el diseño Taller, todo sale con viñeta). Si lo dejas vacío, se usan los títulos de las clases.</span>
        </div>
        <?php $frozen_count = $course ? count( IDH_Aula_Certificates::frozen_for_course( $course->id ) ) : 0; ?>
        <?php if ( $frozen_count ) : ?>
          <div class="idh-cert-bulk">
            <label>
              <input type="checkbox" name="refresh_certificates" value="1" />
              <strong><?php echo esc_html( sprintf( _n( 'Actualizar también el certificado ya emitido de este curso', 'Actualizar también los %d certificados ya emitidos de este curso', $frozen_count ), $frozen_count ) ); ?></strong>
            </label>
            <span class="description">Al guardar, vuelve a congelar su contenido con los datos de esta ficha (título, horas, contenido del reverso, diseño, fechas de las clases) y los nombres actuales de los alumnos. Conservan su código, su QR, su fecha de emisión y si están habilitados o no.</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="idh-card">
        <h2>Portada</h2>
        <div class="idh-cover-preview" data-cover-preview><?php echo $cover_url ? '<img src="' . esc_url( $cover_url ) . '" alt="" />' : ''; ?></div>
        <input type="hidden" name="course[cover_image_id]" value="<?php echo (int) $cover_id; ?>" data-cover-input />
        <p>
          <button type="button" class="button" data-cover-select>Elegir imagen</button>
          <button type="button" class="button-link idh-link-danger" data-cover-remove <?php echo $cover_id ? '' : 'hidden'; ?>>Quitar</button>
        </p>
        <p class="description">Se muestra en la tarjeta del curso en el aula virtual.</p>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/partials/resource-picker.php'; ?>

<div class="idh-modal" data-cert-content-modal hidden>
  <div class="idh-modal-backdrop" data-cert-content-close></div>
  <div class="idh-modal-dialog idh-modal-dialog--wide" role="dialog" aria-modal="true" aria-label="Contenido del reverso">
    <div class="idh-modal-head">
      <h2>Contenido del reverso</h2>
      <button type="button" class="button-link" data-cert-content-close aria-label="Cerrar">&times;</button>
    </div>
    <p class="description">Negrita, viñetas, listas numeradas y enlaces. El color y la fuente los define el diseño del certificado; el tamaño del texto (A− / A+, de 8 a 16 pt) lo eliges en la barra y aplica a títulos y viñetas del diseño negro y dorado.</p>
    <?php
    wp_editor(
        $cert_html,
        'idh_cert_content_editor',
        [
            'textarea_name' => 'idh_cert_content_editor_unused',
            'media_buttons' => false,
            'quicktags'     => false,
            'wpautop'       => true,
            'editor_height' => 320,
            'tinymce'       => [
                'toolbar1'            => 'bold,bullist,numlist,link,unlink,undo,redo,|,idh_size_down,idh_size_label,idh_size_up',
                'toolbar2'            => '',
                'block_formats'       => 'Párrafo=p',
                'paste_as_text'       => false,
                'paste_remove_styles' => true,
                'paste_remove_styles_if_webkit' => true,
                'plugins'             => 'lists,link,paste,wordpress,wplink',
                'menubar'             => false,
                'statusbar'           => false,
                'content_style'       => 'body{font-family:sans-serif;font-size:14px}',
            ],
        ]
    );
    ?>
    <p class="idh-modal-actions">
      <button type="button" class="button button-primary" data-cert-content-apply>Aplicar</button>
      <button type="button" class="button" data-cert-content-close>Cancelar</button>
    </p>
  </div>
</div>
