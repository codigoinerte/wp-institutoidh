# IDH Aula Virtual

Plugin de WordPress del portal de alumnos de IDH: acceso con DNI y fecha de nacimiento, cursos con clases (YouTube y Drive), certificados por matrícula, bloqueos y reportes.

El plan completo y las decisiones de diseño están en [`docs/PLAN.md`](docs/PLAN.md).

## Rutas públicas
| Ruta | Descripción |
|---|---|
| `/aula-virtual` | Login, o el listado de cursos si hay sesión |
| `/aula-virtual/curso/{id}` | Detalle del curso (403 si la matrícula está bloqueada) |
| `/aula-virtual/certificado/{code}` | Certificado: PDF servido por la plataforma o redirección a la URL externa. Responde 403 si el alumno o la matrícula están bloqueados |
| `/aula-virtual/salir` | Cierra la sesión |

## Admin (wp-admin → Aula Virtual, solo administradores)
Panel y reportes · Alumnos · Cursos · Biblioteca de recursos · Matrículas (incluye matrícula masiva) · Importar alumnos (CSV).
Además, cada página o entrada tiene en su editor la caja «Enlace público» con el botón **Copiar enlace**, y el listado de páginas incluye la acción de fila «Copiar enlace».

## Estructura
```
idh-aula.php            bootstrap y activación
includes/               esquema (dbDelta), repositorios, auth, router, reportes, CSV
admin/                  menú, acciones (admin-post/ajax), vistas y assets
public/                 plantillas y assets del portal (basados en idh-prototype)
```

## Datos
Usa tablas propias `{prefix}aula_*`, versionadas con la opción `idh_aula_db_version`. Los PDFs se guardan en `uploads/idh-aula/certificados/`, una carpeta con `.htaccess` que bloquea el acceso directo. En Nginx hay que agregar la regla equivalente:
```
location ^~ /wp-content/uploads/idh-aula/ { deny all; }
```

## Ramas
- `develop`: avance local.
- `testing`: sitio de prueba (mismo servidor).
- `main`: web pública.

El despliegue se configura cuando toque publicar. Después del primer deploy hay que activar el plugin para que cree las tablas y las reglas de rewrite.
