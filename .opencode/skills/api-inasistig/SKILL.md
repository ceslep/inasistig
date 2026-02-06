---
name: api-inasistig
description: Lógica de servidor PHP y consultas MySQL para el sistema inasistig.
---

## Qué hago

- Creación de endpoints PHP que retornan JSON.
- Consultas SQL para la tabla `examenes`, `estudiantes` y `asistencias`.

## Reglas de Oro

- **Siempre** usar PDO con Prepared Statements.
- Validar que el `id_docente` esté presente en cada petición.
- Formatear las respuestas como: `{"status": "success", "data": [...]}`.
