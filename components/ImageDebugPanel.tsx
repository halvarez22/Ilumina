import React, { useState, useEffect } from 'react';
import { getImageMappingStats } from '../services/productService';

const ImageDebugPanel: React.FC = () => {
  // En producción no mostramos el panel de debug
  if ((import.meta as any).env?.PROD) {
    return null;
  }
  const [isVisible, setIsVisible] = useState(false);
  const [debugInfo, setDebugInfo] = useState<any>(null);
  const [testResults, setTestResults] = useState<any>(null);

  useEffect(() => {
    const updateDebugInfo = async () => {
      try {
        // Obtener estadísticas del mapeo
        const stats = getImageMappingStats();
        
        // Probar algunas URLs de imágenes
        const testUrls = [
          '/api/get_images.php',
          '/api/serve_image.php?image=ACBOARD2L20WNW.JPG',
          '/api/serve_image.php?image=ACCAB4WTLRGB.JPG'
        ];

        const testResults = await Promise.all(
          testUrls.map(async (url) => {
            try {
              const response = await fetch(url);
              return {
                url,
                status: response.status,
                ok: response.ok,
                contentType: response.headers.get('content-type'),
                size: response.headers.get('content-length')
              };
            } catch (error) {
              return {
                url,
                error: error instanceof Error ? error.message : 'Unknown error'
              };
            }
          })
        );

        setDebugInfo(stats);
        setTestResults(testResults);
      } catch (error) {
        console.error('Error obteniendo debug info:', error);
      }
    };

    if (isVisible) {
      updateDebugInfo();
      const interval = setInterval(updateDebugInfo, 5000);
      return () => clearInterval(interval);
    }
  }, [isVisible]);

  if (!isVisible) {
    return (
      <button
        onClick={() => setIsVisible(true)}
        className="fixed bottom-4 right-4 bg-red-500 text-white p-3 rounded-full shadow-lg z-50 hover:bg-red-600"
        title="Debug de Imágenes"
      >
        🐛
      </button>
    );
  }

  return (
    <div className="fixed bottom-4 right-4 bg-brand-dark-secondary border border-brand-gold p-4 rounded-lg shadow-xl z-50 max-w-md max-h-96 overflow-y-auto">
      <div className="flex justify-between items-center mb-3">
        <h3 className="text-brand-gold font-semibold">🐛 Debug Imágenes</h3>
        <button
          onClick={() => setIsVisible(false)}
          className="text-brand-text-secondary hover:text-brand-gold"
        >
          ✕
        </button>
      </div>
      
      <div className="text-sm text-brand-text-secondary space-y-3">
        {/* Estadísticas del mapeo */}
        {debugInfo && (
          <div>
            <h4 className="text-brand-gold font-medium mb-2">📊 Mapeo de Imágenes</h4>
            <p>🖼️ Imágenes mapeadas: <span className="text-brand-silver">{debugInfo.totalImages}</span></p>
            <p>✅ Inicializado: <span className="text-brand-silver">{debugInfo.initialized ? 'Sí' : 'No'}</span></p>
          </div>
        )}

        {/* Resultados de pruebas de API */}
        {testResults && (
          <div>
            <h4 className="text-brand-gold font-medium mb-2">🔗 Pruebas de API</h4>
            {testResults.map((result: any, index: number) => (
              <div key={index} className="mb-2 p-2 bg-brand-dark-primary rounded">
                <p className="text-xs text-brand-silver mb-1">{result.url}</p>
                {result.error ? (
                  <p className="text-red-400 text-xs">❌ {result.error}</p>
                ) : (
                  <div className="text-xs">
                    <p>Status: <span className={result.ok ? 'text-green-400' : 'text-red-400'}>{result.status}</span></p>
                    <p>Tipo: {result.contentType || 'N/A'}</p>
                    <p>Tamaño: {result.size || 'N/A'} bytes</p>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}

        {/* Información del navegador */}
        <div>
          <h4 className="text-brand-gold font-medium mb-2">🌐 Navegador</h4>
          <p>URL actual: <span className="text-brand-silver text-xs">{window.location.href}</span></p>
          <p>User Agent: <span className="text-brand-silver text-xs">{navigator.userAgent.substring(0, 50)}...</span></p>
        </div>

        {/* Botones de acción */}
        <div className="flex gap-2 mt-4">
          <button
            onClick={() => {
              fetch('/api/debug_images.php')
                .then(res => res.text())
                .then(data => {
                  console.log('Debug Images:', data);
                  alert('Ver consola para detalles');
                })
                .catch(err => alert('Error: ' + err.message));
            }}
            className="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600"
          >
            Debug PHP
          </button>
          <button
            onClick={() => {
              fetch('/api/debug_sku_mapping.php')
                .then(res => res.text())
                .then(data => {
                  console.log('Debug SKU Mapping:', data);
                  alert('Ver consola para detalles');
                })
                .catch(err => alert('Error: ' + err.message));
            }}
            className="bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600"
          >
            Debug SKU
          </button>
        </div>
      </div>
    </div>
  );
};

export default ImageDebugPanel; 