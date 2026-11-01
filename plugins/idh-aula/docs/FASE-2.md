# Fase 2: certificados PDF autogenerados con QR

## Contexto
La fase 1 del aula (`wp-content/plugins/idh-aula`) ya está en producción con DB 1.5.0. Los certificados por matrícula y por clase funcionan en modo `url` (qr.io) o `pdf` (archivo subido a mano). La fase 2 hace que la plataforma genere el PDF con su propio QR.

Requisitos del cliente (audio de WhatsApp del 23/10 y chats):
- El QR es **el único ente verificador**. Apunta a la página pública `/verificar/{code}`.
- Los certificados ya entregados (modos url/pdf) se quedan como están.
- No hay firma digital del ministerio: va la firma de Erick como imagen. Tiene que haber **firmantes adicionales** (por ejemplo, un decano, por un convenio) que se marquen como un checklist.
- Aplica también a los **certificados por clase o tema**.
- La forma de mostrarlo al alumno (solo el PDF, o el PDF en un iframe dentro de una página) se decide después.

Primero se hace la **demo del PDF** hasta que el diseño cuadre con Canva. Después viene la lógica.

## Referencia de diseño
Las vistas previas de Canva (págs. 1–7 de «BROCHURE TCC NIÑOS») solo sirven de guía. Las referencias exactas son PDF planos, con anverso y reverso.

### Diseño azul (disponible, se empieza por este)
Archivo: `~/Downloads/Sheyla-Tatiana-Caballero-Bobadilla.pdf`. Está hecho en PowerPoint 2019. Ya lo convertí a PNG de 80 dpi en la carpeta temporal de la sesión (`scratchpad/ref/azul-{1,2}.png`).

- **Página:** 960 × 540 pt (16:9), no A4. La demo usa el mismo tamaño.
- **Imágenes embebidas** (se extraen con `pdfimages -png`):
  - el fondo con las franjas, de 2000×1414;
  - el logo de la cabeza de colores;
  - la firma de Erick;
  - el **sello circular «Instituto de Desarrollo de Habilidades Profesionales – IDH»**;
  - el QR de qr.io, que tiene el logo al centro.
  
  Se guardan en `assets/certificados/azul/`.
- **Fuentes:** Calibri, Arial Narrow y JavaneseText (la del «IDH» grande). Son de Microsoft y no se pueden redistribuir, así que se usan sustitutas libres con las mismas métricas:
  - Calibri → **Carlito**
  - Arial Narrow → **Liberation Sans Narrow**
  - JavaneseText → el «IDH» del encabezado se extrae como SVG vectorial con `pdftocairo -svg`. Si no sale bien, se usa una serif parecida (Tinos o Noto Serif).
- **Anverso:**
  - logo e «IDH» con el subtítulo «INSTITUTO DESARROLLO DE HABILIDADES PROFESIONALES», luego «Otorga el presente:»;
  - «CERTIFICADO» grande en gris azulado;
  - «**Otorgado** a:» y el nombre en negrita, sobre una línea;
  - el párrafo centrado: «Por su participación en el **"{curso}"**, desarrollado en modalidad virtual durante el mes de {mes} de {año}, con una duración de **{horas} horas académicas**.». Si el curso abarca más de un mes, el período dice «del {inicio} al {fin}». Las fechas se calculan con `start_date` y las clases;
  - la firma con el sello encima, «Erick Núñez Vitor / **Gerente General**»;
  - el QR abajo a la derecha, sin leyenda.
- **Reverso:**
  - el logo arriba a la derecha;
  - «**EJES TEMATICO DE NUESTRO "{CURSO}"**» y las viñetas, que salen de `certificate_content`;
  - el QR abajo a la derecha con la leyenda «QR para verificación».
- **QR propio:** chillerlan admite poner el logo IDH al centro, como en el de qr.io. Se usa corrección de errores H.

### Diseño negro y dorado (Especialización): pendiente
El usuario todavía no tiene el PDF. Se hace después con el mismo método.

- **Especialización** (negro y dorado), para la categoría `curso-especializacion`:
  - Anverso: logo IDH, cinta «Certificado», «Otorgado a:», nombre en caligrafía y el párrafo «Por su participación en nuestro Curso de Especialización "{curso}", realizado de manera virtual, con una duración de {horas} horas académicas, del {inicio} al {fin}». Abajo, la firma «Gerente General».
  - Reverso: tipo y título del curso, módulos con viñetas, QR abajo a la derecha y logo arriba a la derecha.
