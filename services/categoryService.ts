import Papa from 'papaparse';
// Si persiste el error de tipado, instala los tipos: npm i --save-dev @types/papaparse

export interface SkuCategoryMap {
  [sku: string]: string;
}

function cleanCategory(raw: string): string {
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

export async function loadSkuCategoryMap(): Promise<{ skuToCategory: SkuCategoryMap, categories: string[] }> {
  return new Promise((resolve, reject) => {
    fetch('/sku_categoria/sku_categoria.csv')
      .then(res => res.text())
      .then(csvText => {
        Papa.parse(csvText, {
          header: true,
          skipEmptyLines: true,
          complete: (results: Papa.ParseResult<any>) => {
            const skuToCategory: SkuCategoryMap = {};
            const categoriesSet = new Set<string>();

            for (const row of results.data as any[]) {
              let sku = (row['CODIGO'] || '').trim();
              // Buscar la categoría en diferentes posibles nombres de columna
              let cat = (row['CATEGORÍA'] || row['CATEGORIA'] || row['CATEGORA'] || row['CATEGORIA'] || '').trim();
              cat = cleanCategory(cat);
              
              if (sku && cat && cat.toLowerCase() !== 'varios') {
                skuToCategory[sku] = cat;
                categoriesSet.add(cat);
              }
            }

            let categoriesArray = Array.from(categoriesSet).sort();
            // Eliminar todas las variantes de 'L*mparas' que no sean exactamente 'Lamparas'
            categoriesArray = categoriesArray.filter(cat => cat === 'Lamparas' || !/^L.mparas$/i.test(cat));
            resolve({ skuToCategory, categories: categoriesArray });
          },
          error: (err: Error) => {
            console.error('Error parsing CSV:', err);
            reject(err);
          }
        });
      })
      .catch(err => {
        console.error('Error loading CSV:', err);
        reject(err);
      });
  });
} 