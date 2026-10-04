<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_CSV {

    const IMPORT_COLUMNS = [ 'dni', 'nombres', 'apellidos', 'fecha_nacimiento', 'celular', 'correo', 'curso_id' ];

    /**
     * Envía un CSV al navegador (UTF-8 con BOM para que Excel muestre bien las tildes) y termina.
     */
    public static function download( $filename, array $header, array $rows ) {
        nocache_headers();
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
        $out = fopen( 'php://output', 'w' );
        fwrite( $out, "\xEF\xBB\xBF" );
        fputcsv( $out, $header );
        foreach ( $rows as $row ) {
            fputcsv( $out, array_map( [ __CLASS__, 'cell' ], $row ) );
        }
        fclose( $out );
        exit;
    }

    /**
     * Evita inyección de fórmulas al abrir el CSV en Excel.
     */
    private static function cell( $value ) {
        $value = (string) $value;
        if ( '' !== $value && in_array( $value[0], [ '=', '+', '-', '@' ], true ) && ! is_numeric( $value ) ) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * Lee un CSV subido. Detecta separador (, o ;) y codificación. Devuelve filas asociativas por encabezado normalizado.
     */
    public static function parse( $path ) {
        $content = file_get_contents( $path );
        if ( false === $content ) {
            return new WP_Error( 'read', 'No se pudo leer el archivo.' );
        }
        $content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );
        if ( ! mb_check_encoding( $content, 'UTF-8' ) ) {
            $content = mb_convert_encoding( $content, 'UTF-8', 'Windows-1252' );
        }
        $lines = preg_split( '/\r\n|\r|\n/', trim( $content ) );
        if ( count( $lines ) < 2 ) {
            return new WP_Error( 'empty', 'El archivo no tiene filas de datos.' );
        }
        $delimiter = substr_count( $lines[0], ';' ) > substr_count( $lines[0], ',' ) ? ';' : ',';
        $header    = array_map( [ __CLASS__, 'normalize_header' ], str_getcsv( array_shift( $lines ), $delimiter ) );

        if ( ! in_array( 'dni', $header, true ) ) {
            return new WP_Error( 'header', 'El archivo debe tener una columna "dni". Descarga la plantilla para ver el formato.' );
        }

        $rows = [];
        foreach ( $lines as $index => $line ) {
            if ( '' === trim( $line, " \t" . $delimiter ) ) {
                continue;
            }
            $values = str_getcsv( $line, $delimiter );
            $row    = [ '_line' => $index + 2 ];
            foreach ( $header as $i => $key ) {
                $row[ $key ] = trim( $values[ $i ] ?? '' );
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private static function normalize_header( $value ) {
        $value = strtolower( remove_accents( trim( $value ) ) );
        $value = preg_replace( '/[^a-z0-9]+/', '_', $value );
        $map   = [
            'documento'        => 'dni',
            'nro_documento'    => 'dni',
            'nombre'           => 'nombres',
            'apellido'         => 'apellidos',
            'fecha_de_nacimiento' => 'fecha_nacimiento',
            'nacimiento'       => 'fecha_nacimiento',
            'telefono'         => 'celular',
            'email'            => 'correo',
            'curso'            => 'curso_id',
        ];
        $value = trim( $value, '_' );
        return $map[ $value ] ?? $value;
    }
}