- **Taller / Seminario / Webinar** (azul), para las demás categorías:
  - Anverso: franjas en las esquinas, «CERTIFICADO», «Otorgado a:», nombre, párrafo, firma «Representante IDH» y un QR pequeño.
  - Reverso: «Bloque temático» y el QR con la leyenda «QR para verificación».

## Decisiones
- **Librerías:** se instalan con Composer dentro del plugin. **dompdf** convierte HTML/CSS a PDF y usa GD, porque el servidor no tiene imagick. **chillerlan/php-qrcode** genera el QR en SVG o PNG. La carpeta `vendor/` se versiona para que el deploy por rsync siga igual. Hay que revisar `.gitignore` y `DESPLIEGUE-PRODUCCION.md`.
- **Plantillas:** `templates/certificados/taller.php` (azul, primero) y luego `especializacion.php`, en 960×540 pt con 2 páginas.
- **Firmantes con sello:** cada firmante tiene una imagen de firma y, opcionalmente, una imagen de sello.
  - Las decoraciones de Canva se exportan como PNG o SVG a `assets/certificados/`.
  - Las fuentes van como TTF locales en `assets/fonts/`, registradas en dompdf: una caligráfica para el nombre, una serif y una sans.
- **Asignación del diseño:** por categoría. La ficha del curso tiene un selector «Plantilla del certificado» (por defecto «Según categoría»). La resolución la hace `IDH_Aula_Certificates::template_for($course)`.
- **Reverso:** un campo nuevo en el curso, `certificate_content` (textarea con módulos y viñetas). Admite líneas de título de módulo y líneas que empiezan con «-» para las viñetas.
- **Certificado por clase:** usa el mismo diseño del curso. El párrafo dice «Por su participación en la clase "{tema}" del {tipo} "{curso}"», con la fecha de la clase. El reverso lleva la descripción del tema y el QR.
- **Firmantes:**
  - En **Ajustes** se define una lista global (nombre, cargo, imagen de firma desde la biblioteca de medios). Se guarda en `idh_aula_signers` y uno queda marcado como «por defecto» (Erick, Gerente General).
  - En la ficha del curso hay un checklist de firmantes, guardado en `course.signer_ids` (JSON). Si está vacío, se usan los firmantes por defecto.
  - La plantilla acomoda de 1 a 3 firmas.
- **Emisión y congelado:** al guardar el modo `generated` con una fecha de emisión, el PDF se genera y se guarda en la carpeta privada que ya existe, `uploads/idh-aula/certificados/{Y}/`, con `pdf_path`.
  - En la tabla se guarda además `snapshot` (JSON con el nombre, el documento, el curso, las horas, las fechas y los firmantes), para que el certificado emitido no cambie solo.
  - El botón **«Regenerar»** vuelve a generar el PDF con los datos actuales y conserva el mismo `code` y el mismo QR.
- **QR:** apunta a `home_url('/verificar/{code}')`. La página es pública y `noindex`.
  - Muestra si el certificado es válido, el nombre, el documento enmascarado (`IDH_Aula_Documents::display`), el curso o la clase, las horas, la fecha de emisión y los firmantes.
  - Si el certificado se borró, muestra «no encontrado».
  - Un alumno bloqueado o con pago pendiente no anula la validez, porque el certificado ya se emitió. Esto lo confirma el usuario después.

## Pasos

### 0. Rama y documentación (sin perder lo registrado)
1. Desde `wp-content`, crear la rama: `git fetch && git switch -c fase-2 origin/testing`.
2. En `docs/PLAN.md`, ampliar «Preparación para la fase 2» con el resumen de este plan. En `docs/AVANCE.md`, actualizar la sección «Fase 2» con los requisitos del cliente: QR único verificador, firmantes, certificados por clase y entregados sin cambios. No se borra nada de lo que ya está.
3. Copiar este plan a `docs/FASE-2.md`.
4. Actualizar la memoria `aula-virtual-plugin.md` con la rama `fase-2`.

