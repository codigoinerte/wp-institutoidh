<?php
/*
Template Name: Landing Page - Template 2
Template Post Type: page
*/

get_header("custom"); 

// Obtener valores de Meta Box - Redes Sociales (Global)
$instagram = rwmb_meta('instagram') ?: '#';
$facebook = rwmb_meta('facebook') ?: '#';
$float_whatsapp_link = rwmb_meta('float_whatsapp_link') ?: 'https://wa.me/0000000000';

// Obtener valores de Meta Box - Hero Section (misma estructura de campos que el template 1)
$hero_title = rwmb_meta('hero_title') ?: '';
$hero_subtitle = rwmb_meta('hero_subtitle') ?: '';
$hero_whatsapp_link = rwmb_meta('hero_whatsapp_link') ?: 'https://wa.me/0000000000';
$hero_whatsapp_label = rwmb_meta('hero_whatsapp_label') ?: 'WhatsApp';
$webinar_date = rwmb_meta('webinar_date') ?: '';
$webinar_timestamp = $webinar_date ? strtotime($webinar_date) : 0;
$webinar_date_iso = $webinar_timestamp ? date('c', $webinar_timestamp) : '';
$hero_image_data = rwmb_meta('hero_image', ['size' => 'full']);
$hero_image = !empty($hero_image_data) ? reset($hero_image_data)['full_url'] : get_stylesheet_directory_uri() . '/assets/template-2/images/image3.jpg';

// About Webinar Section (¿Qué aprenderás?)
$about_main_title = rwmb_meta('about_main_title') ?: '';
$about_image_data = rwmb_meta('about_image', ['size' => 'full']);
$about_image = !empty($about_image_data) ? reset($about_image_data)['full_url'] : '';
$about_tabs = [];
foreach ([1, 2, 3] as $tab_number) {
  $tab_title = trim((string) rwmb_meta('about_tab' . $tab_number . '_title'));
  if ($tab_title !== '') {
    $about_tabs[] = [
      'title' => $tab_title,
      'content' => rwmb_meta('about_tab' . $tab_number . '_content'),
    ];
  }
}

// Temario Section (módulos: primera línea = título, siguientes = temas)
$temario_title = rwmb_meta('temario_title') ?: '';
$temario_modules_raw = rwmb_meta('temario_modules');
$temario_modules_raw = is_array($temario_modules_raw) ? $temario_modules_raw : (empty($temario_modules_raw) ? [] : [$temario_modules_raw]);
$temario_modules = [];
foreach ($temario_modules_raw as $module_text) {
  $lines = preg_split('/\r\n|\r|\n/', (string) $module_text);
  $lines = array_values(array_filter(array_map('trim', $lines), 'strlen'));
  if (empty($lines)) {
    continue;
  }
  $temario_modules[] = [
    'title' => array_shift($lines),
    'topics' => $lines,
  ];
}

// Exponente Section (imagen, nombre y descripción por exponente)
$exponente_show = rwmb_meta('exponente_show');
$exponente_title = rwmb_meta('exponente_title') ?: '';
$exponente_images_raw = rwmb_meta('exponente_images', ['size' => 'full']);
$exponente_names_raw = rwmb_meta('exponente_names');
$exponente_descriptions_raw = rwmb_meta('exponente_descriptions');
$exponente_images_raw = is_array($exponente_images_raw) ? $exponente_images_raw : [];
$exponente_names_raw = is_array($exponente_names_raw) ? $exponente_names_raw : [];
$exponente_descriptions_raw = is_array($exponente_descriptions_raw) ? $exponente_descriptions_raw : [];

$get_exponente_image_url = function ($image_value) {
  while (is_array($image_value) && !isset($image_value['full_url']) && !isset($image_value['url']) && !isset($image_value['ID']) && !empty($image_value)) {
    $image_value = reset($image_value);
  }
  if (is_array($image_value)) {
    if (!empty($image_value['full_url'])) {
      return $image_value['full_url'];
    }
    if (!empty($image_value['url'])) {
      return $image_value['url'];
    }
    $image_value = $image_value['ID'] ?? '';
  }
  if (is_numeric($image_value) && (int) $image_value > 0) {
    $url = wp_get_attachment_image_url((int) $image_value, 'full');
    return $url ? $url : '';
  }
  return '';
};

