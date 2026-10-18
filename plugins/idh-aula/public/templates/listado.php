<?php
/**
 * @var object   $student
 * @var object[] $enrollments
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$used_categories = [];
foreach ( $enrollments as $row ) {
    if ( $row->category ) {
        $used_categories[ $row->category ] = IDH_Aula_Categories::label( $row->category );
    }
}
?>
<main class="aula-main">
  <div class="aula-page-head">
    <h1 class="aula-page-title">Hola, <?php echo esc_html( $student->first_name ); ?></h1>
    <p class="aula-page-subtitle">Estos son tus cursos, talleres y webinars. Ingresa a cada uno para ver las clases grabadas, descargar los materiales y tu certificado.</p>
  </div>

  <?php if ( ! $enrollments ) : ?>
    <div class="state-card">
      <div class="state-icon"><?php echo idh_aula_icon( 'book', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      <h2 class="state-title">Aún no tienes cursos registrados</h2>
      <p class="state-text">Cuando IDH registre tu inscripción, tus cursos aparecerán aquí.</p>
    </div>
  <?php else : ?>

    <?php if ( count( $used_categories ) > 1 ) : ?>
      <div class="filter-tabs" role="tablist">
        <button type="button" class="filter-tab is-active" data-filter="all">Todos</button>
        <?php foreach ( $used_categories as $value => $label ) : ?>
          <button type="button" class="filter-tab" data-filter="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="course-grid">
      <?php foreach ( $enrollments as $row ) :
          $state     = IDH_Aula_Enrollments::access_state( $row );
          $locked    = 'ok' !== $state;
          $cover     = IDH_Aula_Courses::cover_url( (object) [ 'cover_image_id' => $row->cover_image_id ], 'medium_large' );
          $tag       = $locked ? 'div' : 'a';
          $href      = $locked ? '' : ' href="' . esc_url( idh_aula_portal_url( 'curso/' . (int) $row->course_id ) ) . '"';
          ?>
        <<?php echo $tag . $href; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="course-card<?php echo $locked ? ' is-locked' : ''; ?>" data-category="<?php echo esc_attr( $row->category ); ?>"<?php echo $locked ? ' aria-disabled="true"' : ''; ?>>
          <div class="cover<?php echo $cover ? '' : ' cover--empty'; ?>">
            <?php if ( $cover ) : ?>
              <img src="<?php echo esc_url( $cover ); ?>" alt="" loading="lazy" />
            <?php else : ?>
              <?php echo idh_aula_icon( 'book', 36 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php endif; ?>
            <?php if ( $locked ) : ?>
              <div class="lock-overlay"><span><?php echo idh_aula_icon( 'lock', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div>
            <?php endif; ?>
          </div>
          <div class="course-card-body">
            <div class="badges" style="margin-bottom:0">
              <span class="badge badge-purple"><?php echo esc_html( IDH_Aula_Categories::label( $row->category ) ); ?></span>
              <?php if ( ! $locked && IDH_Aula_Enrollments::is_pending( $row ) ) : ?>
                <?php echo idh_aula_pending_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
              <?php elseif ( $row->certificate_id && IDH_Aula_Enrollments::certificate_available( $row ) ) : ?>
                <span class="badge badge-success">Certificado disponible</span>
              <?php endif; ?>
            </div>
            <h2 class="course-card-title"><?php echo esc_html( $row->course_title ); ?></h2>
            <div class="course-card-meta">
              <?php if ( $row->hours ) : ?>
                <span><?php echo idh_aula_icon( 'clock', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $row->hours ); ?></span>
              <?php endif; ?>
              <?php if ( $row->start_date ) : ?>
                <span><?php echo idh_aula_icon( 'calendar', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( idh_aula_format_date( $row->start_date ) ); ?></span>
              <?php endif; ?>
            </div>

            <?php if ( 'pending' === $state ) : ?>
              <p class="lock-message"><strong>Pendiente de pago.</strong> Regulariza tu pago para acceder a las clases, materiales y certificado.</p>
              <?php echo idh_aula_whatsapp_button( 'pending', $student, $row->course_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php elseif ( 'locked' === $state ) : ?>
              <p class="lock-message lock-message--danger"><strong>Acceso restringido.</strong> Comunícate con IDH para más información.</p>
              <?php echo idh_aula_whatsapp_button( 'locked', $student, $row->course_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            <?php else : ?>
              <div class="course-card-footer">
                <span class="course-card-cta">Ver curso <?php echo idh_aula_icon( 'chevron', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
              </div>
            <?php endif; ?>
          </div>
        </<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
