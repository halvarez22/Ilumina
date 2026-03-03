/**
 * Vercel Serverless: /api/serve_image (rewrite desde /api/serve_image.php)
 * Sirve una imagen de fotos/Ductus/ por nombre. Query: ?image=XXX.JPG
 */
import fs from 'fs';
import path from 'path';

const ROOT = process.cwd();
const IMAGES_PATH = path.join(ROOT, 'fotos', 'Ductus');

export default function handler(req, res) {
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    return res.status(204).end();
  }
  if (req.method !== 'GET') {
    res.setHeader('Content-Type', 'text/plain');
    return res.status(405).end('Método no permitido');
  }

  const imageName = (req.query && req.query.image) || '';
  if (!/^[A-Za-z0-9._-]+\.JPG$/i.test(imageName)) {
    res.setHeader('Content-Type', 'text/plain');
    return res.status(400).end('Nombre de imagen inválido');
  }

  const fullPath = path.join(IMAGES_PATH, imageName);
  if (!fs.existsSync(fullPath) || !fs.statSync(fullPath).isFile()) {
    res.setHeader('Content-Type', 'text/plain');
    return res.status(404).end('Imagen no encontrada');
  }

  const realPath = path.resolve(fullPath);
  const allowedPath = path.resolve(IMAGES_PATH);
  if (!realPath.startsWith(allowedPath)) {
    res.setHeader('Content-Type', 'text/plain');
    return res.status(403).end('Acceso denegado');
  }

  const ext = path.extname(imageName).toLowerCase();
  if (ext !== '.jpg' && ext !== '.jpeg') {
    res.setHeader('Content-Type', 'text/plain');
    return res.status(400).end('Tipo de archivo no permitido');
  }

  res.setHeader('Content-Type', 'image/jpeg');
  res.setHeader('Cache-Control', 'public, max-age=31536000');
  const buf = fs.readFileSync(fullPath);
  return res.status(200).end(buf);
}
