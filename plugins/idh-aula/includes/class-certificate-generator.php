<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Certificados generados por la plataforma (modo 'generated', fase 2).
 *
 * Al habilitar un certificado se congela su contenido (snapshot JSON). El PDF no se guarda:
 * se genera al descargarlo con ese contenido, así un dato corregido después no cambia
 * un certificado ya emitido, pero un ajuste de diseño sí se aplica a todos.
 */
class IDH_Aula_Certificate_Generator {

    /** Plantillas elegibles en la ficha del curso ('' = según la categoría). */
    const TEMPLATE_CHOICES = [
        ''                => 'Según la categoría',
        'taller'          => 'Azul (taller, seminario, webinar)',
        'especializacion' => 'Negro y dorado (especialización)',
    ];

    /**
     * Datos de contacto de IDH que muestra el verificador público (los mismos de su página de qr.io).
     * Las redes sin enlace no se muestran.
     */
    const ORG = [
        'name'      => 'Instituto Desarrollo de Habilidades Profesionales',
        'phone'     => '+51 947227702',
        'email'     => 'idhp.capacitaciones@gmail.com',
        'website'   => 'https://www.idh.com.pe',
        'location'  => 'Lima, Lima, Lima, Peru',
        'facebook'  => 'https://www.facebook.com/profile.php?id=61559437738012',
        'instagram' => 'https://www.instagram.com/idh.cursos.vir/',
    ];

    const MONTHS = [ 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ];

    /**
     * Plantilla del curso: la elegida en su ficha o, si no, la de su categoría.
     */
    public static function template_for( $course ) {
        $chosen = (string) ( $course->certificate_template ?? '' );
        if ( '' !== $chosen && isset( IDH_Aula_Certificate_PDF::TEMPLATES[ $chosen ] ) ) {
            return $chosen;
        }
        // «curso» es la categoría histórica que ahora se llama «Curso de Especialización».
        return in_array( $course->category ?? '', [ 'curso-especializacion', 'curso' ], true ) ? 'especializacion' : 'taller';
    }

    /**
     * Firmantes por defecto. Las imágenes van como rutas relativas al plugin para que el snapshot
     * no dependa del servidor. (Los firmantes configurables en Ajustes vienen en un paso posterior.)
     */
    public static function default_signers( $template ) {
        $dir = 'especializacion' === $template ? 'especializacion' : 'azul';
        return [
            [
                'name'      => 'Erick Núñez Vitor',
                'role'      => 'Gerente General',
                'signature' => "assets/certificados/{$dir}/firma.png",
                'seal'      => "assets/certificados/{$dir}/sello.png",
            ],
        ];
    }

