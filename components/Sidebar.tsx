import React, { useState, useEffect } from 'react';
import './Sidebar.css';
import { loadSkuCategoryMap } from '../services/categoryService';

interface SidebarProps {
  selectedCategory: string;
  onSelectCategory: (category: string) => void;
}

const Sidebar: React.FC<SidebarProps> = ({ selectedCategory, onSelectCategory }) => {
  const [open, setOpen] = useState(false);
  const [categories, setCategories] = useState<string[]>([]);

  useEffect(() => {
    loadSkuCategoryMap().then(({ categories }) => {
      setCategories(['Todos', ...categories]);
    });
  }, []);

  const handleCategoryClick = (cat: string) => {
    onSelectCategory(cat);
    setOpen(false); // Cierra el menú en móvil
  };

  return (
    <nav className="sidebar">
      {/* Botón hamburguesa para móvil */}
      <button className="sidebar__toggle" onClick={() => setOpen(!open)}>
        <span className="sidebar__hamburger" />
      </button>
      <ul className={`sidebar__list${open ? ' sidebar__list--open' : ''}`}>
        {categories.map((cat) => (
          <button
            key={cat}
            className={`sidebar-category${selectedCategory === cat ? ' active' : ''}`}
            onClick={() => handleCategoryClick(cat)}
          >
            {cat}
          </button>
        ))}
      </ul>
    </nav>
  );
};

export default Sidebar; 