### 1. Demo del PDF (lo prioritario)
1. Ejecutar `composer require dompdf/dompdf chillerlan/php-qrcode` en el plugin y cargar `vendor/autoload.php` en `idh-aula.php`.
2. Crear `includes/class-certificate-pdf.php` con `IDH_Aula_Certificate_PDF::render(array $data, string $template): string`, que devuelve el binario del PDF. También tendrá `qr_svg($url)`.
3. Crear las plantillas y los assets a partir de los PDF planos. Las decoraciones y las fuentes se extraen con `pdfimages`/`pdffonts` o se exportan desde Canva.
4. Crear el script de demo `bin/demo-certificado.php`, que se corre con `wp eval-file` o con PHP CLI y datos de ejemplo. Genera `demo-especializacion.pdf`, `demo-taller.pdf` y `demo-clase.pdf` en la carpeta temporal de la sesión.
5. **Validación visual**, en ciclo hasta que cuadre:
   - Generar la demo con los mismos datos de la referencia: Sheyla Tatiana Caballero Bobadilla, «IV Seminario de Neurodiversidad Infantil TEA…», 50 horas, octubre de 2026.
   - Convertir la demo a PNG con `pdftoppm -r 80`, la misma resolución que la referencia.
   - Compararlas lado a lado (Read de las dos imágenes).
   - Superponerlas con ImageMagick, que está instalado (`compare -metric AE` y `composite -dissolve 50`). Así se ven los desplazamientos.
   - Revisar márgenes, tamaños de letra, posición del QR, de la firma y del sello, nombres largos y párrafos de varias líneas.
   - `zbarimg` no está instalado. El QR se valida decodificándolo en PHP o con el lector de la cámara del usuario.

### 2. Datos y admin
1. Schema 1.6.0 en `includes/class-schema.php`:
   - `courses.certificate_template`, `courses.certificate_content` y `courses.signer_ids`.
   - `snapshot longtext` en `idh_aula_certificates` y en `idh_aula_session_certificates`.
2. Agregar `'generated' => 'Generado por la plataforma'` a `MODES` (`includes/class-repo-certificates.php:15`). En `persist()` (`:136-187`), el modo generated no pide archivo: arma el snapshot, llama a `render()` y guarda el archivo reutilizando `storage_dir()`, el nombre aleatorio y `delete_file()`.
3. Formularios:
   - `admin/views/partials/enrollment-form.php` y `session-certificates.php`: tercera opción de modo, botones «Regenerar» y «Vista previa».
   - `admin/assets/admin.js:67-74`: el toggle del modo.
   - Ficha del curso (`course-edit.php`): plantilla, contenido del reverso y checklist de firmantes.
4. Ajustes (`admin/views/settings.php` y `class-admin-actions.php:354`): una tarjeta «Firmantes» con el selector de medios. Se reutiliza el patrón de `admin.js:130`.

### 3. Rutas y portal
1. En `includes/class-router.php`, agregar la regla `^verificar/([A-Za-z0-9]+)/?$`, que lleva a `idh_aula=verify`, con su plantilla pública `public/templates/verificar.php`.
2. Subir `IDH_AULA_VERSION` (`idh-aula.php:15`) para que se vuelvan a generar las reglas.
3. `/aula-virtual/certificado/{code}` sirve el PDF generado igual que el modo `pdf`, con las mismas validaciones (`class-router.php:174-215`).
4. `student_url()` / `student_url_for()` (`:288-303`): el modo generated se trata igual que pdf. El enlace «Ver verificador» apunta a `/verificar/{code}`.
5. Lo de mostrar el PDF en un iframe dentro de una página queda para después.

### 4. Cierre
- Agregar al `.gitignore` las excepciones de `vendor/` y revisar que el rsync de los workflows lo incluya.
- Actualizar `AVANCE.md` (Hecho / Pruebas) y el manual.

## Verificación
- La demo: los PNG de la demo y de la referencia coinciden visualmente en los dos diseños, en anverso y reverso. Probar con un nombre largo, un curso de título largo, 1, 2 y 3 firmantes, y un certificado por clase.
- El QR escaneado abre `/verificar/{code}` y muestra los datos correctos. Un código inexistente muestra «no encontrado».
- Flujo en el admin:
  - Emitir en modo generated → el alumno lo descarga desde el portal.
  - Bloquear al alumno → 403 en `/aula-virtual/certificado/{code}`, pero la verificación sigue siendo válida.
  - Pago pendiente → el certificado queda retenido.
  - «Regenerar» conserva el código.
- Los certificados existentes en modo url/pdf no cambian.
- Hacer el `dbDelta` 1.5.0 → 1.6.0 en una copia y probar en `testing` antes de `main`.

## Pendiente del usuario
- PDF plano del diseño **negro y dorado** (Especialización). El azul ya está.
- Opcional: la firma y el sello en PNG de mayor resolución. Por ahora se usan los que vienen en el PDF.
- Confirmar si la verificación sigue siendo válida cuando el alumno está bloqueado.

