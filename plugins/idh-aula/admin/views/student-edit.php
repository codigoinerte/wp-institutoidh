<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$id      = absint( $_GET['id'] ?? 0 );
$student = $id ? IDH_Aula_Students::find( $id ) : null;
if ( $id && ! $student ) {
    echo '<div class="notice notice-error"><p>Alumno no encontrado.</p></div>';
    return;
}
$old    = IDH_Aula_Admin::old();
$value  = static function ( $key ) use ( $old, $student ) {
    if ( isset( $old[ $key ] ) ) {
        return $old[ $key ];
    }
    return $student ? (string) ( $student->$key ?? '' ) : '';
};
$list   = idh_aula_admin_url( 'idh-aula-alumnos' );
$self   = $student ? idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $student->id ] ) : '';
$enrollments = $student ? IDH_Aula_Enrollments::for_student( $student->id ) : [];
$is_foreign  = isset( $old['doc_type'] )
    ? IDH_Aula_Documents::FOREIGN_TYPE === $old['doc_type']
    : ( $student && IDH_Aula_Documents::FOREIGN_TYPE === $student->doc_type );
// Código ya asignado: solo existe si el alumno guardado es extranjero (al cambiar el tipo se genera otro).
$has_code    = $student && IDH_Aula_Documents::FOREIGN_TYPE === $student->doc_type;
$code        = $has_code ? IDH_Aula_Documents::display( $student->doc_number, $student->doc_type ) : '';
?>
<h1 class="wp-heading-inline"><?php echo $student ? esc_html( IDH_Aula_Students::full_name( $student ) ) : 'Registrar alumno'; ?></h1>
<?php if ( $student ) : ?>
  <?php echo 'active' === $student->status ? '<span class="idh-badge idh-badge--success idh-heading-badge">Activo</span>' : '<span class="idh-badge idh-badge--danger idh-heading-badge">Bloqueado</span>'; ?>
<?php endif; ?>
<a href="<?php echo esc_url( $list ); ?>" class="page-title-action">← Volver a alumnos</a>
<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $list ) ); ?>" class="page-title-action idh-action-new">+ Nuevo alumno</a>
<hr class="wp-header-end">

