<?php
/**
 * Certificados por clase (tema) de una matrícula: opcionales y aparte del certificado general.
 * Hay una fila por clase del curso; admin.js oculta las que no están incluidas en la matrícula.
 *
 * @var object   $e        Fila de IDH_Aula_Enrollments::for_student().
 * @var object[] $sessions IDH_Aula_Courses::session_list() del curso.
 * @var bool     $partial  La matrícula es «Solo algunas clases».
 * @var int[]    $selected Clases incluidas cuando es parcial.
 * @var string   $redirect URL de retorno.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! $sessions ) {
    return;
}

$certs   = IDH_Aula_Certificates::for_enrollment_sessions( $e->id );
$visible = $partial ? array_intersect_key( $certs, array_flip( $selected ) ) : $certs;
?>
<details class="idh-cert-sessions" data-session-certs <?php echo $visible ? 'open' : ''; ?>>
  <summary>
    Certificado por clase <span class="description">(opcional)</span>
    <?php if ( $visible ) : ?><span class="idh-badge idh-badge--success"><?php echo esc_html( sprintf( '%d cargado(s)', count( $visible ) ) ); ?></span><?php endif; ?>
  </summary>
  <p class="description">Completa solo las clases que tengan certificado. El alumno los ve en una sección aparte, debajo del certificado general.</p>

  <?php foreach ( $sessions as $session ) : ?>
    <?php
    $sid      = (int) $session->id;
    $cert     = $certs[ $sid ] ?? null;
    $mode     = $cert ? $cert->mode : 'url';
    $name     = 'session_certs[' . $sid . ']';
    $included = ! $partial || in_array( $sid, $selected, true );
    $pdf_url  = $cert && $cert->pdf_path ? IDH_Aula_Certificates::download_url( $cert->code ) : '';
    ?>
    <div class="idh-cert idh-cert-row" data-session-cert="<?php echo esc_attr( $sid ); ?>" <?php echo $included ? '' : 'hidden'; ?>>
      <div class="idh-cert-row-head">
        <strong><?php echo esc_html( $session->title ); ?></strong>
        <?php if ( $cert ) : ?><span class="idh-badge idh-badge--success">Cargado</span><?php endif; ?>
      </div>
      <div class="idh-cert-modes">
        <?php foreach ( IDH_Aula_Certificates::MODES as $value => $label ) : ?>
          <label><input type="radio" name="<?php echo esc_attr( $name ); ?>[mode]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $mode, $value ); ?> data-cert-mode /> <?php echo esc_html( $label ); ?></label>
        <?php endforeach; ?>
      </div>

      <div class="idh-grid-2">
        <p class="idh-field" data-cert-for="url">
          <label>Enlace del certificado</label>
          <input type="url" class="widefat" name="<?php echo esc_attr( $name ); ?>[external_url]" value="<?php echo esc_attr( $cert->external_url ?? '' ); ?>" placeholder="https://qr.io/…" />
        </p>
        <p class="idh-field" data-cert-for="pdf">
          <label>Archivo PDF</label>
          <input type="file" name="session_cert_pdf[<?php echo esc_attr( $sid ); ?>]" accept="application/pdf,.pdf" />
          <?php if ( $pdf_url ) : ?>
            <span class="description">Actual: <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $cert->pdf_name ?: 'certificado.pdf' ); ?></a>. Sube otro para reemplazarlo.</span>
            <?php $uploaded_left = IDH_Aula_Certificates::upload_notice_left( $e->id, $sid ); ?>
            <?php if ( $uploaded_left ) : ?>
            <span class="idh-upload-ok" role="status" data-hide-after="<?php echo (int) $uploaded_left; ?>">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="8 12.5 11 15.5 16 9.5"/></svg>
              Documento cargado correctamente
            </span>
            <?php endif; ?>
          <?php else : ?>
            <span class="description">Máx. 15 MB.</span>
          <?php endif; ?>
        </p>
        <p class="idh-field">
          <label>Enlace del verificador <span class="description">(opcional)</span></label>
          <input type="url" class="widefat" name="<?php echo esc_attr( $name ); ?>[verify_url]" value="<?php echo esc_attr( $cert->verify_url ?? '' ); ?>" placeholder="https://…" />
        </p>
        <p class="idh-field">
          <label>Fecha de emisión</label>
          <input type="date" name="<?php echo esc_attr( $name ); ?>[issued_at]" value="<?php echo esc_attr( $cert->issued_at ?? current_time( 'Y-m-d' ) ); ?>" />
        </p>
      </div>
      <?php if ( $cert ) : ?>
        <p class="description">Código: <code><?php echo esc_html( $cert->code ); ?></code> ·
          <button type="button" class="button-link" data-copy="<?php echo esc_attr( IDH_Aula_Certificates::download_url( $cert->code ) ); ?>">Copiar enlace del alumno</button> ·
          <a class="idh-link-danger" href="<?php echo esc_url( IDH_Aula_Admin::post_url( 'idh_aula_delete_session_certificate', [ 'enrollment_id' => $e->id, 'session_id' => $sid, '_redirect' => rawurlencode( $redirect ) ] ) ); ?>" data-confirm="¿Eliminar el certificado de esta clase?">Eliminar</a>
        </p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</details>
