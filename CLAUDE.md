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
- **Service layer:** `api/service.ts` — typed fetch wrappers with offline support
- **Constants:** `src/constants.ts` — all API URLs, spreadsheet IDs, academic periods. Never hardcode URLs.
- **Backend:** PHP endpoints at `app.iedeoccidente.com` proxying to Google Sheets and MySQL
- **Offline-first:** IndexedDB queue (`src/lib/offlineQueue.ts`) syncs pending operations on reconnect via `src/lib/networkStore.ts`

### State Management
- Svelte 5 runes: `$state()`, `$derived()`, `$props()`
- Stores: `themeStore.ts` (light/dim/dark with localStorage), `networkStore.ts` (online/offline/syncing)

### Theming
Three themes (light, dim, dark) via CSS custom properties in `app.css`. Apply with Tailwind arbitrary values:
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
```
Key variables: `--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`

### Reusable UI Components
Located in `src/components/ui/` — Badge, Button, Card, Toast, Skeleton, Tooltip, NetworkStatus. Exported via `index.ts`.

### Exports
Excel (ExcelJS), PDF (jsPDF + jspdf-autotable), file download (file-saver). Vite manually chunks these for bundle optimization.

### PWA
Service worker with auto-update (60min interval). Workbox runtime caching (StaleWhileRevalidate) for API endpoints. Manifest configured for standalone display.

## Code Style

- **All UI text in Spanish**
- **Never use `any`** — always use interfaces/types
- **Import order:** Svelte builtins → external libs → internal services → constants → stores → assets → components (separated by blank lines)
- **Naming:** Components PascalCase, functions/vars camelCase, constants UPPER_SNAKE_CASE, interfaces PascalCase, CSS kebab-case
- **Error handling:** try/catch with SweetAlert2 alerts (messages in Spanish, `confirmButtonColor: "#ef4444"`)
- **Loading states:** `$state(false)` toggled in try/finally blocks
- **Responsive:** Mobile-first with Tailwind (`w-full sm:w-auto`)
- **All components must support all three themes**

## Checklist Before Committing

- `npm run check` passes
- Existing functionality intact
- Components work in light/dim/dark themes
- Responsive on mobile
- Ask user before committing
