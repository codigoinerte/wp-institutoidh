<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$search  = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$status  = sanitize_key( $_GET['status'] ?? '' );
$paged   = IDH_Aula_Admin::current_page();
$args    = [ 'search' => $search, 'status' => $status ];
$total   = IDH_Aula_Students::count( $args );
$items   = IDH_Aula_Students::search( array_merge( $args, [ 'per_page' => IDH_Aula_Admin::PER_PAGE, 'page' => $paged ] ) );
$base    = idh_aula_admin_url( 'idh-aula-alumnos' );
$export  = wp_nonce_url( add_query_arg( [ 'action' => 'idh_aula_export', 'type' => 'students', 's' => $search, 'status' => $status ], admin_url( 'admin-post.php' ) ), 'idh_aula_export' );
$filters = [ '' => 'Todos', 'active' => 'Activos', 'blocked' => 'Bloqueados' ];
?>
<h1 class="wp-heading-inline">Alumnos</h1>
<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base ) ); ?>" class="page-title-action">Registrar alumno</a>
<a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-importar' ) ); ?>" class="page-title-action">Importar CSV</a>
<a href="<?php echo esc_url( $export ); ?>" class="page-title-action">Exportar CSV</a>
<hr class="wp-header-end">

<ul class="subsubsub">
  <?php $i = 0; foreach ( $filters as $value => $label ) : ?>
    <li><a href="<?php echo esc_url( add_query_arg( [ 'status' => $value, 's' => $search ], $base ) ); ?>" class="<?php echo $status === $value ? 'current' : ''; ?>"><?php echo esc_html( $label ); ?></a><?php echo ++$i < count( $filters ) ? ' |' : ''; ?></li>
  <?php endforeach; ?>
</ul>

<form method="get" class="search-form idh-search">
  <input type="hidden" name="page" value="idh-aula-alumnos" />
  <input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" />
  <label class="screen-reader-text" for="idh-student-search">Buscar alumno</label>
  <input type="search" id="idh-student-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="DNI, nombre, celular o correo" />
  <button class="button">Buscar</button>
</form>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="idh-students-bulk">
  <input type="hidden" name="action" value="idh_aula_bulk_enroll" />
  <?php wp_nonce_field( 'idh_aula_bulk_enroll' ); ?>

  <div class="tablenav top">
    <div class="alignleft actions idh-bulk-enroll">
      <label class="screen-reader-text" for="idh-bulk-course">Matricular seleccionados en</label>
      <select name="course_id" id="idh-bulk-course"><?php echo IDH_Aula_Admin::course_options( 0, 'Matricular seleccionados en…' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
      <select name="payment_status">
        <option value="pending">Pago pendiente</option>
        <option value="paid">Pagado</option>
      </select>
      <button class="button" data-requires-selection>Matricular</button>
    </div>
  </div>

  <table class="wp-list-table widefat fixed striped idh-table">
    <thead>
      <tr>
        <td class="manage-column check-column"><input type="checkbox" data-check-all /></td>
        <th style="width:110px">DNI</th>
        <th>Alumno</th>
        <th>Contacto</th>
        <th style="width:90px">Cursos</th>
        <th style="width:120px">Pendientes de pago</th>
        <th style="width:110px">Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php if ( ! $items ) : ?>
        <tr><td colspan="7"><?php echo $search ? 'No se encontraron alumnos para «' . esc_html( $search ) . '».' : 'Aún no hay alumnos registrados.'; ?></td></tr>
      <?php endif; ?>
      <?php foreach ( $items as $item ) :
          $edit = add_query_arg( [ 'action' => 'edit', 'id' => $item->id ], $base ); ?>
        <tr>
          <th scope="row" class="check-column"><input type="checkbox" name="student_ids[]" value="<?php echo (int) $item->id; ?>" /></th>
          <td><code><?php echo esc_html( $item->doc_number ); ?></code></td>
          <td>
            <strong><a class="row-title" href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( IDH_Aula_Students::full_name( $item ) ); ?></a></strong>
            <div class="row-actions">
              <span><a href="<?php echo esc_url( $edit ); ?>">Ver / editar</a> | </span>
              <span><a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 's' => $item->doc_number ] ) ); ?>">Matrículas</a></span>
            </div>
          </td>
          <td><?php echo esc_html( $item->phone ); ?><?php echo $item->email ? '<br><span class="description">' . esc_html( $item->email ) . '</span>' : ''; ?></td>
          <td><?php echo (int) $item->courses_count; ?></td>
          <td><?php echo $item->pending_count ? '<span class="idh-badge idh-badge--warning">' . (int) $item->pending_count . '</span>' : '—'; ?></td>
          <td><?php echo 'active' === $item->status ? '<span class="idh-badge idh-badge--success">Activo</span>' : '<span class="idh-badge idh-badge--danger">Bloqueado</span>'; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</form>
<?php IDH_Aula_Admin::pagination( $total, IDH_Aula_Admin::PER_PAGE, $paged ); ?>
