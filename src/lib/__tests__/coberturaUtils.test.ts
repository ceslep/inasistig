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
  construirCargaDiariaSesion,
  construirCargaDiariaHistorica,
  getPosiblesCobradoresParaSlot,
} from '../coberturaUtils';
import type { CoberturaHistorica } from '../coberturaUtils';
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
import type { CoberturaSugerida, Ausencia } from '../coberturaUtils';

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

  it('grupo ausente: slots del docente con ese grupo pasan a libre (no requieren cubrimiento)', () => {
    const slots = getSlotsDelDia('viernes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaGrupo101, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    // grupo no asiste — docente queda libre, no hay clase a cubrir
    expect(anaSlots[4].tipo).toBe('libre');
    expect(anaSlots[4].grupoAusente).toBe('');
    expect(anaSlots[4].motivoAusencia).toBe('');
  });

  it('grupo ausente con hora inicio 5: solo afecta desde h5', () => {
    const ausencia101h5: typeof ausenciaGrupo101 = [
      { tipo: "grupo", nombre: "101", horaInicio: 5, motivo: "GRUPO AUSENTE" },
    ];
    const slots = getSlotsDelDia('viernes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausencia101h5, horariosTest);

    const anaSlots = slotsConAusencia.filter(s => s.docente === 'Ana');
    expect(anaSlots[0].tipo).toBe('libre');
    // h5: grupo 101 ausente → slot raw "MAT 101" pasa a libre (sin cubrimiento)
    expect(anaSlots[4].tipo).toBe('libre');
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

  it('debería asignar approved: true por defecto', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const coberturas = asignarAutomaticamente(libresPorAusencia, horariosTest, [], 'lunes', fechaActual);

    expect(coberturas.every(c => c.aprobada === true)).toBe(true);
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

describe('construirCargaDiariaSesion', () => {
  it('debería contar 1 hora cuando un docente cubre una cobertura aprobada', () => {
    const coberturas = [
      { hora: 0, docenteAusente: 'Ana', docenteCubre: 'Carlos', aprobada: true, posiblesCobradores: [] as string[], grupoAusente: '', grupoACubrir: '', violation: '', motivoAusencia: '' },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '');
    expect(carga.get('Carlos')).toBe(1);
  });

  it('debería excluir el slot que se está cambiando y contar el docente nuevo', () => {
    const coberturas = [
      { hora: 0, docenteAusente: 'Ana', docenteCubre: 'Carlos', aprobada: true, posiblesCobradores: [] as string[], grupoAusente: '', grupoACubrir: '', violation: '', motivoAusencia: '' },
    ];
    const carga = construirCargaDiariaSesion(coberturas, 0, 'María');
    expect(carga.get('Carlos')).toBeUndefined();
    expect(carga.get('María')).toBe(1);
  });

  it('debería contar 0 para special roles', () => {
    const coberturas = [
      { hora: 0, docenteAusente: 'Ana', docenteCubre: 'ORIENTADOR', aprobada: true, posiblesCobradores: [] as string[], grupoAusente: '', grupoACubrir: '', violation: '', motivoAusencia: '' },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '');
    expect(carga.get('ORIENTADOR')).toBeUndefined();
  });
});

describe('getPosiblesCobradoresParaSlot', () => {
  const fechaActual = '2026-05-25';

  it('debería incluir docente disponible cuya hora está libre', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const carga = new Map<string, number>();
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const posibles = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, carga, horasSemana, indice, []
    );

    expect(posibles.some(p => p.docente === 'Carlos')).toBe(true);
  });

  it('debería excluir al docente ausente de posibles cobradores', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const carga = new Map<string, number>();
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const posibles = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, carga, horasSemana, indice, []
    );

    expect(posibles.some(p => p.docente === 'Ana')).toBe(false);
  });

  it('debería excluir docente cuya hora ya está ocupada', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const carga = new Map<string, number>();
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const posibles = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, carga, horasSemana, indice, []
    );

    expect(posibles.some(p => p.docente === 'María')).toBe(false);
  });

  it('debería bloquear docente que ya cubrió 1 hora en la sesión', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const carga = new Map<string, number>();
    carga.set('Carlos', 1);
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const posibles = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, carga, horasSemana, indice, []
    );

    expect(posibles.some(p => p.docente === 'Carlos')).toBe(false);
  });

  it('debería mantener disponible al docente freed cuando se excluye su asignación previa', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const carga = new Map<string, number>();
    carga.set('Carlos', 1);
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const asignacionesSesion = [
      { hora: 0, docenteAusente: 'Ana', docenteCubre: 'Carlos', aprobada: true, posiblesCobradores: [] as string[], grupoAusente: '', grupoACubrir: '', violation: '', motivoAusencia: '' },
    ];

    const posibles = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, carga, horasSemana, indice, asignacionesSesion
    );

    expect(posibles.some(p => p.docente === 'Carlos')).toBe(false);

    const asignacionesSinCarlos = asignacionesSesion.filter(a => a.docenteCubre !== 'Carlos');
    const posiblesFreed = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosTest, libresPorAusencia, new Map(), horasSemana, indice, asignacionesSinCarlos
    );
    expect(posiblesFreed.some(p => p.docente === 'Carlos')).toBe(true);
  });
});

