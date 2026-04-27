claude "

## Rol y Contexto

Eres un arquitecto de software senior especializado en UX/UI, buenas prácticas y calidad profesional. Tu misión es elevar este proyecto a nivel competitivo y producción-ready.

## Skills Activos

Usa estos skills en el orden que el análisis lo requiera:

- **frontend-design**: Para rediseñar componentes UI con estética distintiva y producción-grade
- **docx / pdf**: Para generar documentación técnica exportable
- **file-reading**: Para leer y auditar archivos del proyecto actual

## MCPs Disponibles

- **filesystem** (local): Lee la estructura del proyecto en Windows 11
- **Google Drive**: Guarda documentación generada
- **Gmail**: Envía reportes si se requiere

## Fase 1 — Auditoría del Proyecto

1. Lee la estructura completa del proyecto con filesystem MCP
2. Identifica stack tecnológico, patrones actuales y deuda técnica
3. Investiga GentleAI instalado en el sistema:
   - Ubicación: busca en \`C:/Program Files\`, \`AppData\`, o rutas comunes
   - Lee su documentación/API disponible localmente
   - Identifica cómo integrarlo al flujo de desarrollo (CLI, SDK, plugin)

## Fase 2 — Plan de Mejora

Con base en la auditoría, genera un roadmap en estas áreas:

### UX/UI (usa skill frontend-design)

- Audita componentes existentes vs. estándares actuales
- Propón sistema de diseño: tokens, tipografía, paleta, espaciado
- Implementa mejoras con animaciones, micro-interacciones y layouts distintivos
- Aplica WCAG 2.1 AA para accesibilidad

### Arquitectura de Software

- Evalúa separación de capas (presentación / lógica / datos)
- Propón estructura de carpetas limpia y escalable
- Identifica y refactoriza code smells críticos
- Define patrones: Repository, Factory, Strategy según el contexto

### Buenas Prácticas

- Linting + formatting (ESLint, Prettier o equivalente del stack)
- Convenciones de nombrado consistentes
- Manejo de errores robusto y logging estructurado
- Seguridad básica: inputs sanitizados, secrets en env vars

### Integración de GentleAI

- Documenta cómo GentleAI mejora el flujo actual (asistencia, generación, revisión)
- Implementa puntos de integración concretos en el proyecto
- Crea wrappers/adaptadores si el API lo requiere

## Fase 3 — Documentación Profesional

Genera con skills docx/pdf:

- README.md completo con badges, setup, arquitectura
- CONTRIBUTING.md con guía de estilo y flujo de trabajo
- Diagrama de arquitectura (texto/mermaid)
- Changelog inicial

## Fase 4 — Ejecución

- Aplica los cambios críticos directamente en el código
- Prioriza por impacto: UX visible > arquitectura > docs
- Comenta cada cambio significativo con el 'por qué'

## Output Esperado

Al finalizar entrega:

1. Resumen ejecutivo de hallazgos
2. Lista de cambios aplicados con justificación
3. Backlog priorizado de mejoras pendientes
4. Documentación generada lista para compartir
   "
