import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'
import { resolve } from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  appType: 'spa',
  plugins: [react()],
  build: {
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.html'),
        calendarEmbed: resolve(__dirname, 'calendar-embed.html'),
      },
    },
  },
  css: {
    postcss: {
      plugins: [tailwindcss(), autoprefixer()],
    },
  },
  server: {
    host: '127.0.0.1',
    port: Number(process.env.VITE_DEV_PORT) || 5174,
    strictPort: true,
    proxy: {
      '/backend': {
        target: `http://127.0.0.1:${process.env.BACKEND_PORT || 8080}`,
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/backend/, ''),
      },
    },
  },
})
