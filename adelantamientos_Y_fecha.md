# Plan: Adelantamiento de Horas + Corrección de Selección de Fecha

## Problema 1: Adelantamiento Automático de Horas

### Contexto

Cuando se presiona el botón rojo "Liberar {grupo} (hora {X+1})" en Step 3 (AsignacionesView), los adelantos se aplican pero los slots que quedan libres NO se cubren automáticamente. La modal actual solo muestra adelantos aplicados/no aplicables, no los huecos que requieren cobertura.

### Ejemplo del caso 2026-06-02

```
Situación:
- Grupo 1001, Docente ausente: CESAR LEANDRO PATIÑO VELEZ
- Se adelanta hora 7 → hora 6 (o anterior disponible)

PROBLEMA: El slot hora 7 queda libre_ausencia y necesita docente manualmente.
```

---

## Problema 2: Selección de Fecha Incorrecta

### Contexto

`getFechaSemana()` no maneja días festivos colombianos, causando selección incorrecta de fechas.

### Reglas de Negocio

1. **Lunes a jueves**: seleccionar fecha de la semana actual (o próxima si ya pasó)
2. **Viernes**: por defecto seleccionar el lunes siguiente
3. **Colombia**: no seleccionar fechas que sean festivos (usar `src/lib/festivos.ts`)

---

## Archivos Creados/Modificados

### 1. `src/lib/festivos.ts` (NUEVO)

- Festivos Colombia 2026 con estructura `Festivo[]`
- Funciones `esFestivo()` y `siguienteDiaHabil()`

### 2. `src/lib/coberturaUtils.ts`

- Nueva función `getHuecosLibresPorAdelanto()`:
  - Recibe: grupo, horaLiberada, dia, adelantos, horariosBase
  - Retorna: `SlotInfo[]` con tipo `libre_ausencia` para cada horaOrigen de adelanto aplicable
  - Por cada adelanto aplicable donde `grupoGrado === grupo`, el slot `horaOrigen` queda libre → crear SlotInfo

### 3. `src/components/horarios/CoberturasManager.svelte`

**Imports agregados:**
- `getHuecosLibresPorAdelanto` de `coberturaUtils`
- `festivos`, `siguienteDiaHabil` de `festivos`

**Funciones modificadas:**

- `formatDate(d: Date): string` — helper para formateo de fechas
- `isFestivo(fecha: string): boolean` — valida si una fecha es festivo
- `getFechaSemana()` — ahora salta festivos automáticamente (busca siguiente día hábil)
- `seleccionarFecha(fecha)` — rechaza fechas festivas con mensaje Swal

**Tipo expandido `AdelantoModalData`:**
```typescript
{
  grupo: string;
  horaLiberada: number;
  docenteAusente: string;
  adelantos: Adelanto[];
  huecosPorAdelanto: SlotInfo[];  // NUEVO
}
```

**Función `liberarGrupoConAdelantos()` expandida:**
1. Calcular adelantos (existente)
2. Aplicar adelantos (existente)
3. **NUEVO**: `getHuecosLibresPorAdelanto()` para obtener slots libres
4. **NUEVO**: Para cada hueco, crear `CoberturaSugerida` automática usando `getPosiblesCobradoresParaSlot()`
5. **NUEVO**: Añadir a `coberturasSugeridas` si no existe
6. Liberar grupo (existente)
7. Guardar en `AdelantoModalData` con `huecosPorAdelanto`

**Modal `mostrarModalAdelantos` expandida:**
- Nueva sección al final del modal que muestra:
  - Título: "⚠️ Huecos libres por adelantamiento — requieren cobertura"
  - Lista de huecos con hora, docente ausente, y posibles cobradores

---

## Flujo Esperado

1. Usuario presiona botón rojo "Liberar {grupo} (hora {X+1})"
2. Sistema calcula adelantos y los aplica
3. **NUEVO**: Sistema identifica huecos libres por adelantamiento
4. **NUEVO**: Para cada hueco, busca docentes disponibles y crea cobertura automática
5. Modal muestra:
   - Adelantos aplicados (✓) y no aplicables (✕)
   - **NUEVO**: Huecos libres por adelantamiento con posibles docentes

---

## Validaciones Implementadas

1. **No duplicar coberturas**: Se verifica con `existe` antes de añadir
2. **Límites de cobertura**: `getPosiblesCobradoresParaSlot` respeta 1h/día, 2h/semana; roles sin límite sin límites
3. **Festivos**: `getFechaSemana()` salta festivos automáticamente; `seleccionarFecha()` rechaza festivo
4. **Slots `libre_ausencia`**: Mantenidos para que WhatsApp los muestre correctamente

---

## Testing Sugerido

