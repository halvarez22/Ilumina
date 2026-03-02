import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import ShoppingCart from '../components/ShoppingCart';
import type { CartItemType } from '../types';

describe('ShoppingCart component', () => {
  const noop = () => {};

  it('muestra mensaje de carrito vacío cuando no hay items', () => {
    render(
      <ShoppingCart
        isOpen={true}
        onClose={noop}
        cartItems={[]}
        onUpdateQuantity={noop}
        onRemoveItem={noop}
        onCheckout={noop}
      />
    );

    expect(screen.getByText('Tu carrito está vacío.')).toBeInTheDocument();
  });

  it('muestra el subtotal correcto cuando hay items', () => {
    const cartItems: CartItemType[] = [
      {
        product: {
          id: 'SKU1',
          name: 'Producto 1',
          description: 'Desc',
          price: 100,
          imageUrl: 'https://example.com/img.jpg',
          category: 'Cat',
          stock: 10,
        },
        quantity: 2,
      },
    ];

    render(
      <ShoppingCart
        isOpen={true}
        onClose={noop}
        cartItems={cartItems}
        onUpdateQuantity={noop}
        onRemoveItem={noop}
        onCheckout={noop}
      />
    );

    expect(screen.getByText('$200.00')).toBeInTheDocument();
  });
});

