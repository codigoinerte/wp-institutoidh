<?php
/**
 * @var string      $error
 * @var string      $error_code invalid | rate | blocked | ''
 * @var object|null $blocked    Alumno bloqueado que intentó ingresar.
 * @var array       $old
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<main class="login-wrap">
  <div class="login-card">
    <div class="login-icon"><?php echo idh_aula_icon( 'book', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
    <h1 class="login-title">Aula virtual</h1>
    <p class="login-subtitle">Ingresa con tu DNI y tu fecha de nacimiento para ver tus cursos, clases grabadas, materiales y certificados.</p>

    <?php if ( $error ) : ?>
      <div class="alert alert-error" role="alert"><?php echo esc_html( $error ); ?></div>
      <?php if ( 'blocked' === $error_code ) : ?>
        <?php echo idh_aula_whatsapp_button( 'blocked', $blocked ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endif; ?>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( idh_aula_portal_url() ); ?>" novalidate data-login-form>
      <input type="hidden" name="idh_aula_login" value="1" />
      <input type="hidden" name="_idh_nonce" value="<?php echo esc_attr( wp_create_nonce( 'idh_aula_login' ) ); ?>" />

      <div class="field">
        <label for="doc_display">DNI</label>
        <div class="field-password">
          <input id="doc_display" type="text" inputmode="numeric" maxlength="8"
                 autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="12345678" required autofocus
                 data-doc-display />
          <input type="hidden" id="doc_number" name="doc_number" value="<?php echo esc_attr( $old['doc_number'] ); ?>" />
          <button type="button" class="field-eye" data-doc-eye aria-label="Mostrar DNI" aria-pressed="false">
            <?php echo idh_aula_icon( 'eye', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
          </button>
        </div>
      </div>

      <div class="field">
        <label for="birth_date">Fecha de nacimiento</label>
        <input id="birth_date" name="birth_date" type="date" required max="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"
               value="<?php echo esc_attr( idh_aula_parse_date( $old['birth_date'] ) ); ?>" />
      </div>

      <label class="remember">
        <input type="checkbox" name="remember" value="1" data-remember />
        <span>Recordar mis datos en este dispositivo</span>
      </label>
      <p class="remember-hint" data-remember-hint hidden>
        Tus datos están guardados en este dispositivo. <button type="button" class="link-btn" data-forget>Olvidar mis datos</button>
      </p>

      <button type="submit" class="btn-primary">Ingresar</button>
    </form>

    <p class="remember-note">No marques «Recordar» si usas una computadora compartida o de una cabina. Por seguridad, tu sesión se cierra sola después de 24 horas.</p>

    <?php if ( idh_aula_whatsapp_number() && 'blocked' !== $error_code ) : ?>
      <p class="login-help">¿Problemas para ingresar?</p>
      <?php echo idh_aula_whatsapp_button( 'login', null, '', 'Escríbenos por WhatsApp', 'btn-whatsapp--outline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
    <?php elseif ( ! idh_aula_whatsapp_number() ) : ?>
      <p class="login-help">¿Problemas para ingresar? Escríbenos por WhatsApp y te ayudamos.</p>
    <?php endif; ?>
  </div>
</main>
