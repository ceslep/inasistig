# Auditoría Completa — Inasistig
**Fecha:** 2026-04-27 | **Líneas auditadas:** ~19,378 | **Componentes:** 30

---

## 1. Resumen Ejecutivo

Proyecto **producción-ready** con patrones profesionales de Svelte 5. Sin embargo, presenta **deuda técnica significativa** en 5 áreas:

| Área | Severidad | Impacto |
|------|-----------|---------|
| Código duplicado (~40-50%) | 🔴 Crítica | Mantenimiento 4x más lento |
| Migración Svelte 4→5 incompleta | 🔴 Crítica | Inconsistencia, bugs potenciales |
| Type safety (`any` ~30+ instancias) | 🟡 Alta | Errores en runtime no detectados |
| Accesibilidad (WCAG 2.1 AA) | 🟡 Alta | Exclusión de usuarios, incumplimiento |
| Arquitectura (componentes monolíticos) | 🟠 Media | Dificulta onboarding y cambios |

---

## 2. Hallazgos por Área

### 2.1 Duplicación de Código (CRÍTICA)

**~150+ líneas idénticas** entre report generators, **~40-50% estructura duplicada** entre módulos principales.

#### Patrones duplicados:
- **Carga dinámica de libs** (jsPDF, ExcelJS, autoTable) — mismo bloque en 4 archivos
- **`normalize()`** — idéntica en ReportGeneratorInas + ReportGeneratorInasView
- **`getDocenteNumber()`** — idéntica en ReportGeneratorInas + ReportGeneratorDiario
- **`formatDate*()`** — 3 implementaciones distintas para mismo propósito
- **Estado de filtros** (selectedDocente, selectedMateria, etc.) — repetido en 4+ archivos
- **Lógica de localStorage** — reinventada en cada componente principal
- **Estructura de formularios** — ClassPlannerForm y Piar comparten ~60% estructura
- **Exportación CSV** — InasistenciaFilter y AnotadorFilter con lógica casi idéntica
- **Feature alert** — lógica duplicada en Dashboard y componentes hijos

#### Archivos más afectados:
| Archivo | Líneas | Duplicación estimada |
|---------|--------|---------------------|
| ClassPlannerForm.svelte | 5,544 | ~30% con Piar |
| Piar.svelte | 2,477 | ~30% con ClassPlannerForm |
| InasistenciaFilter.svelte | 1,445 | ~30% con AnotadorFilter |
| ReportGenerator.svelte | 789 | ~50% con otros generators |
| ReportGeneratorDiario.svelte | 649 | ~50% con otros generators |
| ReportGeneratorInas.svelte | 542 | ~50% con otros generators |

---

### 2.2 Migración Svelte 4→5 Incompleta

**Estado mixto** — algunos componentes usan patrones modernos, otros legacy.

| Patrón Legacy | Patrón Svelte 5 | Archivos afectados |
|---------------|-----------------|-------------------|
| `export let` | `$props()` | InasistenciaFilter, AnotadorFilter, DiarioAnotacionOptions, FeaturePopup |
| `on:click` | `onclick` | InasistenciaForm, Anotador, Diario, ia.svelte, Inas2, filtros |
| `$:` reactivo | `$derived` / `$derived.by` | InasistenciaFilter (60+ instancias), AnotadorFilter, Anotador |
| `class:name` | `class` con objetos/arrays | Inas2 |
| `use:action` | `{@attach}` | Ninguno usa actions, pero oportunidad perdida |
| `<slot>` | `{#snippet}` + `{@render}` | Ya migrado correctamente |

**Archivos ya modernos:** Dashboard, ModuleHeader, Observador, Piar, Badge, Button, Card
**Archivos legacy:** InasistenciaFilter, AnotadorFilter, ia.svelte, FeaturePopup, Toast

---

### 2.3 Type Safety

**30+ instancias de `any`** distribuidas en:

| Categoría | Instancias | Archivos |
|-----------|-----------|----------|
| Dynamic imports (`let X: any = null`) | 12 | Todos los report generators, TomSelect, App.svelte |
| Snippet params (`obj: any, key: string`) | 4 | ClassPlannerForm, Piar |
| Unsafe casting (`as any`) | 8 | InasistenciaForm, InasistenciaFilter, ReportGeneratorDiario |
| REST params (`[key: string]: any`) | 2 | Button, AnalyticsModal |
| API responses sin tipar | 5+ | Report generators |

**Tipos faltantes notables:**
- No hay validación de respuestas API contra interfaces
- Componentes de App.svelte tipan lazy imports como `any`
- CSV export no escapa comillas (bug funcional derivado de falta de tipos)

---

### 2.4 Accesibilidad (WCAG 2.1 AA)