    /**
     * Contenido congelado del certificado general (session_id = 0) o de una clase.
     *
     * @return array|WP_Error
     */
    public static function snapshot( $enrollment_id, $session_id = 0 ) {
        $enrollment = IDH_Aula_Enrollments::find( $enrollment_id );
        $student    = $enrollment ? IDH_Aula_Students::find( $enrollment->student_id ) : null;
        $course     = $enrollment ? IDH_Aula_Courses::find( $enrollment->course_id ) : null;
        if ( ! $enrollment || ! $student || ! $course ) {
            return new WP_Error( 'missing', 'No se encontró la matrícula, el alumno o el curso.' );
        }

        $template = self::template_for( $course );
        $sessions = self::sessions( $course->id );
        $snap     = [
            'version'      => 1,
            'kind'         => $session_id ? 'session' : 'course',
            'template'     => $template,
            'student_name' => trim( $student->first_name . ' ' . $student->last_name ),
            'doc_type'     => $student->doc_type,
            'doc_number'   => $student->doc_number,
            'course_id'    => (int) $course->id,
            'course_title' => $course->title,
            'course_type'  => self::course_type( $course ),
            'hours'        => self::hours_number( $course->hours ),
            'signers'      => self::default_signers( $template ),
            'frozen_at'    => idh_aula_now(),
            'topics_size'  => min( 16, max( 8, (int) ( $course->certificate_font_size ?? 12 ) ?: 12 ) ),
        ];

        if ( $session_id ) {
            $session = $sessions[ (int) $session_id ] ?? null;
            if ( ! $session ) {
                return new WP_Error( 'missing', 'La clase ya no existe en el curso.' );
            }
            $snap['session_id']    = (int) $session->id;
            $snap['session_title'] = $session->title;
            $snap['period']        = $session->session_datetime ? 'el ' . self::long_date( $session->session_datetime ) : '';
            $snap['topics']        = self::lines_as_bullets( (string) $session->description );
            return $snap;
        }

        // Período: de las clases incluidas en la matrícula (todas si no es parcial); si no tienen fecha, la de inicio.
        $allowed = IDH_Aula_Enrollments::allowed_session_ids( $enrollment );
        $dates   = [];
        foreach ( $sessions as $session ) {
            if ( $session->session_datetime && ( null === $allowed || in_array( (int) $session->id, $allowed, true ) ) ) {
                $dates[] = $session->session_datetime;
            }
        }
        if ( ! $dates && $course->start_date ) {
            $dates[] = $course->start_date;
        }
        $snap['period'] = self::period_text( $dates );

        // Reverso: el contenido escrito en el curso; si está vacío, los títulos de las clases.
        $content = trim( (string) ( $course->certificate_content ?? '' ) );
        if ( '' !== $content ) {
            $snap['topics'] = self::topics_from_content( $content );
        } else {
            $snap['topics'] = [];
            foreach ( $sessions as $session ) {
                $snap['topics'][] = '- ' . $session->title;
            }
        }
        return $snap;
    }

    /**
     * «durante el mes de octubre de 2026» si todo cae en un mes; si no, «del 21 de noviembre de 2025 al 09 de enero de 2026».
     */
    public static function period_text( array $dates ) {
        $dates = array_filter( array_map( 'strtotime', $dates ) );
        if ( ! $dates ) {
            return '';
        }
        $from = min( $dates );
        $to   = max( $dates );
        if ( gmdate( 'Y-m', $from ) === gmdate( 'Y-m', $to ) ) {
            return 'durante el mes de ' . self::MONTHS[ (int) gmdate( 'n', $from ) - 1 ] . ' de ' . gmdate( 'Y', $from );
        }
        return 'del ' . self::long_date( gmdate( 'Y-m-d', $from ) ) . ' al ' . self::long_date( gmdate( 'Y-m-d', $to ) );
    }

    public static function long_date( $date ) {
        $ts = strtotime( $date );
        return gmdate( 'd', $ts ) . ' de ' . self::MONTHS[ (int) gmdate( 'n', $ts ) - 1 ] . ' de ' . gmdate( 'Y', $ts );
    }

    /**
     * Genera el PDF de un certificado habilitado a partir de su snapshot.
     *
     * @return string|WP_Error Binario del PDF.
     */
    public static function render( $cert ) {
        $snap = self::decode( $cert );
        if ( ! $snap ) {
            return new WP_Error( 'snapshot', 'El certificado no tiene contenido congelado.' );
        }
        return self::render_snapshot( $snap, $cert->code );
    }

    /**
     * Genera el PDF de un snapshot (también lo usa la vista previa del admin, sin guardar nada).
     */
    public static function render_snapshot( array $snap, $code ) {
        $data            = $snap;
        $data['signers'] = [];
        foreach ( (array) ( $snap['signers'] ?? [] ) as $signer ) {
            foreach ( [ 'signature', 'seal' ] as $key ) {
                $signer[ $key ] = self::asset_path( $signer[ $key ] ?? '' );
            }
            $data['signers'][] = $signer;
        }
        $data['verify_url'] = self::verify_url( $code );

        try {
            return IDH_Aula_Certificate_PDF::render( $data, $snap['template'] ?? 'taller' );
        } catch ( \Throwable $e ) {
            return new WP_Error( 'render', 'No se pudo generar el PDF: ' . $e->getMessage() );
        }
    }

