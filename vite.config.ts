import path from 'path';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig(() => {
  return {
    plugins: [
      viteStaticCopy({
        targets: [
          { src: 'imagenes_carrusel', dest: '.' },
        ],
      }),
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    server: {
      host: '0.0.0.0', // Permite que el servidor sea accesible desde fuera de localhost
      allowedHosts: [
        '1d64-189-203-206-90.ngrok-free.app', // Dominio de ngrok
        'localhost', // Mantén localhost para pruebas locales
      ],
      proxy: {
        '/api': {
          target: 'http://localhost:8000',
          changeOrigin: true,
          secure: false,
        },
      },
    },
  };
});