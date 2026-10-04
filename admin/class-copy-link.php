<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Botón "Copiar enlace" en el editor de páginas/entradas (landings de cursos) y en el listado,
 * para no tener que copiar la URL desde la barra del navegador.
 */
class IDH_Aula_Copy_Link {

    const POST_TYPES = [ 'page', 'post' ];

    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'meta_box' ] );
        add_filter( 'page_row_actions', [ __CLASS__, 'row_action' ], 10, 2 );
        add_filter( 'post_row_actions', [ __CLASS__, 'row_action' ], 10, 2 );
        add_action( 'admin_footer-post.php', [ __CLASS__, 'script' ] );
        add_action( 'admin_footer-post-new.php', [ __CLASS__, 'script' ] );
        add_action( 'admin_footer-edit.php', [ __CLASS__, 'script' ] );
    }

    public static function meta_box() {
        foreach ( self::POST_TYPES as $type ) {
            add_meta_box( 'idh-copy-link', 'Enlace público', [ __CLASS__, 'render' ], $type, 'side', 'high' );
        }
    }

    public static function render( $post ) {
        $published = 'publish' === $post->post_status;
        $url       = $published ? get_permalink( $post ) : '';
        ?>
        <div class="idh-copy-link" data-idh-copy-box>
          <input type="text" readonly value="<?php echo esc_attr( $url ); ?>" data-idh-copy-input
                 placeholder="Publica la página para obtener su enlace" onfocus="this.select()" style="width:100%;font-size:12px;margin-bottom:8px" />
          <button type="button" class="button button-primary" data-idh-copy-button style="width:100%" <?php disabled( ! $published ); ?>>
            <span class="dashicons dashicons-admin-links" style="vertical-align:text-bottom"></span> Copiar enlace
          </button>
          <p class="description" style="margin-top:8px">Pégalo en WhatsApp, Facebook o tus campañas sin riesgo de errores.</p>
        </div>
        <?php
    }

    public static function row_action( $actions, $post ) {
        if ( in_array( $post->post_type, self::POST_TYPES, true ) && 'publish' === $post->post_status ) {
            $actions['idh_copy_link'] = '<a href="#" data-idh-copy="' . esc_attr( get_permalink( $post ) ) . '">Copiar enlace</a>';
        }
        return $actions;
    }

    public static function script() {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
            return;
        }
        ?>
        <script>
        (function () {
          function copy(text, onDone) {
            if (navigator.clipboard && window.isSecureContext) {
              navigator.clipboard.writeText(text).then(onDone);
              return;
            }
            var tmp = document.createElement('textarea');
            tmp.value = text;
            tmp.style.position = 'fixed';
            tmp.style.opacity = '0';
            document.body.appendChild(tmp);
            tmp.select();
            document.execCommand('copy');
            document.body.removeChild(tmp);
            onDone();
          }

          document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-idh-copy-button], [data-idh-copy]');
            if (!btn) return;
            e.preventDefault();
            var text = btn.hasAttribute('data-idh-copy')
              ? btn.getAttribute('data-idh-copy')
              : btn.closest('[data-idh-copy-box]').querySelector('[data-idh-copy-input]').value;
            if (!text) return;
            var original = btn.innerHTML;
            copy(text, function () {
              btn.textContent = '¡Enlace copiado!';
              setTimeout(function () { btn.innerHTML = original; }, 1800);
            });
          });

          // Editor de bloques: mantener el enlace al día si cambia el slug o se publica.
          if (window.wp && wp.data && wp.data.select('core/editor')) {
            wp.data.subscribe(function () {
              var editor = wp.data.select('core/editor');
              var input = document.querySelector('[data-idh-copy-input]');
              var button = document.querySelector('[data-idh-copy-button]');
              if (!editor || !input) return;
              var published = editor.getEditedPostAttribute('status') === 'publish' && editor.isCurrentPostPublished();
              var link = published ? editor.getPermalink() : '';
              if (link && input.value !== link) input.value = link;
              if (button) button.disabled = !link && !input.value;
            });
          }
        })();
        </script>
        <?php
    }
}
