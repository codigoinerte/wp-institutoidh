<?php
/*
Template Name: Landing Page - Template 3
Template Post Type: page
*/

get_header("custom");

// Variables de Meta Box - Redes Sociales (Global)
$instagram = rwmb_meta('instagram') ?: '#';
$facebook = rwmb_meta('facebook') ?: '#';
$float_whatsapp_link = rwmb_meta('float_whatsapp_link') ?: 'https://wa.me/0000000000';

// Variables de Meta Box para Hero Section
$hero_heading           = rwmb_meta('hero_heading') ?: 'Master Your Money with';
$hero_heading_highlight = rwmb_meta('hero_heading_highlight') ?: 'AI Intelligence';
$hero_subheading        = rwmb_meta('hero_subheading') ?: 'Stop guessing. Start growing. Mintly automates your savings, optimizes investments, and tracks every penny with bank-level security.';
$hero_whatsapp_link     = rwmb_meta('hero_whatsapp_link') ?: '#signup';
$hero_whatsapp_label    = rwmb_meta('hero_whatsapp_label') ?: 'Ingresa al grupo ahora !';
$hero_image_data        = rwmb_meta('hero_image', ['size' => 'full']);
$hero_image             = !empty($hero_image_data) ? reset($hero_image_data)['full_url'] : get_stylesheet_directory_uri() . '/assets/template-3/images/photo-1563986768609-322da13575f3.jpeg';
$hero_image_alt         = rwmb_meta('hero_image_alt') ?: 'Mintly Dashboard on Tablet';

// Variables de Meta Box para Key Benefits Section
$benefits_title      = rwmb_meta('benefits_title') ?: '¿QUÉ APRENDERÁS?';
$benefits_card1_p1   = rwmb_meta('benefits_card1_p1') ?: 'Descubre nuevas habilidades para llevar tu carrera al próximo nivel aprendiendo de los mejores expertos.';
$benefits_card1_p2   = rwmb_meta('benefits_card1_p2') ?: 'DIPLOMADO PSICOPATOLOGÍA EN LA INFANCIA Y ADOLESCENCIA';
$benefits_card1_p3   = rwmb_meta('benefits_card1_p3') ?: 'El Diplomado en Psicopatología en la Infancia y adolescencia permitirá a los participantes, proporcionar fundamentos teóricos y herramientas clínicas de la psicopatología infantil y adolescente desde el modelo cognitivo procesal sistémico. Reflexionando sobre los procesos de la salud mental, la psicopatología infantil y adolescente, y su abordaje desde una mirada comprensiva, amorosa y terapéutica la sintomatología clínica, enfatizando en la importancia de las relaciones vinculares y las características de estas, hacia una tendencia a desarrollar determinados cuadros clínicos o manifestaciones conductuales.';
$benefits_card2_title = rwmb_meta('benefits_card2_title') ?: 'Dirigido a';
$benefits_card2_items = rwmb_meta('benefits_card2_items') ?: ['Psicólogos', 'Trabajadores sociales y orientadores', 'Educadores', 'Estudiantes', 'Pedagogos', 'Público en general'];
$benefits_card3_title = rwmb_meta('benefits_card3_title') ?: '¿Qué lograrás?';
$benefits_card3_items = rwmb_meta('benefits_card3_items') ?: ['Comprensión Teórica y Práctica', 'Pensamiento Crítico y habilidades de investigación científica en Psicopatología', 'Mejorar las habilidades de comunicación profesional', 'Conocer las peculiaridades del desarrollo de distintas áreas específicas', 'Perfeccionar las habilidades clínicas de entrevista, exploración y diagnóstico en Psicología Clínica', 'Dotar de habilidades y estrategias de Gestión Clínica', 'Conseguir habilidades suficientes para establecer criterios de diagnóstico diferencial en la infancia y adolescencia'];
$benefits_whatsapp_link  = rwmb_meta('benefits_whatsapp_link') ?: '#signup';
$benefits_whatsapp_label = rwmb_meta('benefits_whatsapp_label') ?: 'Ingresa al grupo ahora !';

