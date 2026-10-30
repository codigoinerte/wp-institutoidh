<?php
/**
 * Estados sin acceso: matrícula pendiente/bloqueada, curso o certificado inexistente.
 *
 * @var string      $state pending | locked | missing | certificate
 * @var object|null $enrollment
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$messages = [
    'pending'     => [ 'Pendiente de pago', 'Tu acceso a este curso está pendiente de pago. Cuando regularices tu pago podrás ver las clases, los materiales y tu certificado.' ],
    'locked'      => [ 'Acceso restringido', 'Tu acceso a este curso está restringido. Comunícate con IDH para más información.' ],
    'missing'     => [ 'No encontrado', 'El contenido que buscas no existe o no está disponible para tu cuenta.' ],
    'certificate' => [ 'Certificado no disponible', 'Este certificado no se encuentra disponible en este momento.' ],
    'certificate_pending' => [ 'Certificado retenido', 'Este certificado se habilitará cuando se complete el pago del curso. Si ya pagaste, comunícate con IDH para actualizar tu estado.' ],
];
list( $heading, $text ) = $messages[ $state ] ?? $messages['missing'];
if ( 'pending' === $state && ! empty( $enrollment->pending_lock_message ) ) {
    $text = $enrollment->pending_lock_message; // Mensaje personalizado del curso.
}
$student                = $student ?? null;
?>
<main class="aula-main">
  <div class="state-card">
    <div class="state-icon"><?php echo idh_aula_icon( in_array( $state, [ 'pending', 'locked', 'certificate_pending' ], true ) ? 'lock' : 'alert', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <h1 class="state-title"><?php echo esc_html( $heading ); ?></h1>
    <?php if ( ! empty( $enrollment ) ) : ?>
      <p class="state-text" style="font-weight:600;color:#0F1117;margin-bottom:6px"><?php echo esc_html( $enrollment->course_title ); ?></p>
    <?php endif; ?>
    <p class="state-text"><?php echo esc_html( $text ); ?></p>
    <?php if ( $student && ! empty( $enrollment ) && in_array( $state, [ 'pending', 'locked' ], true ) ) : ?>
      <div class="state-whatsapp"><?php echo idh_aula_whatsapp_button( $state, $student, $enrollment->course_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <?php endif; ?>
    <a class="state-action" href="<?php echo esc_url( idh_aula_portal_url() ); ?>"><?php echo $student ? 'Volver a mis cursos' : 'Ir al aula virtual'; ?></a>
  </div>
</main>
