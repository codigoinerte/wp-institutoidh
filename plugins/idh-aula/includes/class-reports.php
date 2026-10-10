<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reportes de certificados emitidos. Se cuentan por fecha de emisión y solo de matrículas pagadas.
 */
class IDH_Aula_Reports {

    const PERIODS = [
        'month'    => 'Mes',
        'semester' => 'Semestre',
        'year'     => 'Año',
        'custom'   => 'Personalizado',
    ];

    /**
     * Resuelve el período pedido a un rango [from, to, label].
     */
    public static function resolve_period( array $q ) {
        $period = isset( self::PERIODS[ $q['period'] ?? '' ] ) ? $q['period'] : 'month';
        $year   = (int) ( $q['year'] ?? 0 ) ?: (int) current_time( 'Y' );
        $months = [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ];

        switch ( $period ) {
            case 'year':
                return [ 'period' => $period, 'year' => $year, 'from' => "{$year}-01-01", 'to' => "{$year}-12-31", 'label' => "Año {$year}" ];

            case 'semester':
                $semester = 2 === (int) ( $q['semester'] ?? 0 ) ? 2 : ( 1 === (int) ( $q['semester'] ?? 0 ) ? 1 : ( (int) current_time( 'n' ) > 6 ? 2 : 1 ) );
                $from     = 1 === $semester ? "{$year}-01-01" : "{$year}-07-01";
                $to       = 1 === $semester ? "{$year}-06-30" : "{$year}-12-31";
                return [ 'period' => $period, 'year' => $year, 'semester' => $semester, 'from' => $from, 'to' => $to, 'label' => "Semestre {$semester} · {$year}" ];

            case 'custom':
                $from = idh_aula_parse_date( $q['from'] ?? '' ) ?: current_time( 'Y-m-01' );
                $to   = idh_aula_parse_date( $q['to'] ?? '' ) ?: current_time( 'Y-m-d' );
                if ( $from > $to ) {
                    list( $from, $to ) = [ $to, $from ];
                }
                return [ 'period' => $period, 'from' => $from, 'to' => $to, 'label' => idh_aula_format_date( $from ) . ' – ' . idh_aula_format_date( $to ) ];

            default:
                $month = (int) ( $q['month'] ?? 0 );
                $month = $month >= 1 && $month <= 12 ? $month : (int) current_time( 'n' );
                $from  = sprintf( '%04d-%02d-01', $year, $month );
                return [ 'period' => 'month', 'year' => $year, 'month' => $month, 'from' => $from, 'to' => gmdate( 'Y-m-t', strtotime( $from ) ), 'label' => $months[ $month - 1 ] . ' ' . $year ];
        }
    }

    private static function base_from() {
        return 'FROM ' . idh_aula_table( 'certificates' ) . ' cert
            INNER JOIN ' . idh_aula_table( 'enrollments' ) . ' e ON e.id = cert.enrollment_id
            INNER JOIN ' . idh_aula_table( 'courses' ) . " c ON c.id = e.course_id
            WHERE e.payment_status = 'paid' AND cert.issued_at BETWEEN %s AND %s";
    }

    public static function total( $from, $to ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) ' . self::base_from(), $from, $to ) );
    }

    public static function by_course( $from, $to ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT c.id, c.title, c.category, COUNT(*) AS total ' . self::base_from() . ' GROUP BY c.id, c.title, c.category ORDER BY total DESC, c.title ASC',
                $from,
                $to
            )
        );
    }

    public static function by_category( $from, $to ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare( 'SELECT c.category, COUNT(*) AS total ' . self::base_from() . ' GROUP BY c.category ORDER BY total DESC', $from, $to )
        );
    }

    /**
     * Serie por mes dentro del rango (incluye meses en cero).
     */
    public static function by_month( $from, $to ) {
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare( "SELECT DATE_FORMAT(cert.issued_at, '%%Y-%%m') AS ym, COUNT(*) AS total " . self::base_from() . ' GROUP BY ym ORDER BY ym', $from, $to ),
            OBJECT_K
        );
        $series = [];
        $cursor = strtotime( substr( $from, 0, 7 ) . '-01' );
        $end    = strtotime( substr( $to, 0, 7 ) . '-01' );
        $guard  = 0;
        while ( $cursor <= $end && $guard++ < 240 ) {
            $ym            = gmdate( 'Y-m', $cursor );
            $series[ $ym ] = isset( $rows[ $ym ] ) ? (int) $rows[ $ym ]->total : 0;
            $cursor        = strtotime( '+1 month', $cursor );
        }
        return $series;
    }

    /**
     * Indicadores generales (no dependen del período).
     */
    public static function kpis() {
        global $wpdb;
        $s = idh_aula_table( 'students' );
        $e = idh_aula_table( 'enrollments' );
        $c = idh_aula_table( 'courses' );
        return [
            'students'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$s} WHERE status = 'active'" ),
            'blocked'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$s} WHERE status = 'blocked'" ),
            'courses'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$c}" ),
            'pending'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$e} WHERE payment_status = 'pending'" ),
        ];
    }

    public static function month_label( $ym ) {
        $months = [ 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic' ];
        list( $y, $m ) = explode( '-', $ym );
        return $months[ (int) $m - 1 ] . ' ' . $y;
    }
}
