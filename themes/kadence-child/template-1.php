<?php
/*
Template Name: Landing Page - Template 1
Template Post Type: page
*/

get_header('custom'); 

// Obtener valores de Meta Box - Redes Sociales (Global)
$instagram = rwmb_meta('instagram') ?: '#';
$facebook = rwmb_meta('facebook') ?: '#';
$float_whatsapp_link = rwmb_meta('float_whatsapp_link') ?: 'https://wa.me/0000000000';

// Obtener valores de Meta Box
$hero_title = rwmb_meta('hero_title') ?: 'Corporate & Business Site Template by Ayro UI.';
$hero_logo = rwmb_meta('hero_logo') ?: 'Logo Name';
$hero_subtitle = rwmb_meta('hero_subtitle') ?: 'We are a digital agency that helps brands to achieve their business outcomes. We see technology as a tool to create amazing things.';
$hero_whatsapp_link = rwmb_meta('hero_whatsapp_link') ?: 'https://wa.me/0000000000';
$hero_whatsapp_label = rwmb_meta('hero_whatsapp_label') ?: 'WhatsApp';
$webinar_date = rwmb_meta('webinar_date') ?: 'FRIDAY SEPTEMBER 20, 2024 20:30 PM';
$webinar_timestamp = strtotime($webinar_date);
$webinar_date_iso = $webinar_timestamp ? date('c', $webinar_timestamp) : '';

// Imagen Hero (Meta Box devuelve array con detalles de imagen)
$hero_image_data = rwmb_meta('hero_image', ['size' => 'full']);
$hero_image = !empty($hero_image_data) ? reset($hero_image_data)['full_url'] : get_stylesheet_directory_uri() . '/assets/template-1/images/header/hero-image.jpg';

// About Webinar Section
$about_main_title = rwmb_meta('about_main_title') ?: '¿QUÉ APRENDERÁS?';
$about_image_data = rwmb_meta('about_image', ['size' => 'full']);
$about_image = !empty($about_image_data) ? reset($about_image_data)['full_url'] : get_stylesheet_directory_uri() . '/assets/template-1/images/about/about-img1.jpg';

$about_tab1_title = rwmb_meta('about_tab1_title');
$about_tab1_content = rwmb_meta('about_tab1_content');
$about_tab2_title = rwmb_meta('about_tab2_title');
$about_tab2_content = rwmb_meta('about_tab2_content');
$about_tab3_title = rwmb_meta('about_tab3_title');
$about_tab3_content = rwmb_meta('about_tab3_content');

// Call Action Section
$call_action_text = rwmb_meta('call_action_text') ?: 'Una vez ingrese a la comunidad o grupo solo debe esperar a que el administrador comparta toda la información, NO DEBE atender llamadas ni mensajes de personas diferentes';
$call_action_link = rwmb_meta('call_action_link') ?: $hero_whatsapp_link;
$call_action_label = rwmb_meta('call_action_label') ?: $hero_whatsapp_label;


// Exponente Section
$exponente_show = rwmb_meta('exponente_show');
$exponente_title = rwmb_meta('exponente_title') ?: 'Exponente';
$exponente_images_raw = rwmb_meta('exponente_images', ['size' => 'full']);
$exponente_names_raw = rwmb_meta('exponente_names');
$exponente_descriptions_raw = rwmb_meta('exponente_descriptions');

$exponente_images_raw = is_array($exponente_images_raw) ? $exponente_images_raw : [];
$exponente_names_raw = is_array($exponente_names_raw) ? $exponente_names_raw : [];
$exponente_descriptions_raw = is_array($exponente_descriptions_raw) ? $exponente_descriptions_raw : [];