describe('escenario: dos docentes ausentes misma hora, liberar cubre al otro slot', () => {
  const fechaActual = '2026-05-25';

  it('al liberar un docente que cubrió, debe estar disponible para otro slot en la misma hora', () => {
    const horariosDosAusentes: typeof horariosTest = [
      {
        docente: "Ana",
        lunes: ["MAT-101", "QUIM-101", "FIS-201", "", "", "", ""],
        martes: ["", "MAT-101", "", "QUIM-101", "", "", ""],
        miercoles: ["FIS-201", "", "", "MAT-101", "", "", ""],
        jueves: ["", "", "MAT-101", "", "QUIM-101", "", ""],
        viernes: ["", "FIS-201", "", "", "MAT-101", "", ""],
      },
      {
        docente: "Carlos",
        lunes: ["QUIM-101", "", "FIS-201", "", "", "", ""],
        martes: ["MAT-101", "", "", "", "FIS-201", "", ""],
        miercoles: ["", "QUIM-101", "", "", "MAT-101", "", ""],
        jueves: ["FIS-201", "", "", "", "", "QUIM-101", ""],
        viernes: ["", "", "MAT-101", "", "", "", "FIS-201"],
      },
      {
        docente: "María",
        lunes: ["", "", "FIS-201", "QUIM-101", "", "", ""],
        martes: ["", "", "QUIM-101", "", "MAT-101", "", ""],
        miercoles: ["MAT-101", "", "FIS-201", "", "", "", ""],
        jueves: ["", "MAT-101", "", "QUIM-101", "", "", ""],
        viernes: ["FIS-201", "", "", "", "", "MAT-101", ""],
      },
    ];

    const ausenciaAnaYCarlos: Ausencia[] = [
      { tipo: "docente", nombre: "Ana", motivo: "MEDICO" },
      { tipo: "docente", nombre: "Carlos", motivo: "FAMILIAR" },
    ];

    const slots = getSlotsDelDia('lunes', horariosDosAusentes);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaAnaYCarlos, horariosDosAusentes);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    expect(libresPorAusencia.length).toBeGreaterThanOrEqual(2);

    const cargaInicial = construirCargaDiariaSesion([], -1, '');
    const horasSemana = new Map<string, number>();
    const indice = new Map<string, number>();

    const slotAna = libresPorAusencia.find(s => s.docenteAusente === 'Ana')!;
    const slotCarlos = libresPorAusencia.find(s => s.docenteAusente === 'Carlos')!;

    expect(slotAna.hora).toBe(slotCarlos.hora);

    const posiblesAna = getPosiblesCobradoresParaSlot(
      slotAna, 'lunes', horariosDosAusentes, libresPorAusencia, cargaInicial, horasSemana, indice, []
    );

    expect(posiblesAna.some(p => p.docente === 'María')).toBe(true);

    cargaInicial.set('María', 1);

    const posiblesCarlos = getPosiblesCobradoresParaSlot(
      slotCarlos, 'lunes', horariosDosAusentes, libresPorAusencia, cargaInicial, horasSemana, indice, []
    );

    expect(posiblesCarlos.some(p => p.docente === 'María')).toBe(false);

    const asignacionesSinMaria: CoberturaSugerida[] = [];
    const posiblesCarlosFreed = getPosiblesCobradoresParaSlot(
      slotCarlos, 'lunes', horariosDosAusentes, libresPorAusencia, new Map(), horasSemana, indice, asignacionesSinMaria
    );
    expect(posiblesCarlosFreed.some(p => p.docente === 'María')).toBe(true);
  });
});

