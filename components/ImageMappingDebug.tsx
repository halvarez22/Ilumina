import React, { useState, useEffect } from 'react';
import { getImageMappingStats } from '../services/productService';

const ImageMappingDebug: React.FC = () => {
  const [stats, setStats] = useState<any>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const updateStats = () => {
      const currentStats = getImageMappingStats();
      setStats(currentStats);
    };

    // Actualizar stats cada 2 segundos
    const interval = setInterval(updateStats, 2000);
    updateStats(); // Actualizar inmediatamente

    return () => clearInterval(interval);
  }, []);

  if (!isVisible) {
    return (
      <button
        onClick={() => setIsVisible(true)}
        className="fixed bottom-20 right-4 bg-blue-500 text-white p-2 rounded-full shadow-lg z-40"
        title="Mostrar debug de imágenes"
      >
        📊
      </button>
    );
  }

  return (
    <div className="fixed bottom-20 right-4 bg-brand-dark-secondary border border-brand-gold p-4 rounded-lg shadow-xl z-40 max-w-xs">
      <div className="flex justify-between items-center mb-2">
        <h3 className="text-brand-gold font-semibold">Debug Imágenes</h3>
        <button
          onClick={() => setIsVisible(false)}
          className="text-brand-text-secondary hover:text-brand-gold"
        >
          ✕
        </button>
      </div>
      
      {stats ? (
        <div className="text-sm text-brand-text-secondary">
          <p>🖼️ Imágenes mapeadas: <span className="text-brand-silver">{stats.totalImages}</span></p>
          <p>✅ Inicializado: <span className="text-brand-silver">{stats.initialized ? 'Sí' : 'No'}</span></p>
        </div>
      ) : (
        <p className="text-sm text-brand-text-secondary">Cargando estadísticas...</p>
      )}
    </div>
  );
};

export default ImageMappingDebug; 