$get_exponente_image_url = function ($image_value) {
  if (empty($image_value)) {
    return '';
  }
  if (is_numeric($image_value)) {
    $url = wp_get_attachment_image_url((int) $image_value, 'full');
    return $url ? $url : '';
  }
  if (is_array($image_value)) {
    if (!empty($image_value['full_url'])) {
      return $image_value['full_url'];
    }
    if (!empty($image_value['url'])) {
      return $image_value['url'];
    }
    if (!empty($image_value['ID'])) {
      $url = wp_get_attachment_image_url((int) $image_value['ID'], 'full');
      return $url ? $url : '';
    }
    $first = reset($image_value);
    if (is_array($first)) {
      if (!empty($first['full_url'])) {
        return $first['full_url'];
      }
      if (!empty($first['url'])) {
        return $first['url'];
      }
      if (!empty($first['ID'])) {
        $url = wp_get_attachment_image_url((int) $first['ID'], 'full');
        return $url ? $url : '';
      }
    }
    if (is_numeric($first)) {
      $url = wp_get_attachment_image_url((int) $first, 'full');
      return $url ? $url : '';
    }
  }
  return '';
};

// Temario Section
$temario_title = rwmb_meta('temario_title') ?: 'Conoce nuestro temario';
$temario_modules_raw = rwmb_meta('temario_modules');
$temario_modules = [];
$parse_temario_module = function ($module_text) {
  $lines = preg_split('/\r\n|\r|\n/', (string) $module_text);
  $lines = array_values(array_filter(array_map('trim', $lines), 'strlen'));
  if (empty($lines)) {
    return null;
  }
  $title = array_shift($lines);
  return [
    'title' => $title,
    'topics' => $lines,
  ];
};

if (is_array($temario_modules_raw)) {
  foreach ($temario_modules_raw as $module_text) {
    $module = $parse_temario_module($module_text);
    if ($module) {
      $temario_modules[] = $module;
    }
  }
} elseif (!empty($temario_modules_raw)) {
  $module = $parse_temario_module($temario_modules_raw);
  if ($module) {
    $temario_modules[] = $module;
  }
}

if (empty($temario_modules)) {
  $temario_modules = [
    [
      'title' => 'Psicopatología en la Infancia',
      'topics' => [
        'Psicopatología de la percepción',
        'Trastorno del pensamiento',
        'Psicopatología del Lenguaje',
        'Psicopatología de la Afectividad',
      ],
    ],
    [
      'title' => 'Psicopatología en la Adolescencia',
      'topics' => [
        'Psicopatología de la Psicomotricidad',
        'Psicopatología del sueño',
        'Psicopatología de la Inteligencia',
        'Psicopatología (visión integradora)',
      ],
    ],
    [
      'title' => 'Estrategias de Intervención en Psicología del Vínculo',
      'topics' => [
        'Terapia cognitiva',
        'Terapia Conductual',
        'Terapia Sistémica',
        'Terapia Humanista',
      ],
    ],
    [
      'title' => 'Herramientas de Evaluación en Infancia Temprana',
      'topics' => [
        'Evaluación del Desarrollo Infantil',
        'Evaluación del Desarrollo socioemocional',
        'Evaluación del Temperamento',
        'Evaluación de Interacciones Tempranas',
      ],
    ],
    [
      'title' => 'Observación y Mediciones Clínicas para Adolescentes',
      'topics' => [
        'Evaluación de Sensibilidad',
        'Evaluación del Patrón de Apego',
        'Evaluación de Sintomatología',
        'Evaluación de la calidad vincular',
      ],
    ],
  ];
}

// WhatsApp Button (usado en about-webinar, temario y call-action)
$about_whatsapp_link = rwmb_meta('about_whatsapp_link') ?: 'https://wa.me/51999999999';
$about_whatsapp_label = rwmb_meta('about_whatsapp_label') ?: 'Unirse al grupo de WhatsApp';

