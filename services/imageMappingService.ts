// Servicio para mapear SKUs de productos con imágenes locales
export class ImageMappingService {
  private static imageCache: Map<string, string> = new Map();
  private static initialized = false;

  // Inicializar el mapeo de imágenes
  static async initialize(): Promise<void> {
    if (this.initialized) return;

    try {
      // Obtener la lista de imágenes disponibles
      const response = await fetch('/api/get_images.php');
      if (response.ok) {
        const images = await response.json();
        
        // Crear mapeo de SKU a ruta de imagen
        images.forEach((image: string) => {
          const sku = this.extractSkuFromFilename(image);
          if (sku) {
            this.imageCache.set(sku, `/api/serve_image.php?image=${encodeURIComponent(image)}`);
          }
        });
      }
    } catch (error) {
      console.warn('No se pudo cargar el mapeo de imágenes:', error);
    }

    this.initialized = true;
  }

  // Extraer SKU del nombre del archivo
  private static extractSkuFromFilename(filename: string): string | null {
    // Remover extensión .JPG
    const nameWithoutExt = filename.replace(/\.JPG$/i, '');
    
    // Buscar patrones comunes de SKU en los nombres de archivo
    // Ejemplos: ACBOARD2L20WNW, ACCAB4WTLRGB, etc.
    if (nameWithoutExt.length >= 3) {
      return nameWithoutExt;
    }
    
    return null;
  }

  // Obtener URL de imagen para un SKU específico
  static getImageUrl(sku: string): string | null {
    return this.imageCache.get(sku) || null;
  }

  // Obtener URL de imagen con fallback
  static getImageUrlWithFallback(sku: string, fallbackUrl?: string): string {
    const localImage = this.getImageUrl(sku);
    if (localImage) {
      return localImage;
    }
    
    // Si no hay imagen local, usar fallback o generar una placeholder
    return fallbackUrl || `https://picsum.photos/seed/${sku}/600/400`;
  }

  // Verificar si existe imagen local para un SKU
  static hasLocalImage(sku: string): boolean {
    return this.imageCache.has(sku);
  }

  // Obtener estadísticas del mapeo
  static getStats() {
    return {
      totalImages: this.imageCache.size,
      initialized: this.initialized
    };
  }
} 