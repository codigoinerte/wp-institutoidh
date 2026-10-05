<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Íconos SVG del aula (mismo trazo que el prototipo).
 */
function idh_aula_icon( $name, $size = 18 ) {
    $paths = [
        'play'     => '<circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor" stroke="none"/>',
        'folder'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'link'     => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'award'    => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        'chevron'  => '<polyline points="9 18 15 12 9 6"/>',
        'back'     => '<polyline points="15 18 9 12 15 6"/>',
        'lock'     => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'copy'     => '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'clock'    => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'book'     => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'close'    => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'alert'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ];
    $stroke = in_array( $name, [ 'chevron', 'back', 'close' ], true ) ? 2 : 1.75;
    return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $stroke . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/**
 * Tooltip HTML personalizado. Se abre con hover, foco (teclado) o toque (móvil, vía aula.js).
 *
 * @param string $trigger Contenido HTML ya escapado que dispara el tooltip.
 * @param string $title   Título del tooltip.
 * @param string $text    Texto explicativo.
 * @param string $class   Clases extra del contenedor (ej. 'has-tip--block').
 */
function idh_aula_tooltip( $trigger, $title, $text, $class = '' ) {
    static $n = 0;
    $id = 'aula-tip-' . ( ++$n );
    return '<span class="has-tip ' . esc_attr( $class ) . '" tabindex="0" role="button" aria-describedby="' . esc_attr( $id ) . '">'
        . $trigger
        . '<span class="tip" role="tooltip" id="' . esc_attr( $id ) . '">'
        . '<span class="tip-title">' . esc_html( $title ) . '</span>'
        . '<span class="tip-text">' . esc_html( $text ) . '</span>'
        . '</span></span>';
}

/**
 * Tag "Pago pendiente" con tooltip explicativo.
 */
function idh_aula_pending_tag() {
    return idh_aula_tooltip(
        '<span class="badge badge-warning badge-pending">' . idh_aula_icon( 'clock', 13 ) . 'Pago pendiente</span>',
        'Tienes un pago pendiente',
        'Puedes ver tus clases y materiales, pero tu certificado se habilitará cuando completes el pago. Si ya pagaste, escríbenos para actualizar tu estado.'
    );
}
