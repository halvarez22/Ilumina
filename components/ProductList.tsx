import React, { useState, useEffect } from 'react';
import { Product } from '../types';
import { getProducts } from '../services/productService';
import ProductCard from './ProductCard';

interface ProductListProps {
  onAddToCart: (product: Product) => void;
  selectedCategory: string;
}

const ProductList: React.FC<ProductListProps> = ({ onAddToCart, selectedCategory }) => {
  const [products, setProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const fetchedProducts = await getProducts();
        setProducts(fetchedProducts);
      } catch (err) {
        setError('No se pudieron cargar los productos. Inténtalo de nuevo más tarde.');
        console.error(err);
      } finally {
        setIsLoading(false);
      }
    };

    fetchProducts();
  }, []);

  let filteredProducts = products;
  if (selectedCategory && selectedCategory !== 'Todos') {
    filteredProducts = products.filter((p) => p.category === selectedCategory);
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-brand-gold"></div>
        <p className="ml-4 text-brand-silver text-lg">Cargando productos...</p>
      </div>
    );
  }

  if (error) {
    return <p className="text-center text-red-400">{error}</p>;
  }

  if (products.length === 0) {
    return <p className="text-center text-brand-text-secondary">No hay productos disponibles en este momento.</p>;
  }

  return (
    <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h2 className="text-3xl font-logo font-bold mb-8 text-center" style={{ color: '#B08D57' }}>Nuestra Colección Exclusiva</h2>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        {filteredProducts.map((product) => (
          <ProductCard key={product.id} product={product} onAddToCart={onAddToCart} />
        ))}
      </div>
    </div>
  );
};

export default ProductList;
