/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'brand-dark-primary': '#111827',
        'brand-dark-secondary': '#1F2937',
        'brand-gold': '#B08D57',
        'brand-silver': '#C0C0C0',
        'brand-text-primary': '#F7FAFC',
        'brand-text-secondary': '#A0AEC0',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
        logo: ['Cinzel', 'Georgia', 'serif'],
      },
    },
  },
  plugins: [],
};
