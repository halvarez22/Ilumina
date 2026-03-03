/**
 * Vercel Serverless: /api/get_images (rewrite desde /api/get_images.php)
 * Devuelve la lista de archivos JPG en fotos/Ductus/
 */
import fs from 'fs';
import path from 'path';

const ROOT = process.cwd();
const IMAGES_PATH = path.join(ROOT, 'fotos', 'Ductus');

function jsonResponse(res, statusCode, data) {
  res.setHeader('Content-Type', 'application/json');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.status(statusCode).end(JSON.stringify(data));
}

export default function handler(req, res) {
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    return res.status(204).end();
  }
  if (req.method !== 'GET') {
    return jsonResponse(res, 405, { error: 'Método no permitido' });
  }

  if (!fs.existsSync(IMAGES_PATH) || !fs.statSync(IMAGES_PATH).isDirectory()) {
    return jsonResponse(res, 404, { error: 'Carpeta de imágenes no encontrada' });
  }

  const files = fs.readdirSync(IMAGES_PATH);
  const images = files.filter((f) => /\.jpg$/i.test(f));
  images.sort();

  return jsonResponse(res, 200, images);
}
