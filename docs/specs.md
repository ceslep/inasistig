# Reglas del Proyecto: inasistig

## Perfil del Proyecto

- **Propósito:** Sistema de gestión de asistencia y seguimiento académico para docentes en Colombia.
- **Stack:** Svelte 5 (Runes), Tailwind CSS, PHP (Backend API), MySQL.
- **Normativa:** Alineado con los estándares del Ministerio de Educación Nacional de Colombia.

## Estándares de Código

1. **Svelte 5:** Prohibido usar sintaxis antigua. Solo usar Runes (`$state`, `$derived`, `$effect`, `$props`).
2. **Componentes:** Estructura basada en componentes reutilizables y modulares.
3. **Estilos:** Tailwind CSS puro. Priorizar utilidades sobre CSS personalizado.
4. **Backend:** El backend es PHP. Cada acción del frontend debe estar validada por un contrato de API (JSON).

## Flujo de Trabajo (GentleAI SDD)

Antes de escribir cualquier código, el agente DEBE:

1. **Analizar:** Leer el estado actual del archivo `especificaciones_inasistig.md`.
2. **Planear:** Proponer los cambios en lenguaje natural y esperar aprobación.
3. **Ejecutar:** Implementar el código siguiendo la arquitectura existente.
4. **Documentar:** Actualizar la memoria del proyecto (Engram) con cualquier decisión técnica tomada.

## Contexto de Negocio

- Considerar siempre que el usuario final es un docente.
- La interfaz debe ser extremadamente ligera y funcional para entornos escolares.
- El manejo de fechas y periodos académicos debe seguir el calendario escolar colombiano.
