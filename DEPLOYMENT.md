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

## 4. Flujo recomendado con GitHub

1. Inicializa un repositorio Git local y súbelo a GitHub.
2. Al hacer push a `main`/`master`, el workflow `.github/workflows/ci.yml` ejecutará:
   - `npm ci`
   - `npm run verify`
   - `npm test`
3. Solo desplegar versiones que pasen este CI (SQA básico garantizado).