## Avance (2026-10-31)

### Hecho (sin commit, en la rama `fase-2`)
- **Rama `fase-2`** creada desde `origin/testing`, sin upstream.
- **Documentación:** `PLAN.md` y `AVANCE.md` actualizados con los requisitos de la fase 2; este plan quedó en `docs/FASE-2.md`.
- **Composer** (`composer.json` y `composer.lock`, `platform.php` = 7.4.33): dompdf ^3.1 y chillerlan/php-qrcode ^5.0 (la v6 pide PHP 8.2). `vendor/` pesa 12 MB.
- **`includes/class-certificate-pdf.php`:** `IDH_Aula_Certificate_PDF`.
  - `render( $data, $template )` devuelve el binario del PDF.
  - `qr_data_uri( $url, $logo )` arma el QR en PNG: módulos redondos, buscadores cuadrados, logo al centro y corrección de errores H.
  - Funciona fuera de WordPress.
  - La caché de fuentes va en `uploads/idh-aula/cache-pdf`, o en `/tmp/idh-aula-pdf` cuando corre por CLI.
  - Todavía **no** se carga desde `idh-aula.php`.
- **`templates/certificados/taller.php`:** plantilla azul, anverso y reverso.
  - Variantes `kind` = `course` | `session`.
  - De 1 a 3 firmantes (`name`, `role`, `signature`, `seal`).
  - El nombre se achica cuando pasa de 40 caracteres.
  - El título del reverso va en negrita hasta el «:».
- **`assets/certificados/azul/`:** `fondo.png`, `logo.png` (incluye la flecha amarilla), `idh.png` (logotipo en JavaneseText), `firma.png` y `sello.png`. El sello va pre-girado 12,83°, como en la referencia.
- **`assets/fonts/`:** Carlito y Liberation Sans Narrow (Regular y Bold). Reemplazan a Calibri y Arial Narrow con las mismas métricas.
- **`bin/demo-certificado.php <carpeta>`:** genera `demo-taller.pdf` (los datos de la referencia), `demo-taller-largo.pdf` y `demo-clase.pdf`.

### Validación
- La demo se superpuso a la referencia con `composite -dissolve 50` a 80 y 200 dpi. Los textos quedan a ≤1,5 pt, los cortes de línea del párrafo son idénticos, y la firma, el sello, el logo y el QR calzan.
- El QR se lee desde el PDF a 150 dpi (lector de chillerlan).

### Notas técnicas (dompdf)
- `line-height` en `pt` sale inflado ×1,333 (96/72). Hay que usar valores **sin unidad**: 0,8987 para un interlineado de 21,57 pt con texto de 18 pt.
- Los textos se posicionan por línea base, con `line-height: 1`:
  - Liberation Sans Narrow: `top` = base − tamaño.
  - Carlito: `top` = base − 1,075 × tamaño.
- Los PNG con transparencia tienen que tener el alfa real (PNG32). La composición con PyMuPDF los dejaba opacos, con fondo negro.
- La referencia azul se extrajo de `~/Downloads/Sheyla-Tatiana-Caballero-Bobadilla.pdf` con PyMuPDF: posiciones, fuentes y matrices de las imágenes.

### Siguiente
1. Revisar la demo con el cliente.
2. Cuando llegue el PDF negro y dorado, crear la plantilla de especialización con el mismo método.
3. Hacer el commit en `fase-2`. Antes, evaluar si se recorta `vendor/` (fuentes de dompdf).
4. Seguir con la lógica (sección «Pasos», puntos 2 y 3):
   - schema 1.6.0;
   - modo `generated` con snapshot;
   - `/verificar/{code}`;
   - firmantes en Ajustes y checklist por curso;
   - `certificate_content` en el curso.
5. Pendiente del cliente: si la verificación sigue siendo válida con el alumno bloqueado, y la firma y el sello de los firmantes adicionales.

## Avance (2026-11-01): diseño negro y dorado

- **Referencia:** `~/Downloads/Julia-Ricardina-Ramos-Cuadros.pdf` (Canva, A4 apaisado de 842,25 × 595,5 pt, 2 páginas).
- **Fondos horneados.** Con PyMuPDF se borraron el texto y las imágenes variables (redacción y `delete_image`), y cada página se renderizó a 220 dpi como `fondo-anverso.jpg` y `fondo-reverso.jpg`, en `assets/certificados/especializacion/`.
  - El fondo ya trae las decoraciones, el marco, el logo, «IDH», la cinta «Certificado», «Otorgado a:» y las líneas con puntos.
  - Además, `firma.png` y `sello.png` (con alfa) y `logo.png` (para el QR).
