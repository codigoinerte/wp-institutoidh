<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Acciones del admin (admin-post.php y admin-ajax.php).
 */
class IDH_Aula_Admin_Actions {

    const POST_ACTIONS = [
        'idh_aula_save_student',
        'idh_aula_student_status',
        'idh_aula_delete_student',
        'idh_aula_enroll_student',
        'idh_aula_save_enrollment',
        'idh_aula_delete_enrollment',
        'idh_aula_delete_certificate',
        'idh_aula_save_course',
        'idh_aula_delete_course',
        'idh_aula_duplicate_course',
        'idh_aula_save_resource',
        'idh_aula_delete_resource',
        'idh_aula_bulk_enroll',
        'idh_aula_import_preview',
        'idh_aula_import_confirm',
        'idh_aula_export',
        'idh_aula_csv_template',
        'idh_aula_save_settings',
    ];

    const AJAX_ACTIONS = [
        'idh_aula_search_resources',
        'idh_aula_create_resource',
        'idh_aula_toggle_enrollment',
        'idh_aula_search_students',
    ];

    public static function init() {
        foreach ( self::POST_ACTIONS as $action ) {
            add_action( 'admin_post_' . $action, [ __CLASS__, substr( $action, 9 ) ] );
        }
        foreach ( self::AJAX_ACTIONS as $action ) {
            add_action( 'wp_ajax_' . $action, [ __CLASS__, 'ajax_' . substr( $action, 9 ) ] );
        }
    }

    // ─── Utilidades ───

    private static function guard( $action ) {
        if ( ! current_user_can( IDH_AULA_CAP ) ) {
            wp_die( 'No tienes permisos para realizar esta acción.', 403 );
        }
        check_admin_referer( $action );
    }

