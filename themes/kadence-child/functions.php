<?php

// ============================================
// CONFIGURACIÓN DE VERSIONES DE ASSETS
// ============================================

// Versión general del tema - Cambiar aquí para actualizar todos los assets
define('KADENCE_CHILD_VERSION', '1.0.1');

/**
 * Obtiene la versión para un asset (CSS o JS)
 * 
 * @param string $file_path Ruta relativa del archivo desde la carpeta del tema
 * @param bool $use_filemtime Si es true, usa la fecha de modificación del archivo como versión (cache busting automático)
 * @return string|null Versión del asset o null
 */
function kadence_child_get_asset_version($file_path = '', $use_filemtime = true) {
    if (empty($file_path)) {
        return KADENCE_CHILD_VERSION;
    }
    
    if ($use_filemtime) {
        $full_path = get_stylesheet_directory() . $file_path;
        if (file_exists($full_path)) {
            return filemtime($full_path);
        }
    }
    
    return KADENCE_CHILD_VERSION;
}

// ============================================
// ENQUEUE DE ESTILOS Y SCRIPTS
// ============================================

function kadence_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles' );

function cargar_estilos_personalizados() {
    if ( is_page_template('template-1.php') ) {
        // Estilos externos (CDN) - sin versión para usar la del CDN
        wp_enqueue_style( 'template-1-animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), null );
        
        // Estilos locales - con versión automática basada en fecha de modificación
        wp_enqueue_style( 'global.css', get_stylesheet_directory_uri() . '/assets/global/css/global.css', array(), kadence_child_get_asset_version('/assets/global/css/global.css') );
        wp_enqueue_style( 'bootstrap-css', get_stylesheet_directory_uri() . '/assets/template-1/css/bootstrap.min.css', array(), kadence_child_get_asset_version('/assets/template-1/css/bootstrap.min.css') );
        wp_enqueue_style( 'lineicons-css', get_stylesheet_directory_uri() . '/assets/template-1/css/lineicons.css', array(), kadence_child_get_asset_version('/assets/template-1/css/lineicons.css') );
        wp_enqueue_style( 'tiny-slider-css', get_stylesheet_directory_uri() . '/assets/template-1/css/tiny-slider.css', array(), kadence_child_get_asset_version('/assets/template-1/css/tiny-slider.css') );
        wp_enqueue_style( 'glightbox-css', get_stylesheet_directory_uri() . '/assets/template-1/css/glightbox.min.css', array(), kadence_child_get_asset_version('/assets/template-1/css/glightbox.min.css') );
        wp_enqueue_style( 'template-1-style', get_stylesheet_directory_uri() . '/assets/template-1/css/style.css', array(), kadence_child_get_asset_version('/assets/template-1/css/style.css') );
    }

    if ( is_page_template('template-2.php') ) {
        // Estilos externos (CDN) - sin versión
        wp_enqueue_style( 'template-2-animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), null );
        wp_enqueue_style( 'template-2-google-fonts', 'https://fonts.googleapis.com/css?family=Poppins:300,400,500,700|Roboto+Condensed:300,400,500,700|Roboto:300,400,500,700', array(), null );
        
        // Estilos locales - con versión automática
        wp_enqueue_style( 'global.css', get_stylesheet_directory_uri() . '/assets/global/css/global.css', array(), kadence_child_get_asset_version('/assets/global/css/global.css') );
        wp_enqueue_style( 'all.min.css', get_stylesheet_directory_uri() . '/assets/template-2/css/all.min.css', array(), kadence_child_get_asset_version('/assets/template-2/css/all.min.css') );
        wp_enqueue_style( 'style.css', get_stylesheet_directory_uri() . '/assets/template-2/css/style.css', array(), kadence_child_get_asset_version('/assets/template-2/css/style.css') );
        wp_enqueue_style( 'template-2.css', get_stylesheet_directory_uri() . '/assets/template-2/css/template-2.css', array(), kadence_child_get_asset_version('/assets/template-2/css/template-2.css') );
    }

    if ( is_page_template('template-3.php') ) {
        // Estilos externos (CDN) - sin versión
        wp_enqueue_style( 'template-3-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );
        wp_enqueue_style( 'template-3-animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), null );
        
        // Estilos locales - con versión automática
        wp_enqueue_style( 'global.css', get_stylesheet_directory_uri() . '/assets/global/css/global.css', array(), kadence_child_get_asset_version('/assets/global/css/global.css') );
        wp_enqueue_style( 'template-3.css', get_stylesheet_directory_uri() . '/assets/template-3/css/template-3.css', array(), kadence_child_get_asset_version('/assets/template-3/css/template-3.css') );
        wp_enqueue_style( 'style.css', get_stylesheet_directory_uri() . '/assets/template-3/css/style.css', array(), kadence_child_get_asset_version('/assets/template-3/css/style.css') );
    }

    if ( is_page_template('home.php') ) {
        // Estilos locales - con versión automática
        wp_enqueue_style( 'bootstrap-icons.min.css', get_stylesheet_directory_uri() . '/assets/home/css/bootstrap-icons.min.css', array(), kadence_child_get_asset_version('/assets/home/css/bootstrap-icons.min.css') );
        wp_enqueue_style( 'home-style', get_stylesheet_directory_uri() . '/assets/home/css/style.css', array(), kadence_child_get_asset_version('/assets/home/css/style.css') );
        wp_enqueue_style( 'font-awesome.min.css', get_stylesheet_directory_uri() . '/assets/home/css/font-awesome.min.css', array(), kadence_child_get_asset_version('/assets/home/css/font-awesome.min.css') );
        wp_enqueue_style( 'owl.carousel.min.css', get_stylesheet_directory_uri() . '/assets/home/css/owl.carousel.min.css', array(), kadence_child_get_asset_version('/assets/home/css/owl.carousel.min.css') );
        wp_enqueue_style( 'owl.theme.default.min.css', get_stylesheet_directory_uri() . '/assets/home/css/owl.theme.default.min.css', array(), kadence_child_get_asset_version('/assets/home/css/owl.theme.default.min.css') );
    }
}
add_action('wp_enqueue_scripts', 'cargar_estilos_personalizados');

function cargar_javascript_personalizado() {
    if ( is_page_template('template-1.php') ) {
        // Scripts externos (CDN) - con su propia versión
        wp_enqueue_script( 'tailwindcss-browser', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), '4.0.0', false );
        
        // Scripts locales - con versión automática basada en fecha de modificación
        wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . '/assets/template-1/js/bootstrap.bundle.min.js', array(), kadence_child_get_asset_version('/assets/template-1/js/bootstrap.bundle.min.js'), true );
        wp_enqueue_script( 'glightbox-js', get_stylesheet_directory_uri() . '/assets/template-1/js/glightbox.min.js', array(), kadence_child_get_asset_version('/assets/template-1/js/glightbox.min.js'), true );
        wp_enqueue_script( 'tiny-slider-js', get_stylesheet_directory_uri() . '/assets/template-1/js/tiny-slider.js', array(), kadence_child_get_asset_version('/assets/template-1/js/tiny-slider.js'), true );
    }

    if ( is_page_template('template-2.php') ) {
        // Scripts externos (CDN) - con su propia versión
        wp_enqueue_script( 'tailwindcss-browser', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), '4.0.0', false );
    }

    if ( is_page_template('template-3.php') ) {
        // Scripts externos (CDN) - con su propia versión
        wp_enqueue_script( 'tailwindcss-browser', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), '4.0.0', false );
        
        // Scripts locales - con versión automática
        wp_enqueue_script( 'template-3-script', get_stylesheet_directory_uri() . '/assets/template-3/js/script.js', array(), kadence_child_get_asset_version('/assets/template-3/js/script.js'), true );
    }

    if ( is_page_template('home.php') ) {
        // Scripts locales - con versión automática
        wp_enqueue_script( 'jquery.min.js', get_stylesheet_directory_uri() . '/assets/home/js/jquery.min.js', array(), kadence_child_get_asset_version('/assets/home/js/jquery.min.js'), true );
        wp_enqueue_script( 'owl.carousel.min.js', get_stylesheet_directory_uri() . '/assets/home/js/owl.carousel.min.js', array(), kadence_child_get_asset_version('/assets/home/js/owl.carousel.min.js'), true );
        wp_enqueue_script( 'main.js', get_stylesheet_directory_uri() . '/assets/home/js/main.js', array(), kadence_child_get_asset_version('/assets/home/js/main.js'), true );
    }
}
add_action('wp_enqueue_scripts', 'cargar_javascript_personalizado');

