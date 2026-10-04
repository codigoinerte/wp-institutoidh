<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rutas públicas del aula virtual:
 *   /aula-virtual                     login o listado de cursos
 *   /aula-virtual/curso/{id}          detalle del curso
 *   /aula-virtual/certificado/{code}  descarga del certificado (respeta bloqueos)
 *   /aula-virtual/salir               cerrar sesión
 */
class IDH_Aula_Router {

    const QV_ROUTE = 'idh_aula';
    const QV_PARAM = 'idh_aula_param';

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_rules' ] );
        add_filter( 'query_vars', [ __CLASS__, 'query_vars' ] );
        add_action( 'template_redirect', [ __CLASS__, 'dispatch' ], 0 );
    }

    public static function register_rules() {
        $slug = IDH_AULA_SLUG;
        add_rewrite_rule( "^{$slug}/?$", 'index.php?' . self::QV_ROUTE . '=home', 'top' );
        add_rewrite_rule( "^{$slug}/curso/([0-9]+)/?$", 'index.php?' . self::QV_ROUTE . '=course&' . self::QV_PARAM . '=$matches[1]', 'top' );
        add_rewrite_rule( "^{$slug}/certificado/([A-Za-z0-9]+)/?$", 'index.php?' . self::QV_ROUTE . '=certificate&' . self::QV_PARAM . '=$matches[1]', 'top' );
        add_rewrite_rule( "^{$slug}/salir/?$", 'index.php?' . self::QV_ROUTE . '=logout', 'top' );
    }

    public static function query_vars( $vars ) {
        $vars[] = self::QV_ROUTE;
        $vars[] = self::QV_PARAM;
        return $vars;
    }

    public static function dispatch() {
        $route = get_query_var( self::QV_ROUTE );
        if ( ! $route ) {
            return;
        }
        global $wp_query;
        $wp_query->is_404 = false;
        status_header( 200 );
        nocache_headers();
        header( 'X-Robots-Tag: noindex, nofollow', true );

        $param = get_query_var( self::QV_PARAM );
        switch ( $route ) {
            case 'home':
                self::home();
                break;
            case 'course':
                self::course( (int) $param );
                break;
            case 'certificate':
                self::certificate( (string) $param );
                break;
            case 'logout':
                IDH_Aula_Auth::logout();
                wp_safe_redirect( idh_aula_portal_url() );
                break;
        }
        exit;
    }

    private static function home() {
        $error = '';
        $old   = [ 'doc_number' => '', 'birth_date' => '' ];

        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['idh_aula_login'] ) ) {
            $old['doc_number'] = sanitize_text_field( wp_unslash( $_POST['doc_number'] ?? '' ) );
            $old['birth_date'] = sanitize_text_field( wp_unslash( $_POST['birth_date'] ?? '' ) );

            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_idh_nonce'] ?? '' ) ), 'idh_aula_login' ) ) {
                $error = 'La sesión del formulario expiró. Vuelve a intentarlo.';
            } else {
                $result = IDH_Aula_Auth::attempt( $old['doc_number'], $old['birth_date'] );
                if ( is_wp_error( $result ) ) {
                    $error = $result->get_error_message();
                } else {
                    wp_safe_redirect( idh_aula_portal_url() );
                    return;
                }
            }
        }

        $student = IDH_Aula_Auth::current();
        if ( ! $student ) {
            self::render( 'login', [ 'title' => 'Ingresa al aula virtual', 'error' => $error, 'old' => $old ] );
            return;
        }

        self::render(
            'listado',
            [
                'title'       => 'Mis cursos',
                'student'     => $student,
                'enrollments' => IDH_Aula_Enrollments::for_student( $student->id, true ),
            ]
        );
    }

    private static function course( $course_id ) {
        $student = IDH_Aula_Auth::current();
        if ( ! $student ) {
            wp_safe_redirect( idh_aula_portal_url() );
            return;
        }

        $enrollment = null;
        foreach ( IDH_Aula_Enrollments::for_student( $student->id, true ) as $row ) {
            if ( (int) $row->course_id === $course_id ) {
                $enrollment = $row;
                break;
            }
        }

        if ( ! $enrollment ) {
            status_header( 404 );
            self::render( 'bloqueado', [ 'title' => 'Curso no encontrado', 'student' => $student, 'state' => 'missing' ] );
            return;
        }

        $state = IDH_Aula_Enrollments::access_state( $enrollment );
        if ( 'ok' !== $state ) {
            status_header( 403 );
            self::render( 'bloqueado', [ 'title' => $enrollment->course_title, 'student' => $student, 'state' => $state, 'enrollment' => $enrollment ] );
            return;
        }

        self::render(
            'curso',
            [
                'title'      => $enrollment->course_title,
                'student'    => $student,
                'enrollment' => $enrollment,
                'course'     => IDH_Aula_Courses::find( $course_id ),
                'sessions'   => IDH_Aula_Courses::sessions( $course_id ),
            ]
        );
    }

    /**
     * Enlace público del certificado. Deja de funcionar si el alumno o la matrícula están bloqueados.
     */
    private static function certificate( $code ) {
        $cert       = IDH_Aula_Certificates::find_by_code( $code );
        $enrollment = $cert ? IDH_Aula_Enrollments::find( $cert->enrollment_id ) : null;
        $student    = $enrollment ? IDH_Aula_Students::find( $enrollment->student_id ) : null;

        if ( ! $cert || ! $enrollment || ! $student ) {
            status_header( 404 );
            self::render( 'bloqueado', [ 'title' => 'Certificado no encontrado', 'state' => 'missing' ] );
            return;
        }
        if ( 'active' !== $student->status || (int) $enrollment->locked ) {
            status_header( 403 );
            self::render( 'bloqueado', [ 'title' => 'Certificado no disponible', 'state' => 'certificate' ] );
            return;
        }

        if ( 'url' === $cert->mode ) {
            wp_redirect( $cert->external_url ); // phpcs:ignore WordPress.Security.SafeRedirect -- URL externa registrada por el admin.
            return;
        }

        $path = IDH_Aula_Certificates::pdf_absolute_path( $cert );
        if ( ! $path ) {
            status_header( 404 );
            self::render( 'bloqueado', [ 'title' => 'Certificado no encontrado', 'state' => 'missing' ] );
            return;
        }

        $filename = $cert->pdf_name ?: 'certificado-' . $cert->code . '.pdf';
        $inline   = ! isset( $_GET['descargar'] );
        header( 'Content-Type: application/pdf' );
        header( 'Content-Length: ' . filesize( $path ) );
        header( 'Content-Disposition: ' . ( $inline ? 'inline' : 'attachment' ) . '; filename="' . sanitize_file_name( $filename ) . '"' );
        header( 'X-Content-Type-Options: nosniff' );
        readfile( $path );
    }

    private static function render( $template, array $vars ) {
        $vars['template'] = $template;
        idh_aula_view( 'public/templates/layout.php', $vars );
    }
}