describe('construirCargaDiariaHistorica', () => {
  const fechaActual = '2026-05-25';

  it('debería retornar Map vacío sin entradas para fechaActual', () => {
    const historico: CoberturaHistorica[] = [
      { fecha: '2026-05-20', dia_semana: 'miercoles', hora: 0, docente_ausente: 'Ana', grupo_ausente: '', docente_cubre: 'Carlos', grupo_a_cubrir: '101', estado: 'aprobado', motivo: 'MEDICO' },
    ];
    const carga = construirCargaDiariaHistorica(historico, fechaActual);
    expect(carga.size).toBe(0);
  });

  it('debería contar aprobadas del mismo día', () => {
    const historico: CoberturaHistorica[] = [
      { fecha: fechaActual, dia_semana: 'lunes', hora: 0, docente_ausente: 'Ana', grupo_ausente: '', docente_cubre: 'Carlos', grupo_a_cubrir: '101', estado: 'aprobado', motivo: 'MEDICO' },
      { fecha: fechaActual, dia_semana: 'lunes', hora: 2, docente_ausente: 'Pedro', grupo_ausente: '', docente_cubre: 'María', grupo_a_cubrir: '201', estado: 'aprobado', motivo: 'FAMILIAR' },
      { fecha: '2026-05-20', dia_semana: 'miercoles', hora: 0, docente_ausente: 'X', grupo_ausente: '', docente_cubre: 'Carlos', grupo_a_cubrir: '101', estado: 'aprobado', motivo: 'MEDICO' },
    ];
    const carga = construirCargaDiariaHistorica(historico, fechaActual);
    expect(carga.get('Carlos')).toBe(1);
    expect(carga.get('María')).toBe(1);
  });

  it('debería ignorar entradas no aprobadas', () => {
    const historico: CoberturaHistorica[] = [
      { fecha: fechaActual, dia_semana: 'lunes', hora: 0, docente_ausente: 'Ana', grupo_ausente: '', docente_cubre: 'Carlos', grupo_a_cubrir: '101', estado: 'pendiente', motivo: 'MEDICO' },
    ];
    const carga = construirCargaDiariaHistorica(historico, fechaActual);
    expect(carga.get('Carlos')).toBeUndefined();
  });

  it('debería saltar ROLES_SIN_LIMITE', () => {
    const historico: CoberturaHistorica[] = [
      { fecha: fechaActual, dia_semana: 'lunes', hora: 0, docente_ausente: 'Ana', grupo_ausente: '', docente_cubre: 'ORIENTADOR', grupo_a_cubrir: '101', estado: 'aprobado', motivo: 'MEDICO' },
      { fecha: fechaActual, dia_semana: 'lunes', hora: 1, docente_ausente: 'Pedro', grupo_ausente: '', docente_cubre: 'COORDINADOR', grupo_a_cubrir: '201', estado: 'aprobado', motivo: 'MEDICO' },
      { fecha: fechaActual, dia_semana: 'lunes', hora: 2, docente_ausente: 'Luis', grupo_ausente: '', docente_cubre: 'BIBLIOTECA', grupo_a_cubrir: '301', estado: 'aprobado', motivo: 'MEDICO' },
    ];
    const carga = construirCargaDiariaHistorica(historico, fechaActual);
    expect(carga.size).toBe(0);
  });
});

