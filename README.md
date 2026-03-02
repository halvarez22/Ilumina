# Run and deploy your AI Studio app

This contains everything you need to run your app locally.

## Run Locally

**Prerequisites:**  Node.js


1. Install dependencies:
   `npm install`
2. Crea tu archivo de entorno local (no se debe commitear):
   - Copia `.env.example` a `.env.local`
   - Completa `GEMINI_API_KEY` (para el chatbot) y, si aplica, `INVENTORY_*` (para el proxy de inventario)
3. Inicia el servidor PHP para los endpoints en `/api` (en otra terminal):
   `php -S localhost:8000 -t .`
4. Run the app:
   `npm run dev`

## Requisitos para que la API responda bien (evitar 500)

El frontend llama a `/api/*`. Esas rutas las sirve **PHP** en `localhost:8000`. Si algo falla, verás **500 Internal Server Error** y en consola: *"No se pudo obtener el inventario real"*.

**Comprobar el motivo del 500:** F12 → pestaña **Network** → recarga la página → haz clic en la petición que falla (`get_real_products.php` o `get_images.php`) → pestaña **Response**. Ahí verás el mensaje de error que devuelve PHP.

**Archivos/carpetas que deben existir** (en la raíz del proyecto):

| Endpoint | Qué usa | Si falta → |
|----------|--------|------------|
| `get_real_products.php` | `precios/Precios Venta Página.csv` | 500 "Archivo de precios no encontrado" |
| `get_real_products.php` | `sku_categoria/sku_categoria.csv` | 500 "Archivo de categorías no encontrado" |
| `get_images.php` | Carpeta `fotos/Ductus/` (con archivos .jpg) | 404 o 500 según configuración |

- Asegúrate de tener **PHP en el PATH** y de haber ejecutado en la raíz del proyecto:  
  `php -S localhost:8000 -t .`
- Si no tienes los CSV o la carpeta de fotos, la app seguirá funcionando con **productos de prueba** (fallback en el frontend), pero el catálogo real no se cargará hasta que existan esos datos.

## Calidad (SQA)

- Verificación rápida de tipos + build:
  - `npm run verify`
- Ejecutar pruebas automatizadas:
  - `npm test`
