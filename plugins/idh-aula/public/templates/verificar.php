<?php
/**
 * Verificador público del certificado (destino del QR): /verificar/{code}.
 *
 * Página propia, sin la barra del aula. Replica el diseño de la página de verificación que IDH
 * usaba en qr.io (fondo celeste, tarjeta con el logo superpuesto, texto del certificado,
 * contactos y redes sociales). Además muestra si el certificado está vigente y, si se puede,
 * el botón para ver el PDF.
 *
 * @var array|null $verify Datos de IDH_Aula_Router::verify_data() o null si el código no existe.
 * @var string     $code
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$v    = $verify ?? null;
$org  = IDH_Aula_Certificate_Generator::ORG;
$logo = IDH_AULA_URL . 'assets/certificados/azul/logo.png';
$css  = IDH_AULA_DIR . 'public/assets/verificar.css';
$ver  = file_exists( $css ) ? filemtime( $css ) : IDH_AULA_VERSION;
$site = preg_replace( '#^https?://#', '', $org['website'] );

// Íconos (trazo, estilo Feather/Lucide, licencia ISC/MIT).
$svg = static function ( $name, $size = 16 ) {
    $paths = [
        'phone'     => '<path fill="currentColor" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor"/><path d="m22 7-10 6L2 7" stroke="#fff"/>',
        'link'      => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'pin'       => '<path fill="currentColor" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3" fill="#fff" stroke="none"/>',
        'facebook'  => '<circle cx="12" cy="12" r="11" fill="currentColor" stroke="none"/><path d="M15.5 8h-1.6c-.9 0-1.4.5-1.4 1.4V12h2.8l-.4 2.8h-2.4V23h-2.9v-8.2H7.5V12h2.1V9.1C9.6 6.9 10.9 5.6 13 5.6c.9 0 1.8.1 2.5.2z" fill="#fff" stroke="none"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
        'check'     => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'alert'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'file'      => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
    ];
    return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
};

if ( ! $v ) {
    $state = 'missing';
} else {
    $state = $v['valid'] ? 'ok' : 'void';
}
$socials = array_filter(
    [
        'facebook'  => $org['facebook'],
        'instagram' => $org['instagram'],
    ]
);
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title><?php echo esc_html( $org['name'] ); ?></title>
<link rel="icon" href="<?php echo esc_url( $logo ); ?>" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( IDH_AULA_URL . 'public/assets/verificar.css?ver=' . $ver ); ?>" />
</head>
<body>

<main class="vf">
  <section class="vf-card">
    <div class="vf-logo">
      <img src="<?php echo esc_url( $logo ); ?>" alt="IDH" />
      <span>Instituto Desarrollo<br>Habilidades Profesionales</span>
    </div>

    <div class="vf-body">
      <h1 class="vf-title"><?php echo esc_html( $org['name'] ); ?></h1>

      <?php if ( 'ok' === $state ) : ?>
        <p class="vf-status vf-status--ok"><?php echo $svg( 'check', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Certificado válido · <?php echo esc_html( $v['cert']->code ); ?></p>
      <?php elseif ( 'void' === $state ) : ?>
        <p class="vf-status vf-status--void"><?php echo $svg( 'alert', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Certificado no vigente · <?php echo esc_html( $v['cert']->code ); ?></p>
      <?php else : ?>
        <p class="vf-status vf-status--void"><?php echo $svg( 'alert', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Certificado no encontrado</p>
      <?php endif; ?>

      <?php if ( $v ) : ?>
        <p class="vf-text"><?php echo esc_html( $v['sentence'] ); ?></p>
        <p class="vf-meta"><?php echo esc_html( $v['document'] ); ?> · Emitido el <?php echo esc_html( idh_aula_format_date( $v['issued_at'] ) ); ?></p>
        <?php if ( 'void' === $state ) : ?>
          <p class="vf-note">Este certificado fue emitido por IDH, pero en este momento no se encuentra vigente. Para más información, comunícate con IDH.</p>
        <?php endif; ?>
        <?php if ( $v['pdf_available'] ) : ?>
          <p class="vf-actions"><a class="vf-btn" href="<?php echo esc_url( $v['pdf_url'] ); ?>" target="_blank" rel="noopener"><?php echo $svg( 'file', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Ver certificado</a></p>
        <?php endif; ?>
      <?php else : ?>
        <p class="vf-text">No existe un certificado emitido por IDH con el código <strong><?php echo esc_html( $code ); ?></strong>. Revisa que el código esté bien escrito.</p>
      <?php endif; ?>

      <hr />

      <p class="vf-label"><?php echo $svg( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <strong>Número de Teléfono</strong></p>
      <a class="vf-value" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $org['phone'] ) ); ?>"><?php echo esc_html( $org['phone'] ); ?></a>

      <p class="vf-label vf-label--mt"><?php echo $svg( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <strong>Dirección de correo electrónico</strong></p>
      <a class="vf-value" href="<?php echo esc_url( 'mailto:' . $org['email'] ); ?>"><?php echo esc_html( $org['email'] ); ?></a>

      <p class="vf-label vf-label--mt"><?php echo $svg( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <strong>Sitio web</strong></p>
      <a class="vf-value" href="<?php echo esc_url( $org['website'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $site ); ?></a>

      <hr />

      <span class="vf-muted"><?php echo $svg( 'pin', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $org['location'] ); ?></span>
    </div>
  </section>

  <?php if ( $socials ) : ?>
    <section class="vf-card vf-card--social">
      <div class="vf-body">
        <h2 class="vf-subtitle">Redes Sociales</h2>
        <div class="vf-list">
          <?php foreach ( $socials as $network => $url ) : ?>
            <a class="vf-item" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
              <span class="vf-item-name vf-<?php echo esc_attr( $network ); ?>"><?php echo $svg( $network ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $network ); ?></span>
              <span class="vf-visit">Visitar</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

</body>
</html>
