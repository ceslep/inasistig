# AGENTS.md - Inasistig Code Guidelines

Guidelines for agentic coding agents working on the inasistig project.

---

## Commands

```bash
npm run dev        # Dev server (Vite) - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build locally
npm run deploy     # Build + deploy to GitHub Pages
npm run check      # Type checking (svelte-check + tsc) - LINT + TYPES
```

**Testing**: No test framework. Manual testing only:
1. Modify `App.svelte` to import/render only the component
2. Run `npm run dev` and open http://localhost:5173
3. Revert changes to `App.svelte` after testing

**PHP Backend**: Place PHP files in `src/assets/php/`, access at `http://localhost:5173/src/assets/php/file.php`

---

## Stack

- **Framework**: Svelte 5 + TypeScript + Vite
- **Styling**: TailwindCSS 4.x with CSS custom properties
- **Alerts**: SweetAlert2
- **Exports**: ExcelJS, jsPDF with jspdf-autotable
- **Backend**: PHP + MySQL
- **Deployment**: GitHub Pages (base: `/inasistig/`)

---

## Project Structure

```
src/
├── api/           # API service layer (service.ts)
├── components/   # Svelte components (PascalCase)
├── lib/          # Stores (themeStore.ts)
├── assets/       # Static assets (images, PHP backend)
├── constants.ts  # App constants (URLs, config)
├── app.css       # Global styles + CSS variables
└── App.svelte    # Root component
```

---

## Code Style

### Import Order (strict - separate groups with blank lines)

1. Node built-ins (`svelte: onMount, onDestroy`)
2. External libraries (`sweetalert2`, `exceljs`, `jspdf`)
3. Internal services/api
4. Internal constants
5. Internal stores
6. Internal assets/images
7. Internal components

### TypeScript Rules

- **Never use `any`** - always use explicit interfaces/types
- **Never cast to `as any`** - define proper types instead
- Define interfaces at the top of the component script
- Use full function typing including return types
- Default exports for main route components, named exports for utilities

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/variables | camelCase | `handleClick`, `isLoading` |
| Interfaces | PascalCase | `interface Estudiante` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL` |
| Boolean variables | is/has/should prefix | `isLoading`, `hasError` |

### Svelte 5 State Management (Required)

Use runes (`$state`, `$derived`, `$props`):
```typescript
let count = $state(0);
let doubled = $derived(count * 2);
let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();
```

### Error Handling

Always use try-catch with SweetAlert2 for user feedback:
```typescript
try {
  const result = await fetch(url);
  if (!result.ok) throw new Error("Failed to fetch");
  return await result.json();
} catch (e) {
  console.error(e);
  await Swal.fire({
    icon: "error", title: "Error", text: "Mensaje en español",
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
  catch (e) { /* error handling */ }
  finally { isLoading = false; }
};
```

---

## Theming & CSS

### CSS Variables (in `app.css`)

`--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`, `--bg-tertiary`, `--text-muted`

### Usage

```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">Content</div>
```

### Guidelines

- Use CSS variables for theme-aware styling
- Prefer Tailwind utility classes over custom CSS
- Mobile-first: `class="w-full sm:w-auto"`
- TailwindCSS 4.x: uses `@theme` directive, arbitrary values like `bg-[#ff0000]`

---

## PHP Backend

- Place PHP files in `src/assets/php/`
- Access via browser at `/src/assets/php/file.php`
- MySQL credentials stored in PHP files (not frontend)
- API endpoints communicate via `fetch()`

---

## Available Skills

Use the `skill` tool for domain-specific instructions:
- `ui-inasistencias` - UI component standards
- `google-sync` - Google Sheets sync logic
- `api-inasistig` - PHP backend and MySQL queries

---

## Checklist Before Committing

- [ ] TypeScript with full types (no `any`)
- [ ] CSS variables for theming
- [ ] Tailwind utility classes
- [ ] Error handling with SweetAlert2 (Spanish text)
- [ ] Loading states for async operations
- [ ] Responsive (mobile-first)
- [ ] `npm run check` passes with no errors
- [ ] Existing functionality intact
- [ ] UI text in Spanish
- [ ] Ask user before creating git commits

---

## Git Workflow

- Create feature branches for new features
- Commit messages in Spanish or English (be consistent)
- Ask before pushing to remote
- Never force push to main/master
