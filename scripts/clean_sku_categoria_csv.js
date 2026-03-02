const fs = require('fs');
const path = require('path');

const INPUT = path.join(__dirname, '../public/sku_categoria/sku_categoria.csv');
const OUTPUT = path.join(__dirname, '../public/sku_categoria/sku_categoria_clean.csv');

function cleanCategory(raw) {
  return raw
    .replace(/CATEGOR[ÍI]A|CATEGORA|CATEGORIA/g, 'CATEGORÍA')
    .replace(/Lmparas/g, 'Lámparas')
    .replace(/Iluminacin/g, 'Iluminación')
    .replace(/Lnea/g, 'Línea')
    .replace(/Modular/g, 'Modular')
    .replace(/Controladores/g, 'Controladores')
    .replace(/Tiras de Leds/g, 'Tiras de Leds')
    .replace(/Rieles/g, 'Rieles')
    .replace(/Varios/g, 'Varios')
    .replace(/Placas/g, 'Placas')
    .replace(/Sistemas/g, 'Sistemas')
    .replace(/Smart/g, 'Smart')
    .replace(/Drivers/g, 'Drivers')
    .replace(/Focos/g, 'Focos')
    .replace(/Perfiles LED/g, 'Perfiles LED')
    .replace(/Lámparas/g, 'Lámparas')
    .replace(/Iluminación Lineal/g, 'Iluminación Lineal')
    .replace(/Iluminación Modular/g, 'Iluminación Modular')
    .replace(/Línea Europea \/ Decorativa/g, 'Línea Europea / Decorativa')
    .replace(/Ã¡/g, 'á')
    .replace(/Ã©/g, 'é')
    .replace(/Ã­/g, 'í')
    .replace(/Ã³/g, 'ó')
    .replace(/Ãº/g, 'ú')
    .replace(/Ã±/g, 'ñ');
}

const raw = fs.readFileSync(INPUT, 'latin1');
const lines = raw.split(/\r?\n/);
const cleaned = lines.map(cleanCategory);
fs.writeFileSync(OUTPUT, cleaned.join('\n'), 'utf8');

console.log('Archivo limpiado y guardado como sku_categoria_clean.csv'); 