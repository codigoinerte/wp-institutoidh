<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_Courses {

    public static function table() {
        return idh_aula_table( 'courses' );
    }

    public static function find( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
    }

    public static function all( $only_published = false ) {
        global $wpdb;
        $where = $only_published ? "WHERE status = 'published'" : '';
        return $wpdb->get_results( 'SELECT * FROM ' . self::table() . " {$where} ORDER BY created_at DESC" );
    }

    public static function sanitize( array $input ) {
        $errors = [];
        $data   = [
            'title'           => sanitize_text_field( $input['title'] ?? '' ),
            'category'        => sanitize_title( $input['category'] ?? '' ),
            'description'     => sanitize_textarea_field( $input['description'] ?? '' ),
            'cover_image_id'  => absint( $input['cover_image_id'] ?? 0 ),
            'hours'           => sanitize_text_field( $input['hours'] ?? '' ),
            'instructor'      => sanitize_text_field( $input['instructor'] ?? '' ),
            'price'           => max( 0, round( (float) str_replace( ',', '.', (string) ( $input['price'] ?? 0 ) ), 2 ) ),
            'landing_page_id' => absint( $input['landing_page_id'] ?? 0 ),
            'start_date'      => idh_aula_parse_date( $input['start_date'] ?? '' ) ?: null,
            'status'          => 'draft' === ( $input['status'] ?? '' ) ? 'draft' : 'published',
        ];
        $data['slug'] = sanitize_title( $data['title'] );

        if ( '' === $data['title'] ) {
            $errors[] = 'El título es obligatorio.';
        }
        return [ $data, $errors ];
    }

    public static function create( array $data ) {
        global $wpdb;
        $now                = idh_aula_now();
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

    /**
     * Elimina un curso solo si no tiene matrículas.
     */
    public static function delete( $id ) {
        global $wpdb;
        if ( IDH_Aula_Enrollments::count_for_course( $id ) > 0 ) {
            return false;
        }
        self::save_sessions( $id, [] );
        return (bool) $wpdb->delete( self::table(), [ 'id' => (int) $id ] );
    }

    /**
     * Clases del curso con sus recursos: [ { id, title, description, session_datetime, resources: [...] } ].
     */
    public static function sessions( $course_id ) {
        global $wpdb;
        $sessions = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . idh_aula_table( 'sessions' ) . ' WHERE course_id = %d ORDER BY position ASC, id ASC',
                $course_id
            )
        );
        if ( ! $sessions ) {
            return [];
        }

        $ids          = wp_list_pluck( $sessions, 'id' );
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $rows         = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT sr.session_id, r.* FROM ' . idh_aula_table( 'session_resources' ) . ' sr
                 INNER JOIN ' . idh_aula_table( 'resources' ) . " r ON r.id = sr.resource_id
                 WHERE sr.session_id IN ({$placeholders}) ORDER BY sr.position ASC",
                $ids
            )
        );

        $by_session = [];
        foreach ( $rows as $row ) {
            $by_session[ $row->session_id ][] = $row;
        }
        foreach ( $sessions as $session ) {
            $session->resources = $by_session[ $session->id ] ?? [];
        }
        return $sessions;
    }

    /**
     * Reemplaza las clases del curso. $sessions: [ [ title, description, session_datetime, resource_ids[] ] ].
     */
    public static function save_sessions( $course_id, array $sessions ) {
        global $wpdb;
        $sessions_table = idh_aula_table( 'sessions' );
        $pivot_table    = idh_aula_table( 'session_resources' );

        $old_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$sessions_table} WHERE course_id = %d", $course_id ) );
        if ( $old_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $old_ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$pivot_table} WHERE session_id IN ({$placeholders})", $old_ids ) );
            $wpdb->delete( $sessions_table, [ 'course_id' => (int) $course_id ] );
        }

        foreach ( array_values( $sessions ) as $position => $session ) {
            $wpdb->insert(
                $sessions_table,
                [
                    'course_id'        => (int) $course_id,
                    'position'         => $position,
                    'title'            => $session['title'],
                    'description'      => $session['description'],
                    'session_datetime' => $session['session_datetime'] ?: null,
                ]
            );
            $session_id = (int) $wpdb->insert_id;
            foreach ( array_values( array_unique( array_map( 'absint', $session['resource_ids'] ) ) ) as $res_position => $resource_id ) {
                if ( $resource_id ) {
                    $wpdb->insert(
                        $pivot_table,
                        [ 'session_id' => $session_id, 'resource_id' => $resource_id, 'position' => $res_position ]
                    );
                }
            }
        }
    }

    /**
     * Normaliza las clases enviadas desde el formulario del admin.
     */
    public static function sanitize_sessions( $raw ) {
        $sessions = [];
        if ( ! is_array( $raw ) ) {
            return $sessions;
        }
        foreach ( $raw as $item ) {
            $title = sanitize_text_field( $item['title'] ?? '' );
            $ids   = isset( $item['resource_ids'] ) ? array_filter( array_map( 'absint', explode( ',', (string) $item['resource_ids'] ) ) ) : [];
            if ( '' === $title && ! $ids ) {
                continue;
            }
            $datetime = '';
            if ( ! empty( $item['date'] ) ) {
                $date = idh_aula_parse_date( $item['date'] );
                if ( $date ) {
                    $time     = preg_match( '/^\d{2}:\d{2}$/', $item['time'] ?? '' ) ? $item['time'] : '00:00';
                    $datetime = $date . ' ' . $time . ':00';
                }
            }
            $sessions[] = [
                'title'            => $title ?: 'Sesión ' . str_pad( (string) ( count( $sessions ) + 1 ), 2, '0', STR_PAD_LEFT ),
                'description'      => sanitize_textarea_field( $item['description'] ?? '' ),
                'session_datetime' => $datetime,
                'resource_ids'     => $ids,
            ];
        }
        return $sessions;
    }

    /**
     * Duplica un curso (con sus clases y recursos compartidos) para una nueva edición.
     */
    public static function duplicate( $id ) {
        $course = self::find( $id );
        if ( ! $course ) {
            return 0;
        }
        $data = (array) $course;
        unset( $data['id'], $data['created_at'], $data['updated_at'] );
        $data['title']  = $course->title . ' (copia)';
        $data['slug']   = sanitize_title( $data['title'] );
        $data['status'] = 'draft';
        $new_id         = self::create( $data );

        $sessions = [];
        foreach ( self::sessions( $id ) as $session ) {
            $sessions[] = [
                'title'            => $session->title,
                'description'      => $session->description,
                'session_datetime' => $session->session_datetime,
                'resource_ids'     => wp_list_pluck( $session->resources, 'id' ),
            ];
        }
        self::save_sessions( $new_id, $sessions );
        return $new_id;
    }

    public static function cover_url( $course, $size = 'large' ) {
        if ( $course->cover_image_id ) {
            $url = wp_get_attachment_image_url( $course->cover_image_id, $size );
            if ( $url ) {
                return $url;
            }
        }
        if ( $course->landing_page_id && has_post_thumbnail( $course->landing_page_id ) ) {
            return get_the_post_thumbnail_url( $course->landing_page_id, $size );
        }
        return '';
    }

    /**
     * Listado admin con conteos de matrículas.
     */
    public static function search( $args = [] ) {
        global $wpdb;
        $args   = wp_parse_args( $args, [ 'search' => '', 'category' => '', 'per_page' => 20, 'page' => 1 ] );
        $where  = [ '1=1' ];
        $params = [];
        if ( '' !== $args['search'] ) {
            $where[]  = 'c.title LIKE %s';
            $params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }
        if ( '' !== $args['category'] ) {
            $where[]  = 'c.category = %s';
            $params[] = $args['category'];
        }
        $where_sql   = 'WHERE ' . implode( ' AND ', $where );
        $enrollments = idh_aula_table( 'enrollments' );
        $sessions    = idh_aula_table( 'sessions' );

        $total = (int) $wpdb->get_var(
            $params ? $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . " c {$where_sql}", $params ) : 'SELECT COUNT(*) FROM ' . self::table() . " c {$where_sql}"
        );

        $params[] = (int) $args['per_page'];
        $params[] = max( 0, ( (int) $args['page'] - 1 ) * (int) $args['per_page'] );
        $items    = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*,
                    (SELECT COUNT(*) FROM {$enrollments} e WHERE e.course_id = c.id) AS students_count,
                    (SELECT COUNT(*) FROM {$enrollments} e WHERE e.course_id = c.id AND e.payment_status = 'pending') AS pending_count,
                    (SELECT COUNT(*) FROM {$sessions} s WHERE s.course_id = c.id) AS sessions_count
                 FROM " . self::table() . " c {$where_sql} ORDER BY c.created_at DESC LIMIT %d OFFSET %d",
                $params
            )
        );
        return [ $items, $total ];
    }
}
