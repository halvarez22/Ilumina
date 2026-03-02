import React, { useState, useCallback, useEffect } from 'react';
import Header from './components/Header';
import Footer from './components/Footer';
import ProductList from './components/ProductList';
import ShoppingCart from './components/ShoppingCart';
import ChatbotButton from './components/ChatbotButton';
import Chatbot from './components/Chatbot';
import Button from './components/Button'; // Added import for Button
import { Product, CartItemType } from './types';
import { BRAND_NAME } from './constants';
import Sidebar from './components/Sidebar';
import MercuryHero from './components/MercuryHero';
import BannerCarousel from './components/BannerCarousel';

type AppView = 'home' | 'products';

const App: React.FC = () => {
  const [cartItems, setCartItems] = useState<CartItemType[]>([]);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [isChatOpen, setIsChatOpen] = useState(false);
  const [currentView, setCurrentView] = useState<AppView>('home');
  const [showAddedToCartMessage, setShowAddedToCartMessage] = useState<string | null>(null);
  const [selectedCategory, setSelectedCategory] = useState<string>('Todos');


  const handleAddToCart = useCallback((product: Product) => {
    setCartItems((prevItems: CartItemType[]) => {
      const existingItem = prevItems.find((item: CartItemType) => item.product.id === product.id);
      if (existingItem) {
        return prevItems.map((item: CartItemType) =>
          item.product.id === product.id
            ? { ...item, quantity: Math.min(item.quantity + 1, product.stock) }
            : item
        );
      }
      return [...prevItems, { product, quantity: 1 }];
    });
    setShowAddedToCartMessage(`${product.name} añadido al carrito!`);
    setTimeout(() => setShowAddedToCartMessage(null), 3000);
  }, []);

  const handleRemoveFromCart = useCallback((productId: string) => {
    setCartItems((prevItems: CartItemType[]) => prevItems.filter((item: CartItemType) => item.product.id !== productId));
  }, []);

  const handleUpdateCartQuantity = useCallback((productId: string, newQuantity: number) => {
    if (newQuantity <= 0) {
      handleRemoveFromCart(productId);
      return;
    }
    setCartItems((prevItems: CartItemType[]) =>
      prevItems.map((item: CartItemType) =>
        item.product.id === productId ? { ...item, quantity: Math.min(newQuantity, item.product.stock) } : item
      )
    );
  }, [handleRemoveFromCart]);

  const toggleCart = useCallback(() => setIsCartOpen((prev: boolean) => !prev), []);
  const toggleChat = useCallback(() => setIsChatOpen((prev: boolean) => !prev), []);
  
  const handleNavigate = useCallback((view: AppView) => {
    setCurrentView(view);
    window.scrollTo(0, 0); // Scroll to top on navigation
  }, []);

  const cartItemCount = cartItems.reduce((sum: number, item: CartItemType) => sum + item.quantity, 0);
  
  const handleCheckout = () => {
    // Basic checkout action
    if (cartItems.length > 0) {
      alert(`Gracias por tu compra de ${cartItemCount} artículo(s) por un total de $${cartItems.reduce((sum: number, item: CartItemType) => sum + item.product.price * item.quantity, 0).toFixed(2)}! \n(Esta es una demostración, no se ha procesado ningún pago real.)`);
      setCartItems([]);
      setIsCartOpen(false);
    } else {
      alert("Tu carrito está vacío.");
    }
  };

  useEffect(() => {
    document.title = currentView === 'home' ? `${BRAND_NAME} - Inicio` : `${BRAND_NAME} - Productos`;
  }, [currentView]);


  return (
    <div className="flex flex-col min-h-screen" style={{ background: '#0a0a0d', color: 'var(--brand-text-primary, #fff)' }}>
      <Header
        cartItemCount={cartItemCount}
        onCartClick={toggleCart}
        onNavigate={handleNavigate}
      />
      {showAddedToCartMessage && (
        <div className="fixed top-24 left-1/2 -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg z-50 transition-opacity duration-300">
          {showAddedToCartMessage}
        </div>
      )}
      <main className="flex-grow">
        {currentView === 'home' && (
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center" style={{ background: '#000', borderRadius: '1rem' }}>
            <MercuryHero />
            <Button variant="primary" size="lg" onClick={() => handleNavigate('products')} className="bg-brand-gold text-brand-dark-primary hover:bg-yellow-400">
              Explorar Colección
            </Button>
            <BannerCarousel />
          </div>
        )}
        {currentView === 'products' && (
          <div className="flex">
            <Sidebar
              selectedCategory={selectedCategory}
              onSelectCategory={setSelectedCategory}
            />
            <div className="flex-1 ml-[200px] px-4 sm:px-6 lg:px-8 py-12" style={{ minHeight: '100vh' }}>
              <ProductList onAddToCart={handleAddToCart} selectedCategory={selectedCategory} />
            </div>
          </div>
        )}
      </main>
      <Footer />
      <ShoppingCart
        isOpen={isCartOpen}
        onClose={toggleCart}
        cartItems={cartItems}
        onUpdateQuantity={handleUpdateCartQuantity}
        onRemoveItem={handleRemoveFromCart}
        onCheckout={handleCheckout}
      />
      <ChatbotButton onClick={toggleChat} isChatOpen={isChatOpen} />
      <Chatbot isOpen={isChatOpen} onClose={toggleChat} />
    </div>
  );
};

export default App;
