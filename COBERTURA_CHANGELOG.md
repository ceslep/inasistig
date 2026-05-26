no esta funcionado, al seleccionar biblioteca el docente que estaba no se actualiza en los demas select donde se pueda ubicar y si esta el mismo docente en dos selects no da aviso de nada de que no se puede recuerda que un docente solo se puede una hora al dia verific eso y no ermitas que un docente esté mas de dos veces a no ser de que se apruebe manualmente

revisa esto, estamos trabajando en @src\components\Horarios.svelte y en "Gestionar Coberturas" y revisa esto: el dia lunes estan ausentes los docentes "DELCID DE JESUS BUENO ARANDIA" Y "JOSE DARIO OREJUELA SANCHEZ" al generar los cubrimientos automaticos en la hora de cubrimiento de "DELCID DE JESUS BUENO ARANDIA" en la hora 5 cambio al docente "ANDRES MAYORGA BOTERO" este entonces debe alimentar el select de docentes para la hora 5 de cubrimiento de "JOSE DARIO OREJUELA SANCHEZ" y no aparece para ser escogido, esto pasa el dia lunes, analiza si es un problema general e implementa una solución para esto y revisa mas casos asi

es que cuando el select de la hora 5 de delcid se que esta con andres se cambia por otro este deberia pasar automaticamente al select de doncetes que cubren a jose dario por que no se esta utilizando la hora 5 de andres para esto

al liberar la hora 5 de andres para cubrir a delcid ya esta diciendo que andres cubre una hora (limite de 1 hora) al cambiar a otro cubridor esta hora de andres ahi se liberar para el osea que ese dia a cubierto 0 horas para poder asignarlo a otro docente en la 5 hora en este caso la de jose dario, 

si hay docentes asignados automaticamente al recalcular no borrar los que aparecen ya asignados

en la tabla de asignaciones sugeridas cuando esta en blanco el docente a seleccionar esta calculando los limites y colocandolos, arregla eso, tambien haz que al darle click al nombre del docente ausente se pueda ver su horario semanal en una modal

cuando le doy a liberar grupos este liberado por ejemplo todo el dia sigue apareciendo en las opcione ssugeridas

no, sigue el problema hay docentes que aparecen mas de una vez el mismo dia en el ejemplo que te di del lunes a derly la permite varias veces entre otros errores ya avisados

# Historial de Cambios - Sistema de Coberturas

## Sesión: Correcciones de bugs en Gestionar Coberturas

**Fecha:** Mayo 2026
**Archivos modificados:** `src/lib/coberturaUtils.ts`, `src/components/horarios/CoberturasManager.svelte`, `src/components/horarios/AsignacionesView.svelte`, `src/lib/__tests__/coberturaUtils.test.ts`

---

## Problema Principal

Cuando se cambiaba el docente cubre de una cobertura (ej: DELCID hora 5 de ANDRES a PEPITO), el docente liberado (ANDRES) **no aparecía** en los `posiblesCobradores` de otras coberturas del mismo día, incluso cuando debería estar disponible.

### Escenario del bug:
1. Lunes: DELCID y JOSE DARIO ausentes, ambos hora 5
2. ANDRES cubre hora 5 de DELCID → consume 1h/día
3. Usuario cambia DELCID hora 5 de ANDRES → PEPITO
4. **Bug:** ANDRES no aparecía en `posiblesCobradores` de JOSE DARIO hora 5
5. **Bug adicional:** El docente podía aparecer **varias veces** en el select del mismo día

---

## Cambios Realizados

### 1. `src/lib/coberturaUtils.ts`

#### Funciones nuevas agregadas:

**`construirCargaDiariaSesion(coberturas, indiceExcluir, docenteNuevo)`**
- Construye el mapa de carga horaria de la sesión actual
- Recibe el índice a excluir y el docente nuevo para contar correctamente
- Excluye roles especiales (ORIENTADOR, COORDINADOR, BIBLIOTECA)

**`getPosiblesCobradoresParaSlot(slot, dia, horarios, slotsLibresPorAusencia, cargaDiariaSesion, horasCubiertasSemana, indiceAusencias, asignacionesSesion)`**
- Extraída la lógica de filtrado de cobradores desde `asignarAutomaticamente`
- Recibe `cargaDiariaSesion` y `asignacionesSesion` como parámetros para permitir recalcular basándose en el estado actual de la sesión
- Verifica: docente no ausente, hora libre, no ocupado en sesión, dentro de límites 1h/día y 2h/semana

---

### 2. `src/components/horarios/CoberturasManager.svelte`

#### Fix `cambiarDocenteCubre(index, docente)`:

**Antes (problemático):**
- Usaba `previosMap` para restaurar coberturas aprobadas
- Llamaba `asignarAutomaticamente` que no conocía el estado de la sesión actual
- `cargaDiariaSesion` se construía desde cero, sin saber que un docente fue liberado

**Después (corregido):**
- Construye `cargaDiariaSesion` desde el estado actual de la sesión
- Para cada cobertura distinta a la afectada, recalcula `posiblesCobradores` usando `getPosiblesCobradoresParaSlot`
- Si un docente ya aprobado no aparece en los nuevos posibles, lo preserva agregándolo al inicio
- Se agregaron guards `&& docenteAnterior !== ""` para manejar casos de valor vacío

