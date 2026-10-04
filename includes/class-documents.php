<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tipos de documento de identidad.
 *
 * Fase 1: solo DNI. Para habilitar CE / Pasaporte (ver docs/PLAN.md, "Plan de migración")
 * basta con añadirlos a TYPES; la tabla ya guarda doc_type y la clave única es (doc_type, doc_number).
 */
class IDH_Aula_Documents {

    const DEFAULT_TYPE = 'DNI';

    const TYPES = [
        'DNI' => [ 'label' => 'DNI', 'pattern' => '/^\d{8}$/', 'hint' => '8 dígitos' ],
        // 'CE'        => [ 'label' => 'Carnet de extranjería', 'pattern' => '/^[A-Z0-9]{9,12}$/', 'hint' => '9 a 12 caracteres' ],
        // 'PASAPORTE' => [ 'label' => 'Pasaporte', 'pattern' => '/^[A-Z0-9]{6,12}$/', 'hint' => '6 a 12 caracteres' ],
    ];

    public static function normalize( $number ) {
        return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $number ) );
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
}