    /**
     * Texto del certificado en una sola frase (el mismo del PDF), para el verificador público.
     */
    public static function sentence( array $snap ) {
        $period = (string) ( $snap['period'] ?? '' );
        $hours  = (string) ( $snap['hours'] ?? '' );
        $course = '“' . ( $snap['course_title'] ?? '' ) . '”';
        $dur    = '' !== $hours ? ', con una duración de ' . $hours . ' horas académicas' : '';
        list( $type_art, $type ) = IDH_Aula_Certificate_PDF::type_phrase( (string) ( $snap['course_type'] ?? '' ) );

        if ( 'session' === ( $snap['kind'] ?? '' ) ) {
            $of   = '' !== $type ? ( 'la' === $type_art ? 'de la ' : 'del ' ) . $type : 'del';
            $text = 'Por su participación en la clase “' . ( $snap['session_title'] ?? '' ) . '” ' . $of . ' ' . $course
                . ', desarrollada en modalidad virtual' . ( '' !== $period ? ' ' . $period : '' ) . '.';
        } elseif ( 'especializacion' === ( $snap['template'] ?? '' ) ) {
            $text = 'Por su participación en ' . ( 'la' === $type_art ? 'nuestra' : 'nuestro' ) . ' ' . ( $snap['course_type'] ?? 'Curso' ) . ' ' . $course
                . ', realizado de manera virtual' . $dur . ( '' !== $period ? ', ' . $period : '' ) . '.';
        } else {
            $text = 'Por su participación en ' . ( '' !== $type ? $type_art . ' ' . $type : 'el' ) . ' ' . $course . ', desarrollado en modalidad virtual'
                . ( '' !== $dur ? $dur . ( '' !== $period ? ', ' . $period : '' ) : ( '' !== $period ? ' ' . $period : '' ) ) . '.';
        }
        return trim( ( $snap['student_name'] ?? '' ) . '. ' . $text );
    }

    public static function decode( $cert ) {
        $snap = $cert && ! empty( $cert->snapshot ) ? json_decode( $cert->snapshot, true ) : null;
        return is_array( $snap ) ? $snap : null;
    }

    /**
     * URL pública del verificador (la del QR). Corta, para que el QR sea menos denso.
     */
    public static function verify_url( $code ) {
        return home_url( 'verificar/' . rawurlencode( $code ) );
    }

    public static function filename( array $snap ) {
        $name = sanitize_file_name( remove_accents( $snap['student_name'] ?? 'certificado' ) );
        return 'Certificado-' . ( $name ?: 'IDH' ) . '.pdf';
    }

