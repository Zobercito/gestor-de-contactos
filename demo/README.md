# Gestor de contactos · demo estática

Maqueta funcional sin backend. Todo el estado (contactos, segmentos, notas, importaciones y sesión) vive en `localStorage`, y el CSV se procesa en el navegador.

## Ejecutarla en local

Cualquier servidor estático sirve. Por ejemplo:

```bash
python3 -m http.server 8123
# abre http://127.0.0.1:8123
```

No abras `index.html` con `file://`: el navegador bloquea algunas APIs necesarias.

## Desplegarla

El flujo `.github/workflows/pages.yml` publica esta carpeta en GitHub Pages al hacer push a `main`.

1. En GitHub, ve a **Settings → Pages** y elige **GitHub Actions** como origen.
2. Haz push de un cambio dentro de `demo/` (o ejecuta el workflow a mano).
3. La demo quedará en `https://<usuario>.github.io/gestor-de-contactos/`.

También puedes subir el contenido de esta carpeta a Netlify, Cloudflare Pages o cualquier hosting estático.

## Qué incluye

- Entrada con tres perfiles: administración, edición y lectura.
- Panel con totales, cumpleaños próximos, segmentos e historial.
- Contactos: búsqueda, filtros, paginación, alta, edición, borrado y notas.
- Segmentos con color y CRUD.
- Importación CSV con vista previa, mapeo automático, duplicados e informe.
- Exportación CSV con BOM y escape de fórmulas.
- Actividad, gestión de usuarios y ajustes con tema claro/oscuro.

Los 48 contactos iniciales son ficticios y el botón **Restablecer datos** devuelve la demo a su estado original.
