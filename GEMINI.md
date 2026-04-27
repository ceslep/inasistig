# GEMINI.md - Contexto de Inasistig

Este archivo proporciona instrucciones y contexto esencial para los agentes de IA que trabajen en el proyecto **Inasistig**, el Ecosistema Digital del Instituto Guática.

## 🚀 Resumen del Proyecto

**Inasistig** es una Progressive Web App (PWA) diseñada para la gestión pedagógica docente. Permite el seguimiento de asistencia, incidencias de clase, diarios de campo y planeación académica.

### Stack Tecnológico
- **Frontend:** Svelte 5 (Runes) + TypeScript.
- **Estilos:** TailwindCSS 4 + CSS Custom Properties para temas.
- **Build Tool:** Vite 7 + Vite PWA Plugin.
- **Base de Datos/Backend:** 
  - Endpoints PHP alojados en `app.iedeoccidente.com`.
  - Almacenamiento persistente en Google Sheets (vía proxy PHP).
  - Almacenamiento local mediante IndexedDB (cola offline) y LocalStorage.
- **Despliegue:** GitHub Pages (`/inasistig/`).

## 🛠 Comandos Clave

- `npm run dev`: Servidor de desarrollo (HMR).
- `npm run build`: Genera la build de producción en `dist/`.
- `npm run check`: Ejecuta validación de tipos (Svelte-check + TSC).
- `npm run deploy`: Construye y despliega en GitHub Pages.

## 🏗 Arquitectura y Patrones

### Navegación y Vistas
- **SPA basada en estados:** La navegación se gestiona en `App.svelte` mediante la variable `activeView`.
- **Code Splitting:** Las vistas se importan dinámicamente para optimizar la carga inicial.
- **Vistas principales:** `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`.

### Gestión de Estado
- **Runes (Svelte 5):** Uso intensivo de `$state`, `$derived`, `$props` y `$bindable` en componentes.
- **Stores (Svelte 4/Writable):** Los estados globales persistentes como `authStore`, `networkStore` y `themeStore` utilizan `writable` tradicional.

### Sincronización Offline (Offline-First)
- **Cola IndexedDB:** Las peticiones fallidas se encolan en `src/lib/offlineQueue.ts`.
- **Procesamiento:** `networkStore.ts` detecta el retorno de conexión y procesa la cola automáticamente.

### Autenticación
- **Google OAuth:** Manejado por `LoginScreen.svelte` y `authStore.ts`.
- **Fuzzy Matching:** El sistema asocia el usuario de Google con el nombre del docente en la base de datos institucional ignorando sufijos numéricos (ej: `Nombre-2` -> `Nombre`).

## 🎨 Convenciones de UI y Estilos

- **Temas:** Soporta `light`, `dim` y `dark`. Las clases se aplican al elemento `<html>`.
- **Variables CSS:** Se deben usar variables como `--bg-primary`, `--text-primary`, etc., mediante valores arbitrarios de Tailwind: `bg-[rgb(var(--bg-primary))]`.
- **Componentes UI:** Utilizar los componentes base en `src/components/ui/` (Button, Card, Badge, etc.).
- **Header:** Todos los módulos deben usar `ModuleHeader.svelte` para consistencia.

## 📝 Reglas de Desarrollo (Directrices)

1. **Idioma:** Todo el texto de la interfaz y mensajes de error deben estar en **Español**.
2. **Tipado:** No usar `any`. Definir interfaces precisas para las respuestas de la API y modelos de datos.
3. **API:** No hardcodear URLs. Usar siempre las constantes definidas en `src/constants.ts`.
4. **Alertas:** Utilizar `SweetAlert2` para notificaciones importantes y errores.
5. **PWA:** Al realizar cambios críticos, actualizar `src/version.ts` y `public/version.json` para forzar la actualización del Service Worker en los clientes.
6. **Responsive:** Priorizar diseño mobile-first.

## 📂 Estructura de Directorios

- `src/components/`: Componentes lógicos y módulos pedagógicos.
- `src/components/ui/`: Librería de componentes de diseño atómicos.
- `src/lib/`: Lógica de negocio, stores y servicios (Auth, Sync, Analytics).
- `src/assets/php/`: (Nota: El backend real es remoto, pero aquí residen referencias o scripts espejo).
- `public/`: Assets estáticos y manifiesto PWA.

---
**Última actualización:** Abril 2026
**Contexto de IA:** Optimizado para Svelte 5 y TypeScript.