<div class="idh-columns-layout">
  <div class="idh-col-main">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card">
      <h2>Datos del alumno</h2>
      <input type="hidden" name="action" value="idh_aula_save_student" />
      <input type="hidden" name="id" value="<?php echo (int) $id; ?>" />
      <?php wp_nonce_field( 'idh_aula_save_student' ); ?>

      <p class="idh-field">
        <label class="idh-switch">
          <input type="checkbox" name="student[is_foreign]" value="1" id="is_foreign" data-foreign-toggle <?php checked( $is_foreign ); ?> />
          <span>Alumno extranjero (no tiene DNI peruano)</span>
        </label>
      </p>

      <div class="idh-grid-2">
        <p class="idh-field" data-foreign-hide>
          <label for="doc_number">DNI <span class="required">*</span></label>
          <input type="text" id="doc_number" name="student[doc_number]" value="<?php echo esc_attr( $is_foreign ? '' : $value( 'doc_number' ) ); ?>" inputmode="numeric" maxlength="8" pattern="\d{8}" <?php echo $is_foreign ? '' : 'required'; ?> class="regular-text" data-foreign-required />
          <span class="description">8 dígitos. Es el usuario de acceso del alumno.</span>
        </p>
        <p class="idh-field" data-foreign-show hidden>
          <label>Código de alumno</label>
          <?php if ( $code && $is_foreign ) : ?>
            <span class="idh-code"><code><?php echo esc_html( $code ); ?></code>
              <button type="button" class="button-link" data-copy="<?php echo esc_attr( $code ); ?>">Copiar</button></span>
            <span class="description">Es el usuario de acceso del alumno, en lugar del DNI. Entrégaselo junto con la indicación de que ingrese con su fecha de nacimiento.</span>
          <?php else : ?>
            <span class="description">Se genera automáticamente al guardar (ej.: EXT-K7M2-QX9R). Quedará visible aquí para que se lo entregues al alumno.</span>
          <?php endif; ?>
        </p>
        <p class="idh-field">
          <label for="birth_date">Fecha de nacimiento <span class="required">*</span></label>
          <input type="date" id="birth_date" name="student[birth_date]" value="<?php echo esc_attr( $value( 'birth_date' ) ); ?>" required max="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" />
          <span class="description">El alumno la usa como segundo dato para ingresar.</span>
        </p>
        <p class="idh-field">
          <label for="first_name">Nombres <span class="required">*</span></label>
          <input type="text" id="first_name" name="student[first_name]" value="<?php echo esc_attr( $value( 'first_name' ) ); ?>" required class="regular-text" />
        </p>
        <p class="idh-field">
          <label for="last_name">Apellidos <span class="required">*</span></label>
          <input type="text" id="last_name" name="student[last_name]" value="<?php echo esc_attr( $value( 'last_name' ) ); ?>" required class="regular-text" />
        </p>
        <p class="idh-field">
          <label for="phone">Celular</label>
          <input type="tel" id="phone" name="student[phone]" value="<?php echo esc_attr( $value( 'phone' ) ); ?>" class="regular-text" />
        </p>
        <p class="idh-field">
          <label for="email">Correo</label>
          <input type="email" id="email" name="student[email]" value="<?php echo esc_attr( $value( 'email' ) ); ?>" class="regular-text" />
        </p>
      </div>
      <p class="idh-field">
        <label for="notes">Notas internas</label>
        <textarea id="notes" name="student[notes]" rows="2" class="large-text"><?php echo esc_textarea( $value( 'notes' ) ); ?></textarea>
      </p>
      <p><button class="button button-primary"><?php echo $student ? 'Guardar cambios' : 'Registrar alumno'; ?></button></p>
    </form>

    <?php if ( $student ) : ?>
      <div class="idh-card" id="matriculas">
        <div class="idh-card-head">
          <h2>Cursos matriculados (<?php echo count( $enrollments ); ?>)</h2>
        </div>

        <?php
        // Solo se ofrecen los cursos en los que aún no está matriculado.
        $enrolled_ids    = array_map( 'intval', wp_list_pluck( $enrollments, 'course_id' ) );
        $available_count = count( array_diff( array_map( 'intval', wp_list_pluck( IDH_Aula_Courses::all(), 'id' ) ), $enrolled_ids ) );
        ?>
        <?php if ( $available_count ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-inline-form">
            <input type="hidden" name="action" value="idh_aula_enroll_student" />
            <input type="hidden" name="student_id" value="<?php echo (int) $student->id; ?>" />
            <?php wp_nonce_field( 'idh_aula_enroll_student' ); ?>
            <select name="course_id" required><?php echo IDH_Aula_Admin::course_options( 0, 'Selecciona un curso', $enrolled_ids ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
            <select name="payment_status">
              <option value="pending">Pago pendiente</option>
              <option value="paid">Pagado</option>
            </select>
            <button class="button button-secondary">Matricular</button>
            <div class="idh-scope-holder" data-scope-holder></div>
          </form>
        <?php else : ?>
          <p class="idh-inline-form description">Este alumno ya está matriculado en todos los cursos.</p>
        <?php endif; ?>

        <?php if ( ! $enrollments ) : ?>
          <p class="idh-empty">Este alumno aún no está matriculado en ningún curso.</p>
        <?php endif; ?>

        <?php foreach ( $enrollments as $e ) :
            idh_aula_view( 'admin/views/partials/enrollment-form.php', [ 'e' => $e, 'redirect' => $self . '#matricula-' . $e->id, 'show_student' => false ] );
        endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( $student ) : ?>
    <div class="idh-col-side">
      <div class="idh-card">
        <h2>Acceso al aula</h2>
        <?php if ( 'active' === $student->status ) : ?>
          <p>El alumno puede ingresar con su <?php echo $has_code ? 'código de alumno' : 'DNI'; ?> y fecha de nacimiento.</p>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="idh_aula_student_status" />
            <input type="hidden" name="id" value="<?php echo (int) $student->id; ?>" />
            <input type="hidden" name="status" value="blocked" />
            <?php wp_nonce_field( 'idh_aula_student_status' ); ?>
            <p class="idh-field">
              <label for="reason">Motivo (interno)</label>
              <input type="text" id="reason" name="reason" class="widefat" placeholder="Ej.: se retiró del programa" />
            </p>
            <button class="button idh-button-danger" data-confirm="¿Bloquear a este alumno? No podrá ingresar al aula ni descargar sus certificados.">Bloquear alumno</button>
          </form>
        <?php else : ?>
          <p><strong>Bloqueado.</strong> No puede ingresar ni descargar certificados servidos por la plataforma.</p>
          <?php if ( $student->blocked_reason ) : ?>
            <p class="description">Motivo: <?php echo esc_html( $student->blocked_reason ); ?></p>
          <?php endif; ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="idh_aula_student_status" />
            <input type="hidden" name="id" value="<?php echo (int) $student->id; ?>" />
            <input type="hidden" name="status" value="active" />
            <?php wp_nonce_field( 'idh_aula_student_status' ); ?>
            <button class="button button-primary">Desbloquear alumno</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="idh-card">
        <h2>Resumen</h2>
        <?php
        $paid    = count( array_filter( $enrollments, static function ( $e ) { return 'paid' === $e->payment_status; } ) );
        $pending = count( $enrollments ) - $paid;
        $certs   = count( array_filter( $enrollments, static function ( $e ) { return (bool) $e->certificate_id; } ) );
        ?>
        <ul class="idh-summary">
          <li><span>Cursos pagados</span><strong><?php echo (int) $paid; ?></strong></li>
          <li><span>Pendientes de pago</span><strong class="<?php echo $pending ? 'idh-text-warning' : ''; ?>"><?php echo (int) $pending; ?></strong></li>
          <li><span>Certificados</span><strong><?php echo (int) $certs; ?></strong></li>
          <li><span>Registrado</span><strong><?php echo esc_html( idh_aula_format_date( $student->created_at ) ); ?></strong></li>
        </ul>
        <?php if ( 0 === count( $enrollments ) ) : ?>
          <p><a class="idh-link-danger" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_student', [ 'id' => $student->id ] ) ); ?>" data-confirm="¿Eliminar definitivamente a este alumno?">Eliminar alumno</a></p>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
