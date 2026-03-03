/**
 * Servidor API en Node.js para desarrollo sin PHP.
 * Atiende /api/gemini_chat.php (Groq) y /api/get_real_products.php en el puerto 8000.
 * Uso: node api-server.js   (o: npm run api)
 */

import http from "http";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PORT = 8000;

function loadEnv() {
  const roots = [path.resolve(__dirname), process.cwd()];
  for (const root of roots) {
    for (const file of [".env.local", ".env"]) {
      const p = path.join(root, file);
      try {
        let content = fs.readFileSync(p, "utf8");
        content = content.replace(/^\uFEFF/, ""); // quitar BOM si existe
        for (const line of content.split(/\r?\n/)) {
          const trimmed = line.trim();
          if (!trimmed || trimmed.startsWith("#")) continue;
          const eq = trimmed.indexOf("=");
          if (eq === -1) continue;
          const key = trimmed.slice(0, eq).trim();
          let val = trimmed.slice(eq + 1).trim();
          if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'")))
            val = val.slice(1, -1);
          if (key && process.env[key] === undefined) process.env[key] = val;
        }
      } catch {
        // archivo no existe o no legible
      }
    }
  }
}

loadEnv();

const GROQ_API_KEY = process.env.GROQ_API_KEY;
const GROQ_MODEL = process.env.GROQ_MODEL || "llama-3.3-70b-versatile";

function jsonResponse(res, statusCode, data) {
  res.writeHead(statusCode, {
    "Content-Type": "application/json; charset=utf-8",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "GET, POST, OPTIONS",
    "Access-Control-Allow-Headers": "Content-Type",
  });
  res.end(JSON.stringify(data));
}

function parseBody(req) {
  return new Promise((resolve, reject) => {
    let body = "";
    req.on("data", (chunk) => (body += chunk));
    req.on("end", () => {
      try {
        resolve(body ? JSON.parse(body) : {});
      } catch {
        reject(new Error("Body JSON inválido."));
      }
    });
    req.on("error", reject);
  });
}

// --- Productos reales (misma lógica que get_real_products.php) ---
const ROOT = path.resolve(__dirname);

function parseCSVLine(line) {
  const out = [];
  let cur = "";
  let inQuotes = false;
  for (let i = 0; i < line.length; i++) {
    const c = line[i];
    if (c === '"') inQuotes = !inQuotes;
    else if (c === "," && !inQuotes) {
      out.push(cur.trim());
      cur = "";
    } else cur += c;
  }
  out.push(cur.trim());
  return out;
}

function cleanPrice(price) {
  const s = String(price).replace(/[$,\s]/g, "").trim();
  return parseFloat(s) || 0;
}

function cleanUtf8(s) {
  if (typeof s !== "string") return "";
  return s
    .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, "")
    .replace(/[\u200B-\u200D\uFEFF]/g, "")
    .trim();
}

const SKU_DESCRIPTIONS = {
  ACBOARD: "Panel LED de alta eficiencia",
  ACCAB: "Cable para tiras LED",
  ACCON: "Conector para tiras LED",
  ACCOP: "Cople para tiras LED",
  ACDIFI: "Difusor para iluminación lineal",
  ACENDCAP: "Tapa final para tiras LED",
  ACGRAP: "Grapa para tiras LED",
  ACLECH: "Lecho para iluminación lineal",
  ACLED: "LED de alta potencia",
  ACOPL: "Opla para tiras LED",
  ACRDIF: "Difusor redondo",
  ACT: "Tira LED de alta calidad",
  ACTL: "Tira LED con protección",
  AR111: "Lámpara LED AR111",
  BADAGE: "Badge decorativo",
  BASE: "Base para iluminación",
  BASINF: "Base inferior",
  BASLAT: "Base lateral",
  BASUP: "Base superior",
  BELDEN: "Cable Belden",
  CAB: "Cable de conexión",
  CANOTRAC: "Canal de tracción",
  CAS: "Caja de conexión",
  CI: "Cilindro LED",
  CL: "Cilindro LED PCB",
  CLIP: "Clip de montaje",
  CON: "Conector",
  COP: "Cople de conexión",
  CP: "Controlador de potencia",
  DD: "Driver de LED",
  DHPFMV: "Driver de alta potencia",
  DXA: "Difusor XA",
  ECAP: "Encapsulado",
  ENCAP: "Encapsulado",
  ENDCAP: "Tapa final",
  FP: "Fuente de poder",
  G: "Gabinete",
  GRAPA: "Grapa de montaje",
};