// Contar tabs activos
$active_tabs = [];
if (!empty($about_tab1_title)) $active_tabs[] = 1;
if (!empty($about_tab2_title)) $active_tabs[] = 2;
if (!empty($about_tab3_title)) $active_tabs[] = 3;
$first_active_tab = !empty($active_tabs) ? $active_tabs[0] : 0;
?>


  <!--====== NAVBAR NINE PART START ======-->

  <section class="navbar-area navbar-nine">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand text-white d-flex flex-row gap-3" href="<?php echo home_url('/'); ?>">
              <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/global/images/logo.png" alt="Logo" />
              <span class="items-center justify-content-center font-bold"><?php echo esc_html($hero_logo); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNine"
              aria-controls="navbarNine" aria-expanded="false" aria-label="Toggle navigation">
              <span class="toggler-icon"></span>
              <span class="toggler-icon"></span>
              <span class="toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse sub-menu-bar visible" id="navbarNine">
              <ul class="navbar-nav me-auto">
                <li class="nav-item">
                  <a class="page-scroll active" href="#hero-area">Inicio</a>
                </li>
                
                <li class="nav-item">
                  <a class="page-scroll" href="#about-webinar">¿Que aprenderas?</a>
                </li>

                <li class="nav-item">
                  <a class="page-scroll" href="#temario">Temario</a>
                </li>

                <li class="nav-item">
                  <a class="page-scroll" href="#exponente">Exponente</a>
                </li>

              </ul>
            </div>

            <div class="navbar-btn d-none d-lg-inline-block">
              <a class="menu-bar" href="#side-menu-left"><i class="lni lni-menu"></i></a>
            </div>
          </nav>
          <!-- navbar -->
        </div>
      </div>
      <!-- row -->
    </div>
    <!-- container -->
  </section>

  <!--====== NAVBAR NINE PART ENDS ======-->

  <!--====== SIDEBAR PART START ======-->

  <div class="sidebar-left">
    <div class="sidebar-close">
      <a class="close" href="#close"><i class="lni lni-close"></i></a>
    </div>
    <div class="sidebar-content">
      <div class="sidebar-logo">
        <a href="index.html"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/template-1/images/logo.svg" alt="Logo" /></a>
      </div>
      <p class="text">Lorem ipsum dolor sit amet adipisicing elit. Sapiente fuga nisi rerum iusto intro.</p>
      <!-- logo -->
      <div class="sidebar-menu">
        <h5 class="menu-title">Quick Links</h5>
        <ul>
          <li><a href="javascript:void(0)">About Us</a></li>
          <li><a href="javascript:void(0)">Our Team</a></li>
          <li><a href="javascript:void(0)">Latest News</a></li>
          <li><a href="javascript:void(0)">Contact Us</a></li>
        </ul>
      </div>
      <!-- menu -->
      <div class="sidebar-social align-items-center justify-content-center">
        <h5 class="social-title">Follow Us On</h5>
        <ul>
          <li>
            <a href="javascript:void(0)"><i class="lni lni-facebook-filled"></i></a>
          </li>
          <li>
            <a href="javascript:void(0)"><i class="lni lni-twitter-original"></i></a>
          </li>
          <li>
            <a href="javascript:void(0)"><i class="lni lni-linkedin-original"></i></a>
          </li>
          <li>
            <a href="javascript:void(0)"><i class="lni lni-youtube"></i></a>
          </li>
        </ul>
      </div>
      <!-- sidebar social -->
    </div>
    <!-- content -->
  </div>
  <div class="overlay-left"></div>

  <!--====== SIDEBAR PART ENDS ======-->

  <!-- Start header Area -->
  <section id="hero-area" class="header-area header-eight">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 col-md-12 col-12">
          <div class="header-content">
            <h1><?php echo esc_html($hero_title); ?></h1>
            <?php echo $hero_subtitle; ?>
            <div class="whatsapp-hero-container mt-5 m-auto">
              <a href="<?php echo esc_url($hero_whatsapp_link); ?>" class="btn whatsapp-button animate__animated animate__pulse animate__infinite infinite" target="_blank" rel="noopener">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                  <path 
                    d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
                    fill="currentColor"
                  />
                </svg>
                <?php echo esc_html($hero_whatsapp_label); ?>
              </a>
            </div>

            <!-- countdown -->
            <div class="countdown" data-target="<?php echo esc_attr($webinar_date_iso); ?>">
              <div class="countdown-item">
                <span class="countdown-number" data-unit="days">00</span>
                <span class="countdown-label">Dias</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" data-unit="hours">00</span>
                <span class="countdown-label">Horas</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" data-unit="minutes">00</span>
                <span class="countdown-label">Minutos</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" data-unit="seconds">00</span>
                <span class="countdown-label">Segundos</span>
              </div>
            </div>
            <script>
              (function () {
                var countdownEl = document.querySelector('.countdown[data-target]');
                if (!countdownEl) {
                  return;
                }

                var targetStr = countdownEl.getAttribute('data-target');
                if (!targetStr) {
                  return;
                }

                var targetDate = new Date(targetStr);
                if (isNaN(targetDate.getTime())) {
                  return;
                }

                var units = {
                  days: 86400000,
                  hours: 3600000,
                  minutes: 60000,
                  seconds: 1000
                };

                var update = function () {
                  var now = new Date();
                  var diff = targetDate - now;
                  if (diff < 0) {
                    diff = 0;
                  }

                  var days = Math.floor(diff / units.days);
                  diff -= days * units.days;
                  var hours = Math.floor(diff / units.hours);
                  diff -= hours * units.hours;
                  var minutes = Math.floor(diff / units.minutes);
                  diff -= minutes * units.minutes;
                  var seconds = Math.floor(diff / units.seconds);

                  var map = {
                    days: days,
                    hours: hours,
                    minutes: minutes,
                    seconds: seconds
                  };

                  countdownEl.querySelectorAll('[data-unit]').forEach(function (el) {
                    var key = el.getAttribute('data-unit');
                    var value = map[key] || 0;
                    el.textContent = String(value).padStart(2, '0');
                  });
                };

                update();
                setInterval(update, 1000);
              })();
            </script>
            <!-- countdown -->

          </div>
        </div>
        <div class="col-lg-6 col-md-12 col-12">
          <div class="header-image ">
            <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_title); ?>" />
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- End header Area -->


  <!--====== ABOUT FIVE PART START ======-->

  <section id="about-webinar" class="about-area about-five">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 col-12">
          <div class="about-image-five">
            <svg class="shape" width="106" height="134" viewBox="0 0 106 134" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <circle cx="1.66654" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="132" r="1.66667" fill="#DADADA" />
            </svg>
            <img src="<?php echo esc_url($about_image); ?>" alt="<?php echo esc_attr($about_main_title); ?>" />
          </div>
        </div>
        <div class="col-lg-6 col-12">
          <div class="about-five-content">
            <h2 class="main-title fw-bold"><?php echo esc_html($about_main_title); ?></h2>
            <?php if (count($active_tabs) > 0): ?>
            <div class="about-five-tab">
              <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                  <?php if (!empty($about_tab1_title)): ?>
                  <button class="nav-link <?php echo ($first_active_tab === 1) ? 'active' : ''; ?>" id="nav-tab1" data-bs-toggle="tab" data-bs-target="#nav-content1"
                    type="button" role="tab" aria-controls="nav-content1" aria-selected="<?php echo ($first_active_tab === 1) ? 'true' : 'false'; ?>"><?php echo esc_html($about_tab1_title); ?></button>
                  <?php endif; ?>
                  <?php if (!empty($about_tab2_title)): ?>
                  <button class="nav-link <?php echo ($first_active_tab === 2) ? 'active' : ''; ?>" id="nav-tab2" data-bs-toggle="tab" data-bs-target="#nav-content2"
                    type="button" role="tab" aria-controls="nav-content2" aria-selected="<?php echo ($first_active_tab === 2) ? 'true' : 'false'; ?>"><?php echo esc_html($about_tab2_title); ?></button>
                  <?php endif; ?>
                  <?php if (!empty($about_tab3_title)): ?>
                  <button class="nav-link <?php echo ($first_active_tab === 3) ? 'active' : ''; ?>" id="nav-tab3" data-bs-toggle="tab" data-bs-target="#nav-content3"
                    type="button" role="tab" aria-controls="nav-content3" aria-selected="<?php echo ($first_active_tab === 3) ? 'true' : 'false'; ?>"><?php echo esc_html($about_tab3_title); ?></button>
                  <?php endif; ?>
                </div>
              </nav>
              <div class="tab-content" id="nav-tabContent">
                <?php if (!empty($about_tab1_title)): ?>
                <div class="tab-pane fade <?php echo ($first_active_tab === 1) ? 'show active' : ''; ?>" id="nav-content1" role="tabpanel" aria-labelledby="nav-tab1">
                  <?php echo wpautop($about_tab1_content); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($about_tab2_title)): ?>
                <div class="tab-pane fade <?php echo ($first_active_tab === 2) ? 'show active' : ''; ?>" id="nav-content2" role="tabpanel" aria-labelledby="nav-tab2">
                  <?php echo wpautop($about_tab2_content); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($about_tab3_title)): ?>
                <div class="tab-pane fade <?php echo ($first_active_tab === 3) ? 'show active' : ''; ?>" id="nav-content3" role="tabpanel" aria-labelledby="nav-tab3">
                  <?php echo wpautop($about_tab3_content); ?>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($hero_whatsapp_link); ?>" class="btn whatsapp-button mt-5 w-fit-content animate__animated animate__pulse animate__infinite infinite" target="_blank" rel="noopener noreferrer">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                  <path 
                    d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
                    fill="currentColor"
                  />
              </svg>
              <?php echo esc_html($hero_whatsapp_label); ?>
            </a>

          </div>
        </div>
      </div>
    </div>
    <!-- container -->
  </section>

  <!--====== ABOUT FIVE PART ENDS ======-->


  <!-- ===== service-area start ===== -->
  <section id="temario" class="services-area services-eight">
    <!--======  Start Section Title Five ======-->
    <div class="section-title-five">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="content">
              <h2 class="fw-bold"><?php echo esc_html($temario_title); ?></h2>
            </div>
          </div>
        </div>
        <!-- row -->
        
      </div>
      <!-- container -->
    </div>
    <!--======  End Section Title Five ======-->
    <div class="container">
      <div class="row">
        <?php if (!empty($temario_modules)) : ?>
          <?php foreach ($temario_modules as $module_index => $module) : ?>
            <?php
              $module_title = isset($module['title']) ? trim($module['title']) : '';
              $topics = isset($module['topics']) ? (array) $module['topics'] : [];
              $topics = array_values(array_filter(array_map('trim', $topics)));
              if ($module_title === '' && empty($topics)) {
                  continue;
              }
              $module_heading = '';
              if ($module_title !== '') {
                  $module_heading .= $module_title;
              }
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="single-services">
                <div class="service-content">
                  <h4><?php echo esc_html($module_heading); ?></h4>
                  <?php if (!empty($topics)) : ?>
                    <ul style="list-style: none; padding-left: 0;">
                      <?php foreach ($topics as $topic_index => $topic) : ?>
                        <li style="display: flex; align-items: flex-start; margin-bottom: 8px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px; margin-top: 2px; flex-shrink: 0; color: #4CAF50;"><polyline points="20 6 9 17 4 12"></polyline></svg><span><strong><?php echo esc_html('Tema ' . ($topic_index + 1) . ':'); ?></strong> <?php echo esc_html($topic); ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
          <a href="<?php echo esc_url($hero_whatsapp_link); ?>" class="btn whatsapp-button mt-5 w-fit-content animate__animated animate__pulse animate__infinite infinite" target="_blank" rel="noopener noreferrer">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                  <path 
                    d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
                    fill="currentColor"
                  />
              </svg>
              <?php echo esc_html($hero_whatsapp_label); ?>
            </a>
        </div>
      </div>
    </div>
  </section>
  <!-- ===== service-area end ===== -->

  <!-- Start Latest News Area -->
  <?php if ($exponente_show) : ?>
  <div id="exponente" class="latest-news-area section">
    <!--======  Start Section Title Five ======-->
    <div class="section-title-five">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="content">
              <h2 class="fw-bold"><?php echo esc_html($exponente_title); ?></h2>
            </div>
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
    </div>
    <!--======  End Section Title Five ======-->
    <div class="container">
      <div class="row justify-content-center">
        <?php
          $exponente_defaults = [
            [
              'image' => get_stylesheet_directory_uri() . '/assets/template-1/images/blog/1.jpg',
              'name' => 'Exponente 1',
              'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard.',
            ],
            [
              'image' => get_stylesheet_directory_uri() . '/assets/template-1/images/blog/2.jpg',
              'name' => 'Exponente 2',
              'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard.',
            ],
            [
              'image' => get_stylesheet_directory_uri() . '/assets/template-1/images/blog/3.jpg',
              'name' => 'Exponente 3',
              'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard.',
            ],
          ];

          $exponente_count = max(count($exponente_images_raw), count($exponente_names_raw), count($exponente_descriptions_raw));
          if ($exponente_count === 0) {
            $exponente_count = count($exponente_defaults);
          }
        ?>
        <?php for ($i = 0; $i < $exponente_count; $i++) : ?>
          <?php
            $image_value = $exponente_images_raw[$i] ?? '';
            $image_url = $get_exponente_image_url($image_value);
            $name = isset($exponente_names_raw[$i]) ? trim($exponente_names_raw[$i]) : '';
            $description = isset($exponente_descriptions_raw[$i]) ? trim($exponente_descriptions_raw[$i]) : '';

            if ($image_url === '' && $name === '' && $description === '') {
              $fallback = $exponente_defaults[$i] ?? null;
              if ($fallback) {
                $image_url = $fallback['image'];
                $name = $fallback['name'];
                $description = $fallback['description'];
              } else {
                continue;
              }
            }
          ?>
          <div class="col-lg-4 col-md-6 col-12">
            <!-- Single News -->
            <div class="single-news">
              <?php if ($image_url !== '') : ?>
                <div class="image">
                  <img class="thumb" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($name); ?>" />
                </div>
              <?php endif; ?>
              <div class="content-body">
                <?php if ($name !== '') : ?>
                  <h4 class="title">
                    <?php echo esc_html($name); ?>
                  </h4>
                <?php endif; ?>
                <?php if ($description !== '') : ?>
                  <p>
                    <?php echo esc_html($description); ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
            <!-- End Single News -->
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <!-- End Latest News Area -->

  <?php get_section_video(); ?>

  <section id="call-action" class="call-action">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-9">
          <div class="inner-content">
            <h3><?php echo esc_html($call_action_text); ?></h3>
            <a href="<?php echo esc_url($call_action_link); ?>" class="btn whatsapp-button animate__animated animate__pulse animate__infinite infinite">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                  <path 
                    d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
                    fill="currentColor"
                  />
              </svg>
              <?php echo esc_html($call_action_label); ?>
            </a>
            
          </div>
        </div>
      </div>
    </div>
  </section>

  <a href="<?php echo esc_url($float_whatsapp_link); ?>" target="_blank" class="float-whatsapp-button animate__animated animate__pulse animate__infinite infinite">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
        <path 
          d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
          fill="currentColor"
        />
    </svg>
  </a>

  <a href="<?php echo esc_url($instagram??'#'); ?>" target="_blank" class="float-instagram-button animate__animated animate__pulse animate__infinite infinite">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
        <path 
          d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" 
          fill="currentColor"
        />
    </svg>
  </a>

  <a href="<?php echo esc_url($facebook??'#'); ?>" target="_blank" class="float-facebook-button animate__animated animate__pulse animate__infinite infinite">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
        <path 
          d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" 
          fill="currentColor"
        />
    </svg>
  </a>

  <a href="#" class="scroll-top btn-hover">
    <i class="lni lni-chevron-up"></i>
  </a>

<?php get_footer(); ?>

