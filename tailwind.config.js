/** @type {import('tailwindcss').Config} */
const plugin = require('tailwindcss/plugin');

module.exports = {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'body': '#FFFBF3',
        'dark-body': '#000000',
        'primary-color': '#000000',
        'secondary-color': '#aaaaaa',
        'dark-primary-color': '#E8B931',
        'dark-form-base': '#101010',
        'dark-text-base': '#B3B3B3',
        'dark-text-base-gray': '#F3F3F3',
        'dark-form-surface': '#1E1E1E',
        'dark-border-color-icon': '#303030',
        'main-color': '#333333',
        'sub-color': '#828282', 
        'main-bg': '#f0f4ff',
        'sidebar': '#fff',
        'dark-input-background-color': '#000000',
        'input-background-color': '#fff',
        'input-text-color': '#333',
        'input-border-color': '#e0e0e0',
        'input-icon-color': '#828282',
        'input-placeholder-color': '#828282',
        'invalid-color': '#EB5757',
        'table-border-color': '#e0e0e0',
        'table-header-color': '#E0E0E0',
        'divider': '#e0e0e0',
        'light-divider': '#F2F2F2',
        'web-wash': '#e0e0e0',
        'light-web-wash': '#F4F4F4',
        'dark-web-wash': '#1E1E1E',
        'primary-box-color': '#F2F2F2',
        'secondary-box-color': '#BDBDBD',
        'base-red': '#EB3349',
        'base-green': '#27AE60',
        'base-yellow': '#eea236',
        'light-yellow': '#FFF7E2',
        'dark-yellow': '#8C83314F',
        'gray-6': '#F2F2F2',
        'gray-trans-4': 'rgba(51, 51, 51, 0.4)',
        'light-blue': '#F1FBFF',
        'dark-message': '#1f3452',
        'black-trans-4': 'rgba(0, 0, 0, 0.4)',
        'black-trans-5': 'rgba(0, 0, 0, 0.5)',
        'black-trans-6': 'rgba(0, 0, 0, 0.6)',
        'black-trans-75': 'rgba(0, 0, 0, 0.75)',
        'slate-400': '#94a3b8',
        'slate-800': '#1e293b',
        'gray-300': '#d1d5db',
        'white': '#ffffff',
        'badge-color': '#CCF0FF',
        'reply-color': 'rgba(0, 0, 0, 0.04)',
        'white/10': 'rgba(255, 255, 255, 0.15)',
        'chat-incoming-background-color': '#E4E4E4',
        'chat-incoming-border-color': '#E4E4E4',
        'light-gray': '#FCFCFC',
        'input-disabled-background-color': '#e2e8f0',
        'input-disabled-border-color': '#cbd5e1',
        'input-placeholder-dark-color': '#cbd5e1',
        'media-inner': 'rgba(0, 0, 0, .1)',
        'media-inner-dark': 'rgba(255, 255, 255, .1)',
        'scrollbar-thumb': '#bbb',
        'scrollbar-thumb-dark': '#ffffff26',
        'feed-hover': 'var(--hover-overlay)',
        'dark-gray': '#D9D9D9'
      },
      backgroundImage: {
        'gradient-story': 'linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d)',
        'black-gradient': 'linear-gradient(rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0.9))',
        'story-detail-linear': 'linear-gradient(180deg,rgba(0, 0, 0, 0.5),rgba(0, 0, 0, 0.1) 52.5%,transparent)',
        'header-linear': 'linear-gradient(180deg,rgba(38,38,38,.6) 0%,rgba(38,38,38,0) 100%)',
        'footer-linear': 'linear-gradient(180deg,rgba(38,38,38,0) 0%,rgba(38,38,38,.6) 100%)',
        'story-preview-linear': 'linear-gradient(0deg,rgba(0, 0, 0, 0.4),transparent)',
        'hover': 'linear-gradient(rgba(0, 0, 0, 0.1) 0 0)'
      },
      fontSize: {
        'base-none': '0',
        'xxs': '0.625rem',
        'base-xs': '0.8125rem',
        'base-sm': '0.9375rem',
        'base-lg': '1.1875rem',
        'base-4xl': '2.75rem'
      },
      borderRadius: {
        'base': '0.3125rem',
        'base-lg': '0.625rem',
        'base-10xl': '6.25rem',
        'chat-left': '5px 20px 20px 20px',
        'chat-right': '20px 5px 20px 20px',
        '1000': '1000px'
      },
      spacing: {
        'base-1': '0.3125rem',
        'base-2': '0.625rem',
        'base-7': '1.875rem',
        'base-9': '2.375rem',
        'base-13': '3.125rem',
        'base-30': '7.5rem',
        'story-ratio': '178%'
      },
      flex: {
        '2': '2 1 0%',
        '3': '3 1 0%',
        '4': '4 1 0%',
        '32': '0 0 8rem',
        '150px': '0 0 150px',
      },
      fontFamily: {
        'workSans': ['Work Sans', 'sans-serif']
      },
      boxShadow:{
        'sidebar-more': 'rgba(0, 0, 0, 0.35) 0px 2px 10px',
        'popover': 'rgba(0, 0, 0, 0.24) 0px 3px 8px',
        'dark-popover': 'rgba(128, 128, 128, 0.24) 0px 3px 8px',
        'post-item': '0 0 0 1px #e0e0e0',
        'post-item-dark': '0 0 0 1px rgba(255, 255, 255, 0.15)',
      },
      minWidth: {
        '10': '2.5rem',
        '40': '10rem'
      },
      maxWidth: {
        'half': '50%',
        '35vh': '35vh',
        'container': '1090px',
        '5/12': '41.666667%',
        '1/2': '50%',
        '7/12': '58.333333%',
        '3/5': '60%',
        '2/3': '66.666667%',
        '3/4': '75%',
        '10/12': '83.333333%'
      },
      width: {
        'base-36': '9.375rem',
        'main-content': 'calc(100% - 350px)'
      },
      minHeight: {
        '40': '10rem',
        '96': '24rem'
      },
      height: {
        'base-36': '9.375rem',
        'chat': 'calc(100vh - 7rem)',
        'bubble-chat-box': '32rem'
      },
      maxHeight: {
        '1/3': '33.333333%',
        '1/2': '50%'
      },
      borderWidth: {
        '3': '3px'
      },
      animation: {
        'heart-glow': 'heartGlow 2s ease-out infinite',
        'spin-slow': 'spin 3s linear infinite',
      },
      keyframes: {
        heartGlow: {
          '0%': {
            transform: 'scale(1)',
            boxShadow: '0 0 0 0 rgba(235,51,73,0.6)',
          },
          '40%': {
            transform: 'scale(1.15)',
            boxShadow: '0 0 0 12px rgba(235,51,73,0)',
          },
          '100%': {
            transform: 'scale(1)',
            boxShadow: '0 0 0 0 rgba(235,51,73,0)',
          },
        },
      },
    }
  },
  plugins: [
    require('tailwindcss-rtl'),
    plugin(function({ addVariant, e }) {
      addVariant('documentScrollingDown', ({ modifySelectors, separator }) => {
        modifySelectors(({ className }) => {
          return `.documentScrollingDown .${e(`documentScrollingDown${separator}${className}`)}`;
        });
      });
    }),
  ],
}
