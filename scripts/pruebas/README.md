# Scripts de Prueba - Coberturas

Scripts standalone para verificar y analizar la lógica de asignación de coberturas usando la hoja "pruebas" del spreadsheet.

## Prerequisites

- Node.js 18+
- Acceso a internet (para consultar Google Sheets)

## Uso

### 1. Generar datos de prueba

Genera registros aleatorios en la hoja "pruebas".

```bash
node scripts/pruebas/generate_pruebas.js --count=200
```

- `--count=N`: Número de registros a generar (default: 200)
- Usa 15 docentes y 11 grupos de `horarios.json`
- Fechas aleatorias en los últimos 60 días

### 2. Verificar asignación de coberturas

Simula la asignación de coberturas para un día específico y verifica los límites.

```bash
node scripts/pruebas/verify_assignment.js --dia=lunes --fecha=2026-06-08
```

- `--dia`: Día de la semana (lunes, martes, miercoles, jueves, viernes)
- `--fecha`: Fecha en formato YYYY-MM-DD

**Salida incluye:**
- Slots que necesitan cobertura
- Posibles cobradores ordenados por disponibilidad
- Verificación de límites 1h/día y 2h/semana
- Resumen de asignaciones

### 3. Analizar docente específico

Muestra el análisis completo de un docente.

```bash
node scripts/pruebas/analyze_cobertura.js --docente="ANA SOFIA CARDENAS PETUMA"
```

- `--docente`: Nombre completo del docente (usar comillas)

**Salida incluye:**
- Horas clase y horas libres del docente
- Coberturas hechas esta semana y semana pasada
- Límites aplicados (1h/día, 2h/semana)
- Slots disponibles para cubrir
- Estado: puede o no puede cubrir

### 4. Limpiar datos de prueba

Elimina registros de la hoja "pruebas".

```bash
# Ver qué se eliminaría (sin confirmar)
node scripts/pruebas/cleanup_pruebas.js

# Eliminar todos los registros (confirmado)
node scripts/pruebas/cleanup_pruebas.js --confirm=true

# Eliminar solo una fecha específica
node scripts/pruebas/cleanup_pruebas.js --fecha=2026-06-08 --confirm=true
```

## Estructura de Datos

### Docentes disponibles (15)

```
1. ANA SOFIA CARDENAS PETUMA
2. ANDRES MAYORGA BOTERO
3. CARLOS ALBERTO AGUIRRE GONZALEZ
4. CARLOS ARMANDO RIOS ALVAREZ
5. CESAR LEANDRO PATINO VELEZ
6. BLANCA NELLY MARIN GIRALDO
7. DANIEL QUICENO RIVERA
8. DELCID DE JESUS BUENO ARANDIA
9. DERLY JOANNA PUERTAS
10. ILDORY JARAMILLO
11. JAVIER DE JESUS AGUDELO CARVAJAL
12. JON JAMES VASCO RODRIGUEZ
13. JOHN EDWIN ARBOLEDA ACEVEDO
14. JHON JAIRO VELEZ TEJADA
15. JOSE DARIO OREJUELA SANCHEZ
```

### Grupos (11)

```
601, 602, 701, 702, 801, 802, 901, 902, 1001, 1101, 1102
```

## Reglas de Asignación

| Regla | Límite |
|-------|--------|
| Máximo horas/día | 1 hora |
| Máximo horas/semana | 2 horas |
| Roles sin límite | ORIENTADOR, COORDINADOR, BIBLIOTECA, AUDITORIO |
| Hora 6 especial | Excluye si libre todos los días |
| Grupo ausente | Slot del docente queda "libre" |

## Ejemplo de Flujo

```bash
# 1. Generar datos de prueba
node scripts/pruebas/generate_pruebas.js --count=200

# 2. Verificar asignación para un día
node scripts/pruebas/verify_assignment.js --dia=lunes --fecha=2026-06-08

# 3. Analizar un docente específico
node scripts/pruebas/analyze_cobertura.js --docente="ANA SOFIA CARDENAS PETUMA"

# 4. Si necesitas resetear
node scripts/pruebas/cleanup_pruebas.js --confirm=true
```

## Notas

- Los scripts usan la hoja "pruebas", no "historial"
- La lógica de asignación replica exactamente la del sistema productivo
- Los scripts son read-only para verificación (no modifican la lógica de negocio)
- `generate_pruebas.js` y `cleanup_pruebas.js` sí modifican datos en Sheets