    /** Envía el PDF al navegador (inline, o como descarga con ?descargar). */
    public static function send( $pdf, array $snap ) {
        $inline = ! isset( $_GET['descargar'] ); // phpcs:ignore WordPress.Security.NonceVerification
        header( 'Content-Type: application/pdf' );
        header( 'Content-Length: ' . strlen( $pdf ) );
        header( 'Content-Disposition: ' . ( $inline ? 'inline' : 'attachment' ) . '; filename="' . self::filename( $snap ) . '"' );
        header( 'X-Content-Type-Options: nosniff' );
        echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    /** Documento enmascarado para la página pública: «DNI ****6789». */
    public static function masked_document( $doc_type, $doc_number ) {
        $number = IDH_Aula_Documents::normalize( $doc_number );
        $label  = IDH_Aula_Documents::label( $doc_type ?: IDH_Aula_Documents::type_for( $number ) );
        $tail   = substr( $number, -4 );
        return $label . ' ' . str_repeat( '*', max( 0, strlen( $number ) - 4 ) ) . $tail;
    }

    // ─── Internos ───

    /** Clases del curso con descripción, indexadas por id, en el orden del curso. */
    private static function sessions( $course_id ) {
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, title, description, session_datetime FROM ' . idh_aula_table( 'sessions' ) . ' WHERE course_id = %d ORDER BY position ASC, id ASC',
                $course_id
            )
        );
        $out = [];
        foreach ( $rows as $row ) {
            $out[ (int) $row->id ] = $row;
        }
        return $out;
    }

    /** «Curso de Especialización», «Curso Taller»… desde la categoría. */
    private static function course_type( $course ) {
        $label = $course->category ? IDH_Aula_Categories::label( $course->category ) : '';
        return $label ?: 'Curso';
    }

    /** Etiquetas permitidas en el contenido del reverso: sin color, tamaño ni fuente. */
    const CONTENT_TAGS = [
        'p'      => [],
        'br'     => [],
        'strong' => [],
        'b'      => [],
        'ul'     => [],
        'ol'     => [],
        'li'     => [],
        'a'      => [ 'href' => true ],
    ];

    /** ¿El contenido del reverso es HTML del editor (y no el texto plano de antes)? */
    public static function content_is_html( $content ) {
        return (bool) preg_match( '/<\/?(p|ul|ol|li|br|strong|b|a)\b/i', (string) $content );
    }

    /**
     * Limpia el contenido del reverso al guardar el curso. Texto plano (formato anterior): se deja como estaba.
     * HTML del editor: solo pasan párrafos, listas, negrita, saltos de línea y enlaces http(s)/mailto, sin atributos de estilo.
     */
    public static function sanitize_content( $raw ) {
        $raw = (string) $raw;
        if ( ! self::content_is_html( $raw ) ) {
            return sanitize_textarea_field( $raw );
        }
        $raw   = preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', '', $raw );
        $clean = wp_kses( $raw, self::CONTENT_TAGS, [ 'http', 'https', 'mailto' ] );
        $clean = str_replace( [ "\r", "\xc2\xa0", '&nbsp;' ], [ '', ' ', ' ' ], $clean );
        // El editor deja párrafos vacíos entre bloques: no aportan nada al certificado.
        $clean = preg_replace( '#<p>\s*(<br\s*/?>)?\s*</p>#i', '', $clean );
        return trim( (string) $clean );
    }

    /** Contenido para cargar en el editor: el HTML tal cual, o el texto plano antiguo convertido (líneas con «-» → lista). */
    public static function content_to_html( $content ) {
        $content = trim( (string) $content );
        if ( '' === $content || self::content_is_html( $content ) ) {
            return $content;
        }
        $html = '';
        $open = false;
        foreach ( preg_split( '/\R/', $content ) as $line ) {
            $line = trim( $line );
            if ( '' === $line ) {
                continue;
            }
            if ( 0 === strpos( $line, '-' ) ) {
                $html .= $open ? '' : '<ul>';
                $open  = true;
                $html .= '<li>' . esc_html( ltrim( substr( $line, 1 ) ) ) . '</li>';
            } else {
                $html .= $open ? '</ul>' : '';
                $open  = false;
                $html .= '<p>' . esc_html( $line ) . '</p>';
            }
        }
        return $html . ( $open ? '</ul>' : '' );
    }

    /**
     * Líneas del reverso para el snapshot. Texto plano: una cadena por línea (con «- » = viñeta). HTML del editor: filas
     * ['type' => 'module'|'item', 'html' => HTML inline seguro, 'marker' => '1.' si es lista numerada].
     */
    public static function topics_from_content( $content ) {
        $content = trim( (string) $content );
        if ( ! self::content_is_html( $content ) ) {
            return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $content ) ), 'strlen' ) );
        }
        if ( ! class_exists( 'DOMDocument' ) ) {
            return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', wp_strip_all_tags( str_replace( [ '</p>', '</li>', '<br>' ], "\n", $content ) ) ) ), 'strlen' ) );
        }
        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="utf-8"?><body>' . $content . '</body>' );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev );
        $body = $dom->getElementsByTagName( 'body' )->item( 0 );

        $rows = [];
        if ( $body ) {
            self::collect_rows( $body, $rows );
        }
        return $rows;
    }

    private static function collect_rows( DOMNode $parent, array &$rows ) {
        $loose = '';
        $flush = static function () use ( &$loose, &$rows ) {
            if ( '' !== trim( strip_tags( $loose ) ) ) {
                $rows[] = [ 'type' => 'module', 'html' => trim( $loose ) ];
            }
            $loose = '';
        };
        foreach ( $parent->childNodes as $node ) {
            $name = strtolower( $node->nodeName );
            if ( 'p' === $name ) {
                $flush();
                $html = self::inline_html( $node );
                if ( '' !== trim( strip_tags( $html ) ) ) {
                    $rows[] = [ 'type' => 'module', 'html' => trim( $html ) ];
                }
            } elseif ( 'ul' === $name || 'ol' === $name ) {
                $flush();
                self::collect_items( $node, 'ol' === $name, $rows );
            } else {
                $loose .= self::inline_html( $node, true );
            }
        }
        $flush();
    }

    /** Elementos de una lista; las sublistas se aplanan (el diseño tiene un solo nivel de viñetas). */
    private static function collect_items( DOMNode $list, $ordered, array &$rows ) {
        $n = 0;
        foreach ( $list->childNodes as $li ) {
            if ( 'li' !== strtolower( $li->nodeName ) ) {
                continue;
            }
            $n++;
            $html = self::inline_html( $li );
            if ( '' !== trim( strip_tags( $html ) ) ) {
                $row = [ 'type' => 'item', 'html' => trim( $html ) ];
                if ( $ordered ) {
                    $row['marker'] = $n . '.';
                }
                $rows[] = $row;
            }
            foreach ( $li->childNodes as $child ) {
                $cn = strtolower( $child->nodeName );
                if ( 'ul' === $cn || 'ol' === $cn ) {
                    self::collect_items( $child, 'ol' === $cn, $rows );
                }
            }
        }
    }

    /** HTML inline seguro de un nodo: texto escapado, negrita, saltos de línea y enlaces. Las sublistas se saltan. */
    private static function inline_html( DOMNode $node, $self = false ) {
        $out   = '';
        $nodes = $self ? [ $node ] : iterator_to_array( $node->childNodes );
        foreach ( $nodes as $child ) {
            if ( XML_TEXT_NODE === $child->nodeType ) {
                $out .= esc_html( preg_replace( '/\s+/u', ' ', $child->nodeValue ) );
                continue;
            }
            if ( XML_ELEMENT_NODE !== $child->nodeType ) {
                continue;
            }
            $tag = strtolower( $child->nodeName );
            if ( 'ul' === $tag || 'ol' === $tag ) {
                continue;
            }
            $inner = self::inline_html( $child );
            if ( 'strong' === $tag || 'b' === $tag ) {
                $out .= '' !== trim( $inner ) ? '<b>' . $inner . '</b>' : $inner;
            } elseif ( 'br' === $tag ) {
                $out .= '<br>';
            } elseif ( 'a' === $tag ) {
                $href = esc_url( $child->getAttribute( 'href' ), [ 'http', 'https', 'mailto' ] );
                $out .= '' !== $href ? '<a href="' . esc_attr( $href ) . '">' . $inner . '</a>' : $inner;
            } else {
                $out .= $inner;
            }
        }
        return $out;
    }

    /** Las horas del curso son texto libre («200 hrs. académicas»): el certificado usa solo el número. */
    private static function hours_number( $hours ) {
        return preg_match( '/\d+(?:[.,]\d+)?/', (string) $hours, $m ) ? $m[0] : '';
    }

    private static function lines_as_bullets( $text ) {
        $out = [];
        foreach ( preg_split( '/\R/', $text ) as $line ) {
            $line = trim( $line );
            if ( '' !== $line ) {
                $out[] = 0 === strpos( $line, '-' ) ? $line : '- ' . $line;
            }
        }
        return $out;
    }

    /** Ruta absoluta de una imagen del snapshot, solo si está dentro del plugin. */
    private static function asset_path( $relative ) {
        if ( '' === (string) $relative ) {
            return '';
        }
        $path = realpath( IDH_AULA_DIR . ltrim( $relative, '/' ) );
        return $path && 0 === strpos( $path, realpath( IDH_AULA_DIR ) ) && is_file( $path ) ? $path : '';
    }
}
