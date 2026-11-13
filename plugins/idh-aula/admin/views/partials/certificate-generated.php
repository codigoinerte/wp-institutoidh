<?php
/**
 * Campos del modo «Generado por la plataforma» (certificado general o de una clase).
 *
 * @var object|null $cert          Fila del certificado (null si aún no existe).
 * @var string      $name          Prefijo de los campos: 'certificate' o 'session_certs[ID]'.
 * @var int         $enrollment_id
 * @var int         $session_id    0 para el certificado general.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$generated = $cert && 'generated' === $cert->mode;
$enabled   = $generated && (int) $cert->enabled;
$frozen    = $generated && ! empty( $cert->snapshot );
$by        = $frozen && $cert->enabled_by ? get_userdata( (int) $cert->enabled_by ) : null;
// Aviso de datos incompletos del curso: el certificado (y el verificador) saldrían sin duración.
$enr_row   = IDH_Aula_Enrollments::find( $enrollment_id );
$course    = $enr_row ? IDH_Aula_Courses::find( $enr_row->course_id ) : null;
$no_hours  = $course && ! $session_id && ! preg_match( '/\d/', (string) $course->hours );
// Si el curso cambió después de congelar (horas, título o diseño), se sugiere «Actualizar contenido».
$changed = [];
if ( $frozen && $course ) {
    $snap      = IDH_Aula_Certificate_Generator::decode( $cert );
    $now_hours = preg_match( '/\d+(?:[.,]\d+)?/', (string) $course->hours, $m ) ? $m[0] : '';
    if ( (string) ( $snap['hours'] ?? '' ) !== $now_hours && ! $session_id ) {
        $changed[] = 'horas académicas (' . ( ( $snap['hours'] ?? '' ) !== '' ? $snap['hours'] : 'vacío' ) . ' → ' . ( '' !== $now_hours ? $now_hours : 'vacío' ) . ')';
    }
    if ( ( $snap['course_title'] ?? '' ) !== IDH_Aula_Certificate_Generator::course_title( $course ) ) {
        $changed[] = 'título del curso';
    }
    if ( ( $snap['course_type'] ?? '' ) !== IDH_Aula_Certificate_Generator::course_type( $course ) ) {
        $changed[] = 'categoría en el certificado';
    }
    if ( ( $snap['template'] ?? '' ) !== IDH_Aula_Certificate_Generator::template_for( $course ) ) {
        $changed[] = 'diseño';
    }
}
$course_url = $course ? idh_aula_admin_url( 'idh-aula-cursos', [ 'action' => 'edit', 'id' => $course->id ] ) : '';
$preview   = IDH_Aula_Admin::post_url( 'idh_aula_preview_certificate', [ 'enrollment_id' => $enrollment_id, 'session_id' => $session_id ] );
?>
<div class="idh-cert-generated" data-cert-for="generated">
  <?php if ( $no_hours ) : ?>
    <p class="idh-cert-warning">
      <strong>Este curso no tiene horas académicas:</strong> el certificado y el verificador saldrán sin la duración.
      <a href="<?php echo esc_url( $course_url ); ?>" target="_blank" rel="noopener">Completar en la ficha del curso</a><?php echo $frozen ? ' y luego marca «Actualizar contenido».' : ' antes de habilitarlo.'; ?>
    </p>
  <?php endif; ?>
  <?php if ( $changed ) : ?>
    <p class="idh-cert-warning">
      <strong>El curso cambió desde que se emitió este certificado:</strong> <?php echo esc_html( implode( ', ', $changed ) ); ?>.
      Marca «Actualizar contenido» y guarda para que el PDF y el verificador lo reflejen.
    </p>
  <?php endif; ?>
  <label class="idh-cert-enable">
    <input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $enabled ); ?> />
    <strong>Habilitar certificado</strong>
  </label>
  <p class="description">
    <?php if ( $enabled ) : ?>
      Habilitado el <?php echo esc_html( idh_aula_format_datetime( $cert->enabled_at ) ); ?><?php echo $by ? ' por ' . esc_html( $by->display_name ) : ''; ?>.
      El alumno lo descarga y el QR lo muestra como válido. Si lo desmarcas, queda <em>no vigente</em> (mismo código y QR).
    <?php elseif ( $frozen ) : ?>
      Deshabilitado: el alumno no lo ve y el verificador lo muestra como <em>no vigente</em>. Márcalo para habilitarlo de nuevo con el mismo código.
    <?php else : ?>
      Al habilitarlo se congela el contenido (nombre, curso, horas, período, temas y firmas) con la fecha y la hora. El PDF se genera cada vez que se descarga.
    <?php endif; ?>
  </p>
  <?php if ( $frozen ) : ?>
    <label class="idh-cert-refresh">
      <input type="checkbox" name="<?php echo esc_attr( $name ); ?>[refresh]" value="1" />
      Actualizar contenido con los datos actuales
    </label>
    <span class="description">Úsalo si corregiste el nombre, el curso o los temas. Conserva el código y el QR.</span>
  <?php endif; ?>
  <p class="idh-cert-links">
    <a href="<?php echo esc_url( add_query_arg( 'fresh', 1, $preview ) ); ?>" target="_blank" rel="noopener">Vista previa con los datos actuales</a>
    <?php if ( $frozen ) : ?>
      · <a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener">Ver el certificado emitido</a>
      · <a href="<?php echo esc_url( IDH_Aula_Certificate_Generator::verify_url( $cert->code ) ); ?>" target="_blank" rel="noopener">Abrir verificador</a>
    <?php endif; ?>
  </p>
</div>