// Variables de Meta Box para Temario Section
$temario_title          = rwmb_meta('temario_title') ?: 'Nuestro temario personalizado para cada etapa';
$temario_subtitle       = rwmb_meta('temario_subtitle') ?: 'Cada uno de nuestros cursos está diseñado para adaptarse a las necesidades específicas de cada etapa de aprendizaje.';
$temario_whatsapp_link  = rwmb_meta('temario_whatsapp_link') ?: '#signup';
$temario_whatsapp_label = rwmb_meta('temario_whatsapp_label') ?: 'Ingresa al grupo ahora !';

// Leer títulos y contenidos por separado
$temario_titles   = rwmb_meta('temario_item_titles') ?: [];
$temario_contents = rwmb_meta('temario_item_contents') ?: [];

// Asegurar que sean arrays
if (!is_array($temario_titles)) $temario_titles = [];
if (!is_array($temario_contents)) $temario_contents = [];

// Combinar títulos y contenidos en un array estructurado
$temario_items = [];
$max_items = max(count($temario_titles), count($temario_contents));

for ($i = 0; $i < $max_items; $i++) {
    $title = isset($temario_titles[$i]) ? trim((string) $temario_titles[$i]) : '';
    $content = isset($temario_contents[$i]) ? (string) $temario_contents[$i] : '';
    
    // Solo agregar si al menos uno de los dos tiene contenido
    if ($title !== '' || $content !== '') {
        $temario_items[] = [
            'item_title'   => $title !== '' ? $title : 'Sin título',
            'item_content' => $content !== '' ? $content : '<p>Sin contenido</p>',
        ];
    }
}

// Valores por defecto para temario_items si está vacío
if (empty($temario_items)) {
    $temario_items = [
        ['item_title' => 'Is Mintly secure?', 'item_content' => '<p>Yes, security is our top priority. We use bank-level 256-bit encryption to protect your data. We are SOC2 Type II certified and never sell your personal information to third parties. Your credentials are never stored on our servers.</p>'],
        ['item_title' => 'Can I cancel my subscription anytime?', 'item_content' => '<p>Absolutely. You can cancel your subscription at any time directly from your dashboard settings. There are no cancellation fees, and you\'ll retain access to your account until the end of your current billing cycle.</p>'],
        ['item_title' => 'How does the AI analysis work?', 'item_content' => '<p>Our AI analyzes your spending patterns, income regularity, and financial goals. It identifies "safe-to-save" amounts that won\'t impact your daily life and automatically moves them to your savings or investment accounts. It learns and adapts to your habits over time.</p>'],
        ['item_title' => 'Do you support my bank?', 'item_content' => '<p>We support over 10,000 financial institutions across the US and Canada, including all major banks (Chase, Bank of America, Wells Fargo, etc.) and most credit unions. You can search for your bank during the signup process.</p>'],
        ['item_title' => 'Is there a free trial?', 'item_content' => '<p>Yes! All paid plans come with a 14-day free trial. You can explore all features without being charged. We\'ll remind you 3 days before your trial ends.</p>'],
    ];
}

// Variables de Meta Box para Expositores Section
$expositores_show = rwmb_meta('expositores_show');
$expositores_title = rwmb_meta('expositores_title') ?: 'Nuestros expositores';

// Leer los arrays separados
$expositor_images = rwmb_meta('expositor_images', ['size' => 'full']) ?: [];
$expositor_nombres = rwmb_meta('expositor_nombres') ?: [];
$expositor_profesiones = rwmb_meta('expositor_profesiones') ?: [];
$expositor_descripciones = rwmb_meta('expositor_descripciones') ?: [];

// Combinar los arrays en una estructura unificada
$expositores_list = [];
$max_expositores = max(
    count($expositor_images),
    count($expositor_nombres),
    count($expositor_profesiones),
    count($expositor_descripciones)
);

