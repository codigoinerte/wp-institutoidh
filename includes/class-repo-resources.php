<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Biblioteca de recursos reutilizables (videos de YouTube, carpetas de Drive, enlaces).
 */
class IDH_Aula_Resources {

    const TYPES = [
        'youtube' => 'Video de YouTube',
        'drive'   => 'Materiales (Google Drive)',
        'link'    => 'Enlace',
    ];

    public static function table() {
        return idh_aula_table( 'resources' );
    }

    public static function find( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) );
    }

    public static function sanitize( array $input ) {
        $errors = [];
        $type   = sanitize_key( $input['type'] ?? '' );
        $data   = [
            'type'  => isset( self::TYPES[ $type ] ) ? $type : 'link',
            'title' => sanitize_text_field( $input['title'] ?? '' ),
            'url'   => esc_url_raw( trim( (string) ( $input['url'] ?? '' ) ) ),
        ];
        if ( '' === $data['title'] ) {
            $errors[] = 'El título del recurso es obligatorio.';
        }
        if ( '' === $data['url'] ) {
            $errors[] = 'La URL del recurso no es válida.';
        } elseif ( 'youtube' === $data['type'] && '' === idh_aula_youtube_id( $data['url'] ) ) {
            $errors[] = 'La URL no corresponde a un video de YouTube.';
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

    public static function usage_count( $id ) {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(DISTINCT s.course_id) FROM ' . idh_aula_table( 'session_resources' ) . ' sr
                 INNER JOIN ' . idh_aula_table( 'sessions' ) . ' s ON s.id = sr.session_id WHERE sr.resource_id = %d',
                $id
            )
        );
    }

    /**
     * Elimina un recurso solo si ningún curso lo usa.
     */
    public static function delete( $id ) {
        global $wpdb;
        if ( self::usage_count( $id ) > 0 ) {
            return false;
        }
        $wpdb->delete( idh_aula_table( 'session_resources' ), [ 'resource_id' => (int) $id ] );
        return (bool) $wpdb->delete( self::table(), [ 'id' => (int) $id ] );
    }

    public static function search( $args = [] ) {
        global $wpdb;
        $args   = wp_parse_args( $args, [ 'search' => '', 'type' => '', 'per_page' => 20, 'page' => 1 ] );
        $where  = [ '1=1' ];
        $params = [];
        if ( '' !== $args['search'] ) {
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where[]  = '(r.title LIKE %s OR r.url LIKE %s)';
            $params[] = $like;
            $params[] = $like;
        }
        if ( isset( self::TYPES[ $args['type'] ] ) ) {
            $where[]  = 'r.type = %s';
            $params[] = $args['type'];
        }
        $where_sql = 'WHERE ' . implode( ' AND ', $where );
        $count_sql = 'SELECT COUNT(*) FROM ' . self::table() . " r {$where_sql}";
        $total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

        $sr       = idh_aula_table( 'session_resources' );
        $ss       = idh_aula_table( 'sessions' );
        $params[] = (int) $args['per_page'];
        $params[] = max( 0, ( (int) $args['page'] - 1 ) * (int) $args['per_page'] );
        $items    = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, (SELECT COUNT(DISTINCT s.course_id) FROM {$sr} sr INNER JOIN {$ss} s ON s.id = sr.session_id WHERE sr.resource_id = r.id) AS usage_count
                 FROM " . self::table() . " r {$where_sql} ORDER BY r.updated_at DESC LIMIT %d OFFSET %d",
                $params
            )
        );
        return [ $items, $total ];
    }

    public static function to_json( $resource ) {
        return [
            'id'    => (int) $resource->id,
            'type'  => $resource->type,
            'title' => $resource->title,
            'url'   => $resource->url,
            'label' => self::TYPES[ $resource->type ] ?? $resource->type,
        ];
    }
}
