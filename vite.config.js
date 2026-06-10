import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [laravel(['resources/js/app.js'])],
  server: {
    host: true,
    allowedHosts: [
      'renegade-italicize-fraction.ngrok-free.dev'
    ]
  }
})