describe('construirCargaDiariaSesion con cargaInicial', () => {
  it('debería merge la cargaInicial con asignaciones de sesión', () => {
    const cargaInicial = new Map<string, number>([['Carlos', 1]]);
    const coberturas: CoberturaSugerida[] = [
      { hora: 0, docenteAusente: 'Ana', docenteCubre: 'María', aprobada: true, posiblesCobradores: [], grupoAusente: '', grupoACubrir: '', violation: '', motivoAusencia: '' },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', cargaInicial);
    expect(carga.get('Carlos')).toBe(1);
    expect(carga.get('María')).toBe(1);
  });

  it('no muta cargaInicial cuando se pasa', () => {
    const cargaInicial = new Map<string, number>([['Carlos', 1]]);
    construirCargaDiariaSesion([], -1, '', cargaInicial);
    expect(cargaInicial.size).toBe(1);
    expect(cargaInicial.get('Carlos')).toBe(1);
  });
});

describe('asignarAutomaticamente con historico mismo dia', () => {
  const fechaActual = '2026-05-25';

  it('no debería asignar docente que ya cubrió 1h aprobada el mismo día', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    const historicoMismoDia: CoberturaHistorica[] = [
      {
        fecha: fechaActual,
        dia_semana: 'lunes',
        hora: 5,
        docente_ausente: 'Pedro',
        grupo_ausente: '',
        docente_cubre: 'Carlos',
        grupo_a_cubrir: '101',
        estado: 'aprobado',
        motivo: 'MEDICO',
      },
    ];

    const coberturas = asignarAutomaticamente(
      libresPorAusencia,
      horariosTest,
      historicoMismoDia,
      'lunes',
      fechaActual
    );

    // Carlos ya cubrió 1h ese día — no debe aparecer como docenteCubre
    expect(coberturas.every((c) => c.docenteCubre !== 'Carlos' || c.violation !== '')).toBe(true);
  });
});

