<?php
/**
 * @var string $error
 * @var array  $old
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
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( idh_aula_portal_url() ); ?>" novalidate>
      <input type="hidden" name="idh_aula_login" value="1" />
      <input type="hidden" name="_idh_nonce" value="<?php echo esc_attr( wp_create_nonce( 'idh_aula_login' ) ); ?>" />

      <div class="field">
        <label for="doc_number">DNI</label>
        <input id="doc_number" name="doc_number" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8"
               autocomplete="username" placeholder="12345678" required autofocus
               value="<?php echo esc_attr( $old['doc_number'] ); ?>" />
      </div>

      <div class="field">
        <label for="birth_date">Fecha de nacimiento</label>
        <input id="birth_date" name="birth_date" type="date" required max="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"
               value="<?php echo esc_attr( idh_aula_parse_date( $old['birth_date'] ) ); ?>" />
      </div>

      <button type="submit" class="btn-primary">Ingresar</button>
    </form>

    <p class="login-help">¿Problemas para ingresar? Escríbenos por WhatsApp y te ayudamos.</p>
  </div>
</main>
