# Despliegue a producción (idh.com.pe)

Plan para publicar el Aula Virtual en la web pública y retirar el despliegue antiguo del theme.

## Situación actual

| | Testing | Producción |
|---|---|---|
| Sitio | `/var/www/institutoidh` | `/var/www/idh.com.pe` (mismo VPS) |
| Repo | monorepo `codigoinerte/wp-institutoidh` (rama `testing`) | repo antiguo `codigoinerte/wp-kadence-child` (rama `main`) |
| Mecanismo | GitHub Actions → `rsync` de theme + plugin | GitHub Actions → `git pull` dentro de `themes/kadence-child` |
| Plugin `idh-aula` | instalado | **no existe** |

- El theme del monorepo es **idéntico** al `main` actual de producción (commit `7487aa2`, verificado con `diff -r`). Para el theme, el despliegue no cambia nada; lo único nuevo es el plugin.
- El plugin solo **agrega** cosas: 7 tablas `*_aula_*`, la ruta `/aula-virtual` y el menú *Aula Virtual*. No modifica páginas, landings ni datos existentes.
- Los datos de testing (alumnos, cursos y matrículas de prueba) **no se copian** a producción. Producción empieza vacía.

## Paso 0: validar en testing

1. Hacer push de `develop` a `testing` (incluye `ad4bc99`, las mejoras de accesibilidad y WhatsApp) y esperar el workflow *Deploy testing*.
2. En el sitio de prueba, configurar **Aula Virtual → Ajustes** (WhatsApp) y que IDH revise:
   - Importación CSV en otra computadora y con Google Sheets.
   - Grabaciones / Materiales, botón «Ver curso», WhatsApp, «Recordar mis datos».
3. Si todo está bien, sigue el paso 1. Si no, corregir en `develop` y repetir.

## Paso 1: revisar el servidor de producción (solo lectura)

Por SSH en el VPS:

```bash
cd /var/www/idh.com.pe

# a) El theme no tiene cambios hechos a mano en el servidor
git -C wp-content/themes/kadence-child status --short     # debe salir vacío
git -C wp-content/themes/kadence-child log -1 --oneline   # debe ser 7487aa2

# b) Entorno
php -v                                   # >= 7.4
wp --info && wp core version             # wp-cli disponible
wp config get table_prefix
wp option get permalink_structure        # no debe estar vacío
wp plugin list --status=active           # anotar plugins de caché (LiteSpeed, WP Rocket, etc.)
wp post list --post_type=page,post --name=aula-virtual --fields=ID,post_title,post_status   # debe salir vacío: el slug debe estar libre

# c) Servidor web (define cómo se protege la carpeta de certificados)
ls /etc/nginx/sites-enabled 2>/dev/null; apache2ctl -S 2>/dev/null | head

# d) Permisos: el usuario del deploy debe poder escribir en plugins/ y uploads/
ls -ld wp-content/plugins wp-content/uploads
```

Si el sitio pasa por Cloudflare, revisa también las reglas de caché y confirma que **no** haya un «Cache Everything» que afecte a `/aula-virtual*`. Si lo hay, agrega una regla *Bypass cache* para `/aula-virtual*`.

| Si encuentras… | Qué hacer |
|---|---|
| Cambios sin commit en el theme del servidor | Copiarlos al monorepo (`develop`) antes de seguir; si no, el rsync los borra. |
| **Nginx** | Agregar `location ^~ /wp-content/uploads/idh-aula/ { deny all; }` y recargar Nginx (el `.htaccess` del plugin solo protege en Apache). |
| Plugin de caché | Excluir `/aula-virtual` y la cookie `idh_aula_session` de la caché. |
| Página con slug `aula-virtual` | Cambiarle el slug o borrarla antes de activar el plugin. |

## Paso 2: preparar el repo (no toca producción)

1. Crear `.github/workflows/deploy-production.yml` en el monorepo, como copia de `deploy-testing.yml` con estos cambios:
   - `on: push: branches: [ main ]` + `workflow_dispatch` con `dry_run` (por defecto `true`).
   - `WP_PATH: /var/www/idh.com.pe`, `concurrency: deploy-production`.
   - `environment: production`: en GitHub (*Settings → Environments*) crear el entorno con **aprobación obligatoria**, para que ningún push a `main` publique sin que lo apruebes.
   - Usa los mismos secretos `VPS_*` (es el mismo servidor).
