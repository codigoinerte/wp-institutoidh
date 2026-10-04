<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$category = sanitize_title( wp_unslash( $_GET['category'] ?? '' ) );
$paged    = IDH_Aula_Admin::current_page();
list( $items, $total ) = IDH_Aula_Courses::search( [ 'search' => $search, 'category' => $category, 'per_page' => IDH_Aula_Admin::PER_PAGE, 'page' => $paged ] );
$base     = idh_aula_admin_url( 'idh-aula-cursos' );
?>
<h1 class="wp-heading-inline">Cursos</h1>
<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base ) ); ?>" class="page-title-action">Nuevo curso</a>
<hr class="wp-header-end">

<p class="description">Cursos, talleres, webinars y seminarios del aula virtual. Las categorías se administran en <a href="<?php echo esc_url( IDH_Aula_Categories::manage_url() ); ?>">Apariencia → Categorías de Portada</a>.</p>

<form method="get" class="idh-filters">
  <input type="hidden" name="page" value="idh-aula-cursos" />
  <select name="category">
    <option value="">Todas las categorías</option>
    <?php foreach ( IDH_Aula_Categories::options() as $value => $label ) : ?>
      <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $category, $value ); ?>><?php echo esc_html( $label ); ?></option>
    <?php endforeach; ?>
  </select>
  <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por título" />
  <button class="button">Filtrar</button>
</form>

<table class="wp-list-table widefat fixed striped idh-table">
  <thead>
    <tr>
      <th>Curso</th>
      <th style="width:170px">Categoría</th>
      <th style="width:100px">Inicio</th>
      <th style="width:80px">Clases</th>
      <th style="width:90px">Alumnos</th>
      <th style="width:100px">Pendientes</th>
      <th style="width:100px">Precio</th>
    </tr>
  </thead>
  <tbody>
    <?php if ( ! $items ) : ?>
      <tr><td colspan="7">No hay cursos. <a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base ) ); ?>">Crea el primero</a>.</td></tr>
    <?php endif; ?>
    <?php foreach ( $items as $item ) :
        $edit = add_query_arg( [ 'action' => 'edit', 'id' => $item->id ], $base ); ?>
      <tr>
        <td>
          <strong><a class="row-title" href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( $item->title ); ?></a><?php echo 'draft' === $item->status ? ' — <span class="post-state">Borrador</span>' : ''; ?></strong>
          <div class="row-actions">
            <span><a href="<?php echo esc_url( $edit ); ?>">Editar</a> | </span>
            <span><a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $item->id ] ) ); ?>">Alumnos</a> | </span>
            <span><a href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_duplicate_course', [ 'id' => $item->id ] ) ); ?>">Duplicar</a><?php echo $item->students_count ? '' : ' | '; ?></span>
            <?php if ( ! $item->students_count ) : ?>
              <span class="trash"><a href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_course', [ 'id' => $item->id ] ) ); ?>" data-confirm="¿Eliminar este curso y sus clases?">Eliminar</a></span>
            <?php endif; ?>
          </div>
        </td>
        <td><?php echo IDH_Aula_Admin::category_badge( $item->category ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
        <td><?php echo esc_html( idh_aula_format_date( $item->start_date ) ?: '—' ); ?></td>
        <td><?php echo (int) $item->sessions_count; ?></td>
        <td><a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $item->id ] ) ); ?>"><?php echo (int) $item->students_count; ?></a></td>
        <td><?php echo $item->pending_count ? '<a href="' . esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $item->id, 'payment' => 'pending' ] ) ) . '" class="idh-badge idh-badge--warning">' . (int) $item->pending_count . '</a>' : '—'; ?></td>
        <td><?php echo esc_html( idh_aula_money( $item->price ) ); ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php IDH_Aula_Admin::pagination( $total, IDH_Aula_Admin::PER_PAGE, $paged ); ?>
