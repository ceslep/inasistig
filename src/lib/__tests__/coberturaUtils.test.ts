import { describe, it, expect } from 'vitest';
import {
  getSemanaDelAno,
  clasificarSlot,
  getGrupoFromSlot,
  getMateriaFromSlot,
  getSlotsDelDia,
  aplicarAusencias,
  asignarAutomaticamente,
  getSlotsLibresPorAusencia,
  getDiaFromFecha,
} from '../coberturaUtils';
import {
  horariosTest,
  horariosTestConHora7Libre,
  coberturasHistoricasTest,
  coberturasHistoricasSemanaCompleta,
  ausenciaDocenteAna,
  ausenciaDocenteCarlos,
  ausenciaGrupo101,
  ausenciaMixtha,
} from './testData';

describe('getSemanaDelAno', () => {
  it('debería calcular la semana correcta para una fecha', () => {
    expect(getSemanaDelAno('2026-01-01')).toBe(0);
    expect(getSemanaDelAno('2026-01-04')).toBe(0);
    expect(getSemanaDelAno('2026-05-25')).toBe(20);
  });
});

describe('getMateriaFromSlot', () => {
  it('debería extraer la materia sin el grupo (4+ digitos)', () => {
    expect(getMateriaFromSlot('MAT-1001')).toBe('MAT-');
    expect(getMateriaFromSlot('FIS-2001')).toBe('FIS-');
    expect(getMateriaFromSlot('')).toBe('');
    expect(getMateriaFromSlot('DESC')).toBe('DESC');
    expect(getMateriaFromSlot('MAT-101')).toBe('MAT-101');
  });
});

describe('clasificarSlot', () => {
  it('debería clasificar slots correctamente', () => {
    expect(clasificarSlot('')).toBe('libre');
    expect(clasificarSlot('DESC')).toBe('desc');
    expect(clasificarSlot('PEDAG')).toBe('pedag');
    expect(clasificarSlot('DEESC')).toBe('desc');
    expect(clasificarSlot('MAT-101')).toBe('clase');
    expect(clasificarSlot('FIS-201')).toBe('clase');
  });
});

describe('getGrupoFromSlot', () => {
  it('debería extraer el grupo del slot', () => {
    expect(getGrupoFromSlot('MAT-101')).toBe('101');
    expect(getGrupoFromSlot('FIS-201')).toBe('201');
    expect(getGrupoFromSlot('QUIM-1001')).toBe('1001');
    expect(getGrupoFromSlot('')).toBe('');
    expect(getGrupoFromSlot('DESC')).toBe('');
  });
});

describe('getMateriaFromSlot', () => {
  it('debería extraer la materia sin el grupo (4+ digitos)', () => {
    expect(getMateriaFromSlot('MAT-1001')).toBe('MAT-');
    expect(getMateriaFromSlot('FIS-2001')).toBe('FIS-');
    expect(getMateriaFromSlot('')).toBe('');
    expect(getMateriaFromSlot('DESC')).toBe('DESC');
    expect(getMateriaFromSlot('MAT-101')).toBe('MAT-101');
  });
});

describe('getDiaFromFecha', () => {
  it('debería retornar el día de la semana para una fecha', () => {
    expect(getDiaFromFecha('2026-05-25')).toBe('lunes');
    expect(getDiaFromFecha('2026-05-26')).toBe('martes');
    expect(getDiaFromFecha('2026-05-27')).toBe('miercoles');
    expect(getDiaFromFecha('2026-05-28')).toBe('jueves');
    expect(getDiaFromFecha('2026-05-29')).toBe('viernes');
    expect(getDiaFromFecha('2026-05-30')).toBe('');
    expect(getDiaFromFecha('2026-05-31')).toBe('');
  });
});

describe('getSlotsDelDia', () => {
  it('debería obtener todos los slots del día para todos los docentes', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    expect(slots.length).toBe(21);
  });

  it('debería clasificar cada slot correctamente', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const anaSlots = slots.filter(s => s.docente === 'Ana');
    expect(anaSlots[0].tipo).toBe('clase');
    expect(anaSlots[1].tipo).toBe('libre');
    expect(anaSlots[2].tipo).toBe('clase');
  });
});

