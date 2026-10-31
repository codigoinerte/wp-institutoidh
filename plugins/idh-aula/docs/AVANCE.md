# Avance — Aula Virtual IDH (fase 1)

**Fecha:** 2026-10-04
**Rama:** `develop`
**Entorno:** local (`https://institutoidh.local.io`), plugin activo
**Plan:** [`PLAN.md`](PLAN.md)

---

## Resumen
La fase 1 (versión manual, S/240) está implementada como el plugin `wp-content/plugins/idh-aula/`. La probé de punta a punta con curl y wp-cli. **Falta revisarla visualmente en el navegador** (admin y portal en celular), porque la extensión de Chrome no estaba disponible.

## Hecho

### Base de datos
- [x] 7 tablas propias (`idh_aula_students`, `courses`, `resources`, `sessions`, `session_resources`, `enrollments`, `certificates`), versionadas con la opción `idh_aula_db_version`.
- [x] `doc_type` existe desde el inicio (solo DNI por ahora), con la clave única `(doc_type, doc_number)`, lista para la migración a CE o pasaporte.
- [x] `certificates.code` es único: queda preparado para el QR de la fase 2.
- [x] Carpeta privada `uploads/idh-aula/certificados/` con `.htaccess` que bloquea el acceso directo.

### Portal del alumno (`/aula-virtual`)
- [x] Login con DNI + fecha de nacimiento, cookie firmada (HMAC) y nonce.
- [x] Rate limit: 5 intentos fallidos por IP cada 15 min y 10 por DNI cada hora.
- [x] Listado de cursos en tarjetas (estilo del prototipo), con filtro por categoría y badge "Certificado disponible".
- [x] Candado en las matrículas bloqueadas: "Pendiente de pago" o "Acceso restringido".
- [x] Detalle del curso basado en `idh-prototype/index.html`: clases, video en ventana (en móvil abre YouTube), materiales, certificado y verificador con botón "Copiar".
- [x] `/aula-virtual/certificado/{code}`: sirve el PDF o redirige a la URL externa, y responde 403 si el alumno o la matrícula están bloqueados.
- [x] Revalidación del alumno en cada request: un bloqueo cierra su sesión de inmediato.
- [x] `/aula-virtual/salir`.

### Admin (wp-admin → Aula Virtual, solo administradores)
- [x] **Panel y reportes:** KPIs y certificados por mes, semestre, año o rango personalizado, desglosados por curso y por categoría (solo matrículas pagadas, por fecha de emisión), con exportación a CSV.
- [x] **Alumnos:** búsqueda por DNI, nombre, celular o correo; filtro activo/bloqueado; matrícula de los seleccionados; exportación a CSV.
- [x] **Ficha del alumno:** datos, bloqueo con motivo, resumen (pagados, pendientes, certificados) y matrículas editables (pago, precio, candado, motivo y certificado URL o PDF con verificador y fecha de emisión).
- [x] **Cursos:** CRUD con categoría (reutiliza las Categorías de Portada del theme), horas, docente, fecha, precio privado, portada y landing vinculada; repetidor de clases ordenable con selector de recursos en modal; acción "Duplicar" para la reedición anual.
- [x] **Biblioteca de recursos:** YouTube, Drive y enlace, con columna "usado en N cursos". Un recurso en uso no se puede eliminar.
- [x] **Matrículas:** filtros por curso, pago, acceso y certificado; cambio de pago y candado directo en la lista (AJAX); matrícula masiva pegando DNIs (informa los no registrados y los inválidos); exportación a CSV.
- [x] **Importar alumnos (CSV):** vista previa con validación (DNI, fecha, duplicados, curso), detección de separador `,` o `;`, soporte de Windows-1252, relleno de ceros en los DNI y plantilla descargable.
- [x] **Copiar enlace:** caja "Enlace público" en el editor de páginas y entradas (se actualiza en el editor de bloques) y acción de fila en los listados.
- [x] Protecciones: no se eliminan alumnos con matrículas, cursos con alumnos ni recursos en uso.

### Ajustes posteriores (revisión en el navegador)
- [x] **Fix:** el filtro por categoría del listado no ocultaba las tarjetas (`display: flex` pisaba el atributo `hidden`).
- [x] **Logo en el navbar:** toma el logo del personalizador; si no hay, el del theme (`kadence-child/assets/global/images/logo.png`) y, en último caso, el ícono del sitio. Mide 40 px de alto, con el nombre del sitio al lado.
- [x] **Matrícula individual (Matrículas → «Matricular alumno»):** eliges el curso, buscas por DNI o nombre con autocompletado (marca «Ya matriculado» y «Alumno bloqueado»), agregas uno o varios y matriculas. Usa el mismo handler de la matrícula masiva.
- [x] **Rate limit detrás de Cloudflare:** la IP sale de `CF-Connecting-IP` (con fallback a `REMOTE_ADDR`).
- [x] **Pago pendiente:**
  - Matrícula pendiente sin candado: el alumno ve las clases y los materiales, pero el **certificado y el verificador quedan retenidos**. Además, `/aula-virtual/certificado/{code}` responde 403 con "Certificado retenido".
  - Tag "Pago pendiente" con **tooltip HTML personalizado** (hover, foco o toque en móvil) en el listado y en el detalle.
  - En el detalle, la fila del certificado se reemplaza por "Certificado retenido" (ámbar, con candado y tooltip).
  - El badge "Certificado disponible" solo aparece si el certificado se puede descargar.
  - Con candado, el curso sigue bloqueado por completo, como antes.

