# Deployment - Ilumileds Online Store

Este documento resume cómo poner la app en línea en un entorno típico.

## 1. Requisitos

- Servidor con:
  - Node.js para construir (`npm run build`).
  - PHP 8+ para servir los endpoints de `/api/*.php`.
  - Servidor web (Apache/Nginx) o similar para servir archivos estáticos.

## 2. Build de frontend

En tu máquina de build (o en el servidor):

```bash
npm install
npm run verify   # typecheck + build + tests
```

El frontend compilado quedará en la carpeta `dist/`.

## 3. Despliegue en un hosting clásico (PHP + estáticos)

1. Copia al servidor:
   - Carpeta `dist/` (contenido estático de la web).
   - Carpeta `api/` (scripts PHP).
   - Carpetas de datos requeridas (`fotos/`, `precios/`, `sku_categoria/`, etc.).
   - Archivos de configuración necesarios (`.htaccess`, `mime_types.php`, etc.).
2. Configura el servidor web:
   - Que el **document root** apunte a `dist/` (para servir el frontend).
   - Que las rutas `/api/*` se resuelvan contra los scripts PHP en `api/`.
3. Define las variables de entorno en el servidor (sin subir `.env.local`):
   - `GEMINI_API_KEY`
   - `INVENTORY_API_URL`, `INVENTORY_USERNAME`, `INVENTORY_PASSWORD`, `INVENTORY_SSL_VERIFY`

## 4. Despliegue en Vercel (recomendado, sin PHP)

La app puede desplegarse en **Vercel** usando solo Node.js: el frontend (Vite) y las APIs se sirven como estáticos + serverless.

1. Conecta el repositorio de GitHub con [Vercel](https://vercel.com).
2. En **Project Settings → Environment Variables** añade:
   - `GROQ_API_KEY`: tu clave de API de Groq (para el chatbot).
   - Opcional: `GROQ_MODEL` (por defecto `llama-3.3-70b-versatile`).
3. Asegúrate de que en el repo existan las carpetas y archivos de datos:
   - `precios/` (con un CSV de precios, p. ej. `Precios Venta Página.csv`),
   - `sku_categoria/sku_categoria.csv`,
   - `fotos/Ductus/` (imágenes JPG de productos).
4. Despliega: cada push a `main` puede configurarse para hacer deploy automático.

Las rutas `/api/get_real_products.php`, `/api/gemini_chat.php`, `/api/get_images.php` y `/api/serve_image.php` se reescriben internamente a funciones serverless en Node (ver `vercel.json` y carpeta `api/*.js`). No se usa PHP en Vercel.

**Límite:** si `fotos/Ductus/` es muy grande (p. ej. >100 MB), el despliegue puede ser lento o superar límites; en ese caso conviene servir imágenes desde un CDN o almacenamiento externo.

## 5. Flujo recomendado con GitHub

1. Inicializa un repositorio Git local y súbelo a GitHub.
2. Al hacer push a `main`/`master`, el workflow `.github/workflows/ci.yml` ejecutará:
   - `npm ci`
   - `npm run verify`
   - `npm test`
3. Solo desplegar versiones que pasen este CI (SQA básico garantizado).

