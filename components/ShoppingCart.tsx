import React from 'react';
import { CartItemType } from '../types';
import CartItemCard from './CartItemCard';
import Button from './Button';
import Modal from './Modal';

interface ShoppingCartProps {
  isOpen: boolean;
  onClose: () => void;
  cartItems: CartItemType[];
  onUpdateQuantity: (productId: string, newQuantity: number) => void;
  onRemoveItem: (productId: string) => void;
  onCheckout: () => void;
}

const ShoppingCart: React.FC<ShoppingCartProps> = ({
  isOpen,
  onClose,
  cartItems,
  onUpdateQuantity,
  onRemoveItem,
  onCheckout,
}) => {
  const subtotal = cartItems.reduce((sum, item) => sum + item.product.price * item.quantity, 0);

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Tu Carrito de Compras" size="lg">
      {cartItems.length === 0 ? (
        <p className="text-brand-text-secondary text-center py-8">Tu carrito está vacío.</p>
      ) : (
        <div className="flex flex-col">
          <div className="divide-y divide-gray-700 max-h-[50vh] overflow-y-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
            {cartItems.map((item) => (
              <CartItemCard
                key={item.product.id}
                item={item}
                onUpdateQuantity={onUpdateQuantity}
                onRemoveItem={onRemoveItem}
              />
            ))}
          </div>
          <div className="mt-6 pt-6 border-t border-gray-700">
            <div className="flex justify-between items-center mb-4">
              <span className="text-lg text-brand-text-secondary">Subtotal:</span>
              <span className="text-2xl font-bold text-brand-gold">${subtotal.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
            </div>
            <Button
              onClick={onCheckout}
              variant="primary"
              size="lg"
              className="w-full bg-brand-gold text-brand-dark-primary hover:bg-yellow-400"
              disabled={cartItems.length === 0}
            >
              Proceder al Pago
            </Button>
          </div>
        </div>
      )}
    </Modal>
  );
};

export default ShoppingCart;
