<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sesión del alumno: DNI + fecha de nacimiento, cookie firmada con HMAC y límite de intentos.
 * Es independiente de los usuarios de WordPress (los alumnos no son usuarios WP).
 */
class IDH_Aula_Auth {

    const COOKIE            = 'idh_aula_session';
    const TTL               = 7 * DAY_IN_SECONDS;
    const MAX_IP_FAILS      = 5;
    const IP_WINDOW         = 15 * MINUTE_IN_SECONDS;
    const MAX_DOC_FAILS     = 10;
    const DOC_WINDOW        = HOUR_IN_SECONDS;

    private static $current = null;

    /**
     * Intenta iniciar sesión. Devuelve el alumno o WP_Error con un mensaje para mostrar.
     */
    public static function attempt( $doc_number, $birth_date ) {
        $ip_key  = 'idh_aula_rl_ip_' . md5( self::ip() );
        $doc     = IDH_Aula_Documents::normalize( $doc_number );
        $doc_key = 'idh_aula_rl_doc_' . md5( $doc );

        if ( (int) get_transient( $ip_key ) >= self::MAX_IP_FAILS || (int) get_transient( $doc_key ) >= self::MAX_DOC_FAILS ) {
            return new WP_Error( 'rate', 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.' );
        }

        $birth   = idh_aula_parse_date( $birth_date );
        $student = ( $doc && $birth ) ? IDH_Aula_Students::find_by_doc( $doc ) : null;

        if ( ! $student || $student->birth_date !== $birth ) {
            set_transient( $ip_key, (int) get_transient( $ip_key ) + 1, self::IP_WINDOW );
            if ( $doc ) {
                set_transient( $doc_key, (int) get_transient( $doc_key ) + 1, self::DOC_WINDOW );
            }
            return new WP_Error( 'invalid', 'Los datos ingresados no coinciden. Revisa tu DNI y tu fecha de nacimiento.' );
        }

        if ( 'blocked' === $student->status ) {
            return new WP_Error( 'blocked', 'Tu acceso al aula virtual está deshabilitado. Comunícate con IDH para más información.' );
        }

        delete_transient( $ip_key );
        delete_transient( $doc_key );
        self::set_cookie( (int) $student->id );
        self::$current = $student;
        return $student;
    }

    /**
     * Alumno con sesión válida y activo, o null. Se revalida el estado en cada request.
     */
    public static function current() {
        if ( null !== self::$current ) {
            return self::$current ?: null;
        }
        self::$current = false;

        $raw = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
        $parts = explode( '|', $raw );
        if ( 3 !== count( $parts ) ) {
            return null;
        }
        list( $id, $expires, $signature ) = $parts;
        if ( (int) $expires < time() || ! hash_equals( self::sign( (int) $id, (int) $expires ), $signature ) ) {
            return null;
        }
        $student = IDH_Aula_Students::find( (int) $id );
        if ( ! $student || 'active' !== $student->status ) {
            self::logout();
            return null;
        }
        self::$current = $student;
        return $student;
    }

    public static function logout() {
        self::$current = false;
        self::write_cookie( '', time() - YEAR_IN_SECONDS );
    }

    private static function set_cookie( $student_id ) {
        $expires = time() + self::TTL;
        self::write_cookie( $student_id . '|' . $expires . '|' . self::sign( $student_id, $expires ), $expires );
    }

    private static function write_cookie( $value, $expires ) {
        if ( headers_sent() ) {
            return;
        }
        setcookie(
            self::COOKIE,
            $value,
            [
                'expires'  => $expires,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private static function sign( $student_id, $expires ) {
        return hash_hmac( 'sha256', $student_id . '|' . $expires, wp_salt( 'auth' ) . 'idh-aula' );
    }

    private static function ip() {
        return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
    }
}
