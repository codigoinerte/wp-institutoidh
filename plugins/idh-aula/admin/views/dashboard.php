<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$query       = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
$range       = IDH_Aula_Reports::resolve_period( $query );
$kpis        = IDH_Aula_Reports::kpis();
$total       = IDH_Aula_Reports::total( $range['from'], $range['to'] );
$by_course   = IDH_Aula_Reports::by_course( $range['from'], $range['to'] );
$by_category = IDH_Aula_Reports::by_category( $range['from'], $range['to'] );
$by_month    = IDH_Aula_Reports::by_month( $range['from'], $range['to'] );
$max_month   = max( 1, max( $by_month ?: [ 0 ] ) );
$max_course  = $by_course ? max( 1, (int) $by_course[0]->total ) : 1;
$this_year   = (int) current_time( 'Y' );
$months      = [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ];
$export_url  = wp_nonce_url(
    add_query_arg(
        array_merge(
            [ 'action' => 'idh_aula_export', 'type' => 'report' ],
            array_intersect_key( $range, array_flip( [ 'period', 'year', 'month', 'semester', 'from', 'to' ] ) )
        ),
        admin_url( 'admin-post.php' )
    ),
    'idh_aula_export'
);
?>
<h1 class="wp-heading-inline">Aula Virtual · Panel y reportes</h1>
<a href="<?php echo esc_url( idh_aula_portal_url() ); ?>" class="page-title-action" target="_blank" rel="noopener">Ver aula virtual ↗</a>
<hr class="wp-header-end">

<div class="idh-kpis">
  <div class="idh-kpi"><span class="idh-kpi-label">Alumnos activos</span><span class="idh-kpi-value"><?php echo esc_html( $kpis['students'] ); ?></span></div>
  <div class="idh-kpi"><span class="idh-kpi-label">Alumnos bloqueados</span><span class="idh-kpi-value"><?php echo esc_html( $kpis['blocked'] ); ?></span></div>
  <div class="idh-kpi"><span class="idh-kpi-label">Cursos</span><span class="idh-kpi-value"><?php echo esc_html( $kpis['courses'] ); ?></span></div>
  <a class="idh-kpi idh-kpi--link" href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'payment' => 'pending' ] ) ); ?>">
    <span class="idh-kpi-label">Matrículas pendientes de pago</span><span class="idh-kpi-value idh-text-warning"><?php echo esc_html( $kpis['pending'] ); ?></span>
  </a>
</div>

<div class="idh-panel">
  <div class="idh-panel-head">
    <h2>Certificados emitidos</h2>
    <p class="description">Se cuentan por <strong>fecha de emisión</strong> y solo de matrículas <strong>pagadas</strong>.</p>
  </div>

  <form method="get" class="idh-period-form" id="idh-period-form">
    <input type="hidden" name="page" value="idh-aula" />
    <label>Período
      <select name="period" data-period-select>
        <?php foreach ( IDH_Aula_Reports::PERIODS as $value => $label ) : ?>
          <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $range['period'], $value ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label data-period="month">Mes
      <select name="month">
        <?php foreach ( $months as $i => $name ) : ?>
          <option value="<?php echo (int) $i + 1; ?>" <?php selected( $range['month'] ?? (int) current_time( 'n' ), $i + 1 ); ?>><?php echo esc_html( $name ); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label data-period="semester">Semestre
      <select name="semester">
        <option value="1" <?php selected( $range['semester'] ?? 0, 1 ); ?>>1 (ene–jun)</option>
        <option value="2" <?php selected( $range['semester'] ?? 0, 2 ); ?>>2 (jul–dic)</option>
      </select>
    </label>
    <label data-period="month semester year">Año
      <select name="year">
        <?php for ( $y = $this_year; $y >= $this_year - 5; $y-- ) : ?>
          <option value="<?php echo (int) $y; ?>" <?php selected( $range['year'] ?? $this_year, $y ); ?>><?php echo (int) $y; ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label data-period="custom">Desde <input type="date" name="from" value="<?php echo esc_attr( $range['from'] ); ?>" /></label>
    <label data-period="custom">Hasta <input type="date" name="to" value="<?php echo esc_attr( $range['to'] ); ?>" /></label>
    <button class="button button-primary">Ver reporte</button>
    <a class="button" href="<?php echo esc_url( $export_url ); ?>">Exportar CSV</a>
  </form>

  <div class="idh-report-summary">
    <span class="idh-report-period"><?php echo esc_html( $range['label'] ); ?></span>
    <span class="idh-report-total"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
    <span class="idh-report-caption">certificados emitidos</span>
  </div>

  <div class="idh-report-grid">
    <div class="idh-card">
      <h3>Por mes</h3>
      <?php if ( ! $total ) : ?>
        <p class="idh-empty">Sin certificados en este período.</p>
      <?php else : ?>
        <div class="idh-columns" role="img" aria-label="Certificados por mes">
          <?php foreach ( $by_month as $ym => $count ) : ?>
            <div class="idh-column" title="<?php echo esc_attr( IDH_Aula_Reports::month_label( $ym ) . ': ' . $count ); ?>">
              <span class="idh-column-value"><?php echo esc_html( $count ); ?></span>
              <span class="idh-column-bar" style="height:<?php echo esc_attr( round( $count / $max_month * 100 ) ); ?>%"></span>
              <span class="idh-column-label"><?php echo esc_html( IDH_Aula_Reports::month_label( $ym ) ); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="idh-card">
      <h3>Por categoría</h3>
      <?php if ( ! $by_category ) : ?>
        <p class="idh-empty">Sin datos.</p>
      <?php else : ?>
        <ul class="idh-bars">
          <?php foreach ( $by_category as $row ) :
              $color = IDH_Aula_Categories::color( $row->category ); ?>
            <li>
              <div class="idh-bar-head"><span><?php echo esc_html( IDH_Aula_Categories::label( $row->category ) ); ?></span><strong><?php echo esc_html( $row->total ); ?></strong></div>
              <div class="idh-bar-track"><span style="width:<?php echo esc_attr( round( $row->total / max( 1, $total ) * 100 ) ); ?>%;background:<?php echo esc_attr( $color ); ?>"></span></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="idh-card">
    <h3>Por curso / taller / webinar</h3>
    <?php if ( ! $by_course ) : ?>
      <p class="idh-empty">Sin certificados en este período.</p>
    <?php else : ?>
      <table class="wp-list-table widefat striped">
        <thead><tr><th>Curso</th><th>Categoría</th><th style="width:45%">Certificados</th></tr></thead>
        <tbody>
          <?php foreach ( $by_course as $row ) : ?>
            <tr>
              <td><a href="<?php echo esc_url( idh_aula_admin_url( 'idh-aula-matriculas', [ 'course_id' => $row->id, 'certificate' => 'with' ] ) ); ?>"><?php echo esc_html( $row->title ); ?></a></td>
              <td><?php echo IDH_Aula_Admin::category_badge( $row->category ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
              <td>
                <div class="idh-inline-bar">
                  <div class="idh-bar-track"><span style="width:<?php echo esc_attr( round( $row->total / $max_course * 100 ) ); ?>%"></span></div>
                  <strong><?php echo esc_html( $row->total ); ?></strong>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
