<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once IDH_AULA_DIR . 'admin/class-admin-actions.php';

/**
 * Menú "Aula Virtual" en wp-admin (solo administradores).
 */
class IDH_Aula_Admin {

    const PAGES = [
        'idh-aula'             => 'Panel y reportes',
        'idh-aula-alumnos'     => 'Alumnos',
        'idh-aula-cursos'      => 'Cursos',
        'idh-aula-recursos'    => 'Biblioteca de recursos',
        'idh-aula-matriculas'  => 'Matrículas',
        'idh-aula-importar'    => 'Importar alumnos',
        'idh-aula-ajustes'     => 'Ajustes',
    ];

    const PER_PAGE = 20;

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
        IDH_Aula_Admin_Actions::init();
    }

    public static function menu() {
        add_menu_page( 'Aula Virtual', 'Aula Virtual', IDH_AULA_CAP, 'idh-aula', [ __CLASS__, 'render' ], 'dashicons-welcome-learn-more', 26 );
        foreach ( self::PAGES as $slug => $title ) {
            add_submenu_page( 'idh-aula', $title, 'idh-aula' === $slug ? 'Panel y reportes' : $title, IDH_AULA_CAP, $slug, [ __CLASS__, 'render' ] );
        }
    }

    public static function is_plugin_screen() {
        $page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
        return isset( self::PAGES[ $page ] );
    }

    public static function assets() {
        if ( ! self::is_plugin_screen() ) {
            return;
        }
        $ver = static function ( $file ) {
            return filemtime( IDH_AULA_DIR . 'admin/assets/' . $file ) ?: IDH_AULA_VERSION;
        };
        wp_enqueue_media();
        wp_enqueue_style( 'idh-aula-admin', IDH_AULA_URL . 'admin/assets/admin.css', [], $ver( 'admin.css' ) );
        wp_enqueue_script( 'idh-aula-admin', IDH_AULA_URL . 'admin/assets/admin.js', [ 'jquery', 'jquery-ui-sortable' ], $ver( 'admin.js' ), true );
        wp_localize_script(
            'idh-aula-admin',
            'IDHAula',
            [
                'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                'nonce'         => wp_create_nonce( 'idh_aula_ajax' ),
                'resourceTypes' => IDH_Aula_Resources::TYPES,
            ]
        );
    }

    public static function render() {
        if ( ! current_user_can( IDH_AULA_CAP ) ) {
            wp_die( 'No tienes permisos para acceder a esta página.' );
        }
        $page   = sanitize_key( $_GET['page'] ?? 'idh-aula' );
        $action = sanitize_key( $_GET['action'] ?? '' );

        echo '<div class="wrap idh-aula-admin">';
        self::notices();

        switch ( $page ) {
            case 'idh-aula-alumnos':
                $action === 'edit' || $action === 'new' ? self::view( 'student-edit' ) : self::view( 'students-list' );
                break;
            case 'idh-aula-cursos':
                $action === 'edit' || $action === 'new' ? self::view( 'course-edit' ) : self::view( 'courses-list' );
                break;
            case 'idh-aula-recursos':
                self::view( 'resources' );
                break;
            case 'idh-aula-matriculas':
                self::view( 'enrollments' );
                break;
            case 'idh-aula-importar':
                self::view( 'import' );
                break;
            case 'idh-aula-ajustes':
                self::view( 'settings' );
                break;
            default:
                self::view( 'dashboard' );
        }
        echo '</div>';
    }

    private static function view( $name ) {
        include IDH_AULA_DIR . 'admin/views/' . $name . '.php';
    }

    // ─── Avisos (flash) ───

    public static function flash( $type, $messages, $old = null ) {
        set_transient(
            'idh_aula_flash_' . get_current_user_id(),
            [ 'type' => $type, 'messages' => (array) $messages, 'old' => $old ],
            MINUTE_IN_SECONDS * 5
        );
    }

    /**
     * Datos de formulario que se reenvían tras un error de validación.
     */
    public static function old( $key = null, $default = '' ) {
        static $old = null;
        if ( null === $old ) {
            $flash = get_transient( 'idh_aula_flash_' . get_current_user_id() );
            $old   = is_array( $flash ) && is_array( $flash['old'] ) ? $flash['old'] : [];
        }
        if ( null === $key ) {
            return $old;
        }
        return $old[ $key ] ?? $default;
    }

    private static function notices() {
        $key   = 'idh_aula_flash_' . get_current_user_id();
        $flash = get_transient( $key );
        if ( ! $flash ) {
            return;
        }
        self::old(); // carga los datos antiguos antes de borrar el aviso
        delete_transient( $key );
        $class = 'error' === $flash['type'] ? 'notice-error' : ( 'warning' === $flash['type'] ? 'notice-warning' : 'notice-success' );
        // data-idh-toast: admin.js muestra además un aviso flotante con el primer mensaje.
        echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible" data-idh-toast="' . esc_attr( $flash['type'] ) . '" data-idh-toast-more="' . (int) max( 0, count( $flash['messages'] ) - 1 ) . '">';
        foreach ( $flash['messages'] as $message ) {
            echo '<p>' . wp_kses( $message, [ 'strong' => [], 'a' => [ 'href' => [] ], 'code' => [] ] ) . '</p>';
        }
        echo '</div>';
    }

    // ─── Utilidades de vistas ───

    // ─── Orden de los listados ───

    /**
     * Orden pedido en la URL. Vale `sort=clave:dirección` (el selector) o `orderby` y `order` (los
     * encabezados de columna). Si no es válido o no viene, se usa la fecha de registro, de más reciente a más antigua.
     *
     * @param array $sorts Criterios permitidos (IDH_Aula_Students::SORTS o IDH_Aula_Courses::SORTS).
     * @return array [ clave, 'asc'|'desc' ]
     */
    public static function sort_request( array $sorts ) {
        $orderby = sanitize_key( $_GET['orderby'] ?? '' );
        $order   = sanitize_key( $_GET['order'] ?? '' );
        if ( ! empty( $_GET['sort'] ) ) {
            $parts   = explode( ':', sanitize_text_field( wp_unslash( $_GET['sort'] ) ) );
            $orderby = sanitize_key( $parts[0] );
            $order   = sanitize_key( $parts[1] ?? '' );
        }
        if ( ! isset( $sorts[ $orderby ] ) ) {
            return [ 'created_at', 'desc' ];
        }
        return [ $orderby, in_array( $order, [ 'asc', 'desc' ], true ) ? $order : $sorts[ $orderby ][1] ];
    }

    /**
     * Encabezado de columna ordenable con el estilo nativo de WordPress (flechas arriba y abajo).
     * Un clic ordena por la columna; otro clic invierte la dirección.
     */
    public static function sortable_th( $label, $key, array $sorts, $current, array $args = [], $width = '' ) {
        list( $orderby, $order ) = $current;
        $active = $orderby === $key;
        $next   = $active ? ( 'asc' === $order ? 'desc' : 'asc' ) : $sorts[ $key ][1];
        $url    = add_query_arg( array_merge( $args, [ 'orderby' => $key, 'order' => $next ] ), remove_query_arg( [ 'sort', 'paged' ] ) );
        $class  = 'manage-column sortable ' . ( $active ? 'sorted ' . $order : ( 'asc' === $next ? 'desc' : 'asc' ) );
        $style  = $width ? ' style="width:' . esc_attr( $width ) . '"' : '';
        printf(
            '<th scope="col" class="%s"%s aria-sort="%s"><a href="%s"><span>%s</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span></a></th>',
            esc_attr( $class ),
            $style, // phpcs:ignore WordPress.Security.EscapeOutput
            $active ? ( 'asc' === $order ? 'ascending' : 'descending' ) : 'none',
            esc_url( $url ),
            esc_html( $label )
        );
    }

    /**
     * Formulario «Ordenar por». Se imprime dos veces: arriba (escritorio, junto a los filtros) y debajo de la tabla (móvil).
     * Selector «Ordenar por» con criterios que no tienen columna (registro, última matrícula…).
     * Comparte el estado con los encabezados: si el orden activo es una columna, el selector queda en «Ordenar por…».
     *
     * @param array $options Mapa 'clave:dirección' => etiqueta.
     */
    public static function sort_form( array $options, $current, array $hidden, $position = 'top' ) {
        echo '<form method="get" class="idh-sort-form idh-sort-form--' . esc_attr( $position ) . '">';
        foreach ( $hidden as $name => $value ) {
            echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
        }
        self::sort_select( $options, $current, 'idh-sort-' . $position );
        echo '</form>';
    }

    public static function sort_select( array $options, $current, $id = 'idh-sort' ) {
        $active = $current[0] . ':' . $current[1];
        echo '<span class="idh-sort">';
        echo '<label class="idh-sort-icon" for="' . esc_attr( $id ) . '" title="Ordenar por"><span class="screen-reader-text">Ordenar por</span>';
        echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M11 4h10"/><path d="M11 8h7"/><path d="M11 12h4"/></svg>';
        echo '</label>';
        echo '<select name="sort" id="' . esc_attr( $id ) . '" data-autosubmit>';
        echo '<option value="">Ordenar por…</option>';
        foreach ( $options as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '"' . selected( $active, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '</span>';
    }

    public static function pagination( $total, $per_page, $current ) {
        $pages = (int) ceil( $total / $per_page );
        if ( $pages < 2 ) {
            return;
        }
        $links = paginate_links(
            [
                'base'      => add_query_arg( 'paged', '%#%' ),
                'format'    => '',
                'current'   => $current,
                'total'     => $pages,
                'prev_text' => '‹',
                'next_text' => '›',
            ]
        );
        echo '<div class="tablenav"><div class="tablenav-pages"><span class="displaying-num">' . esc_html( $total ) . ' elementos</span> <span class="pagination-links">' . $links . '</span></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    public static function current_page() {
        return max( 1, absint( $_GET['paged'] ?? 1 ) );
    }

    public static function post_url( $action, array $args = [] ) {
        return wp_nonce_url( add_query_arg( array_merge( [ 'action' => $action ], $args ), admin_url( 'admin-post.php' ) ), $action );
    }

    public static function payment_badge( $status ) {
        return 'paid' === $status
            ? '<span class="idh-badge idh-badge--success">Pagado</span>'
            : '<span class="idh-badge idh-badge--warning">Pendiente</span>';
    }

    public static function category_badge( $value ) {
        $color = IDH_Aula_Categories::color( $value );
        return '<span class="idh-badge" style="background:' . esc_attr( $color ) . '1a;color:' . esc_attr( $color ) . '">' . esc_html( IDH_Aula_Categories::label( $value ) ) . '</span>';
    }

    public static function course_options( $selected = 0, $placeholder = 'Selecciona un curso' ) {
        $html = '<option value="">' . esc_html( $placeholder ) . '</option>';
        foreach ( IDH_Aula_Courses::all() as $course ) {
            $label = $course->title . ( 'draft' === $course->status ? ' (borrador)' : '' ) . ( $course->start_date ? ' · ' . idh_aula_format_date( $course->start_date ) : '' );
            $html .= '<option value="' . (int) $course->id . '"' . selected( (int) $selected, (int) $course->id, false ) . '>' . esc_html( $label ) . '</option>';
        }
        return $html;
    }
}
