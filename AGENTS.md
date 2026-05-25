# AGENTS.md - Inasistig Quick Reference

## Commands
```bash
npm run dev        # Dev server - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build
npm run check      # svelte-check + tsc -p tsconfig.node.json (runs BOTH)
npm run deploy     # Build + gh-pages to GitHub Pages at /inasistig/
```

Single file check: `npx svelte-check --tsconfig ./tsconfig.app.json src/path/to/file.svelte`

**No test framework** — manual testing via `npm run dev` only.

## TypeScript
- `tsconfig.app.json`: `checkJs: false` — JS files NOT type-checked
- `tsconfig.node.json`: strict, checked by `npm run check` (vite.config.ts only)
- No `any` types — use `interface` for shapes, `type` for unions, `unknown` when needed

## Version Sync (REQUIRED before deploy)
**Both** `src/version.ts` (`APP_VERSION`) **and** `public/version.json` (`version`) must match.

## Routing
String-based SPA via `activeView` in `App.svelte`. Browser back always returns to `"dashboard"`. Views dynamically imported. Views: `dashboard`, `inasistencia`, `anotador`, `diario`, `planeador`, `observador`, `piar`, `horarios`, `horas_laborables`, `actividades_recuperacion`, `acta_area`, `acta_izada`, `acta_padres`

## Auth & API
- Google OAuth via `LoginScreen.svelte` → `authStore.ts` (Svelte 4 `writable()` stores)
- Teacher matching strips trailing `-N` suffix ("Juan-5" → "Juan")
- Token expiry checked every 5 min + on visibility change
- API: `/ig/` (read), `/gs/` (write). PHP backend at `app.iedeoccidente.com`. Defined in `src/constants.ts`.
- `VITE_OPENROUTER_API_KEY` handled server-side via `ai_proxy.php` — no need in `.env` for AI features

## State Management
- **Components**: Svelte 5 runes (`$state()`, `$derived()`, `$props()`)
- **Two-way binding**: `$bindable()` for bindable props
- **Global stores** (`authStore`, `networkStore`, `themeStore`): Svelte 4 `writable()` — use `$storeName` in components
- **Snippets**: `children: Snippet` prop + `{@render children()}` instead of slots

## Theming
Three themes (light/dim/dark) via CSS custom properties on `<html>`. Tailwind arbitrary values: `bg-[rgb(var(--bg-primary))]`. Key vars: `--bg-primary`, `--text-primary`, `--accent-primary`, `--card-bg`, `--border-primary`.

Tailwind v4 with `@tailwindcss/vite` plugin (not PostCSS). Use CSS custom properties for theming, not Tailwind config.

## Admin Access
`horas_laborables/AdminStats.svelte` shown after user clicks "Ver Estadísticas Globales" (calendar view → Swal → confirm). Password-gated per-month access using date-based password. **No email whitelist** in code.

## Cobertura System (`src/components/horarios/`)
- Step 1: Select day + absent teachers (with absence type/motivo)
- Step 2: Analysis table — red = hours freed by absence
- Step 3: Auto-assign coverage — toggle each to approve
- Rules: max 1 h/day per teacher, max 2 h/week per teacher
- **ORIENTADOR, COORDINADOR and BIBLIOTECA have NO limits** — can cover any amount
- Saves to Google Sheets `historial` tab (spreadsheet `1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw`)
- PHP: `save_cobertura.php`, `get_coberturas.php`, `delete_cobertura.php` at `/gs/`
- Tests: `npm run test:unit` (22 tests), `npm run test:integration` (4 tests, requires Google Sheets column I header "MOTIVO")

## Key Conventions
- All UI text in Spanish
- Error messages via SweetAlert2 (`confirmButtonColor: "#ef4444"`)
- Icons: `@lucide/svelte` (individual imports). Brand icons: `@icons-pack/svelte-simple-icons`. `@iconify/svelte` in `actividades_recuperacion`
- localStorage keys: `theme`, `docenteMaterias`, `docenteMateriasDiario`, `lastDocente`, `lastDocenteDiario`, `planeaciones_local`, `app_version`, `dismissedFeatureAlert*`
- Mobile-first Tailwind (`w-full sm:w-auto`)
- Commit checklist: `npm run check` passes, all 3 themes work, no `any` types, Spanish errors, ask before commit

## Skills
Use `svelte5-best-practices` and `svelte-code-writer` for Svelte component work.