- **Plantilla `templates/certificados/especializacion.php`:**
  - En el anverso solo dibuja el nombre, el párrafo y los firmantes. Con 2 o 3 firmantes agrega líneas doradas propias, porque la del fondo es para uno solo.
  - En el reverso dibuja el tipo de curso, el título, los módulos y viñetas, el QR y la leyenda.
  - En `topics`, una línea que empieza con «- » es una viñeta y las demás son títulos de módulo.
- **Tamaño de página por plantilla:** `IDH_Aula_Certificate_PDF::PAGES`.
- **Fuentes nuevas en `assets/fonts/`** (todas OFL):
  - Noto Serif reemplaza a Droid Serif.
  - Great Vibes reemplaza a Amoresa Aged, que es exclusiva de Canva.
  - Bai Jamjuree es la misma fuente de la referencia. Las viñetas llevan `letter-spacing: -0.21pt`, como en Canva.
  
  Great Vibes y Bai Jamjuree se descargaron de github.com/google/fonts con permiso del usuario.
- **Validación:**
  - En el anverso, los cortes de línea y los extremos del párrafo son idénticos a la referencia (±0,4 pt), y el nombre, la firma, el sello y el firmante quedan a ≤0,1 pt.
  - En el reverso calza todo, salvo que la referencia deja una línea vacía extra antes de la primera viñeta del módulo II. Aquí el espaciado es uniforme.
  - El QR se lee desde el PDF.
- **Demo:** `demo-especializacion-ref.pdf` (datos de Julia, para calibrar) y `demo-especializacion.pdf` (Carlos Alberto Mendoza Salazar, el modelo que se presentó).
- **Errores de tipeo en los textos del cliente:** «Nùñez», «Introducciòn», «Tècnicas», «Transtornos» y «MODULO» sin tilde. En la demo se copiaron tal cual. En producción salen de la base de datos y los ajustes, así que se corrigen ahí.

### Ajuste (2026-11-01): grosor del «IDH» azul
- En el original, el «IDH» tiene relleno más un contorno del mismo color de 2,29 pt (PowerPoint), con esquinas en punta. Por eso se veía más grueso que la versión anterior.
- `assets/certificados/azul/idh.png` ahora se recorta directamente del PDF original: se dejó solo ese texto y se renderizó a 8×. En la plantilla va en `left 413.75pt`, `top 43.5pt`, de 145.5 × 59.25 pt.
- Superpuesto con el original coincide pixel a pixel.
- `bin/demo-certificado.php` genera además `demo-taller-modelo.pdf`, a nombre de Valeria Fernanda Quispe Huamán.

## Decisiones de generación (2026-11-01, acordadas con el usuario)

Estas decisiones **reemplazan** a la de «Emisión y congelado» de arriba, que proponía guardar el PDF en disco.

1. **Certificados subidos a mano (modos `url` y `pdf`):** no se tocan. El alumno los descarga como siempre y su QR sigue siendo el de qr.io. Si el administrador quiere, puede pasar uno al modo «Generado por la plataforma». En ese caso se genera con el QR propio y el PDF viejo sigue existiendo por fuera.
2. **Checkbox «Habilitar certificado»** en el modo `generated`, tanto en el general como en cada clase. Al marcarlo se guardan:
   - la fecha y la hora (`enabled_at`);
   - quién lo habilitó (`enabled_by`);
   - el **contenido congelado** (`snapshot`, JSON): nombre, documento, curso, tipo, horas, período, temas, firmantes y plantilla.
3. **No se guarda el PDF.** Se genera al descargar (menos de 1 s) con el contenido congelado. Así:
   - si se corrige un dato, el certificado ya emitido no cambia;
   - si se ajusta el diseño, se aplica a todos (el cliente lo aceptó);
   - el dominio del QR sale de `home_url()` al generarlo.
