# AGENTS.md - Inasistig Quick Reference

## Commands
```bash
npm run dev        # Dev server - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build
npm run check      # Type checking (svelte-check + tsc)
npm run deploy     # Build + deploy to GitHub Pages
```

Single file check: `npx svelte-check --tsconfig ./tsconfig.app.json src/path/to/file.svelte`

**No test framework** — manual testing via `npm run dev` only.

## Svelte Skills
Use `svelte5-best-practices` and `svelte-code-writer` for Svelte component work.

## Environment
- Requires `VITE_OPENROUTER_API_KEY` in `.env` for AI features (free tier at openrouter.ai)
- Build deploys to GitHub Pages at `/inasistig/`

## Version Sync Required
**Both** `src/version.ts` (`APP_VERSION`) **and** `public/version.json` (`version`) must match before `npm run deploy`. Current: `1.0.23`.

## Architecture

**Routing**: String-based SPA via `activeView` state in `App.svelte`. Browser back always returns to `"dashboard"`. Views dynamically imported.

**Views**: `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`, `horas_laborables`, `actividades_recuperacion`, `acta_area`

**Integrated Modules**: `horas_laborables` and `activ_recuperacion` are internal Svelte components (not iframes). Use auth from `authStore.ts`.

**Admin Access**: `horas_laborables/AdminStats.svelte` shows only for `ceslep@gmail.com` or `rectoria.guatica@gmail.com`.

**Auth**: Google OAuth via `LoginScreen.svelte`, stored in `authStore.ts`. Teacher matching strips trailing `-N` suffix ("Juan-5" → "Juan"). Token expiry checked every 5 min + on visibility change.

**API**: Two paths - `/ig/` (read), `/gs/` (write). PHP backend at `app.iedeoccidente.com`.

**State Management**:
- Svelte 5 runes: `$state()`, `$derived()`, `$props()`
- `$bindable()` for two-way bound props
- Global stores: `authStore.ts`, `networkStore.ts`, `themeStore.ts` use Svelte 4 `writable()` (imported with `$` prefix)

**Theming**: Three themes (light/dim/dark) via CSS custom properties. Theme class on `<html>`. Use Tailwind: `bg-[rgb(var(--bg-primary))]`. Key vars: `--bg-primary`, `--text-primary`, `--accent-primary`, `--card-bg`, `--border-primary`.

**Exports**: ExcelJS (chunked), jsPDF + jspdf-autotable, file-saver. Chart.js for PieChart.

**PWA**: Service worker with auto-update. Caches `/ig/` read endpoints (getprofes, getMaterias, getEstudiantes, getOpcionesAnotador, adiario) — 20 entries max, 1 day TTL. Auto-update clears all caches and unregisters service workers, then reloads.

**Google Drive**: `gdriveService.ts` for direct uploads. Used by report generators, PIAR, planeador, anotador, horas.

**Offline Queue**: IndexedDB queue in `src/lib/offlineQueue.ts` — stops on first error (no retry). Planeador uses localStorage drafts (max 100) with JSON import/export.

## Large Components
- `ClassPlannerForm.svelte` (~6000 lines)
- `Piar.svelte` (~2700 lines)
- `InasistenciaForm.svelte` (~1600 lines)
- `HoursRegistration.svelte` (~1300 lines)

## Key localStorage Keys
`theme`, `docenteMaterias`, `docenteMateriasDiario`, `lastDocente`, `lastDocenteDiario`, `planeaciones_local`, `app_version`, `dismissedFeatureAlert*`

## Before Commit
- [ ] `npm run check` passes
- [ ] All themes work (light/dim/dark)
- [ ] No `any` types
- [ ] Spanish error messages (SweetAlert2)
- [ ] Ask before git commit