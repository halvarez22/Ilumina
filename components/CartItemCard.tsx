import React from 'react';
import { CartItemType } from '../types';
import { PlusIcon, MinusIcon, TrashIcon } from '../constants';
import Button from './Button';

interface CartItemCardProps {
  item: CartItemType;
  onUpdateQuantity: (productId: string, newQuantity: number) => void;
  onRemoveItem: (productId: string) => void;
}

const CartItemCard: React.FC<CartItemCardProps> = ({ item, onUpdateQuantity, onRemoveItem }) => {
  const handleQuantityChange = (newQuantity: number) => {
    if (newQuantity <= 0) {
      onRemoveItem(item.product.id);
    } else if (newQuantity <= item.product.stock) {
      onUpdateQuantity(item.product.id, newQuantity);
    }
  };

  return (
    <div className="flex items-center justify-between p-4 border-b border-gray-700 last:border-b-0">
      <div className="flex items-center space-x-4">
        <img 
          src={item.product.imageUrl} 
          alt={item.product.name} 
          className="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-md"
        />
        <div>
          <h4 className="text-md sm:text-lg font-semibold text-brand-silver truncate max-w-[150px] sm:max-w-xs" title={item.product.name}>
            {item.product.name}
          </h4>
          <p className="text-sm text-brand-text-secondary">${item.product.price.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
        </div>
      </div>
      <div className="flex items-center space-x-2 sm:space-x-3">
        <Button 
          size="sm"
          variant="ghost"
          onClick={() => handleQuantityChange(item.quantity - 1)}
          aria-label="Disminuir cantidad"
          className="p-1"
        >
          <MinusIcon className="w-4 h-4 sm:w-5 sm:h-5 text-brand-silver" />
        </Button>
        <span className="text-md sm:text-lg w-8 text-center text-brand-text-primary">{item.quantity}</span>
        <Button 
          size="sm"
          variant="ghost"
          onClick={() => handleQuantityChange(item.quantity + 1)}
          disabled={item.quantity >= item.product.stock}
          aria-label="Aumentar cantidad"
          className="p-1"
        >
          <PlusIcon className="w-4 h-4 sm:w-5 sm:h-5 text-brand-silver" />
        </Button>
        <Button 
          size="sm"
          variant="ghost"
          onClick={() => onRemoveItem(item.product.id)}
          aria-label="Eliminar artículo"
          className="p-1"
        >
          <TrashIcon className="w-4 h-4 sm:w-5 sm:h-5 text-red-500 hover:text-red-400" />
        </Button>
      </div>
    </div>
  );
};

export default CartItemCard;
