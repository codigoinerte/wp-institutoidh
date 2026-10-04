<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$search  = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$type    = sanitize_key( $_GET['type'] ?? '' );
$paged   = IDH_Aula_Admin::current_page();
$edit_id = absint( $_GET['edit'] ?? 0 );
$editing = $edit_id ? IDH_Aula_Resources::find( $edit_id ) : null;
$old     = IDH_Aula_Admin::old();
$value   = static function ( $key ) use ( $old, $editing ) {
    return $old[ $key ] ?? ( $editing ? $editing->$key : '' );
};
list( $items, $total ) = IDH_Aula_Resources::search( [ 'search' => $search, 'type' => $type, 'per_page' => IDH_Aula_Admin::PER_PAGE, 'page' => $paged ] );
$base = idh_aula_admin_url( 'idh-aula-recursos' );
?>
<h1 class="wp-heading-inline">Biblioteca de recursos</h1>
<hr class="wp-header-end">
<p class="description">Videos de YouTube, carpetas de Google Drive y enlaces. Regístralos una vez y reutilízalos en las clases de varios cursos. Si cambias la URL aquí, se actualiza en todos los cursos.</p>

<div class="idh-columns-layout idh-columns-layout--left">
  <div class="idh-col-side">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card">
      <h2><?php echo $editing ? 'Editar recurso' : 'Agregar recurso'; ?></h2>
      <input type="hidden" name="action" value="idh_aula_save_resource" />
      <input type="hidden" name="id" value="<?php echo (int) $edit_id; ?>" />
      <?php wp_nonce_field( 'idh_aula_save_resource' ); ?>
      <p class="idh-field">
        <label for="res-type">Tipo</label>
        <select id="res-type" name="resource[type]" class="widefat">
          <?php foreach ( IDH_Aula_Resources::TYPES as $val => $label ) : ?>
            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $value( 'type' ), $val ); ?>><?php echo esc_html( $label ); ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <p class="idh-field">
        <label for="res-title">Título</label>
        <input type="text" id="res-title" name="resource[title]" value="<?php echo esc_attr( $value( 'title' ) ); ?>" class="widefat" required />
        <span class="description">Es el texto que verá el alumno.</span>
      </p>
      <p class="idh-field">
        <label for="res-url">URL</label>
        <input type="url" id="res-url" name="resource[url]" value="<?php echo esc_attr( $value( 'url' ) ); ?>" class="widefat" required />
      </p>
      <p>
        <button class="button button-primary"><?php echo $editing ? 'Guardar cambios' : 'Agregar recurso'; ?></button>
        <?php if ( $editing ) : ?><a href="<?php echo esc_url( $base ); ?>" class="button-link">Cancelar</a><?php endif; ?>
      </p>
    </form>
  </div>

  <div class="idh-col-main">
    <form method="get" class="idh-filters">
      <input type="hidden" name="page" value="idh-aula-recursos" />
      <select name="type">
        <option value="">Todos los tipos</option>
        <?php foreach ( IDH_Aula_Resources::TYPES as $val => $label ) : ?>
          <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $type, $val ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por título o URL" />
      <button class="button">Filtrar</button>
    </form>

    <table class="wp-list-table widefat fixed striped idh-table">
      <thead><tr><th>Recurso</th><th style="width:170px">Tipo</th><th style="width:110px">Usado en</th></tr></thead>
      <tbody>
        <?php if ( ! $items ) : ?>
          <tr><td colspan="3">No hay recursos.</td></tr>
        <?php endif; ?>
        <?php foreach ( $items as $item ) : ?>
          <tr>
            <td>
              <strong><?php echo esc_html( $item->title ); ?></strong><br>
              <a href="<?php echo esc_url( $item->url ); ?>" target="_blank" rel="noopener" class="idh-url"><?php echo esc_html( $item->url ); ?></a>
              <div class="row-actions">
                <span><a href="<?php echo esc_url( add_query_arg( 'edit', $item->id, $base ) ); ?>">Editar</a><?php echo $item->usage_count ? '' : ' | '; ?></span>
                <?php if ( ! $item->usage_count ) : ?>
                  <span class="trash"><a href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_resource', [ 'id' => $item->id ] ) ); ?>" data-confirm="¿Eliminar este recurso?">Eliminar</a></span>
                <?php endif; ?>
              </div>
            </td>
            <td><span class="idh-chip-type idh-chip--<?php echo esc_attr( $item->type ); ?>"><?php echo esc_html( IDH_Aula_Resources::TYPES[ $item->type ] ?? $item->type ); ?></span></td>
            <td><?php echo (int) $item->usage_count; ?> curso(s)</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php IDH_Aula_Admin::pagination( $total, IDH_Aula_Admin::PER_PAGE, $paged ); ?>
  </div>
</div>
