<?php
/**
 * Detalle del curso (basado en idh-prototype/index.html).
 *
 * @var object   $student
 * @var object   $enrollment Matrícula con datos del curso y certificado.
 * @var object   $course
 * @var object[] $sessions
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cover     = IDH_Aula_Courses::cover_url( $course, 'large' );
$pending   = IDH_Aula_Enrollments::is_pending( $enrollment );
// Con pago pendiente no se exponen el enlace del certificado ni el del verificador.
$cert_url  = $enrollment->certificate_id && ! $pending ? IDH_Aula_Certificates::student_url( $enrollment ) : '';
$is_pdf    = 'pdf' === $enrollment->certificate_mode;
$verify    = $pending ? '' : $enrollment->verify_url;
$chevron   = '<span class="row-chevron">' . idh_aula_icon( 'chevron', 16 ) . '</span>';
$copy_btn  = static function ( $url ) {
    return '<button type="button" class="copy-btn" data-copy="' . esc_attr( $url ) . '" aria-label="Copiar enlace">' . idh_aula_icon( 'copy', 14 ) . '<span class="copy-label">Copiar</span></button>';
};
?>
<main class="aula-main aula-main--narrow">
  <a class="aula-back" href="<?php echo esc_url( idh_aula_portal_url() ); ?>"><?php echo idh_aula_icon( 'back', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> Mis cursos</a>

  <div class="card">
    <div class="cover<?php echo $cover ? '' : ' cover--empty'; ?>">
      <?php if ( $cover ) : ?>
        <img src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $course->title ); ?>" />
      <?php else : ?>
        <?php echo idh_aula_icon( 'book', 44 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <?php endif; ?>
    </div>

    <div class="content">
      <h1 class="course-title"><?php echo esc_html( $course->title ); ?></h1>

      <?php if ( $course->description ) : ?>
        <p class="course-description"><?php echo esc_html( $course->description ); ?></p>
      <?php endif; ?>

      <div class="student-row">
        <div class="avatar"><?php echo esc_html( idh_aula_initials( $student->first_name, $student->last_name ) ); ?></div>
        <div>
          <div class="student-info-name"><?php echo esc_html( IDH_Aula_Students::full_name( $student ) ); ?></div>
          <div class="student-info-label">Participante</div>
        </div>
      </div>

      <div class="badges">
        <span class="badge badge-purple"><?php echo esc_html( IDH_Aula_Categories::label( $course->category ) ); ?></span>
        <?php if ( $course->hours ) : ?>
          <span class="badge badge-red"><?php echo esc_html( $course->hours ); ?></span>
        <?php endif; ?>
        <?php if ( $pending ) : ?>
          <?php echo idh_aula_pending_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php endif; ?>
        <?php if ( $course->instructor ) : ?>
          <span class="badge badge-gray"><?php echo idh_aula_icon( 'user', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $course->instructor ); ?></span>
        <?php endif; ?>
      </div>

      <div class="divider"></div>

      <?php if ( ! $sessions ) : ?>
        <div class="section">
          <p class="empty-note">Las clases de este curso se publicarán pronto.</p>
        </div>
      <?php endif; ?>

      <?php foreach ( $sessions as $session ) :
          // Grabaciones = videos de YouTube (se abren en el reproductor); el resto son materiales.
          $videos    = [];
          $materials = [];
          foreach ( $session->resources as $resource ) {
              $video_id = 'youtube' === $resource->type ? idh_aula_youtube_id( $resource->url ) : '';
              if ( $video_id ) {
                  $videos[] = [ $resource, $video_id ];
              } else {
                  $materials[] = $resource;
              }
          }
          ?>
        <section class="session">
          <div class="session-head">
            <h2 class="session-title"><?php echo esc_html( $session->title ); ?></h2>
            <?php if ( $session->session_datetime ) : ?>
              <span class="session-date"><?php echo idh_aula_icon( 'calendar', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( idh_aula_format_datetime( $session->session_datetime ) ); ?></span>
            <?php endif; ?>
          </div>
          <?php if ( $session->description ) : ?>
            <p class="section-text"><?php echo esc_html( $session->description ); ?></p>
          <?php endif; ?>

          <?php if ( $videos ) : ?>
            <div class="group group--videos">
              <h3 class="group-title"><span class="group-icon"><?php echo idh_aula_icon( 'play', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>Grabaciones</h3>
              <div class="rows">
                <?php foreach ( $videos as list( $resource, $video_id ) ) : ?>
                  <button type="button" class="row row--video" data-video-id="<?php echo esc_attr( $video_id ); ?>" data-video-title="<?php echo esc_attr( $resource->title ); ?>">
                    <span class="row-left">
                      <span class="row-icon"><?php echo idh_aula_icon( 'play', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                      <span class="row-text"><?php echo esc_html( $resource->title ); ?></span>
                    </span>
                    <span class="row-action">Ver video</span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if ( $materials ) : ?>
            <div class="group group--materials">
              <h3 class="group-title"><span class="group-icon"><?php echo idh_aula_icon( 'folder', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>Materiales</h3>
              <div class="rows">
                <?php foreach ( $materials as $resource ) : ?>
                  <a href="<?php echo esc_url( $resource->url ); ?>" target="_blank" rel="noopener" class="row row--material">
                    <span class="row-left">
                      <span class="row-icon"><?php echo idh_aula_icon( 'drive' === $resource->type ? 'folder' : 'link', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                      <span class="row-text"><?php echo esc_html( $resource->title ); ?></span>
                    </span>
                    <span class="row-action">Abrir</span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>

      <!-- CERTIFICADO -->
      <div class="section">
        <div class="section-label">Certificado</div>
        <div class="rows">
          <?php if ( $pending ) : ?>
            <?php
            echo idh_aula_tooltip( // phpcs:ignore WordPress.Security.EscapeOutput
                '<span class="row row-static row-retained">'
                    . '<span class="row-left"><span class="row-icon">' . idh_aula_icon( 'lock' ) . '</span>'
                    . '<span class="row-text">' . ( $enrollment->certificate_id ? 'Certificado retenido' : 'Certificado de finalización' ) . '</span></span>'
                    . '<span class="row-right"><span class="row-meta">Pago pendiente</span>' . idh_aula_icon( 'alert', 16 ) . '</span>'
                . '</span>',
                $enrollment->certificate_id ? 'Tu certificado está listo, pero retenido' : 'Certificado bloqueado por falta de pago',
                'Para descargar tu certificado y acceder a su verificador, completa el pago de este curso. Si ya pagaste, comunícate con IDH para actualizar tu estado.',
                'has-tip--block'
            );
            ?>
          <?php elseif ( $cert_url ) : ?>
            <div class="row-with-action">
              <a href="<?php echo esc_url( $cert_url ); ?>" target="_blank" rel="noopener" class="row row-cert">
                <div class="row-left">
                  <span class="row-icon"><?php echo idh_aula_icon( 'award' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                  <span class="row-text"><?php echo $is_pdf ? 'Descargar certificado de finalización' : 'Ver certificado de finalización'; ?></span>
                </div>
                <div class="row-right">
                  <?php if ( $course->hours ) : ?>
                    <span class="row-meta"><?php echo esc_html( $course->hours ); ?></span>
                  <?php endif; ?>
                  <?php echo $chevron; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
              </a>
              <?php echo $copy_btn( $cert_url ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            </div>

            <?php if ( $verify ) : ?>
              <div class="row-with-action">
                <a href="<?php echo esc_url( $verify ); ?>" target="_blank" rel="noopener" class="row">
                  <div class="row-left">
                    <span class="row-icon"><?php echo idh_aula_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                    <span class="row-text">Verificador del certificado</span>
                  </div>
                  <div class="row-right"><?php echo $chevron; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                </a>
                <?php echo $copy_btn( $verify ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <div class="row row-static">
              <div class="row-left">
                <span class="row-icon"><?php echo idh_aula_icon( 'award' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                <span class="row-text">Tu certificado aún no está disponible</span>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- VIDEO MODAL -->
<div class="modal-overlay" id="videoModal">
  <div class="modal-container">
    <button type="button" class="modal-close" id="modalClose" aria-label="Cerrar"><?php echo idh_aula_icon( 'close', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
    <div class="modal-video">
      <iframe id="modalIframe" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
    </div>
    <div class="modal-title" id="modalTitle"></div>
  </div>
</div>
