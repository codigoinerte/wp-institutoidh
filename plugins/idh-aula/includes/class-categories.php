<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reutiliza las "Categorías de portada" del child theme (Apariencia → Categorías de Portada).
 */
class IDH_Aula_Categories {

    public static function all() {
        if ( function_exists( 'kadence_child_get_cover_categories' ) ) {
            return kadence_child_get_cover_categories();
        }
        return [
            [ 'label' => 'Curso taller', 'value' => 'curso-taller', 'color' => '#7c3aed' ],
            [ 'label' => 'Curso de especialización', 'value' => 'curso-especializacion', 'color' => '#1f7a8c' ],
            [ 'label' => 'Webinar', 'value' => 'webinar', 'color' => '#d97706' ],
            [ 'label' => 'Seminario', 'value' => 'seminario', 'color' => '#2563eb' ],
        ];
    }

    public static function options() {
        $options = [];
        foreach ( self::all() as $category ) {
            $options[ $category['value'] ] = $category['label'];
        }
        return $options;
    }

    public static function get( $value ) {
        foreach ( self::all() as $category ) {
            if ( $category['value'] === $value ) {
                return $category;
            }
        }
        return null;
    }

    public static function label( $value ) {
        $category = self::get( $value );
        return $category ? $category['label'] : ( $value ? ucfirst( str_replace( '-', ' ', $value ) ) : 'Sin categoría' );
    }

    public static function color( $value ) {
        $category = self::get( $value );
        return $category ? $category['color'] : '#7C3AED';
    }

    public static function manage_url() {
        return admin_url( 'themes.php?page=kadence-child-cover-categories' );
    }
}
