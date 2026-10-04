# Plan: Aula Virtual IDH (fase 1 — versión manual, S/240)

## Contexto
IDH entrega hoy los enlaces de clases, los materiales y los certificados uno por uno por WhatsApp, y los alumnos los pierden. El cliente quiere un portal donde el alumno ingrese con su DNI y vea sus cursos/talleres/webinars con sus clases (video de YouTube privado y materiales de Drive) y su certificado. El administrador matricula a cada alumno, controla los pagos, bloquea alumnos o matrículas específicas y consulta reportes de certificados emitidos. La fase 2 (noviembre) generará los certificados en PDF y QR desde la plataforma, así que el modelo de datos ya debe contemplarlo.

El sitio es WordPress (`/var/www/institutoidh`, prefijo `idh_`, entorno de testing; producción en `/var/www/idh.com.pe`) con el child theme `kadence-child` y Meta Box 5.11 (versión gratuita). El prototipo de referencia es `~/Desktop/open-projects/idh-prototype/index.html`.

## Decisiones tomadas
- **Plugin propio** `wp-content/plugins/idh-aula/` con tablas propias. Es independiente del theme.
- **Login:** DNI (8 dígitos) + fecha de nacimiento. Por ahora solo DNI; la estructura queda lista para Tipo + número (ver la sección de migración).
- **Contenido:** pertenece al curso, no al alumno. Las clases usan una **Biblioteca de recursos** reutilizable (YouTube, Drive, enlace), al estilo de la biblioteca de medios de WordPress: se registra una vez y se usa en varios cursos. El **certificado pertenece a la matrícula** (alumno × curso).
- **Pagos:** por matrícula, estado `pagado`/`pendiente`. El curso tiene un **precio privado**, que se copia a la matrícula al matricular para calcular ingresos más adelante.
- **Bloqueos:** a nivel alumno (no puede ingresar y sus PDFs servidos por la plataforma dejan de responder) y a nivel matrícula (el curso aparece con candado). Las URLs externas (qr.io) solo se ocultan en el portal.
- **Reportes:** cuentan certificados por **fecha de emisión**, **solo de matrículas pagadas**.
- **Acceso al admin:** solo administradores (`manage_options`).
- **Categorías:** se reutiliza `kadence_child_get_cover_categories()` (`themes/kadence-child/functions.php:186`), con fallback si el theme no está activo. El cliente agrega "Curso taller", "Curso de especialización" y "Seminario" desde la pantalla existente.
- **URL:** `/aula-virtual`.

## Modelo de datos (`dbDelta`, versionado con la opción `idh_aula_db_version`)
| Tabla | Campos clave |
|---|---|
| `idh_aula_students` | id, `doc_type` (default `'DNI'`), `doc_number` varchar(20), first_name, last_name, birth_date, phone, email, `status` (active/blocked), blocked_reason, notes, timestamps. **UNIQUE(doc_type, doc_number)** |
| `idh_aula_courses` | id, title, slug, category (value de cover categories), description, cover_image_id (adjunto WP), hours, instructor, `price` decimal (privado), landing_page_id (opcional, enlaza la landing existente), start_date, status (draft/published), timestamps |
| `idh_aula_resources` | id, type (youtube/drive/link), title, url, timestamps |
| `idh_aula_sessions` | id, course_id, position, title, description, session_datetime |
| `idh_aula_session_resources` | session_id, resource_id, position |
| `idh_aula_enrollments` | id, student_id, course_id, `payment_status` (paid/pending), `price_paid` (copiado del curso, editable), `locked` (0/1), lock_reason, enrolled_at. UNIQUE(student_id, course_id) |
| `idh_aula_certificates` | id, enrollment_id UNIQUE, `code` (único, corto, para la fase 2 QR), `mode` (url/pdf; la fase 2 añade `generated`), external_url, pdf_path, verify_url (opcional en modo PDF), issued_at, timestamps |

Los PDFs se guardan en `uploads/idh-aula/certificados/` con `.htaccess` `Deny from all` y nombre aleatorio. Se sirven por PHP solo si el alumno está activo y la matrícula no está bloqueada.

