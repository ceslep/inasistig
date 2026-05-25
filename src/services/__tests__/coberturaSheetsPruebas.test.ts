import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { coberturaSheetsPruebasService } from '../coberturaSheetsPruebasService';

const TEST_DATE = '2026-05-25';

describe('CoberturaSheetsPruebasService - Integración', () => {
  afterEach(async () => {
    await coberturaSheetsPruebasService.deleteCoberturasPorFecha(TEST_DATE);
  });

  describe('saveCobertura y getCoberturas', () => {
    it('debería guardar y recuperar una cobertura con motivo', async () => {
      const cobertura = {
        fecha: TEST_DATE,
        dia_semana: 'lunes',
        hora: 0,
        docente_ausente: 'Ana',
        grupo_ausente: '',
        docente_cubre: 'Carlos',
        grupo_a_cubrir: '101',
        estado: 'aprobado',
        motivo: 'MEDICO',
      };

      await coberturaSheetsPruebasService.saveCobertura(cobertura);

      const coberturas = await coberturaSheetsPruebasService.getCoberturas();
      const guardada = coberturas.find(c => c.fecha === TEST_DATE && c.hora === 0);

      expect(guardada).toBeDefined();
      expect(guardada?.docente_ausente).toBe('Ana');
      expect(guardada?.docente_cubre).toBe('Carlos');
      expect(guardada?.motivo).toBe('MEDICO');
    });

    it('debería guardar múltiples coberturas', async () => {
      const coberturas = [
        {
          fecha: TEST_DATE,
          dia_semana: 'lunes',
          hora: 0,
          docente_ausente: 'Ana',
          grupo_ausente: '',
          docente_cubre: 'Carlos',
          grupo_a_cubrir: '101',
          estado: 'aprobado',
          motivo: 'MEDICO',
        },
        {
          fecha: TEST_DATE,
          dia_semana: 'lunes',
          hora: 1,
          docente_ausente: 'Ana',
          grupo_ausente: '',
          docente_cubre: 'María',
          grupo_a_cubrir: '201',
          estado: 'aprobado',
          motivo: 'MEDICO',
        },
      ];

      await coberturaSheetsPruebasService.saveCobertura(coberturas[0]);
      await coberturaSheetsPruebasService.saveCobertura(coberturas[1]);

      const recuperadas = await coberturaSheetsPruebasService.getCoberturas();
      const delDia = recuperadas.filter(c => c.fecha === TEST_DATE);

      expect(delDia.length).toBeGreaterThanOrEqual(2);
    });
  });

  describe('deleteCoberturasPorFecha', () => {
    it('debería eliminar todas las coberturas de una fecha', async () => {
      const cobertura = {
        fecha: TEST_DATE,
        dia_semana: 'lunes',
        hora: 0,
        docente_ausente: 'Ana',
        grupo_ausente: '',
        docente_cubre: 'Carlos',
        grupo_a_cubrir: '101',
        estado: 'aprobado',
        motivo: 'FAMILIAR',
      };

      await coberturaSheetsPruebasService.saveCobertura(cobertura);
      await coberturaSheetsPruebasService.deleteCoberturasPorFecha(TEST_DATE);

      const coberturas = await coberturaSheetsPruebasService.getCoberturas();
      const delDia = coberturas.filter(c => c.fecha === TEST_DATE);

      expect(delDia.length).toBe(0);
    });
  });

  describe('validación de columna motivo', () => {
    beforeEach(async () => {
      await coberturaSheetsPruebasService.deleteCoberturasPorFecha(TEST_DATE);
    });

    it('debería persistir correctamente el campo motivo', async () => {
      const motivos = ['MEDICO', 'FAMILIAR', 'LICENCIA', 'CAPACITACION', 'FUERZA MAYOR'];

      for (let i = 0; i < motivos.length; i++) {
        const cobertura = {
          fecha: TEST_DATE,
          dia_semana: 'lunes',
          hora: i,
          docente_ausente: 'Ana',
          grupo_ausente: '',
          docente_cubre: 'Carlos',
          grupo_a_cubrir: '101',
          estado: 'aprobado',
          motivo: motivos[i],
        };
        await coberturaSheetsPruebasService.saveCobertura(cobertura);
      }

      const coberturas = await coberturaSheetsPruebasService.getCoberturas();
      const delDia = coberturas.filter(c => c.fecha === TEST_DATE);

      expect(delDia.length).toBe(motivos.length);
      motivos.forEach(motivo => {
        expect(delDia.some(c => c.motivo === motivo)).toBe(true);
      });
    });
  });
});
