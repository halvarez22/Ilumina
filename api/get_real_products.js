/**
 * Vercel Serverless: /api/get_real_products (rewrite desde /api/get_real_products.php)
 */
import { getRealProductsData } from '../lib/productsApi.js';

function jsonResponse(res, statusCode, data) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.status(statusCode).end(JSON.stringify(data));
}

export default function handler(req, res) {
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    return res.status(204).end();
  }
  if (req.method !== 'GET') {
    return jsonResponse(res, 405, { error: true, message: 'Método no permitido. Usa GET.' });
  }
  try {
    const data = getRealProductsData();
    return jsonResponse(res, 200, data);
  } catch (err) {
    console.error('Error get_real_products:', err);
    return jsonResponse(res, 500, {
      success: false,
      error: err.message || 'Error al cargar productos',
    });
  }
}
