# Plan: Restaurar archivos y agregar mejoras móviles específicas

## Problema
Se reemplazó completamente el código de los componentes en lugar de agregar solo mejoras móviles. Los archivos originales ya tenían diseño responsivo con `md:hidden` / `hidden md:block`.

## Archivos Modificados
- `src/components/horarios/CoberturasManager.svelte`
- `src/components/horarios/AnalisisView.svelte`
- `src/components/horarios/AsignacionesView.svelte`
- `src/components/horarios/HistorialCoberturas.svelte`

## Archivos Nuevos (útiles, mantener)
- `src/components/horarios/BottomSheet.svelte` - Modal móvil reutilizable
- `src/components/horarios/SwipeCarousel.svelte` - Carrusel táctil

## Plan de Restauración y Mejora

### Paso 1: Restaurar archivos originales
```bash
git checkout HEAD -- src/components/horarios/CoberturasManager.svelte
git checkout HEAD -- src/components/horarios/AnalisisView.svelte
git checkout HEAD -- src/components/horarios/AsignacionesView.svelte
git checkout HEAD -- src/components/horarios/HistorialCoberturas.svelte
```

### Paso 2: Crear componente móvil reutilizable
Mantener `BottomSheet.svelte` - es útil para modales en móvil.

### Paso 3: Aplicar mejoras móviles SOLO usando CSS/classes

**Para CoberturasManager.svelte:**
- Agregar `min-h-[48px]` a botones táctiles (Stepper buttons)
- Mejorar espaciado en modal de grupos con media query
- Solo ajustar padding y tamaños mínimos para móvil

**Para AnalisisView.svelte:**
- Mejorar cards de hora en vista móvil (ya existe `md:hidden` para tarjetas)
- Solo ajustar tamaños táctiles y espaciado

**Para AsignacionesView.svelte:**
- Mejorar toggle de aprobar (más grande para móvil)
- Ajustar selector de docente para vista móvil

**Para HistorialCoberturas.svelte:**
- Ya tiene diseño móvil con `md:hidden`
- Solo mejorar espaciado y tamaños táctiles

## Principio Clave
**SOLO CSS y clases** - No cambiar estructura HTML/JS, solo mejorar experiencia táctil con:
- `min-h-[48px]` para targets táctiles
- `p-3 sm:p-4` para padding responsive
- `gap-2 sm:gap-3` para espaciado responsive
- `text-sm sm:text-base` para tipografía responsive

## NO HACER
- ❌ Reemplazar tablas por cards (desktop funciona bien)
- ❌ Cambiar estructura de modales (solo mejorar tamaños)
- ❌ Modificar lógica de negocio
- ❌ Agregar nuevos componentes complejos sin necesidad