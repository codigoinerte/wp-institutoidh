<?php
/**
 * Página 404 - No encontrado
 * Template personalizado para kadence-child
 *
 * @package kadence-child
 */

get_header("custom");
?>

<style>
/* Header 404 */
.header-404 {
    background: #fff;
    padding: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 100;
}

.header-404 .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-404 .logo img {
    height: 60px;
    width: auto;
    transition: transform 0.3s ease;
}

.header-404 .logo:hover img {
    transform: scale(1.05);
}

.header-404-nav {
    display: flex;
    gap: 15px;
    align-items: center;
}

.header-404-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.header-404-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: #fff;
}

@media (max-width: 768px) {
    .header-404 .logo img {
        height: 50px;
    }
    
    .header-404-btn {
        padding: 8px 20px;
        font-size: 13px;
    }
}

.error-404-page {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.error-404-page::before {
    content: '404';
    position: absolute;
    font-size: 300px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.05);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 0;
}

.error-404-container {
    max-width: 700px;
    text-align: center;
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    padding: 60px 40px;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.error-404-title {
    font-size: 120px;
    font-weight: 900;
    color: #667eea;
    margin: 0;
    line-height: 1;
    text-shadow: 4px 4px 8px rgba(0, 0, 0, 0.1);
}

.error-404-subtitle {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin: 20px 0;
}

.error-404-text {
    font-size: 18px;
    color: #666;
    margin: 20px 0 40px;
    line-height: 1.6;
}

.error-404-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.error-404-btn {
    display: inline-block;
    padding: 15px 35px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.error-404-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: 2px solid transparent;
}

.error-404-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.error-404-btn-secondary {
    background: transparent;
    color: #667eea;
    border: 2px solid #667eea;
}

.error-404-btn-secondary:hover {
    background: #667eea;
    color: #fff;
    transform: translateY(-3px);
}

.error-404-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .error-404-title {
        font-size: 80px;
    }
    
    .error-404-subtitle {
        font-size: 24px;
    }
    
    .error-404-text {
        font-size: 16px;
    }
    
    .error-404-container {
        padding: 40px 30px;
    }
    
    .error-404-page::before {
        font-size: 180px;
    }
}
</style>

<!-- Header 404 -->
<header class="header-404">
    <div class="container">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/home/img/logo.png" alt="<?php bloginfo('name'); ?>" width="70" height="60">
        </a>
        <nav class="header-404-nav">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="header-404-btn">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
        </nav>
    </div>
</header>

<div class="error-404-page">
    <div class="error-404-container">
        <div class="error-404-icon">🔍</div>
        <h1 class="error-404-title">404</h1>
        <h2 class="error-404-subtitle">¡Página no encontrada!</h2>
        <p class="error-404-text">
            Lo sentimos, la página que estás buscando no existe o ha sido movida. 
            Puede que haya sido eliminada o que la URL sea incorrecta.
        </p>
        
        <div class="error-404-buttons">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="error-404-btn error-404-btn-primary">
                <i class="fas fa-home"></i> Volver al inicio
            </a>
            <a href="javascript:history.back()" class="error-404-btn error-404-btn-secondary">
                <i class="fas fa-arrow-left"></i> Página anterior
            </a>
        </div>
        
        <?php
        // Opcional: Mostrar buscador
        if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
            <div style="margin-top: 40px;">
                <h3 style="color: #333; margin-bottom: 15px;">¿Buscas algo específico?</h3>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
?>
