<?php
/**
 * Verificador público del certificado (destino del QR): /verificar/{code}.
 *
 * @var array|null $verify Datos de IDH_Aula_Router::verify_data() o null si el código no existe.
 * @var string     $code
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$student = null; // Página pública: sin menú del alumno.
$v       = $verify ?? null;
?>
<main class="aula-main aula-main--narrow">
  <div class="verify-card">
    <?php if ( ! $v ) : ?>
      <div class="verify-status verify-status--missing">
        <span class="verify-status-icon"><?php echo idh_aula_icon( 'alert', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
        <div>
          <h1 class="verify-title">Certificado no encontrado</h1>
          <p class="verify-sub">No existe un certificado emitido por IDH con el código <strong><?php echo esc_html( $code ); ?></strong>. Revisa que el código esté bien escrito.</p>
        </div>
      </div>
    <?php else : ?>
      <?php if ( $v['valid'] ) : ?>
        <div class="verify-status verify-status--ok">
          <span class="verify-status-icon"><?php echo idh_aula_icon( 'shield', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
          <div>
            <h1 class="verify-title">Certificado válido</h1>
            <p class="verify-sub">Emitido por el Instituto Desarrollo de Habilidades Profesionales (IDH).</p>
          </div>
        </div>
      <?php else : ?>
        <div class="verify-status verify-status--void">
          <span class="verify-status-icon"><?php echo idh_aula_icon( 'alert', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
          <div>
            <h1 class="verify-title">Certificado no vigente</h1>
            <p class="verify-sub">Este certificado fue emitido por IDH, pero en este momento no se encuentra vigente. Para más información, comunícate con IDH.</p>
          </div>
        </div>
      <?php endif; ?>

      <dl class="verify-data">
        <div><dt>Otorgado a</dt><dd class="verify-name"><?php echo esc_html( $v['student_name'] ); ?></dd></div>
        <div><dt>Documento</dt><dd><?php echo esc_html( $v['document'] ); ?></dd></div>
        <div><dt><?php echo esc_html( $v['course_type'] ); ?></dt><dd><?php echo esc_html( $v['course_title'] ); ?></dd></div>
        <?php if ( $v['session_title'] ) : ?>
          <div><dt>Clase</dt><dd><?php echo esc_html( $v['session_title'] ); ?></dd></div>
        <?php endif; ?>
        <?php if ( $v['hours'] ) : ?>
          <div><dt>Duración</dt><dd><?php echo esc_html( is_numeric( str_replace( ',', '.', $v['hours'] ) ) ? $v['hours'] . ' horas académicas' : $v['hours'] ); ?></dd></div>
        <?php endif; ?>
        <?php if ( $v['period'] ) : ?>
          <div><dt>Período</dt><dd><?php echo esc_html( ucfirst( $v['period'] ) ); ?></dd></div>
        <?php endif; ?>
        <div><dt>Fecha de emisión</dt><dd><?php echo esc_html( idh_aula_format_date( $v['issued_at'] ) ); ?></dd></div>
        <?php if ( $v['signers'] ) : ?>
          <div><dt>Firmado por</dt><dd>
            <?php foreach ( $v['signers'] as $signer ) : ?>
              <span class="verify-signer"><?php echo esc_html( $signer['name'] ); ?><?php echo $signer['role'] ? ' · ' . esc_html( $signer['role'] ) : ''; ?></span>
            <?php endforeach; ?>
          </dd></div>
        <?php endif; ?>
        <div><dt>Código</dt><dd><code><?php echo esc_html( $v['cert']->code ); ?></code></dd></div>
      </dl>

      <?php if ( $v['pdf_available'] ) : ?>
        <a class="verify-pdf" href="<?php echo esc_url( $v['pdf_url'] ); ?>" target="_blank" rel="noopener">
          <?php echo idh_aula_icon( 'award', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Ver certificado
        </a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
