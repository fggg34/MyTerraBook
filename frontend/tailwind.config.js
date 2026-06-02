/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f4f8',
          100: '#d9e2ec',
          200: '#bcccdc',
          300: '#9fb3c8',
          700: '#334e68',
          800: '#243b53',
          900: '#102a43',
          950: '#0a1929',
        },
        accent: {
          DEFAULT: '#ea580c',
          hover: '#c2410c',
          light: '#ffedd5',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      boxShadow: {
        card: '0 4px 6px -1px rgb(16 42 67 / 0.08), 0 2px 4px -2px rgb(16 42 67 / 0.06)',
        'card-hover': '0 20px 25px -5px rgb(16 42 67 / 0.12), 0 8px 10px -6px rgb(16 42 67 / 0.08)',
      },
    },
  },
  plugins: [],
}