for ($i = 0; $i < $max_expositores; $i++) {
    $image_data = isset($expositor_images[$i]) ? $expositor_images[$i] : '';
    $image_url = '';
    
    // Extraer la URL de la imagen
    if (!empty($image_data) && is_array($image_data)) {
        $image_url = reset($image_data)['full_url'];
    }
    
    $expositores_list[] = [
        'expositor_image' => $image_url,
        'expositor_nombre' => isset($expositor_nombres[$i]) ? $expositor_nombres[$i] : '',
        'expositor_profesion' => isset($expositor_profesiones[$i]) ? $expositor_profesiones[$i] : '',
        'expositor_descripcion' => isset($expositor_descripciones[$i]) ? $expositor_descripciones[$i] : '',
    ];
}

// Valores por defecto si está vacío
if (empty($expositores_list)) {
    $expositores_list = [
        [
            'expositor_image' => '',
            'expositor_nombre' => 'Sarah Jenkins',
            'expositor_profesion' => 'Marketing Director',
            'expositor_descripcion' => '"Mintly has completely transformed how I manage my finances. The AI automation is a game-changer—I\'m saving more without even thinking about it."',
        ],
        [
            'expositor_image' => '',
            'expositor_nombre' => 'Michael Chen',
            'expositor_profesion' => 'Product Designer',
            'expositor_descripcion' => '"Finally, a financial app that doesn\'t feel overwhelming. The interface is clean, and the insights are actually actionable."',
        ],
        [
            'expositor_image' => '',
            'expositor_nombre' => 'Emily Rodriguez',
            'expositor_profesion' => 'Small Business Owner',
            'expositor_descripcion' => '"As a business owner, tracking personal and business finances was a nightmare. Mintly simplified everything. Highly recommend!"',
        ],
    ];
}