function kadence_child_add_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' !== $relation_type ) {
        return $urls;
    }

    if ( ! is_page_template( 'template-2.php' ) ) {
        return $urls;
    }

    $urls[] = [
        'href' => 'https://fonts.gstatic.com',
        'crossorigin' => '',
    ];

    return $urls;
}
add_filter( 'wp_resource_hints', 'kadence_child_add_resource_hints', 10, 2 );

// ============================================
// CATEGORIAS DE PORTADA
// ============================================

function kadence_child_get_default_cover_categories() {
    return [
        [
            'label' => 'Curso',
            'value' => 'curso',
            'color' => '#1f7a8c',
        ],
        [
            'label' => 'Webinar',
            'value' => 'webinar',
            'color' => '#d97706',
        ],
        [
            'label' => 'Taller',
            'value' => 'taller',
            'color' => '#7c3aed',
        ],
    ];
}

function kadence_child_get_empty_cover_category() {
    return [
        'label' => '',
        'value' => '',
        'color' => '#1f7a8c',
    ];
}

function kadence_child_has_saved_cover_categories() {
    return null !== get_option( 'kadence_child_cover_categories', null );
}

function kadence_child_normalize_cover_category_value( $value, $label = '' ) {
    $candidate = sanitize_title( wp_unslash( (string) $value ) );

    if ( '' === $candidate && '' !== $label ) {
        $candidate = sanitize_title( wp_unslash( (string) $label ) );
    }

    return $candidate;
}

function kadence_child_get_cover_categories() {
    $categories = get_option( 'kadence_child_cover_categories', null );

    if ( null === $categories ) {
        return kadence_child_get_default_cover_categories();
    }

    if ( ! is_array( $categories ) ) {
        return [];
    }

    $normalized = [];

    foreach ( $categories as $category ) {
        if ( ! is_array( $category ) ) {
            continue;
        }

        $label = sanitize_text_field( $category['label'] ?? '' );
        $value = kadence_child_normalize_cover_category_value( $category['value'] ?? '', $label );
        $color = sanitize_hex_color( $category['color'] ?? '' );

        if ( '' === $label || '' === $value ) {
            continue;
        }

        $normalized[] = [
            'label' => $label,
            'value' => $value,
            'color' => $color ?: '#1f7a8c',
        ];
    }

    return array_values( $normalized );
}

function kadence_child_get_cover_category_options() {
    $options = [];

    foreach ( kadence_child_get_cover_categories() as $category ) {
        $options[ $category['value'] ] = $category['label'];
    }

    return $options;
}

function kadence_child_get_cover_category( $value ) {
    $value = sanitize_title( (string) $value );

    if ( '' === $value ) {
        return null;
    }

    foreach ( kadence_child_get_cover_categories() as $category ) {
        if ( $category['value'] === $value ) {
            return $category;
        }
    }

    return null;
}

function kadence_child_get_contrast_text_color( $hex_color ) {
    $hex_color = sanitize_hex_color( $hex_color );

    if ( empty( $hex_color ) ) {
        return '#ffffff';
    }

    $color = ltrim( $hex_color, '#' );

    if ( 3 === strlen( $color ) ) {
        $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    }

    $red = hexdec( substr( $color, 0, 2 ) );
    $green = hexdec( substr( $color, 2, 2 ) );
    $blue = hexdec( substr( $color, 4, 2 ) );
    $brightness = ( ( $red * 299 ) + ( $green * 587 ) + ( $blue * 114 ) ) / 1000;

    return $brightness > 155 ? '#111827' : '#ffffff';
}

function kadence_child_sanitize_cover_categories( $raw_categories ) {
    if ( ! is_array( $raw_categories ) ) {
        return [];
    }

    $sanitized = [];
    $used_values = [];

    foreach ( $raw_categories as $category ) {
        if ( ! is_array( $category ) ) {
            continue;
        }

        $label = sanitize_text_field( $category['label'] ?? '' );
        $value = kadence_child_normalize_cover_category_value( $category['value'] ?? '', $label );
        $color = sanitize_hex_color( $category['color'] ?? '' );

        if ( '' === $label || '' === $value || isset( $used_values[ $value ] ) ) {
            continue;
        }

        $used_values[ $value ] = true;

        $sanitized[] = [
            'label' => $label,
            'value' => $value,
            'color' => $color ?: '#1f7a8c',
        ];
    }

    return array_values( $sanitized );
}

function kadence_child_register_cover_categories_menu() {
    add_theme_page(
        'Categorias de Portada',
        'Categorias de Portada',
        'manage_options',
        'kadence-child-cover-categories',
        'kadence_child_render_cover_categories_page'
    );
}
add_action( 'admin_menu', 'kadence_child_register_cover_categories_menu' );

function kadence_child_handle_cover_categories_save() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( ! isset( $_POST['kadence_child_cover_categories_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kadence_child_cover_categories_nonce'] ) ), 'kadence_child_save_cover_categories' ) ) {
        return;
    }

    $screen_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

    if ( 'kadence-child-cover-categories' !== $screen_page ) {
        return;
    }

    $categories = kadence_child_sanitize_cover_categories( wp_unslash( $_POST['cover_categories'] ?? [] ) );

    update_option( 'kadence_child_cover_categories', $categories );

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'kadence-child-cover-categories',
                'updated' => 'true',
            ],
            admin_url( 'themes.php' )
        )
    );
    exit;
}
add_action( 'admin_init', 'kadence_child_handle_cover_categories_save' );

