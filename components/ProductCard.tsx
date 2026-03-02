import React, { useState } from 'react';
import { Product } from '../types';
import Button from './Button';

interface ProductCardProps {
  product: Product;
  onAddToCart: (product: Product) => void;
}

const ProductCard: React.FC<ProductCardProps> = ({ product, onAddToCart }) => {
  const [imageError, setImageError] = useState(false);
  const [imageLoading, setImageLoading] = useState(true);

  const handleImageError = () => {
    setImageError(true);
    setImageLoading(false);
  };

  const handleImageLoad = () => {
    setImageLoading(false);
  };

  // URL de imagen fallback
  const fallbackImageUrl = `https://picsum.photos/seed/${product.id}/600/400`;
  const displayImageUrl = imageError ? fallbackImageUrl : product.imageUrl;

  return (
    <div className="bg-brand-dark-secondary rounded-lg shadow-xl overflow-hidden flex flex-col transition-all duration-300 group group-hover:shadow-2xl group-hover:ring-2 group-hover:ring-brand-gold">
      <div className="relative w-full h-56 sm:h-64 overflow-hidden">
        {/* Loading spinner */}
        {imageLoading && (
          <div className="absolute inset-0 flex items-center justify-center bg-brand-dark-secondary">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-gold"></div>
          </div>
        )}
        
        {/* Imagen del producto */}
        <img 
          src={displayImageUrl} 
          alt={product.name} 
          className={`w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ${
            imageLoading ? 'opacity-0' : 'opacity-100'
          }`}
          onError={handleImageError}
          onLoad={handleImageLoad}
        />
        
        {/* Overlay de hover */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        
        {/* Indicador de imagen local */}
        {!imageError && product.imageUrl.includes('/api/serve_image.php') && (
          <div className="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
            Local
          </div>
        )}
      </div>
      
      <div className="p-5 flex flex-col flex-grow">
        <h3
          className="text-xl font-semibold font-logo mb-2 truncate group-hover:text-yellow-400 transition-colors duration-150"
          style={{ color: '#B08D57' }}
          title={product.name}
        >
          {product.name}
        </h3>
        <p className="text-brand-text-secondary text-sm mb-3 flex-grow min-h-[40px] overflow-hidden">{product.description}</p>
        <div className="flex items-center justify-between mt-auto">
          <div>
            <p className="text-2xl font-bold text-brand-silver">
              ${product.price.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </p>
            <p className="text-xs text-brand-silver leading-tight">IVA Incluido</p>
            <p className="text-xs text-brand-silver leading-tight">Stock disponible: {product.stock}</p>
          </div>
          <Button 
            onClick={() => onAddToCart(product)} 
            variant="primary"
            size="sm"
            className="bg-brand-gold text-brand-dark-primary hover:bg-yellow-400 focus:ring-brand-gold"
            disabled={product.stock === 0}
          >
            Añadir al Carrito
          </Button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
