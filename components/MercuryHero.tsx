import React from 'react';
import './MercuryHero.css';

const MercuryHero: React.FC = () => (
  <div className="mercury-hero-wrapper">
    <svg className="defs">
      <filter id="liquid">
        <feTurbulence type="fractalNoise" baseFrequency="0.005" numOctaves="2" result="noise" />
        <feDisplacementMap in="SourceGraphic" in2="noise" scale="2" xChannelSelector="R" yChannelSelector="G" />
        <feGaussianBlur stdDeviation="0.5" />
      </filter>
    </svg>
    <div>
      <h1 className="mercury-title" style={{ filter: 'url(#liquid)' }}>
        Bienvenido a Ilumileds
      </h1>
      <p className="mercury-subtitle">
        Descubre la elegancia y la innovación en iluminación. Soluciones sofisticadas para cada espacio de tu vida.
      </p>
    </div>
  </div>
);

export default MercuryHero; 