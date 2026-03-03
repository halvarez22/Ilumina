/**
 * Lógica compartida de productos para api-server.js y Vercel serverless.
 * Usa process.cwd() como raíz del proyecto.
 */
import fs from "fs";
import path from "path";

const ROOT = process.cwd();

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
  BASE: "Base para iluminación",
  BASINF: "Base inferior",
  CANOTRAC: "Canal de tracción",
  CP: "Controlador de potencia",
  CON: "Conector",
  COP: "Cople de conexión",
  DD: "Driver de LED",
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
  try {
    const files = fs.readdirSync(dir, { withFileTypes: true });
    const f = files.find((e) => e.isFile() && e.name.endsWith(".csv") && e.name.includes("Precios"));
    return f ? path.join(dir, f.name) : path.join(dir, name);
  } catch {
    return path.join(dir, name);
  }
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

export function getRealProductsData() {
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
