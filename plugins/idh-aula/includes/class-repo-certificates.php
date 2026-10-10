<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Certificados por matrícula. Modos: 'url' (enlace externo) o 'pdf' (archivo subido, servido por la plataforma).
 * La fase 2 añadirá 'generated' (PDF + QR generados por el sistema) reutilizando el campo `code`.
 */
class IDH_Aula_Certificates {

    const MODES      = [ 'url' => 'Enlace externo', 'pdf' => 'PDF subido' ];
    const STORAGE    = 'idh-aula/certificados';
    const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function table() {
        return idh_aula_table( 'certificates' );
    }

    public static function find_by_enrollment( $enrollment_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE enrollment_id = %d', $enrollment_id ) );
    }

    public static function find_by_code( $code ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE code = %s', strtoupper( $code ) ) );
    }

    public static function storage_dir() {
        $uploads = wp_upload_dir( null, false );
        return trailingslashit( $uploads['basedir'] ) . self::STORAGE;
    }

    /**
     * Crea la carpeta privada de certificados, bloqueada para acceso directo.
     */
    public static function ensure_storage() {
        $dir = self::storage_dir();
        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        if ( ! file_exists( $dir . '/.htaccess' ) ) {
            file_put_contents( $dir . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n" );
        }
        if ( ! file_exists( $dir . '/index.php' ) ) {
            file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" );
        }
    }

    public static function generate_code() {
        $chars = self::CODE_CHARS;
        do {
            $code = '';
            for ( $i = 0; $i < 8; $i++ ) {
                $code .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
            }
        } while ( self::find_by_code( $code ) );
        return $code;
    }

    /**
     * Guarda (crea o actualiza) el certificado de una matrícula.
     *
     * @param array      $input mode, external_url, verify_url, issued_at.
     * @param array|null $file  Entrada de $_FILES para el PDF.
     * @return true|string[] true o lista de errores.
     */
    public static function save( $enrollment_id, array $input, $file = null ) {
        global $wpdb;
        $existing = self::find_by_enrollment( $enrollment_id );
        $mode     = isset( self::MODES[ $input['mode'] ?? '' ] ) ? $input['mode'] : 'url';
        $errors   = [];

        $data = [
            'mode'         => $mode,
            'external_url' => esc_url_raw( trim( (string) ( $input['external_url'] ?? '' ) ) ),
            'verify_url'   => esc_url_raw( trim( (string) ( $input['verify_url'] ?? '' ) ) ),
            'issued_at'    => idh_aula_parse_date( $input['issued_at'] ?? '' ) ?: current_time( 'Y-m-d' ),
            'updated_at'   => idh_aula_now(),
        ];

        $has_upload = $file && ! empty( $file['name'] ) && UPLOAD_ERR_NO_FILE !== ( $file['error'] ?? UPLOAD_ERR_NO_FILE );

        if ( 'url' === $mode && '' === $data['external_url'] ) {
            $errors[] = 'Ingresa el enlace del certificado.';
        }
        if ( 'pdf' === $mode && ! $has_upload && ( ! $existing || '' === $existing->pdf_path ) ) {
            $errors[] = 'Selecciona el archivo PDF del certificado.';
        }

        if ( $has_upload && 'pdf' === $mode && ! $errors ) {
            $stored = self::store_pdf( $file );
            if ( is_wp_error( $stored ) ) {
                $errors[] = $stored->get_error_message();
            } else {
                if ( $existing && $existing->pdf_path ) {
                    self::delete_file( $existing->pdf_path );
                }
                $data['pdf_path'] = $stored['path'];
                $data['pdf_name'] = $stored['name'];
            }
        }

        if ( $errors ) {
            return $errors;
        }

        if ( $existing ) {
            $wpdb->update( self::table(), $data, [ 'id' => (int) $existing->id ] );
        } else {
            $data['enrollment_id'] = (int) $enrollment_id;
            $data['code']          = self::generate_code();
            $data['created_at']    = idh_aula_now();
            $wpdb->insert( self::table(), $data );
        }
        return true;
    }

    private static function store_pdf( $file ) {
        if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
            return new WP_Error( 'upload', 'No se pudo subir el archivo (código ' . (int) $file['error'] . ').' );
        }
        $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], [ 'pdf' => 'application/pdf' ] );
        if ( 'pdf' !== $check['ext'] ) {
            return new WP_Error( 'type', 'El certificado debe ser un archivo PDF.' );
        }
        if ( $file['size'] > 15 * MB_IN_BYTES ) {
            return new WP_Error( 'size', 'El PDF no debe superar los 15 MB.' );
        }

        self::ensure_storage();
        $relative = gmdate( 'Y' ) . '/' . wp_generate_password( 24, false ) . '.pdf';
        $target   = self::storage_dir() . '/' . $relative;
        wp_mkdir_p( dirname( $target ) );

        if ( ! @move_uploaded_file( $file['tmp_name'], $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
            return new WP_Error( 'move', 'No se pudo guardar el PDF en el servidor.' );
        }
        return [ 'path' => $relative, 'name' => sanitize_file_name( $file['name'] ) ];
    }

    private static function delete_file( $relative ) {
        $path = self::storage_dir() . '/' . ltrim( $relative, '/' );
        if ( is_file( $path ) && 0 === strpos( realpath( $path ), realpath( self::storage_dir() ) ) ) {
            wp_delete_file( $path );
        }
    }

    public static function delete_for_enrollment( $enrollment_id ) {
        global $wpdb;
        $cert = self::find_by_enrollment( $enrollment_id );
        if ( ! $cert ) {
            return false;
        }
        if ( $cert->pdf_path ) {
            self::delete_file( $cert->pdf_path );
        }
        return (bool) $wpdb->delete( self::table(), [ 'id' => (int) $cert->id ] );
    }

    public static function pdf_absolute_path( $cert ) {
        if ( ! $cert || '' === $cert->pdf_path ) {
            return '';
        }
        $path = self::storage_dir() . '/' . ltrim( $cert->pdf_path, '/' );
        return is_file( $path ) ? $path : '';
    }

    public static function download_url( $code ) {
        return idh_aula_portal_url( 'certificado/' . rawurlencode( $code ) );
    }

    /**
     * URL que ve el alumno para su certificado.
     */
    public static function student_url( $row ) {
        if ( 'pdf' === $row->certificate_mode ) {
            return self::download_url( $row->certificate_code );
        }
        return $row->external_url;
    }
}
