# wp-content · Instituto IDH

Código propio del sitio de WordPress de IDH. La raíz del repo es `wp-content/`.

| Carpeta | Qué es |
|---|---|
| `themes/kadence-child/` | Theme hijo (landings, home, categorías de portada) |
| `plugins/idh-aula/` | Plugin del aula virtual ([plan](plugins/idh-aula/docs/PLAN.md) · [avance](plugins/idh-aula/docs/AVANCE.md)) |

WordPress core, los plugins y themes de terceros y `uploads/` **no** se versionan (ver `.gitignore`).

## Ramas y despliegue
| Rama | Destino | Estado |
|---|---|---|
| `develop` | Local | Trabajo diario |
| `testing` | Sitio de prueba (`/var/www/institutoidh` en el VPS) | Workflow `deploy-testing.yml` (rsync) |
| `main` | Web pública | **Pendiente.** Producción sigue desplegando desde el repo `wp-kadence-child` (rama `main`) |

### Deploy a testing
1. Merge de `develop` a `testing` y push. El workflow sincroniza el theme y el plugin por rsync y luego activa el plugin y hace el flush de rewrite (si el servidor tiene wp-cli).
2. Para simular sin tocar el servidor: Actions → *Deploy testing* → *Run workflow* con `dry_run` activado.

Los archivos listados en `scripts/rsync-exclude.txt` no se copian al servidor.

Secrets requeridos en el repo: `VPS_HOST`, `VPS_USER`, `VPS_PORT` y `VPS_SSH_KEY`.
