# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
npm run dev          # Dev server at http://localhost:5173
npm run build        # Production build to dist/
npm run preview      # Preview production build
npm run deploy       # Build + deploy to GitHub Pages
npm run check        # Type checking (svelte-check + tsc)
```

Single file type checking:
```bash
npx svelte-check --tsconfig ./tsconfig.app.json src/path/to/file.svelte
```

No test framework — manual testing only via `npm run dev`.

## Architecture

**Svelte 5 + TypeScript + Vite PWA** deployed to GitHub Pages at base path `/inasistig/`.

### Routing
String-based SPA routing in `App.svelte` via `activeView` state variable. Each view is dynamically imported for code splitting. Navigation back always returns to `"dashboard"`.

Views: `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`, `horas_laborables`

### Modules (6 pedagogical tools)
- **Registro Diario** (`InasistenciaForm.svelte`) — Attendance tracking
- **Anotador de Clase** (`Anotador.svelte`) — Class notes/incidents
- **Diario de Campo** (`Diario.svelte`) — Pedagogical journal
- **Planeador de Clases** (`ClassPlannerForm.svelte`) — Lesson planning with DBA mapping
- **Observador** (`Observador.svelte`) — Behavioral observation with signature canvas
- **PIAR** (`Piar.svelte`) — Inclusive education plan (NEE students)

Each module has companion filter/report components (e.g., `InasistenciaFilter.svelte`, `ReportGeneratorInas.svelte`).

### API & Data Flow
- **No service layer:** API calls are made directly with `fetch` in components. Constants in `src/constants.ts` define all endpoint URLs — never hardcode URLs.
- **Backend:** PHP endpoints at `app.iedeoccidente.com` with two base paths:
  - `BASE_URL` (`/ig/`) — read endpoints (get students, teachers, options)
  - `API_URL_GS` (`/gs/`) — write endpoints (save to Google Sheets)
- **AI integration:** `AI_PROXY_URL` endpoint + `@openrouter/sdk` for AI features (`ia.svelte`)
- **Offline-first:** IndexedDB queue (`src/lib/offlineQueue.ts`) enqueues failed POST requests; `src/lib/networkStore.ts` triggers sync on reconnect via `processQueue()`.

### State Management
- **Components:** Svelte 5 runes (`$state()`, `$derived()`, `$props()`)
- **Global stores:** `networkStore.ts` and `themeStore.ts` use Svelte 4 `writable()` stores (not runes), imported with `$` prefix in components

### Theming
Three themes (light, dim, dark) via CSS custom properties in `app.css`. Theme class (`dim` or `dark`) applied to `<html>`. Apply with Tailwind arbitrary values:
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
```
Key variables: `--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`

### Reusable UI Components
Located in `src/components/ui/` — Badge, Button, Card, Toast, Skeleton, Tooltip, NetworkStatus. Exported via `index.ts`.

### Version System
`src/version.ts` defines `APP_VERSION` and `APP_BUILD_DATE`. On load and visibility change, fetches `/inasistig/version.json` to detect updates, then clears service worker caches and force-reloads. Update `APP_VERSION` when deploying.

### Analytics
`src/lib/analyticsService.ts` batches events and sends to `ANALYTICS_URL`. Session-based tracking with `sendBeacon` on unload. `AnalyticsModal.svelte` displays usage stats (floating button in `App.svelte`).

### Exports
Excel (ExcelJS), PDF (jsPDF + jspdf-autotable), file download (file-saver). Vite manually chunks these in `vite.config.ts` for bundle optimization.

### PWA
Service worker with auto-update (Workbox). Runtime caching (StaleWhileRevalidate) for API GET endpoints. Manifest configured for standalone display. `version.json` check forces hard reload on new versions.

## Code Style

- **All UI text in Spanish**
- **Never use `any`** — use `interface` for object shapes, `type` for unions/aliases, `unknown` when type is truly unknown
- **Formatting:** 2-space indent, trailing commas, single quotes, ~100 char max line length, arrow functions for callbacks
- **Import order:** Svelte builtins → external libs → internal services → constants → stores → assets → components (separated by blank lines)
- **Naming:** Components PascalCase, functions/vars camelCase, constants UPPER_SNAKE_CASE, interfaces PascalCase, CSS kebab-case
- **Error handling:** try/catch with SweetAlert2 alerts (messages in Spanish, `confirmButtonColor: "#ef4444"`)
- **Loading states:** `$state(false)` toggled in try/finally blocks
- **Responsive:** Mobile-first with Tailwind (`w-full sm:w-auto`)
- **All components must support all three themes**
- **Icons:** lucide-svelte
- **localStorage keys:** `theme`, `docenteMaterias`, `docenteMateriasDiario`, `lastDocente`, `lastDocenteDiario` — used for persistence across sessions

## Checklist Before Committing

- `npm run check` passes
- Existing functionality intact
- Components work in light/dim/dark themes
- Responsive on mobile
- Ask user before committing
