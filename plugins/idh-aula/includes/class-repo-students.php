<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_Students {

    /**
     * Criterios de orden del listado: clave => [ expresión SQL, dirección inicial al hacer clic ].
     * Cada expresión lleva {dir} y se completa con ASC o DESC; el desempate es siempre el id más reciente.
     */
    const SORTS = [
        'created_at'    => [ 's.created_at {dir}', 'desc' ],
        'last_name'     => [ 's.last_name {dir}, s.first_name {dir}', 'asc' ],
        'doc_number'    => [ 's.doc_number {dir}', 'asc' ],
        'courses_count' => [ 'courses_count {dir}', 'desc' ],
        'pending_count' => [ 'pending_count {dir}', 'desc' ],
        'status'        => [ 's.status {dir}', 'asc' ],
        'last_enrolled' => [ '(last_enrolled IS NULL) ASC, last_enrolled {dir}', 'desc' ],
    ];

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
     * Alumno por el identificador que escribió en el login: DNI o código de alumno.
     */
    public static function find_by_identifier( $value ) {
        $value = IDH_Aula_Documents::normalize( $value );
        return $value ? self::find_by_doc( $value, IDH_Aula_Documents::type_for( $value ) ) : null;
    }

    /**
     * Alumno extranjero ya registrado con el mismo nombre y fecha de nacimiento (no tiene DNI para comparar).
     * Evita duplicados al volver a importar el mismo archivo.
     */
    public static function find_foreign_match( $first_name, $last_name, $birth_date ) {
        global $wpdb;
        if ( '' === $birth_date ) {
            return null;
        }
        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::table() . ' WHERE doc_type = %s AND birth_date = %s AND first_name = %s AND last_name = %s LIMIT 1',
                IDH_Aula_Documents::FOREIGN_TYPE,
                $birth_date,
                sanitize_text_field( $first_name ),
                sanitize_text_field( $last_name )
            )
        );
    }

    /**
     * Valida y limpia datos de un alumno. Devuelve [ $data, $errors ].
     */
    public static function sanitize( array $input, $current_id = 0 ) {
        $errors     = [];
        $current    = $current_id ? self::find( $current_id ) : null;
        $is_foreign = ! empty( $input['is_foreign'] );
        $data       = [
            'doc_type'   => $is_foreign ? IDH_Aula_Documents::FOREIGN_TYPE : IDH_Aula_Documents::DEFAULT_TYPE,
            'doc_number' => IDH_Aula_Documents::normalize( $input['doc_number'] ?? '' ),
            'first_name' => sanitize_text_field( $input['first_name'] ?? '' ),
            'last_name'  => sanitize_text_field( $input['last_name'] ?? '' ),
            'birth_date' => idh_aula_parse_date( $input['birth_date'] ?? '' ),
            'phone'      => sanitize_text_field( $input['phone'] ?? '' ),
            'email'      => sanitize_email( $input['email'] ?? '' ),
            'notes'      => sanitize_textarea_field( $input['notes'] ?? '' ),
        ];

        if ( $is_foreign ) {
            // El código nunca se edita a mano: se conserva el que ya tenía o se genera uno nuevo.
            $keeps_code         = $current && IDH_Aula_Documents::FOREIGN_TYPE === $current->doc_type;
            $data['doc_number'] = $keeps_code ? $current->doc_number : IDH_Aula_Documents::generate_code();
        } elseif ( ! IDH_Aula_Documents::is_valid( $data['doc_number'], $data['doc_type'] ) ) {
            $errors[] = 'El DNI debe tener ' . IDH_Aula_Documents::hint( $data['doc_type'] ) . '. Si es extranjero, marca «Alumno extranjero».';
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

        $orderby = isset( self::SORTS[ $args['orderby'] ] ) ? $args['orderby'] : 'created_at';
        $order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
        $offset          = max( 0, ( (int) $args['page'] - 1 ) * (int) $args['per_page'] );

        // Los alumnos sin matrículas (last_enrolled NULL) quedan siempre al final.
        $order_sql   = str_replace( '{dir}', $order, self::SORTS[ $orderby ][0] );
        $enrollments = idh_aula_table( 'enrollments' );
        $sql         = 'SELECT s.*,
                (SELECT COUNT(*) FROM ' . $enrollments . ' e WHERE e.student_id = s.id) AS courses_count,
                (SELECT COUNT(*) FROM ' . $enrollments . " e WHERE e.student_id = s.id AND e.payment_status = 'pending') AS pending_count,
                (SELECT MAX(e.enrolled_at) FROM " . $enrollments . " e WHERE e.student_id = s.id) AS last_enrolled
            FROM " . self::table() . " s {$where} ORDER BY {$order_sql}, s.id DESC LIMIT %d OFFSET %d";

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
            // El código se guarda sin guiones: se busca también el texto normalizado (EXT-K7M2 → EXTK7M2).
            $doc_norm = IDH_Aula_Documents::normalize( $args['search'] );
            $doc_like = '' !== $doc_norm ? '%' . $wpdb->esc_like( $doc_norm ) . '%' : $like;
            $where[]  = "(s.doc_number LIKE %s OR CONCAT(s.first_name, ' ', s.last_name) LIKE %s OR s.phone LIKE %s OR s.email LIKE %s)";
            $params[] = $doc_like;
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
