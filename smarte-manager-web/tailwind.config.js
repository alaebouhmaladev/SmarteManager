/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    // this tell tailwind to check and read all files in this project with this extention 
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        sm: {
          yellow: '#FDD760',
          dark: '#252525',
          cream: '#F6F2E9',
          white: '#FFFFFF',
          grey: '#7D7D7D',
        },
      },
      boxShadow: {
        'sm-card': '0 4px 12px rgba(0,0,0,0.04)',
        'md-card': '0 10px 25px rgba(0,0,0,0.06)',
      },
      borderRadius: {
        xl: '0.75rem',
        '2xl': '1rem',
      },
    },
  },
  plugins: [],
}