2. Subir la versión del plugin a `1.0.0` (`idh-aula.php`) y anotar en `AVANCE.md`.
3. Crear la rama `main` desde `testing` ya validada, **todavía sin push**.

## Paso 3: día del despliegue

Conviene hacerlo en un horario de poco tráfico. Tiempo estimado: 30 a 45 minutos.

1. **Respaldos** (en el VPS):
   ```bash
   mkdir -p ~/backups/$(date +%F) && cd /var/www/idh.com.pe
   wp db export ~/backups/$(date +%F)/idh-prod.sql
   tar czf ~/backups/$(date +%F)/kadence-child.tgz -C wp-content/themes kadence-child   # incluye su .git
   ```
2. **Retirar el despliegue antiguo**: desactivar el workflow del repo viejo para que un push accidental no vuelva a hacer `git pull`:
   ```bash
   gh workflow disable Deploy -R codigoinerte/wp-kadence-child
   ```
3. **Sacar el `.git` del theme en producción**. El rsync no lo borra, pero quedaría desactualizado y confundiría:
   ```bash
   mv /var/www/idh.com.pe/wp-content/themes/kadence-child/.git ~/backups/$(date +%F)/kadence-child.git
   ```
4. **Push de `main`** y, en GitHub, ejecutar primero *Deploy production* con `dry_run = true`. En el log revisa que:
   - `themes/kadence-child` no muestre cambios, o solo cambios esperados.
   - `plugins/idh-aula` aparezca completo como archivos nuevos.
5. **Aprobar el despliegue real**, sea el run del push o uno manual con `dry_run = false`. El post-deploy activa el plugin (crea las tablas), hace `wp rewrite flush` y muestra `idh_aula_db_version`.

## Paso 4: verificación (inmediatamente después)

- [ ] La web pública y algunas landings cargan igual que antes.
- [ ] `https://idh.com.pe/aula-virtual` muestra el login (no un 404).
- [ ] En wp-admin aparece **Aula Virtual** con todas sus pantallas.
- [ ] `wp db query "SHOW TABLES LIKE '%aula_%'"` lista 7 tablas.
- [ ] Prueba completa con datos ficticios: crear un recurso, un curso con una clase, un alumno y una matrícula → ingresar al portal → ver Grabaciones/Materiales → subir un certificado PDF.
- [ ] El acceso directo a `/wp-content/uploads/idh-aula/certificados/...pdf` devuelve **403**.
- [ ] 6 intentos fallidos de login muestran el bloqueo temporal.
- [ ] **Borrar los datos de prueba** (primero el certificado, luego la matrícula, el alumno y el curso).

## Paso 5: configuración y carga inicial (con IDH)

1. **Aula Virtual → Ajustes**: número de WhatsApp de IDH.
2. Categorías que falten (Curso taller, Curso de especialización, Seminario) desde la pantalla de categorías del theme.
3. Biblioteca de recursos y cursos reales (con sus clases), con portada y fecha opcional.
4. Importar los alumnos por CSV y matricularlos.
5. Entregar el manual (PDF) y el enlace `idh.com.pe/aula-virtual` para compartir con los alumnos.

## Plan de vuelta atrás

| Problema | Acción |
|---|---|
| Falla el portal o el admin del aula | `wp plugin deactivate idh-aula`: el resto de la web sigue igual. Las tablas quedan, pero no afectan nada. |
| La web pública se ve mal después del deploy (theme) | `tar xzf ~/backups/<fecha>/kadence-child.tgz -C wp-content/themes`. |
| Problema en la base de datos | `wp db import ~/backups/<fecha>/idh-prod.sql` (solo como último recurso: se pierde lo cargado después del respaldo). |

## Después

- Flujo definitivo: `develop` → `testing` (sitio de prueba) → `main` (producción, con aprobación).
- Archivar el repo `codigoinerte/wp-kadence-child` (solo lectura) cuando el nuevo flujo haya funcionado 1 o 2 semanas.
- Borrar `~/backups/<fecha>/kadence-child.git` en ese mismo momento.
