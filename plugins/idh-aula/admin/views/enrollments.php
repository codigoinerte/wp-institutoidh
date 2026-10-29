<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$filters = [
    'course_id'   => absint( $_GET['course_id'] ?? 0 ),
    'payment'     => sanitize_key( $_GET['payment'] ?? '' ),
    'locked'      => sanitize_text_field( wp_unslash( $_GET['locked'] ?? '' ) ),
    'certificate' => sanitize_key( $_GET['certificate'] ?? '' ),
    'search'      => sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ),
];
$paged  = IDH_Aula_Admin::current_page();
list( $items, $total ) = IDH_Aula_Enrollments::search( array_merge( $filters, [ 'per_page' => IDH_Aula_Admin::PER_PAGE, 'page' => $paged ] ) );
$course  = $filters['course_id'] ? IDH_Aula_Courses::find( $filters['course_id'] ) : null;
$partial = IDH_Aula_Enrollments::partial_summary( $items );
$export = wp_nonce_url(
    add_query_arg(
        [ 'action' => 'idh_aula_export', 'type' => 'enrollments', 'course_id' => $filters['course_id'], 'payment' => $filters['payment'], 'locked' => $filters['locked'], 'certificate' => $filters['certificate'], 's' => $filters['search'] ],
        admin_url( 'admin-post.php' )
    ),
    'idh_aula_export'
);
?>
<h1 class="wp-heading-inline">Matrículas<?php echo $course ? ': ' . esc_html( $course->title ) : ''; ?></h1>
<a href="#idh-single" class="page-title-action idh-action-new" data-toggle-target="#idh-single">+ Matricular alumno</a>
<a href="#idh-bulk" class="page-title-action" data-toggle-target="#idh-bulk">Matrícula masiva</a>
<a href="<?php echo esc_url( $export ); ?>" class="page-title-action">Exportar CSV</a>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card" id="idh-single" data-student-enroll <?php echo isset( $_GET['single'] ) ? '' : 'hidden'; ?>>
  <h2>Matricular alumno</h2>
  <p class="description">Elige el curso y busca al alumno por DNI, código o nombre. Puedes agregar uno o varios antes de matricular.</p>
  <input type="hidden" name="action" value="idh_aula_bulk_enroll" />
  <?php wp_nonce_field( 'idh_aula_bulk_enroll' ); ?>
  <div class="idh-grid-2">
    <p class="idh-field">
      <label>Curso</label>
      <select name="course_id" class="widefat" required data-enroll-course><?php echo IDH_Aula_Admin::course_options( $filters['course_id'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
    </p>
    <p class="idh-field">
      <label>Estado de pago inicial</label>
      <select name="payment_status">
        <option value="pending">Pendiente</option>
        <option value="paid">Pagado</option>
      </select>
    </p>
  </div>
  <div data-scope-holder></div>
  <div class="idh-field idh-student-search">
    <label for="idh-student-q">Alumno</label>
    <input type="search" id="idh-student-q" class="widefat" autocomplete="off" placeholder="Escribe el DNI, el código o el nombre (mín. 2 caracteres)" data-student-search />
    <ul class="idh-picker-list idh-student-results" data-student-results hidden></ul>
  </div>
  <ul class="idh-chips" data-student-selected></ul>
  <p class="description">¿No aparece? <a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'new' ] ) ); ?>">Regístralo primero</a>.</p>
  <button class="button button-primary" data-student-submit disabled>Matricular</button>
