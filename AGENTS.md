# AGENTS.md - Inasistig Code Guidelines

## Commands

```bash
npm run dev        # Dev server (Vite) - http://localhost:5173
npm run build      # Production build to dist/
npm run preview    # Preview production build locally
npm run deploy     # Build + deploy to GitHub Pages
npm run check      # Type checking (svelte-check + tsc)
```

**Testing**: No test framework configured. Manual testing via `test.html` files in root.

---

## Stack

- **Framework**: Svelte 5 + TypeScript + Vite
- **Styling**: TailwindCSS 4.x with CSS custom properties for theming
- **Alerts**: SweetAlert2 for modals/confirmations
- **Exports**: ExcelJS for Excel, jsPDF for PDF generation
- **Deployment**: GitHub Pages (base path: `/inasistig/`)

---

## Project Structure

```
src/
├── api/                 # API service layer (Google Sheets)
├── components/         # Svelte components
├── lib/                 # Stores (themeStore.ts)
├── assets/              # Static assets
├── constants.ts         # App constants (URLs, config)
├── app.css             # Global styles + CSS variables
└── App.svelte         # Root component
```

---

## Code Style

### Import Organization
Order imports by category (separate with blank lines):
1. Node built-ins 2. External libraries 3. Internal services/api 4. Internal constants
5. Internal stores 6. Internal components

```typescript
import { onMount } from "svelte";
import Swal from "sweetalert2";
import { saveInasistencias } from "../../api/service";
import { SPREADSHEET_ID } from "../constants";
import { theme } from "../lib/themeStore";
import Dashboard from "./Dashboard.svelte";
```

### TypeScript Guidelines
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

### State Management
- **Svelte stores**: Global state (theme) in `lib/`
- **Props**: Parent-child via `export let`
- **Local state**: Component-specific with `let`

### Reactive (Svelte 5)
```typescript
$: filteredItems = items.filter(i => i.active);
let count = $state(0);
let doubled = $derived(count * 2);
```

### Navigation
- Single-page app with `activeView` state
- Consistent `handleBack()` returning to "dashboard"
- Use Svelte transitions: `fade`, `fly`, `slide`

### Forms
- Use `bind:value` for inputs
- Validate in real-time with visual feedback
- Use SweetAlert2 for confirmations/errors
- Handle loading states with `isLoading` flags

### Error Handling
```typescript
import Swal from "sweetalert2";
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

### API
- All URLs in `constants.ts` - never hardcode
- Use async/await with try/catch
- Always type API responses with interfaces

---

## Theming

CSS variables in `app.css`: `--bg-primary`, `--bg-secondary`, `--text-primary`, `--text-secondary`, `--card-bg`, `--card-border`, `--accent-primary`, `--border-primary`

Usage:
```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  Content
</div>

$: styles = { bg: "rgb(var(--bg-primary))", text: "rgb(var(--text-primary))" };
```

---

## CSS & Tailwind

- Use CSS variables for theme-aware styling
- Prefer Tailwind utility classes
- Use `transition-colors duration-200` for smooth transitions
- Mobile-first: `class="w-full sm:w-auto"`

---

## Checklist

- [ ] TypeScript with full types (no `any`)
- [ ] CSS variables for theming
- [ ] Tailwind utility classes
- [ ] Error handling with SweetAlert2
- [ ] Loading states for async
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

**Version**: 2.3  
**Updated**: Feb 2026
