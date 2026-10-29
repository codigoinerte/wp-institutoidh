<?php
/**
 * Clases incluidas en una matrícula: todas o solo algunas (pack de talleres por tema).
 * Se usa en la tarjeta de la matrícula y, cargado por AJAX al elegir el curso, en los formularios para matricular.
 *
 * @var object[] $sessions IDH_Aula_Courses::session_list() del curso.
 * @var string   $scope    'all' | 'partial'.
 * @var int[]    $selected Clases marcadas.
 * @var string   $prefix   Prefijo de los campos ('enrollment' → enrollment[access_scope]); vacío para campos sueltos.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$scope    = 'partial' === ( $scope ?? 'all' ) ? 'partial' : 'all';
$selected = array_map( 'intval', $selected ?? [] );
$prefix   = $prefix ?? '';
$field    = static function ( $key ) use ( $prefix ) {
    return $prefix ? "{$prefix}[{$key}]" : $key;
};
?>
<fieldset class="idh-scope" data-session-scope>
  <legend>Clases incluidas</legend>
  <?php if ( ! $sessions ) : ?>
    <input type="hidden" name="<?php echo esc_attr( $field( 'access_scope' ) ); ?>" value="all" />
    <p class="description">Este curso aún no tiene clases. El alumno tendrá acceso a todas las que se agreguen.</p>
  <?php else : ?>
    <div class="idh-scope-modes">
      <label><input type="radio" name="<?php echo esc_attr( $field( 'access_scope' ) ); ?>" value="all" <?php checked( $scope, 'all' ); ?> data-scope-mode /> Todas las clases</label>
      <label><input type="radio" name="<?php echo esc_attr( $field( 'access_scope' ) ); ?>" value="partial" <?php checked( $scope, 'partial' ); ?> data-scope-mode /> Solo algunas clases</label>
    </div>
    <div class="idh-scope-list" data-scope-list <?php echo 'partial' === $scope ? '' : 'hidden'; ?>>
      <?php foreach ( $sessions as $session ) : ?>
        <label class="idh-scope-item">
          <input type="checkbox" name="<?php echo esc_attr( $field( 'session_ids' ) ); ?>[]" value="<?php echo (int) $session->id; ?>" <?php checked( in_array( (int) $session->id, $selected, true ) ); ?> />
          <span><?php echo esc_html( $session->title ); ?></span>
          <?php if ( $session->session_datetime ) : ?>
            <span class="description"><?php echo esc_html( idh_aula_format_datetime( $session->session_datetime ) ); ?></span>
          <?php endif; ?>
        </label>
      <?php endforeach; ?>
    </div>
    <p class="description">
      <strong>Todas:</strong> incluye también las clases que se agreguen después.
      <strong>Solo algunas:</strong> el alumno ve las demás con candado, sin sus videos ni materiales.
    </p>
  <?php endif; ?>
</fieldset>
