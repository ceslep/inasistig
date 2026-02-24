# AGENTS.md - Inasistig Code Guidelines

## Commands

```bash
npm run dev        # Dev server (Vite) - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build locally
npm run deploy     # Build + deploy to GitHub Pages
npm run check      # Type checking (svelte-check + tsc)
```

**Manual Testing**: No test framework. Run `npm run dev` and open `http://localhost:5173`. For single component testing: modify `App.svelte` to import and render only that component. Test PHP backend: place files in `src/assets/php/` and access via browser.

---

## Stack

- **Framework**: Svelte 5 + TypeScript + Vite
- **Styling**: TailwindCSS 4.x with CSS custom properties
- **Alerts**: SweetAlert2
- **Exports**: ExcelJS, jsPDF
- **Backend**: PHP + MySQL
- **Deployment**: GitHub Pages (base: `/inasistig/`)

---

## Project Structure

```
src/
├── api/           # API service layer
├── components/   # Svelte components
├── lib/          # Stores (themeStore.ts)
├── assets/       # Static assets (images, PHP backend)
├── constants.ts  # App constants (URLs, config)
├── app.css       # Global styles + CSS variables
└── App.svelte    # Root component
```

---

## Code Style

### Import Order (separate with blank lines)
1. Node built-ins (`svelte: onMount, onDestroy`)
2. External libraries (sweetalert2, exceljs, jspdf)
3. Internal services/api
4. Internal constants
5. Internal stores
6. Internal assets/images
7. Internal components

### TypeScript
- **Never use `any`** - always use interfaces/types. Never cast to `as any`.
- Define interfaces at the top of components (e.g., `interface Estudiante`)
- Full function typing: `const fetchData = async (id: string): Promise<Data[]> => {...}`
- Named exports preferred; default for main route components

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/vars | camelCase | `handleClick` |
| Interfaces | PascalCase | `interface Estudiante` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL` |

### State Management (Svelte 5)
```typescript
let count = $state(0);
let doubled = $derived(count * 2);
let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();
```

### Error Handling
```typescript
try {
  const result = await fetch(url);
  if (!result.ok) throw new Error("Failed");
} catch (e) {
  console.error(e);
  await Swal.fire({ icon: "error", title: "Error", text: "Mensaje en español", confirmButtonColor: "#ef4444" });
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
- [ ] UI text in Spanish
- [ ] Ask before git commits
