<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IDH_Aula_Schema {

    const DB_VERSION = '1.0.0';
    const OPTION     = 'idh_aula_db_version';

    public static function maybe_upgrade() {
        if ( get_option( self::OPTION ) !== self::DB_VERSION ) {
            self::install();
        }
    }

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $students     = idh_aula_table( 'students' );
        $courses      = idh_aula_table( 'courses' );
        $resources    = idh_aula_table( 'resources' );
        $sessions     = idh_aula_table( 'sessions' );
        $session_res  = idh_aula_table( 'session_resources' );
        $enrollments  = idh_aula_table( 'enrollments' );
        $certificates = idh_aula_table( 'certificates' );

        // dbDelta exige dos espacios antes de la definición de PRIMARY KEY y un campo por línea.
        $sql = [];

        $sql[] = "CREATE TABLE {$students} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  doc_type varchar(20) NOT NULL DEFAULT 'DNI',
  doc_number varchar(20) NOT NULL,
  first_name varchar(120) NOT NULL DEFAULT '',
  last_name varchar(120) NOT NULL DEFAULT '',
  birth_date date DEFAULT NULL,
  phone varchar(30) NOT NULL DEFAULT '',
  email varchar(190) NOT NULL DEFAULT '',
  status varchar(20) NOT NULL DEFAULT 'active',
  blocked_reason varchar(255) NOT NULL DEFAULT '',
  notes text NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY doc (doc_type,doc_number),
  KEY doc_number (doc_number),
  KEY status (status)
) {$charset};";

        $sql[] = "CREATE TABLE {$courses} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  slug varchar(200) NOT NULL DEFAULT '',
  category varchar(100) NOT NULL DEFAULT '',
  description text NULL,
  cover_image_id bigint(20) unsigned NOT NULL DEFAULT 0,
  hours varchar(50) NOT NULL DEFAULT '',
  instructor varchar(190) NOT NULL DEFAULT '',
  price decimal(10,2) NOT NULL DEFAULT 0.00,
  landing_page_id bigint(20) unsigned NOT NULL DEFAULT 0,
  start_date date DEFAULT NULL,
  status varchar(20) NOT NULL DEFAULT 'published',
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY category (category),
  KEY status (status)
) {$charset};";

        $sql[] = "CREATE TABLE {$resources} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  type varchar(20) NOT NULL DEFAULT 'link',
  title varchar(255) NOT NULL,
  url text NOT NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY type (type)
) {$charset};";

        $sql[] = "CREATE TABLE {$sessions} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  course_id bigint(20) unsigned NOT NULL,
  position int(11) NOT NULL DEFAULT 0,
  title varchar(255) NOT NULL DEFAULT '',
  description text NULL,
  session_datetime datetime DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY course_id (course_id)
) {$charset};";

        $sql[] = "CREATE TABLE {$session_res} (
  session_id bigint(20) unsigned NOT NULL,
  resource_id bigint(20) unsigned NOT NULL,
  position int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (session_id,resource_id),
  KEY resource_id (resource_id)
) {$charset};";

        $sql[] = "CREATE TABLE {$enrollments} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  student_id bigint(20) unsigned NOT NULL,
  course_id bigint(20) unsigned NOT NULL,
  payment_status varchar(20) NOT NULL DEFAULT 'pending',
  price_paid decimal(10,2) NOT NULL DEFAULT 0.00,
  locked tinyint(1) NOT NULL DEFAULT 0,
  lock_reason varchar(255) NOT NULL DEFAULT '',
  enrolled_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY student_course (student_id,course_id),
  KEY course_id (course_id),
  KEY payment_status (payment_status)
) {$charset};";

        $sql[] = "CREATE TABLE {$certificates} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  enrollment_id bigint(20) unsigned NOT NULL,
  code varchar(32) NOT NULL,
  mode varchar(20) NOT NULL DEFAULT 'url',
  external_url text NULL,
  pdf_path varchar(255) NOT NULL DEFAULT '',
  pdf_name varchar(255) NOT NULL DEFAULT '',
  verify_url text NULL,
  issued_at date NOT NULL,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY enrollment_id (enrollment_id),
  UNIQUE KEY code (code),
  KEY issued_at (issued_at)
) {$charset};";

        foreach ( $sql as $statement ) {
            dbDelta( $statement );
        }

        update_option( self::OPTION, self::DB_VERSION );
    }
}
