<?php
/**
 * Plantilla azul (curso taller, seminario, webinar). Anverso + reverso, 960 × 540 pt.
 *
 * Variables disponibles (desde IDH_Aula_Certificate_PDF::render):
 * - $data   array con student_name, course_title, period, hours, signers[], topics[], kind, session_title, session_date
 * - $assets ruta local de las imágenes del diseño
 * - $fonts  ruta local de las fuentes
 * - $qr     data URI del QR (vacío si no hay URL de verificación)
 * - $layout ajuste del reverso cuando el contenido es largo: shift (pt que sube el bloque) y
 *           scale (factor de la letra). Lo calcula IDH_Aula_Certificate_PDF::render midiendo el PDF.
 *
 * Las posiciones salen del PDF de referencia (PowerPoint). Las fuentes de Microsoft se
 * reemplazan por equivalentes libres con las mismas métricas:
 * Calibri → Carlito, Arial Narrow → Liberation Sans Narrow.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IDH_AULA_DIR' ) ) {
    exit;
}

$e = [ 'IDH_Aula_Certificate_PDF', 'e' ];

$name    = (string) ( $data['student_name'] ?? '' );
$course  = (string) ( $data['course_title'] ?? '' );
$period  = (string) ( $data['period'] ?? '' );
$hours   = trim( (string) ( $data['hours'] ?? '' ) );
$topics  = (array) ( $data['topics'] ?? [] );
$kind    = (string) ( $data['kind'] ?? 'course' ); // course | session
// «el curso taller», «el webinar»… según la categoría del curso; vacío si no hay.
list( $type_art, $type ) = IDH_Aula_Certificate_PDF::type_phrase( (string) ( $data['course_type'] ?? '' ) );
$type_pre = '' !== $type ? $type_art . ' ' . $type : 'el';
$session = (string) ( $data['session_title'] ?? '' );
$signers = array_values( (array) ( $data['signers'] ?? [] ) );

$layout = array_merge( [ 'shift' => 0, 'scale' => 1 ], isset( $layout ) ? (array) $layout : [] );
$shift  = (float) $layout['shift'];
$scale  = (float) $layout['scale'];

// Nombres largos: se reduce la letra para que quepan en una línea sobre la raya.
$name_len  = function_exists( 'mb_strlen' ) ? mb_strlen( $name ) : strlen( $name );
$name_size = $name_len > 40 ? max( 20, round( 28 * 40 / $name_len, 1 ) ) : 28;

// Centros horizontales de las firmas según cuántas haya (1 a 3).
$signer_centers = [
    1 => [ 482.05 ],
    2 => [ 330, 560 ],
    3 => [ 215, 420, 625 ],
];
$signers = array_slice( $signers, 0, 3 );
$centers = $signer_centers[ max( 1, count( $signers ) ) ];

// Título del reverso: lo que va antes de «:» va en negrita y mayúsculas (como la referencia).
$colon = strpos( $course, ':' );
if ( false !== $colon ) {
    $title_bold = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( substr( $course, 0, $colon + 1 ), 'UTF-8' ) : strtoupper( substr( $course, 0, $colon + 1 ) );
    $title_rest = ltrim( substr( $course, $colon + 1 ) );
} else {
    $title_bold = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $course, 'UTF-8' ) : strtoupper( $course );
    $title_rest = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @font-face { font-family: 'Carlito'; font-weight: normal; src: url('<?php echo $fonts; ?>Carlito-Regular.ttf'); }
    @font-face { font-family: 'Carlito'; font-weight: bold; src: url('<?php echo $fonts; ?>Carlito-Bold.ttf'); }
    @font-face { font-family: 'Narrow'; font-weight: normal; src: url('<?php echo $fonts; ?>LiberationSansNarrow-Regular.ttf'); }
    @font-face { font-family: 'Narrow'; font-weight: bold; src: url('<?php echo $fonts; ?>LiberationSansNarrow-Bold.ttf'); }

    @page { margin: 0; }
    * { margin: 0; padding: 0; }
    body { font-family: 'Carlito'; color: #002060; }
    .page { position: relative; width: 960pt; height: 540pt; overflow: hidden; page-break-after: always; }
    .page.last { page-break-after: auto; }
    .abs { position: absolute; white-space: nowrap; }
    .c { text-align: center; }
    .narrow { font-family: 'Narrow'; }
    b { font-weight: bold; }

    /* Anverso */
    .bg { position: absolute; left: -5.1pt; top: -0.5pt; width: 965.1pt; height: 541.6pt; }
    .subtitle { left: 196pt; width: 580pt; top: 107.03pt; font-size: 16pt; line-height: 1; }
    .otorga { left: 385.5pt; width: 200pt; top: 132.5pt; font-size: 14pt; line-height: 1; color: #7f7f7f; }
    .title { left: 285.6pt; width: 400pt; top: 159.4pt; font-size: 54pt; line-height: 1; color: #8497b0; }
    .otorgado { left: 187.1pt; top: 266.2pt; font-size: 18pt; line-height: 1; font-weight: bold; color: #000; }
    .otorgado span { color: #002060; }
    .name { left: 262pt; width: 500pt; font-weight: bold; color: #000; }
    .name-line { position: absolute; left: 282.6pt; top: 292.35pt; width: 535.1pt; height: 0; border-top: 0.5pt solid #4472c4; }
    .paragraph { position: absolute; left: 196pt; width: 614pt; top: 303.9pt; font-size: 18pt; line-height: 0.8987; text-align: center; }

    .sign-line { position: absolute; top: 474.2pt; width: 213.5pt; height: 0; border-top: 1pt solid #000; }
    .sign-img { position: absolute; top: 405.3pt; width: 114.8pt; height: 78.8pt; }
    .seal-img { position: absolute; top: 388pt; width: 155.5pt; height: 138.6pt; }
    .sign-name, .sign-role { width: 220pt; font-size: 14pt; line-height: 1; }
    .sign-name { top: 477.8pt; }
    .sign-role { top: 494.1pt; font-weight: bold; }
    .qr-front { position: absolute; left: 672.6pt; top: 423.2pt; width: 71.8pt; height: 71.8pt; }

    /* Reverso */
    .logo-back { position: absolute; left: 766.3pt; top: 25.2pt; width: 111.4pt; height: 104.6pt; }
    .logo-back-text { left: 742pt; width: 160pt; top: 133.2pt; font-size: 7pt; line-height: 0.9; }
    .topics-title { position: absolute; left: 53.7pt; width: 790pt; top: <?php echo round( 279.1 - $shift, 2 ); ?>pt; font-size: 16pt; line-height: 0.95; color: #000; }
    .topics { position: absolute; left: 65.3pt; width: 690pt; top: <?php echo round( 345.4 - $shift, 2 ); ?>pt; color: #000; }
    .topic { position: relative; padding-left: 22.5pt; font-size: <?php echo round( 16 * $scale, 2 ); ?>pt; line-height: 0.9; margin-bottom: <?php echo round( 7.1 * $scale, 2 ); ?>pt; }
    .topic .dot { position: absolute; left: 0; top: 0; font-family: 'Narrow'; font-size: <?php echo round( 16 * $scale, 2 ); ?>pt; }
    .topic a { color: #000; text-decoration: underline; }
    .qr-back { position: absolute; left: 774.4pt; top: 337.5pt; width: 133.7pt; height: 133.7pt; }
    .qr-caption { left: 773.6pt; top: 467.8pt; font-size: 18pt; line-height: 1; color: #000; }
</style>
</head>
<body>

<div class="page">
    <img class="bg" src="<?php echo $assets; ?>fondo.png" alt="">
    <img class="abs" style="left:159.2pt; top:24.6pt; width:111.4pt; height:104.6pt;" src="<?php echo $assets; ?>logo.png" alt="">
    <img class="abs" style="left:413.75pt; top:43.5pt; width:145.5pt; height:59.25pt;" src="<?php echo $assets; ?>idh.png" alt="IDH">
    <div class="abs c subtitle">INSTITUTO DESARROLLO DE HABILIDADES PROFESIONALES</div>
    <div class="abs c narrow otorga">Otorga el presente:</div>
    <div class="abs c title">CERTIFICADO</div>

    <div class="abs narrow otorgado"><span>Otorgado</span> a:</div>
    <div class="abs c narrow name" style="font-size:<?php echo $name_size; ?>pt; line-height:1; top:<?php echo 286.5 - $name_size; ?>pt;"><?php echo $e( $name ); ?></div>
    <div class="name-line"></div>

    <div class="paragraph">
        <?php if ( 'session' === $kind ) : ?>
            Por su participación en la clase <b>“<?php echo $e( $session ); ?>”</b> d<?php echo $e( '' !== $type ? ( 'la' === $type_art ? 'e la ' : 'el ' ) . $type : 'el' ); ?> <b>“<?php echo $e( $course ); ?>”</b>, desarrollada en modalidad virtual<?php echo '' !== $period ? ' ' . $e( $period ) : ''; ?>.
        <?php else : ?>
            Por su participación en <?php echo $e( $type_pre ); ?> <b>“<?php echo $e( $course ); ?>”</b>, desarrollado en modalidad virtual<?php if ( '' !== $hours ) : ?>, con una duración de <b><?php echo $e( $hours ); ?> horas académicas</b><?php echo '' !== $period ? ', ' . $e( $period ) : ''; ?><?php else : ?><?php echo '' !== $period ? ' ' . $e( $period ) : ''; ?><?php endif; ?>.
        <?php endif; ?>
    </div>

    <?php foreach ( $signers as $i => $signer ) :
        $cx = $centers[ $i ];
        ?>
        <div class="sign-line" style="left:<?php echo $cx - 106.75; ?>pt;"></div>
        <?php if ( ! empty( $signer['signature'] ) ) : ?>
            <img class="sign-img" style="left:<?php echo $cx - 59.45; ?>pt;" src="<?php echo $e( $signer['signature'] ); ?>" alt="">
        <?php endif; ?>
        <?php if ( ! empty( $signer['seal'] ) ) : ?>
            <img class="seal-img" style="left:<?php echo $cx + 30.05; ?>pt;" src="<?php echo $e( $signer['seal'] ); ?>" alt="">
        <?php endif; ?>
        <div class="abs c narrow sign-name" style="left:<?php echo $cx - 110.8; ?>pt;"><?php echo $e( $signer['name'] ?? '' ); ?></div>
        <div class="abs c narrow sign-role" style="left:<?php echo $cx - 110.8; ?>pt;"><?php echo $e( $signer['role'] ?? '' ); ?></div>
    <?php endforeach; ?>

    <?php if ( $qr ) : ?>
        <img class="qr-front" src="<?php echo $qr; ?>" alt="QR">
    <?php endif; ?>
</div>

<div class="page last">
    <img class="logo-back" src="<?php echo $assets; ?>logo.png" alt="">
    <div class="abs c logo-back-text">INSTITUTO DE DESARROLLO DE<br>HABILIDADES PROFESIONALES</div>

    <div class="narrow topics-title">
        <?php if ( 'session' === $kind ) : ?>
            <b>CLASE “<?php echo $e( $session ); ?>”</b> DEL <?php echo $e( $course ); ?>
        <?php else : ?>
            <b>EJES TEMÁTICOS DE NUESTRO “<?php echo $e( $title_bold ); ?></b> <?php echo $e( $title_rest ); ?>”
        <?php endif; ?>
    </div>

    <div class="narrow topics idh-fit">
        <?php foreach ( IDH_Aula_Certificate_PDF::topic_rows( $topics, true ) as $row ) : ?>
            <div class="topic"><span class="dot"><?php echo '' !== $row['marker'] ? $e( $row['marker'] ) : '•'; ?></span><?php echo $row['html']; ?></div>
        <?php endforeach; ?>
    </div>

    <?php if ( $qr ) : ?>
        <img class="qr-back" src="<?php echo $qr; ?>" alt="QR">
        <div class="abs narrow qr-caption">QR para verificación</div>
    <?php endif; ?>
</div>

</body>
</html>
