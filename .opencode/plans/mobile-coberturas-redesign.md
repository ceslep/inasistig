# Plan: Rediseño Móvil - Cobertura de Horas (CoberturasManager)

## Problema Identificado
El módulo de gestión de coberturas es difícil de usar en dispositivos móviles (Android/iPhone) debido a:
- Tablas con muchas columnas que no caben en pantallas pequeñas
- Botones y elementos táctiles demasiado juntos
- Modales centrados que no aprovechan el espacio móvil
- Navegación entre steps poco intuitiva en táctil
- Falta de feedback visual claro en interacciones touch

## Componentes a Modificar
1. `CoberturasManager.svelte` - Step 1 (día y ausencias)
2. `AnalisisView.svelte` - Step 2 (análisis de horas libres)
3. `AsignacionesView.svelte` - Step 3 (asignaciones sugeridas)
4. `HistorialCoberturas.svelte` - Vista historial
5. `CoberturaTour.svelte` - Tour de ayuda
6. `WhatsAppReport.svelte` - Reporte WhatsApp
7. `CoberturasHelp.svelte` - Ayuda

## Nuevos Componentes a Crear
1. `BottomSheet.svelte` - Modal tipo sheet que se desliza desde abajo
2. `SwipeCarousel.svelte` - Contenedor swipeable para navegación entre steps
3. `TimelineItem.svelte` - Componente para línea temporal en historial
4. `MobileCard.svelte` - Card genérica para móvil

---

## Diseño Paso a Paso

### 1. BottomSheet.svelte (NUEVO)
Modal que se desliza desde abajo, ideal para móvil.

**Props:**
- `open: boolean` (bindable)
- `title: string`
- `onClose: () => void`
- `maxHeight: string` (default: "85vh")
- `showHandle: boolean` (default: true)

**Características:**
- Handle visual para indicar que es deslizable
- Drag to dismiss (deslizar hacia abajo para cerrar)
- Backdrop con blur
- Animación de entrada slide-up
- Tecla Escape para cerrar
- Click fuera para cerrar

**Estados:**
- Closed (no renderizado)
- Open (visible con animación)

---

### 2. SwipeCarousel.svelte (NUEVO)
Contenedor que permite swipe horizontal entre páginas/steps.

**Props:**
- `initialStep: number` (default: 0)
- `onStepChange: (step: number) => void`
- `children: Snippet[]`

**Características:**
- Swipe horizontal para cambiar de step
- Indicador de puntos (dots) debajo
- Deshabilitar swipe si hay formulario incompleto
- Animación de transición suave
- Soporte para gesture de swipe en beide directions

---

### 3. CoberturasManager.svelte (REDISEÑO)

#### Step 1: Día y Ausencias
**Cambios en layout móvil:**
- Selector de día: botones más grandes (min 48px height), 5 columnas en grid
- Selector de fecha: DatePicker ocupa ancho completo
- Lista de docentes: grid de 2 columnas, cards con checkbox grande (44x44px min)
- Badge de tipo ausencia junto al nombre del docente
- Modal de grupos: BottomSheet en móvil
- Modal de tipo ausencia: BottomSheet en móvil

**Nuevo layout móvil (Step 1):**
```
┌─────────────────────────────────┐
│ [handle]                        │
│ ─────────────────────────────── │
│ 📅 Selecciona el día            │
│ ┌─────┬─────┬─────┬─────┬─────┐ │
│ │ LUN │ MAR │ MIE │ JUE │ VIE │ │
│ └─────┴─────┴─────┴─────┴─────┘ │
│ [__________ Fecha ____________] │
│                                 │
│ 👥 Docentes ausentes            │
│ ┌──────────────┬──────────────┐ │
│ │ ☐ ANA SOFIA  │ ☐ CARLOS M.  │ │
│ │    🔴 INC   │    🟡 CAP    │ │
│ ├──────────────┼──────────────┤ │
│ │ ☐ PEDRO S.  │ ☐ MARÍA L.   │ │
│ │              │              │ │
│ └──────────────┴──────────────┘ │
│                                 │
│ 📤 Liberar grupos (3)          │
│ [button full width]             │
│                                 │
│ [Continuar →] (full width)     │
└─────────────────────────────────┘
```