| Problema | Severidad | Componentes |
|----------|-----------|-------------|
| 100+ elementos interactivos sin `aria-label` | 🔴 Alta | Todos los módulos |
| Sin keyboard handlers en selectores custom | 🔴 Alta | TomSelect, filtros |
| Información solo por color (badges, estados) | 🟡 Media | InasistenciaForm, Anotador |
| FeaturePopup sin `role="dialog"`, ESC roto | 🟡 Media | FeaturePopup |
| Tablas sin `scope="col"`, `<thead>` | 🟡 Media | Filtros, reportes |
| Canvas de firma sin alternativa | 🟡 Media | Observador |
| Sin skip-to-content link | 🟠 Baja | App.svelte |
| Tooltip con colores hardcoded (no tema) | 🟠 Baja | Tooltip |

---

### 2.5 Arquitectura y Organización

#### Componentes monolíticos
- **ClassPlannerForm.svelte: 5,544 líneas** — formulario de 7 pasos, PDF, localStorage, validación, todo en 1 archivo
- **Piar.svelte: 2,477 líneas** — estructura similar
- **InasistenciaForm.svelte: 1,568 líneas**
- **InasistenciaFilter.svelte: 1,445 líneas**

#### Datos hardcoded inline
- Observador: CATEGORIAS (50+ opciones), TEXTOS_ASISTENTE (60+ templates), FUNDAMENTO_LEGAL — todo inline
- Anotador: categoryColors (30+ líneas), material-to-category mapping
- ClassPlannerForm: URL CDN hardcoded (html2pdf)
- Piar: misma URL CDN duplicada

#### Sin capa de servicio
- Componentes llaman `fetch` directamente
- Sin abstracción para manejo de errores HTTP
- Sin interceptors para auth tokens
- Offline queue existe pero cada componente decide cuándo usarla

#### Iconos — doble paquete instalado
- `lucide-svelte@0.577.0` (legacy) — usado en mayoría de componentes
- `@lucide/svelte@1.7.0` (actual) — usado en Dashboard, ModuleHeader, AnalyticsModal
- **Bundle innecesariamente grande** por doble dependencia

---

### 2.6 Seguridad

| Problema | Severidad | Ubicación |
|----------|-----------|-----------|
| `localStorage.getItem` sin try/catch en LoginScreen | 🟡 Media | LoginScreen.svelte |
| Input de nombre sin sanitización | 🟡 Media | LoginScreen.svelte |
| JWT decodificado con `atob()` sin validación de payload | 🟠 Baja | authStore.ts |
| Google Client ID expuesto (aceptable para client-side) | ℹ️ Info | constants.ts |
| CSV export sin escapar — potencial CSV injection | 🟡 Media | InasistenciaFilter, AnotadorFilter |

---

### 2.7 Fortalezas del Proyecto

- ✅ Sistema de temas excelente (3 temas, CSS custom properties, consistente)
- ✅ PWA bien implementada (Workbox, offline queue, version check)
- ✅ Code splitting efectivo (dynamic imports por ruta)
- ✅ ModuleHeader como patrón de componente reutilizable
- ✅ UI components (Badge, Button, Card) bien diseñados
- ✅ networkStore + offlineQueue — offline-first sólido
- ✅ Analytics service completo con batching y sendBeacon
- ✅ Responsive design consistente (mobile-first)

---

## 3. Roadmap de Mejora

### Fase A — Quick Wins (1-2 semanas) — Alto impacto, bajo esfuerzo

| # | Tarea | Impacto | Esfuerzo | Archivos |
|---|-------|---------|----------|----------|
| A1 | Unificar iconos: eliminar `lucide-svelte`, migrar todo a `@lucide/svelte` | Bundle size, consistencia | Bajo | ~15 archivos |
| A2 | Extraer utilidades compartidas a `src/lib/utils.ts`: `normalize()`, `formatDate()`, `getDocenteNumber()`, `loadPdfLibraries()`, `loadExcelLibraries()` | Eliminar ~150 líneas duplicadas | Bajo | 6 report/filter files |
| A3 | Mover datos hardcoded a `src/data/`: categorías, textos, fundamento legal, motivos | Legibilidad, mantenibilidad | Bajo | Observador, Anotador, InasistenciaForm |
| A4 | Mover URL CDN de html2pdf a `constants.ts` | Seguridad, mantenibilidad | Trivial | ClassPlannerForm, Piar |
| A5 | Fix CSV export: escapar comillas, agregar BOM UTF-8 | Bug funcional | Bajo | InasistenciaFilter, AnotadorFilter |
| A6 | Tipar dynamic imports en App.svelte (eliminar `any`) | Type safety | Trivial | App.svelte |

### Fase B — Migración Svelte 5 (2-3 semanas)