    private static function ajax_guard() {
        if ( ! current_user_can( IDH_AULA_CAP ) || ! check_ajax_referer( 'idh_aula_ajax', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Sesión expirada o sin permisos. Recarga la página.' ], 403 );
        }
    }

    private static function redirect( $url ) {
        wp_safe_redirect( $url );
        exit;
    }

    private static function back( $fallback ) {
        $to = isset( $_REQUEST['_redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['_redirect'] ) ) : '';
        self::redirect( $to ?: ( wp_get_referer() ?: $fallback ) );
    }

    private static function input( $key, $default = '' ) {
        return isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $default; // phpcs:ignore WordPress.Security
    }

    // ─── Alumnos ───

    public static function save_student() {
        self::guard( 'idh_aula_save_student' );
        $id                     = absint( self::input( 'id', 0 ) );
        list( $data, $errors )  = IDH_Aula_Students::sanitize( (array) self::input( 'student', [] ), $id );

        if ( $errors ) {
            IDH_Aula_Admin::flash( 'error', $errors, $data );
            self::redirect( idh_aula_admin_url( 'idh-aula-alumnos', $id ? [ 'action' => 'edit', 'id' => $id ] : [ 'action' => 'new' ] ) );
        }

        if ( $id ) {
            IDH_Aula_Students::update( $id, $data );
            IDH_Aula_Admin::flash( 'success', 'Datos del alumno actualizados.' );
        } else {
            $id       = IDH_Aula_Students::create( $data );
            $messages = [ 'Alumno registrado. Ahora puedes matricularlo en sus cursos.' ];
            if ( IDH_Aula_Documents::FOREIGN_TYPE === $data['doc_type'] ) {
                $messages[] = 'Su código de acceso es <code>' . esc_html( IDH_Aula_Documents::display( $data['doc_number'], $data['doc_type'] ) ) . '</code>. Entrégaselo al alumno: ingresa con este código y su fecha de nacimiento.';
            }
            IDH_Aula_Admin::flash( 'success', $messages );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $id ] ) );
    }

    public static function student_status() {
        self::guard( 'idh_aula_student_status' );
        $id     = absint( self::input( 'id', $_GET['id'] ?? 0 ) );
        $status = sanitize_key( self::input( 'status', $_GET['status'] ?? '' ) );
        $reason = sanitize_text_field( self::input( 'reason', '' ) );
        IDH_Aula_Students::set_status( $id, $status, $reason );
        IDH_Aula_Admin::flash(
            'success',
            'blocked' === $status
                ? 'Alumno bloqueado: ya no puede ingresar al aula ni descargar sus certificados.'
                : 'Alumno desbloqueado: vuelve a tener acceso al aula.'
        );
        self::back( idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $id ] ) );
    }

    public static function delete_student() {
        self::guard( 'idh_aula_delete_student' );
        $id = absint( $_GET['id'] ?? 0 );
        if ( IDH_Aula_Students::delete( $id ) ) {
            IDH_Aula_Admin::flash( 'success', 'Alumno eliminado.' );
        } else {
            IDH_Aula_Admin::flash( 'error', 'No se puede eliminar un alumno con matrículas. Si ya no estará en la plataforma, <strong>bloquéalo</strong>.' );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-alumnos' ) );
    }

    // ─── Matrículas ───

    public static function enroll_student() {
        self::guard( 'idh_aula_enroll_student' );
        $student_id = absint( self::input( 'student_id', 0 ) );
        $course_id  = absint( self::input( 'course_id', 0 ) );
        $payment    = sanitize_key( self::input( 'payment_status', 'pending' ) );

        if ( ! $course_id || ! IDH_Aula_Students::find( $student_id ) ) {
            IDH_Aula_Admin::flash( 'error', 'Selecciona un curso.' );
        } else {
            list( , $created ) = IDH_Aula_Enrollments::enroll( $student_id, $course_id, $payment );
            IDH_Aula_Admin::flash( $created ? 'success' : 'warning', $created ? 'Alumno matriculado.' : 'El alumno ya estaba matriculado en ese curso.' );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $student_id ] ) . '#matriculas' );
    }

    public static function save_enrollment() {
        self::guard( 'idh_aula_save_enrollment' );
        $id         = absint( self::input( 'enrollment_id', 0 ) );
        $enrollment = IDH_Aula_Enrollments::find( $id );
        if ( ! $enrollment ) {
            IDH_Aula_Admin::flash( 'error', 'Matrícula no encontrada.' );
            self::back( idh_aula_admin_url( 'idh-aula-matriculas' ) );
        }

        $input = (array) self::input( 'enrollment', [] );
        IDH_Aula_Enrollments::update(
            $id,
            [
                'payment_status' => 'paid' === ( $input['payment_status'] ?? '' ) ? 'paid' : 'pending',
                'locked'         => empty( $input['locked'] ) ? 0 : 1,
                'lock_reason'    => sanitize_text_field( $input['lock_reason'] ?? '' ),
                'price_paid'     => max( 0, round( (float) str_replace( ',', '.', (string) ( $input['price_paid'] ?? 0 ) ), 2 ) ),
            ]
        );

        $messages = [ 'Matrícula actualizada.' ];
        $type     = 'success';
        $cert     = (array) self::input( 'certificate', [] );
        $file     = $_FILES['certificate_pdf'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        $has_cert = ! empty( $cert['external_url'] ) || ! empty( $cert['verify_url'] ) || ( $file && ! empty( $file['name'] ) ) || IDH_Aula_Certificates::find_by_enrollment( $id );

        if ( $has_cert && ! empty( $cert['mode'] ) ) {
            $result = IDH_Aula_Certificates::save( $id, $cert, $file );
            if ( true === $result ) {
                $messages[] = 'Certificado guardado.';
            } else {
                $type     = 'error';
                $messages = array_merge( [ 'La matrícula se guardó, pero el certificado tiene errores:' ], $result );
            }
        }

        IDH_Aula_Admin::flash( $type, $messages );
        self::back( idh_aula_admin_url( 'idh-aula-alumnos', [ 'action' => 'edit', 'id' => $enrollment->student_id ] ) );
    }

    public static function delete_enrollment() {
        self::guard( 'idh_aula_delete_enrollment' );
        $enrollment = IDH_Aula_Enrollments::find( absint( $_GET['id'] ?? 0 ) );
        if ( $enrollment ) {
            IDH_Aula_Enrollments::delete( $enrollment->id );
            IDH_Aula_Admin::flash( 'success', 'Matrícula eliminada (y su certificado, si tenía).' );
        }
        self::back( idh_aula_admin_url( 'idh-aula-matriculas' ) );
    }

    public static function delete_certificate() {
        self::guard( 'idh_aula_delete_certificate' );
        IDH_Aula_Certificates::delete_for_enrollment( absint( $_GET['enrollment_id'] ?? 0 ) );
        IDH_Aula_Admin::flash( 'success', 'Certificado eliminado.' );
        self::back( idh_aula_admin_url( 'idh-aula-matriculas' ) );
    }

    /**
     * Matrícula masiva: lista de DNIs pegada o alumnos seleccionados en el listado.
     */
    public static function bulk_enroll() {
        self::guard( 'idh_aula_bulk_enroll' );
        $course_id = absint( self::input( 'course_id', 0 ) );
        $payment   = sanitize_key( self::input( 'payment_status', 'pending' ) );
        $fallback  = idh_aula_admin_url( 'idh-aula-matriculas' );

        if ( ! $course_id || ! IDH_Aula_Courses::find( $course_id ) ) {
            IDH_Aula_Admin::flash( 'error', 'Selecciona el curso en el que quieres matricular.' );
            self::back( $fallback );
        }

        $student_ids = array_filter( array_map( 'absint', (array) self::input( 'student_ids', [] ) ) );
        $not_found   = [];
        $invalid     = [];

        $raw_dnis = (string) self::input( 'dnis', '' );
        if ( '' !== trim( $raw_dnis ) ) {
            foreach ( array_unique( preg_split( '/[\s,;]+/', $raw_dnis, -1, PREG_SPLIT_NO_EMPTY ) ) as $dni ) {
                $dni = IDH_Aula_Documents::normalize( $dni );
                if ( ! IDH_Aula_Documents::is_valid( $dni, IDH_Aula_Documents::type_for( $dni ) ) ) {
                    $invalid[] = $dni;
                    continue;
                }
                $student = IDH_Aula_Students::find_by_identifier( $dni );
                if ( $student ) {
                    $student_ids[] = (int) $student->id;
                } else {
                    $not_found[] = $dni;
                }
            }
        }

        if ( ! $student_ids && ! $not_found && ! $invalid ) {
            IDH_Aula_Admin::flash( 'error', 'No seleccionaste alumnos ni ingresaste DNIs o códigos.' );
            self::back( $fallback );
        }

        $created = 0;
        $skipped = 0;
        foreach ( array_unique( $student_ids ) as $student_id ) {
            list( , $was_created ) = IDH_Aula_Enrollments::enroll( $student_id, $course_id, $payment );
            $was_created ? $created++ : $skipped++;
        }

        $messages = [ sprintf( '<strong>%d</strong> alumno(s) matriculado(s).', $created ) ];
        if ( $skipped ) {
            $messages[] = sprintf( '%d ya estaban matriculados en ese curso.', $skipped );
        }
        if ( $not_found ) {
            $messages[] = 'DNIs o códigos no registrados (regístralos primero o impórtalos por CSV): <code>' . esc_html( implode( ', ', $not_found ) ) . '</code>';
        }
        if ( $invalid ) {
            $messages[] = 'Valores que no son un DNI ni un código de alumno válidos: <code>' . esc_html( implode( ', ', $invalid ) ) . '</code>';
        }
        IDH_Aula_Admin::flash( $not_found || $invalid ? 'warning' : 'success', $messages );
        self::redirect( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $course_id ] ) );
    }

    // ─── Ajustes ───

    public static function save_settings() {
        self::guard( 'idh_aula_save_settings' );
        $number = preg_replace( '/\D+/', '', (string) self::input( 'whatsapp', '' ) );
        if ( $number && strlen( $number ) < 10 ) {
            IDH_Aula_Admin::flash( 'error', 'Escribe el número con el código de país, por ejemplo 51987654321.' );
            self::redirect( idh_aula_admin_url( 'idh-aula-ajustes' ) );
        }
        update_option( 'idh_aula_whatsapp', $number, false );
        IDH_Aula_Admin::flash( 'success', 'Ajustes guardados.' );
        self::redirect( idh_aula_admin_url( 'idh-aula-ajustes' ) );
    }

    // ─── Cursos ───

    public static function save_course() {
        self::guard( 'idh_aula_save_course' );
        $id                    = absint( self::input( 'id', 0 ) );
        list( $data, $errors ) = IDH_Aula_Courses::sanitize( (array) self::input( 'course', [] ) );
        $sessions              = IDH_Aula_Courses::sanitize_sessions( self::input( 'sessions', [] ) );

        if ( $errors ) {
            IDH_Aula_Admin::flash( 'error', $errors, array_merge( $data, [ '_sessions' => $sessions ] ) );
            self::redirect( idh_aula_admin_url( 'idh-aula-cursos', $id ? [ 'action' => 'edit', 'id' => $id ] : [ 'action' => 'new' ] ) );
        }

        if ( $id ) {
            IDH_Aula_Courses::update( $id, $data );
        } else {
            $id = IDH_Aula_Courses::create( $data );
        }
        IDH_Aula_Courses::save_sessions( $id, $sessions );
        IDH_Aula_Admin::flash( 'success', 'Curso guardado.' );
        self::redirect( idh_aula_admin_url( 'idh-aula-cursos', [ 'action' => 'edit', 'id' => $id ] ) );
    }

    public static function delete_course() {
        self::guard( 'idh_aula_delete_course' );
        if ( IDH_Aula_Courses::delete( absint( $_GET['id'] ?? 0 ) ) ) {
            IDH_Aula_Admin::flash( 'success', 'Curso eliminado.' );
        } else {
            IDH_Aula_Admin::flash( 'error', 'No se puede eliminar un curso con alumnos matriculados. Puedes pasarlo a borrador para ocultarlo.' );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-cursos' ) );
    }

    public static function duplicate_course() {
        self::guard( 'idh_aula_duplicate_course' );
        $new_id = IDH_Aula_Courses::duplicate( absint( $_GET['id'] ?? 0 ) );
        if ( $new_id ) {
            IDH_Aula_Admin::flash( 'success', 'Curso duplicado como borrador, con las mismas clases y recursos. Ajusta el título y las fechas de la nueva edición.' );
            self::redirect( idh_aula_admin_url( 'idh-aula-cursos', [ 'action' => 'edit', 'id' => $new_id ] ) );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-cursos' ) );
    }

    // ─── Recursos ───

    public static function save_resource() {
        self::guard( 'idh_aula_save_resource' );
        $id                    = absint( self::input( 'id', 0 ) );
        list( $data, $errors ) = IDH_Aula_Resources::sanitize( (array) self::input( 'resource', [] ) );
        if ( $errors ) {
            IDH_Aula_Admin::flash( 'error', $errors, $data );
            self::redirect( idh_aula_admin_url( 'idh-aula-recursos', $id ? [ 'edit' => $id ] : [] ) );
        }
        if ( $id ) {
            IDH_Aula_Resources::update( $id, $data );
            IDH_Aula_Admin::flash( 'success', 'Recurso actualizado. El cambio se refleja en todos los cursos que lo usan.' );
        } else {
            IDH_Aula_Resources::create( $data );
            IDH_Aula_Admin::flash( 'success', 'Recurso agregado a la biblioteca.' );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-recursos' ) );
    }

    public static function delete_resource() {
        self::guard( 'idh_aula_delete_resource' );
        if ( IDH_Aula_Resources::delete( absint( $_GET['id'] ?? 0 ) ) ) {
            IDH_Aula_Admin::flash( 'success', 'Recurso eliminado.' );
        } else {
            IDH_Aula_Admin::flash( 'error', 'No se puede eliminar un recurso que está en uso. Quítalo primero de las clases.' );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-recursos' ) );
    }

    // ─── Importación CSV ───

    public static function import_preview() {
        self::guard( 'idh_aula_import_preview' );
        $page = idh_aula_admin_url( 'idh-aula-importar' );
        $file = $_FILES['csv'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

        if ( ! $file || UPLOAD_ERR_OK !== (int) $file['error'] ) {
            IDH_Aula_Admin::flash( 'error', 'Selecciona un archivo CSV.' );
            self::redirect( $page );
        }
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $ext, [ 'csv', 'txt' ], true ) ) {
            IDH_Aula_Admin::flash( 'error', 'El archivo debe ser .csv. En Excel usa "Guardar como → CSV UTF-8".' );
            self::redirect( $page );
        }

        $rows = IDH_Aula_CSV::parse( $file['tmp_name'] );
        if ( is_wp_error( $rows ) ) {
            IDH_Aula_Admin::flash( 'error', $rows->get_error_message() );
            self::redirect( $page );
        }

        $default_course = absint( self::input( 'course_id', 0 ) );
        $payment        = 'paid' === self::input( 'payment_status', '' ) ? 'paid' : 'pending';
        $seen           = [];
        $preview        = [];

        foreach ( $rows as $row ) {
            $foreign = in_array( strtolower( remove_accents( trim( $row['extranjero'] ?? '' ) ) ), [ 'si', 's', 'x', '1', 'true', 'yes', 'y' ], true );
            $dni     = preg_replace( '/\D/', '', $row['dni'] ?? '' );
            if ( ! $foreign && '' !== $dni && strlen( $dni ) < 8 ) {
                // Excel elimina los ceros a la izquierda de los DNI.
                $dni = str_pad( $dni, 8, '0', STR_PAD_LEFT );
            }
            $input = [
                'doc_number' => $foreign ? '' : $dni,
                'first_name' => $row['nombres'] ?? '',
                'last_name'  => $row['apellidos'] ?? '',
                'birth_date' => $row['fecha_nacimiento'] ?? '',
                'phone'      => $row['celular'] ?? '',
                'email'      => $row['correo'] ?? '',
            ];
            if ( $foreign ) {
                // Sin DNI: el alumno recibe un código de acceso al confirmar la importación.
                $input['is_foreign'] = 1;
                $birth               = idh_aula_parse_date( $input['birth_date'] );
                $existing            = IDH_Aula_Students::find_foreign_match( $input['first_name'], $input['last_name'], $birth );
                $doc                 = '';
                $key                 = $birth ? strtolower( trim( $input['first_name'] . '|' . $input['last_name'] ) ) . '|' . $birth : '';
            } else {
                $doc      = IDH_Aula_Documents::normalize( $input['doc_number'] );
                $existing = $doc ? IDH_Aula_Students::find_by_doc( $doc ) : null;
                $key      = $doc;
            }
            $course = absint( $row['curso_id'] ?? 0 ) ?: $default_course;
            $errors = [];

            if ( $existing ) {
                // Alumno ya registrado: solo se matricula (si corresponde), no se modifican sus datos.
                $data   = (array) $existing;
                $status = 'existing';
            } else {
                list( $data, $errors ) = IDH_Aula_Students::sanitize( $input );
                $status                = $errors ? 'error' : 'new';
                if ( $foreign ) {
                    $data['doc_number'] = ''; // el código se genera al confirmar
                }
            }
            if ( $key && isset( $seen[ $key ] ) ) {
                $errors[] = ( $foreign ? 'Alumno repetido' : 'DNI repetido' ) . ' en el archivo (fila ' . $seen[ $key ] . ').';
                $status   = 'error';
            }
            if ( $course && ! IDH_Aula_Courses::find( $course ) ) {
                $errors[] = 'El curso_id ' . $course . ' no existe.';
                $status   = 'error';
            }
            if ( 'error' !== $status && $key ) {
                $seen[ $key ] = $row['_line'];
            }

            $preview[] = [
                'line'      => $row['_line'],
                'status'    => $status,
                'errors'    => $errors,
                'data'      => $data,
                'course_id' => $course,
                'foreign'   => $foreign,
            ];
        }

        $token = wp_generate_password( 16, false );
        set_transient( 'idh_aula_import_' . $token, [ 'rows' => $preview, 'payment' => $payment, 'user' => get_current_user_id() ], HOUR_IN_SECONDS );
        self::redirect( idh_aula_admin_url( 'idh-aula-importar', [ 'token' => $token ] ) );
    }

    public static function import_confirm() {
        self::guard( 'idh_aula_import_confirm' );
        $token  = sanitize_key( self::input( 'token', '' ) );
        $import = get_transient( 'idh_aula_import_' . $token );
        $page   = idh_aula_admin_url( 'idh-aula-importar' );

        if ( ! $import || (int) $import['user'] !== get_current_user_id() ) {
            IDH_Aula_Admin::flash( 'error', 'La vista previa expiró. Vuelve a subir el archivo.' );
            self::redirect( $page );
        }
        delete_transient( 'idh_aula_import_' . $token );

        $created  = 0;
        $enrolled = 0;
        $skipped  = 0;
        $codes    = [];
        foreach ( $import['rows'] as $row ) {
            if ( 'error' === $row['status'] ) {
                $skipped++;
                continue;
            }
            if ( 'new' === $row['status'] ) {
                // Revalida por si alguien registró el DNI entre la vista previa y la confirmación.
                $input = $row['data'];
                if ( ! empty( $row['foreign'] ) ) {
                    $input['is_foreign'] = 1;
                }
                list( $data, $errors ) = IDH_Aula_Students::sanitize( $input );
                if ( $errors ) {
                    $skipped++;
                    continue;
                }
                $student_id = IDH_Aula_Students::create( $data );
                $created++;
                if ( IDH_Aula_Documents::FOREIGN_TYPE === $data['doc_type'] ) {
                    $course_row = $row['course_id'] ? IDH_Aula_Courses::find( $row['course_id'] ) : null;
                    $codes[]    = [
                        'name'  => trim( $data['first_name'] . ' ' . $data['last_name'] ),
                        'code'  => IDH_Aula_Documents::display( $data['doc_number'], $data['doc_type'] ),
                        'birth' => $data['birth_date'],
                        'phone' => $data['phone'],
                        'email' => $data['email'],
                        'course' => $course_row ? $course_row->title : '',
                    ];
                }
            } else {
                $student_id = (int) $row['data']['id'];
            }
            if ( $row['course_id'] ) {
                list( , $was_created ) = IDH_Aula_Enrollments::enroll( $student_id, $row['course_id'], $import['payment'] );
                $enrolled += $was_created ? 1 : 0;
            }
        }

        IDH_Aula_Admin::flash(
            'success',
            sprintf( 'Importación completada: <strong>%d</strong> alumnos nuevos, <strong>%d</strong> matrículas creadas, %d filas omitidas por errores.', $created, $enrolled, $skipped )
        );
        if ( $codes ) {
            // Los códigos solo se conocen ahora: se muestran en una tabla descargable (también quedan en la ficha de cada alumno).
            set_transient( 'idh_aula_import_codes_' . $token, [ 'rows' => $codes, 'user' => get_current_user_id() ], HOUR_IN_SECONDS );
            self::redirect( idh_aula_admin_url( 'idh-aula-importar', [ 'codes' => $token ] ) );
        }
        self::redirect( idh_aula_admin_url( 'idh-aula-alumnos' ) );
    }

    public static function csv_template() {
        self::guard( 'idh_aula_csv_template' );
        IDH_Aula_CSV::download(
            'plantilla-alumnos-idh.csv',
            IDH_Aula_CSV::IMPORT_COLUMNS,
            [
                [ '12345678', 'María', 'García López', '15/03/1990', '987654321', 'maria@correo.com', '', '' ],
                [ '', 'John', 'Smith', '20/07/1985', '', 'john@correo.com', '', 'sí' ],
            ]
        );
    }

    // ─── Exportaciones ───

    public static function export() {
        self::guard( 'idh_aula_export' );
        $type = sanitize_key( $_GET['type'] ?? '' );

        if ( 'report' === $type ) {
            $range = IDH_Aula_Reports::resolve_period( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            $rows  = [];
            foreach ( IDH_Aula_Reports::by_course( $range['from'], $range['to'] ) as $row ) {
                $rows[] = [ $row->title, IDH_Aula_Categories::label( $row->category ), $row->total ];
            }
            $rows[] = [ 'TOTAL', '', IDH_Aula_Reports::total( $range['from'], $range['to'] ) ];
            IDH_Aula_CSV::download(
                'certificados-' . $range['from'] . '-a-' . $range['to'] . '.csv',
                [ 'Curso', 'Categoría', 'Certificados emitidos (' . $range['label'] . ')' ],
                $rows
            );
        }

        if ( 'enrollments' === $type ) {
            list( $items ) = IDH_Aula_Enrollments::search(
                [
                    'course_id'   => absint( $_GET['course_id'] ?? 0 ),
                    'payment'     => sanitize_key( $_GET['payment'] ?? '' ),
                    'locked'      => sanitize_text_field( wp_unslash( $_GET['locked'] ?? '' ) ),
                    'certificate' => sanitize_key( $_GET['certificate'] ?? '' ),
                    'search'      => sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ),
                    'per_page'    => 0,
                ]
            );
            $rows = [];
            foreach ( $items as $item ) {
                $rows[] = [
                    IDH_Aula_Documents::display( $item->doc_number ),
                    $item->first_name,
                    $item->last_name,
                    $item->phone,
                    $item->course_title,
                    IDH_Aula_Categories::label( $item->category ),
                    'paid' === $item->payment_status ? 'Pagado' : 'Pendiente',
                    number_format( (float) $item->price_paid, 2, '.', '' ),
                    $item->locked ? 'Sí' : 'No',
                    $item->certificate_id ? 'Sí' : 'No',
                    $item->issued_at ? idh_aula_format_date( $item->issued_at ) : '',
                    idh_aula_format_date( $item->enrolled_at ),
                ];
            }
            IDH_Aula_CSV::download(
                'matriculas-' . current_time( 'Y-m-d' ) . '.csv',
                [ 'DNI / Código', 'Nombres', 'Apellidos', 'Celular', 'Curso', 'Categoría', 'Pago', 'Precio (S/)', 'Bloqueado', 'Certificado', 'Fecha emisión', 'Fecha matrícula' ],
                $rows
            );
        }

        if ( 'foreign_codes' === $type ) {
            $codes = get_transient( 'idh_aula_import_codes_' . sanitize_key( $_GET['token'] ?? '' ) );
            if ( ! $codes || (int) $codes['user'] !== get_current_user_id() ) {
                wp_die( 'La lista de códigos expiró. Búscalos en la ficha de cada alumno.' );
            }
            $rows = [];
            foreach ( $codes['rows'] as $r ) {
                $rows[] = [ $r['name'], $r['code'], idh_aula_format_date( $r['birth'] ), $r['phone'], $r['email'], $r['course'] ];
            }
            IDH_Aula_CSV::download(
                'alumnos-extranjeros-' . current_time( 'Y-m-d' ) . '.csv',
                [ 'Alumno', 'Código de acceso', 'Fecha de nacimiento', 'Celular', 'Correo', 'Curso' ],
                $rows
            );
        }

        if ( 'students' === $type ) {
            $items = IDH_Aula_Students::search(
                [
                    'search'   => sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) ),
                    'status'   => sanitize_key( $_GET['status'] ?? '' ),
                    'per_page' => PHP_INT_MAX,
                ]
            );
            $rows = [];
            foreach ( $items as $item ) {
                $rows[] = [
                    IDH_Aula_Documents::display( $item->doc_number, $item->doc_type ),
                    $item->first_name,
                    $item->last_name,
                    idh_aula_format_date( $item->birth_date ),
                    $item->phone,
                    $item->email,
                    'active' === $item->status ? 'Activo' : 'Bloqueado',
                    $item->courses_count,
                    $item->pending_count,
                ];
            }
            IDH_Aula_CSV::download(
                'alumnos-' . current_time( 'Y-m-d' ) . '.csv',
                [ 'DNI / Código', 'Nombres', 'Apellidos', 'Fecha de nacimiento', 'Celular', 'Correo', 'Estado', 'Cursos', 'Pendientes de pago' ],
                $rows
            );
        }

        wp_die( 'Tipo de exportación no válido.' );
    }

    // ─── AJAX ───

    public static function ajax_search_resources() {
        self::ajax_guard();
        list( $items ) = IDH_Aula_Resources::search(
            [
                'search'   => sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ),
                'type'     => sanitize_key( $_GET['type'] ?? '' ),
                'per_page' => 50,
            ]
        );
        wp_send_json_success( array_map( [ 'IDH_Aula_Resources', 'to_json' ], $items ) );
    }

    public static function ajax_create_resource() {
        self::ajax_guard();
        list( $data, $errors ) = IDH_Aula_Resources::sanitize( (array) wp_unslash( $_POST['resource'] ?? [] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
        if ( $errors ) {
            wp_send_json_error( [ 'message' => implode( ' ', $errors ) ] );
        }
        $id = IDH_Aula_Resources::create( $data );
        wp_send_json_success( IDH_Aula_Resources::to_json( IDH_Aula_Resources::find( $id ) ) );
    }

    /**
     * Autocompletado de la matrícula individual: alumnos por DNI o nombre,
     * marcando los que ya están matriculados en el curso elegido.
     */
    public static function ajax_search_students() {
        self::ajax_guard();
        $q         = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
        $course_id = absint( $_GET['course_id'] ?? 0 );
        if ( mb_strlen( $q ) < 2 ) {
            wp_send_json_success( [] );
        }
        $items = IDH_Aula_Students::search( [ 'search' => $q, 'per_page' => 10, 'orderby' => 'last_name', 'order' => 'ASC' ] );
        wp_send_json_success(
            array_map(
                static function ( $s ) use ( $course_id ) {
                    return [
                        'id'       => (int) $s->id,
                        'doc'      => IDH_Aula_Documents::display( $s->doc_number, $s->doc_type ),
                        'name'     => IDH_Aula_Students::full_name( $s ),
                        'blocked'  => 'blocked' === $s->status,
                        'enrolled' => $course_id && IDH_Aula_Enrollments::find_by( $s->id, $course_id ),
                    ];
                },
                $items
            )
        );
    }

    public static function ajax_toggle_enrollment() {
        self::ajax_guard();
        $id    = absint( $_POST['id'] ?? 0 );
        $field = sanitize_key( $_POST['field'] ?? '' );
        $value = sanitize_key( $_POST['value'] ?? '' );
        if ( ! IDH_Aula_Enrollments::find( $id ) ) {
            wp_send_json_error( [ 'message' => 'Matrícula no encontrada.' ] );
        }
        if ( 'payment_status' === $field ) {
            IDH_Aula_Enrollments::update( $id, [ 'payment_status' => 'paid' === $value ? 'paid' : 'pending' ] );
        } elseif ( 'locked' === $field ) {
            IDH_Aula_Enrollments::update( $id, [ 'locked' => '1' === $value ? 1 : 0 ] );
        } else {
            wp_send_json_error( [ 'message' => 'Campo no válido.' ] );
        }
        wp_send_json_success( [ 'enrollment' => IDH_Aula_Enrollments::find( $id ) ] );
    }
}
