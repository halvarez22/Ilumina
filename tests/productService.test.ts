import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { getProducts } from '../services/productService';

vi.mock('../services/imageMappingService', () => ({
  ImageMappingService: {
    initialize: vi.fn().mockResolvedValue(undefined),
    getImageUrlWithFallback: vi.fn((_sku: string, fallback: string) => fallback),
    getStats: vi.fn(() => ({ totalImages: 0, initialized: true })),
  },
}));

describe('productService.getProducts', () => {
  const originalFetch = global.fetch;

  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    global.fetch = originalFetch;
  });

  it('devuelve productos mock cuando la API falla', async () => {
    global.fetch = vi.fn().mockRejectedValue(new Error('Network error'));

    const products = await getProducts();

    expect(products.length).toBeGreaterThan(0);
    expect(products[0]).toHaveProperty('id');
    expect(products[0]).toHaveProperty('name');
    expect(products[0]).toHaveProperty('price');
  });

  it('mapea correctamente productos cuando la API responde OK', async () => {
    const apiResponse = {
      products: [
        {
          sku: 'SKU123',
          name: 'Lámpara prueba',
          description: 'Desc prueba',
          price: 99.5,
          image: 'SKU123.JPG',
          category: 'Lamparas',
          stock: 5,
        },
      ],
    };
    global.fetch = vi.fn().mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify(apiResponse),
    } as any);

    const products = await getProducts();

    expect(products).toHaveLength(1);
    expect(products[0]).toMatchObject({
      id: 'SKU123',
      name: 'Lámpara prueba',
      description: 'Desc prueba',
      price: 99.5,
      category: 'Lamparas',
      stock: 5,
    });
  });
});