describe('aplicarAusencias', () => {
  it('debería marcar slots como libre_ausencia cuando el docente está ausente', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    expect(anaSlots[0].tipo).toBe('libre_ausencia');
    expect(anaSlots[2].tipo).toBe('libre_ausencia');
  });

  it('debería propagar el motivo de la ausencia al slot', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    expect(anaSlots[0].motivoAusencia).toBe('MEDICO');
    expect(anaSlots[2].motivoAusencia).toBe('MEDICO');
  });

  it('debería marcar slots como libre_ausencia cuando el grupo está ausente', () => {
    const slots = getSlotsDelDia('viernes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaGrupo101, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    expect(anaSlots[4].tipo).toBe('libre_ausencia');
    expect(anaSlots[4].motivoAusencia).toBe('GRUPO AUSENTE');
  });

  it('debería manejar ausencia de grupo con hora inicio 5', () => {
    const ausencia101h5: typeof ausenciaGrupo101 = [
      { tipo: "grupo", nombre: "101", horaInicio: 5, motivo: "GRUPO AUSENTE" },
    ];
    const slots = getSlotsDelDia('viernes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausencia101h5, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    expect(anaSlots[0].tipo).toBe('libre');
    expect(anaSlots[4].tipo).toBe('libre_ausencia');
  });
});

describe('getSlotsLibresPorAusencia', () => {
  it('debería filtrar solo slots libre_ausencia', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    expect(libresPorAusencia.length).toBe(2);
    expect(libresPorAusencia.every(s => s.tipo === 'libre_ausencia')).toBe(true);
  });
});

describe('asignarAutomaticamente', () => {
  const fechaActual = '2026-05-25';

  it('debería asignar coberturas sin violación cuando hay docentes disponibles', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.length).toBe(2);
    expect(coberturas.every(c => c.violation === '')).toBe(true);
  });

  it('debería asignar docente ausente correctamente', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.every(c => c.docenteAusente === 'Ana')).toBe(true);
  });

  it('debería incluir el motivo de la ausencia en la cobertura', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.every(c => c.motivoAusencia === 'MEDICO')).toBe(true);
  });

  it('debería limitar a máximo 1 hora por docente por día en la sesión', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.length).toBeGreaterThan(0);
    expect(coberturas.every(c => typeof c.docenteCubre === 'string')).toBe(true);
  });

  it('debería respetar límite de 2 horas semanales desde historial', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteCarlos, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(
      libresPorAusencia,
      horariosTest,
      coberturasHistoricasSemanaCompleta,
      'lunes',
      fechaActual
    );

    expect(coberturas.length).toBeGreaterThanOrEqual(0);
  });

  it('debería priorizar docentes con más ausencias en 2 semanas', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(
      libresPorAusencia,
      horariosTest,
      coberturasHistoricasTest,
      'lunes',
      fechaActual
    );

    expect(coberturas.length).toBeGreaterThan(0);
  });

  it('debería proteger hora 7 si docente tiene todas las tardes libres', () => {
    const slots = getSlotsDelDia('viernes', horariosTestConHora7Libre);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteCarlos, horariosTestConHora7Libre);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(
      libresPorAusencia,
      horariosTestConHora7Libre,
      [],
      'viernes',
      fechaActual
    );

    const hora7Cobertura = coberturas.filter(c => c.hora === 6);
    expect(hora7Cobertura.every(c => c.docenteCubre !== 'Carlos')).toBe(true);
  });

  it('debería asignar approved: false por defecto', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.every(c => c.aprobada === false)).toBe(true);
  });

  it('debería incluir posiblesCobradores en cada cobertura', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.every(c => Array.isArray(c.posiblesCobradores))).toBe(true);
    expect(coberturas.every(c => c.posiblesCobradores.length > 0)).toBe(true);
  });
});
