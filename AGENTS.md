# AGENTS.md - Inasistig Code Guidelines

## Commands
```bash
npm run dev        # Dev server (Vite) - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build locally
npm run deploy     # Build + deploy to GitHub Pages
npm run check      # Type checking (svelte-check + tsc)
```
**Single File Check**: `npx svelte-check --tsconfig ./tsconfig.app.json src/path/to/file.svelte`
**Manual Testing**: No test framework - run `npm run dev` and open `http://localhost:5173`. For single component testing, modify `App.svelte` to import and render only that component.
**Note**: No explicit lint command - type checking with `svelte-check` is the primary validation.

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
├── api/              # API service layer (service.ts)
├── components/       # Svelte components (PascalCase)
│   ├── ui/          # Reusable UI components
│   └── features/    # Feature-specific components
├── lib/             # Stores (themeStore.ts)
├── assets/          # Static assets, PHP backend
├── constants.ts     # App constants (URLs, config)
├── types.ts         # TypeScript interfaces
├── app.css          # Global styles + CSS variables
└── App.svelte       # Root component
```

---

## Code Style

### Import Order (strict)
1. Node built-ins (`svelte: onMount`, `svelte:store`)
2. External libraries (sweetalert2, exceljs, jspdf, lucide-svelte)
3. Internal services/api
4. Internal constants
5. Internal stores
6. Internal assets/images
7. Internal components

### TypeScript Rules
- **NEVER use `any`** - always use interfaces/types
- Use `interface` for object shapes, `type` for unions/aliases
- Full function typing required: `const fetchData = async (id: string): Promise<Data[]> => {...}`
- Use optional chaining (`?.`) and nullish coalescing (`??`)
- Prefer `unknown` over `any` when type is truly unknown

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/vars | camelCase | `handleClick` |
| Interfaces | PascalCase | `Estudiante`, `Inasistencia` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL`, `MAX_FILE_SIZE` |
| CSS classes | kebab-case | `btn-primary`, `card-shadow` |
| File names | kebab-case | `api-service.ts`, `theme-store.ts` |

### Formatting
- Use 2-space indentation
- Trailing commas in objects/arrays
- Single quotes for strings
- Prefer arrow functions for callbacks
- Max line length: 100 characters

---

## Svelte 5 Patterns

### State Management
```typescript
// Primitive state
let count = $state(0);

// Derived state
let doubled = $derived(count * 2);

// Props with defaults
let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();

// Effect (use sparingly)
$effect(() => {
  console.log(count);
  return () => {}; // cleanup
});
```

### Component Structure
```svelte
<script lang="ts">
  // Props
  let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();

  // State
  let isLoading = $state(false);

  // Derived
  $: missingFields = !formData.name;

  // Handlers
  const handleSubmit = async () => { /* ... */ };
</script>

<div class="component-class">
  <!-- Template content -->
</div>

<style>
  /* Scoped styles - use Tailwind when possible */
</style>
```

---

## Navigation & Routing
- SPA with `activeView` state (string-based routing)
- Views: "dashboard", "registro", "reportes", "configuracion"
- Consistent `handleBack()` returning to "dashboard"
- Use Svelte transitions: `fade`, `fly`, `slide`

---

## Error Handling

### API Errors
```typescript
try {
  const response = await fetch(url);
  if (!response.ok) throw new Error(`HTTP ${response.status}`);
  const data = await response.json();
  return data;
} catch (e) {
  console.error(e);
  await Swal.fire({
    icon: "error",
    title: "Error",
    text: "Mensaje en español",
    confirmButtonColor: "#ef4444",
    timer: 3000,
    timerProgressBar: true
  });
  throw e;
}
```

### Form Validation
```typescript
$: validationErrors = (() => {
  const errors: string[] = [];
  if (!formData.nombre?.trim()) errors.push("El nombre es requerido");
  if (formData.edad < 18) errors.push("Debe ser mayor de edad");
  return errors;
})();

$: isValid = validationErrors.length === 0;
```

### Network Error Detection
```typescript
const isNetworkError = (error: unknown): boolean => {
  return error instanceof TypeError && error.message === "Failed to fetch";
};
```

---

## Loading States
```typescript
let isLoading = $state(false);

const handleAsyncOperation = async () => {
  isLoading = true;
  try {
    await performOperation();
  } catch (e) {
    // Error handled above
  } finally {
    isLoading = false;
  }
};
```

---

## Theming & CSS

### CSS Variables (app.css)
```css
:root {
  --bg-primary: var(--bg-primary);
  --bg-secondary: var(--bg-secondary);
  --text-primary: var(--text-primary);
  --text-secondary: var(--text-secondary);
  --card-bg: var(--card-bg);
  --card-border: var(--card-border);
  --accent-primary: var(--accent-primary);
  --border-primary: var(--border-primary);
}
```

### Usage
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  Content
</div>
```

### Guidelines
- Use CSS variables for theme-aware styling
- Prefer Tailwind utility classes over custom CSS
- Mobile-first: `class="w-full sm:w-auto"`
- Use semantic class names

---

## API Service

### Service Layer Pattern
```typescript
// src/api/types.ts
interface ApiResponse<T> {
  status: "success" | "error";
  data?: T;
  message?: string;
}

// src/api/service.ts
const API_BASE_URL = import.meta.env.VITE_API_URL;

export const fetchEstudiantes = async (idDocente: string): Promise<Estudiante[]> => {
  const response = await fetch(`${API_BASE_URL}/estudiantes.php?id_docente=${idDocente}`);
  if (!response.ok) throw new Error("Failed to fetch");
  const result: ApiResponse<Estudiante[]> = await response.json();
  if (result.status === "error") throw new Error(result.message);
  return result.data ?? [];
};
```

### PHP Backend Guidelines
- Use PDO with prepared statements
- Validate `id_docente` from session/cookie
- Return `{"status": "success", "data": [...]}` format
- Handle CORS for local development

---

## Available Skills
Use the `skill` tool for domain-specific guidance:
- `ui-inasistencias` - UI component standards, button styles, alert patterns
- `api-inasistig` - PHP backend, MySQL queries, PDO patterns
- `google-sync` - Google Sheets sync logic and API integration

---

## Checklist Before Commit
- [ ] `npm run check` passes with no errors
- [ ] No `any` types - use proper interfaces
- [ ] All async functions have try/catch
- [ ] Loading states for user interactions
- [ ] Error messages in Spanish
- [ ] Mobile-first responsive design
- [ ] SweetAlert2 for confirmations (destructive actions)
- [ ] CSS variables for theming (light/dim/dark)
- [ ] Icons from `lucide-svelte` (import individually)

---

## Important Notes
1. **Always use TypeScript** - never compromise typing
2. **UI text in Spanish** - all user-facing messages
3. **Ask before git commits** - don't auto-commit
4. **localStorage** for user preferences (docente, theme)
5. **Service worker** for offline read operations
6. **Confirm destructive actions** with SweetAlert2
