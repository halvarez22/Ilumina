import { Product } from '../types';
import { ImageMappingService } from './imageMappingService';

// Productos mock para fallback
const MOCK_PRODUCTS: Product[] = [
  {
    id: 'ACBOARD2L20WNW',
    name: 'Panel LED ACBOARD2L20WNW',
    description: 'Panel LED de alta eficiencia para iluminación comercial',
    price: 150.00,
    imageUrl: '/fotos/Ductus/ACBOARD2L20WNW.JPG',
    category: 'Lamparas',
    stock: 50,
  },
  {
    id: 'ACBOARD2L20WW',
    name: 'Panel LED ACBOARD2L20WW',
    description: 'Panel LED de alta eficiencia para iluminación comercial',
    price: 160.00,
    imageUrl: '/fotos/Ductus/ACBOARD2L20WW.JPG',
    category: 'Lamparas',
    stock: 45,
  },
  {
    id: 'ACBOARD2L20WWW',
    name: 'Panel LED ACBOARD2L20WWW',
    description: 'Panel LED de alta eficiencia para iluminación comercial',
    price: 170.00,
    imageUrl: '/fotos/Ductus/ACBOARD2L20WWW.JPG',
    category: 'Lamparas',
    stock: 40,
  }
];

export const getProducts = async (): Promise<Product[]> => {
  // Inicializar el servicio de mapeo de imágenes
  await ImageMappingService.initialize();

  try {
    // Llama al endpoint principal para obtener productos reales
    const response = await fetch('/api/get_real_products.php');
    const bodyText = await response.text();
    let data: any = null;
    try {
      data = JSON.parse(bodyText);
    } catch {
      data = null;
    }

    if (!response.ok) {
      const serverMsg = (typeof data?.message === 'string' && data.message) || (typeof data?.error === 'string' && data.error) || null;
      throw new Error(serverMsg || 'No se pudo obtener el inventario real');
    }

    if (!data) data = {};
    
    // Mapear los datos de la API real al formato que espera el frontend
    if (data.products && Array.isArray(data.products)) {
      return data.products.map((item: any) => {
        const sku = item.sku || item.id;
        // Intentar obtener imagen local usando el SKU
        const localImageUrl = ImageMappingService.getImageUrlWithFallback(
          sku,
          `/fotos/Ductus/${item.image}` // fallback local
        );
        return {
          id: sku,
          name: item.name || sku,
          description: item.description || 'Sin descripción disponible',
          price: item.price || 0.00,
          imageUrl: localImageUrl,
          category: item.category || 'Sin categoría',
          stock: item.stock || 0,
        };
      }).filter((product: Product) => product.stock > 0); // Solo productos con stock
    }

    // Si no hay datos válidos, devolver array vacío
    return [];
  } catch (error) {
    console.error('Error obteniendo productos:', error);
    // En caso de error, devolver productos mock con imágenes locales
    return MOCK_PRODUCTS.map(product => {
      const sku = product.id;
      const localImageUrl = ImageMappingService.getImageUrlWithFallback(sku, product.imageUrl);
      return {
        ...product,
        imageUrl: localImageUrl
      };
    });
  }
};

export const getProductsByCategory = async (category: string): Promise<Product[]> => {
  const allProducts = await getProducts();
  return allProducts.filter(product => product.category === category);
};

export const searchProducts = async (query: string): Promise<Product[]> => {
  const allProducts = await getProducts();
  const lowercaseQuery = query.toLowerCase();
  
  return allProducts.filter(product => 
    product.name.toLowerCase().includes(lowercaseQuery) ||
    product.description.toLowerCase().includes(lowercaseQuery) ||
    product.sku?.toLowerCase().includes(lowercaseQuery)
  );
};

export const getProductById = async (id: string): Promise<Product | undefined> => {
  const products = await getProducts();
  return products.find(p => p.id === id);
};

// Función para obtener estadísticas del mapeo de imágenes
export const getImageMappingStats = () => {
  return ImageMappingService.getStats();
};