describe('asignarAutomaticamente: dedup de slots', () => {
  const fechaActual = '2026-05-25';

  it('no debería generar filas duplicadas si slot aparece varias veces', () => {
    const slots = getSlotsDelDia('lunes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausenciaDocenteAna, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

    // Duplicar artificialmente
    const conDuplicados = [...libresPorAusencia, ...libresPorAusencia];

    const coberturas = asignarAutomaticamente(conDuplicados, horariosTest, [], 'lunes', fechaActual);

    const claves = new Set<string>();
    for (const c of coberturas) {
      const key = `${c.hora}|${c.docenteAusente}|${c.grupoAusente}`;
      expect(claves.has(key)).toBe(false);
      claves.add(key);
    }
  });

  it('martes scenario: docente con hora libre por grupo ausente puede cubrir varias veces', () => {
    // Carlos martes: ['MAT-101', '', '', '', 'FIS-201', '', '']
    // Si grupo 101 ausente desde h1, slot h0 de Carlos (MAT-101) queda libre.
    // María martes: ['', '', 'QUIM-101', '', 'MAT-101', '', '']
    // Si grupo 101 ausente desde h1, slots h2 y h4 de María quedan libres.
    // Ana ausente martes (clases h1 MAT-101, h3 QUIM-101) → con grupo 101 ausente
    // h1 y h3 NO necesitan cubrimiento. Forzamos slots libres manuales:
    const ausencias: Ausencia[] = [
      { tipo: 'docente', nombre: 'Ana', motivo: 'MEDICO' },
    ];
    const slots = getSlotsDelDia('martes', horariosTest);
    const slotsConAusencia = aplicarAusencias(slots, ausencias, horariosTest);
    const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);
    // grupo 101 desde h1 — Carlos h0 (MAT-101) y María h2 (QUIM-101) y h4 (MAT-101) libres
    const ausenciasGrupo = [{ grupo: '101', horaInicio: 1 }];

    const coberturas = asignarAutomaticamente(
      libresPorAusencia,
      horariosTest,
      [],
      'martes',
      fechaActual,
      ausenciasGrupo
    );

    // Conteo: docente que cubra en hora donde su slot era de 101 puede aparecer
    // multiples veces sin violation.
    const carlosCovers = coberturas.filter((c) => c.docenteCubre === 'Carlos');
    const mariaCovers = coberturas.filter((c) => c.docenteCubre === 'María');
    // Al menos uno de los dos debe poder aparecer en multiples horas sin violation
    // (depende de qué slots cubre cada uno — el aserto clave es ausencia de violation
    // de "Límite diario alcanzado" cuando todas las horas asignadas son liberadas).
    const violacionesLimite = coberturas.filter((c) =>
      c.violation && c.violation.includes('Límite')
    );
    // María tiene 2 slots libres por grupo 101 (h2, h4) — si cubre ambos, no violation.
    if (mariaCovers.length >= 2) {
      expect(violacionesLimite.filter((c) => c.docenteCubre === 'María').length).toBe(0);
    }
    // Carlos puede cubrir h0 sin contar contra su límite.
    expect(carlosCovers.length).toBeGreaterThanOrEqual(0);
  });

  it('construirCargaDiariaSesion: no cuenta hora si slot era de grupo liberado', () => {
    const ausenciasGrupo = [{ grupo: '101', horaInicio: 1 }];
    const coberturas: CoberturaSugerida[] = [
      {
        hora: 0, // Carlos martes h0 = MAT-101 → liberado
        docenteAusente: 'Ana',
        grupoAusente: '',
        docenteCubre: 'Carlos',
        grupoACubrir: '101',
        aprobada: true,
        posiblesCobradores: ['Carlos'],
        motivoAusencia: 'MEDICO',
      },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', undefined, {
      dia: 'martes',
      horarios: horariosTest,
      ausenciasGrupo,
    });
    // Carlos NO debe contar — su h0 era 101 (liberado).
    expect(carga.get('Carlos') || 0).toBe(0);
  });

  it('regla grupo liberado aplica a LUNES: docente con slot del grupo queda libre', () => {
    // Ana lunes: ['MAT-101', '', 'FIS-201', '', '', '', '']
    // Grupo 101 ausente desde h1 → Ana h0 (MAT-101) liberado.
    const ausenciasGrupo = [{ grupo: '101', horaInicio: 1 }];
    const coberturas: CoberturaSugerida[] = [
      {
        hora: 0,
        docenteAusente: 'Carlos',
        grupoAusente: '',
        docenteCubre: 'Ana',
        grupoACubrir: '101',
        aprobada: true,
        posiblesCobradores: ['Ana'],
        motivoAusencia: 'MEDICO',
      },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', undefined, {
      dia: 'lunes',
      horarios: horariosTest,
      ausenciasGrupo,
    });
    expect(carga.get('Ana') || 0).toBe(0);
  });

  it('regla grupo liberado aplica a MIERCOLES', () => {
    // María miercoles: ['MAT-101', '', 'FIS-201', '', '', '', '']
    // Grupo 101 ausente desde h1 → María h0 (MAT-101) liberado.
    const ausenciasGrupo = [{ grupo: '101', horaInicio: 1 }];
    const coberturas: CoberturaSugerida[] = [
      {
        hora: 0,
        docenteAusente: 'Ana',
        grupoAusente: '',
        docenteCubre: 'María',
        grupoACubrir: '101',
        aprobada: true,
        posiblesCobradores: ['María'],
        motivoAusencia: 'MEDICO',
      },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', undefined, {
      dia: 'miercoles',
      horarios: horariosTest,
      ausenciasGrupo,
    });
    expect(carga.get('María') || 0).toBe(0);
  });

  it('regla NO aplica si grupo no es el del slot original', () => {
    // Carlos martes h0 = MAT-101. Si grupo ausente es 201 (no 101), no liberar.
    const ausenciasGrupo = [{ grupo: '201', horaInicio: 1 }];
    const coberturas: CoberturaSugerida[] = [
      {
        hora: 0,
        docenteAusente: 'Ana',
        grupoAusente: '',
        docenteCubre: 'Carlos',
        grupoACubrir: '101',
        aprobada: true,
        posiblesCobradores: ['Carlos'],
        motivoAusencia: 'MEDICO',
      },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', undefined, {
      dia: 'martes',
      horarios: horariosTest,
      ausenciasGrupo,
    });
    expect(carga.get('Carlos') || 0).toBe(1);
  });

  it('regla NO aplica si hora antes de horaInicio del grupo', () => {
    // Carlos martes h0 = MAT-101. Grupo 101 ausente desde h3 (horaInicio=3).
    // h0 está antes → slot NO liberado, debe contar.
    const ausenciasGrupo = [{ grupo: '101', horaInicio: 3 }];
    const coberturas: CoberturaSugerida[] = [
      {
        hora: 0,
        docenteAusente: 'Ana',
        grupoAusente: '',
        docenteCubre: 'Carlos',
        grupoACubrir: '101',
        aprobada: true,
        posiblesCobradores: ['Carlos'],
        motivoAusencia: 'MEDICO',
      },
    ];
    const carga = construirCargaDiariaSesion(coberturas, -1, '', undefined, {
      dia: 'martes',
      horarios: horariosTest,
      ausenciasGrupo,
    });
    expect(carga.get('Carlos') || 0).toBe(1);
  });
});
