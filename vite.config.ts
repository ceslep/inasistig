import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";
import { VitePWA } from "vite-plugin-pwa";

// https://vite.dev/config/
export default defineConfig({
  base: "/inasistig/",
  plugins: [
    svelte(),
    tailwindcss(),
    VitePWA({
      registerType: "autoUpdate",
      devOptions: {
        enabled: true,
      },
      workbox: {
        globPatterns: ["**/*.{js,css,html,ico,png,svg,woff,woff2,webmanifest}"],
        navigateFallback: "index.html",
        navigateFallbackAllowlist: [/^\/inasistig\//],
        runtimeCaching: [
          {
            urlPattern:
              /^https:\/\/app\.iedeoccidente\.com\/ig\/(getprofes|getMaterias|getEstudiantes|getOpcionesAnotador|getOpcionesAnotador2|getOpcionesAnotador3|adiario)\.php/,
            handler: "StaleWhileRevalidate",
            options: {
              cacheName: "api-reference-data",
              expiration: {
                maxEntries: 20,
                maxAgeSeconds: 60 * 60 * 24,
              },
              cacheableResponse: {
                statuses: [0, 200],
              },
            },
          },
        ],
      },
      manifest: {
        name: "Ecosistema Digital Instituto Guática",
        short_name: "EIE Digital",
        description:
          "Ecosistema digital del Instituto Guática para la gestión pedagógica y administrativa.",
        theme_color: "#ffffff",
        background_color: "#020617",
        display: "standalone",
        scope: "/inasistig/",
        start_url: "/inasistig/",
        icons: [
          {
            src: "assets/eie.png",
            sizes: "512x512",
            type: "image/png",
            purpose: "any",
          },
          {
            src: "assets/eie.png",
            sizes: "512x512",
            type: "image/png",
            purpose: "maskable",
          },
        ],
      },
    }),
  ],
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