#### Navegación entre Steps
- Contenedor SwipeCarousel para los 3 steps
- Dots indicadores en la parte inferior
- Swipe izquierda/derecha para cambiar
- Botón "Continuar" avanza al siguiente step
- Botón "Atrás" vuelve al step anterior

---

### 4. AnalisisView.svelte (REDISEÑO)

#### Layout móvil - Cards por hora
En lugar de tabla, mostrar cards apilados verticalmente:

```
┌─────────────────────────────────┐
│ 📊 Análisis - Lunes 15/ene     │
│ ─────────────────────────────── │
│ ⭐ 3 horas libres · 5 docentes  │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ HORA 1 - Libre por ausencia │ │
│ │ ─────────────────────────── │ │
│ │ 👨‍🏫 CARLOS M. ausente        │ │
│ │ 📚 MATEMÁTICAS - 601        │ │
│ │                            │ │
│ │ ✅ Tiene hora libre h3      │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ HORA 2 - Clase normal       │ │
│ │ ─────────────────────────── │ │
│ │ 👨‍🏫 ANA SOFIA              │ │
│ │ 📚 CIENCIAS - 602           │ │
│ └─────────────────────────────┘ │
│                                 │
│ [Volver]    [Generar →]        │
└─────────────────────────────────┘
```

**Cada card muestra:**
- Número de hora
- Tipo de slot (libre, clase, descanso)
- Docente ausente (si aplica)
- Grupo afectado (si aplica)
- Indicador visual de color (rojo = requiere cobertura)

**Collapse/Expand:**
- Cards colapsables para reducir scroll
- Solo mostrar detalles de horas con ausencia
- Botón "Ver todas las horas" para expandir

---

### 5. AsignacionesView.svelte (REDISEÑO)

#### Layout móvil - Cards de cobertura
Cada cobertura sugerida es un card expandible:

```
┌─────────────────────────────────┐
│ ✓ 3 coberturas · ⚠️ 1 violación │
│ ─────────────────────────────── │
│ [✓ Aprobar todo]  [+ Liberar]   │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ 🔴 HORA 1 - Carlos ausente  │ │
│ │ 📚 MATEMÁTICAS - 601       │ │
│ │ ─────────────────────────── │ │
│ │ CUBRE:                     │ │
│ │ [▼ PEDRO S. v]             │ │
│ │ ⚠️ Ya cubrió 1h hoy        │ │
│ │ ─────────────────────────── │ │
│ │ [✓ Aprobado] [🗑 Liberar]  │ │
│ └─────────────────────────────┘ │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ 🟡 HORA 2 - María ausente   │ │
│ │ 📚 ESPAÑOL - 502           │ │
│ │ ─────────────────────────── │ │
│ │ CUBRE:                     │ │
│ │ [▼ ANA SOFIA v]            │ │
│ │ ─────────────────────────── │ │
│ │ [ ] Pendiente    [🗑 Liberar]│ │
│ └─────────────────────────────┘ │
│                                 │
│ [Volver]    [Guardar 2 ✓]      │
└─────────────────────────────────┘
```

**Card de cobertura incluye:**
- Hora y docente ausente
- Materia y grupo
- Selector de docente que cubre (dropdown)
- Toggle aprobar/rechazar (switch grande, 60px width)
- Botón liberar grupo (opcional)
- Warnings de duplicidad
- Indicador de violación de reglas

**Selector de docente en móvil:**
- Al hacer tap, abre BottomSheet con lista de docentes
- Lista scrolleable con búsqueda
- Indicadores de disponibilidad y conflictos

---

### 6. HistorialCoberturas.svelte (REDISEÑO)

#### Timeline vertical
Agrupa coberturas por fecha en línea temporal:

