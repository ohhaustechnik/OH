import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
// base './' => Assets laden relativ, egal in welchem Unterordner es liegt
export default defineConfig({ plugins: [react()], base: './' })
