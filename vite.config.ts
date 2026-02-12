import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";

// https://vite.dev/config/
export default defineConfig({
  base: "/inasistig/",
  plugins: [svelte(), tailwindcss()],
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          // Separar librerías grandes en chunks separados
          vendor: ['svelte', 'svelte/transition', 'svelte/store'],
          ui: ['sweetalert2'],
          excel: ['exceljs'],
          pdf: ['jspdf', 'jspdf-autotable'],
          utils: ['file-saver']
        }
      }
    },
    chunkSizeWarningLimit: 800 // Aumentar límite de advertencia a 800kB
  }
});
