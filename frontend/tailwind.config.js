/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#e8eff7',
          100: '#e8eff7',
          200: '#e0e5ed',
          300: '#94a1b0',
          700: '#4474a4',
          800: '#17334f',
          900: '#041e37',
          950: '#041e37',
        },
        accent: {
          DEFAULT: '#f4a83c',
          hover: '#e89a2e',
          light: '#f6f2ec',
        },
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Bricolage Grotesque', 'Plus Jakarta Sans', 'sans-serif'],
      },
      boxShadow: {
        card: '0 4px 6px -1px rgb(16 42 67 / 0.08), 0 2px 4px -2px rgb(16 42 67 / 0.06)',
        'card-hover': '0 20px 25px -5px rgb(16 42 67 / 0.12), 0 8px 10px -6px rgb(16 42 67 / 0.08)',
      },
    },
  },
  plugins: [],
}
