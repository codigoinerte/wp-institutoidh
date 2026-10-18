<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$number  = idh_aula_whatsapp_number();
$preview = idh_aula_whatsapp_url( 'locked', (object) [ 'first_name' => 'Rosa', 'last_name' => 'Quispe Mamani', 'doc_number' => '72345678' ], 'Curso de ejemplo' );
?>
<h1 class="wp-heading-inline">Ajustes</h1>
<hr class="wp-header-end">

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="idh-card" style="max-width:720px">
  <h2>WhatsApp de IDH</h2>
  <input type="hidden" name="action" value="idh_aula_save_settings" />
  <?php wp_nonce_field( 'idh_aula_save_settings' ); ?>

  <p class="idh-field">
    <label for="whatsapp">Número de WhatsApp</label>
    <input type="text" id="whatsapp" name="whatsapp" value="<?php echo esc_attr( $number ); ?>" class="regular-text" inputmode="numeric" placeholder="51987654321" />
    <span class="description">Con el código de país y sin espacios ni signos (Perú: 51 + el número de 9 dígitos).</span>
  </p>
  <p class="description">
    El alumno ve un botón <strong>«Escribir a IDH por WhatsApp»</strong> en sus cursos con pago pendiente o con acceso restringido, en el ingreso al aula y cuando su acceso está deshabilitado.
    El mensaje ya va escrito con su nombre, su DNI y el curso. Si dejas el número vacío, los botones no se muestran.
  </p>
  <?php if ( $preview ) : ?>
    <p><a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener">Probar el enlace con un mensaje de ejemplo</a></p>
  <?php endif; ?>

  <?php submit_button( 'Guardar ajustes' ); ?>
</form>