4. **Desmarcar el checkbox:** el alumno ya no lo puede descargar y `/verificar/{code}` lo muestra como «no vigente». Si se vuelve a marcar, se conserva el mismo código y el mismo QR.
5. **«Actualizar contenido»:** vuelve a congelar los datos actuales sin cambiar el código.
6. **Reglas existentes que se mantienen:** con pago pendiente, matrícula con candado o alumno bloqueado, la descarga del alumno queda retenida.
7. **`/verificar/{code}`** es pública y `noindex`:
   - Muestra la validez y los datos congelados. El documento va enmascarado.
   - El PDF público (`/verificar/{code}/pdf`) solo se sirve si el certificado está vigente y además cumple las reglas de retención del punto 6.
   - Para los certificados manuales (`url`/`pdf`) muestra los datos actuales de la base.
8. **Pruebas:** la del QR de punta a punta (celular → `/verificar`) se hace en **testing**. Nada pasa a `main` hasta que el usuario lo valide.

## Avance (2026-11-01): verificador y generador

### Hecho
- **Schema 1.6.0:**
  - En las dos tablas de certificados: `enabled`, `enabled_at`, `enabled_by` y `snapshot`.
  - En los cursos: `certificate_template` y `certificate_content`.
  - La migración la corre sola `maybe_upgrade()`.
- **`IDH_AULA_VERSION` 1.1.0**, para que se vuelvan a generar las reglas de rewrite al desplegar.
- **`includes/class-certificate-generator.php`:**
  - `snapshot()` arma el contenido congelado.
  - `period_text()` devuelve «durante el mes de…» o «del … al …».
  - `render()` y `render_snapshot()` generan el PDF.
  - También están `verify_url()` (`home_url('verificar/CODE')`), `send()` y `masked_document()`.
  - Los firmantes por defecto (Erick, con la firma y el sello de cada diseño) quedan **provisionales** hasta que exista la configuración en Ajustes.
- **Repositorio de certificados:** el modo `generated` en `MODES`, y en `persist()` el checkbox «Habilitar» con su contenido congelado. Si se desmarca, queda no vigente con el mismo código. Además, `is_live()`, y las URLs del alumno solo aparecen cuando está habilitado.
- **Rutas:**
  - `/verificar/{code}`: público, `noindex`, con el documento enmascarado.
  - `/verificar/{code}/pdf`: solo si está vigente y no retenido.
  - `/aula-virtual/certificado/{code}`: genera el PDF al momento.
- **Portal:**
  - El certificado generado aparece solo si está habilitado.
  - El enlace «Verificador» apunta a `/verificar`.
  - El badge «Certificado disponible» y los reportes cuentan solo los generados que están habilitados.
- **Admin:**
  - Bloque `admin/views/partials/certificate-generated.php`, en el certificado general y en cada clase: el checkbox, el estado («Habilitado el … por …»), «Actualizar contenido», «Vista previa con los datos actuales», «Ver el certificado emitido» y «Abrir verificador».
  - La acción `idh_aula_preview_certificate`.
  - En la ficha del curso, la tarjeta «Certificado generado», con el diseño y el contenido del reverso.

### Pruebas en local (`https://institutoidh.local.io`, matrícula 36)
| Caso | Resultado |
|---|---|
| Habilitar → `/verificar/CODE` | 200, «Certificado válido», DNI enmascarado (****5252) |
| `/verificar/CODE/pdf` y `/aula-virtual/certificado/CODE` | 200 `application/pdf` |
| Código inexistente | 404, «Certificado no encontrado» |
| Deshabilitar | Verificador: «no vigente». PDF público y descarga del alumno: 404 |
| Rehabilitar | Mismo código, PDF 200 |
| Pago pendiente | Verificador: sigue «válido», sin botón de PDF. PDF público: 404. Descarga del alumno: 403 |
| Certificado por clase generado | OK. Sin fecha de clase, el texto queda sin período |
| Curso con diseño negro y dorado y contenido propio | OK. El QR leído desde el PDF apunta a `https://institutoidh.local.io/verificar/K2AYV83F` |
| Ficha del alumno renderizada | Sin avisos de PHP |

Datos de prueba que quedaron en local:
- El curso 2 tiene el diseño `especializacion` y contenido en el reverso.
- La matrícula 36 tiene el certificado general y el de la clase en modo generado y habilitados.

### Pendiente para testing
- Confirmar que el servidor tenga la extensión **GD** de PHP. dompdf la usa para los PNG con transparencia y php-qrcode para el QR. Se puede revisar con `php -m | grep -i gd` en el VPS.
- La prueba del QR con el celular contra testing.
- Siguientes pasos: firmantes en Ajustes con checklist por curso, y cómo se muestra al alumno (PDF directo o en un iframe).
