
import React from 'react';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
}

const Button: React.FC<ButtonProps> = ({
  children,
  variant = 'primary',
  size = 'md',
  className = '',
  ...props
}) => {
  const baseStyles = 'font-semibold rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-brand-dark-primary transition-colors duration-150 ease-in-out inline-flex items-center justify-center';

  const sizeStyles = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
  };

  const variantStyles = {
    primary: 'bg-brand-gold text-brand-dark-primary hover:bg-yellow-400 focus:ring-brand-gold',
    secondary: 'bg-brand-dark-secondary text-brand-text-primary hover:bg-gray-600 focus:ring-brand-silver',
    outline: 'border border-brand-gold text-brand-gold hover:bg-brand-gold hover:text-brand-dark-primary focus:ring-brand-gold',
    ghost: 'text-brand-silver hover:bg-brand-dark-secondary hover:text-brand-text-primary focus:ring-brand-silver',
  };

  return (
    <button
      type="button"
      className={`${baseStyles} ${sizeStyles[size]} ${variantStyles[variant]} ${className}`}
      {...props}
    >
      {children}
    </button>
  );
};

export default Button;
