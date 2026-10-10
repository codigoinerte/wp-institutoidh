<?php
/**
 * Plugin Name: IDH Aula Virtual
 * Description: Portal de alumnos (acceso por DNI) con cursos, clases, materiales, certificados, bloqueos y reportes.
 * Version:     0.1.0
 * Author:      Fredy Martinez
 * Text Domain: idh-aula
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IDH_AULA_VERSION', '0.1.0' );
define( 'IDH_AULA_FILE', __FILE__ );
define( 'IDH_AULA_DIR', plugin_dir_path( __FILE__ ) );
define( 'IDH_AULA_URL', plugin_dir_url( __FILE__ ) );
define( 'IDH_AULA_SLUG', 'aula-virtual' );
define( 'IDH_AULA_CAP', 'manage_options' );

require_once IDH_AULA_DIR . 'includes/helpers.php';
require_once IDH_AULA_DIR . 'includes/class-schema.php';
require_once IDH_AULA_DIR . 'includes/class-categories.php';
require_once IDH_AULA_DIR . 'includes/class-documents.php';
require_once IDH_AULA_DIR . 'includes/class-repo-students.php';
require_once IDH_AULA_DIR . 'includes/class-repo-courses.php';
require_once IDH_AULA_DIR . 'includes/class-repo-resources.php';
require_once IDH_AULA_DIR . 'includes/class-repo-enrollments.php';
require_once IDH_AULA_DIR . 'includes/class-repo-certificates.php';
require_once IDH_AULA_DIR . 'includes/class-reports.php';
require_once IDH_AULA_DIR . 'includes/class-csv.php';
require_once IDH_AULA_DIR . 'includes/class-auth.php';
require_once IDH_AULA_DIR . 'includes/class-router.php';

register_activation_hook( __FILE__, 'idh_aula_activate' );
register_deactivation_hook( __FILE__, 'idh_aula_deactivate' );

function idh_aula_activate() {
    IDH_Aula_Schema::install();
    IDH_Aula_Certificates::ensure_storage();
    IDH_Aula_Router::register_rules();
    flush_rewrite_rules();
}

function idh_aula_deactivate() {
    flush_rewrite_rules();
}

add_action( 'plugins_loaded', [ 'IDH_Aula_Schema', 'maybe_upgrade' ] );

/**
 * Tras un deploy (git pull / rsync) el plugin no se reactiva: si cambió la versión,
 * se regeneran las reglas de rewrite para que las rutas nuevas funcionen sin pasos manuales.
 */
add_action(
    'init',
    static function () {
        if ( get_option( 'idh_aula_rewrite_version' ) !== IDH_AULA_VERSION ) {
            flush_rewrite_rules( false );
            update_option( 'idh_aula_rewrite_version', IDH_AULA_VERSION );
        }
    },
    99
);
IDH_Aula_Router::init();

if ( is_admin() ) {
    require_once IDH_AULA_DIR . 'admin/class-admin.php';
    require_once IDH_AULA_DIR . 'admin/class-copy-link.php';
    IDH_Aula_Admin::init();
    IDH_Aula_Copy_Link::init();
}