</form>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card" id="idh-bulk" <?php echo isset( $_GET['bulk'] ) ? '' : 'hidden'; ?>>
  <h2>Matrícula masiva</h2>
  <p class="description">Pega los DNIs o códigos de los alumnos (uno por línea, o separados por comas o espacios). Los que no estén registrados se listan al final para que los registres o importes.</p>
  <input type="hidden" name="action" value="idh_aula_bulk_enroll" />
  <?php wp_nonce_field( 'idh_aula_bulk_enroll' ); ?>
  <div class="idh-grid-2">
    <p class="idh-field">
      <label>Curso</label>
      <select name="course_id" class="widefat" required><?php echo IDH_Aula_Admin::course_options( $filters['course_id'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
    </p>
    <p class="idh-field">
      <label>Estado de pago inicial</label>
      <select name="payment_status">
        <option value="pending">Pendiente</option>
        <option value="paid">Pagado</option>
      </select>
    </p>
  </div>
  <div data-scope-holder></div>
  <p class="idh-field">
    <label>DNIs o códigos</label>
    <textarea name="dnis" rows="5" class="large-text code" placeholder="12345678&#10;87654321"></textarea>
  </p>
  <button class="button button-primary">Matricular</button>
</form>

<form method="get" class="idh-filters">
  <input type="hidden" name="page" value="idh-aula-matriculas" />
  <select name="course_id"><?php echo IDH_Aula_Admin::course_options( $filters['course_id'], 'Todos los cursos' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
  <select name="payment">
    <option value="">Pago: todos</option>
    <option value="paid" <?php selected( $filters['payment'], 'paid' ); ?>>Pagados</option>
    <option value="pending" <?php selected( $filters['payment'], 'pending' ); ?>>Pendientes</option>
  </select>
  <select name="locked">
    <option value="">Acceso: todos</option>
    <option value="0" <?php selected( $filters['locked'], '0' ); ?>>Con acceso</option>
    <option value="1" <?php selected( $filters['locked'], '1' ); ?>>Bloqueados</option>
  </select>
  <select name="certificate">
    <option value="">Certificado: todos</option>
    <option value="with" <?php selected( $filters['certificate'], 'with' ); ?>>Con certificado</option>
    <option value="without" <?php selected( $filters['certificate'], 'without' ); ?>>Sin certificado</option>
  </select>
  <input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="DNI, código o nombre" />
  <button class="button">Filtrar</button>
  <?php if ( array_filter( $filters ) ) : ?>
    <a class="button-link" href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas' ) ); ?>">Limpiar</a>
  <?php endif; ?>
</form>

<table class="wp-list-table widefat fixed striped idh-table">
  <thead>
    <tr>
      <th style="width:130px">DNI / Código</th>
      <th>Alumno</th>
      <th>Curso</th>
      <th style="width:130px">Pago</th>
      <th style="width:90px">Precio</th>
      <th style="width:120px">Acceso</th>
      <th style="width:130px">Certificado</th>
    </tr>
  </thead>
  <tbody>
    <?php if ( ! $items ) : ?>
      <tr><td colspan="7">No hay matrículas con estos filtros.</td></tr>
    <?php endif; ?>
    <?php foreach ( $items as $item ) :
        $student_url = idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $item->student_id ] ) . '#matricula-' . $item->id; ?>
      <tr data-enrollment="<?php echo (int) $item->id; ?>">
        <td><code><?php echo esc_html( IDH_Aula_Documents::display( $item->doc_number ) ); ?></code></td>
        <td>
          <strong><a href="<?php echo esc_url( $student_url ); ?>"><?php echo esc_html( $item->first_name . ' ' . $item->last_name ); ?></a></strong>
          <?php if ( 'blocked' === $item->student_status ) : ?><span class="idh-badge idh-badge--danger">Alumno bloqueado</span><?php endif; ?>
          <div class="row-actions"><span><a href="<?php echo esc_url( $student_url ); ?>">Editar matrícula y certificado</a></span></div>
        </td>
        <td>
          <?php echo esc_html( $item->course_title ); ?><br><?php echo IDH_Aula_Admin::category_badge( $item->category ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
          <?php if ( isset( $partial[ (int) $item->id ] ) ) : ?>
            <span class="idh-badge idh-badge--info"><?php echo esc_html( vsprintf( '%d de %d clases', $partial[ (int) $item->id ] ) ); ?></span>
          <?php endif; ?>
        </td>
        <td>
          <select data-inline-toggle="payment_status" class="idh-inline-select idh-pay-<?php echo esc_attr( $item->payment_status ); ?>">
            <option value="pending" <?php selected( $item->payment_status, 'pending' ); ?>>Pendiente</option>
            <option value="paid" <?php selected( $item->payment_status, 'paid' ); ?>>Pagado</option>
          </select>
        </td>
        <td><?php echo esc_html( idh_aula_money( $item->price_paid ) ); ?></td>
        <td>
          <label class="idh-switch idh-switch--compact">
            <input type="checkbox" data-inline-toggle="locked" <?php checked( (int) $item->locked, 1 ); ?> />
            <span><?php echo $item->locked ? '🔒 Bloqueado' : 'Con acceso'; ?></span>
          </label>
        </td>
        <td><?php echo $item->certificate_id ? '<span class="idh-badge idh-badge--success">' . esc_html( 'pdf' === $item->certificate_mode ? 'PDF' : 'Enlace' ) . '</span> <span class="description">' . esc_html( idh_aula_format_date( $item->issued_at ) ) . '</span>' : '<a href="' . esc_url( $student_url ) . '">Agregar</a>'; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php IDH_Aula_Admin::pagination( $total, IDH_Aula_Admin::PER_PAGE, $paged ); ?>
