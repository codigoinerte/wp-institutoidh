<?php
/**
 * Generación de certificados en PDF (fase 2): plantilla HTML/CSS → dompdf, con QR propio.
 *
 * Las coordenadas de las plantillas están en puntos y salen del PDF de referencia
 * (PowerPoint 960 × 540 pt). Se dibuja todo con posicionamiento absoluto para que
 * el resultado calce con el diseño.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IDH_AULA_DIR' ) ) {
    exit;
}

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class IDH_Aula_Certificate_PDF {

    /**
     * Tamaño de página en puntos de cada plantilla, igual que su referencia:
     * azul = PowerPoint 16:9; especialización = Canva A4 apaisado.
     */
    const PAGES = [
        'taller'          => [ 960, 540 ],
        'especializacion' => [ 842.25, 595.5 ],
    ];

    /** Plantillas disponibles: slug => nombre visible. */
    const TEMPLATES = [
        'taller'          => 'Azul (taller, seminario, webinar)',
        'especializacion' => 'Negro y dorado (especialización)',
    ];

    /**
     * Genera el PDF y devuelve el binario.
     *
     * @param array  $data     Ver demo: student_name, course_title, period, hours, signers, topics, verify_url, kind, session_title.
     * @param string $template Slug de TEMPLATES.
     */
    public static function render( array $data, string $template = 'taller' ): string {
        self::autoload();

        if ( ! isset( self::TEMPLATES[ $template ] ) ) {
            $template = 'taller';
        }
        $file = IDH_AULA_DIR . 'templates/certificados/' . $template . '.php';
        if ( ! is_readable( $file ) ) {
            $file = IDH_AULA_DIR . 'templates/certificados/taller.php';
        }

        $assets = IDH_AULA_DIR . 'assets/certificados/' . ( 'especializacion' === $template ? 'especializacion' : 'azul' ) . '/';
        $fonts  = IDH_AULA_DIR . 'assets/fonts/';
        $qr     = self::qr_data_uri( (string) ( $data['verify_url'] ?? '' ), $assets . 'logo.png' );

        ob_start();
        include $file;
        $html = (string) ob_get_clean();

        $options = new Options();
        $options->setChroot( [ IDH_AULA_DIR ] );
        $options->setIsRemoteEnabled( false );
        $options->setDefaultFont( 'Carlito' );
        $options->setDpi( 96 );
        $cache = self::font_cache_dir();
        $options->setFontDir( $cache );
        $options->setFontCache( $cache );
        $options->setTempDir( $cache );

        $dompdf = new Dompdf( $options );
        $dompdf->setPaper( [ 0, 0, self::PAGES[ $template ][0], self::PAGES[ $template ][1] ] );
        $dompdf->loadHtml( $html, 'UTF-8' );
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * QR como PNG en data URI: módulos redondos, buscadores cuadrados y el logo al centro
     * (como el QR de qr.io que usa IDH hoy). Corrección de errores H para tolerar el logo.
     */
    public static function qr_data_uri( string $url, string $logo = '' ): string {
        self::autoload();
        if ( '' === $url ) {
            return '';
        }

        $options = new QROptions(
            [
                'eccLevel'            => EccLevel::H,
                'outputType'          => QROutputInterface::GDIMAGE_PNG,
                'outputBase64'        => false,
                'scale'               => 20,
                'quietzoneSize'       => 1,
                'addLogoSpace'        => '' !== $logo,
                'logoSpaceWidth'      => 9,
                'logoSpaceHeight'     => 9,
                'drawCircularModules' => true,
                'circleRadius'        => 0.42,
                'keepAsSquare'        => [
                    QRMatrix::M_FINDER_DARK,
                    QRMatrix::M_FINDER_DOT,
                    QRMatrix::M_ALIGNMENT_DARK,
                ],
                'bgColor'             => [ 255, 255, 255 ],
            ]
        );

        $png = ( new QRCode( $options ) )->render( $url );

        if ( '' !== $logo && is_readable( $logo ) && function_exists( 'imagecreatefromstring' ) ) {
            $png = self::stamp_logo( $png, $logo );
        }

        return 'data:image/png;base64,' . base64_encode( $png );
    }

    /** Pega el logo en el hueco central que deja addLogoSpace. */
    private static function stamp_logo( string $png, string $logo_file ): string {
        $qr   = imagecreatefromstring( $png );
        $logo = imagecreatefrompng( $logo_file );
        if ( ! $qr || ! $logo ) {
            return $png;
        }
        $qw = imagesx( $qr );
        $lw = imagesx( $logo );
        $lh = imagesy( $logo );

        // El hueco es de 9 módulos; el logo ocupa ~7 para dejar margen blanco.
        $modules = 9;
        $total   = $qw / 20; // scale = 20.
        $box     = (int) round( $qw * ( $modules - 2 ) / $total );
        $ratio   = min( $box / $lw, $box / $lh );
        $w       = (int) round( $lw * $ratio );
        $h       = (int) round( $lh * $ratio );

        imagealphablending( $qr, true );
        imagecopyresampled( $qr, $logo, (int) ( ( $qw - $w ) / 2 ), (int) ( ( $qw - $h ) / 2 ), 0, 0, $w, $h, $lw, $lh );

        ob_start();
        imagepng( $qr );
        $out = (string) ob_get_clean();
        imagedestroy( $qr );
        imagedestroy( $logo );
        return $out;
    }

    /** Escapa para HTML sin depender de WordPress (el demo puede correr fuera de WP). */
    public static function e( $text ): string {
        return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
    }

    /** Ruta local de un asset para dompdf (dentro del chroot del plugin). */
    public static function asset( string $dir, string $file ): string {
        return $dir . $file;
    }

    private static function font_cache_dir(): string {
        if ( function_exists( 'wp_upload_dir' ) ) {
            $dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'idh-aula/cache-pdf';
        } else {
            $dir = rtrim( sys_get_temp_dir(), '/' ) . '/idh-aula-pdf';
        }
        if ( ! is_dir( $dir ) ) {
            @mkdir( $dir, 0775, true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        }
        return $dir;
    }

    private static function autoload(): void {
        static $done = false;
        if ( ! $done ) {
            require_once IDH_AULA_DIR . 'vendor/autoload.php';
            $done = true;
        }
    }
}