function generateDescription(sku) {
  for (const [prefix, desc] of Object.entries(SKU_DESCRIPTIONS)) {
    if (sku.startsWith(prefix)) return `${desc} - ${sku}`;
  }
  return `Producto de iluminación LED - ${sku}`;
}

function findPreciosCsv() {
  const dir = path.join(ROOT, "precios");
  const name = "Precios Venta Página.csv";
  if (fs.existsSync(path.join(dir, name))) return path.join(dir, name);
  const files = fs.readdirSync(dir, { withFileTypes: true });
  const f = files.find((e) => e.isFile() && e.name.endsWith(".csv") && e.name.includes("Precios"));
  return f ? path.join(dir, f.name) : path.join(dir, name);
}

function readPrices() {
  const p = findPreciosCsv();
  if (!fs.existsSync(p)) throw new Error(`Archivo de precios no encontrado: ${p}`);
  const content = fs.readFileSync(p, "utf8").replace(/\r\n/g, "\n").replace(/\r/g, "\n");
  const lines = content.split("\n").filter((l) => l.trim());
  const prices = {};
  for (let i = 1; i < lines.length; i++) {
    const row = parseCSVLine(lines[i]);
    if (row.length >= 2) {
      const sku = cleanUtf8(row[0]);
      const price = cleanPrice(row[1]);
      if (sku && price > 0) prices[sku] = price;
    }
  }
  return prices;
}

function readCategories() {
  const p = path.join(ROOT, "sku_categoria", "sku_categoria.csv");
  if (!fs.existsSync(p)) throw new Error(`Archivo de categorías no encontrado: ${p}`);
  const content = fs.readFileSync(p, "utf8").replace(/\r\n/g, "\n").replace(/\r/g, "\n");
  const lines = content.split("\n").filter((l) => l.trim());
  const categories = {};
  for (let i = 1; i < lines.length; i++) {
    const row = parseCSVLine(lines[i]);
    if (row.length >= 3) {
      const sku = cleanUtf8(row[0]);
      const category = cleanUtf8(row[2]);
      if (sku && category && category !== "NO") categories[sku] = category;
    }
  }
  return categories;
}

function getRealProducts() {
  const prices = readPrices();
  const categories = readCategories();
  const products = [];
  let productId = 1;
  for (const [sku, price] of Object.entries(prices)) {
    const category = categories[sku] || "Sin categoría";
    if (category === "Varios" || category === "NO") continue;
    products.push({
      id: productId++,
      sku: cleanUtf8(sku),
      name: cleanUtf8(sku),
      description: cleanUtf8(generateDescription(sku)),
      price,
      category: cleanUtf8(category),
      image: cleanUtf8(sku) + ".JPG",
      stock: Math.floor(Math.random() * 91) + 10,
      rating: Math.round((Math.random() * 15 + 35) / 10) / 10,
      reviews: Math.floor(Math.random() * 46) + 5,
    });
  }
  products.sort((a, b) => {
    if (a.category !== b.category) return a.category.localeCompare(b.category);
    return a.sku.localeCompare(b.sku);
  });
  const validProducts = products.filter((prod) => {
    try {
      JSON.stringify(prod);
      return true;
    } catch {
      return false;
    }
  });
  return { success: true, products: validProducts, total: validProducts.length };
}

