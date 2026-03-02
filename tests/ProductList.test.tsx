import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import ProductList from '../components/ProductList';
import type { Product } from '../types';

vi.mock('../services/productService', () => {
  const products: Product[] = [
    {
      id: 'SKU1',
      name: 'Producto Cat1',
      description: 'Desc',
      price: 10,
      imageUrl: 'https://example.com/img1.jpg',
      category: 'Cat1',
      stock: 10,
    },
    {
      id: 'SKU2',
      name: 'Producto Cat2',
      description: 'Desc',
      price: 20,
      imageUrl: 'https://example.com/img2.jpg',
      category: 'Cat2',
      stock: 5,
    },
  ];

  return {
    getProducts: vi.fn().mockResolvedValue(products),
  };
});

describe('ProductList component', () => {
  const noop = () => {};

  it('muestra todos los productos cuando la categoría es "Todos"', async () => {
    render(<ProductList onAddToCart={noop} selectedCategory="Todos" />);

    expect(await screen.findByText('Producto Cat1')).toBeInTheDocument();
    expect(await screen.findByText('Producto Cat2')).toBeInTheDocument();
  });

  it('filtra productos por categoría seleccionada', async () => {
    render(<ProductList onAddToCart={noop} selectedCategory="Cat1" />);

    expect(await screen.findByText('Producto Cat1')).toBeInTheDocument();
    expect(screen.queryByText('Producto Cat2')).not.toBeInTheDocument();
  });
});

