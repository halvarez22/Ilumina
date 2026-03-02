import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Chatbot from '../components/Chatbot';
import type { Product } from '../types';

vi.mock('../services/productService', () => {
  const products: Product[] = [];
  return {
    getProducts: vi.fn().mockResolvedValue(products),
  };
});

vi.mock('../services/geminiService', () => ({
  startChatWithCatalog: vi.fn(() => []),
  sendMessage: vi.fn(async () => ({
    text: 'Respuesta de prueba',
    groundingMetadata: undefined,
    history: [],
  })),
}));

describe('Chatbot component', () => {
  it('muestra el mensaje inicial del bot al abrirse', () => {
    // jsdom no implementa correctamente scrollIntoView en elementos de referencia
    (window.HTMLElement.prototype as any).scrollIntoView = vi.fn();

    render(<Chatbot isOpen={true} onClose={() => {}} />);

    expect(
      screen.getByText(/Soy Lumi, tu asistente virtual de Ilumileds/i)
    ).toBeInTheDocument();
  });
});

