<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$token  = sanitize_key( $_GET['token'] ?? '' );
$import = $token ? get_transient( 'idh_aula_import_' . $token ) : null;
$labels = [ 'new' => [ 'Nuevo', 'success' ], 'existing' => [ 'Ya registrado', 'info' ], 'error' => [ 'Error', 'danger' ] ];
?>
<h1 class="wp-heading-inline">Importar alumnos</h1>
<hr class="wp-header-end">

<?php
$codes_token = sanitize_key( $_GET['codes'] ?? '' );
$codes       = $codes_token ? get_transient( 'idh_aula_import_codes_' . $codes_token ) : null;
if ( $codes && (int) $codes['user'] === get_current_user_id() ) :
    $codes_csv = wp_nonce_url( add_query_arg( [ 'action' => 'idh_aula_export', 'type' => 'foreign_codes', 'token' => $codes_token ], admin_url( 'admin-post.php' ) ), 'idh_aula_export' ); ?>
  <div class="idh-card">
    <h2>Alumnos extranjeros importados (<?php echo count( $codes['rows'] ); ?>)</h2>
    <p>Estos alumnos ingresan con su <strong>código de acceso</strong> y su fecha de nacimiento. Descarga la lista y entrégales su código: esta tabla estará disponible por una hora, y el código también queda en la ficha de cada alumno.</p>
    <table class="wp-list-table widefat striped idh-table">
      <thead><tr><th>Alumno</th><th style="width:170px">Código de acceso</th><th style="width:130px">Nacimiento</th><th>Celular</th><th>Curso</th></tr></thead>
      <tbody>
        <?php foreach ( $codes['rows'] as $r ) : ?>
          <tr>
            <td><?php echo esc_html( $r['name'] ); ?></td>
            <td><code><?php echo esc_html( $r['code'] ); ?></code> <button type="button" class="button-link" data-copy="<?php echo esc_attr( $r['code'] ); ?>">Copiar</button></td>
            <td><?php echo esc_html( idh_aula_format_date( $r['birth'] ) ); ?></td>
            <td><?php echo esc_html( $r['phone'] ); ?></td>
            <td><?php echo $r['course'] ? esc_html( $r['course'] ) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p class="idh-actions-row"><a class="button button-primary" href="<?php echo esc_url( $codes_csv ); ?>">Descargar CSV con los códigos</a></p>
  </div>
<?php endif; ?>

