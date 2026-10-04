<?php
/**
 * Layout del aula virtual (independiente del theme).
 *
 * @var string      $template
 * @var string      $title
 * @var object|null $student
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/icons.php';

$student   = $student ?? null;
$logo_id   = (int) get_theme_mod( 'custom_logo' );
$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$site_name = get_bloginfo( 'name' );
$icon_url  = get_site_icon_url( 64 );
$asset_ver = static function ( $file ) {
    $path = IDH_AULA_DIR . 'public/assets/' . $file;
    return file_exists( $path ) ? filemtime( $path ) : IDH_AULA_VERSION;
};
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />
<title><?php echo esc_html( $title . ' · Aula Virtual ' . $site_name ); ?></title>
<?php if ( $icon_url ) : ?>
<link rel="icon" href="<?php echo esc_url( $icon_url ); ?>" />
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( IDH_AULA_URL . 'public/assets/aula.css?ver=' . $asset_ver( 'aula.css' ) ); ?>" />
</head>
<body class="idh-aula idh-aula--<?php echo esc_attr( $template ); ?>">

<header class="aula-topbar">
  <div class="aula-topbar-inner">
    <a class="aula-brand" href="<?php echo esc_url( idh_aula_portal_url() ); ?>">
      <?php if ( $logo_url ) : ?>
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" />
      <?php else : ?>
        <?php echo esc_html( $site_name ); ?>
      <?php endif; ?>
    </a>
    <?php if ( $student ) : ?>
      <div class="aula-user">
        <div class="avatar"><?php echo esc_html( idh_aula_initials( $student->first_name, $student->last_name ) ); ?></div>
        <span class="aula-user-name"><?php echo esc_html( IDH_Aula_Students::full_name( $student ) ); ?></span>
        <a class="aula-logout" href="<?php echo esc_url( idh_aula_portal_url( 'salir' ) ); ?>">Salir</a>
      </div>
    <?php else : ?>
      <a class="aula-logout" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ir a la web</a>
    <?php endif; ?>
  </div>
</header>

<?php include __DIR__ . '/' . $template . '.php'; ?>

<script src="<?php echo esc_url( IDH_AULA_URL . 'public/assets/aula.js?ver=' . $asset_ver( 'aula.js' ) ); ?>"></script>
</body>
</html>