1. Probar con 2026-06-02 (grupo 1001, CESAR LEANDRO PATIÑO VELEZ)
2. Verificar que coberturas automáticas aparecen en Step 3
3. Verificar selección de fecha en diferentes días de semana
4. Verificar que festivos (ej: 2026-04-02 Jueves Santo) no son seleccionables
5. Verificar que al seleccionar viernes, el sistema muestra fecha de lunes siguiente

---

## Guía de Uso: Gestión de Coberturas (Paso a Paso)

Esta guía está diseñada para usuarios que necesitan ayuda para crear la cobertura de un día. Sigue cada paso en orden.

---

### Antes de Empezar: ¿Qué es una cobertura?

Cuando un docente falta, su clase queda sin profesor. El sistema de **cobertura** busca qué otro docente puede dictar esa clase en su lugar.

**elementos clave:**
- **Docente ausente**: El profesor que falta
- **Grupo afectado**: La clase que se queda sin profesor (ej: 601, 1001)
- **Hora afectada**: Qué hora escolar queda sin profesor
- **Docente que cubre**: El profesor que替你 la clase

---

### Paso 1: Acceder al Módulo

1. Abre el menú principal de la aplicación
2. Busca y selecciona **"Horarios"**
3. Dentro de Horarios, busca el botón **"Gestionar Coberturas"**
4. Haz clic para entrar al módulo

**Deberías ver:** Una pantalla con 3 pasos numerados (1, 2, 3) y botones de días de la semana (LUN, MAR, MIE, JUE, VIE)

---

### Paso 2: Seleccionar el Día

#### 2.1 Seleccionar el día de la semana

1. Verás 5 botones: **LUN**, **MAR**, **MIE**, **JUE**, **VIE**
2. **Haz clic en el botón del día que necesitas** (ej: si es martes, clic en MAR)
3. El botón se pondrá de color accent (azul) para indicar que está seleccionado

#### 2.2 Seleccionar la fecha exacta

1. Justo debajo de los botones de días, hay un **selector de fecha**
2. El sistema muestra la fecha del día seleccionado automáticamente
3. **Si la fecha es incorrecta**, haz clic en el calendario y selecciona la fecha correcta
4. **Importante**: No puedes seleccionar fechas de fines de semana (sábado/domingo) ni festivos

**Consejo**: Si hoy es viernes y necesitas gestionar coberturas del lunes siguiente, el sistema lo hace automáticamente. Los días festivos también se saltan.

---

### Paso 3: Paso 1 — Registrar Quién Falta

En el **Step 1** verás dos secciones principales:

#### Sección A: Docentes Ausentes

1. **Busca el nombre del docente** que falta en la lista
2. **Haz clic en el nombre** para marcarlo como ausente
3. **Selecciona el tipo de ausencia** (ej: INCAPACIDAD, CAPACITACION, FAMILIAR)
4. El docente aparecerá en la lista de "Ausentes"

#### Sección B: Grupos Ausentes (si aplica)

1. Si hay algún grupo que no asiste (ej: todo el grado 601 en viaje), búscalo en la lista
2. **Selecciona desde qué hora** el grupo queda libre
   - Hora 1 = El grupo no asiste desde la primera hora
   - Hora 5 = El grupo se va después de la hora 4
3. Haz clic en el + para agregarlo

**Ejemplo práctico:**
> *"La profesora María López está enferma y no puede dar clases al grupo 601. El grupo 601也会 faltará porque no hay quien les enseñe."*
> - Paso 1: Seleccionar "MAR" (martes)
> - Paso 2: Buscar y seleccionar "María López" en Docentes Ausentes
> - Paso 3: En Grupos Ausentes, buscar "601" y agregarlo

---

### Paso 4: Paso 2 — Analizar Horas Libres

Una vez registrado quién falta, el sistema analiza las horas que quedan sin profesor.

1. Haz clic en el botón **"Analizar horas libres"**
2. El sistema mostrará:
   - **Horas libres por ausencia**: Clases que necesitan docente
   - **Quién puede cubrirlas**: Lista de profesores disponibles
   - **Sugerencias automáticas**: El sistema propone coberturas

**Qué estás viendo:**
- **Columnas de horas**: Las 7 horas del día escolar
- **Colores**:
  - 🟢 Verde: Clase con profesor asignado
  - 🔴 Rojo: Clase sin cubrir
  - 🟡 Amarillo: Sugerencia de cobertura

---

### Paso 5: Paso 3 — Revisar y Aprobar Coberturas

En el **Step 3** verás una tabla con todas las coberturas sugeridas:

#### 5.1 Revisar cada cobertura

