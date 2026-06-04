import { describe, it, expect } from "vitest";
import { resolverFechaDia, esFestivo, siguienteDiaHabil } from "../festivos";

// DIAS index: 0=lunes, 1=martes, 2=miercoles, 3=jueves, 4=viernes

describe("resolverFechaDia", () => {
  it("caso del usuario: hoy viernes 2026-06-05, click LUNES → festivo Corpus Christi → martes 2026-06-09", () => {
    // Lunes de la semana de hoy = 2026-06-01, ya pasó → rollover a 2026-06-08 (Corpus Christi, festivo).
    // Debe avisar (eraFestivo) y reubicar al siguiente día hábil: martes 2026-06-09.
    const r = resolverFechaDia("2026-06-05", 0);
    expect(r.eraFestivo).toBe(true);
    expect(r.nombreFestivo).toBe("Corpus Christi");
    expect(r.fecha).toBe("2026-06-09");
    expect(r.dia).toBe("martes");
  });

  it("hoy viernes 2026-06-05, click VIERNES → hoy mismo, no festivo", () => {
    const r = resolverFechaDia("2026-06-05", 4);
    expect(r.eraFestivo).toBe(false);
    expect(r.fecha).toBe("2026-06-05");
    expect(r.dia).toBe("viernes");
  });

  it("hoy jueves 2026-06-04, click VIERNES → 2026-06-05 (no salta a próxima semana)", () => {
    const r = resolverFechaDia("2026-06-04", 4);
    expect(r.eraFestivo).toBe(false);
    expect(r.fecha).toBe("2026-06-05");
    expect(r.dia).toBe("viernes");
  });

  it("hoy jueves 2026-06-04, click LUNES → lunes ya pasó → 2026-06-08 festivo → martes 2026-06-09", () => {
    const r = resolverFechaDia("2026-06-04", 0);
    expect(r.eraFestivo).toBe(true);
    expect(r.fecha).toBe("2026-06-09");
    expect(r.dia).toBe("martes");
  });

  it("hoy lunes 2026-06-01 (día hábil), click LUNES → hoy mismo", () => {
    const r = resolverFechaDia("2026-06-01", 0);
    expect(r.eraFestivo).toBe(false);
    expect(r.fecha).toBe("2026-06-01");
    expect(r.dia).toBe("lunes");
  });

  it("día no festivo futuro de esta semana se mantiene: hoy lunes 2026-06-01, click MIERCOLES → 2026-06-03", () => {
    const r = resolverFechaDia("2026-06-01", 2);
    expect(r.eraFestivo).toBe(false);
    expect(r.fecha).toBe("2026-06-03");
    expect(r.dia).toBe("miercoles");
  });

  it("festivo Jueves Santo 2026-04-02: hoy lunes 2026-03-30, click JUEVES → reubica a viernes... que es Viernes Santo 04-03 → lunes 04-06", () => {
    // 04-02 Jueves Santo y 04-03 Viernes Santo son festivos consecutivos.
    // siguienteDiaHabil debe saltar ambos y el fin de semana → lunes 2026-04-06.
    const r = resolverFechaDia("2026-03-30", 3);
    expect(r.eraFestivo).toBe(true);
    expect(r.nombreFestivo).toBe("Jueves Santo");
    expect(r.fecha).toBe("2026-04-06");
    expect(r.dia).toBe("lunes");
  });
});

describe("esFestivo", () => {
  it("detecta festivos conocidos", () => {
    expect(esFestivo("2026-06-08")).toBe(true); // Corpus Christi
    expect(esFestivo("2026-01-01")).toBe(true); // Año Nuevo
    expect(esFestivo("2026-06-09")).toBe(false);
  });
});

describe("siguienteDiaHabil", () => {
  it("salta fin de semana: viernes 2026-06-05 → lunes 2026-06-08 es festivo → martes 2026-06-09", () => {
    expect(siguienteDiaHabil("2026-06-05")).toBe("2026-06-09");
  });

  it("día normal: 2026-06-09 (martes) → 2026-06-10 (miercoles)", () => {
    expect(siguienteDiaHabil("2026-06-09")).toBe("2026-06-10");
  });
});