| # | Tarea | Impacto | Esfuerzo | Archivos |
|---|-------|---------|----------|----------|
| B1 | Migrar `export let` → `$props()` | Consistencia | Medio | InasistenciaFilter, AnotadorFilter, DiarioAnotacionOptions, FeaturePopup |
| B2 | Migrar `on:click` → `onclick` | Consistencia | Medio | ~10 archivos con legacy events |
| B3 | Migrar `$:` → `$derived` / `$derived.by` | Eliminar side effects, consistencia | Alto | InasistenciaFilter (60+ instancias), AnotadorFilter, Anotador |
| B4 | Migrar `class:name` → class con arrays/objetos | Consistencia | Bajo | Inas2 |
| B5 | Modernizar FeaturePopup: `$props()`, `role="dialog"`, keyboard support | A11y + consistencia | Medio | FeaturePopup |
| B6 | Modernizar TomSelect: tipado, `$effect` lifecycle | Type safety, modernización | Medio | TomSelect |

### Fase C — Arquitectura (3-4 semanas)

| # | Tarea | Impacto | Esfuerzo | Archivos |
|---|-------|---------|----------|----------|
| C1 | Crear capa de servicio `src/services/api.ts`: wrapper de fetch con error handling, auth headers, offline fallback | Eliminar fetch directo, centralizar errores | Alto | Nuevo + todos los componentes |
| C2 | Crear `ReportGeneratorBase` o composable compartido para generación PDF/Excel | Eliminar ~50% duplicación en generators | Alto | 4 report generators |
| C3 | Extraer sub-componentes de ClassPlannerForm (FormStep, StepNavigation, PlanPreview) | Mantenibilidad | Alto | ClassPlannerForm (5,544→~2,000 líneas) |
| C4 | Extraer sub-componentes de Piar | Mantenibilidad | Alto | Piar (2,477→~1,200 líneas) |
| C5 | Crear `src/lib/localStorageSync.ts` — helper reutilizable para persistencia | Eliminar duplicación en cada componente | Medio | Nuevo + 6 componentes |
| C6 | Crear componentes de formulario compartidos: FormField, SelectGroup, DateRangeSelector | Eliminar duplicación, consistencia | Alto | Nuevo + múltiples componentes |

### Fase D — Accesibilidad (2-3 semanas, paralela a B/C)

| # | Tarea | Impacto | Esfuerzo |
|---|-------|---------|----------|
| D1 | Agregar `aria-label` a todos los botones de ícono | WCAG 2.1 AA | Medio |
| D2 | Agregar keyboard handlers a selectores custom | WCAG 2.1 AA | Medio |
| D3 | Agregar texto alternativo a indicadores de color | WCAG 2.1 AA | Bajo |
| D4 | Agregar `role="dialog"` + focus trap a modales | WCAG 2.1 AA | Medio |
| D5 | Agregar `scope="col"` y `<thead>` a tablas | WCAG 2.1 AA | Bajo |
| D6 | Agregar skip-to-content link | WCAG 2.1 AA | Trivial |
| D7 | Fix Tooltip: usar CSS custom properties en vez de colores hardcoded | Temas | Trivial |

### Fase E — Calidad y DX (ongoing)

| # | Tarea | Impacto | Esfuerzo |
|---|-------|---------|----------|
| E1 | Eliminar todos los `any` restantes — crear tipos para dynamic imports | Type safety | Medio |
| E2 | Agregar ESLint + Prettier config | Consistencia de código | Bajo |
| E3 | Agregar validación de API responses (Zod o runtime checks) | Robustez | Medio |
| E4 | Sanitizar input en LoginScreen | Seguridad | Bajo |
| E5 | Eliminar `ia.svelte` (duplicado de `Inas2.svelte`, que es versión refactorizada) | Limpieza | Trivial |

---

## 4. Priorización por Impacto Visual vs Esfuerzo

```
IMPACTO ALTO
    │
    │  A1 (iconos)     C1 (API layer)     C3 (split ClassPlanner)
    │  A2 (utils)       C2 (report base)   C4 (split Piar)
    │  A5 (CSV fix)     B3 ($: → $derived)
    │  B5 (FeaturePopup)
    │
    │  A3 (datos ext)   B1 (export let)    D1-D4 (a11y)
    │  A4 (CDN URL)     B2 (on:click)
    │  A6 (App types)   B6 (TomSelect)
    │
    │  D7 (Tooltip)     E2 (ESLint)        E3 (validation)
    │  E5 (cleanup)     E4 (sanitize)
    │
IMPACTO BAJO ──────────────────────────────────────────── ESFUERZO ALTO
                BAJO          MEDIO           ALTO
```

**Recomendación de ejecución:** A1→A2→A5→A3→A4→A6 → B1→B2→B5 → C1→C2 → D1→D4

---

## 5. Métricas Objetivo

| Métrica | Actual | Objetivo |
|---------|--------|----------|
| Instancias de `any` | 30+ | 0 |
| Líneas duplicadas | ~500+ | <50 |
| Componente más grande | 5,544 líneas | <2,000 líneas |
| Archivos con legacy Svelte 4 | ~10 | 0 |
| Paquetes de iconos | 2 | 1 |
| Elementos sin aria-label | 100+ | 0 |
| Cobertura WCAG 2.1 AA | ~60% | 95%+ |