Para cada fila (cobertura) verás:
- **Hora**: Qué hora escolar es
- **Ausente**: El docente que falta
- **Grupo**: Qué grupo se queda sin clase
- **Cubre**: Quién cubrirá (o vacío si no hay propuesta)
- **Estado**: Aprobada ✓, Pendiente, o Violación ⚠️

#### 5.2 Aprobar o modificar coberturas

1. **Para aprobar una cobertura**: Haz clic en el botón ✓ de la columna "Listo"
2. **Para cambiar el docente que cubre**:
   - Haz clic en el campo "Cubre"
   - Selecciona otro docente de la lista desplegable
3. **Para liberar un grupo con adelantamiento** (caso especial):
   - Busca el botón rojo: **"🗑️ Liberar {grupo} (hora {X})"**
   - Este se usa cuando el docente faltante puede adelantar su hora
   - **Aplica cuando**: El docente tiene clase en hora X y puede moverla a un hueco libre anterior
   - Al hacer clic, se abrirá una ventana mostrando qué adelantos se aplicaron

#### 5.3 Casos especiales de coberturas

**Coberturas con violación (⚠️):**
> Estas usan docentes que ya completaron su límite semanal (2 horas). Revisar si es necesario.

**Coberturas por grupo liberado:**
> Cuando un grupo completo falta (ej: 601), el sistema busca quién puede cubrir TODAS sus horas libres.

---

### Paso 6: Guardar las Coberturas

Cuando hayas revisado todo:

1. Haz clic en el botón **"Guardar {n} cobertura(s)"** (n = número de coberturas)
2. El sistema guardará en Google Sheets
3. Verás un mensaje de confirmación: **"Coberturas guardadas correctamente"**

---

### Funciones de Ayuda

#### Botón "Ver Todos" (LUN, MAR, etc.)
- Muestra la fecha completa del día seleccionado
- Útil para verificar que la fecha es la correcta

#### Botón "Ver Carga Laboral"
- Al hacer clic en el nombre de un docente, puedes ver:
  - Total de horas de clase
  - Horas de descanso
  - Coberturas ya asignadas esta semana
  - Límites (1 hora/día, 2 horas/semana)

#### Botón "Ver Historial"
- Accede al historial de coberturas guardadas
- Útil para consultar coberturas de días anteriores

#### Reporte WhatsApp/PDF
- Genera un reporte con imagen para enviar por WhatsApp
- O un PDF con firmas para documentación oficial

---

### Preguntas Frecuentes

**P: ¿Puedo seleccionar un festivo?**
R: No. El sistema automáticamente salta los festivos de Colombia y muestra un mensaje de error si intentas seleccionar uno.

**P: ¿Qué hago si no hay docentes disponibles para cubrir?**
R: En el Step 3, la cobertura aparecerá sin docente asignado. Puedes:
1. Buscar un docente con rol especial (ORIENTADOR, COORDINADOR, BIBLIOTECA) — ellos no tienen límite
2. Dejarlo pendiente para gestión manual posterior

**P: ¿Qué es el botón rojo "Liberar"?**
R: Se usa cuando un docente AUSENTE puede adelantar su hora. El sistema mueve la clase a un hueco libre anterior, liberando esa hora para cobertura.

**P: ¿Por qué aparecen coberturas con "violación"?**
R: Indica que el docente propuesto ya alcanzó su límite semanal (2 horas). Puedes aceptarlas en caso de emergencia o buscar alternativas.

**P: ¿Qué pasa si marco un docente ausente por error?**
R: En el Step 1, haz clic nuevamente en el docente para desmarcarlo. También puedes usar el botón "Limpiar" para reiniciar todo.

---

### Glosario Rápido

| Término | Significado |
|---------|-------------|
| **Cobertura** | Reasignación de una clase a otro docente |
| **Docente ausente** | Profesor que no puede dictar su clase |
| **Grupo afectado** | Curso que pierde la clase |
| **Adelanto** | Mover una clase a un horario anterior |
| **Liberar grupo** | Cuando un grupo no asiste, liberar sus horas |
| **Slot libre_ausencia** | Hora escolar sin clase por falta de docente |
| **Hora libre** | Hora del horario sin clase (no asignada) |

---

### Consejos de Uso Eficiente

1. **Siempre verificar la fecha** antes de empezar — especialmente si es lunes o viernes
2. **Revisar las violaciones** antes de guardar — no son bloqueantes pero deben ser intentionales
3. **Grupos ausentes primero** — si un grupo completo falta, marcarlo antes de asignar coberturas individuales
4. **Guardar al final del día** — el reporte de WhatsApp puede enviarse directamente desde el sistema
5. **Consultar el historial** si necesitas saber quién cubrió una clase específica en el pasado