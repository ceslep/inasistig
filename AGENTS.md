# AGENTS.md - Inasistig Code Guidelines

## Commands

```bash
npm run dev        # Dev server (Vite) - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build locally
npm run deploy     # Build + deploy to GitHub Pages
npm run check      # Type checking (svelte-check + tsc)
```

**Testing**: No test framework. Manual testing only:
- Run `npm run dev` and open `http://localhost:5173`
- For single component testing, modify App.svelte to import and render only that component

**Linting**: No separate linter. Run `npm run check` for type checking.

---

## Stack

- **Framework**: Svelte 5 + TypeScript + Vite
- **Styling**: TailwindCSS 4.x with CSS custom properties for theming
- **Alerts**: SweetAlert2 for modals/confirmations
- **Exports**: ExcelJS (Excel), jsPDF (PDF)
- **Deployment**: GitHub Pages (base: `/inasistig/`)
- **Backend**: PHP + MySQL

---

## Project Structure

```
src/
├── api/                 # API service layer
├── components/         # Svelte components
├── lib/                # Stores (themeStore.ts)
├── assets/             # Static assets (images, PHP backend)
├── constants.ts        # App constants (URLs, config)
├── app.css             # Global styles + CSS variables
└── App.svelte          # Root component
```

---

## Code Style

### Import Order (separate with blank lines)
1. Node built-ins (svelte: onMount, onDestroy)
2. External libraries (sweetalert2, etc.)
3. Internal services/api
4. Internal constants
5. Internal stores
6. Internal assets/images
7. Internal components

### TypeScript
- **Always** use interfaces/types, never `any`
- Named exports preferred; default for main route components
- Constants in `UPPER_SNAKE_CASE`
- Full function typing: `const fetchData = async (id: string): Promise<Data[]> => {...}`

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/vars | camelCase | `handleClick` |
| Interfaces | PascalCase | `interface Estudiante` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL` |
| CSS classes | kebab-case | `btn-primary` |

---

## Patterns

### State Management (Svelte 5)
```typescript
// Svelte 5 runes
let count = $state(0);
let doubled = $derived(count * 2);

// Props - both styles in use (Svelte 4 export let still common)
let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();
```
Note: Both `$props()` (Svelte 5) and `export let` (Svelte 4) are used. Prefer `$props()` for new code.

### Component Props
```svelte
<script lang="ts">
  let { onBack, title = "Default Title" }: { onBack: () => void; title?: string } = $props();
</script>
<button onclick={onBack}>{title}</button>
```

### Navigation
- SPA with `activeView` state (string-based routing)
- Consistent `handleBack()` returning to "dashboard"
- Use Svelte transitions: `fade`, `fly`, `slide`

### Error Handling
```typescript
try {
  const result = await fetch(url);
  if (!result.ok) throw new Error("Failed");
} catch (e) {
  console.error(e);
  await Swal.fire({
    icon: "error",
    title: "Error",
    text: "Mensaje en español",
    confirmButtonColor: "#ef4444"
  });
}
```

### Loading States
```typescript
let isLoading = $state(false);
const handleSubmit = async () => {
  isLoading = true;
  try { await saveData(); }
  finally { isLoading = false; }
};
```

---

## Theming & CSS

CSS variables in `app.css`: `--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`

```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">Content</div>
```

- Use CSS variables for theme-aware styling
- Prefer Tailwind utility classes
- Mobile-first: `class="w-full sm:w-auto"`

---

## Available Skills

Use the `skill` tool to load domain-specific instructions:
- `ui-inasistencias` - UI component standards for inasistencias
- `google-sync` - Google Sheets sync logic
- `api-inasistig` - PHP backend and MySQL queries

---

## Checklist

- [ ] TypeScript with full types (no `any`)
- [ ] CSS variables for theming
- [ ] Tailwind utility classes
- [ ] Error handling with SweetAlert2
- [ ] Loading states for async operations
- [ ] Responsive (mobile-first)
- [ ] `npm run check` passes
- [ ] Existing functionality intact

---

## Notes

1. Always use TypeScript - never compromise typing
2. Follow existing component patterns
3. All components must support light/dim/dark themes
4. UI text in Spanish
5. Mobile-first responsive design
6. Ask before git commits

---

## Backend API (PHP)

- PHP files in `src/assets/php/` and `public/assets/php/`
- Use MySQL via PHP for data persistence
- All URLs in `constants.ts` - never hardcode
- Always type API responses with interfaces
