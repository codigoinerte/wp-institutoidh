<?php
/**
 * Plantilla negro y dorado (curso de especialización). Anverso + reverso, A4 apaisado (842,25 × 595,5 pt).
 *
 * La referencia viene de Canva. Las partes fijas (decoraciones, marco, logo, «IDH», cinta
 * «Certificado», «Otorgado a:» y las líneas con puntos) están horneadas en los fondos JPG.
 * Aquí solo se dibuja lo variable.
 *
 * Variables (desde IDH_Aula_Certificate_PDF::render):
 * - $data   student_name, course_title, course_type, period, hours, signers[], topics[], kind, session_title
 *           topics: cada línea que empieza con «- » es una viñeta; las demás, un párrafo (mismo tamaño, topics_size)
 * - $assets, $fonts, $qr
 *
 * Fuentes: Droid Serif → Noto Serif (derivada de Droid, mismas métricas); Amoresa Aged (exclusiva
 * de Canva) → Great Vibes; Bai Jamjuree es la misma de la referencia.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IDH_AULA_DIR' ) ) {
    exit;
}

$e = [ 'IDH_Aula_Certificate_PDF', 'e' ];

$name    = (string) ( $data['student_name'] ?? '' );
$course  = (string) ( $data['course_title'] ?? '' );
$type    = (string) ( $data['course_type'] ?? 'Curso de Especialización' );
$our     = 'la' === IDH_Aula_Certificate_PDF::type_phrase( $type )[0] ? 'nuestra' : 'nuestro'; // «nuestro curso…», «nuestra charla…»
$period  = (string) ( $data['period'] ?? '' );
$hours   = trim( (string) ( $data['hours'] ?? '' ) );
$topics  = (array) ( $data['topics'] ?? [] );
$tsize   = min( 16, max( 8, (float) ( $data['topics_size'] ?? 12 ) ?: 12 ) ); // Tamaño del texto del reverso (pt), igual para títulos y viñetas.
$kind    = (string) ( $data['kind'] ?? 'course' );
$session = (string) ( $data['session_title'] ?? '' );
$signers = array_slice( array_values( (array) ( $data['signers'] ?? [] ) ), 0, 3 );

$name_len  = function_exists( 'mb_strlen' ) ? mb_strlen( $name ) : strlen( $name );
$name_size = $name_len > 32 ? max( 24, round( 38 * 32 / $name_len, 1 ) ) : 38;

// Centro de la página: la firma única va centrada; con 2 o 3 se reparten.
$signer_centers = [
    1 => [ 421.1 ],
    2 => [ 300, 542 ],
    3 => [ 230, 421.1, 612 ],
];
$centers = $signer_centers[ max( 1, count( $signers ) ) ];

// El fondo del anverso se amplió un 2,6 % desde el centro de la hoja para recortar la franja blanca de
// su borde (ver assets/certificados/especializacion). Lo dibujado en el fondo (marco, líneas, firma)
// se movió con él, así que las posiciones verticales del anverso se llevan por la misma transformación.
$bg_scale = 1.026;
$fy       = static function ( $y ) use ( $bg_scale ) {
    return round( 297.75 + ( $y - 297.75 ) * $bg_scale, 2 );
};
$fx       = static function ( $x ) use ( $bg_scale ) {
    return round( 421.125 + ( $x - 421.125 ) * $bg_scale, 2 );
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @font-face { font-family: 'Serif'; font-weight: normal; src: url('<?php echo $fonts; ?>NotoSerif-Regular.ttf'); }
    @font-face { font-family: 'Serif'; font-weight: bold; src: url('<?php echo $fonts; ?>NotoSerif-Bold.ttf'); }
    @font-face { font-family: 'Script'; font-weight: normal; src: url('<?php echo $fonts; ?>GreatVibes-Regular.ttf'); }
    @font-face { font-family: 'Bai'; font-weight: normal; src: url('<?php echo $fonts; ?>BaiJamjuree-Regular.ttf'); }
    @font-face { font-family: 'Bai'; font-weight: bold; src: url('<?php echo $fonts; ?>BaiJamjuree-Bold.ttf'); }

    @page { margin: 0; }
    * { margin: 0; padding: 0; }
    body { font-family: 'Serif'; color: #000; }
    .page { position: relative; width: 842.25pt; height: 595.5pt; overflow: hidden; page-break-after: always; }
    .page.last { page-break-after: auto; }
    .bg { position: absolute; left: 0; top: 0; width: 842.25pt; height: 595.5pt; }
    .abs { position: absolute; white-space: nowrap; }
    .c { text-align: center; }
    b { font-weight: bold; }

    /* Anverso */
    .name { left: 71.1pt; width: 700pt; font-family: 'Script'; line-height: 1; }
    .paragraph { position: absolute; left: 90.9pt; width: 692pt; top: <?php echo $fy( 347.9 ); ?>pt; font-size: 16pt; line-height: 0.909; text-align: center; }
    .sign-img { position: absolute; top: <?php echo $fy( 413.7 ); ?>pt; width: 122.3pt; height: 96.8pt; }
    .seal-img { position: absolute; top: <?php echo $fy( 416.5 ); ?>pt; width: 138.8pt; height: 123.8pt; }
    .qr-front { position: absolute; left: <?php echo $fx( 702 ); ?>pt; top: <?php echo $fy( 450 ); ?>pt; width: 76pt; height: 76pt; }
    .sign-extra-line { position: absolute; top: <?php echo $fy( 485.4 ); ?>pt; width: 216pt; height: 0; border-top: 2.1pt solid #c9a24a; }
    .sign-name, .sign-role { width: 240pt; font-size: 14pt; line-height: 1; }
    .sign-name { top: <?php echo $fy( 489.5 ); ?>pt; font-weight: bold; }
    .sign-role { top: <?php echo $fy( 506 ); ?>pt; }

    /* Reverso */
    .type { left: 81.8pt; top: 159.8pt; font-family: 'Bai'; font-size: 14pt; line-height: 1; }
    .course { position: absolute; left: 81.8pt; width: 520pt; top: 180.7pt; font-family: 'Bai'; font-size: 14pt; line-height: 0.93; font-weight: bold; }
    .topics { position: absolute; left: 81.8pt; width: 500pt; top: 225.7pt; font-family: 'Bai'; }
    .module { font-size: <?php echo $tsize; ?>pt; line-height: 0.92; margin-top: 17.5pt; letter-spacing: <?php echo round( -0.015 * $tsize, 3 ); ?>pt; }
    .module.first { margin-top: 0; }
    .item { position: relative; padding-left: 37.8pt; font-size: <?php echo $tsize; ?>pt; line-height: 0.929; margin-top: 0; letter-spacing: <?php echo round( -0.015 * $tsize, 3 ); ?>pt; }
    .item .dot { position: absolute; left: 23.6pt; top: <?php echo round( 0.46 * $tsize, 2 ); ?>pt; width: <?php echo round( 0.32 * $tsize, 2 ); ?>pt; height: <?php echo round( 0.32 * $tsize, 2 ); ?>pt; border-radius: <?php echo round( 0.16 * $tsize, 2 ); ?>pt; background: #000; }
    .item .num { position: absolute; left: 17pt; top: 0; width: 18pt; text-align: right; }
    .topics a { color: #000; text-decoration: underline; }
    .qr-back { position: absolute; left: 622.8pt; top: 345.1pt; width: 85.5pt; height: 85.5pt; }
    .qr-caption { left: 585.6pt; width: 160pt; top: 439pt; font-family: 'Bai'; font-size: 7pt; line-height: 1; }
</style>
</head>
<body>

<div class="page">
    <img class="bg" src="<?php echo $assets; ?>fondo-anverso.jpg" alt="">

    <div class="abs c name" style="font-size:<?php echo $name_size; ?>pt; top:<?php echo $fy( 312.5 ) - 1.103 * $name_size; ?>pt;"><?php echo $e( $name ); ?></div>

    <div class="paragraph">
        <?php if ( 'session' === $kind ) : ?>
            Por su participación en la clase <b>“<?php echo $e( $session ); ?>”</b> de <?php echo $our; ?> <?php echo $e( $type ); ?> <b>“<?php echo $e( $course ); ?>”</b>, realizada de manera virtual<?php echo '' !== $period ? ' ' . $e( $period ) : ''; ?>.
        <?php else : ?>
            Por su participación en <?php echo $our; ?> <?php echo $e( $type ); ?> <b>“<?php echo $e( $course ); ?>”</b>, realizado de manera virtual<?php if ( '' !== $hours ) : ?>, con una duración de <?php echo $e( $hours ); ?> horas académicas<?php endif; ?><?php echo '' !== $period ? ', ' . $e( $period ) : ''; ?>.
        <?php endif; ?>
    </div>

    <?php foreach ( $signers as $i => $signer ) :
        $cx = $centers[ $i ];
        ?>
        <?php if ( count( $signers ) > 1 ) : // La línea única del fondo es para un firmante. ?>
            <div class="sign-extra-line" style="left:<?php echo $cx - 108; ?>pt;"></div>
        <?php endif; ?>
        <?php if ( ! empty( $signer['signature'] ) ) : ?>
            <img class="sign-img" style="left:<?php echo $cx - 60.5; ?>pt;" src="<?php echo $e( $signer['signature'] ); ?>" alt="">
        <?php endif; ?>
        <?php if ( ! empty( $signer['seal'] ) ) : ?>
            <img class="seal-img" style="left:<?php echo $cx - 29.6; ?>pt;" src="<?php echo $e( $signer['seal'] ); ?>" alt="">
        <?php endif; ?>
        <div class="abs c sign-name" style="left:<?php echo $cx - 120; ?>pt;"><?php echo $e( $signer['name'] ?? '' ); ?></div>
        <div class="abs c sign-role" style="left:<?php echo $cx - 120; ?>pt;"><?php echo $e( $signer['role'] ?? '' ); ?></div>
    <?php endforeach; ?>

    <?php if ( $qr ) : ?>
        <img class="qr-front" src="<?php echo $qr; ?>" alt="QR">
    <?php endif; ?>
</div>

<div class="page last">
    <img class="bg" src="<?php echo $assets; ?>fondo-reverso.jpg" alt="">

    <div class="abs type"><?php echo $e( 'session' === $kind ? 'Clase de ' . $type : $type ); ?></div>
    <div class="course">“<?php echo $e( 'session' === $kind ? $session : $course ); ?>”</div>

    <div class="topics">
        <?php
        $first = true;
        foreach ( IDH_Aula_Certificate_PDF::topic_rows( $topics ) as $row ) :
            if ( 'item' === $row['type'] ) :
                ?>
                <div class="item"><?php echo '' !== $row['marker'] ? '<span class="num">' . $e( $row['marker'] ) . '</span>' : '<span class="dot"></span>'; ?><?php echo $row['html']; ?></div>
            <?php else : ?>
                <div class="module<?php echo $first ? ' first' : ''; ?>"><?php echo $row['html']; ?></div>
                <?php
            endif;
            $first = false;
        endforeach;
        ?>
    </div>

    <?php if ( $qr ) : ?>
        <img class="qr-back" src="<?php echo $qr; ?>" alt="QR">
        <div class="abs c qr-caption">QR PARA VERIFICACIÓN</div>
    <?php endif; ?>
</div>

</body>
</html>