function kadence_child_render_cover_categories_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $categories = kadence_child_get_cover_categories();
    $categories_for_form = ! empty( $categories ) ? $categories : [ kadence_child_get_empty_cover_category() ];
    ?>
    <div class="wrap">
        <h1>Categorias de Portada</h1>
        <p>Administra las categorias disponibles para el campo Categoria dentro de Portada section en los templates 1, 2 y 3.</p>
        <p>Puedes crear, editar y eliminar categorias libremente. Si dejas la lista vacia, el selector de categoria no mostrara opciones hasta que agregues nuevas.</p>
        <?php if ( isset( $_GET['updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Las categorias de portada fueron actualizadas.</p>
            </div>
        <?php endif; ?>
        <?php if ( empty( $categories ) && kadence_child_has_saved_cover_categories() ) : ?>
            <div class="notice notice-warning inline">
                <p>No hay categorias activas. Usa el boton Agregar categoria para crear nuevas opciones.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'kadence_child_save_cover_categories', 'kadence_child_cover_categories_nonce' ); ?>
            <table class="widefat striped" id="kadence-child-cover-categories-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Label</th>
                        <th style="width: 28%;">Value</th>
                        <th style="width: 20%;">Color</th>
                        <th style="width: 20%;">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $categories_for_form as $index => $category ) : ?>
                        <tr>
                            <td>
                                <input type="text" class="regular-text" name="cover_categories[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $category['label'] ); ?>" placeholder="Ej: Webinar" required>
                            </td>
                            <td>
                                <input type="text" class="regular-text" name="cover_categories[<?php echo esc_attr( $index ); ?>][value]" value="<?php echo esc_attr( $category['value'] ); ?>" placeholder="ej: webinar">
                                <p class="description">Se normaliza como slug. Si lo dejas vacio, se genera desde el label.</p>
                            </td>
                            <td>
                                <input type="color" name="cover_categories[<?php echo esc_attr( $index ); ?>][color]" value="<?php echo esc_attr( $category['color'] ); ?>">
                            </td>
                            <td>
                                <button type="button" class="button kadence-child-remove-category">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top: 16px;">
                <button type="button" class="button" id="kadence-child-add-category">+ Agregar categoria</button>
            </p>

            <?php submit_button( 'Guardar categorias' ); ?>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.querySelector('#kadence-child-cover-categories-table tbody');
        const addButton = document.getElementById('kadence-child-add-category');

        if (!tableBody || !addButton) {
            return;
        }

        const getNextIndex = function () {
            return tableBody.querySelectorAll('tr:not(.kadence-child-empty-state)').length;
        };

        const buildEmptyState = function () {
            const row = document.createElement('tr');
            row.className = 'kadence-child-empty-state';
            row.innerHTML = '<td colspan="4">No hay categorias configuradas. Haz clic en "+ Agregar categoria" para crear una.</td>';
            return row;
        };

        const refreshEmptyState = function () {
            const rows = tableBody.querySelectorAll('tr:not(.kadence-child-empty-state)');
            const emptyState = tableBody.querySelector('.kadence-child-empty-state');

            if (rows.length === 0 && !emptyState) {
                tableBody.appendChild(buildEmptyState());
            }

            if (rows.length > 0 && emptyState) {
                emptyState.remove();
            }
        };

        const createRow = function (index) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="text" class="regular-text" name="cover_categories[${index}][label]" value="" placeholder="Ej: Seminario" required>
                </td>
                <td>
                    <input type="text" class="regular-text" name="cover_categories[${index}][value]" value="" placeholder="ej: seminario">
                    <p class="description">Se normaliza como slug. Si lo dejas vacio, se genera desde el label.</p>
                </td>
                <td>
                    <input type="color" name="cover_categories[${index}][color]" value="#1f7a8c">
                </td>
                <td>
                    <button type="button" class="button kadence-child-remove-category">Eliminar</button>
                </td>
            `;

            return row;
        };

        addButton.addEventListener('click', function () {
            const emptyState = tableBody.querySelector('.kadence-child-empty-state');

            if (emptyState) {
                emptyState.remove();
            }

            tableBody.appendChild(createRow(getNextIndex()));
        });

        tableBody.addEventListener('click', function (event) {
            if (!event.target.classList.contains('kadence-child-remove-category')) {
                return;
            }

            const row = event.target.closest('tr');

            if (!row) {
                return;
            }

            row.remove();
            refreshEmptyState();
        });

        refreshEmptyState();
    });
    </script>
    <?php
}

// ============================================
// META BOX - TEMPLATE 1 CUSTOM FIELDS
// ============================================

add_filter( 'rwmb_meta_boxes', 'template1_register_meta_boxes' );

function template1_register_meta_boxes( $meta_boxes ) {

    // Obtener el post_id actual desde la URL o del POST (compatibilidad con Gutenberg)
    $post_id = isset( $_GET['post'] )      ? intval( $_GET['post'] )      :
               ( isset( $_POST['post_ID'] ) ? intval( $_POST['post_ID'] ) : 0 );

    // Solo registrar los meta boxes si el template del post es template-1.php
    if ( $post_id && get_page_template_slug( $post_id ) !== 'template-1.php' ) {
        return $meta_boxes;
    }
    
    // HERO SECTION
    $meta_boxes[] = [
        'title'      => '📌 Hero Section',
        'id'         => 'hero_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Título Principal',
                'id'    => 'hero_title',
                'type'  => 'text',
                'std'   => 'Effective Insurance Management Webinar',
                'size'  => 100,
            ],
            [
                'name'  => 'Nombre del Logo',
                'id'    => 'hero_logo',
                'type'  => 'text',
                'std'   => 'Logo Name',
            ],
            [
                'name'  => 'Subtítulo',
                'id'    => 'hero_subtitle',
                'type'  => 'wysiwyg',
                'std'   => 'Join Our Free Webinar to Master Your Insurance Needs',
                'size'  => 100,
            ],
            [
                'name' => 'Link WhatsApp',
                'id'   => 'hero_whatsapp_link',
                'type' => 'url',
                'std'  => 'https://wa.me/0000000000',
                'desc' => 'Solo el enlace (ej: https://wa.me/51999999999).',
            ],
            [
                'name' => 'Texto del Botón WhatsApp',
                'id'   => 'hero_whatsapp_label',
                'type' => 'text',
                'std'  => 'WhatsApp',
                'size' => 60,
            ],
            [
                'name'  => 'Fecha del Webinar',
                'id'    => 'webinar_date',
                'type'  => 'datetime-local',
                'std'   => 'FRIDAY SEPTEMBER 20, 2024 20:30 PM',
                'desc'  => 'Formato: FRIDAY SEPTEMBER 20, 2024 20:30 PM',
            ],
            [
                'name'            => 'Imagen Hero',
                'id'              => 'hero_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    // PORTADA SECTION
    $meta_boxes[] = [
        'title'      => '🖼️ Portada (Listado Home)',
        'id'         => 'cover_section_template1',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'            => 'Imagen Portada',
                'id'              => 'listing_cover_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
                'desc'            => 'Imagen usada en el listado de landing pages/cursos del template Home.',
            ],
            [
                'name'        => 'Título de portada',
                'id'          => 'listing_cover_title',
                'type'        => 'text',
                'size'        => 100,
                'placeholder' => 'Si se deja vacío, se usa el título original de la página.',
                'desc'        => 'Título personalizado para la tarjeta del listado en Home.',
            ],
            [
                'name'        => 'Categoria',
                'id'          => 'listing_cover_category',
                'type'        => 'select',
                'options'     => kadence_child_get_cover_category_options(),
                'placeholder' => 'Selecciona una categoria',
                'desc'        => sprintf(
                    'Las opciones se administran desde Apariencia > <a href="%s">Categorias de Portada</a>.',
                    esc_url( admin_url( 'themes.php?page=kadence-child-cover-categories' ) )
                ),
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    // ABOUT WEBINAR SECTION
    $meta_boxes[] = [
        'title'      => '📖 About Webinar Section',
        'id'         => 'about_webinar_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Título Principal',
                'id'    => 'about_main_title',
                'type'  => 'text',
                'std'   => '¿QUÉ APRENDERÁS?',
                'size'  => 100,
            ],
            [
                'name'            => 'Imagen About',
                'id'              => 'about_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
            ],
            [
                'name'  => 'Tab 1 - Título',
                'id'    => 'about_tab1_title',
                'type'  => 'text',
                'std'   => 'Definición',
                'desc'  => 'Dejar vacío para ocultar este tab',
            ],
            [
                'name'  => 'Tab 1 - Contenido',
                'id'    => 'about_tab1_content',
                'type'  => 'wysiwyg',
                'raw'   => false,
                'options' => [
                    'textarea_rows' => 8,
                    'teeny'         => false,
                ],
            ],
            [
                'name'  => 'Tab 2 - Título',
                'id'    => 'about_tab2_title',
                'type'  => 'text',
                'std'   => 'Dirigido a',
                'desc'  => 'Dejar vacío para ocultar este tab',
            ],
            [
                'name'  => 'Tab 2 - Contenido',
                'id'    => 'about_tab2_content',
                'type'  => 'wysiwyg',
                'raw'   => false,
                'options' => [
                    'textarea_rows' => 8,
                    'teeny'         => false,
                ],
            ],
            [
                'name'  => 'Tab 3 - Título',
                'id'    => 'about_tab3_title',
                'type'  => 'text',
                'std'   => '¿Qué lograrás?',
                'desc'  => 'Dejar vacío para ocultar este tab',
            ],
            [
                'name'  => 'Tab 3 - Contenido',
                'id'    => 'about_tab3_content',
                'type'  => 'wysiwyg',
                'raw'   => false,
                'options' => [
                    'textarea_rows' => 8,
                    'teeny'         => false,
                ],
            ],
        ],
        'include' => [
            'relation' => ['AND'],
            'page_template' => ['template-1.php'],
        ],
    ];

    // TEMARIO SECTION
    $meta_boxes[] = [
        'title'      => '📚 Temario Section',
        'id'         => 'temario_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'temario_title',
                'type' => 'text',
                'std'  => 'Conoce nuestro temario',
                'size' => 100,
            ],
            [
                'name'        => 'Módulos (formato por líneas)',
                'id'          => 'temario_modules',
                'type'        => 'textarea',
                'clone'       => true,
                'sort_clone'  => true,
                'add_button'  => 'Agregar módulo',
                'rows'        => 6,
                'desc'        => "Primera línea: título del módulo. Líneas siguientes: temas. Ejemplo:\nMódulo 1: Psicopatología en la Infancia\nTema 1: Psicopatología de la percepción\nTema 2: Trastorno del pensamiento",
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    // EXPONENTE SECTION
    $meta_boxes[] = [
        'title'      => '🎤 Exponente Section',
        'id'         => 'exponente_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Mostrar Sección Exponente',
                'id'   => 'exponente_show',
                'type' => 'switch',
                'std'  => 1,
                'desc' => 'Activar para mostrar la sección de exponentes en la página.',
            ],
            [
                'name' => 'Título de la Sección',
                'id'   => 'exponente_title',
                'type' => 'text',
                'std'  => 'Exponente',
                'size' => 100,
            ],
            [
                'name'        => 'Imágenes (una por exponente)',
                'id'          => 'exponente_images',
                'type'        => 'image_advanced',
                'max_file_uploads' => 1,
                'clone'       => true,
                'sort_clone'  => true,
                'add_button'  => 'Agregar imagen',
                'desc'        => 'El orden debe coincidir con nombres y descripciones.',
            ],
            [
                'name'        => 'Nombres',
                'id'          => 'exponente_names',
                'type'        => 'text',
                'clone'       => true,
                'sort_clone'  => true,
                'add_button'  => 'Agregar nombre',
                'desc'        => 'El orden debe coincidir con imágenes y descripciones.',
            ],
            [
                'name'        => 'Descripciones',
                'id'          => 'exponente_descriptions',
                'type'        => 'textarea',
                'rows'        => 4,
                'clone'       => true,
                'sort_clone'  => true,
                'add_button'  => 'Agregar descripción',
                'desc'        => 'El orden debe coincidir con imágenes y nombres.',
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    // CALL-ACTION SECTION
    $meta_boxes[] = [
        'title'      => '📣 Call-Action Section',
        'id'         => 'call_action_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Texto Principal',
                'id'   => 'call_action_text',
                'type' => 'textarea',
                'rows' => 4,
                'std'  => 'Una vez ingrese a la comunidad o grupo solo debe esperar a que el administrador comparta toda la información, NO DEBE atender llamadas ni mensajes de personas diferentes',
            ],
            [
                'name' => 'Link del Botón',
                'id'   => 'call_action_link',
                'type' => 'url',
                'std'  => 'https://wa.me/0000000000',
                'desc' => 'Solo el enlace (ej: https://wa.me/51999999999).',
            ],
            [
                'name' => 'Texto del Botón',
                'id'   => 'call_action_label',
                'type' => 'text',
                'std'  => 'WhatsApp',
                'size' => 60,
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    // VIDEO SECTION
    $meta_boxes[] = [
        'title'      => '🎥 Video Section',
        'id'         => 'video_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'video_title_section',
                'type' => 'text',
                'std'  => 'Mira nuestro video',
                'size' => 100,
                'desc' => 'Título que aparecerá encima del video.',
            ],
            [
                'name' => 'URL del Video',
                'id'   => 'video_url',
                'type' => 'url',
                'std'  => '',
                'size' => 100,
                'desc' => 'URL del video de YouTube (formato embed). Ejemplo: https://www.youtube.com/embed/VIDEO_ID. Dejar vacío para ocultar la sección.',
            ],
        ],
        'include' => [
            'page_template' => ['template-1.php'],
        ],
    ];

    return $meta_boxes;
}

// ============================================
// META BOX - TEMPLATE 2 CUSTOM FIELDS
// ============================================

add_filter( 'rwmb_meta_boxes', 'template2_register_meta_boxes' );

function template2_register_meta_boxes( $meta_boxes ) {

    // Obtener el post_id actual desde la URL o del POST (compatibilidad con Gutenberg)
    $post_id = isset( $_GET['post'] )      ? intval( $_GET['post'] )      :
               ( isset( $_POST['post_ID'] ) ? intval( $_POST['post_ID'] ) : 0 );

    // Solo registrar los meta boxes si el template del post es template-2.php
    if ( $post_id && get_page_template_slug( $post_id ) !== 'template-2.php' ) {
        return $meta_boxes;
    }
    
    // HEADER/CABECERA SECTION - Botón WhatsApp
    $meta_boxes[] = [
        'title'      => '📱 Header/Cabecera - Botón WhatsApp',
        'id'         => 'header_whatsapp_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Link WhatsApp',
                'id'   => 'hero_whatsapp_link',
                'type' => 'url',
                'std'  => 'https://wa.me/0000000000',
                'desc' => 'Solo el enlace (ej: https://wa.me/51999999999).',
            ],
            [
                'name' => 'Texto del Botón WhatsApp',
                'id'   => 'hero_whatsapp_label',
                'type' => 'text',
                'std'  => 'WhatsApp',
                'size' => 60,
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // HERO SECTION
    $meta_boxes[] = [
        'title'      => '🎯 Hero Section',
        'id'         => 'hero_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Título Principal',
                'id'    => 'hero_title',
                'type'  => 'text',
                'std'   => 'El viaje de conversión: 5 pasos para generar más clientes potenciales y ventas',
                'size'  => 100,
            ],
            [
                'name'            => 'Imagen Hero',
                'id'              => 'hero_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
            ],
            [
                'name' => 'Texto del Botón Principal',
                'id'   => 'hero_button_label',
                'type' => 'text',
                'std'  => 'GUARDAR MI ASIENTO AHORA',
                'size' => 60,
            ],
            [
                'name' => 'Link del Botón Principal',
                'id'   => 'hero_button_link',
                'type' => 'url',
                'std'  => '#',
                'desc' => 'URL del botón principal.',
            ],
            [
                'name'  => 'Texto Descriptivo',
                'id'    => 'hero_description',
                'type'  => 'textarea',
                'rows'  => 3,
                'std'   => 'Haga clic en el botón ahora para unirse a nosotros en este seminario web en vivo gratuito este miércoles para propietarios de pequeñas empresas, emprendedores y proveedores de servicios',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // PORTADA SECTION
    $meta_boxes[] = [
        'title'      => '🖼️ Portada (Listado Home)',
        'id'         => 'cover_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'            => 'Imagen Portada',
                'id'              => 'listing_cover_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
                'desc'            => 'Imagen usada en el listado de landing pages/cursos del template Home.',
            ],
            [
                'name'        => 'Título de portada',
                'id'          => 'listing_cover_title',
                'type'        => 'text',
                'size'        => 100,
                'placeholder' => 'Si se deja vacío, se usa el título original de la página.',
                'desc'        => 'Título personalizado para la tarjeta del listado en Home.',
            ],
            [
                'name'        => 'Categoria',
                'id'          => 'listing_cover_category',
                'type'        => 'select',
                'options'     => kadence_child_get_cover_category_options(),
                'placeholder' => 'Selecciona una categoria',
                'desc'        => sprintf(
                    'Las opciones se administran desde Apariencia > <a href="%s">Categorias de Portada</a>.',
                    esc_url( admin_url( 'themes.php?page=kadence-child-cover-categories' ) )
                ),
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // DETALLES DEL WEBINAR SECTION
    $meta_boxes[] = [
        'title'      => '📅 Detalles del Webinar',
        'id'         => 'detalles_webinar_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Título Principal',
                'id'    => 'detalles_title',
                'type'  => 'text',
                'std'   => 'Aquí están los detalles del webinar',
                'size'  => 100,
            ],
            [
                'name'    => 'Contenido del Bloque',
                'id'      => 'detalles_content',
                'type'    => 'wysiwyg',
                'raw'     => false,
                'options' => [
                    'textarea_rows' => 15,
                    'teeny'         => false,
                ],
                'std' => '<ul>
                            <li><strong>¿Cuándo es?</strong> 19 de abril, 3PM Nueva York (EDT), 12PM Los Ángeles (PDT), 7PM Londres (GMT), 6AM Sídney (AEDT)</li>
                            <li><strong>¿Dónde es?</strong> Únete desde tu laptop/escritorio</li>
                            <li>¿Quién lo hace? Presentado por Conor O\'Neil, Educador Senior de Conversión</li>
                            <li><strong>¿Por qué deberías estar allí?</strong> Allanar el camino para obtener más clientes potenciales y ventas para un crecimiento empresarial más rápido.</li>
                            <li><strong>¿De qué se trata?</strong> Un viaje de conversión de 5 pasos para hacer crecer tu negocio. Detalles completos abajo.</li>
                        </ul>',
            ],
            [
                'name'  => 'Título del Countdown',
                'id'    => 'countdown_title',
                'type'  => 'text',
                'std'   => 'Este entrenamiento comienza en...',
                'size'  => 100,
            ],
            [
                'name'  => 'Fecha del Webinar',
                'id'    => 'webinar_date',
                'type'  => 'datetime-local',
                'std'   => '',
                'desc'  => 'Selecciona la fecha y hora del webinar.',
            ],
            [
                'name' => 'Texto del Botón',
                'id'   => 'detalles_button_label',
                'type' => 'text',
                'std'  => 'GUARDAR MI ASIENTO AHORA',
                'size' => 60,
            ],
            [
                'name' => 'Link del Botón',
                'id'   => 'detalles_button_link',
                'type' => 'url',
                'std'  => '#',
                'desc' => 'URL del botón.',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // DETALLES DEL EXPONENTE SECTION
    $meta_boxes[] = [
        'title'      => '👤 Detalles del Exponente',
        'id'         => 'detalles_exponente_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Mostrar Sección Exponente',
                'id'   => 'exponente_show',
                'type' => 'switch',
                'std'  => 1,
                'desc' => 'Activar para mostrar la sección de detalles del exponente en la página.',
            ],
            [
                'name'  => 'Título Principal',
                'id'    => 'exponente_title',
                'type'  => 'text',
                'std'   => 'Mejores prácticas de 7+ años de crecimiento rápido',
                'size'  => 100,
            ],
            [
                'name'    => 'Contenido del Exponente',
                'id'      => 'exponente_content',
                'type'    => 'wysiwyg',
                'raw'     => false,
                'options' => [
                    'textarea_rows' => 15,
                    'teeny'         => false,
                ],
                'std' => '<p>"Cuando entiendes el viaje de conversión, desbloqueas un potencial de crecimiento ilimitado para tu negocio.</p>
                        <p>Desde 2013, Marketing Guru ha crecido to over 46,000 active customers because we\'ve tested and tracked our marketing experiments to figure out what works.</p>
                        <p>En este webinar, estaré compartiendo these best practices of what\'s worked for us with you. ¡Nos vemos allá!"</p>
                        <p>— <strong>Conor O\'Neil</strong> - Senior Conversion Educator</p>',
            ],
            [
                'name'            => 'Imagen del Exponente',
                'id'              => 'exponente_image',
                'type'            => 'image_advanced',
                'max_file_uploads' => 1,
            ],
            [
                'name' => 'Texto del Botón',
                'id'   => 'exponente_button_label',
                'type' => 'text',
                'std'  => 'SAVE MY SEAT NOW',
                'size' => 60,
            ],
            [
                'name' => 'Link del Botón',
                'id'   => 'exponente_button_link',
                'type' => 'url',
                'std'  => '#',
                'desc' => 'URL del botón.',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // TEMARIO DEL WEBINAR SECTION
    $meta_boxes[] = [
        'title'      => '📋 Temario del Webinar',
        'id'         => 'temario_webinar_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Título de la Sección',
                'id'    => 'temario_title',
                'type'  => 'text',
                'std'   => 'Durante este entrenamiento en vivo gratuito, aprenderás:',
                'size'  => 100,
            ],
            [
                'name'    => 'Items del Temario',
                'id'      => 'temario_items',
                'type'    => 'wysiwyg',
                'clone'   => true,
                'sort_clone' => true,
                'max_clone' => 20,
                'add_button' => '+ Agregar Item',
                'raw'     => false,
                'options' => [
                    'textarea_rows' => 8,
                    'teeny'         => false,
                    'media_buttons' => true,
                    'quicktags'     => true,
                ],
                'desc' => 'Cada editor representa un item (li) de la lista. Usa negritas, listas, etc. Haz clic en "+ Agregar Item" para añadir más.',
            ],
            [
                'name' => 'Texto del Botón',
                'id'   => 'temario_button_label',
                'type' => 'text',
                'std'  => 'GUARDAR MI ASIENTO AHORA',
                'size' => 60,
            ],
            [
                'name' => 'Link del Botón',
                'id'   => 'temario_button_link',
                'type' => 'url',
                'std'  => '#',
                'desc' => 'URL del botón.',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // WARNING SECTION
    $meta_boxes[] = [
        'title'      => '⚠️ Sección de Advertencia',
        'id'         => 'warning_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'  => 'Mostrar Sección de Advertencia',
                'id'    => 'warning_enable',
                'type'  => 'switch',
                'style' => 'rounded',
                'on_label'  => 'Sí',
                'off_label' => 'No',
                'std'   => 1,
                'desc'  => 'Activa o desactiva la visualización de esta sección en la página.',
            ],
            [
                'name'    => 'Contenido de la Advertencia',
                'id'      => 'warning_content',
                'type'    => 'textarea',
                'rows'    => 4,
                'std'     => 'El espacio es limitado y estos entrenamientos EN VIVO siempre se llenan porque son significativamente mejores que la información que otros te cobran miles de dólares... aunque sean gratis.',
                'desc'    => 'El texto de la advertencia. El prefijo "ADVERTENCIA:" se agrega automáticamente.',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    // VIDEO SECTION
    $meta_boxes[] = [
        'title'      => '🎥 Video Section',
        'id'         => 'video_section_template2',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'video_title_section',
                'type' => 'text',
                'std'  => 'Mira nuestro video',
                'size' => 100,
                'desc' => 'Título que aparecerá encima del video.',
            ],
            [
                'name' => 'URL del Video',
                'id'   => 'video_url',
                'type' => 'url',
                'std'  => '',
                'size' => 100,
                'desc' => 'URL del video de YouTube (formato embed). Ejemplo: https://www.youtube.com/embed/VIDEO_ID. Dejar vacío para ocultar la sección.',
            ],
        ],
        'include' => [
            'page_template' => ['template-2.php'],
        ],
    ];

    return $meta_boxes;
}

// METABOXES PARA TEMPLATE-3
add_filter('rwmb_meta_boxes', 'template3_register_meta_boxes');
function template3_register_meta_boxes($meta_boxes) {

    // Obtener el post_id actual desde la URL o del POST (compatibilidad con Gutenberg)
    $post_id = isset($_GET['post']) ? intval($_GET['post']) :
               (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);

    // Solo registrar los meta boxes si el template del post es template-3.php
    if ($post_id && get_page_template_slug($post_id) !== 'template-3.php') {
        return $meta_boxes;
    }

    // HERO SECTION
    $meta_boxes[] = [
        'title'      => '🎯 Hero Section',
        'id'         => 'hero_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título Principal',
                'id'   => 'hero_heading',
                'type' => 'text',
                'std'  => 'Master Your Money with',
                'size' => 60,
                'desc' => 'El texto principal del título del hero.',
            ],
            [
                'name' => 'Texto Resaltado del Título',
                'id'   => 'hero_heading_highlight',
                'type' => 'text',
                'std'  => 'AI Intelligence',
                'size' => 60,
                'desc' => 'El texto que aparecerá con gradiente dentro del título.',
            ],
            [
                'name'    => 'Subtítulo',
                'id'      => 'hero_subheading',
                'type'    => 'textarea',
                'rows'    => 4,
                'std'     => 'Stop guessing. Start growing. Mintly automates your savings, optimizes investments, and tracks every penny with bank-level security.',
                'desc'    => 'La descripción que aparece debajo del título.',
            ],
            [
                'name' => 'Link del Botón WhatsApp',
                'id'   => 'hero_whatsapp_link',
                'type' => 'url',
                'std'  => '#signup',
                'desc' => 'URL del botón de WhatsApp.',
            ],
            [
                'name' => 'Texto del Botón WhatsApp',
                'id'   => 'hero_whatsapp_label',
                'type' => 'text',
                'std'  => 'Ingresa al grupo ahora !',
                'size' => 60,
            ],
            [
                'name'             => 'Imagen Principal',
                'id'               => 'hero_image',
                'type'             => 'image_advanced',
                'max_file_uploads' => 1,
                'desc'             => 'La imagen principal del hero (dashboard/tablet).',
            ],
            [
                'name' => 'Texto Alternativo de la Imagen',
                'id'   => 'hero_image_alt',
                'type' => 'text',
                'std'  => 'Mintly Dashboard on Tablet',
                'size' => 60,
                'desc' => 'Texto alternativo para accesibilidad (atributo alt).',
            ],
        ],
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    // PORTADA SECTION
    $meta_boxes[] = [
        'title'      => '🖼️ Portada (Listado Home)',
        'id'         => 'cover_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'             => 'Imagen Portada',
                'id'               => 'listing_cover_image',
                'type'             => 'image_advanced',
                'max_file_uploads' => 1,
                'desc'             => 'Imagen usada en el listado de landing pages/cursos del template Home.',
            ],
            [
                'name'        => 'Título de portada',
                'id'          => 'listing_cover_title',
                'type'        => 'text',
                'size'        => 100,
                'placeholder' => 'Si se deja vacío, se usa el título original de la página.',
                'desc'        => 'Título personalizado para la tarjeta del listado en Home.',
            ],
            [
                'name'        => 'Categoria',
                'id'          => 'listing_cover_category',
                'type'        => 'select',
                'options'     => kadence_child_get_cover_category_options(),
                'placeholder' => 'Selecciona una categoria',
                'desc'        => sprintf(
                    'Las opciones se administran desde Apariencia > <a href="%s">Categorias de Portada</a>.',
                    esc_url( admin_url( 'themes.php?page=kadence-child-cover-categories' ) )
                ),
            ],
        ],
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    // KEY BENEFITS SECTION
    $meta_boxes[] = [
        'title'      => '🎓 Key Benefits Section (¿Qué Aprenderás?)',
        'id'         => 'benefits_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'benefits_title',
                'type' => 'text',
                'std'  => '¿QUÉ APRENDERÁS?',
                'size' => 60,
            ],
            [
                'type' => 'heading',
                'name' => '📄 Tarjeta Principal (XL Card)',
            ],
            [
                'name'    => 'Párrafo 1 - Introducción',
                'id'      => 'benefits_card1_p1',
                'type'    => 'textarea',
                'rows'    => 3,
                'std'     => 'Descubre nuevas habilidades para llevar tu carrera al próximo nivel aprendiendo de los mejores expertos.',
            ],
            [
                'name' => 'Párrafo 2 - Título del Diplomado',
                'id'   => 'benefits_card1_p2',
                'type' => 'text',
                'std'  => 'DIPLOMADO PSICOPATOLOGÍA EN LA INFANCIA Y ADOLESCENCIA',
                'size' => 80,
            ],
            [
                'name'    => 'Párrafo 3 - Descripción Detallada',
                'id'      => 'benefits_card1_p3',
                'type'    => 'textarea',
                'rows'    => 8,
                'std'     => 'El Diplomado en Psicopatología en la Infancia y adolescencia permitirá a los participantes, proporcionar fundamentos teóricos y herramientas clínicas de la psicopatología infantil y adolescente desde el modelo cognitivo procesal sistémico. Reflexionando sobre los procesos de la salud mental, la psicopatología infantil y adolescente, y su abordaje desde una mirada comprensiva, amorosa y terapéutica la sintomatología clínica, enfatizando en la importancia de las relaciones vinculares y las características de estas, hacia una tendencia a desarrollar determinados cuadros clínicos o manifestaciones conductuales.',
            ],
            [
                'type' => 'heading',
                'name' => '🎯 Tarjeta "Dirigido a"',
            ],
            [
                'name' => 'Título de "Dirigido a"',
                'id'   => 'benefits_card2_title',
                'type' => 'text',
                'std'  => 'Dirigido a',
                'size' => 60,
            ],
            [
                'name'       => 'Lista de Público Objetivo',
                'id'         => 'benefits_card2_items',
                'type'       => 'text',
                'clone'      => true,
                'max_clone'  => 15,
                'add_button' => '+ Agregar Item',
                'std'        => 'Psicólogos',
                'desc'       => 'Cada item representa una línea de la lista. Puedes agregar, eliminar o reordenar.',
            ],
            [
                'type' => 'heading',
                'name' => '✅ Tarjeta "¿Qué lograrás?"',
            ],
            [
                'name' => 'Título de "¿Qué lograrás?"',
                'id'   => 'benefits_card3_title',
                'type' => 'text',
                'std'  => '¿Qué lograrás?',
                'size' => 60,
            ],
            [
                'name'       => 'Lista de Logros',
                'id'         => 'benefits_card3_items',
                'type'       => 'text',
                'clone'      => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Logro',
                'std'        => 'Comprensión Teórica y Práctica',
                'desc'       => 'Cada item representa un logro o beneficio del diplomado.',
            ],
            [
                'type' => 'heading',
                'name' => '📱 Botón de WhatsApp',
            ],
            [
                'name' => 'Link del Botón WhatsApp',
                'id'   => 'benefits_whatsapp_link',
                'type' => 'url',
                'std'  => '#signup',
                'desc' => 'URL del botón de WhatsApp al final de la sección.',
            ],
            [
                'name' => 'Texto del Botón WhatsApp',
                'id'   => 'benefits_whatsapp_label',
                'type' => 'text',
                'std'  => 'Ingresa al grupo ahora !',
                'size' => 60,
            ],
        ],
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    // TEMARIO SECTION
    $meta_boxes[] = [
        'title'      => '📋 Sección Temario',
        'id'         => 'temario_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'temario_title',
                'type' => 'text',
                'std'  => 'Nuestro temario personalizado para cada etapa',
                'size' => 80,
            ],
            [
                'name'    => 'Subtítulo/Descripción',
                'id'      => 'temario_subtitle',
                'type'    => 'textarea',
                'rows'    => 3,
                'std'     => 'Cada uno de nuestros cursos está diseñado para adaptarse a las necesidades específicas de cada etapa de aprendizaje.',
            ],
            [
                'type' => 'heading',
                'name' => '📑 Items del Temario (Accordion)',
                'desc' => 'Agrega los títulos y luego los contenidos. El ítem 1 tendrá el título 1 y el contenido 1, el ítem 2 tendrá el título 2 y el contenido 2, etc.',
            ],
            [
                'name'       => 'Títulos de los Items',
                'id'         => 'temario_item_titles',
                'type'       => 'text',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 15,
                'add_button' => '+ Agregar Título',
                'std'        => 'Título del item del temario',
                'size'       => 80,
                'desc'       => 'Cada campo representa el título de un item del accordion. Orden: Título 1, Título 2, Título 3...',
            ],
            [
                'name'       => 'Contenidos de los Items',
                'id'         => 'temario_item_contents',
                'type'       => 'wysiwyg',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 15,
                'add_button' => '+ Agregar Contenido',
                'raw'        => false,
                'options'    => [
                    'textarea_rows' => 8,
                    'teeny'         => false,
                    'media_buttons' => true,
                    'quicktags'     => true,
                    'tinymce'       => true,
                ],
                'desc' => 'Cada editor representa el contenido de un item. IMPORTANTE: El orden debe coincidir con los títulos (Contenido 1 para Título 1, etc.).',
            ],
            [
                'type' => 'heading',
                'name' => '📱 Botón de WhatsApp',
            ],
            [
                'name' => 'Link del Botón WhatsApp',
                'id'   => 'temario_whatsapp_link',
                'type' => 'url',
                'std'  => '#signup',
                'desc' => 'URL del botón de WhatsApp en esta sección.',
            ],
            [
                'name' => 'Texto del Botón WhatsApp',
                'id'   => 'temario_whatsapp_label',
                'type' => 'text',
                'std'  => 'Ingresa al grupo ahora !',
                'size' => 60,
            ],
        ],
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    // EXPOSITORES SECTION
    $meta_boxes[] = [
        'title'      => '👥 Sección Expositores',
        'id'         => 'expositores_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Mostrar Sección Expositores',
                'id'   => 'expositores_show',
                'type' => 'switch',
                'std'  => 1,
                'desc' => 'Activar para mostrar la sección de expositores en la página.',
            ],
            [
                'name' => 'Título de la Sección',
                'id'   => 'expositores_title',
                'type' => 'text',
                'std'  => 'Nuestros expositores',
                'size' => 80,
            ],
            [
                'type' => 'heading',
                'name' => '📋 Expositores',
                'desc' => 'Agrega los expositores con su información. Cada posición se corresponde con un expositor (ej: Imagen #1 + Nombre #1 + Profesión #1 + Descripción #1 = Expositor 1).',
            ],
            [
                'name'             => 'Fotos de los Expositores',
                'id'               => 'expositor_images',
                'type'             => 'image_advanced',
                'max_file_uploads' => 1,
                'clone'            => true,
                'sort_clone'       => true,
                'max_clone'        => 20,
                'add_button'       => '+ Agregar Foto',
                'desc'             => 'Foto de cada expositor (preferiblemente cuadrada o vertical). La primera foto corresponde al expositor #1, la segunda al #2, etc.',
            ],
            [
                'name'       => 'Nombres de los Expositores',
                'id'         => 'expositor_nombres',
                'type'       => 'text',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Nombre',
                'size'       => 60,
                'desc'       => 'Nombre completo de cada expositor. El orden debe coincidir con las fotos.',
            ],
            [
                'name'       => 'Profesión/Cargo de los Expositores',
                'id'         => 'expositor_profesiones',
                'type'       => 'text',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Profesión',
                'size'       => 60,
                'desc'       => 'Profesión, cargo o título de cada expositor. El orden debe coincidir con las fotos y nombres.',
            ],
            [
                'name'       => 'Descripciones de los Expositores',
                'id'         => 'expositor_descripciones',
                'type'       => 'textarea',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Descripción',
                'rows'       => 4,
                'desc'       => 'Breve biografía o cita de cada expositor. El orden debe coincidir con los campos anteriores.',
            ],
        ], // fin de fields del metabox
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    // VIDEO SECTION
    $meta_boxes[] = [
        'title'      => '🎥 Video Section',
        'id'         => 'video_section_template3',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Título de la Sección',
                'id'   => 'video_title_section',
                'type' => 'text',
                'std'  => 'Mira nuestro video',
                'size' => 100,
                'desc' => 'Título que aparecerá encima del video.',
            ],
            [
                'name' => 'URL del Video',
                'id'   => 'video_url',
                'type' => 'url',
                'std'  => '',
                'size' => 100,
                'desc' => 'URL del video de YouTube (formato embed). Ejemplo: https://www.youtube.com/embed/VIDEO_ID. Dejar vacío para ocultar la sección.',
            ],
        ],
        'include' => [
            'page_template' => ['template-3.php'],
        ],
    ];

    return $meta_boxes;
}

// ============================================
// META BOX - HOME.PHP CUSTOM FIELDS
// ============================================

add_filter('rwmb_meta_boxes', 'home_register_meta_boxes');

function home_register_meta_boxes($meta_boxes) {
    
    // Obtener el post_id actual desde la URL o del POST (compatibilidad con Gutenberg)
    $post_id = isset($_GET['post']) ? intval($_GET['post']) :
               (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0);

    // Solo registrar los meta boxes si el template del post es home.php
    if ($post_id && get_page_template_slug($post_id) !== 'home.php') {
        return $meta_boxes;
    }

    // HEADER SECTION
    $meta_boxes[] = [
        'title'      => '🎯 Header - Botón de Acción',
        'id'         => 'home_header_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Texto del Botón Header',
                'id'   => 'header_button_text',
                'type' => 'text',
                'std'  => 'Solicita información ahora',
                'size' => 60,
                'desc' => 'Texto que aparecerá en el botón del header',
            ],
            [
                'name' => 'Enlace del Botón Header',
                'id'   => 'header_button_link',
                'type' => 'url',
                'std'  => '#',
                'desc' => 'URL a donde dirigirá el botón del header',
            ],
        ],
        'include' => [
            'page_template' => ['home.php'],
        ],
    ];

    // HERO SECTION
    $meta_boxes[] = [
        'title'      => '🚀 Hero Section - Botón',
        'id'         => 'home_hero_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name' => 'Texto del Botón',
                'id'   => 'hero_button_text',
                'type' => 'text',
                'std'  => 'Explora Nuestros Programas',
                'size' => 60,
                'desc' => 'Texto del botón de llamada a la acción',
            ],
            [
                'name' => 'Enlace del Botón',
                'id'   => 'hero_button_link',
                'type' => 'url',
                'std'  => 'https://www.facebook.com/profile.php?id=61559437738012',
                'desc' => 'URL a donde dirigirá el botón principal',
            ],
        ],
        'include' => [
            'page_template' => ['home.php'],
        ],
    ];

    // TEACHERS/PONENTES SECTION
    $meta_boxes[] = [
        'title'      => '👨‍🏫 Nuestros Ponentes / Teachers',
        'id'         => 'home_teachers_section',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'type' => 'heading',
                'name' => '📋 Lista de Profesores',
                'desc' => 'Agrega los profesores con su información. Cada posición se corresponde con un profesor (ej: Foto #1 + Nombre #1 + Cargo #1 = Profesor 1).',
            ],
            [
                'name'             => 'Fotos de los Profesores',
                'id'               => 'teacher_images',
                'type'             => 'image_advanced',
                'max_file_uploads' => 1,
                'clone'            => true,
                'sort_clone'       => true,
                'max_clone'        => 20,
                'add_button'       => '+ Agregar Foto',
                'desc'             => 'Foto de cada profesor (preferiblemente cuadrada 370x370px). La primera foto corresponde al profesor #1, la segunda al #2, etc.',
            ],
            [
                'name'       => 'Nombres de los Profesores',
                'id'         => 'teacher_names',
                'type'       => 'text',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Nombre',
                'size'       => 60,
                'placeholder' => 'Ej: Dr. John Doe',
                'desc'       => 'Nombre completo de cada profesor. El orden debe coincidir con las fotos.',
            ],
            [
                'name'       => 'Cargos/Especialidades',
                'id'         => 'teacher_positions',
                'type'       => 'text',
                'clone'      => true,
                'sort_clone' => true,
                'max_clone'  => 20,
                'add_button' => '+ Agregar Cargo',
                'size'       => 60,
                'placeholder' => 'Ej: Computer Science and Programming',
                'desc'       => 'Cargo o especialidad de cada profesor. El orden debe coincidir con las fotos y nombres.',
            ],
        ],
        'include' => [
            'page_template' => ['home.php'],
        ],
    ];

    return $meta_boxes;
}

// ============================================
// META BOX - REDES SOCIALES (GLOBAL)
// ============================================

add_filter( 'rwmb_meta_boxes', 'global_social_media_register_meta_boxes' );

function global_social_media_register_meta_boxes( $meta_boxes ) {
    
    $meta_boxes[] = [
        'title'      => 'Redes Sociales',
        'id'         => 'global_social_media',
        'post_types' => ['page'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'name'        => 'Instagram URL',
                'id'          => 'instagram',
                'type'        => 'url',
                'size'        => 60,
                'placeholder' => 'https://instagram.com/tu_usuario',
                'desc'        => 'URL completa de tu perfil de Instagram. Se aplica a todos los templates.',
            ],
            [
                'name'        => 'Facebook URL',
                'id'          => 'facebook',
                'type'        => 'url',
                'size'        => 60,
                'placeholder' => 'https://facebook.com/tu_pagina',
                'desc'        => 'URL completa de tu página de Facebook. Se aplica a todos los templates.',
            ],
            [
                'name'        => 'WhatsApp URL',
                'id'          => 'float_whatsapp_link',
                'type'        => 'url',
                'size'        => 60,
                'placeholder' => 'https://wa.me/0000000000',
                'desc'        => 'URL completa del enlace de WhatsApp para el botón flotante. Se aplica a todos los templates.',
            ],
        ],
    ];

    return $meta_boxes;
}



function get_section_video () {

    // Video Section

    $video_title_section = rwmb_meta('video_title_section') ?: 'Mira nuestro video';
    $video_url = rwmb_meta('video_url') ?: '';

    if(!empty($video_url)){
        // Extraer ID de YouTube
        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=))([\w-]+)/', $video_url, $matches);
        $youtube_id = $matches[1] ?? '';
        $video_embed_url = !empty($youtube_id) ? 'https://www.youtube.com/embed/' . $youtube_id : '';
    } else {
        $video_embed_url = '';
    }

  if(!empty($video_url) && !empty($video_embed_url)): ?>
    <!-- Video Area -->
    <section>
        <div class="area-video-container">

        <div class="container">
            <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="video-area">
                <h2 class="main-title fw-bold">💻 <?php echo esc_html($video_title_section); ?></h2>

                <iframe width="100%" height="400" src="<?php echo esc_url($video_embed_url); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    
                </div>
            </div>
            </div>
        </div>

        </div>
    </section>
    <!-- Video Area -->
  <?php endif;

}