## Estructura del plugin
```
idh-aula/
  idh-aula.php                 bootstrap, activación (tablas + rewrite flush), constantes
  includes/
    class-schema.php           dbDelta + migraciones por versión
    class-repo-*.php           acceso a datos (students, courses, resources, enrollments, certificates)
    class-auth.php             login DNI+fecha, cookie firmada (HMAC con wp_salt, student_id+exp), rate limit por IP (transients: 5 intentos/15 min)
    class-router.php           add_rewrite_rule + template_redirect
    class-categories.php       wrapper de cover categories
    class-reports.php          consultas agregadas
    class-csv.php              import/export (UTF-8 con BOM para Excel)
  admin/
    class-admin-menu.php       menú "Aula Virtual"
    list-tables/               WP_List_Table: alumnos, cursos, recursos, matrículas
    views/                     formularios (vanilla JS para el repetidor de clases + modal selector de recursos)
    class-copy-link.php        botón "Copiar enlace" en el editor de páginas/posts
  public/
    templates/ login.php, listado.php, curso.php, bloqueado.php
    assets/ aula.css, aula.js   (estilos portados del prototipo: Inter, tarjetas, modal YouTube)
```

## Portal del alumno (frontend)
- `GET/POST /aula-virtual`: si no hay sesión, muestra el login (DNI + fecha de nacimiento, mensaje genérico de error, nonce y rate limit). Con sesión, muestra el **listado de cursos** en tarjetas al estilo del prototipo (portada, badge de categoría, título, horas). Las matrículas `locked` se ven con candado y el mensaje "Pendiente de pago" (si payment=pending) o "Acceso restringido, comunícate con IDH", sin enlace al detalle.
- `/aula-virtual/curso/{id}`: es el detalle de `index.html` del prototipo, con datos reales: portada, título, descripción, alumno, badges (categoría, horas), sesiones con fecha, filas de video (modal en desktop, YouTube en móvil, misma lógica JS) y filas de materiales (N por sesión). Al final va el bloque **Certificado**: "Descargar certificado" (PDF → ruta protegida; URL → enlace externo), "Ver verificador" (verify_url) y el botón **Copiar enlace** del verificador. Si no hay certificado, se muestra "Certificado aún no disponible".
- `/aula-virtual/certificado/{code}`: sirve el PDF con validaciones de alumno activo y matrícula no bloqueada.
- `/aula-virtual/salir`: cierra la sesión.
- Cualquier request con sesión vuelve a validar `status` del alumno, así que un bloqueo surte efecto de inmediato.

## Administración (wp-admin → "Aula Virtual")
1. **Panel / Reportes:** tarjetas con totales y filtro de período (mes, semestre, año, rango personalizado). Incluye certificados por curso, por categoría y una serie mensual (gráfico con Chart.js incluido localmente en el plugin). Exporta CSV.
2. **Alumnos:** búsqueda por DNI, nombre o apellido, y filtro activo/bloqueado. La ficha del alumno tiene sus datos, el botón **Bloquear/Desbloquear alumno** (con motivo) y la tabla de sus matrículas con toggles en línea (pagado/pendiente, bloqueado), el precio y el formulario del certificado (modo URL o PDF, fecha de emisión, verify_url). No se eliminan alumnos con matrículas: solo se bloquean.
3. **Cursos:** CRUD con categoría, horas, docente, precio privado, portada (media library), landing vinculada y un repetidor de **clases** (título, descripción, fecha y hora, recursos elegidos de la biblioteca o creados en línea). Incluye la acción "Duplicar curso" para la reedición anual.
4. **Biblioteca de recursos:** CRUD de recursos YouTube/Drive/enlace, con buscador y una columna "usado en N cursos".
5. **Matrículas:** listado filtrable por curso, estado de pago y bloqueo; exporta CSV. Incluye **matrícula masiva**: elegir un curso y pegar una lista de DNIs o seleccionar alumnos; informa los DNIs no encontrados.
6. **Importar alumnos (CSV):** columnas `dni, nombres, apellidos, fecha_nacimiento, celular, correo` y opcionalmente `curso_id`. Hace una vista previa con validación (8 dígitos, fecha válida, duplicados) antes de confirmar. Incluye una plantilla descargable.
7. **Copiar enlace (landings):** `add_meta_box` lateral (prioridad alta) en el editor de `page`/`post`, con la URL pública en un input de solo lectura y un botón "Copiar" (`navigator.clipboard` con fallback). También se agrega la acción de fila "Copiar enlace" en el listado de páginas.

Todos los formularios usan nonces, `current_user_can('manage_options')`, sanitización y `$wpdb->prepare`.

