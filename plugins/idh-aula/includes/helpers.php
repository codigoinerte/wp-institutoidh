<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Nombre completo de una tabla del plugin (con prefijo de WordPress).
 */
function idh_aula_table( $name ) {
    global $wpdb;
    return $wpdb->prefix . 'aula_' . $name;
}

function idh_aula_now() {
    return current_time( 'mysql' );
}

/**
 * Extrae el ID de un video de YouTube desde cualquier formato de URL.
 */
function idh_aula_youtube_id( $url ) {
    $url = trim( (string) $url );
    if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ) {
        return $url;
    }
    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~',
        '~youtube(?:-nocookie)?\.com/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})~',
        '~[?&]v=([A-Za-z0-9_-]{11})~',
    ];
    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $url, $m ) ) {
            return $m[1];
        }
    }
    return '';
}

/**
 * Fecha legible en español: "08 de junio · 8:00 PM".
 */
function idh_aula_format_datetime( $datetime, $with_time = true ) {
    if ( empty( $datetime ) || '0000-00-00 00:00:00' === $datetime ) {
        return '';
    }
    $ts     = strtotime( $datetime );
    $months = [ 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ];
    $out    = date( 'd', $ts ) . ' de ' . $months[ (int) date( 'n', $ts ) - 1 ];
    if ( $with_time && '00:00' !== date( 'H:i', $ts ) ) {
        $out .= ' · ' . date( 'g:i A', $ts );
    }
    return $out;
}

function idh_aula_format_date( $date ) {
    if ( empty( $date ) || '0000-00-00' === substr( (string) $date, 0, 10 ) ) {
        return '';
    }
    return date( 'd/m/Y', strtotime( $date ) );
}

/**
 * Normaliza una fecha ingresada (dd/mm/aaaa o aaaa-mm-dd) a aaaa-mm-dd. Devuelve '' si no es válida.
 */
function idh_aula_parse_date( $value ) {
    $value = trim( (string) $value );
    if ( '' === $value ) {
        return '';
    }
    if ( preg_match( '~^(\d{4})-(\d{1,2})-(\d{1,2})~', $value, $m ) ) {
        list( , $y, $mo, $d ) = $m;
    } elseif ( preg_match( '~^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})$~', $value, $m ) ) {
        list( , $d, $mo, $y ) = $m;
    } else {
        return '';
    }
    if ( ! checkdate( (int) $mo, (int) $d, (int) $y ) ) {
        return '';
    }
    return sprintf( '%04d-%02d-%02d', $y, $mo, $d );
}

function idh_aula_initials( $first, $last ) {
    $a = mb_substr( trim( (string) $first ), 0, 1 );
    $b = mb_substr( trim( (string) $last ), 0, 1 );
    return mb_strtoupper( $a . $b );
}

function idh_aula_money( $amount ) {
    return 'S/ ' . number_format( (float) $amount, 2, '.', ',' );
}

/**
 * Renderiza una plantilla del plugin con variables.
 */
function idh_aula_view( $path, array $vars = [] ) {
    extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include IDH_AULA_DIR . $path;
}

function idh_aula_portal_url( $path = '' ) {
    return home_url( '/' . IDH_AULA_SLUG . '/' . ltrim( $path, '/' ) );
}

function idh_aula_admin_url( $page, array $args = [] ) {
    return add_query_arg( array_merge( [ 'page' => $page ], $args ), admin_url( 'admin.php' ) );
}