$exponentes = [];
$exponente_count = max(count($exponente_images_raw), count($exponente_names_raw), count($exponente_descriptions_raw));
for ($i = 0; $i < $exponente_count; $i++) {
  $item = [
    'image' => $get_exponente_image_url($exponente_images_raw[$i] ?? ''),
    'name' => trim((string) ($exponente_names_raw[$i] ?? '')),
    'description' => trim((string) ($exponente_descriptions_raw[$i] ?? '')),
  ];
  if ($item['image'] !== '' || $item['name'] !== '' || $item['description'] !== '') {
    $exponentes[] = $item;
  }
}

// Call Action Section
$call_action_text = rwmb_meta('call_action_text') ?: '';
$call_action_link = rwmb_meta('call_action_link') ?: $hero_whatsapp_link;
$call_action_label = rwmb_meta('call_action_label') ?: $hero_whatsapp_label;

?>

  <div class="page-background">
  </div>
  <div class="page">

  <!-- header/cabecera section -->
  <section class="section css-1e56whp" data-guid="54d6e9f3-e83e-0062-93ee-8c7485f917e5">
  <div class="container" id="top-menu">
    <div class="layout flex flex--12 flex--xs-between" data-guid="fc673c22-e24b-0523-5dc1-c45b29a8238d">
    <div class="column flex__item--md-4 flex__item--xs-12 css-1dnlddl" data-guid="bf15570e-72eb-96eb-5e79-22ea51493f9c">
      <div class="inner-column css-efs6e5">
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="158e6cbd-5d5e-2f94-83ec-26f2b6759be4" style="margin-bottom:0px">
              <div class="css-1yltisf">
              <div>
                <img 
                  alt="" 
                  class="lp-image-react w-158e6cbd-5d5e-2f94-83ec-26f2b6759be4 css-1h3hmed" 
                  data-image-upload-source="builder3" 
                  src="<?php echo get_stylesheet_directory_uri(); ?>/assets/global/images/logo.png" style="width: 100%; max-width: 80px"/>
              </div>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      </div>
    </div>
    
    <div class="column flex__item--md-4 flex__item--xs-12 css-1dnlddl" data-guid="5ac9dcfd-84e9-ca6a-e177-a477a795182a">
      <div class="inner-column css-efs6e5" style="justify-content:center;align-items:center;display:flex;width:100%;">
      <div class="row flex flex--12 css-1v1axsl" style="width:100%;">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="f47b9298-43ad-ac67-7087-933ccc6e9197" style="margin-bottom:0px;">
              <div class="lp-button-react-wrapper css-1yltisf" data-widget-type="LpButtonReact">
              <a class="lp-button-react w-f47b9298-43ad-ac67-7087-933ccc6e9197 lp-button-react--full is-bold lp-button-react--small lp-button-react--line font-scale-6 line-height-scale-6 css-dnk8ig whatsapp-button animate__animated animate__pulse animate__infinite infinite" contenteditable="false" data-link-type="leadbox" data-widget-link="true" href="<?php echo esc_url($hero_whatsapp_link); ?>" target="_top">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" class="css-1h3hmed" style="margin-right: 8px;">
                    <path 
                      d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
                      fill="currentColor"
                    />
                </svg>
                <b>
                <?php echo esc_html($hero_whatsapp_label); ?>
                </b>
              </a>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </div>
  </section>

  <!-- hero section -->
  <section class="has-background-image has-background-size-cover section css-1587gfd" data-background="assets/images/image2-bg.jpg" data-guid="a6558f5e-2624-d4b5-f806-e7543531d1b6">
  <div class="container" id="hero">
    <div class="layout flex flex--12" data-guid="d80655b9-08f7-55d4-3165-898744aeda6c">
    <div class="column flex__item--md-12 flex__item--xs-12 css-l31ui" data-guid="d8a585f3-4c07-106a-ad38-8d72938b3a36">
      <div class="inner-column css-1r12rvd">
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="2c1bb77f-d561-43ff-36c5-e851052ca3a3">
              <div class="lp-text-react lp-headline-react" data-widget-type="LpTextReact">
              <h1 class="lp-headline text-align-center headline">
                <?php echo esc_html($hero_title); ?>
              </h1>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="699d771e-7cdb-5566-13e2-b16e78b47dc1">
              <div class="lp-video-react w-699d771e-7cdb-5566-13e2-b16e78b47dc1 css-ypxerw has-content hero-image-wrap">
              <div>
                <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_title); ?>"/>
              </div>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-3 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--3">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="38b264de-5c1b-1605-4577-83e6d396570e">
              <div class="lp-space-react w-38b264de-5c1b-1605-4577-83e6d396570e css-1klih5i" data-widget-type="LpSpaceReact">
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
        <div class="composite flex__item--md-6 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--6">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="ab76426d-bab4-3238-4f4b-e676f145f639">
              <div class="lp-button-react-wrapper css-1yltisf" data-widget-type="LpButtonReact">
              <a class="lp-button-react w-ab76426d-bab4-3238-4f4b-e676f145f639 lp-button-react--full is-bold lp-button-react--medium lp-button-react--flat lp-btn-flat font-scale-7 line-height-scale-7 css-1cgplql animate__animated animate__pulse animate__infinite infinite" contenteditable="false" data-link-type="leadbox" data-widget-link="true" href="<?php echo esc_url($hero_whatsapp_link); ?>" target="_top">
                <?php echo esc_html($hero_whatsapp_label); ?>
              </a>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
        <div class="composite flex__item--md-3 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--3">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="16db7251-d606-59c3-a425-9d1bb237ac9c">
              <div class="lp-space-react w-16db7251-d606-59c3-a425-9d1bb237ac9c css-1klih5i" data-widget-type="LpSpaceReact">
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="37f8d7fe-92ed-e1cf-1613-76d5ced88955">
              <div class="lp-text-react lp-headline-react" data-widget-type="LpTextReact">
              <div class="text-align-center hero-subtitle">
                <?php echo $hero_subtitle; ?>
              </div>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </div>
  </section>

  <!-- countdown del webinar -->
  <?php if ($webinar_date_iso !== '') : ?>
  <section class="section css-1ge5b8h" data-guid="36b99077-97e2-3f93-cafb-ac88bbb23957">
    <div class="container" id="countdown">
    <div class="layout flex flex--12" data-guid="76150d03-03b2-22be-cecd-44119c81c27e">
      <div class="column flex__item--md-12 flex__item--xs-12 css-1dnlddl" data-guid="9fbd71c9-16d5-942c-e265-00110a09c24c">
      <div class="inner-column css-efs6e5">
        <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
          <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
            <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12 css-17m6wto">
              <div class="widget" data-widget-id="3c8938ff-b5ba-ae37-8f33-805f71a21e0f">
             
              <!-- countdown area -->
              <div class="lp-countdown-timer-react w-3c8938ff-b5ba-ae37-8f33-805f71a21e0f css-1f8dprf lp-countdown-timer-react--boxed-in" data-countdown-guid="3c8938ff-b5ba-ae37-8f33-805f71a21e0f" data-countdown-type="standard" data-widget-type="LpCountdownTimerReact" data-target="<?php echo esc_attr($webinar_date_iso); ?>">
                <div class="countdown-container lp-countdown-timer-react--boxed-in css-1sefhdb">
                <div class="time-section-wrap css-h72lq2">
                  <div class="time-section css-1s5uhtq">
                  <div class="time-wrapper css-1cvw58v">
                    <span class="days" data-unit="days">
                    00
                    </span>
                  </div>
                  <div class="time-name css-gk3e69">
                    Días
                  </div>
                  </div>
                </div>
                <div class="time-section-wrap css-h72lq2">
                  <div class="time-section css-1s5uhtq">
                  <div class="time-wrapper css-1cvw58v">
                    <span class="hours" data-unit="hours">
                    00
                    </span>
                  </div>
                  <div class="time-name css-gk3e69">
                    Horas
                  </div>
                  </div>
                </div>
                <div class="time-section-wrap css-h72lq2">
                  <div class="time-section css-1s5uhtq">
                  <div class="time-wrapper css-1cvw58v">
                    <span class="minutes" data-unit="minutes">
                    00
                    </span>
                  </div>
                  <div class="time-name css-gk3e69">
                    Minutos
                  </div>
                  </div>
                </div>
                <div class="time-section-wrap css-h72lq2">
                  <div class="time-section css-1s5uhtq">
                  <div class="time-wrapper css-1cvw58v">
                    <span class="seconds" data-unit="seconds">
                    00
                    </span>
                  </div>
                  <div class="time-name css-gk3e69">
                    Segundos
                  </div>
                  </div>
                </div>
                </div>
              </div>
              <script>
              (function () {
                var countdownEl = document.querySelector('.lp-countdown-timer-react[data-target]');
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

              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- about webinar (¿Qué aprenderás?) -->
  <?php if (!empty($about_tabs) || $about_main_title !== '') : ?>
  <section class="section css-fzn3wk idh-about">
  <div class="container" id="about-webinar">
    <?php if ($about_main_title !== '') : ?>
      <h2 class="lp-headline text-align-center subhead"><?php echo esc_html($about_main_title); ?></h2>
    <?php endif; ?>
    <div class="idh-about__grid<?php echo $about_image !== '' ? '' : ' idh-about__grid--single'; ?>">
      <?php if ($about_image !== '') : ?>
        <div class="idh-about__image">
          <img src="<?php echo esc_url($about_image); ?>" alt="<?php echo esc_attr($about_main_title); ?>" />
        </div>
      <?php endif; ?>
      <?php if (!empty($about_tabs)) : ?>
        <div class="idh-tabs">
          <div class="idh-tabs__nav" role="tablist">
            <?php foreach ($about_tabs as $tab_index => $tab) : ?>
              <button type="button" role="tab" class="idh-tabs__button<?php echo 0 === $tab_index ? ' is-active' : ''; ?>" data-tab="<?php echo (int) $tab_index; ?>" aria-selected="<?php echo 0 === $tab_index ? 'true' : 'false'; ?>"><?php echo esc_html($tab['title']); ?></button>
            <?php endforeach; ?>
          </div>
          <?php foreach ($about_tabs as $tab_index => $tab) : ?>
            <div class="idh-tabs__panel<?php echo 0 === $tab_index ? ' is-active' : ''; ?>" role="tabpanel" data-panel="<?php echo (int) $tab_index; ?>">
              <?php echo wpautop($tab['content']); ?>
            </div>
          <?php endforeach; ?>
        </div>
        <script>
          (function () {
            var root = document.querySelector('.idh-tabs');
            if (!root) { return; }
            root.querySelectorAll('.idh-tabs__button').forEach(function (button) {
              button.addEventListener('click', function () {
                var index = button.getAttribute('data-tab');
                root.querySelectorAll('.idh-tabs__button').forEach(function (item) {
                  var active = item === button;
                  item.classList.toggle('is-active', active);
                  item.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                root.querySelectorAll('.idh-tabs__panel').forEach(function (panel) {
                  panel.classList.toggle('is-active', panel.getAttribute('data-panel') === index);
                });
              });
            });
          })();
        </script>
      <?php endif; ?>
    </div>
  </div>
  </section>
  <?php endif; ?>

  <!-- temario -->
  <?php if (!empty($temario_modules)) : ?>
  <section class="section css-1ge5b8h idh-temario">
  <div class="container" id="temario">
    <?php if ($temario_title !== '') : ?>
      <h2 class="lp-headline text-align-center subhead"><?php echo esc_html($temario_title); ?></h2>
    <?php endif; ?>
    <div class="idh-temario__grid">
      <?php foreach ($temario_modules as $module) : ?>
        <div class="idh-temario__card">
          <h4><?php echo esc_html($module['title']); ?></h4>
          <?php if (!empty($module['topics'])) : ?>
            <ul>
              <?php foreach ($module['topics'] as $topic_index => $topic) : ?>
                <li><strong><?php echo esc_html('Tema ' . ($topic_index + 1) . ':'); ?></strong> <?php echo esc_html($topic); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="idh-center">
      <a class="idh-whatsapp-cta whatsapp-button animate__animated animate__pulse animate__infinite infinite" href="<?php echo esc_url($hero_whatsapp_link); ?>" target="_blank" rel="noopener noreferrer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="26" height="26" style="margin-right: 8px;"><path d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" fill="currentColor"/></svg><b><?php echo esc_html($hero_whatsapp_label); ?></b></a>
    </div>
  </div>
  </section>
  <?php endif; ?>

  <!-- exponentes -->
  <?php if ($exponente_show && !empty($exponentes)) : ?>
  <section class="section css-hfzike idh-exponentes">
  <div class="container" id="exponente">
    <?php if ($exponente_title !== '') : ?>
      <h2 class="lp-headline text-align-center subhead"><?php echo esc_html($exponente_title); ?></h2>
    <?php endif; ?>
    <div class="idh-exponentes__grid">
      <?php foreach ($exponentes as $item) : ?>
        <div class="idh-exponentes__card">
          <?php if ($item['image'] !== '') : ?>
            <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>" />
          <?php endif; ?>
          <?php if ($item['name'] !== '') : ?>
            <h4><?php echo esc_html($item['name']); ?></h4>
          <?php endif; ?>
          <?php if ($item['description'] !== '') : ?>
            <p><?php echo esc_html($item['description']); ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  </section>
  <?php endif; ?>

  <?php get_section_video(); ?>

  <!-- call action -->
  <?php if ($call_action_text !== '') : ?>
  <section class="section css-1ge5b8h idh-call-action">
  <div class="container" id="call-action">
    <h3 class="text-align-center"><?php echo esc_html($call_action_text); ?></h3>
    <div class="idh-center">
      <a class="idh-whatsapp-cta whatsapp-button animate__animated animate__pulse animate__infinite infinite" href="<?php echo esc_url($call_action_link); ?>" target="_blank" rel="noopener noreferrer"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="26" height="26" style="margin-right: 8px;"><path d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" fill="currentColor"/></svg><b><?php echo esc_html($call_action_label); ?></b></a>
    </div>
  </div>
  </section>
  <?php endif; ?>

  <section class="section css-1e56whp" data-guid="b18562e5-99f6-b197-c7a4-7813c084437d">
  <div class="container" id="legal">
    <div class="layout flex flex--12" data-guid="e89af331-dfee-047b-699f-d5f2742e6595">
    <div class="column flex__item--md-12 flex__item--xs-12 css-1dnlddl" data-guid="f2dfbc77-16ee-afba-0ed9-022c858bab84">
      <div class="inner-column css-efs6e5">
      <div class="row flex flex--12 css-1v1axsl">
        <div class="composite flex__item--md-12 flex__item--xs-12 css-8x0d4x">
        <div class="inner-composite flex flex--12">
          <div class="widget-column flex__item--md-12 flex__item--xs-12">
          <div class="widget-row flex flex--12">
            <div class="flex__item--md-12 flex__item--xs-12">
            <div class="widget" data-widget-id="a9df20f5-12d9-5af5-39f9-e55408ff7ffb">
              <div class="lp-text-react lp-headline-react" data-widget-type="LpTextReact">
              <p class="text-align-center font-scale-4 line-height-scale-6 gutter-bottom-1">
                <span style="color: rgb(255, 255, 255)">
                  &copy; <?php echo date("Y"); ?> Instituto IDH, Todos los derechos reservados
                </span>
              </p>
              </div>
            </div>
            </div>
          </div>
          </div>
        </div>
        </div>
      </div>
      </div>
    </div>
    </div>
  </div>
  </section>
</div>

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

<?php get_footer("custom"); ?>