## Plan de migración DNI → Tipo + número
La columna `doc_type` existe desde el día 1 (siempre `'DNI'`) y la clave única es (doc_type, doc_number), así que la migración no toca datos:
1. Agregar `CE` y `PASAPORTE` a una constante de tipos, con validación por tipo (DNI 8 dígitos; CE 9-12 alfanumérico; pasaporte 6-12 alfanumérico).
2. Admin: agregar un select de tipo en la ficha, la columna `tipo_doc` opcional en el CSV (default DNI) y mostrar el tipo en los listados.
3. Login: se sigue pidiendo solo número + fecha de nacimiento. La búsqueda es por `doc_number` y, si hay colisión entre tipos, se agrega un select de tipo en el login.
4. Subir `idh_aula_db_version` solo si hiciera falta un índice nuevo.

## Preparación para la fase 2
- `certificates.code` ya es único y público, así que servirá para el QR propio y la página de verificación `/verificar/{code}`.
- `mode` admite el valor `generated`. Los datos para generar el PDF (nombre, DNI, curso, horas, fecha de emisión) ya existen en las tablas.

## Desarrollo local y ramas
Todo se desarrolla **de forma local** en `/var/www/institutoidh`. No hay deploy en esta etapa.
- **Primer paso:** crear el plugin y copiar este plan a `idh-aula/docs/PLAN.md` como primer commit en `develop`.
- Se inicializa un repo git propio en `wp-content/plugins/idh-aula/`, independiente del repo del child theme.
Flujo de ramas (el mismo esquema del child theme):
- **`develop`**: rama de trabajo, donde se hacen los commits del avance local. No despliega nada.
- **`testing`**: despliega al **sitio de prueba** en el mismo servidor (`/var/www/institutoidh`). Recibe merges desde `develop` cuando haya algo para que el cliente revise.
- **`main`**: despliega a la **web pública** con el dominio principal (`/var/www/idh.com.pe`). Solo recibe merges desde `testing` ya validado.
- Los cambios que haya que hacer en el child theme (si los hubiera) van en una rama `feature/aula-virtual` del repo `kadence-child`, porque un push a `testing`/`main` dispara el deploy actual.
- **El despliegue se afina al publicar.** El workflow del plugin (equivalente a `kadence-child/.github/workflows/deploy.yml`), la activación y el flush de rewrite en cada entorno se configurarán en ese momento.

## Verificación
1. Activar el plugin en el entorno local y confirmar que se crean las 7 tablas y que `/aula-virtual` responde (con `wp option get idh_aula_db_version` o SQL).
2. Crear 3 recursos y 1 curso con 2 clases que los reutilicen. Duplicar el curso y comprobar que los recursos se comparten.
3. Crear un alumno a mano e importar 5 por CSV (incluido un DNI inválido y un duplicado, que la vista previa debe marcar).
4. Hacer una matrícula masiva por lista de DNIs.
5. Iniciar sesión con DNI + fecha. Probar una fecha incorrecta y 6 intentos fallidos, que deben activar el rate limit.
6. Revisar el listado y el detalle: el video abre el modal en desktop y YouTube en móvil (probar el viewport de 375 px con las herramientas de Chrome). Los materiales y el certificado deben funcionar en modo URL y en modo PDF.
7. Bloquear una matrícula pendiente: debe aparecer el candado con "Pendiente de pago" y `/curso/{id}` debe devolver 403. Las demás matrículas siguen accesibles.
8. Bloquear al alumno: la sesión activa debe expulsarlo, el login debe rechazarlo, `/aula-virtual/certificado/{code}` debe devolver 403 y el acceso directo a `uploads/idh-aula/certificados/*.pdf` debe devolver 403.
9. Reportes: comparar los conteos con SQL manual por mes, semestre, año y rango, confirmar que las matrículas pendientes no cuentan y que el CSV abre bien en Excel (con tildes).
10. Copiar enlace: el botón debe funcionar en el editor de una landing y en la acción de fila.

## Notas de implementación (desviaciones menores del plan)
- Los listados del admin usan tablas HTML con las clases nativas de WP (`wp-list-table widefat striped`) en lugar de heredar `WP_List_Table`. Se ven igual y es más simple de mantener.
- Los gráficos del panel son barras en CSS (sin Chart.js), así que no hay dependencias externas.
- El login tiene un límite de 5 intentos fallidos por IP cada 15 min y de 10 por DNI cada hora.
- La importación CSV rellena con ceros a la izquierda los DNI de menos de 8 dígitos, porque Excel elimina el cero inicial.
