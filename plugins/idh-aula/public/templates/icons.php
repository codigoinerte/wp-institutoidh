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
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'alert'    =>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
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

/**
 * Logo de WhatsApp (el mismo del botón flotante de las landings del theme).
 */
function idh_aula_whatsapp_icon( $size = 20 ) {
    return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 640 640" fill="currentColor" aria-hidden="true"><path d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z"/></svg>';
}

/**
 * Botón verde "Escribir por WhatsApp" con el mensaje según el caso. Vacío si no hay número configurado.
 */
function idh_aula_whatsapp_button( $case, $student = null, $course = '', $label = 'Escribir a IDH por WhatsApp', $class = '' ) {
    $url = idh_aula_whatsapp_url( $case, $student, $course );
    if ( ! $url ) {
        return '';
    }
    return '<a class="btn-whatsapp ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . idh_aula_whatsapp_icon( 22 ) . '<span>' . esc_html( $label ) . '</span></a>';
}
