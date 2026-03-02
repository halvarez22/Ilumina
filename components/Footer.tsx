import React from 'react';
import { BRAND_NAME } from '../constants';

const Footer: React.FC = () => {
  return (
    <footer className="bg-[#101014] text-brand-text-secondary py-8 mt-12">
      <div className="container mx-auto px-4 text-center">
        <p className="text-brand-text-secondary text-sm">
          &copy; {new Date().getFullYear()} {BRAND_NAME}. Todos los derechos reservados.
        </p>
        <p className="text-xs text-gray-500 mt-1">Diseño elegante para iluminar tu mundo.</p>
      </div>
    </footer>
  );
};

export default Footer;
