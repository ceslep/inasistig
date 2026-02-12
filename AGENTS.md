# AGENTS.md - Guía para Agentes de Código

## 🚀 Comandos Esenciales

### Desarrollo
```bash
npm run dev          # Iniciar servidor de desarrollo (Vite)
npm run build        # Construir para producción
npm run preview      # Previsualizar el build de producción
npm run deploy       # Build y despliegue automático a GitHub Pages
```

### Verificación y Calidad
```bash
npm run check        # Verificación de tipos (svelte-check + tsc)
                    # No hay comandos de linting/testing configurados
```

### Testeo Individual
Este proyecto **no tiene configurado un framework de testeo**. Para pruebas:
- Crear archivos `.test.html` manuales para componentes específicos
- Usar el navegador para pruebas manuales (ej: `test-report-generator.html`)

---

## 🎯 Stack y Configuración

- **Frontend**: Svelte 5 + TypeScript + Vite 7.2.4
- **Estilos**: TailwindCSS 4.1.18 con CSS custom properties para temas
- **Build**: Vite con base path `/inasistig/` (GitHub Pages)
- **Transiciones**: Svelte transitions (fade, fly, slide)
- **Alertas**: SweetAlert2
- **Exportación**: ExcelJS, jsPDF, file-saver

---

## 📁 Estructura del Proyecto

```
src/
├── components/          # Componentes UI principales
│   ├── Dashboard.svelte         # Vista principal con navegación
│   ├── InasistenciaForm.svelte  # Formulario de registro diario
│   ├── Anotador.svelte          # Módulo de anotaciones
│   ├── Diario.svelte            # Diario de campo
│   ├── Loader.svelte            # Componente de carga
│   └── *Filter.svelte           # Componentes de filtrado
├── lib/                # Utilidades y stores
│   ├── themeStore.ts           # Gestión de temas (light/dim/dark)
│   └── Counter.svelte          # Componente utilitario
├── assets/             # Recursos estáticos (imágenes)
├── constants.ts        # URLs y constantes de la aplicación
├── app.css            # Estilos globales y CSS custom properties
├── App.svelte         # Componente raíz con routing
└── main.ts           # Punto de entrada
```

---

## 🎨 Estilo de Código

### Componentes Svelte
```svelte
<script lang="ts">
  // Imports al inicio
  import { onMount } from "svelte";
  import { writable } from "svelte/store";
  
  // Props con export
  export let onSelect: (view: string) => void;
  export let data: any[] = [];
  
  // Estado local
  let mounted = false;
  let activeView = "dashboard";
  
  // Funciones con camelCase
  const handleSelect = (view: string) => {
    activeView = view;
  };
  
  // Lifecycle
  onMount(() => {
    mounted = true;
  });
</script>

<main class="w-full min-h-screen">
  <!-- Template con Svelte syntax -->
</main>

<style>
  /* Estilos específicos del componente */
  /* Estilos globales van en :global() */
</style>
```

### TypeScript
- **Tipado estricto**: Usar interfaces para todos los datos complejos
- **Imports**: ES6 con path relativo (`./` para mismo directorio, `../` para padre)
- **Exportación**: Preferir named exports, default exports para componentes principales

```typescript
// Interfaces bien definidas
interface Estudiante {
  id: string;
  nombre: string;
  grado: string;
  grupo: string;
}

// Constants en UPPER_SNAKE_CASE
export const API_BASE_URL = "https://api.example.com";
export const MAX_ESTUDIANTES = 50;

// Funciones con tipado completo
const fetchEstudiantes = async (grado: string): Promise<Estudiante[]> => {
  const response = await fetch(`${API_BASE_URL}/estudiantes?grado=${grado}`);
  return response.json();
};
```

### CSS y Tailwind
- **CSS Custom Properties**: Usar `rgb(var(--variable-name))` para temas
- **Tailwind Classes**: Preferir clases utilitarias sobre CSS custom
- **Transiciones**: Usar clases de Svelte transitions con duración consistente

```svelte
<!-- Botones con Tailwind -->
<button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors duration-200">
  Enviar
</button>

<!-- Variables CSS para temas -->
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  Contenido
</div>
```

---

## 🔄 Patrones de Diseño

### Gestión de Estado
- **Svelte stores**: Para estado global (temas, datos persistentes)
- **Props**: Para comunicación padre-hijo
- **Event dispatch**: Para comunicación hijo-padre
- **Local state**: Para estado específico del componente

### Navegación
- **Single-page**: Control de vistas mediante variable `activeView`
- **Back navigation**: Función `handleBack()` consistente
- **Transiciones**: Usar `fade`, `fly` para cambios de vista

### Formularios y Validación
- **Binding**: `bind:value` para inputs
- **Validación**: En tiempo real con feedback visual
- **Submit**: Manejo con async/await y SweetAlert2 para confirmación

### API y Datos
- **Constants**: Todas las URLs en `constants.ts`
- **Fetch**: Usar async/await con manejo de errores
- **Tipado**: Interfaces TypeScript para respuestas API

---

## 🎨 Sistema de Temas

### Variables CSS Principales
```css
/* Definidas en app.css */
--bg-primary, --bg-secondary
--text-primary, --text-secondary
--card-bg, --card-border
--accent-primary, --accent-secondary
```

### Uso en Componentes
```typescript
import { theme, type Theme } from "$lib/themeStore";
```

```svelte
<div class="bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]">
  <!-- Contenido con tema aplicado -->
</div>
```

---

## 🚨 Convenciones de Nombres

### Archivos y Componentes
- **PascalCase**: Componentes Svelte (`Dashboard.svelte`, `InasistenciaForm.svelte`)
- **camelCase**: Funciones y variables (`handleClick`, `studentData`)
- **kebab-case**: CSS classes y attributes (`btn-primary`, `data-testid`)

### IDs y Data Attributes
- **data-testid**: Para testing manual (`<div data-testid="student-list">`)
- **IDs**: Descriptivos y únicos (`inasistencia-form`, `anotador-modal`)

---

## 🧪 Pruebas y Depuración

### Desarrollo Manual
- **Componentes**: Crear archivos `.test.html` para pruebas aisladas
- **API**: Usar devtools del navegador para inspeccionar fetch
- **Estado**: `console.log` para depuración de stores

### Errores Comunes
- **Base path**: Olvidar `/inasistig/` en URLs
- **TypeScript**: `any` solo como último recurso
- **Tema**: No aplicar variables CSS en componentes nuevos

---

## 📝 Notas para Agentes

1. **Siempre usar TypeScript** - No comprometer el tipado
2. **Mantener consistencia** - Seguir patrones existentes de componentes
3. **Testing manual** - Este proyecto carece de framework de testeo automatizado
4. **Tema obligatorio** - Todos los componentes nuevos deben soportar light/dim/dark
5. **Español como idioma** - UI y mensajes en español
6. **Google Sheets primero** - Datos persisten en hojas de cálculo
7. **Mobile-first** - Diseño responsive con Tailwind
8. **Sin commits directos** - Preguntar antes de hacer git commit

---

## 🔄 Checklist para Cambios

- [ ] Componente TypeScript con tipos completos
- [ ] Variables CSS aplicadas para temas
- [ ] Clases Tailwind consistentes
- [ ] Manejo de errores con SweetAlert2
- [ ] Responsive design verificado
- [ ] Prueba manual en móvil y desktop
- [ ] npm run check sin errores
- [ ] Funcionalidad existente intacta

---

**Creado para**: Agentes de código trabajando en Inasistig  
**Versión**: 2.0.4 PLATINUM  
**Actualizado**: Febrero 2026