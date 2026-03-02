import React from 'react';
import { BRAND_NAME, CartIcon } from '../constants';

interface HeaderProps {
  cartItemCount: number;
  onCartClick: () => void;
  onNavigate: (section: 'home' | 'products') => void;
}

const Header: React.FC<HeaderProps> = ({ cartItemCount, onCartClick, onNavigate }) => {
  return (
    <header className="bg-[#101014] shadow-lg sticky top-0 z-40">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          <div 
            className="text-3xl font-logo font-bold text-brand-gold cursor-pointer"
            onClick={() => onNavigate('home')}
          >
            {BRAND_NAME}
          </div>
          <nav className="flex items-center space-x-6">
            <button 
              onClick={() => onNavigate('home')} 
              className="text-brand-gold hover:text-yellow-400 transition-colors duration-150 text-sm sm:text-base"
            >
              Inicio
            </button>
            <button 
              onClick={() => onNavigate('products')} 
              className="text-brand-gold hover:text-yellow-400 transition-colors duration-150 text-sm sm:text-base"
            >
              Productos
            </button>
            <button
              onClick={onCartClick}
              className="relative text-brand-gold transition-colors duration-150 group hover:text-yellow-400"
              aria-label="Abrir carrito de compras"
            >
              <CartIcon className="w-6 h-6 sm:w-7 sm:h-7 group-hover:text-yellow-400 text-brand-gold transition-colors duration-150" />
              {cartItemCount > 0 && (
                <span className="absolute -top-2 -right-2 bg-brand-gold text-brand-dark-primary text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                  {cartItemCount}
                </span>
              )}
            </button>
          </nav>
        </div>
      </div>
    </header>
  );
};

export default Header;
