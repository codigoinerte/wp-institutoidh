<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_Enrollments {

    public static function table() {
        return idh_aula_table( 'enrollments' );
    }

    public static function find( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
    }

    public static function find_by( $student_id, $course_id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE student_id = %d AND course_id = %d', $student_id, $course_id )
        );
    }

    /**
     * Matricula a un alumno. Si ya estaba matriculado devuelve el ID existente sin modificarlo.
     *
     * @return array [ int $id, bool $created ]
     */
    public static function enroll( $student_id, $course_id, $payment_status = 'pending', $price = null ) {
        global $wpdb;
        $existing = self::find_by( $student_id, $course_id );
        if ( $existing ) {
            return [ (int) $existing->id, false ];
        }
        $course = IDH_Aula_Courses::find( $course_id );
        if ( ! $course ) {
            return [ 0, false ];
        }
        $now = idh_aula_now();
        $wpdb->insert(
            self::table(),
            [
                'student_id'     => (int) $student_id,
                'course_id'      => (int) $course_id,
                'payment_status' => 'paid' === $payment_status ? 'paid' : 'pending',
                'price_paid'     => null === $price ? (float) $course->price : (float) $price,
                'locked'         => 0,
                'enrolled_at'    => $now,
                'updated_at'     => $now,
            ]
        );
        return [ (int) $wpdb->insert_id, true ];
    }

    public static function update( $id, array $data ) {
        global $wpdb;
        $data['updated_at'] = idh_aula_now();
        return false !== $wpdb->update( self::table(), $data, [ 'id' => (int) $id ] );
    }

    public static function delete( $id ) {
        global $wpdb;
        IDH_Aula_Certificates::delete_for_enrollment( $id );
        return (bool) $wpdb->delete( self::table(), [ 'id' => (int) $id ] );
    }

    public static function count_for_student( $student_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE student_id = %d', $student_id ) );
    }

    public static function count_for_course( $course_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . ' WHERE course_id = %d', $course_id ) );
    }

    /**
     * Matrículas de un alumno con datos del curso y del certificado.
     */
    public static function for_student( $student_id, $only_published = false ) {
        global $wpdb;
        $published = $only_published ? "AND c.status = 'published'" : '';
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT e.*, c.title AS course_title, c.category, c.hours, c.cover_image_id, c.description AS course_description,
                        c.instructor, c.start_date, c.status AS course_status,
                        cert.id AS certificate_id, cert.code AS certificate_code, cert.mode AS certificate_mode,
                        cert.external_url, cert.pdf_path, cert.pdf_name, cert.verify_url, cert.issued_at
                 FROM ' . self::table() . ' e
                 INNER JOIN ' . idh_aula_table( 'courses' ) . ' c ON c.id = e.course_id
                 LEFT JOIN ' . idh_aula_table( 'certificates' ) . " cert ON cert.enrollment_id = e.id
                 WHERE e.student_id = %d {$published}
                 ORDER BY COALESCE(c.start_date, DATE(e.enrolled_at)) DESC, e.id DESC",
                $student_id
            )
        );
    }

    /**
     * Listado admin de matrículas con filtros.
     */
    public static function search( $args = [] ) {
        global $wpdb;
        $args   = wp_parse_args(
            $args,
            [ 'course_id' => 0, 'student_id' => 0, 'payment' => '', 'locked' => '', 'certificate' => '', 'search' => '', 'per_page' => 20, 'page' => 1 ]
        );
        $where  = [ '1=1' ];
        $params = [];
        if ( $args['course_id'] ) {
            $where[]  = 'e.course_id = %d';
            $params[] = (int) $args['course_id'];
        }
        if ( $args['student_id'] ) {
            $where[]  = 'e.student_id = %d';
            $params[] = (int) $args['student_id'];
        }
        if ( in_array( $args['payment'], [ 'paid', 'pending' ], true ) ) {
            $where[]  = 'e.payment_status = %s';
            $params[] = $args['payment'];
        }
        if ( '1' === (string) $args['locked'] || '0' === (string) $args['locked'] ) {
            $where[]  = 'e.locked = %d';
            $params[] = (int) $args['locked'];
        }
        if ( 'with' === $args['certificate'] ) {
            $where[] = 'cert.id IS NOT NULL';
        } elseif ( 'without' === $args['certificate'] ) {
            $where[] = 'cert.id IS NULL';
        }
        if ( '' !== $args['search'] ) {
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $doc_norm = IDH_Aula_Documents::normalize( $args['search'] );
            $where[]  = "(s.doc_number LIKE %s OR CONCAT(s.first_name, ' ', s.last_name) LIKE %s)";
            $params[] = '' !== $doc_norm ? '%' . $wpdb->esc_like( $doc_norm ) . '%' : $like; // el código se guarda sin guiones
            $params[] = $like;
        }

        $from = 'FROM ' . self::table() . ' e
            INNER JOIN ' . idh_aula_table( 'students' ) . ' s ON s.id = e.student_id
            INNER JOIN ' . idh_aula_table( 'courses' ) . ' c ON c.id = e.course_id
            LEFT JOIN ' . idh_aula_table( 'certificates' ) . ' cert ON cert.enrollment_id = e.id
            WHERE ' . implode( ' AND ', $where );

        $total = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) {$from}", $params ) ) : $wpdb->get_var( "SELECT COUNT(*) {$from}" ) );

        $select = "SELECT e.*, s.doc_number, s.first_name, s.last_name, s.status AS student_status, s.phone,
                          c.title AS course_title, c.category, cert.id AS certificate_id, cert.issued_at, cert.mode AS certificate_mode {$from}
                   ORDER BY e.enrolled_at DESC";

        if ( $args['per_page'] > 0 ) {
            $select  .= ' LIMIT %d OFFSET %d';
            $params[] = (int) $args['per_page'];
            $params[] = max( 0, ( (int) $args['page'] - 1 ) * (int) $args['per_page'] );
        }
        $items = $params ? $wpdb->get_results( $wpdb->prepare( $select, $params ) ) : $wpdb->get_results( $select );
        return [ $items, $total ];
    }

    public static function is_pending( $enrollment ) {
        return 'paid' !== $enrollment->payment_status;
    }

    /**
     * El certificado (y su verificador) solo se entregan con la matrícula pagada y sin candado.
     */
    public static function certificate_available( $enrollment ) {
        return ! (int) $enrollment->locked && ! self::is_pending( $enrollment );
    }

    /**
     * Estado de acceso del alumno a una matrícula: 'ok' | 'pending' | 'locked'.
     * 'ok' con pago pendiente permite ver clases y materiales, pero no el certificado.
     */
    public static function access_state( $enrollment ) {
        if ( (int) $enrollment->locked ) {
            return 'pending' === $enrollment->payment_status ? 'pending' : 'locked';
        }
        return 'ok';
    }
}
