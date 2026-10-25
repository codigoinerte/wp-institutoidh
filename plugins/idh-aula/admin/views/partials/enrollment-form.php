<?php
/**
 * Tarjeta editable de una matrícula: pago, bloqueo, precio y certificado.
 *
 * @var object $e        Fila de IDH_Aula_Enrollments::for_student().
 * @var string $redirect URL de retorno.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mode      = $e->certificate_mode ?: 'url';
$pdf_url   = $e->certificate_id && $e->pdf_path ? IDH_Aula_Certificates::download_url( $e->certificate_code ) : '';
$field_id  = 'enr-' . (int) $e->id;
$course_ed = idh_aula_admin_url( 'idh-aula-cursos', [ 'action' => 'edit', 'id' => $e->course_id ] );
?>
<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
      class="idh-enrollment<?php echo $e->locked ? ' is-locked' : ''; ?>" id="matricula-<?php echo (int) $e->id; ?>">
  <input type="hidden" name="action" value="idh_aula_save_enrollment" />
  <input type="hidden" name="enrollment_id" value="<?php echo (int) $e->id; ?>" />
  <input type="hidden" name="_redirect" value="<?php echo esc_url( $redirect ); ?>" />
  <?php wp_nonce_field( 'idh_aula_save_enrollment' ); ?>

  <div class="idh-enrollment-head">
    <div>
      <strong class="idh-enrollment-title"><a href="<?php echo esc_url( $course_ed ); ?>"><?php echo esc_html( $e->course_title ); ?></a></strong>
      <div class="idh-enrollment-meta">
        <?php echo IDH_Aula_Admin::category_badge( $e->category ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo IDH_Aula_Admin::payment_badge( $e->payment_status ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php if ( $e->locked ) : ?><span class="idh-badge idh-badge--danger">🔒 Bloqueado</span><?php endif; ?>
        <?php if ( $e->certificate_id ) : ?><span class="idh-badge idh-badge--success">Certificado</span><?php endif; ?>
        <?php if ( 'draft' === $e->course_status ) : ?><span class="idh-badge">Curso en borrador</span><?php endif; ?>
        <span class="description">Matriculado el <?php echo esc_html( idh_aula_format_date( $e->enrolled_at ) ); ?></span>
      </div>
    </div>
    <button type="button" class="button-link idh-toggle" aria-expanded="false" data-toggle-target="#<?php echo esc_attr( $field_id ); ?>">Editar</button>
  </div>

  <div class="idh-enrollment-body" id="<?php echo esc_attr( $field_id ); ?>" hidden>
    <div class="idh-grid-3">
      <p class="idh-field">
        <label>Estado de pago</label>
        <select name="enrollment[payment_status]">
          <option value="pending" <?php selected( $e->payment_status, 'pending' ); ?>>Pendiente</option>
          <option value="paid" <?php selected( $e->payment_status, 'paid' ); ?>>Pagado</option>
        </select>
      </p>
      <p class="idh-field">
        <label>Precio (S/) <span class="description">privado</span></label>
        <input type="number" step="0.01" min="0" name="enrollment[price_paid]" value="<?php echo esc_attr( $e->price_paid ); ?>" />
      </p>
      <p class="idh-field">
        <label>Acceso al curso</label>
        <label class="idh-switch">
          <input type="checkbox" name="enrollment[locked]" value="1" <?php checked( (int) $e->locked, 1 ); ?> />
          <span>Bloquear este curso (candado)</span>
        </label>
      </p>
    </div>
    <p class="idh-field">
      <label>Motivo del bloqueo (interno)</label>
      <input type="text" class="widefat" name="enrollment[lock_reason]" value="<?php echo esc_attr( $e->lock_reason ); ?>" placeholder="Ej.: debe la segunda cuota" />
      <span class="description">
        <strong>Pago pendiente sin candado:</strong> el alumno ve las clases y los materiales con el aviso «Pago pendiente», pero su certificado y su verificador quedan retenidos.
        <strong>Con candado:</strong> no puede entrar al curso («Pendiente de pago» o «Acceso restringido», según el pago).
      </span>
    </p>

    <fieldset class="idh-cert">
      <legend>Certificado</legend>
      <div class="idh-cert-modes">
        <?php foreach ( IDH_Aula_Certificates::MODES as $value => $label ) : ?>
          <label><input type="radio" name="certificate[mode]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $mode, $value ); ?> data-cert-mode /> <?php echo esc_html( $label ); ?></label>
        <?php endforeach; ?>
      </div>

      <div class="idh-grid-2">
        <p class="idh-field" data-cert-for="url">
          <label>Enlace del certificado</label>
          <input type="url" class="widefat" name="certificate[external_url]" value="<?php echo esc_attr( $e->external_url ); ?>" placeholder="https://qr.io/…" />
          <span class="description">El enlace al que apunta el QR (página externa).</span>
        </p>
        <p class="idh-field" data-cert-for="pdf">
          <label>Archivo PDF</label>
          <input type="file" name="certificate_pdf" accept="application/pdf,.pdf" />
          <?php if ( $pdf_url ) : ?>
            <span class="description">Actual: <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $e->pdf_name ?: 'certificado.pdf' ); ?></a>. Sube otro para reemplazarlo.</span>
            <?php $uploaded_left = IDH_Aula_Certificates::upload_notice_left( $e->id ); ?>
            <?php if ( $uploaded_left ) : ?>
            <span class="idh-upload-ok" role="status" data-hide-after="<?php echo (int) $uploaded_left; ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="8 12.5 11 15.5 16 9.5"/></svg>
              Documento cargado correctamente
            </span>
            <?php endif; ?>
          <?php else : ?>
            <span class="description">Máx. 15 MB. Se guarda en una carpeta privada y se sirve solo si el alumno no está bloqueado.</span>
          <?php endif; ?>
        </p>
        <p class="idh-field">
          <label>Enlace del verificador <span class="description">(opcional)</span></label>
          <input type="url" class="widefat" name="certificate[verify_url]" value="<?php echo esc_attr( $e->verify_url ); ?>" placeholder="https://…" />
          <span class="description">Página pública que valida que el alumno culminó el curso.</span>
        </p>
        <p class="idh-field">
          <label>Fecha de emisión</label>
          <input type="date" name="certificate[issued_at]" value="<?php echo esc_attr( $e->issued_at ?: current_time( 'Y-m-d' ) ); ?>" />
          <span class="description">Se usa en los reportes.</span>
        </p>
      </div>
      <?php if ( $e->certificate_id ) : ?>
        <p class="description">Código: <code><?php echo esc_html( $e->certificate_code ); ?></code> ·
          Enlace del alumno: <code><?php echo esc_html( IDH_Aula_Certificates::download_url( $e->certificate_code ) ); ?></code>
          <button type="button" class="button-link" data-copy="<?php echo esc_attr( IDH_Aula_Certificates::download_url( $e->certificate_code ) ); ?>">Copiar</button>
        </p>
      <?php endif; ?>
    </fieldset>

    <div class="idh-enrollment-actions">
      <button class="button button-primary">Guardar matrícula</button>
      <?php if ( $e->certificate_id ) : ?>
        <a class="idh-link-danger" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_certificate', [ 'enrollment_id' => $e->id, '_redirect' => rawurlencode( $redirect ) ] ) ); ?>" data-confirm="¿Eliminar el certificado de esta matrícula?">Eliminar certificado</a>
      <?php endif; ?>
      <a class="idh-link-danger" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_enrollment', [ 'id' => $e->id, '_redirect' => rawurlencode( $redirect ) ] ) ); ?>" data-confirm="¿Quitar al alumno de este curso? Se eliminará también su certificado. Si solo debe dinero, usa «Bloquear este curso».">Quitar matrícula</a>
    </div>
  </div>
</form>