<?php if ( $import && (int) $import['user'] === get_current_user_id() ) :
    $counts = array_count_values( wp_list_pluck( $import['rows'], 'status' ) ); ?>
  <div class="idh-card">
    <h2>Vista previa</h2>
    <p>
      <span class="idh-badge idh-badge--success"><?php echo (int) ( $counts['new'] ?? 0 ); ?> nuevos</span>
      <span class="idh-badge idh-badge--info"><?php echo (int) ( $counts['existing'] ?? 0 ); ?> ya registrados (solo se matriculan)</span>
      <span class="idh-badge idh-badge--danger"><?php echo (int) ( $counts['error'] ?? 0 ); ?> con errores (se omitirán)</span>
      · Pago inicial de las matrículas: <strong><?php echo 'paid' === $import['payment'] ? 'Pagado' : 'Pendiente'; ?></strong>
    </p>
    <table class="wp-list-table widefat striped idh-table">
      <thead><tr><th style="width:60px">Fila</th><th style="width:120px">Estado</th><th>DNI / Código</th><th>Alumno</th><th>Nacimiento</th><th>Celular</th><th>Curso</th><th>Observaciones</th></tr></thead>
      <tbody>
        <?php foreach ( $import['rows'] as $row ) :
            $d      = $row['data'];
            $course = $row['course_id'] ? IDH_Aula_Courses::find( $row['course_id'] ) : null;
            list( $label, $tone ) = $labels[ $row['status'] ]; ?>
          <tr>
            <td><?php echo (int) $row['line']; ?></td>
            <td><span class="idh-badge idh-badge--<?php echo esc_attr( $tone ); ?>"><?php echo esc_html( $label ); ?></span></td>
            <td>
              <?php if ( 'new' === $row['status'] && ! empty( $row['foreign'] ) ) : ?>
                <span class="idh-badge">Extranjero</span> <span class="description">código al confirmar</span>
              <?php else : ?>
                <?php echo ! empty( $d['doc_number'] ) ? '<code>' . esc_html( IDH_Aula_Documents::display( $d['doc_number'] ) ) . '</code>' : '—'; ?>
              <?php endif; ?>
            </td>
            <td><?php echo esc_html( trim( ( $d['first_name'] ?? '' ) . ' ' . ( $d['last_name'] ?? '' ) ) ); ?></td>
            <td><?php echo esc_html( idh_aula_format_date( $d['birth_date'] ?? '' ) ); ?></td>
            <td><?php echo esc_html( $d['phone'] ?? '' ); ?></td>
            <td><?php echo $course ? esc_html( $course->title ) : '—'; ?></td>
            <td class="idh-text-danger"><?php echo esc_html( implode( ' ', $row['errors'] ) ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-actions-row">
      <input type="hidden" name="action" value="idh_aula_import_confirm" />
      <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>" />
      <?php wp_nonce_field( 'idh_aula_import_confirm' ); ?>
      <button class="button button-primary" <?php disabled( empty( $counts['new'] ) && empty( $counts['existing'] ) ); ?>>Confirmar importación</button>
      <a class="button" href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-importar' ) ); ?>">Cancelar</a>
    </form>
  </div>
<?php else : ?>
  <?php if ( $token ) : ?>
    <div class="notice notice-warning"><p>La vista previa expiró. Vuelve a subir el archivo.</p></div>
  <?php endif; ?>
  <div class="idh-columns-layout">
    <div class="idh-col-main">
      <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card">
        <h2>1. Sube el archivo CSV</h2>
        <input type="hidden" name="action" value="idh_aula_import_preview" />
        <?php wp_nonce_field( 'idh_aula_import_preview' ); ?>
        <p class="idh-field">
          <label for="csv">Archivo (.csv)</label>
          <input type="file" id="csv" name="csv" accept=".csv,text/csv" required />
        </p>
        <div class="idh-grid-2">
          <p class="idh-field">
            <label>Matricular a todos en (opcional)</label>
            <select name="course_id" class="widefat"><?php echo IDH_Aula_Admin::course_options( 0, '— No matricular —' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
            <span class="description">La columna <code>curso_id</code> del archivo, si existe, tiene prioridad.</span>
          </p>
          <p class="idh-field">
            <label>Estado de pago inicial</label>
            <select name="payment_status">
              <option value="pending">Pendiente</option>
              <option value="paid">Pagado</option>
            </select>
          </p>
        </div>
        <button class="button button-primary">Ver vista previa</button>
        <p class="description">Nada se guarda hasta que confirmes en la vista previa.</p>
      </form>
    </div>
    <div class="idh-col-side">
      <div class="idh-card">
        <h2>Formato</h2>
        <p>Columnas: <code><?php echo esc_html( implode( ', ', IDH_Aula_CSV::IMPORT_COLUMNS ) ); ?></code></p>
        <ul class="ul-disc">
          <li><code>dni</code>, <code>nombres</code>, <code>apellidos</code> y <code>fecha_nacimiento</code> son obligatorios. El DNI puede ir vacío solo si el alumno es extranjero.</li>
          <li><code>extranjero</code> (opcional): escribe <code>sí</code> para alumnos sin DNI peruano. Se les genera un código de acceso y, al confirmar, verás una tabla descargable con los códigos. Si el mismo nombre y fecha de nacimiento ya existen, solo se matricula.</li>
          <li>Fecha en formato <code>dd/mm/aaaa</code> o <code>aaaa-mm-dd</code>.</li>
          <li>Si el DNI ya existe, no se modifican sus datos: solo se matricula.</li>
          <li>En Excel: <em>Archivo → Guardar como → CSV UTF-8</em>.</li>
        </ul>
        <p><a class="button" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_csv_template' ) ); ?>">Descargar plantilla</a></p>
      </div>
    </div>
  </div>
<?php endif; ?>
