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

Views: `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`, `horas_laborables`, `actividades_recuperacion`

Last two views (`horas_laborables`, `actividades_recuperacion`) are external modules loaded via `<iframe>` — teacher email and name passed as URL params.

### Modules (6 pedagogical tools)
- **Registro Diario** (`InasistenciaForm.svelte`) — Attendance tracking
- **Anotador de Clase** (`Anotador.svelte`) — Class notes/incidents
- **Diario de Campo** (`Diario.svelte`) — Pedagogical journal
- **Planeador de Clases** (`ClassPlannerForm.svelte`) — Lesson planning with DBA mapping
- **Observador** (`Observador.svelte`) — Behavioral observation with signature canvas
- **PIAR** (`Piar.svelte`) — Inclusive education plan (NEE students)

Each module has companion filter/report components (e.g., `InasistenciaFilter.svelte`, `ReportGeneratorInas.svelte`).

### Authentication
`LoginScreen.svelte` handles Google OAuth via `google.accounts`. Auth state managed in `src/lib/authStore.ts` (`authUser`, `isAuthenticated`, `docenteName` stores). Token expiry checked every 5 minutes + on visibility change. `findMatchingDocente()` uses fuzzy matching: exact match first, then strips trailing number/dash suffix (e.g., "Juan-5" → "Juan").

### API & Data Flow
- **No service layer** — components call `fetch` directly with endpoint constants from `src/constants.ts`. Never hardcode URLs.
- **Backend:** PHP endpoints at `app.iedeoccidente.com` with two base paths:
  - `BASE_URL` (`/ig/`) — read endpoints (get students, teachers, options)
  - `API_URL_GS` (`/gs/`) — write endpoints (save to Google Sheets)
- **AI integration:** `AI_PROXY_URL` endpoint + `@openrouter/sdk` for AI features (`ia.svelte`)
- **Offline-first:** IndexedDB queue (`src/lib/offlineQueue.ts`) enqueues failed POST requests; `src/lib/networkStore.ts` triggers sync on reconnect via `processQueue()`. Queue processing stops on first error (no retry loop). Planeador also supports localStorage drafts (max 100) with JSON import/export (`exportPlaneadoresLocales`/`importPlaneadoresLocales`).

### State Management
- **Components:** Svelte 5 runes (`$state()`, `$derived()`, `$props()`)
- **Two-way binding:** Use `$bindable()` for props that parents bind to (e.g., `TomSelect.svelte`)
- **Snippets:** UI components use `children: Snippet` prop + `{@render children()}` instead of slots
- **Global stores:** `authStore.ts`, `networkStore.ts`, and `themeStore.ts` use Svelte 4 `writable()` stores (not runes), imported with `$` prefix in components

### Theming
Three themes (light, dim, dark) via CSS custom properties in `app.css`. Theme class (`dim` or `dark`) applied to `<html>`. Apply with Tailwind arbitrary values:
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
```
Key variables: `--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`

### Reusable UI Components
Located in `src/components/ui/` — Badge, Button, Card, Toast, Skeleton, Tooltip, NetworkStatus. Exported via `index.ts`.

`ModuleHeader.svelte` — shared sticky header for all modules. Takes `title`, `subtitle`, `onBack`, and optional `actions` snippet. Includes back button, theme toggle, and user avatar.

### Version System
`src/version.ts` defines `APP_VERSION` and `APP_BUILD_DATE`. On load and visibility change, fetches `/inasistig/version.json` to detect updates, then clears service worker caches and force-reloads. **Both `src/version.ts` and `public/version.json` must be updated when deploying.**

### Analytics
`src/lib/analyticsService.ts` batches events and sends to `ANALYTICS_URL`. Session-based tracking with `sendBeacon` on unload. `AnalyticsModal.svelte` displays usage stats (floating button in `App.svelte`).

### Exports
Excel (ExcelJS), PDF (jsPDF + jspdf-autotable), file download (file-saver). Vite manually chunks these in `vite.config.ts` for bundle optimization.

### PWA
Service worker with auto-update (Workbox). Runtime caching (StaleWhileRevalidate) for reference data endpoints only (`getprofes`, `getMaterias`, `getEstudiantes`, `getOpcionesAnotador`, `adiario`) — max 20 entries, 1 day TTL. Manifest configured for standalone display. `version.json` check forces hard reload on new versions. SW update check runs hourly via `setInterval` in `main.ts`.

### Form Components
`TomSelect.svelte` wraps tom-select library with Svelte 5 `$bindable()` props. Supports `create`, `showFavorite`, `searchable` options. Requires async initialization — don't access tom-select instance synchronously after mount.

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
- **Icons:** `@lucide/svelte` (individual imports, e.g., `import { Menu } from '@lucide/svelte'`). Brand icons use `@icons-pack/svelte-simple-icons`.
- **localStorage keys:** `theme`, `docenteMaterias`, `docenteMateriasDiario`, `lastDocente`, `lastDocenteDiario`, `planeaciones_local`, `app_version`, `dismissedFeatureAlert*` — used for persistence across sessions
- **Debug pattern:** Components use `const DEBUG_FORCE_SHOW = false` to control feature popup visibility — set to `true` only during development
- **Large components:** `ClassPlannerForm.svelte` (~5500 lines), `Piar.svelte` (~2500 lines), `InasistenciaForm.svelte` (~1600 lines) — navigate carefully

## Checklist Before Committing

- `npm run check` passes
- Existing functionality intact
- Components work in light/dim/dark themes
- Responsive on mobile
- Ask user before committing
