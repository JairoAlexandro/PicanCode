import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'react'),
    },
  },
  test: {
    environment: 'jsdom',   
    globals: true,
    setupFiles: './test/setup.js',
    include: ['**/test/**/*.test.{js,jsx,ts,tsx}'],
    coverage: {
      reporter: ['text', 'html'],
      exclude: ['node_modules/', 'test/', 'dist/'],
    },
  },
})