?>

        <!-- Modern Header -->
		<header class="header">
			<nav class="nav-container">
				<div class="nav-content">
					<!-- Logo -->
					<a
						href="/"
						class="logo"
					>
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/global/images/logo.png" alt="Logo" />
					</a>

					<!-- Desktop Navigation -->
					<div class="nav-links">
						<a
							href="#features"
							class="nav-link"
						>Beneficios</a
					>
					<a
						href="#faq"
						class="nav-link"
						>Temario</a
					>
					<a
						href="#testimonials"
						class="nav-link"
						>Expositores</a
					>
				</div>

				<!-- CTA Buttons -->
				<div class="nav-actions">
					<a href="<?php echo esc_url($hero_whatsapp_link); ?>" class="nav-btn nav-btn-primary whatsapp-desktop-button animate__animated animate__pulse animate__infinite infinite">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" fill="currentColor">
							<path 
								d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
								fill="currentColor"
							/>
						</svg>
						<?php echo esc_html($hero_whatsapp_label); ?>
					</a>
				</div>

					<!-- Mobile Menu Toggle -->
					<button
						class="mobile-menu-toggle"
						aria-label="Toggle menu"
						aria-expanded="false"
					>
						<span class="hamburger-line"></span>
						<span class="hamburger-line"></span>
						<span class="hamburger-line"></span>
					</button>
				</div>
			</nav>
		</header>

		<!-- Mobile Navigation -->
		<div class="mobile-nav">
			<div class="mobile-nav-content">
				<a
					href="#features"
					class="mobile-nav-link"
					>Beneficios</a
				>
				<a
					href="#faq"
					class="mobile-nav-link"
					>Temario</a
				>
				<a
					href="#testimonials"
					class="mobile-nav-link"
					>Expositores</a
				>
				<div class="mobile-nav-actions">
					<a href="<?php echo esc_url($hero_whatsapp_link); ?>" class="mobile-nav-btn mobile-nav-btn-primary whatsapp-mobile-button animate__animated animate__pulse animate__infinite infinite">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" fill="currentColor">
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

		<!-- Hero Section -->
		<section class="hero">
			<div class="hero-container">
				<div class="hero-content">
					<div class="hero-text-wrapper">
						<h1 class="hero-heading">
							<?php echo esc_html($hero_heading); ?>
							<span class="text-gradient"
								><?php echo esc_html($hero_heading_highlight); ?></span
							>
						</h1>
						<p class="hero-subheading">
							<?php echo esc_html($hero_subheading); ?>
						</p>
						<div class="hero-actions">
							<a
								href="<?php echo esc_url($hero_whatsapp_link); ?>"
								class="btn btn-primary btn-lg whatsapp-hero-button animate__animated animate__pulse animate__infinite infinite"
							>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" fill="currentColor">
								<path 
									d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
									fill="currentColor"
								/>
							</svg>
							<?php echo esc_html($hero_whatsapp_label); ?>
						</a>
							
						</div>						
					</div>
					<div class="hero-visual-wrapper">
						<div class="hero-image-container">
							<img
								src="<?php echo esc_url($hero_image); ?>"
								alt="<?php echo esc_attr($hero_image_alt); ?>"
								class="hero-main-image"
							/>														
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- Key Benefits Section -->
		<section class="benefits" id="features">
			<div class="container">
				<div class="benefits-header">
					<h2 class="benefits-title">
						<?php echo esc_html($benefits_title); ?>
					</h2>					
				</div>

				<div class="bento-grid">
					
					<div class="bento-card xl-card card-savings">
						<div class="card-content">							
							<p><?php echo esc_html($benefits_card1_p1); ?></p>
							<p><?php echo esc_html($benefits_card1_p2); ?></p>
							<p><?php echo esc_html($benefits_card1_p3); ?></p>
						</div>						
					</div>
					
					<div class="bento-card large-card card-ai">
						<div class="card-content">
							<h3><?php echo esc_html($benefits_card2_title); ?></h3>
							<ul>
								<?php foreach ($benefits_card2_items as $item) : ?>
									<li><?php echo esc_html($item); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					
					<div class="bento-card large-card card-savings">
						<div class="card-content">
							<h3><?php echo esc_html($benefits_card3_title); ?></h3>
							<ul>
								<?php foreach ($benefits_card3_items as $item) : ?>
									<li><?php echo esc_html($item); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>						
					</div>
									
				</div>
			</div>
			<div>
				<a href="<?php echo esc_url($benefits_whatsapp_link); ?>" class="mobile-nav-btn mobile-nav-btn-primary whatsapp-mobile-button whatsapp-benefits-button animate__animated animate__pulse animate__infinite infinite">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" fill="currentColor">
						<path 
							d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
							fill="currentColor"
						/>
					</svg>
					<?php echo esc_html($benefits_whatsapp_label); ?>
				</a>
			</div>
		</section>

		<!-- Temario -->
		<section
			class="faq-section"
			id="faq">
			<div class="container">
				<div class="faq-wrapper">
					<!-- Left Side: Header & Support -->
					<div class="faq-header-col">
						<h2 class="section-title">
							<?php echo esc_html($temario_title); ?>
						</h2>
						<p class="section-subtitle"><?php echo esc_html($temario_subtitle); ?></p>
						<div class="support-card">
							<div class="mobile-nav-actions">
								<a href="<?php echo esc_url($temario_whatsapp_link); ?>" class="mobile-nav-btn mobile-nav-btn-primary whatsapp-mobile-button animate__animated animate__pulse animate__infinite infinite">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="30" height="30" fill="currentColor">
										<path 
											d="M476.9 161.1C435 119.1 379.2 96 319.9 96C197.5 96 97.9 195.6 97.9 318C97.9 357.1 108.1 395.3 127.5 429L96 544L213.7 513.1C246.1 530.8 282.6 540.1 319.8 540.1L319.9 540.1C442.2 540.1 544 440.5 544 318.1C544 258.8 518.8 203.1 476.9 161.1zM319.9 502.7C286.7 502.7 254.2 493.8 225.9 477L219.2 473L149.4 491.3L168 423.2L163.6 416.2C145.1 386.8 135.4 352.9 135.4 318C135.4 216.3 218.2 133.5 320 133.5C369.3 133.5 415.6 152.7 450.4 187.6C485.2 222.5 506.6 268.8 506.5 318.1C506.5 419.9 421.6 502.7 319.9 502.7zM421.1 364.5C415.6 361.7 388.3 348.3 383.2 346.5C378.1 344.6 374.4 343.7 370.7 349.3C367 354.9 356.4 367.3 353.1 371.1C349.9 374.8 346.6 375.3 341.1 372.5C308.5 356.2 287.1 343.4 265.6 306.5C259.9 296.7 271.3 297.4 281.9 276.2C283.7 272.5 282.8 269.3 281.4 266.5C280 263.7 268.9 236.4 264.3 225.3C259.8 214.5 255.2 216 251.8 215.8C248.6 215.6 244.9 215.6 241.2 215.6C237.5 215.6 231.5 217 226.4 222.5C221.3 228.1 207 241.5 207 268.8C207 296.1 226.9 322.5 229.6 326.2C232.4 329.9 268.7 385.9 324.4 410C359.6 425.2 373.4 426.5 391 423.9C401.7 422.3 423.8 410.5 428.4 397.5C433 384.5 433 373.4 431.6 371.1C430.3 368.6 426.6 367.2 421.1 364.5z" 
											fill="currentColor"
										/>
									</svg>
									<?php echo esc_html($temario_whatsapp_label); ?>
								</a>
							</div>
						</div>
					</div>

					<!-- Right Side: Accordion -->
					<div class="faq-accordion">
						<?php foreach ($temario_items as $index => $item) : ?>
						<!-- Item <?php echo ($index + 1); ?> -->
						<div class="accordion-item">
							<button class="accordion-trigger" aria-expanded="false">
								<span class="accordion-title">
									<?php echo esc_html($item['item_title']); ?>
								</span>
								<span class="accordion-icon">
									<svg
										width="24"
										height="24"
										viewBox="0 0 24 24"
										fill="none"
										stroke="currentColor"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round">
										<polyline points="6 9 12 15 18 9"></polyline>
									</svg>
								</span>
							</button>
							<div
								class="accordion-content"
								aria-hidden="true"
							>
								<div class="accordion-body">
									<?php echo $item['item_content']; ?>
								</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>


		<?php get_section_video(); ?>

		<!-- Expositores Section -->
		<?php if ($expositores_show) : ?>
		<section class="social-proof-section" id="testimonials">
			<div class="container">
				<div class="social-proof-header">
					<h2 class="social-proof-title">
						<?php echo esc_html($expositores_title); ?>
					</h2>
				</div>

				<div class="testimonials-grid">
					<?php foreach ($expositores_list as $index => $expositor) : 
						// Obtener los datos del expositor
						$expositor_image = !empty($expositor['expositor_image']) ? $expositor['expositor_image'] : get_stylesheet_directory_uri() . '/assets/template-3/images/photo-1494790108377-be9c29b29330.jpeg';
						$expositor_nombre = !empty($expositor['expositor_nombre']) ? $expositor['expositor_nombre'] : 'Nombre del Expositor';
						$expositor_profesion = !empty($expositor['expositor_profesion']) ? $expositor['expositor_profesion'] : 'Profesión';
						$expositor_descripcion = !empty($expositor['expositor_descripcion']) ? $expositor['expositor_descripcion'] : 'Descripción del expositor.';
					?>
					<!-- Expositor <?php echo ($index + 1); ?> -->
					<div class="testimonial-card">
						<div class="testimonial-author">
							<img
								src="<?php echo esc_url($expositor_image); ?>"
								alt="<?php echo esc_attr($expositor_nombre); ?>"
								class="author-avatar"
								loading="lazy"
							/>
							<div class="author-info">
								<div class="author-name">
									<?php echo esc_html($expositor_nombre); ?>
								</div>
								<div class="author-role">
									<?php echo esc_html($expositor_profesion); ?>
								</div>
							</div>
						</div>
						<div class="testimonial-content">
							<p class="testimonial-quote">
								<?php echo esc_html($expositor_descripcion); ?>
							</p>
						</div>
					</div>
					<?php endforeach; ?>
				</div>				

			</div>
		</section>
		<?php endif; ?>

		<!-- Footer (Redesigned) -->
		<footer class="modern-footer">
			<div class="footer-bottom">
				<div class="container">
					<div class="footer-bottom-wrapper text-center">
						<div class="copyright text-center">
							&copy; <?php echo date('Y'); ?> Instituto IDH, Todos los derechos reservados
						</div>												
					</div>
				</div>
			</div>
		</footer>

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