### Matrícula por clases (pack de talleres por tema, 2026-10-29)
Pedido de Erick: IDH vende un pack de 7 talleres grabados, pero muchos alumnos compran solo 1, 2 o 3 temas.
- [x] Cada matrícula tiene **«Clases incluidas»: Todas** (por defecto; incluye también las que se agreguen después) **o Solo algunas**, con casillas por clase. Se elige al matricular (individual, masiva y desde la ficha del alumno; el selector carga las clases del curso elegido) y se edita en la tarjeta de la matrícula.
- [x] Portal: las clases no compradas aparecen con candado («Clase no incluida en tu matrícula»), sin videos ni materiales: las URLs no llegan al navegador. Aviso «Tu matrícula incluye N de M clases» con botón de WhatsApp «Quiero más clases».
- [x] Badge «N de M clases» en el listado del alumno, en la ficha y en el listado de matrículas; columna «Clases» en el CSV.
- [x] DB 1.1.0: columna `enrollments.access_scope` y tabla `idh_aula_enrollment_sessions`.
- [x] **Las clases ya no se borran y recrean al guardar el curso**: conservan su ID (antes cambiaba en cada guardado). Si se quita una clase, sale también de las matrículas parciales.
- La matrícula masiva no modifica las clases de los alumnos que ya estaban matriculados (se avisa en el mensaje).
- [x] **Mensajes para el alumno editables por matrícula** (ficha del alumno → tarjeta de la matrícula → «Mensajes para el alumno»; vacío = texto por defecto):
  - «Curso bloqueado por falta de pago»: va después de «Pendiente de pago.» en la tarjeta del listado y reemplaza el texto de la página de acceso bloqueado.
  - «Clases no incluidas»: texto del candado de cada clase no comprada.
  - Primero se hicieron por curso (DB 1.2.0 y 1.3.0); en la 1.4.0 pasaron a `enrollments`. La migración copia a sus matrículas los mensajes escritos en un curso (sin pisar los propios) y elimina las columnas del curso.
- [x] La ficha del alumno solo ofrece los cursos en los que aún no está matriculado.

## Pruebas realizadas (local)
| Prueba | Resultado |
|---|---|
| Activación: tablas, reglas de rewrite, carpeta privada | OK |
| Login correcto, incorrecto y rate limit (6.º intento) | OK |
| Listado y detalle; curso bloqueado → 403; curso ajeno → 404; sin sesión → redirección | OK |
| PDF servido (200), acceso directo al archivo (403), alumno bloqueado (403) | OK |
| Bloqueo del alumno: sesión expulsada y login rechazado con mensaje | OK |
| Reportes por mes y semestre; las matrículas pendientes no cuentan | OK |
| Guardar curso con clases (orden respetado, clases vacías ignoradas) | OK |
| Importación CSV con errores mixtos y confirmación | OK |
| Matrícula masiva con DNIs válidos, no registrados e inválidos | OK |
| Subida del PDF de certificado desde la ficha | OK |
| AJAX: toggles, búsqueda y creación de recursos (con validación de YouTube) | OK |
| Exportaciones: reporte, matrículas y alumnos (UTF-8 con BOM) | OK |
| Duplicar curso (queda en borrador, con las mismas clases) | OK |
| Render de todas las pantallas del admin sin errores PHP | OK |
| Pago pendiente sin candado: tag y tooltip, certificado retenido, sin URLs expuestas, ruta del certificado → 403 | OK |
| Al marcar como pagado: certificado → 200 y sin tags | OK |

## Desviaciones menores del plan
- Los listados del admin usan tablas HTML con los estilos nativos de WP, en lugar de `WP_List_Table`.
- Los gráficos del panel son barras CSS (sin Chart.js).

## Pendiente
- [ ] Revisión visual en el navegador del admin y del portal (desktop y móvil 375 px). El listado, el filtro y el logo ya los revisó el usuario; falta revisar el tooltip y el admin.
- [ ] Prueba manual del botón "Copiar enlace" en el editor de una landing.
- [ ] Decidir si el DNI se exporta de forma que Excel conserve el cero inicial.
- [ ] Si producción usa Nginx, agregar la regla `location ^~ /wp-content/uploads/idh-aula/ { deny all; }`.
- [ ] Limpiar los datos de prueba de la base local antes de la entrega.
- [ ] Configurar el repo remoto y el workflow de deploy (`testing` → sitio de prueba, `main` → web pública). Después del primer deploy, activar el plugin.
- [ ] Revisión con el cliente.

## Datos de prueba en local
- Alumno demo: DNI `45678912`, fecha de nacimiento `15/03/1990`. Su curso "Herramientas Inclusivas…" está en **pago pendiente** para mostrar el tag y el certificado retenido.
- Hay 4 cursos (uno duplicado en borrador), 4 recursos, 3 alumnos y 2 PDFs de ejemplo en `uploads/idh-aula/certificados/2026/`.

## Fase 2 (noviembre, fuera de este alcance)
- Generación automática de certificados PDF con QR propio que apunte a `/verificar/{code}` (modo `generated`).
- Requiere los diseños de los certificados (Canva o PPTX) y definir el almacenamiento.
