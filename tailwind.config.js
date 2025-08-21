/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  // Ajustado para evitar varrer node_modules inteiro e melhorar performance de build
  content: [
    './index.php',
    './layout/**/*.php',
    './src/**/*.php',
    './assets/js/**/*.js'
  ],
  theme: {
    extend: {
      fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
      colors: {
        brand: { 50:'#eef4ff',100:'#d9e7ff',200:'#b3d0ff',300:'#84b3ff',400:'#4f8bff',500:'#1f5fff',600:'#1548db',700:'#1239aa',800:'#132f82',900:'#142a66' },
        accent: '#ffb347'
      },
      boxShadow: {
        'brand-sm':'0 2px 4px -1px rgba(31,95,255,0.15),0 1px 3px -1px rgba(31,95,255,0.08)',
        'brand':'0 4px 10px -2px rgba(31,95,255,0.25),0 2px 6px -2px rgba(31,95,255,0.15)',
        'glass':'0 8px 32px -4px rgba(0,0,0,0.15)'
      },
      backdropBlur: { xs: '2px' },
      keyframes: {
        'fade-in': {'0%':{opacity:0,transform:'translateY(12px)'},'100%':{opacity:1,transform:'translateY(0)'}},
        'scale-in': {'0%':{opacity:0,transform:'scale(.95)'},'100%':{opacity:1,transform:'scale(1)'}},
        'pulse-border': {'0%,100%':{boxShadow:'0 0 0 0 rgba(79,139,255,0.5)'},'50%':{boxShadow:'0 0 0 6px rgba(79,139,255,0)'}}
      },
      animation: {
        'fade-in':'fade-in .5s ease-out both',
        'fade-in-delayed':'fade-in .7s .15s ease-out both',
        'scale-in':'scale-in .4s ease-out both',
        'pulse-border':'pulse-border 2.4s ease-in-out infinite'
      },
      transitionTimingFunction: { 'snappy':'cubic-bezier(.4,0,.2,1)' }
    }
  },
  plugins: [require('@tailwindcss/forms')],
};

