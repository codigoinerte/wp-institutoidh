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
    const TTL               = DAY_IN_SECONDS; // La sesión se cierra a las 24 horas.
    const MAX_IP_FAILS      = 5;
    const IP_WINDOW         = 15 * MINUTE_IN_SECONDS;
    const MAX_DOC_FAILS     = 10;
    const DOC_WINDOW        = HOUR_IN_SECONDS;

    private static $current = null;

    /**
     * Intenta iniciar sesión. Devuelve el alumno o WP_Error con un mensaje para mostrar.
     * Tras MAX_IP_FAILS intentos fallidos desde la misma conexión (o MAX_DOC_FAILS con el mismo DNI)
     * el ingreso queda bloqueado temporalmente, aunque luego se escriban los datos correctos.
     */
    public static function attempt( $doc_number, $birth_date ) {
        $ip_key  = 'idh_aula_rl_ip_' . md5( self::ip() );
        $doc     = IDH_Aula_Documents::normalize( $doc_number );
        $doc_key = 'idh_aula_rl_doc_' . md5( $doc );
        $ip      = self::fails( $ip_key );
        $by_doc  = $doc ? self::fails( $doc_key ) : [ 'n' => 0, 'until' => 0 ];

        $until = self::locked_until( $ip, $by_doc );
        if ( $until ) {
            return new WP_Error( 'rate', self::locked_message( $until ) );
        }

        $birth   = idh_aula_parse_date( $birth_date );
        $student = ( $doc && $birth ) ? IDH_Aula_Students::find_by_doc( $doc ) : null;

        if ( ! $student || $student->birth_date !== $birth ) {
            $ip = self::add_fail( $ip_key, $ip, self::IP_WINDOW );
            if ( $doc ) {
                $by_doc = self::add_fail( $doc_key, $by_doc, self::DOC_WINDOW );
            }
            $until = self::locked_until( $ip, $by_doc );
            if ( $until ) {
                return new WP_Error( 'rate', self::locked_message( $until ) );
            }
            $left    = min( self::MAX_IP_FAILS - $ip['n'], self::MAX_DOC_FAILS - $by_doc['n'] );
            $message = 'Los datos ingresados no coinciden. Revisa tu DNI y tu fecha de nacimiento.';
            if ( $left <= 3 ) {
                $message .= ' ' . ( 1 === $left ? 'Te queda 1 intento' : "Te quedan {$left} intentos" ) . ' antes de que el ingreso se bloquee por unos minutos.';
            }
            return new WP_Error( 'invalid', $message );
        }

        if ( 'blocked' === $student->status ) {
            return new WP_Error( 'blocked', 'Tu acceso al aula virtual está deshabilitado. Comunícate con IDH para más información.', $student );
        }

        delete_transient( $ip_key );
        delete_transient( $doc_key );
        self::set_cookie( (int) $student->id );
        self::$current = $student;
        return $student;
    }

    /**
     * Intentos fallidos acumulados: n y el momento en que vence la ventana.
     */
    private static function fails( $key ) {
        $data = get_transient( $key );
        if ( ! is_array( $data ) || (int) $data['until'] <= time() ) {
            return [ 'n' => 0, 'until' => 0 ];
        }
        return [ 'n' => (int) $data['n'], 'until' => (int) $data['until'] ];
    }

    /**
     * La ventana se cuenta desde el primer fallo; el bloqueo dura hasta que vence.
     */
    private static function add_fail( $key, array $fails, $window ) {
        $until = $fails['n'] ? $fails['until'] : time() + $window;
        $fails = [ 'n' => $fails['n'] + 1, 'until' => $until ];
        set_transient( $key, $fails, max( 1, $until - time() ) );
        return $fails;
    }

    /**
     * Momento en que termina el bloqueo, o 0 si no hay bloqueo.
     */
    private static function locked_until( array $ip, array $by_doc ) {
        return max(
            $ip['n'] >= self::MAX_IP_FAILS ? $ip['until'] : 0,
            $by_doc['n'] >= self::MAX_DOC_FAILS ? $by_doc['until'] : 0
        );
    }

    private static function locked_message( $until ) {
        $minutes = max( 1, (int) ceil( ( $until - time() ) / MINUTE_IN_SECONDS ) );
        return sprintf(
            'Por seguridad bloqueamos el ingreso temporalmente tras varios intentos fallidos. Vuelve a intentarlo en %s.',
            1 === $minutes ? '1 minuto' : $minutes . ' minutos'
        );
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

    /**
     * IP del visitante. El sitio está detrás de Cloudflare: REMOTE_ADDR sería la IP del proxy
     * (compartida por muchos alumnos), así que se usa CF-Connecting-IP cuando viene.
     * Si alguien falsificara la cabecera solo evadiría el límite por IP; el límite por DNI sigue activo.
     */
    private static function ip() {
        foreach ( [ 'HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR' ] as $key ) {
            $ip = isset( $_SERVER[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) : '';
            if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
        return '0.0.0.0';
    }
}
