# AGENTS.md - Inasistig Code Guidelines

## Commands

```bash
npm run dev        # Dev server (Vite)
npm run build      # Production build
npm run preview   # Preview build
npm run deploy    # Build + deploy to GitHub Pages
npm run check     # Type checking (svelte-check + tsc)
```

**Testing**: No test framework configured. Use manual `.test.html` files in root.

---

## Stack

- Svelte 5 + TypeScript + Vite
- TailwindCSS 4.x with CSS custom properties for themes
- SweetAlert2 for alerts, ExcelJS/jsPDF for exports
- Base path: `/inasistig/` (GitHub Pages)

---

## Project Structure

```
src/
├── components/    # Svelte components (Dashboard, InasistenciaForm, Anotador, Diario, *Filter)
├── lib/           # Stores (themeStore.ts)
├── assets/        # Static assets
├── constants.ts  # App constants/URLs
├── app.css       # Global styles + CSS variables
└── App.svelte    # Root component
```

---

## Code Style

### Svelte Components
```svelte
<script lang="ts">
  import { onMount } from "svelte";
  
  export let onSelect: (view: string) => void;
  export let data: Estudiante[] = [];
  
  let mounted = false;
  
  const handleSelect = (view: string) => {
    activeView = view;
  };
  
  onMount(() => {
    mounted = true;
  });
</script>

<main class="w-full min-h-screen">
  <!-- Template -->
</main>
```

### TypeScript
- Use interfaces for complex data (avoid `any`)
- Named exports preferred; default for main components
- Constants in UPPER_SNAKE_CASE
- Full typing on functions: `const fetchData = async (id: string): Promise<Data[]> => {...}`

### CSS & Tailwind
- Use CSS variables for themes: `class="bg-[rgb(var(--bg-primary))]"`
- Prefer Tailwind utility classes over custom CSS
- Use Svelte transitions (fade, fly, slide)

---

## Patterns

### State Management
- **Svelte stores**: Global state (theme, persisted data)
- **Props**: Parent-child communication
- **Local state**: Component-specific state

### Navigation
- Single-page with `activeView` variable
- Consistent `handleBack()` function
- Use fade/fly transitions

### Forms
- Use `bind:value` for inputs
- Validate in real-time with visual feedback
- Use SweetAlert2 for confirmations

### Error Handling
```typescript
import Swal from "sweetalert2";

try {
  const result = await fetch(url);
  if (!result.ok) throw new Error("Failed");
} catch (e) {
  Swal.fire({
    icon: "error",
    title: "Error",
    text: "Mensaje de error",
    confirmButtonColor: "#d33"
  });
}
```

### API
- All URLs in `constants.ts`
- Use async/await with try/catch
- Type API responses with interfaces

---

## Theming

CSS variables in `app.css`:
- `--bg-primary`, `--bg-secondary`
- `--text-primary`, `--text-secondary`
- `--card-bg`, `--card-border`
- `--accent-primary`, `--accent-secondary`

Usage:
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  Content
</div>
```

---

## Conventions

| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `Dashboard.svelte` |
| Functions/vars | camelCase | `handleClick` |
| CSS classes | kebab-case | `btn-primary` |
| Constants | UPPER_SNAKE_CASE | `API_BASE_URL` |
| Data attributes | kebab-case | `data-testid="student-list"` |

---

## Checklist

- [ ] TypeScript with full types (no `any`)
- [ ] CSS variables for theming
- [ ] Tailwind classes
- [ ] Error handling with SweetAlert2
- [ ] Responsive design
- [ ] `npm run check` passes
- [ ] Existing functionality intact

---

## Notes

1. Always use TypeScript - don't compromise typing
2. Follow existing component patterns
3. All components must support light/dim/dark themes
4. UI in Spanish
5. Mobile-first design
6. Ask before git commits

**Version**: 2.1  
**Updated**: Feb 2026
