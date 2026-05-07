# Implementación: Formulario de Acta de Reunión de Área

## Contexto

Este documento define las instrucciones para que una IA cliente implemente un **formulario para registrar la información del acta de reunión de área**, aplicable a instituciones de educación básica y media en Colombia, con base en los requisitos legales y de gestión educativa.

## Skills disponibles en el proyecto

El proyecto cuenta con los siguientes skills que deben usarse para esta implementación:

| Skill              | Propósito                                                                        |
| ------------------ | -------------------------------------------------------------------------------- |
| `form-builder`     | Creación de formularios reactivos con validación y campos dinámicos              |
| `legal-compliance` | Validación de campos requeridos por normativa colombiana (Ley 115, Decreto 1860) |
| `storage-service`  | Guardado local o en nube de actas en formato JSON/PDF                            |
| `pdf-generator`    | Exportación del acta a PDF con estructura legalmente válida                      |
| `ui-component`     | Generación de componentes accesibles (fechas, selects, textareas)                |
| `signature-pad`    | Firma digital o validación de participantes                                      |

## Estructura del formulario

### 1. Cabecera del acta

| Campo                      | Tipo                | Obligatorio | Skill a usar                       |
| -------------------------- | ------------------- | ----------- | ---------------------------------- |
| Institución educativa      | Texto               | Sí          | `form-builder`                     |
| Área académica (selección) | Select              | Sí          | `ui-component`                     |
| Asignaturas involucradas   | Multi-select        | Sí          | `form-builder`                     |
| Grados                     | Checkbox (6° a 11°) | Sí          | `ui-component`                     |
| Fecha de reunión           | Datepicker          | Sí          | `ui-component`, `legal-compliance` |
| Hora inicio / fin          | Time                | Sí          | `form-builder`                     |
| Lugar                      | Texto               | No          | `form-builder`                     |

### 2. Participantes

| Campo                                           | Tipo   | Obligatorio   |
| ----------------------------------------------- | ------ | ------------- |
| Docente (nombre completo)                       | Texto  | Sí            |
| Rol en el acta (coordinador/secretario/miembro) | Select | Sí            |
| Firma digital (opcional)                        | Firma  | No (sugerido) |

> ⚠️ **Skill `legal-compliance`**: Verificar que al menos una persona tenga rol "coordinador de área" o "secretario".

### 3. Orden del día

- Lista dinámica de temas (skill `form-builder` con array de objetos)
- Cada tema: descripción + responsable + tiempo estimado

### 4. Desarrollo de la reunión

- Por cada tema del orden del día:
  - Discusión (textarea)
  - Decisiones tomadas (textarea)
  - Votación (sí/no/abstención - si aplica)

### 5. Acuerdos y compromisos

| Campo                               | Tipo       |
| ----------------------------------- | ---------- |
| Actividad                           | Texto      |
| Responsable                         | Texto      |
| Fecha límite                        | Datepicker |
| Estado (pendiente/en curso/cerrado) | Select     |

### 6. Próxima reunión (opcional)

- Fecha, hora, lugar.

### 7. Cierre y validación legal

- Campo para lectura del acta (checkbox de confirmación)
- Generación de PDF con estructura:
  - Encabezado institucional
  - Texto: “El presente acta se ajusta a lo dispuesto en la Ley 115 de 1994 y el Decreto 1860 de 1994”
  - Línea de firma para: Coordinador de área, Secretario ad hoc

## Flujo de implementación sugerido

1. **Usar `form-builder`** para armar el formulario base.
2. **Aplicar `legal-compliance`**:
   - Validar que exista fecha, orden del día y al menos un acuerdo.
   - Verificar que el coordinador o secretario esté presente en la lista.
   - Si el acta incluye decisiones sobre evaluación de estudiantes, agregar advertencia sobre registro en SIEE.
3. **Conectar `storage-service`** para guardado automático cada 30 segundos (borrador).
4. **Al finalizar, invocar `pdf-generator`** para generar el acta.
5. **Opcional: usar `signature-pad`** para firmas digitales (no obligatorio legalmente, pero útil para auditoría).

## Restricciones técnicas

- No se requiere backend en esta fase — usar almacenamiento local (IndexedDB o localStorage).
- El PDF generado debe ser descargable y firmable en físico luego.
- La IA deberá priorizar accesibilidad: etiquetas claras, manejo de teclado.

## Criterios de aceptación

- [ ] El formulario contiene todos los campos señalados.
- [ ] Las validaciones legales impiden enviar actas sin coordinador o fecha.
- [ ] Se puede exportar a PDF con el formato legal requerido.
- [ ] Los acuerdos se pueden editar después de guardar.

## Ejemplo de prompt para la IA

> “Usando los skills `form-builder`, `legal-compliance` y `pdf-generator`, implementa el formulario de acta descrito en este md. Genera el HTML/CSS/JS necesario. Prioriza que los campos de fecha, hora y área sean funcionales y que el PDF resultante tenga la estructura legal colombiana.”
