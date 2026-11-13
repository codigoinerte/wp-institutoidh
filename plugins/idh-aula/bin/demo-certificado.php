<?php
/**
 * Demo de certificados generados (fase 2). Corre fuera de WordPress:
 *
 *   php bin/demo-certificado.php <carpeta-salida>
 *
 * Genera demo-taller.pdf (mismos datos que la referencia de PowerPoint),
 * demo-taller-largo.pdf (nombre y título largos, 2 firmantes), demo-clase.pdf,
 * demo-especializacion-ref.pdf (datos de la referencia de Canva) y demo-especializacion.pdf.
 */

if ( PHP_SAPI !== 'cli' ) {
    exit;
}

define( 'IDH_AULA_DIR', dirname( __DIR__ ) . '/' );
require IDH_AULA_DIR . 'includes/class-certificate-pdf.php';

$out = rtrim( $argv[1] ?? sys_get_temp_dir(), '/' ) . '/';
if ( ! is_dir( $out ) ) {
    mkdir( $out, 0775, true );
}

$assets = IDH_AULA_DIR . 'assets/certificados/azul/';
$erick  = [
    'name'      => 'Erick Núñez Vitor',
    'role'      => 'Gerente General',
    'signature' => $assets . 'firma.png',
    'seal'      => $assets . 'sello.png',
];

$demos = [
    'demo-taller' => [
        'student_name' => 'Sheyla Tatiana Caballero Bobadilla',
        'course_type'  => 'Curso taller',
        'course_title' => 'IV Seminario de Neurodiversidad Infantil TEA: Estrategias Conductuales, Sensoriales y de Comunicación Funcional',
        'period'       => 'durante el mes de octubre de 2026',
        'hours'        => '50',
        'signers'      => [ $erick ],
        'topics'       => [
            'Estrategias sensoriales en niños con TEA.',
            'Estrategias para favorecer la comunicación funcional en niños TEA.',
            'Herramientas conductuales en niños y niñas con TEA y TDAH.',
        ],
        'verify_url'   => 'https://idh.com.pe/verificar/K7M3QX9P',
    ],
    'demo-taller-largo' => [
        'student_name' => 'María de los Ángeles Fernández-Villanueva Quispe',
        'course_title' => 'Curso Taller: Intervención Temprana en Trastornos del Neurodesarrollo y Estrategias de Inclusión Educativa',
        'period'       => 'del 29 de agosto al 17 de octubre de 2026',
        'hours'        => '120',
        'signers'      => [
            $erick,
            [ 'name' => 'Dr. Juan Pérez Ramírez', 'role' => 'Decano', 'signature' => $assets . 'firma.png' ],
        ],
        'topics'       => [
            'Módulo I: Neurodesarrollo y detección temprana.',
            'Módulo II: Evaluación funcional y planes de intervención.',
            'Módulo III: Estrategias sensoriales y de comunicación.',
            'Módulo IV: Inclusión educativa y trabajo con la familia.',
            'Módulo V: Casos clínicos y supervisión.',
        ],
        'verify_url'   => 'https://idh.com.pe/verificar/ABCD2345',
    ],
    'demo-clase' => [
        'kind'          => 'session',
        'student_name'  => 'Sheyla Tatiana Caballero Bobadilla',
        'course_type'   => 'Curso taller',
        'course_title'  => 'IV Seminario de Neurodiversidad Infantil TEA',
        'session_title' => 'Estrategias sensoriales en niños con TEA',
        'period'        => 'el 10 de octubre de 2026',
        'signers'       => [ $erick ],
        'topics'        => [
            'Perfil sensorial y su evaluación en el aula.',
            'Adaptaciones del entorno y dietas sensoriales.',
        ],
        'verify_url'    => 'https://idh.com.pe/verificar/ZX9Q2R4T',
    ],
];

$dorado = IDH_AULA_DIR . 'assets/certificados/especializacion/';
$erick_dorado = [
    'name'      => 'Erick Nùñez Vitor',
    'role'      => 'Gerente General',
    'signature' => $dorado . 'firma.png',
    'seal'      => $dorado . 'sello.png',
];
$modulos = [
    'MODULO I: ABORDAJE COGNITIVO-CONDUCTUAL EN EL AUTISMO (TEA)',
    '- Introducciòn al TEA y Herramientas de Intervenciòn Conductual',
    '- Entrenamiento en Habilidades sociales',
    '- Transtornos de lenguaje en niños autista',
    '- Reducción de conductas desafiantes',
    'MODULO II: ABORDAJE COGNITIVO-CONDUCTUAL EN TDAH',
    '- Comprendiendo el TDAH y su impacto conductual',
    '- Tècnicas para mejorar la atenciòn y autorregulaciòn',
    '- Organizaciòn del comportamiento en el aula y en casa',
    '- Manejo emocional y resoluciòn de Conflictos',
];
// Reverso con mucho contenido: debe subir el bloque y dejar margen inferior.
$demos['demo-taller-contenido-largo'] = array_merge(
    $demos['demo-taller-largo'],
    [
        'topics' => array_map(
            static function ( $i ) {
                return "Tema $i: " . str_repeat( 'contenido del eje temático ', 1 + $i % 3 ) . 'y su aplicación práctica.';
            },
            range( 1, 14 )
        ),
    ]
);
// Modelo azul para presentar: otro alumno, mismos datos de curso que la referencia.
$demos['demo-taller-modelo'] = array_merge(
    $demos['demo-taller'],
    [
        'student_name' => 'Valeria Fernanda Quispe Huamán',
        'verify_url'   => 'https://idh.com.pe/verificar/H3J6K9L2',
    ]
);
// Calibración: mismos datos que la referencia de Canva (Julia Ricardina Ramos Cuadros).
$demos['demo-especializacion-ref'] = [
    'template'     => 'especializacion',
    'student_name' => 'Julia Ricardina Ramos Cuadros',
    'course_type'  => 'Curso de Especialización',
    'course_title' => 'Terapia Cognitivo-Conductual para Niños Neurodiversos: TEA - TDAH',
    'period'       => 'del 21 de noviembre de 2025 al 09 de enero de 2026',
    'hours'        => '120',
    'signers'      => [ $erick_dorado ],
    'topics'       => $modulos,
    'verify_url'   => 'https://idh.com.pe/verificar/Q4W8E2R6',
];
// Modelo para presentar: otro alumno.
$demos['demo-especializacion'] = array_merge(
    $demos['demo-especializacion-ref'],
    [
        'student_name' => 'Carlos Alberto Mendoza Salazar',
        'verify_url'   => 'https://idh.com.pe/verificar/M5N7P3T9',
    ]
);

foreach ( $demos as $file => $data ) {
    $t   = microtime( true );
    $pdf = IDH_Aula_Certificate_PDF::render( $data, $data['template'] ?? 'taller' );
    file_put_contents( $out . $file . '.pdf', $pdf );
    printf( "%s.pdf  %d KB  %.2fs\n", $file, strlen( $pdf ) / 1024, microtime( true ) - $t );
}
