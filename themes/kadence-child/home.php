<?php
/*
    Template Name: Home
*/

get_header('custom');

// Obtener valores de Meta Box para Header y Hero
$header_button_text = rwmb_meta('header_button_text') ?: 'Solicita información ahora';
$header_button_link = rwmb_meta('header_button_link') ?: '#';
$hero_button_text = rwmb_meta('hero_button_text') ?: 'Explora Nuestros Programas';
$hero_button_link = rwmb_meta('hero_button_link') ?: 'https://www.facebook.com/profile.php?id=61559437738012';

// Query para verificar si hay landing pages publicadas
$landing_pages = new WP_Query(array(
  'post_type' => 'page',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'meta_query' => array(
    array(
      'key' => '_wp_page_template',
      'value' => array('template-1.php', 'template-2.php', 'template-3.php'),
      'compare' => 'IN'
    )
  )
));
$has_landing_pages = $landing_pages->have_posts();
wp_reset_postdata();
?>

     <header class="header" data-header>
      <div class="container">
        <a href="#" class="logo">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/logo.png" alt="Education logo" width="70" height="60">
        </a>
        <nav class="navbar " data-navbar>
          <div class="wrapper">
            <a href="#" class="logo">
              <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/logo.png" width="70" height="60" alt="Education logo">
            </a>
            <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
              <i class="fas fa-close"></i>
            </button>
          </div>
          <ul class="navbar-list">
            <li class="navbar-item">
              <a href="#about" class="navbar-link" data-nav-link>Nosotros</a>
            </li>
            <?php if ($has_landing_pages) : ?>
            <li class="navbar-item">
              <a href="#courses" class="navbar-link" data-nav-link>Cursos</a>
            </li>
            <?php endif; ?>
            <li class="navbar-item">
              <a href="#teacher" class="navbar-link" data-nav-link>Expertos</a>
            </li>
          </ul>
        </nav>
        <div class="header-actions">          
          <a href="<?php echo esc_url($header_button_link); ?>" class="btn has-before">
            <span class="span"><?php echo esc_html($header_button_text); ?></span>
            <i class="fas fa-arrow-circle-right"></i>
          </a>
          <button class="header-action-btn" aria-label="open menu" data-nav-toggler style="background-color: var(--kappel); color:white; border: none;">
            <i class="fas fa-bars"></i>
          </button>
        </div>
        <div class="overlay" data-nav-toggler data-overlay></div>
      </div>
     </header>
     <main>
        <div>

            <!-- Hero -->
            <section class="section hero has-bg-image" id="home" aria-label="home" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/n18.jpg');">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#fff" d="M0,256L80,261.3C160,267,320,277,480,266.7C640,256,800,224,960,208C1120,192,1280,192,1360,192L1440,192L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path></svg>                   
              <div class="container">
              <div class="hero-content">
                      <h2 class="h1 section-title">Desarrolla tus <span class="span">Habilidades Profesionales</span> y Transforma tu Futuro</h2>
                      <p class="hero-text">
                          Forma parte del Instituto de Desarrollo de Habilidades Profesionales y potencia tu talento con educación práctica e innovadora.
                      </p>
                      <a href="<?php echo esc_url($hero_button_link); ?>" target="_blank" class="btn has-before">
                          <span class="span"><?php echo esc_html($hero_button_text); ?></span>
                          <i class="ri-arrow-right-line"></i>
                      </a>
                  </div>
                  <figure class="hero-banner">
                    <div class="img-holder one" style="--width: 270; --height: 300;">
                      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/19.jpg" width="270" height="300" alt="hero banner" class="img-cover">
                    </div>
                    <div class="img-holder two" style="--width: 270; --height: 300;">
                      <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/1.jpg" width="270" height="300" alt="hero banner" class="img-cover">
                    </div>
                  </figure>
              </div>
            </section>            
            <!-- About -->
            <section class="section about" id="about" aria-label="about">
            <div class="container">
              <figure class="about-banner">
                <div class="img-holder" style="--width: 520; --height: 370;">
                  <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/17.jpg" class="img-cover" alt="about-banner" width="520" height="370" loading="lazy">
                </div>
              </figure>
              <div class="about-content">
                <p class="section-subtitle">Nosotros</p>
                <h2 class="h2 section-title">
                  Instituto de Desarrollo de <span class="span">Habilidades Profesionales</span>
                </h2>
                <p class="section-text">
                  Somos el Instituto de Desarrollo de Habilidades Profesionales (IDHP), un espacio educativo creado para impulsar el talento y el crecimiento profesional. Entendemos que el éxito no solo depende del conocimiento teórico, sino de la capacidad de aplicarlo, comunicarlo y liderar con él.
                </p>
                <p class="section-text">
                  Por eso, diseñamos programas educativos y talleres enfocados en el "saber hacer". Nuestro equipo se compromete día a día a brindar herramientas actualizadas y un acompañamiento cercano a cada alumno. Creemos firmemente que invertir en habilidades es la clave para construir el futuro que deseas.
                  </p>
                  <ul class="about-list">
                    <li class="about-item">
                      <i class="fas fa-check-circle"></i>
                      <span class="span">Formación Práctica e Innovadora</span>
                    </li>

                    <li class="about-item">
                      <i class="fas fa-check-circle"></i>
                      <span class="span">Docentes Expertos</span>
                    </li>

                    <li class="about-item">
                      <i class="fas fa-check-circle"></i>
                      <span class="span">Adaptado al Mercado Laboral</span>
                    </li>

                  </ul>
              </div>
            </div>
            </section>
            <!-- courses -->
            <?php
            if ($has_landing_pages) :
            // Re-ejecutar query para obtener los posts
            $landing_pages = new WP_Query(array(
              'post_type' => 'page',
              'post_status' => 'publish',
              'posts_per_page' => -1,
              'meta_query' => array(
                array(
                  'key' => '_wp_page_template',
                  'value' => array('template-1.php', 'template-2.php', 'template-3.php'),
                  'compare' => 'IN'
                )
              )
            ));
            ?>
            <section class="section course" id="courses" aria-label="courses">
              <div class="container">
                <p class="section-subtitle">Nuestros eventos</p>
                <h2 class="h2 section-title">Explora nuestros eventos más populares</h2>
                <ul class="grid-list">
                  <?php
                    while ($landing_pages->have_posts()) : $landing_pages->the_post();
                      
                      // Prioridad: Imagen Portada -> Imagen Hero -> primera imagen del contenido -> imagen por defecto
                      $hero_image_data = rwmb_meta('listing_cover_image', ['size' => 'full']);
                      if (empty($hero_image_data)) {
                        $hero_image_data = rwmb_meta('hero_image', ['size' => 'full']);
                      }
                      $hero_image = '';
                      
                      if (!empty($hero_image_data)) {
                        $hero_image = reset($hero_image_data)['full_url'];
                      } else {
                        // Si no hay hero_image, buscar la primera imagen en el contenido
                        $content = get_the_content();
                        preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
                        if (!empty($matches[1])) {
                          $hero_image = $matches[1];
                        } else {
                          // Imagen por defecto si no se encuentra ninguna
                          $hero_image = get_stylesheet_directory_uri() . '/assets/home/img/18.jpg';
                        }
                      }
                      
                      $page_title = get_the_title();
                      $listing_cover_title = trim((string) rwmb_meta('listing_cover_title'));
                        $listing_cover_category = trim((string) rwmb_meta('listing_cover_category'));
                        $category_definition = kadence_child_get_cover_category($listing_cover_category);
                      $card_title = !empty($listing_cover_title) ? $listing_cover_title : $page_title;
                      $page_link = get_permalink();
                  ?>
                  <li>
                      <div class="teacher-card cursor-pointer relative" onClick="window.location.href='<?php echo esc_url($page_link); ?>'">
                        <?php if (!empty($category_definition)) : ?>
                        <span
                          class="cover-category-badge"
                          style="background-color: <?php echo esc_attr($category_definition['color']); ?>; color: <?php echo esc_attr(kadence_child_get_contrast_text_color($category_definition['color'])); ?>;"
                        >
                          <?php echo esc_html($category_definition['label']); ?>
                        </span>
                        <?php endif; ?>
                        <figure class="card-banner img-holder has-after" style="--width: 370; --height: 370;">
                          <img src="<?php echo esc_url($hero_image); ?>" width="370" height="370" alt="<?php echo esc_attr($card_title); ?>" class="img-cover">
                        </figure>
                        <div class="card-content">
                                                    
                          <h3 class="h3">
                            <a href="<?php echo esc_url($page_link); ?>" class="card-title capitalize">
                              <?php echo esc_html($card_title); ?>
                            </a>
                          </h3>
                          
                        </div>
                      </div>
                  </li>
                  
                  <?php
                    endwhile;
                    wp_reset_postdata();
                  ?>
                </ul>
                <a href="#" class="btn has-before">
                  <span class="span">View More Courses</span>
                  <i class="fas fa-arrow-circle-right"></i>
                </a>
              </div>
            </section>
            <?php
            endif;
            wp_reset_postdata();
            ?>
            <!-- Our Teachers -->
            <section class="section teacher has-bg-image" id="teacher" aria-label="teacher" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/n18.jpg'); display:none;">
            <div class="container">
            <p class="section-subtitle">Nuestros ponentes</p>
            <h2 class="h2 section-title">Conozca a Nuestros Instructores Expertos</h2>
            <ul class="grid-list">
              <?php
              // Obtener las listas de profesores desde Meta Box (campos clonables separados)
              $teacher_images = rwmb_meta('teacher_images', ['size' => 'full']);
              $teacher_names = rwmb_meta('teacher_names');
              $teacher_positions = rwmb_meta('teacher_positions');
              
              // Verificar que al menos haya nombres configurados
              if (!empty($teacher_names) && is_array($teacher_names)) :
                $total_teachers = count($teacher_names);
                
                for ($i = 0; $i < $total_teachers; $i++) :
                  // Obtener imagen del profesor
                  $teacher_image = get_stylesheet_directory_uri() . '/assets/home/img/n1.jpg'; // Imagen por defecto
                  
                  if (!empty($teacher_images[$i])) {
                    // image_advanced clonable devuelve un array de imágenes, tomar la primera
                    if (is_array($teacher_images[$i])) {
                      $first_image = reset($teacher_images[$i]);
                      if (isset($first_image['full_url'])) {
                        $teacher_image = $first_image['full_url'];
                      }
                    }
                  }
                  
                  $teacher_name = isset($teacher_names[$i]) ? $teacher_names[$i] : 'Nombre del Profesor';
                  $teacher_position = isset($teacher_positions[$i]) ? $teacher_positions[$i] : 'Especialidad';
                  
                  // Saltar profesores sin nombre
                  if (empty(trim($teacher_name))) continue;
              ?>
              <li>
                <div class="teacher-card">
                  <figure class="card-banner img-holder has-after" style="--width: 370; --height: 370;">
                    <img src="<?php echo esc_url($teacher_image); ?>" width="370" height="370" alt="<?php echo esc_attr($teacher_name); ?>" class="img-cover">
                  </figure>
                  <div class="card-content">
                    <a href="#" class="card-subtitle"><?php echo esc_html($teacher_name); ?></a>
                    <h3 class="h3">
                      <a href="#" class="card-title"><?php echo esc_html($teacher_position); ?></a>
                    </h3>
                  </div>
                </div>
              </li>
              <?php
                endfor;
              endif; ?>
            </ul>
            </div>
            </section>
            <!-- Review -->
            <section class="section stats" aria-label="stats" style="display:none;">
              <div class="container">
                <h2 class="h2 section-title"> <span class="span">Happy</span> Students</h2>
                <ul class="grid-list owl-carousel owl-theme">
                  <li class="item">
                    <div class="stats-card" style="--color: 170, 75%, 41%">
                      <i class="fas fa-quote-left" style="color: darkcyan;font-size: 3rem;"></i>
                      <p class="card-text">The Online Course was flexible and convenient, allowing me to balanced work and studies easily.</p>
                    <h3 class="card-title">Thomas</h3>
                    </div>
                  </li>
                  <li class="item">
                    <div class="stats-card" style="--color: 170, 75%, 41%">
                      <i class="fas fa-quote-left" style="color: darkcyan;font-size: 3rem;"></i>
                      <p class="card-text">The teacher were always available to help and provided feedback, which really help me  improve.</p>
                    <h3 class="card-title">Marie</h3>
                    </div>
                  </li>
                  <li class="item">
                    <div class="stats-card" style="--color: 170, 75%, 41%">
                      <i class="fas fa-quote-left" style="color: darkcyan;font-size: 3rem;"></i>
                      <p class="card-text">I gained practical skills and knowledge that i can apply in real life situation- it's amazing!</p>
                    <h3 class="card-title">Rosey</h3>
                    </div>
                  </li>
                  <li class="item">
                    <div class="stats-card" style="--color: 170, 75%, 41%">
                      <i class="fas fa-quote-left" style="color: darkcyan;font-size: 3rem;"></i>
                      <p class="card-text">I appreciated the encourgement and motivation from the instructor and it kept me going.</p>
                    <h3 class="card-title">John</h3>
                    </div>
                  </li>
                </ul>
              </div>
            </section>


        </div>
     </main> 
     <!-- footer -->
      <footer class="footer" id="footer">
        <div class="footer-bottom">
          <div class="container">
            <p class="copyright">
              &copy; <?php echo date("Y"); ?> todos los derechos reservados. Instituto IDH.
            </p>
          </div>
        </div>
      </footer>
      <!-- Back To Top -->
       <a href="#top" class="back-top-btn" aria-label="back top top" data-back-top-btn>
        <i class="fas fa-chevron-circle-up"></i>
       </a>
<?php
get_footer("custom");