**Código clave:**
```typescript
const cargaDiariaSesion = construirCargaDiariaSesion(coberturasSugeridas, index, docente);

for (let i = 0; i < coberturasSugeridas.length; i++) {
  if (i === index) continue;

  const posibles = getPosiblesCobradoresParaSlot(
    slotParaEsta,
    diaSeleccionado,
    horariosData,
    libresFiltrado,
    cargaDiariaSesion,
    horasCubiertasSemana,
    indiceAusencias,
    coberturasSugeridas  // <-- Pasa todas las asignaciones, no solo las demás filas
  ).map((c) => c.docente);

  // Preserva docente aprobado si no está en la nueva lista
  if (docenteCubreActual && cov.aprobada && !posibles.includes(docenteCubreActual)) {
    posibles.unshift(docenteCubreActual);
  }

  coberturasSugeridas[i] = { ...cov, posiblesCobradores: posibles };
}
```

#### Fix `toggleGrupoAusente(grupo, checked, horaInicio)`:

**Antes:** Cuando se deseleccionaba un grupo en el modal "Liberar Grupos", solo se removía de `gruposAusentes` pero seguía apareciendo en `gruposSugeridosAAusentar`.

**Después:**
```typescript
} else {
  gruposAusentes = gruposAusentes.filter((g) => g.grupo !== grupo);
  gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
}
```

---

### 3. `src/components/horarios/AsignacionesView.svelte`

#### Feature: Click en docente ausente para ver horario semanal

**Agregados:**
- Imports: `horariosData`, `Swal`, `HorarioDocente`
- Funciones auxiliares: `getClaseSlot()`, `formatearMateria()`, `abrirHorarioDocente()`
- El nombre del docente ausente ahora es un botón clickeable que abre un SweetAlert con la tabla del horario semanal del docente

**Código de la modal:**
```typescript
function abrirHorarioDocente(nombre: string) {
  const horario = horariosData.find((h) => h.docente === nombre);
  if (!horario) {
    Swal.fire("Error", `No se encontró el horario para ${nombre}`, "error");
    return;
  }
  // Genera tabla HTML con el horario semanal
  Swal.fire({
    title: `Horario de ${nombre}`,
    html: tableHtml,
    confirmButtonText: "Cerrar",
    width: "650px",
  });
}
```

**Template:**
```svelte
<td class="p-3 text-center border-t" style="border-color: rgb(var(--border-primary));">
  <button
    onclick={() => abrirHorarioDocente(cov.docenteAusente)}
    class="font-medium hover:underline cursor-pointer"
    style="color: rgb(var(--accent-primary));"
    title="Ver horario semanal"
  >
    {cov.docenteAusente}
  </button>
</td>
```

---

### 4. `src/lib/__tests__/coberturaUtils.test.ts`

#### Tests agregados:

**`construirCargaDiariaSesion`:**
- Cuenta 1 hora cuando un docente cubre una cobertura aprobada
- Excluye el slot que se está cambiando y cuenta el docente nuevo
- No cuenta horas para roles especiales

**`getPosiblesCobradoresParaSlot`:**
- Incluye docente disponible cuya hora está libre
- Excluye al docente ausente de posibles cobradores
- Excluye docente cuya hora ya está ocupada
- Bloquea docente que ya cubrió 1 hora en la sesión
- Mantiene disponible al docente freed cuando se excluye su asignación previa

**Test de escenario completo:**
```typescript
describe('escenario: dos docentes ausentes misma hora, liberar cubre al otro slot', () => {
  it('al liberar un docente que cubrió, debe estar disponible para otro slot en la misma hora', () => {
    // Dos docentes ausentes (Ana y Carlos) misma hora
    // María cubre la hora de Ana → consume 1h/día
    // Cuando se libera a María, debe aparecer en posiblesCobradores de Carlos
    // ...
  });
});
```

---

## Bugs Corregidos

| # | Bug | Causa | Fix |
|---|-----|-------|-----|
| 1 | ANDRES no aparecía en `posiblesCobradores` de JOSE DARIO después de cambiarlo | `asignarAutomaticamente` no conocía estado de sesión | Recalcular con `getPosiblesCobradoresParaSlot` usando `cargaDiariaSesion` real |
| 2 | Grupos liberados seguían en sugerencias | `toggleGrupoAusente` no filtraba `gruposSugeridosAAusentar` | Agregar filter al deseleccionar |
| 3 | Select vacío con límites incorrectos | `docenteAnterior` null/falsy no se manejaba | Agregar guards `&& docenteAnterior !== ""` |
| 4 | Click en docente ausente no hacía nada | No había handler | Crear `abrirHorarioDocente()` con SweetAlert |
| 5 | Mismo docente aparecía varias veces en select del mismo día | `asignacionesSesion` no incluía la fila cambiada | Pasar `coberturasSugeridas` completo en lugar de filtrado |

---

## Validación

```bash
npm run check     # 0 errors, 0 warnings
npm run test:unit # 31 tests passed
```

---

## Archivos Cambiados

```
src/
├── lib/
│   ├── coberturaUtils.ts           (+95 líneas: 2 funciones nuevas)
│   └── __tests__/
│       └── coberturaUtils.test.ts  (+200+ líneas: tests nuevos)
└── components/
    └── horarios/
        ├── CoberturasManager.svelte  (~40 líneas modificadas)
        └── AsignacionesView.svelte    (~50 líneas: feature + fixes)
```
