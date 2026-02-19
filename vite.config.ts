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
        manualChunks(id) {
          // Separar librerías grandes en chunks separados
          if (id.includes('node_modules')) {
            if (id.includes('svelte') && !id.includes('svelte/transition')) {
              return 'svelte';
            }
            if (id.includes('sweetalert2')) {
              return 'ui';
            }
            if (id.includes('exceljs')) {
              return 'excel';
            }
            if (id.includes('jspdf')) {
              return 'pdf';
            }
            if (id.includes('file-saver')) {
              return 'utils';
            }
            if (id.includes('html2canvas')) {
              return 'canvas';
            }
            if (id.includes('dompurify')) {
              return 'purify';
            }
          }
        }
      }
    },
    chunkSizeWarningLimit: 1200 // ExcelJS es grande (~1.1MB), pero se carga bajo demanda
  }
});
