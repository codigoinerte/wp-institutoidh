<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Identificación del alumno.
 *
 * - DNI: alumnos peruanos. El número de documento es su usuario de acceso.
 * - EXT: alumnos extranjeros. No se guarda su documento; se les genera un código interno aleatorio
 *   (EXT-XXXX-XXXX) que, junto con su fecha de nacimiento, es su acceso al aula.
 *
 * En ambos casos el identificador vive en students.doc_number y la clave única es (doc_type, doc_number).
 * El tipo se deduce del propio valor (8 dígitos = DNI, EXT + 8 caracteres = código), así que el login
 * no necesita pedirlo.
 */
class IDH_Aula_Documents {

    const DEFAULT_TYPE = 'DNI';
    const FOREIGN_TYPE = 'EXT';

    // Sin 0, 1, I ni O para que el código se pueda dictar o copiar sin confusiones.
    const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const CODE_LENGTH   = 8;

    const TYPES = [
        'DNI' => [ 'label' => 'DNI', 'pattern' => '/^\d{8}$/', 'hint' => '8 dígitos' ],
        'EXT' => [ 'label' => 'Código de alumno', 'pattern' => '/^EXT[A-HJ-NP-Z2-9]{8}$/', 'hint' => 'EXT seguido de 8 caracteres' ],
    ];

    public static function normalize( $number ) {
        return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $number ) );
    }

    /**
     * Tipo según el formato del valor: 8 dígitos es un DNI; cualquier otra cosa se trata como código.
     */
    public static function type_for( $number ) {
        return preg_match( '/^\d{8}$/', self::normalize( $number ) ) ? self::DEFAULT_TYPE : self::FOREIGN_TYPE;
    }

    public static function is_valid( $number, $type = self::DEFAULT_TYPE ) {
        if ( ! isset( self::TYPES[ $type ] ) ) {
            return false;
        }
        return (bool) preg_match( self::TYPES[ $type ]['pattern'], self::normalize( $number ) );
    }

    public static function hint( $type = self::DEFAULT_TYPE ) {
        return self::TYPES[ $type ]['hint'] ?? '';
    }

    public static function label( $type = self::DEFAULT_TYPE ) {
        return self::TYPES[ $type ]['label'] ?? self::DEFAULT_TYPE;
    }

    /**
     * Valor para mostrar: el DNI tal cual y el código con guiones (EXT-K7M2-QX9R).
     */
    public static function display( $number, $type = null ) {
        $number = self::normalize( $number );
        $type   = $type ?: self::type_for( $number );
        if ( self::FOREIGN_TYPE === $type && 11 === strlen( $number ) ) {
            return substr( $number, 0, 3 ) . '-' . substr( $number, 3, 4 ) . '-' . substr( $number, 7 );
        }
        return $number;
    }

    /**
     * Código único nuevo para un alumno extranjero (se guarda normalizado, sin guiones).
     */
    public static function generate_code() {
        $max = strlen( self::CODE_ALPHABET ) - 1;
        do {
            $code = self::FOREIGN_TYPE;
            for ( $i = 0; $i < self::CODE_LENGTH; $i++ ) {
                $code .= self::CODE_ALPHABET[ random_int( 0, $max ) ];
            }
        } while ( IDH_Aula_Students::find_by_doc( $code, self::FOREIGN_TYPE ) );
        return $code;
    }
}
