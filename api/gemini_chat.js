/**
 * Vercel Serverless: /api/gemini_chat (rewrite desde /api/gemini_chat.php)
 * Chat con Groq. Requiere GROQ_API_KEY en variables de entorno de Vercel.
 */
function jsonResponse(res, statusCode, data) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.status(statusCode).end(JSON.stringify(data));
}

function buildMessages(history, message) {
  const messages = [];
  if (Array.isArray(history)) {
    for (const item of history) {
      if (!item || (item.role !== 'user' && item.role !== 'model')) continue;
      const parts = item.parts;
      if (!Array.isArray(parts)) continue;
      const texts = parts
        .filter((p) => p && typeof p.text === 'string' && p.text !== '')
        .map((p) => p.text);
      if (texts.length === 0) continue;
      messages.push({
        role: item.role === 'user' ? 'user' : 'assistant',
        content: texts.join('\n\n'),
      });
    }
  }
  messages.push({ role: 'user', content: message });
  return messages;
}

export default async function handler(req, res) {
  if (req.method === 'OPTIONS') {
    res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    return res.status(204).end();
  }
  if (req.method !== 'POST') {
    return jsonResponse(res, 405, { error: true, message: 'Método no permitido. Usa POST.' });
  }

  const GROQ_API_KEY = process.env.GROQ_API_KEY;
  const GROQ_MODEL = process.env.GROQ_MODEL || 'llama-3.3-70b-versatile';

  if (!GROQ_API_KEY) {
    return jsonResponse(res, 500, {
      error: true,
      message: 'GROQ_API_KEY no configurada. Configúrala en Vercel → Project → Settings → Environment Variables.',
    });
  }

  const data = typeof req.body === 'object' && req.body !== null ? req.body : {};
  const history = data.history ?? [];
  const message = typeof data.message === 'string' ? data.message.trim() : '';
  if (!message) {
    return jsonResponse(res, 400, { error: true, message: 'El campo "message" es requerido.' });
  }

  const messages = buildMessages(history, message);
  const payload = {
    model: GROQ_MODEL,
    messages,
    temperature: 0.9,
    max_tokens: 2048,
  };

  try {
    const groqRes = await fetch('https://api.groq.com/openai/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${GROQ_API_KEY}`,
      },
      body: JSON.stringify(payload),
      signal: AbortSignal.timeout(60000),
    });

    const respBody = await groqRes.text();
    let parsed;
    try {
      parsed = JSON.parse(respBody);
    } catch {
      parsed = null;
    }

    if (!groqRes.ok) {
      const userMessage =
        parsed?.error?.message ||
        (typeof parsed?.error === 'string' ? parsed.error : null) ||
        'Error desde Groq.';
      return jsonResponse(res, groqRes.status, {
        error: true,
        message: userMessage,
        details: respBody,
      });
    }

    const text = parsed?.choices?.[0]?.message?.content ?? '';
    return jsonResponse(res, 200, { text });
  } catch (err) {
    if (err.name === 'AbortError') {
      return jsonResponse(res, 504, { error: true, message: 'Tiempo de espera agotado con Groq.' });
    }
    return jsonResponse(res, 503, {
      error: true,
      message: 'No se pudo conectar con Groq.',
      details: err.message,
    });
  }
}
