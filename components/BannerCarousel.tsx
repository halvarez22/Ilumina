import React, { useState, useEffect } from 'react';

const images = [
  '/imagenes_carrusel/carrusel_1.jpg',
  '/imagenes_carrusel/carrusel_2.jpg',
  '/imagenes_carrusel/carrusel_3.jpg',
  '/imagenes_carrusel/carrusel_4.jpg',
  '/imagenes_carrusel/carrusel_5.jpg',
  '/imagenes_carrusel/carrusel_6.jpg',
  '/imagenes_carrusel/carrusel_7.jpg',
  '/imagenes_carrusel/carrusel_8.jpg',
  '/imagenes_carrusel/carrusel_9.jpg',
  '/imagenes_carrusel/carrusel_10.jpg',
];

const BannerCarousel: React.FC = () => {
  const [current, setCurrent] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrent((prev) => (prev + 1) % images.length);
    }, 4000);
    return () => clearInterval(interval);
  }, []);

  const goTo = (idx: number) => setCurrent(idx);
  const prev = () => setCurrent((prev) => (prev - 1 + images.length) % images.length);
  const next = () => setCurrent((prev) => (prev + 1) % images.length);

  return (
    <div className="relative mt-12 rounded-lg shadow-xl mx-auto w-full max-w-5xl h-[320px] sm:h-[400px] overflow-hidden">
      <img
        src={images[current]}
        alt={`Banner Ilumileds ${current + 1}`}
        className="w-full h-full object-cover transition-opacity duration-700"
        style={{ opacity: 1 }}
      />
      {/* Controles */}
      <button onClick={prev} className="absolute left-2 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full p-2 hover:bg-black/70 z-10">
        &#8592;
      </button>
      <button onClick={next} className="absolute right-2 top-1/2 -translate-y-1/2 bg-black/40 text-white rounded-full p-2 hover:bg-black/70 z-10">
        &#8594;
      </button>
      {/* Indicadores */}
      <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10">
        {images.map((_, idx) => (
          <button
            key={idx}
            onClick={() => goTo(idx)}
            className={`w-3 h-3 rounded-full ${current === idx ? 'bg-brand-gold' : 'bg-white/60'} border border-white`}
            aria-label={`Ir a la imagen ${idx + 1}`}
          />
        ))}
      </div>
    </div>
  );
};

export default BannerCarousel; 