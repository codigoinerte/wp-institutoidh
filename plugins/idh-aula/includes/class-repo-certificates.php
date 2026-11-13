<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Certificados por matrícula. Modos: 'url' (enlace externo), 'pdf' (archivo subido, servido por la plataforma)
 * o 'generated' (fase 2: PDF + QR propios, generados al descargar desde el contenido congelado al habilitarlo).
 */
class IDH_Aula_Certificates {

    const UPLOADED_KEY = 'idh_aula_pdf_ok_';
    const UPLOADED_TTL = 2 * MINUTE_IN_SECONDS;

    const MODES      = [ 'url' => 'Enlace externo', 'pdf' => 'PDF subido', 'generated' => 'Generado por la plataforma' ];
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

    /**
     * Certificados por clase (tema): opcionales y aparte del certificado general de la matrícula.
     */
    public static function session_table() {
        return idh_aula_table( 'session_certificates' );
    }

    public static function find_session( $enrollment_id, $session_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::session_table() . ' WHERE enrollment_id = %d AND session_id = %d', $enrollment_id, $session_id ) );
    }

    /**
     * Certificados por clase de una matrícula, indexados por session_id.
     *
     * @return object[]
     */
    /**
     * Condición SQL: el certificado por clase (alias $sc) sigue disponible para su matrícula (alias $e).
     * Si la matrícula es «Solo algunas clases», la clase debe seguir incluida; si no, el alumno no lo ve y no se cuenta.
     */
    public static function session_visible_sql( $sc = 'sc', $e = 'e' ) {
        return "({$e}.access_scope <> 'partial' OR EXISTS (SELECT 1 FROM " . idh_aula_table( 'enrollment_sessions' ) . " es
            WHERE es.enrollment_id = {$sc}.enrollment_id AND es.session_id = {$sc}.session_id))";
    }

    public static function for_enrollment_sessions( $enrollment_id ) {
        global $wpdb;
        $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::session_table() . ' WHERE enrollment_id = %d', $enrollment_id ) );
        $out  = [];
        foreach ( $rows as $row ) {
            $out[ (int) $row->session_id ] = $row;
        }
        return $out;
    }

    /**
     * Busca el código en los certificados generales y luego en los de clase (comparten la ruta /certificado/{code}).
     */
    public static function find_any_by_code( $code ) {
        global $wpdb;
        $cert = self::find_by_code( $code );
        if ( $cert ) {
            return $cert;
        }
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::session_table() . ' WHERE code = %s', strtoupper( $code ) ) );
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
        } while ( self::find_any_by_code( $code ) );
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
        return self::persist( self::table(), self::find_by_enrollment( $enrollment_id ), [ 'enrollment_id' => (int) $enrollment_id ], $input, $file, self::UPLOADED_KEY . (int) $enrollment_id );
    }

    /**
     * Guarda (crea o actualiza) el certificado de una clase de la matrícula. Misma estructura que el general.
     *
     * @return true|string[] true o lista de errores.
     */
    public static function save_session( $enrollment_id, $session_id, array $input, $file = null ) {
        return self::persist(
            self::session_table(),
            self::find_session( $enrollment_id, $session_id ),
            [ 'enrollment_id' => (int) $enrollment_id, 'session_id' => (int) $session_id ],
            $input,
            $file,
            self::UPLOADED_KEY . (int) $enrollment_id . '_' . (int) $session_id
        );
    }

    /**
     * Validación por modo, PDF, inserción o actualización y aviso de carga, comunes a ambos tipos de certificado.
     *
     * @param array $keys Columnas que identifican la fila nueva (enrollment_id y, si aplica, session_id).
     */
    private static function persist( $table, $existing, array $keys, array $input, $file, $notice_key ) {
        global $wpdb;
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

        // Generado: «Habilitar certificado» congela el contenido; desmarcarlo lo deja no vigente (mismo código).
        if ( 'generated' === $mode ) {
            $enable  = ! empty( $input['enabled'] );
            $refresh = ! empty( $input['refresh'] );
            $data['enabled'] = $enable ? 1 : 0;
            if ( $enable && ( $refresh || ! $existing || empty( $existing->snapshot ) ) ) {
                $snap = IDH_Aula_Certificate_Generator::snapshot( $keys['enrollment_id'], $keys['session_id'] ?? 0 );
                if ( is_wp_error( $snap ) ) {
                    $errors[] = $snap->get_error_message();
                } else {
                    $snap['issued_at']  = $data['issued_at'];
                    $data['snapshot']   = wp_json_encode( $snap, JSON_UNESCAPED_UNICODE );
                    $data['enabled_at'] = idh_aula_now();
                    $data['enabled_by'] = get_current_user_id();
                }
            } elseif ( $enable && $existing && ! (int) $existing->enabled ) {
                $data['enabled_at'] = idh_aula_now(); // Se vuelve a habilitar con el contenido ya congelado.
                $data['enabled_by'] = get_current_user_id();
            }
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

        // Al dejar el modo PDF (p. ej. pasar a generado), el archivo subido ya no se usa: no se guarda en disco.
        $orphan = '';
        if ( 'pdf' !== $mode && $existing && '' !== (string) $existing->pdf_path ) {
            $orphan           = $existing->pdf_path;
            $data['pdf_path'] = '';
            $data['pdf_name'] = '';
        }

        if ( $existing ) {
            if ( false !== $wpdb->update( $table, $data, [ 'id' => (int) $existing->id ] ) && '' !== $orphan ) {
                self::delete_file( $orphan );
            }
        } else {
            $data               = array_merge( $data, $keys );
            $data['code']       = self::generate_code();
            $data['created_at'] = idh_aula_now();
            $wpdb->insert( $table, $data );
        }
        if ( ! empty( $data['pdf_path'] ) && isset( $stored ) ) {
            set_transient( $notice_key, time(), self::UPLOADED_TTL );
        }
        return true;
    }

    /**
     * Segundos que faltan para ocultar el aviso «Documento cargado correctamente» tras subir un PDF (0 si no aplica).
     * Se consume al mostrarlo: si se recarga la página, el aviso ya no aparece.
     */
    public static function upload_notice_left( $enrollment_id, $session_id = 0 ) {
        $key = self::UPLOADED_KEY . (int) $enrollment_id . ( $session_id ? '_' . (int) $session_id : '' );
        $at  = (int) get_transient( $key );
        if ( ! $at ) {
            return 0;
        }
        delete_transient( $key );
        return max( 0, self::UPLOADED_TTL - ( time() - $at ) );
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

    public static function delete_session( $enrollment_id, $session_id ) {
        global $wpdb;
        $cert = self::find_session( $enrollment_id, $session_id );
        if ( ! $cert ) {
            return false;
        }
        if ( $cert->pdf_path ) {
            self::delete_file( $cert->pdf_path );
        }
        return (bool) $wpdb->delete( self::session_table(), [ 'id' => (int) $cert->id ] );
    }

    /**
     * Borra los certificados por clase (y sus PDF) de una matrícula, o de las clases indicadas si se eliminan del curso.
     */
    public static function delete_sessions_where( $column, array $ids ) {
        global $wpdb;
        $ids = array_filter( array_map( 'absint', $ids ) );
        if ( ! $ids || ! in_array( $column, [ 'enrollment_id', 'session_id' ], true ) ) {
            return;
        }
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $where        = $wpdb->prepare( "{$column} IN ({$placeholders})", $ids ); // phpcs:ignore WordPress.DB.PreparedSQL
        foreach ( $wpdb->get_col( 'SELECT pdf_path FROM ' . self::session_table() . " WHERE {$where} AND pdf_path <> ''" ) as $path ) {
            self::delete_file( $path );
        }
        $wpdb->query( 'DELETE FROM ' . self::session_table() . " WHERE {$where}" );
    }

    public static function pdf_absolute_path( $cert ) {
        if ( ! $cert || '' === $cert->pdf_path ) {
            return '';
        }
        $path = self::storage_dir() . '/' . ltrim( $cert->pdf_path, '/' );
        return is_file( $path ) ? $path : '';
    }

    /**
     * Certificados generados ya emitidos (con contenido congelado) de un curso: generales y por clase.
     *
     * @return object[] Filas con enrollment_id, session_id (0 = general), id, snapshot y la tabla de origen.
     */
    public static function frozen_for_course( $course_id ) {
        global $wpdb;
        $enrollments = idh_aula_table( 'enrollments' );
        $sql         = "SELECT cert.id, cert.enrollment_id, %s AS session_id, cert.snapshot, cert.issued_at, %s AS source
            FROM %s cert INNER JOIN {$enrollments} e ON e.id = cert.enrollment_id
            WHERE e.course_id = %d AND cert.mode = 'generated' AND cert.snapshot IS NOT NULL AND cert.snapshot <> ''";
        $general = $wpdb->get_results( sprintf( $sql, '0', "'general'", self::table(), (int) $course_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL
        $session = $wpdb->get_results( sprintf( $sql, 'cert.session_id', "'session'", self::session_table(), (int) $course_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL
        return array_merge( $general, $session );
    }

    /**
     * Vuelve a congelar el contenido de todos los certificados generados ya emitidos de un curso
     * con los datos actuales. Conserva código, QR, estado (habilitado o no) y fecha de emisión.
     *
     * @return array [ actualizados, errores ].
     */
    public static function refresh_course( $course_id ) {
        global $wpdb;
        $done   = 0;
        $errors = [];
        foreach ( self::frozen_for_course( $course_id ) as $row ) {
            $snap = IDH_Aula_Certificate_Generator::snapshot( $row->enrollment_id, (int) $row->session_id );
            if ( is_wp_error( $snap ) ) {
                $errors[] = $snap->get_error_message();
                continue;
            }
            $old               = json_decode( (string) $row->snapshot, true );
            $snap['issued_at'] = $old['issued_at'] ?? $row->issued_at;
            $wpdb->update(
                'session' === $row->source ? self::session_table() : self::table(),
                [ 'snapshot' => wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ), 'updated_at' => idh_aula_now() ],
                [ 'id' => (int) $row->id ]
            );
            $done++;
        }
        return [ $done, array_unique( $errors ) ];
    }

    /**
     * Certificado generado y habilitado (los manuales no usan esta marca).
     */
    public static function is_live( $cert_mode, $enabled ) {
        return 'generated' !== $cert_mode || (int) $enabled;
    }

    public static function download_url( $code ) {
        return idh_aula_portal_url( 'certificado/' . rawurlencode( $code ) );
    }

    /**
     * URL que ve el alumno para su certificado.
     */
    public static function student_url( $row ) {
        if ( 'generated' === $row->certificate_mode ) {
            return (int) $row->certificate_enabled ? self::download_url( $row->certificate_code ) : '';
        }
        if ( 'pdf' === $row->certificate_mode ) {
            return self::download_url( $row->certificate_code );
        }
        return $row->external_url;
    }

    /**
     * URL que ve el alumno para un certificado leído directo de su tabla (p. ej. un certificado por clase).
     */
    public static function student_url_for( $cert ) {
        if ( 'generated' === $cert->mode ) {
            return (int) $cert->enabled ? self::download_url( $cert->code ) : '';
        }
        return 'pdf' === $cert->mode ? self::download_url( $cert->code ) : $cert->external_url;
    }
}