function handleGetRealProducts(req, res) {
  if (req.method !== "GET") {
    jsonResponse(res, 405, { error: true, message: "Método no permitido. Usa GET." });
    return;
  }
  try {
    const data = getRealProducts();
    jsonResponse(res, 200, data);
  } catch (err) {
    console.error("Error get_real_products:", err);
    jsonResponse(res, 500, {
      success: false,
      error: err.message || "Error al cargar productos",
    });
  }
}

function buildMessages(history, message) {
  const messages = [];
  if (Array.isArray(history)) {
    for (const item of history) {
      if (!item || (item.role !== "user" && item.role !== "model")) continue;
      const parts = item.parts;
      if (!Array.isArray(parts)) continue;
      const texts = parts
        .filter((p) => p && typeof p.text === "string" && p.text !== "")
        .map((p) => p.text);
      if (texts.length === 0) continue;
      messages.push({
        role: item.role === "user" ? "user" : "assistant",
        content: texts.join("\n\n"),
      });
    }
  }
  messages.push({ role: "user", content: message });
  return messages;
}

async function handleGeminiChat(req, res) {
  if (req.method !== "POST") {
    jsonResponse(res, 405, { error: true, message: "Método no permitido. Usa POST." });
    return;
  }

  if (!GROQ_API_KEY) {
    jsonResponse(res, 500, {
      error: true,
      message: "GROQ_API_KEY no está configurada. Ponla en .env.local en la raíz del proyecto.",
    });
    return;
  }

  let data;
  try {
    data = await parseBody(req);
  } catch (e) {
    jsonResponse(res, 400, { error: true, message: e.message || "Body JSON inválido." });
    return;
  }

  const history = data.history ?? [];
  const message = typeof data.message === "string" ? data.message.trim() : "";
  if (!message) {
    jsonResponse(res, 400, { error: true, message: 'El campo "message" es requerido.' });
    return;
  }

  const messages = buildMessages(history, message);
  const payload = {
    model: GROQ_MODEL,
    messages,
    temperature: 0.9,
    max_tokens: 2048,
  };

  try {
    const groqRes = await fetch("https://api.groq.com/openai/v1/chat/completions", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
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
        (parsed?.error?.message) ||
        (typeof parsed?.error === "string" ? parsed.error : null) ||
        "Error desde Groq.";
      jsonResponse(res, groqRes.status, {
        error: true,
        message: userMessage,
        details: respBody,
      });
      return;
    }

    const text = parsed?.choices?.[0]?.message?.content ?? "";
    jsonResponse(res, 200, { text });
  } catch (err) {
    if (err.name === "AbortError") {
      jsonResponse(res, 504, { error: true, message: "Tiempo de espera agotado con Groq." });
      return;
    }
    jsonResponse(res, 503, {
      error: true,
      message: "No se pudo conectar con Groq.",
      details: err.message,
    });
  }
}

const server = http.createServer((req, res) => {
  if (req.method === "OPTIONS") {
    res.writeHead(204, {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, POST, OPTIONS",
      "Access-Control-Allow-Headers": "Content-Type",
    });
    res.end();
    return;
  }

  const url = req.url || "";
  if (url === "/api/get_real_products.php" || url.startsWith("/api/get_real_products.php?")) {
    handleGetRealProducts(req, res);
    return;
  }
  if (url === "/api/gemini_chat.php" || url.startsWith("/api/gemini_chat.php?")) {
    handleGeminiChat(req, res).catch((err) => {
      console.error("Error en /api/gemini_chat.php:", err);
      if (!res.headersSent) {
        jsonResponse(res, 500, {
          error: true,
          message: "Error interno del servidor.",
          details: err.message,
        });
      }
    });
    return;
  }

  res.writeHead(404, { "Content-Type": "application/json" });
  res.end(JSON.stringify({ error: true, message: "Ruta no encontrada." }));
});

server.listen(PORT, () => {
  console.log(`API (Node) escuchando en http://localhost:${PORT}`);
  if (!GROQ_API_KEY) {
    console.warn("AVISO: GROQ_API_KEY no definida. Comprueba .env.local en la raíz del proyecto.");
  } else {
    console.log("GROQ_API_KEY cargada correctamente.");
  }
});
