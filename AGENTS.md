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
**Manual Testing**: No test framework - run `npm run dev` and open `http://localhost:5173`.

---

## Stack
- **Framework**: Svelte 5 + TypeScript + Vite
- **Styling**: TailwindCSS 4.x with CSS custom properties
- **Alerts**: SweetAlert2 | **Exports**: ExcelJS, jsPDF
- **Backend**: PHP + MySQL (PDO)
- **Deployment**: GitHub Pages (base: `/inasistig/`)

---

## Code Style

### Import Order
1. Node built-ins (`svelte: onMount`, `svelte:store`)
2. External libraries (sweetalert2, exceljs, jspdf, lucide-svelte)
3. Internal services/api
4. Internal constants
5. Internal stores
6. Internal components

### TypeScript Rules
- **NEVER use `any`** - use interfaces/types
- Full function typing: `const fetchData = async (id: string): Promise<Data[]> => {...}`
- Use optional chaining (`?.`) and nullish coalescing (`??`)

### Naming
| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/vars | camelCase | `handleClick` |
| Interfaces | PascalCase | `Estudiante` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL` |
| File names | kebab-case | `api-service.ts` |

### Formatting
- 2-space indentation, single quotes, trailing commas
- Max line length: 100 characters
- Arrow functions for callbacks

---

## Svelte 5 Patterns

```typescript
// State
let count = $state(0);
let doubled = $derived(count * 2);

// Props
let { onBack, title = "Default" }: { onBack: () => void; title?: string } = $props();

// Effects (use sparingly)
$effect(() => { console.log(count); return () => {}; });
```

---

## Error Handling
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

### Loading States
```typescript
let isLoading = $state(false);
const handleOp = async () => {
  isLoading = true;
  try { await doSomething(); }
  finally { isLoading = false; }
};
```

---

## Theming & CSS
- Use CSS variables for theming (`--bg-primary`, `--text-primary`, etc.)
- Prefer Tailwind utility classes over custom CSS
- Mobile-first: `class="w-full sm:w-auto"`

---

## API Service
```typescript
interface ApiResponse<T> {
  status: "success" | "error";
  data?: T;
  message?: string;
}

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
- Return `{"status": "success", "data": [...]}` format

---

## Available Skills
Use `skill` tool for domain-specific guidance:
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

---

## Important Notes
1. **Always use TypeScript** - never compromise typing
2. **UI text in Spanish** - all user-facing messages
3. **Ask before git commits** - don't auto-commit
4. **localStorage** for user preferences (docente, theme)
5. **PDO prepared statements** for security in PHP
6. **Responsive design** for mobile devices