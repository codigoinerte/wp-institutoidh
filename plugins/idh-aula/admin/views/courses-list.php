<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$category = sanitize_title( wp_unslash( $_GET['category'] ?? '' ) );
$paged    = IDH_Aula_Admin::current_page();
$sorts    = IDH_Aula_Courses::SORTS;
$sort     = IDH_Aula_Admin::sort_request( $sorts );
list( $items, $total ) = IDH_Aula_Courses::search( [ 'search' => $search, 'category' => $category, 'per_page' => IDH_Aula_Admin::PER_PAGE, 'page' => $paged, 'orderby' => $sort[0], 'order' => $sort[1] ] );
$base     = idh_aula_admin_url( 'idh-aula-cursos' );
$sort_hidden  = [ 'page' => 'idh-aula-cursos', 'category' => $category, 's' => $search ];
$sort_options = [
    'created_at:desc' => 'Registro: más recientes primero',
    'created_at:asc'  => 'Registro: más antiguos primero',
    'updated_at:desc' => 'Última modificación: más reciente',
    'updated_at:asc'  => 'Última modificación: más antigua',
];
?>
<h1 class="wp-heading-inline">Cursos</h1>
<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base ) ); ?>" class="page-title-action idh-action-new">+ Nuevo curso</a>
<hr class="wp-header-end">

<p class="description">Cursos, talleres, webinars y seminarios del aula virtual. Las categorías se administran en <a href="<?php echo esc_url( IDH_Aula_Categories::manage_url() ); ?>">Apariencia → Categorías de Portada</a>.</p>

<div class="idh-filters-row">
<form method="get" class="idh-filters">
  <input type="hidden" name="page" value="idh-aula-cursos" />
  <input type="hidden" name="orderby" value="<?php echo esc_attr( $sort[0] ); ?>" />
  <input type="hidden" name="order" value="<?php echo esc_attr( $sort[1] ); ?>" />
  <select name="category">
    <option value="">Todas las categorías</option>
    <?php foreach ( IDH_Aula_Categories::options() as $value => $label ) : ?>
      <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $category, $value ); ?>><?php echo esc_html( $label ); ?></option>
    <?php endforeach; ?>
  </select>
  <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por título" />
  <button class="button">Filtrar</button>
</form>
<?php IDH_Aula_Admin::sort_form( $sort_options, $sort, $sort_hidden, 'top' ); ?>
</div>


<div class="idh-list">

<table class="wp-list-table widefat fixed striped idh-table">
  <thead>
    <tr>
      <?php
      IDH_Aula_Admin::sortable_th( 'Curso', 'title', $sorts, $sort );
      IDH_Aula_Admin::sortable_th( 'Categoría', 'category', $sorts, $sort, [], '170px' );
      IDH_Aula_Admin::sortable_th( 'Inicio', 'start_date', $sorts, $sort, [], '110px' );
      IDH_Aula_Admin::sortable_th( 'Clases', 'sessions_count', $sorts, $sort, [], '90px' );
      IDH_Aula_Admin::sortable_th( 'Alumnos', 'students_count', $sorts, $sort, [], '100px' );
      IDH_Aula_Admin::sortable_th( 'Pendientes', 'pending_count', $sorts, $sort, [], '110px' );
      IDH_Aula_Admin::sortable_th( 'Precio', 'price', $sorts, $sort, [], '100px' );
      ?>
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
<?php IDH_Aula_Admin::sort_form( $sort_options, $sort, $sort_hidden, 'bottom' ); ?>
</div>