```
┌─────────────────────────────────┐
│ 📜 Historial de Coberturas      │
│ ─────────────────────────────── │
│ [PDF] [WhatsApp] [🗑 Elim]     │
│                                 │
│ 📅 Filtros                      │
│ [Desde] [Hasta] [▼ Ausente]     │
│ [▼ Estado] [🔄] [📊 Stats]      │
│                                 │
│ ● ─────────────────────────────│
│ │ 📆 Lunes 15 enero 2026       │
│ │ ────────────────────────────│
│ │ 3 coberturas                 │
│ │ ────────────────────────────│
│ │ ☐ H1 · CARLOS → PEDRO        │
│ │   ✓ Aprobado · 601           │
│ │ ────────────────────────────│
│ │ ☐ H2 · MARÍA → ANA SOFIA     │
│ │   ✓ Aprobado · 502           │
│ │ ────────────────────────────│
│ │ ☐ H5 · LUIS → (sin cubrir)   │
│ │   ⚠️ Sin cubridor            │
│ └──────────────────────────────│
│                                 │
│ ● ─────────────────────────────│
│ │ 📆 Viernes 12 enero 2026     │
│ │ ────────────────────────────│
│ │ 2 coberturas                 │
│ │ ────────────────────────────│
│ │ ☐ H3 · PEDRO → CARLOS       │
│ │   ✓ Aprobado · 603           │
│ └──────────────────────────────│
└─────────────────────────────────┘
```

**Cada item de timeline:**
- Punto de anclaje (círculo)
- Línea conectora vertical
- Fecha como encabezado (colapsable)
- Cards de cobertura hijos (colapsables)
- Badge de estado (aprobado/rechazado/pendiente)
- Checkbox para selección múltiple
- Acciones: eliminar, ver detalle

**Filtros en móvil:**
- Panel colapsable "Filtros"
- Se expande al tocar
- Inputs full width
- Botón limpiar filtros visible

---

### 7. Componentes de Soporte

#### MobileCard.svelte (NUEVO)
Card genérica para móvil con:
- Header con título y badge opcional
- Body con contenido
- Footer con acciones
- Variantes de color (default, success, warning, error)
- Estados: default, selected, expanded, collapsed

#### TimelineItem.svelte (NUEVO)
Item para la línea temporal:
- Conector visual (línea + círculo)
- Timestamp/fecha
- Contenido tipo card
- Slots para child items

---

## Especificaciones de Diseño Móvil

### Touch Targets
- Minimum: 44x44px para todos los elementos interactivos
- Botones primarios: 48px height mínimo
- Checkboxes: 44x44px
- Toggle switches: 60x32px

### Espaciado
- Padding container: 1rem (16px)
- Gap entre elementos: 0.75rem (12px)
- Margin entre sections: 1.5rem (24px)

### Tipografía
- Títulos: 1.125rem (18px) bold
- Subtítulos: 1rem (16px) semibold
- Body: 0.875rem (14px)
- Caption: 0.75rem (12px)

### Animaciones
- Transiciones: 200ms ease-out
- Swipe cards: 300ms cubic-bezier
- Bottom sheet: 300ms ease-out
- Feedback táctil: scale(0.98) on press

### Iconografía
- Tamaño mínimo: 20px para iconos en botones
- Iconos de estado: 16px
- Iconos de materia: 24px

---

## Orden de Implementación

1. **BottomSheet.svelte** - Componente base reutilizable
2. **SwipeCarousel.svelte** - Contenedor de navegación
3. **CoberturasManager.svelte** - Rediseño Step 1 + integración SwipeCarousel
4. **AnalisisView.svelte** - Cards en lugar de tabla
5. **AsignacionesView.svelte** - Cards + selector BottomSheet
6. **HistorialCoberturas.svelte** - Timeline vertical
7. **Integración final** - Ajustes y testing

---

## Consideraciones de Accesibilidad

- Todos los elementos interactivos deben tener `aria-label`
- Roles ARIA apropiados para carousel, tabs, dialogs
- Contraste de colores mínimo 4.5:1
- Focus visible en todos los elementos
- Soporte para lectores de pantalla
- Navegación por teclado (Tab, Enter, Escape)
- Reduced motion para usuarios que lo prefieran