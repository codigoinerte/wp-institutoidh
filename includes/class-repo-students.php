<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_Students {

    public static function table() {
        return idh_aula_table( 'students' );
    }

    public static function find( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
    }

    public static function find_by_doc( $number, $type = IDH_Aula_Documents::DEFAULT_TYPE ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::table() . ' WHERE doc_type = %s AND doc_number = %s',
                $type,
                IDH_Aula_Documents::normalize( $number )
            )
        );
    }

    /**
     * Valida y limpia datos de un alumno. Devuelve [ $data, $errors ].
     */
    public static function sanitize( array $input, $current_id = 0 ) {
        $errors = [];
        $data   = [
            'doc_type'   => IDH_Aula_Documents::DEFAULT_TYPE,
            'doc_number' => IDH_Aula_Documents::normalize( $input['doc_number'] ?? '' ),
            'first_name' => sanitize_text_field( $input['first_name'] ?? '' ),
            'last_name'  => sanitize_text_field( $input['last_name'] ?? '' ),
            'birth_date' => idh_aula_parse_date( $input['birth_date'] ?? '' ),
            'phone'      => sanitize_text_field( $input['phone'] ?? '' ),
            'email'      => sanitize_email( $input['email'] ?? '' ),
            'notes'      => sanitize_textarea_field( $input['notes'] ?? '' ),
        ];

        if ( ! IDH_Aula_Documents::is_valid( $data['doc_number'], $data['doc_type'] ) ) {
            $errors[] = 'El DNI debe tener ' . IDH_Aula_Documents::hint( $data['doc_type'] ) . '.';
        } else {
            $existing = self::find_by_doc( $data['doc_number'], $data['doc_type'] );
            if ( $existing && (int) $existing->id !== (int) $current_id ) {
                $errors[] = 'Ya existe un alumno con el DNI ' . $data['doc_number'] . '.';
            }
        }
        if ( '' === $data['first_name'] ) {
            $errors[] = 'Los nombres son obligatorios.';
        }
        if ( '' === $data['last_name'] ) {
            $errors[] = 'Los apellidos son obligatorios.';
        }
        if ( '' === $data['birth_date'] && '' !== trim( (string) ( $input['birth_date'] ?? '' ) ) ) {
            $errors[] = 'La fecha de nacimiento «' . sanitize_text_field( $input['birth_date'] ) . '» no es válida (usa dd/mm/aaaa).';
        } elseif ( '' === $data['birth_date'] ) {
            $errors[] = 'La fecha de nacimiento es obligatoria (la usa el alumno para ingresar).';
        } elseif ( $data['birth_date'] > current_time( 'Y-m-d' ) ) {
            $errors[] = 'La fecha de nacimiento no puede ser futura.';
        }

        return [ $data, $errors ];
    }

    public static function create( array $data ) {
        global $wpdb;
        $now                = idh_aula_now();
        $data['status']     = 'active';
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $wpdb->insert( self::table(), $data );
        return (int) $wpdb->insert_id;
    }

    public static function update( $id, array $data ) {
        global $wpdb;
        $data['updated_at'] = idh_aula_now();
        return false !== $wpdb->update( self::table(), $data, [ 'id' => (int) $id ] );
    }

    public static function set_status( $id, $status, $reason = '' ) {
        return self::update(
            $id,
            [
                'status'         => 'blocked' === $status ? 'blocked' : 'active',
                'blocked_reason' => 'blocked' === $status ? sanitize_text_field( $reason ) : '',
            ]
        );
    }

    /**
     * Elimina un alumno solo si no tiene matrículas (si las tiene, se debe bloquear).
     */
    public static function delete( $id ) {
        global $wpdb;
        if ( IDH_Aula_Enrollments::count_for_student( $id ) > 0 ) {
            return false;
        }
        return (bool) $wpdb->delete( self::table(), [ 'id' => (int) $id ] );
    }

    /**
     * Búsqueda paginada para el admin.
     */
    public static function search( $args = [] ) {
        global $wpdb;
        $args = wp_parse_args(
            $args,
            [ 'search' => '', 'status' => '', 'per_page' => 20, 'page' => 1, 'orderby' => 'created_at', 'order' => 'DESC' ]
        );

        list( $where, $params ) = self::where( $args );

        $allowed_orderby = [ 'created_at', 'last_name', 'doc_number' ];
        $orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
        $offset          = max( 0, ( (int) $args['page'] - 1 ) * (int) $args['per_page'] );

        $enrollments = idh_aula_table( 'enrollments' );
        $sql         = 'SELECT s.*,
                (SELECT COUNT(*) FROM ' . $enrollments . ' e WHERE e.student_id = s.id) AS courses_count,
                (SELECT COUNT(*) FROM ' . $enrollments . " e WHERE e.student_id = s.id AND e.payment_status = 'pending') AS pending_count
            FROM " . self::table() . " s {$where} ORDER BY s.{$orderby} {$order} LIMIT %d OFFSET %d";

        $params[] = (int) $args['per_page'];
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    public static function count( $args = [] ) {
        global $wpdb;
        list( $where, $params ) = self::where( wp_parse_args( $args, [ 'search' => '', 'status' => '' ] ) );
        $sql = 'SELECT COUNT(*) FROM ' . self::table() . " s {$where}";
        return (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) : $wpdb->get_var( $sql ) );
    }

    private static function where( $args ) {
        global $wpdb;
        $where  = [ '1=1' ];
        $params = [];
        if ( '' !== $args['search'] ) {
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where[]  = "(s.doc_number LIKE %s OR CONCAT(s.first_name, ' ', s.last_name) LIKE %s OR s.phone LIKE %s OR s.email LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ( in_array( $args['status'], [ 'active', 'blocked' ], true ) ) {
            $where[]  = 's.status = %s';
            $params[] = $args['status'];
        }
        return [ 'WHERE ' . implode( ' AND ', $where ), $params ];
    }

    public static function full_name( $student ) {
        return trim( $student->first_name . ' ' . $student->last_name );
    }
}
