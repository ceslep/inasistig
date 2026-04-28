# AGENTS.md - Inasistig Quick Reference

## Commands
```bash
npm run dev        # Dev server - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build
npm run check      # Type checking (svelte-check + tsc)
npm run deploy     # Build + deploy to GitHub Pages
```

## Environment
- Requires `VITE_OPENROUTER_API_KEY` in .env for AI features
- See `.env.example` for format

## Architecture (non-obvious)

- **Routing**: String-based SPA via `activeView` state in `App.svelte`. Back button always returns to `"dashboard"`. Views are dynamically imported for code splitting.
- **External modules**: `horas_laborables` and `actividades_recuperacion` load via `<iframe>` with teacher email/name as URL params.
- **Auth**: Google OAuth via `LoginScreen.svelte`, stored in `authStore.ts`. Fuzzy match: strips trailing `-N` suffix ("Juan-5" → "Juan").
- **API**: Two paths - `/ig/` (read), `/gs/` (write). PHP backend at `app.iedeoccidente.com`.
- **PWA**: Cache only 6 read endpoints, 20 entries max, 1 day TTL via vite-plugin-pwa.
- **Google Drive Integration**: Report generators (ReportGenerator.svelte, ReportGeneratorDiario.svelte, ReportGeneratorInas.svelte) offer direct save to Google Drive via the gdriveService module.

## Offline Behavior
- Queue (`src/lib/offlineQueue.ts`) stops on first error (no retry loop)
- Planeador: localStorage drafts (max 100) with JSON import/export

## Version Updates
**Both** `src/version.ts` **and** `public/version.json` must be updated on deploy.
Current: src/version.ts = 1.0.17, public/version.json = 1.0.7 (needs sync)

## Large Components (navigate carefully)
- `ClassPlannerForm.svelte` (~10,800 lines)
- `Piar.svelte` (~3,900 lines)
- `InasistenciaForm.svelte` (~1,950 lines)

## Skills (use for domain help)
```bash
skill name=ui-inasistencias   # UI component standards
skill name=api-inasistig       # PHP/MySQL patterns
skill name=google-sync         # Google Sheets sync
skill name=ui-ux-pro-max       # Design guidance
```

## Before Commit
- [ ] `npm run check` passes
- [ ] All themes work (light/dim/dark)
- [ ] No `any` types
- [ ] Spanish error messages (SweetAlert2)
- [ ] Ask before git commit

## Key localStorage Keys
`theme`, `docenteMaterias`, `lastDocente`, `planeaciones_local`, `app_version`