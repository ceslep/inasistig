# AGENTS.md - Inasistig Quick Reference

## Commands
```bash
npm run dev        # Dev server - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build
npm run check      # Type checking (svelte-check + tsc -p tsconfig.node.json)
npm run deploy     # Build + deploy to GitHub Pages
```

Single file check: `npx svelte-check --tsconfig ./tsconfig.app.json src/path/to/file.svelte`

**No test framework** — manual testing via `npm run dev` only.

## TypeScript Config Notes
- `tsconfig.app.json`: `checkJs: false` — JS files in `src/` are NOT type-checked
- `tsconfig.app.json`: `allowArbitraryExtensions: true` — allows `.svelte.ts` etc.
- `tsconfig.node.json` is checked by `npm run check` (vite types)

## Svelte Skills
Use `svelte5-best-practices` and `svelte-code-writer` for Svelte component work.

## Environment
- Requires `VITE_OPENROUTER_API_KEY` in `.env` for AI features (free tier at openrouter.ai)
- Build deploys to GitHub Pages at `/inasistig/`

## Version Sync Required
**Both** `src/version.ts` (`APP_VERSION`) **and** `public/version.json` (`version`) must match before `npm run deploy`.

## Architecture

**Routing**: String-based SPA via `activeView` state in `App.svelte`. Browser back always returns to `"dashboard"`. Views dynamically imported.

**Views**: `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`, `horas_laborables`, `actividades_recuperacion`, `acta_area`, `acta_izada`, `acta_padres`

**Integrated Modules**: `horas_laborables` and `activ_recuperacion` are internal Svelte components (not iframes). Use auth from `authStore.ts`.

**Admin Access**: `horas_laborables/AdminStats.svelte` is shown after user confirms via Swal dialog; no email whitelist check.

**Auth**: Google OAuth via `LoginScreen.svelte`, stored in `authStore.ts`. Teacher matching strips trailing `-N` suffix ("Juan-5" → "Juan"). Token expiry checked every 5 min + on visibility change.

**API**: `/ig/` (read), `/gs/` (write). PHP backend at `app.iedeoccidente.com`. Defined in `src/constants.ts`.

**State Management**:
- Svelte 5 runes: `$state()`, `$derived()`, `$props()`
- `$bindable()` for two-way bound props
- Global stores: `authStore.ts`, `networkStore.ts`, `themeStore.ts` use Svelte 4 `writable()` (imported with `$` prefix)

**Theming**: Three themes (light/dim/dark) via CSS custom properties. Theme class on `<html>`. Use Tailwind: `bg-[rgb(var(--bg-primary))]`. Key vars: `--bg-primary`, `--text-primary`, `--accent-primary`, `--card-bg`, `--border-primary`.

**Tailwind**: v4 with `@tailwindcss/vite` plugin (not PostCSS). Use CSS custom properties for theming, not Tailwind config.

**Exports**: ExcelJS (chunked), jsPDF + jspdf-autotable, file-saver. Chart.js for PieChart.

**PWA**: Service worker with auto-update. Caches `/ig/` read endpoints (getprofes, getMaterias, getEstudiantes, getOpcionesAnotador, adiario) — 20 entries max, 1 day TTL. Auto-update clears all caches and unregisters service workers, then reloads.

**Google Drive**: `gdriveService.ts` for direct uploads. Used by report generators, PIAR, planeador, anotador, horas.

**Offline Queue**: IndexedDB queue in `src/lib/offlineQueue.ts` — stops on first error (no retry). Planeador uses localStorage drafts (max 100) with JSON import/export.

## Large Components (by line count)
- `ClassPlannerForm.svelte` (~6030 lines)
- `Piar.svelte` (~2740 lines)
- `ActaArea.svelte` (~1855 lines)
- `InasistenciaForm.svelte` (~1750 lines)
- `HoursRegistration.svelte` (~1400 lines)

## Key localStorage Keys
`theme`, `docenteMaterias`, `docenteMateriasDiario`, `lastDocente`, `lastDocenteDiario`, `planeaciones_local`, `app_version`, `dismissedFeatureAlert*`

## Before Commit
- [ ] `npm run check` passes
- [ ] All themes work (light/dim/dark)
- [ ] No `any` types (tsconfig.app.json has `checkJs: false` so `.js` files won't catch this — manually review)
- [ ] Spanish error messages (SweetAlert2)
- [ ] Ask before git commit