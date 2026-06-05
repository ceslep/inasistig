<script lang="ts">
  import { onMount } from "svelte";
  import horariosData from "../../lib/horarios.json";
  import {
    DIAS,
    getDocentesList,
    getGruposDisponibles,
    getDiaFromFecha,
    getSlotsDelDia,
    aplicarAusencias,
    asignarAutomaticamente,
    getSlotsLibresPorAusencia,
    formatoDia,
    ROLES_SIN_LIMITE,
    getPosiblesCobradoresParaSlot,
    construirCargaDiariaSesion,
    construirCargaDiariaHistorica,
    getSemanaDelAno,
    calcularAdelantos,
    aplicarAdelantosAHorarios,
    getHuecosLibresPorAdelanto,
  } from "../../lib/coberturaUtils";
  import { festivos, siguienteDiaHabil, resolverFechaDia } from "../../lib/festivos";
  import { coberturaSheetsService } from "../../services/coberturaSheetsService";
  import type { SlotInfo, CoberturaSugerida, CoberturaHistorica, Ausencia, SugerenciaGrupo, HorarioDocente, Adelanto } from "../../lib/coberturaUtils";
  import { analizarGruposAAusentar } from "../../lib/coberturaUtils";
  import AnalisisView from "./AnalisisView.svelte";
  import AsignacionesView from "./AsignacionesView.svelte";
  import HistorialCoberturas from "./HistorialCoberturas.svelte";
  import WhatsAppReport from "./WhatsAppReport.svelte";
  import CoberturasHelp from "./CoberturasHelp.svelte";
  import Swal from "sweetalert2";
  import ModuleHeader from "../ModuleHeader.svelte";
  import { Flame, GraduationCap, Car, Heart, Shield, Stethoscope, Briefcase, Calendar, Users, Scale, Skull, Laptop, Award, SportShoe, HelpCircle, PlayCircle, CheckSquare, Save, Eye, UsersRound } from "@lucide/svelte";
  import DatePicker from "../anotador/DatePicker.svelte";
  import CoberturaTour from "./CoberturaTour.svelte";
  import type { TourPaso } from "./CoberturaTour.svelte";

  const TIPOS_ICONOS: Record<string, { icono: any; color: string }> = {
    "CALAMIDAD": { icono: Flame, color: "#f97316" },
    "CAPACITACION": { icono: GraduationCap, color: "#8b5cf6" },
    "DESPLAZAMIENTO PEDAGOGICO": { icono: Car, color: "#06b6d4" },
    "FAMILIAR": { icono: Heart, color: "#ec4899" },
    "FUERZA MAYOR": { icono: Shield, color: "#6366f1" },
    "INCAPACIDAD": { icono: Stethoscope, color: "#ef4444" },
    "INTERCOLEGIADOS": { icono: SportShoe, color: "#ef4466" },
    "JURADO": { icono: Scale, color: "#a16207" },
    "LICENCIA": { icono: Award, color: "#eab308" },
    "LUTO": { icono: Skull, color: "#1f2937" },
    "MEDICO": { icono: Stethoscope, color: "#10b981" },
    "PERSONAL": { icono: Briefcase, color: "#f59e0b" },
    "QUINQUENIO": { icono: Calendar, color: "#3b82f6" },
    "REUNION": { icono: Users, color: "#14b8a6" },
    "SECRETARIA": { icono: Laptop, color: "#0ea5e9" },
    "SINDICATO": { icono: Scale, color: "#78716c" },
  };

  let { onBack }: { onBack: () => void } = $props();

  type SubView = "cobertura" | "historial";
  let subView = $state<SubView>("cobertura");
  let step = $state(1);
  let loading = $state(false);

  let docentes = $state(getDocentesList(horariosData));
  let grupos = $state(getGruposDisponibles(horariosData));

  let mostrarTourCobertura = $state(false);
  const TOUR_KEY = "cobertura_tour_done";

  function shouldShowTour(): boolean {
    try { return !localStorage.getItem(TOUR_KEY); } catch { return true; }
  }

  function marcarTourHecho() {
    try { localStorage.setItem(TOUR_KEY, "1"); } catch {}
  }

  function mostrarTourDeNuevo() {
    try { localStorage.removeItem(TOUR_KEY); } catch {}
  }

  function handleCerrarAyuda() {
    mostrarAyudaCoberturas = false;
    mostrarTourDeNuevo();
    step = 1;
    mostrarTourCobertura = true;
  }

  let fechaSeleccionada = $state("");
  let diaSeleccionado = $state("");
  let docentesAusentes = $state<{ nombre: string; tipo: string }[]>([]);
  let gruposAusentes = $state<{ grupo: string; horaInicio: number }[]>([]);

  let slotsDelDia = $state<SlotInfo[]>([]);
  let slotsConAusencia = $state<SlotInfo[]>([]);
  let coberturasSugeridas = $state<CoberturaSugerida[]>([]);
  let coberturasHistoricas = $state<CoberturaHistorica[]>([]);
  let gruposSugeridosAAusentar = $state<SugerenciaGrupo[]>([]);
  let slotsExcluidos = $state<Set<string>>(new Set());
  // Grupos liberados manualmente desde el botón rojo de cada fila (Step 3).
  // Se registran aquí porque liberarGrupoDesdeHora puede quitarlos de gruposAusentes,
  // y deben persistirse al guardar como liberados. Clave: `${grupo}-${horaLiberada}`.
  let gruposLiberadosManual = $state<{ grupo: string; horaLiberada: number; docenteAusente: string }[]>([]);
  // Adelantos de clase aplicados en la sesión (clase de un grado liberado movida
  // a un hueco libre anterior). Overlay sobre horariosData, solo en memoria.
  let adelantosAplicados = $state<Adelanto[]>([]);
  let mostrarModalAdelantos = $state(false);
  let adelantoModalData = $state<{
    grupo: string;
    horaLiberada: number;
    docenteAusente: string;
    adelantos: Adelanto[];
    huecosPorAdelanto: SlotInfo[];
  } | null>(null);
  let mostrarAyudaCoberturas = $state(false);
  // Horario efectivo del día con adelantos aplicados. Día-específico; las
  // funciones de cobertura deben usar este en vez del import crudo.
  const horariosEfectivos = $derived(
    adelantosAplicados.length && diaSeleccionado
      ? aplicarAdelantosAHorarios(horariosData as HorarioDocente[], diaSeleccionado, adelantosAplicados)
      : (horariosData as HorarioDocente[])
  );
  let mostrarModalGrupos = $state(false);
  let mostrarReporteWhatsApp = $state(false);
  let coberturasGuardadas = $state<CoberturaSugerida[]>([]);
  let vistaPreviaReporte = $state(false);
  let mostrarModalTipoAusencia = $state(false);
  let docenteSeleccionado = $state("");
  let mostrarReportePDF = $state(false);
  let coberturasReportePDF = $state<CoberturaSugerida[]>([]);
  let gruposReportePDF = $state<{ grupo: string; horaInicio: number }[]>([]);
  let diaReportePDF = $state("");
  let fechaReportePDF = $state("");
  let permitirRepetir = $state(false);
  let ignorarHorasPropietarias = $state(false);
  let liberadosReportePDF = $state<import("../../lib/coberturaUtils").CoberturaLiberado[]>([]);

  const isDev = import.meta.env.DEV;

  const tourPasos: TourPaso[] = [
    {
      selector: "#tour-step1-dia",
      titulo: "Selecciona el día",
      descripcion: "Elige el día de la semana y la fecha exacta para la que necesitas gestionar coberturas. Solo se permiten días hábiles (lunes a viernes) dentro de los próximos 7 días.",
      icono: Calendar,
      color: "#3b82f6",
      step: 1,
    },
    {
      selector: "#tour-step1-docentes",
      titulo: "Marca docentes ausentes",
      descripcion: "Busca y selecciona los docentes que estarán ausentes. Al marcar uno, deberás elegir el tipo de ausencia (calamidad, capacitación, enfermedad, etc.). El icono del tipo aparece junto al nombre.",
      icono: Users,
      color: "#ef4444",
      step: 1,
    },
    {
      selector: "#tour-step1-grupos",
      titulo: "Libera grupos (opcional)",
      descripcion: "Si hay grupos que no asisten, libéralos con el botón «LIBERAR GRUPOS». Indica desde qué hora quedan libres. Los grupos liberados generan horas libres que deben ser cubiertas.",
      icono: UsersRound,
      color: "#f59e0b",
      step: 1,
    },
    {
      selector: "#tour-step1-boton",
      titulo: "Analizar horas libres",
      descripcion: "Al hacer clic, el sistema calcula qué docentes tienen horas libres y qué grupos están ausentes. Este análisis alimenta el Step 2 para sugerir coberturas automáticas.",
      icono: PlayCircle,
      color: "#10b981",
      step: 1,
    },
    {
      selector: "#tour-step2-analisis",
      titulo: "Revisa el análisis",
      descripcion: "Aquí ves las horas que quedan libres por ausencia de docente o grupo. Las filas rojas son huecos que requieren cobertura. Puedes activar «Ignorar límites» para forzar asignaciones aunque exceedan el máximo semanal.",
      icono: Eye,
      color: "#8b5cf6",
      step: 2,
    },
    {
      selector: "#tour-step3-opciones",
      titulo: "Revisa y ajusta coberturas",
      descripcion: "Cada fila muestra la hora libre, el grupo ausente y el docente sugerido para cubrirla. Puedes cambiar el docente desde el selector, activar/desactivar la cobertura con el toggle, o liberar el grupo con el botón rojo. «Aprobar todo» marca todas las sugerencias.",
      icono: CheckSquare,
      color: "#06b6d4",
      step: 3,
    },
    {
      selector: "#tour-step3-guardar",
      titulo: "Guardar y compartir",
      descripcion: "Al guardar, las coberturas se envían a Google Sheets. Después puedes compartir el reporte por WhatsApp o generar un PDF. También puedes volver al Step 1 para otra sesión.",
      icono: Save,
      color: "#eab308",
      step: 3,
    },
  ];

  function irAStepTour(paso: number) {
    if (paso >= 1 && paso <= 3) {
      step = paso;
    }
  }

  function iniciarTour() {
    step = 1;
    mostrarTourCobertura = true;
  }

  function cerrarTour() {
    mostrarTourCobertura = false;
    marcarTourHecho();
  }

  function getFechaHoy(): string {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, "0");
    const d = String(now.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
  }

  function isFestivo(fecha: string): boolean {
    return festivos.some((f) => f.fecha === fecha);
  }

  function seleccionarDia(dia: string) {
    const idx = DIAS.indexOf(dia as any);
    if (idx < 0) return;
    const r = resolverFechaDia(getFechaHoy(), idx);
    fechaSeleccionada = r.fecha;
    diaSeleccionado = r.dia;
    if (r.eraFestivo) {
      Swal.fire({
        icon: "info",
        title: "Día festivo",
        text: `${formatoDia(dia)} de esta semana es festivo (${r.nombreFestivo}). Se seleccionó ${formatoDia(r.dia)} ${r.fecha}.`,
        confirmButtonColor: "#ef4444",
      });
    }
  }

  // Ventana permitida para coberturas: desde hoy hasta +7 días.
  const fechaMin = $derived(getFechaHoy());
  const fechaMax = $derived.by(() => {
    const d = new Date(getFechaHoy() + "T00:00:00");
    d.setDate(d.getDate() + 7);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${day}`;
  });

  function seleccionarFecha(fecha: string) {
    if (!fecha) {
      fechaSeleccionada = "";
      return;
    }
    if (fecha < fechaMin) {
      Swal.fire({ icon: "warning", title: "Fecha pasada", text: "No puedes seleccionar una fecha anterior a hoy.", confirmButtonColor: "#ef4444" });
      fechaSeleccionada = "";
      diaSeleccionado = "";
      return;
    }
    if (fecha > fechaMax) {
      Swal.fire({ icon: "warning", title: "Fecha muy lejana", text: `Solo puedes programar coberturas hasta el ${fechaMax} (máximo 7 días).`, confirmButtonColor: "#ef4444" });
      fechaSeleccionada = "";
      diaSeleccionado = "";
      return;
    }
    if (isFestivo(fecha)) {
      Swal.fire({ icon: "warning", title: "Festivo", text: `La fecha ${fecha} es festivo en Colombia. Selecciona otro día.`, confirmButtonColor: "#ef4444" });
      fechaSeleccionada = "";
      diaSeleccionado = "";
      return;
    }
    fechaSeleccionada = fecha;
    const dia = getDiaFromFecha(fecha);
    if (dia) {
      diaSeleccionado = dia;
    } else {
      Swal.fire({ icon: "warning", title: "Fecha no válida", text: "Selecciona un día entre lunes y viernes.", confirmButtonColor: "#ef4444" });
      fechaSeleccionada = "";
      diaSeleccionado = "";
    }
  }

  async function loadHistorico() {
    try {
      loading = true;
      coberturasHistoricas = await coberturaSheetsService.getCoberturas();
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
    } finally {
      loading = false;
    }
  }

  function analizarHorasLibres() {
    if (!diaSeleccionado) {
      Swal.fire("Atención", "Selecciona un día primero", "warning");
      return;
    }

    const allSlots = getSlotsDelDia(diaSeleccionado, horariosData);
    slotsDelDia = allSlots;

    const ausencias: Ausencia[] = [
      ...docentesAusentes.map((n) => ({ tipo: "docente" as const, nombre: n.nombre, motivo: n.tipo })),
      ...gruposAusentes.map((g) => ({ tipo: "grupo" as const, nombre: g.grupo, horaInicio: g.horaInicio, motivo: "GRUPO AUSENTE" })),
    ];

    slotsConAusencia = aplicarAusencias(allSlots, ausencias, horariosData);
    step = 2;
  }

function recalcularCoberturas() {
    console.log("recalcularCoberturas INI gruposAusentes:", gruposAusentes);
    const horarios = horariosEfectivos;
    const allSlots = getSlotsDelDia(diaSeleccionado, horarios);
    slotsDelDia = allSlots;

    const ausencias: Ausencia[] = [
      ...docentesAusentes.map((n) => ({ tipo: "docente" as const, nombre: n.nombre, motivo: n.tipo })),
      ...gruposAusentes.map((g) => ({ tipo: "grupo" as const, nombre: g.grupo, horaInicio: g.horaInicio, motivo: "GRUPO AUSENTE" })),
    ];
    console.log("ausencias:", JSON.stringify(ausencias, null, 2));
    console.log("gruposAusentes.length:", gruposAusentes.length);

    slotsConAusencia = aplicarAusencias(allSlots, ausencias, horarios);

    let libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);
    if (slotsExcluidos.size > 0) {
      libresPorAusencia = libresPorAusencia.filter((s) => {
        const key = `${s.hora}-${s.docenteAusente || s.docente}`;
        return !slotsExcluidos.has(key);
      });
    }
    console.log("libresPorAusencia.length:", libresPorAusencia.length);
    if (libresPorAusencia.length === 0) {
      console.log("No hay slots libres, saliendo");
      coberturasSugeridas = [];
      gruposSugeridosAAusentar = [];
      return;
    }

    const previas = coberturasSugeridas;
    const nuevas = asignarAutomaticamente(
      libresPorAusencia,
      horarios,
      coberturasHistoricas,
      diaSeleccionado,
      fechaSeleccionada,
      gruposAusentes,
      permitirRepetir,
      ignorarHorasPropietarias
    );

    // B1: Merge — preservar docenteCubre manual donde el slot persiste
    const claveSlot = (c: { hora: number; docenteAusente: string; grupoAusente: string }) =>
      `${c.hora}|${c.docenteAusente}|${c.grupoAusente}`;
    const previasMap = new Map<string, CoberturaSugerida>();
    for (const p of previas) {
      if (p.docenteCubre) previasMap.set(claveSlot(p), p);
    }

    const merged: CoberturaSugerida[] = nuevas.map((n) => {
      const prev = previasMap.get(claveSlot(n));
      if (prev && prev.docenteCubre) {
        return {
          ...n,
          docenteCubre: prev.docenteCubre,
          aprobada: prev.aprobada,
          violation: prev.violation,
        };
      }
      return n;
    });

    // Conflict resolution: auto-pick que choque con manual preservado busca otro
    const usadosPorManual = new Set<string>();
    for (let i = 0; i < merged.length; i++) {
      const prev = previasMap.get(claveSlot(merged[i]));
      if (!prev || !prev.docenteCubre) continue;
      const esSinLimite = ROLES_SIN_LIMITE.some((r) => prev.docenteCubre.includes(r));
      if (esSinLimite) continue;
      usadosPorManual.add(prev.docenteCubre);
    }

    for (let i = 0; i < merged.length; i++) {
      const m = merged[i];
      const prev = previasMap.get(claveSlot(m));
      const fuePreservado = !!(prev && prev.docenteCubre);
      if (fuePreservado) continue;
      if (!m.docenteCubre) continue;
      const esSinLimite = ROLES_SIN_LIMITE.some((r) => m.docenteCubre.includes(r));
      if (esSinLimite) continue;

      if (usadosPorManual.has(m.docenteCubre)) {
        const alternativa = m.posiblesCobradores.find(
          (d) => !usadosPorManual.has(d) && d !== m.docenteCubre
        );
        if (alternativa) {
          merged[i] = { ...m, docenteCubre: alternativa, violation: "" };
          usadosPorManual.add(alternativa);
        } else {
          // Sin alternativa: dejar vacío con violation
          merged[i] = {
            ...m,
            docenteCubre: "",
            violation: "⚠️ Sin docentes disponibles (conflicto con asignación previa)",
          };
        }
      } else {
        usadosPorManual.add(m.docenteCubre);
      }
    }

    // Asegurar que docente preservado aparezca en posiblesCobradores
    for (let i = 0; i < merged.length; i++) {
      const m = merged[i];
      if (m.docenteCubre && !m.posiblesCobradores.includes(m.docenteCubre)) {
        const esSinLimite = ROLES_SIN_LIMITE.some((r) => m.docenteCubre.includes(r));
        if (!esSinLimite) {
          merged[i] = { ...m, posiblesCobradores: [m.docenteCubre, ...m.posiblesCobradores] };
        }
      }
    }

    coberturasSugeridas = merged;
    console.log("coberturasSugeridas.length =", coberturasSugeridas.length);
    gruposSugeridosAAusentar = analizarGruposAAusentar(
      libresPorAusencia,
      coberturasSugeridas,
      gruposAusentes.map((g) => g.grupo),
      horarios,
      diaSeleccionado
    );
    console.log("recalcularCoberturas FIN");
  }

  function isGrupoAusente(grupo: string): boolean {
    return gruposAusentes.some((g) => g.grupo === grupo);
  }

  function getHoraInicio(grupo: string): number {
    return gruposAusentes.find((g) => g.grupo === grupo)?.horaInicio ?? 5;
  }

  function toggleGrupoAusente(grupo: string, checked: boolean, horaInicio: number = 5) {
    if (checked) {
      if (!gruposAusentes.some((g) => g.grupo === grupo)) {
        gruposAusentes = [...gruposAusentes, { grupo, horaInicio }];
      }
    } else {
      gruposAusentes = gruposAusentes.filter((g) => g.grupo !== grupo);
      gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
    }
  }

  function actualizarHoraInicio(grupo: string, horaInicio: number) {
    gruposAusentes = gruposAusentes.map((g) =>
      g.grupo === grupo ? { ...g, horaInicio } : g
    );
  }

  function agregarGrupoAusente(grupo: string, horaInicio: number = 5) {
    if (!gruposAusentes.some((g) => g.grupo === grupo)) {
      gruposAusentes = [...gruposAusentes, { grupo, horaInicio }];
      gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
      recalcularCoberturas();
    }
  }

  function liberarGrupoDesdeHora(grupo: string, hora: number, docenteAusente: string) {
    const key = `${hora}-${docenteAusente}`;
    slotsExcluidos = new Set(slotsExcluidos).add(key);

    // Registrar la liberación para que persista al guardar, aun si abajo se quita
    // el grupo de gruposAusentes. hora es 0-indexed → hora liberada 1-indexed = hora + 1.
    const horaLiberada = hora + 1;
    if (!gruposLiberadosManual.some((l) => l.grupo === grupo && l.horaLiberada === horaLiberada)) {
      gruposLiberadosManual = [...gruposLiberadosManual, { grupo, horaLiberada, docenteAusente }];
    }

    // B2: Si la fila era grupo-driven, avanzar horaInicio o quitar chip del grupo
    const slotRow = coberturasSugeridas.find(
      (c) => c.hora === hora && c.grupoAusente === grupo
    );
    if (slotRow) {
      const hayPosteriores = coberturasSugeridas.some(
        (c) => c.grupoAusente === grupo && c.hora > hora
      );
      if (!hayPosteriores) {
        gruposAusentes = gruposAusentes.filter((g) => g.grupo !== grupo);
        gruposSugeridosAAusentar = gruposSugeridosAAusentar.filter((g) => g.grupo !== grupo);
      } else {
        // aplicarAusencias usa s.hora >= horaMin - 1 (s.hora 0-indexed, horaInicio 1-indexed)
        // Para excluir hora (0-indexed) e incluir hora+1: horaInicio = hora + 2
        gruposAusentes = gruposAusentes.map((g) =>
          g.grupo === grupo ? { ...g, horaInicio: hora + 2 } : g
        );
      }
    }

    recalcularCoberturas();
  }

  // Botón rojo (Step 3): adelanta automáticamente las clases del grado liberado
  // a huecos libres anteriores, luego libera la hora y abre la modal-resumen.
  function liberarGrupoConAdelantos(grupo: string, hora: number, docenteAusente: string) {
    const horaLiberada = hora + 1; // 1-indexed
    const base = horariosEfectivos; // ya incluye adelantos previos
    const nuevos = calcularAdelantos(
      grupo,
      horaLiberada,
      diaSeleccionado,
      base,
      docentesAusentes.map((d) => d.nombre)
    );
    for (const a of nuevos.filter((x) => x.aplicable)) {
      if (!adelantosAplicados.some((x) => x.docente === a.docente && x.horaOrigen === a.horaOrigen)) {
        adelantosAplicados = [...adelantosAplicados, a];
      }
    }

    // Obtener huecos que quedan libres por el adelantamiento
    const huecosLibres = getHuecosLibresPorAdelanto(
      grupo,
      diaSeleccionado,
      nuevos,
      horariosData as HorarioDocente[]
    );

    // Crear coberturas automáticas para los huecos libres
    if (huecosLibres.length > 0) {
      const cargaDiariaSesion = new Map<string, number>();
      const horasCubSemana = new Map<string, number>();
      const semanaActual = getSemanaDelAno(fechaSeleccionada);
      for (const cp of coberturasHistoricas) {
        if (cp.estado !== "aprobado") continue;
        if (cp.fecha === fechaSeleccionada) continue;
        const cpSemana = getSemanaDelAno(cp.fecha);
        if (cpSemana !== semanaActual) continue;
        const doc = cp.docente_cubre;
        horasCubSemana.set(doc, (horasCubSemana.get(doc) || 0) + 1);
      }

      for (const hueco of huecosLibres) {
        const posibles = getPosiblesCobradoresParaSlot(
          hueco,
          diaSeleccionado,
          horariosEfectivos,
          huecosLibres,
          cargaDiariaSesion,
          horasCubSemana,
          new Map(),
          coberturasSugeridas,
          gruposAusentes,
          true,
          false
        );

        if (posibles.length > 0) {
          const docenteAsignado = posibles[0].docente;
          const nuevaCobertura: CoberturaSugerida = {
            hora: hueco.hora,
            docenteAusente: hueco.docenteAusente || "",
            grupoAusente: grupo,
            docenteCubre: docenteAsignado,
            grupoACubrir: grupo,
            aprobada: true,
            posiblesCobradores: posibles.map((p) => p.docente),
            motivoAusencia: "Adelanto de hora",
            porGrupoAusente: true,
          };
          const existe = coberturasSugeridas.some(
            (c) => c.hora === nuevaCobertura.hora && c.grupoAusente === nuevaCobertura.grupoAusente
          );
          if (!existe) {
            coberturasSugeridas = [...coberturasSugeridas, nuevaCobertura];
          }
        }
      }
    }

    // Bookkeeping de liberación + recalcularCoberturas() (usa horariosEfectivos).
    liberarGrupoDesdeHora(grupo, hora, docenteAusente);
    adelantoModalData = { grupo, horaLiberada, docenteAusente, adelantos: nuevos, huecosPorAdelanto: huecosLibres };
    mostrarModalAdelantos = true;
  }

  async function generarAsignaciones() {
    loading = true;
    try {
      await loadHistorico();
      recalcularCoberturas();

      if (coberturasSugeridas.length === 0) {
        Swal.fire("Sin horas libres", "No hay horas libres por ausencia en el día seleccionado", "info");
        return;
      }
      step = 3;
    } finally {
      loading = false;
    }
  }

  async function guardarCoberturas() {
    const aprobadas = coberturasSugeridas.filter((c) => c.aprobada && c.docenteCubre !== "IGNORAR");
    if (aprobadas.length === 0 && gruposAusentes.length === 0 && gruposLiberadosManual.length === 0) {
      Swal.fire("Atención", "No hay coberturas ni grupos liberados para guardar", "warning");
      return;
    }

    loading = true;
    try {
      await coberturaSheetsService.deleteCoberturasPorFecha(fechaSeleccionada);
      await coberturaSheetsService.deleteLiberadosPorFecha(fechaSeleccionada);

      await coberturaSheetsService.saveCoberturasBatch(
        aprobadas.map((c) => ({
          fecha: fechaSeleccionada,
          dia_semana: diaSeleccionado,
          hora: c.hora,
          docente_ausente: c.docenteAusente,
          grupo_ausente: c.grupoAusente,
          docente_cubre: c.docenteCubre,
          grupo_a_cubrir: c.grupoACubrir,
          estado: "aprobado",
          motivo: c.motivoAusencia,
        }))
      );

      // Combinar grupos del modal (gruposAusentes) con los liberados manualmente
      // desde el botón rojo de cada fila, evitando duplicados por grupo+hora.
      const liberadosAGuardar = new Map<string, { grupo: string; hora_liberada: number; motivo: string }>();
      for (const g of gruposAusentes) {
        const horaLib = g.horaInicio;
        const motivo = horaLib === 1 ? "NO ASISTE" : "Grupo liberado";
        liberadosAGuardar.set(`${g.grupo}-${horaLib}`, { grupo: g.grupo, hora_liberada: horaLib, motivo });
      }
      // Liberaciones manuales (botón rojo): una entrada por grupo, hora más temprana.
      const manualPorGrupo = new Map<string, { horaLib: number; docenteAusente: string }>();
      for (const l of gruposLiberadosManual) {
        const prev = manualPorGrupo.get(l.grupo);
        if (!prev || l.horaLiberada < prev.horaLib) {
          manualPorGrupo.set(l.grupo, { horaLib: l.horaLiberada, docenteAusente: l.docenteAusente });
        }
      }
      for (const [grupo, info] of manualPorGrupo) {
        liberadosAGuardar.set(`${grupo}-${info.horaLib}`, {
          grupo,
          hora_liberada: info.horaLib,
          motivo: `Grupo liberado desde h${info.horaLib} — ${info.docenteAusente} ausente`,
        });
      }
      // Coberturas aprobadas sin cubridor → el grupo queda liberado. Se colapsa
      // a UNA entrada por grupo, usando la hora más temprana (desde la que queda libre).
      const sinCubridor = coberturasSugeridas.filter((c) => c.aprobada && !c.docenteCubre);
      const sinCubridorPorGrupo = new Map<string, { horaLib: number; docenteAusente: string }>();
      for (const c of sinCubridor) {
        const grupo = c.grupoAusente || c.grupoACubrir;
        if (!grupo) continue;
        const horaLib = c.hora + 1;
        const prev = sinCubridorPorGrupo.get(grupo);
        if (!prev || horaLib < prev.horaLib) {
          sinCubridorPorGrupo.set(grupo, { horaLib, docenteAusente: c.docenteAusente });
        }
      }
      for (const [grupo, info] of sinCubridorPorGrupo) {
        // No sobrescribir si el grupo ya quedó registrado por el modal o liberación manual.
        if ([...liberadosAGuardar.values()].some((l) => l.grupo === grupo)) continue;
        liberadosAGuardar.set(`${grupo}-${info.horaLib}`, {
          grupo,
          hora_liberada: info.horaLib,
          motivo: `Sin cubridor — ${info.docenteAusente} ausente`,
        });
      }

      await coberturaSheetsService.saveLiberadosBatch(
        [...liberadosAGuardar.values()].map((lib) => ({
          fecha: fechaSeleccionada,
          dia_semana: diaSeleccionado,
          grupo: lib.grupo,
          hora_liberada: lib.hora_liberada,
          motivo: lib.motivo,
        }))
      );

      // Preparar datos para el reporte WhatsApp con lo recién guardado.
      const liberadosGuardados: import("../../lib/coberturaUtils").CoberturaLiberado[] = [
        ...liberadosAGuardar.values(),
      ].map((lib) => ({
        fecha: fechaSeleccionada,
        dia_semana: diaSeleccionado,
        grupo: lib.grupo,
        hora_liberada: lib.hora_liberada,
        motivo: lib.motivo,
      }));

      await Swal.fire({
        icon: "success",
        title: "Coberturas guardadas",
        html: `Se guardaron ${aprobadas.length} cobertura(s) y ${liberadosAGuardar.size} grupo(s) liberado(s).`,
        showCancelButton: true,
        confirmButtonText: "Compartir WhatsApp",
        cancelButtonText: "Nueva sesión",
      }).then((r) => {
        if (r.isConfirmed) {
          coberturasGuardadas = [...aprobadas];
          diaReportePDF = diaSeleccionado;
          fechaReportePDF = fechaSeleccionada;
          gruposReportePDF = liberadosGuardados.map((l) => ({ grupo: l.grupo, horaInicio: l.hora_liberada }));
          liberadosReportePDF = liberadosGuardados;
          mostrarReporteWhatsApp = true;
        } else if (r.dismiss === Swal.DismissReason.cancel) {
          resetSesion();
        }
      });
    } catch (e: any) {
      Swal.fire("Error", e.message, "error");
    } finally {
      loading = false;
    }
  }

  function resetSesion() {
    step = 1;
    docentesAusentes = [];
    gruposAusentes = [];
    gruposLiberadosManual = [];
    adelantosAplicados = [];
    mostrarModalAdelantos = false;
    adelantoModalData = null;
    slotsExcluidos = new Set();
    slotsDelDia = [];
    slotsConAusencia = [];
    coberturasSugeridas = [];
    gruposSugeridosAAusentar = [];
    diaSeleccionado = "";
    fechaSeleccionada = "";
    mostrarReporteWhatsApp = false;
    mostrarReportePDF = false;
    vistaPreviaReporte = false;
    coberturasGuardadas = [];
    coberturasReportePDF = [];
    gruposReportePDF = [];
  }

  function toggleCobertura(index: number) {
    coberturasSugeridas[index].aprobada = !coberturasSugeridas[index].aprobada;
  }

  function aprobarTodo() {
    coberturasSugeridas = coberturasSugeridas.map((c) => ({
      ...c,
      aprobada: true,
    }));
  }

  function cambiarDocenteCubre(index: number, docente: string) {
    if (docente === "IGNORAR") {
      coberturasSugeridas[index].docenteCubre = "IGNORAR";
      coberturasSugeridas = [...coberturasSugeridas];
      return;
    }

    const docenteAnterior = coberturasSugeridas[index].docenteCubre;
    const esSpecialRole = ROLES_SIN_LIMITE.some((r) => docente.includes(r));
    const eraSpecialRole = ROLES_SIN_LIMITE.some((r) => docenteAnterior?.includes(r));

    if (!esSpecialRole) {
      const cobertura = coberturasSugeridas[index];
      const jornada = horariosEfectivos.find((h) => h.docente === docente)?.[diaSeleccionado as keyof HorarioDocente] as string[] || [];
      const slotOcupado = jornada[cobertura.hora];
      // si grupo del slot está liberado desde horaInicio cumplida, slot equivale a libre
      const grupoSlot = slotOcupado ? slotOcupado.match(/(\d{3,4})$/)?.[1] : "";
      const grupoLiberado = !!grupoSlot && gruposAusentes.some(
        (g) => g.grupo === grupoSlot && cobertura.hora >= (g.horaInicio ?? 1) - 1
      );
      if (slotOcupado && slotOcupado !== "" && !grupoLiberado) {
        Swal.fire({ icon: "warning", title: "Docente ocupado", text: `${docente} ya tiene clase a esa hora (${slotOcupado})`, confirmButtonColor: "#ef4444" });
        return;
      }

      // Repetición en sesión / histórico no bloquea — la fila se marca visualmente como duplicado en AsignacionesView.
    }

    coberturasSugeridas[index].docenteCubre = docente;

    // Para recálculo correcto: construir contexto sin asumir el docente anterior consume hora.
    // El docente anterior queda LIBRE si: rol especial, o si fue movido a otra cobertura, o
    // simplemente porque ahora otro toma su lugar — por eso recalculamos posibles de cada
    // fila con asignacionesSesion = coberturasSugeridas actuales (donde el anterior ya no
    // figura como docenteCubre).
    const cargaHistorica = construirCargaDiariaHistorica(coberturasHistoricas, fechaSeleccionada);

    const hoy = new Date(fechaSeleccionada + "T00:00:00");
    const hace14dias = new Date(hoy);
    hace14dias.setDate(hoy.getDate() - 14);
    const hace7dias = new Date(hoy);
    hace7dias.setDate(hoy.getDate() - 7);

    const horasCubiertasSemana = new Map<string, number>();
    const indiceAusencias = new Map<string, number>();
    const semanaActual = getSemanaDelAno(fechaSeleccionada);

    for (const cp of coberturasHistoricas) {
      if (cp.estado !== "aprobado") continue;
      const cpSemana = getSemanaDelAno(cp.fecha);
      if (cpSemana === semanaActual) {
        horasCubiertasSemana.set(cp.docente_cubre, (horasCubiertasSemana.get(cp.docente_cubre) || 0) + 1);
      }
      const cpFecha = new Date(cp.fecha + "T00:00:00");
      if (cpFecha >= hace14dias && cpFecha < hace7dias && cp.docente_ausente) {
        indiceAusencias.set(cp.docente_ausente, (indiceAusencias.get(cp.docente_ausente) || 0) + 1);
      }
    }

    const libresFiltrado = getSlotsLibresPorAusencia(slotsConAusencia).filter((s) => {
      const key = `${s.hora}-${s.docenteAusente || s.docente}`;
      return !slotsExcluidos.has(key);
    });

    const DEBUG_RECALC = false; // set true para diagnosticar problemas de recálculo
    if (DEBUG_RECALC) {
      console.log("[cambiarDocenteCubre] index=", index, "docente=", docente);
      console.log("  coberturasSugeridas snapshot=", coberturasSugeridas.map((c, j) => ({ j, hora: c.hora, docenteAusente: c.docenteAusente, docenteCubre: c.docenteCubre })));
    }

    for (let i = 0; i < coberturasSugeridas.length; i++) {
      if (i === index) continue;

      const cov = coberturasSugeridas[i];
      const slotParaEsta = libresFiltrado.find(
        (s) => s.hora === cov.hora && (s.docenteAusente === cov.docenteAusente || s.docente === cov.docenteAusente)
      );

      if (!slotParaEsta) continue;

      // sesionFiltrada: vaciar docenteCubre de filas i e index para que getPosiblesCobradoresParaSlot
      // no autobloquee al docente actual de la fila i ni cuente al docente anterior de la fila index.
      const sesionFiltrada = coberturasSugeridas.map((c, j) =>
        j === i || j === index ? { ...c, docenteCubre: "" } : c
      );

      // Reconstruir cargaDiariaSesion POR FILA: excluir las filas i e index para que el
      // docente actual de la fila i no se cuente a sí mismo (autobloqueo) y para que el
      // docente anterior de la fila index (ahora reemplazado) tampoco aparezca con carga.
      const cargaDiariaSesion = construirCargaDiariaSesion(sesionFiltrada, -1, "", cargaHistorica, {
        dia: diaSeleccionado,
        horarios: horariosEfectivos,
        ausenciasGrupo: gruposAusentes,
      });

      // Para SELECT: permitir docentes con carga/semana previas (solo avisar visualmente,
      // no bloquear). Usuario decide. Por eso forzamos permitirRepetir=true aquí.
      const posibles = getPosiblesCobradoresParaSlot(
        slotParaEsta,
        diaSeleccionado,
        horariosEfectivos,
        libresFiltrado,
        cargaDiariaSesion,
        horasCubiertasSemana,
        indiceAusencias,
        sesionFiltrada,
        gruposAusentes,
        true, // permitirRepetir = true (solo avisar)
        ignorarHorasPropietarias
      ).map((c) => c.docente);

      if (DEBUG_RECALC) {
        const cargaObj = Object.fromEntries(cargaDiariaSesion);
        console.log(`  fila i=${i} (hora=${cov.hora}, ausente=${cov.docenteAusente}) ANA SOFIA carga=`, cargaObj["ANA SOFIA CARDENAS PETUMA"] ?? "(no en mapa)");
        const ana = horariosData.find(h => h.docente === "ANA SOFIA CARDENAS PETUMA");
        if (ana) {
          const slotAna = (ana.miercoles as string[])[cov.hora];
          console.log(`    ANA SOFIA jornada[${cov.hora}]="${slotAna}"`);
        }
      }

      const docenteCubreActual = cov.docenteCubre;
      if (docenteCubreActual && cov.aprobada && !posibles.includes(docenteCubreActual)) {
        posibles.unshift(docenteCubreActual);
      }

      if (DEBUG_RECALC) {
        console.log(`  fila i=${i} (hora=${cov.hora}, ausente=${cov.docenteAusente}) posibles=`, posibles, "ANA incluida?", posibles.includes("ANA SOFIA CARDENAS PETUMA"));
      }

      coberturasSugeridas[i] = { ...cov, posiblesCobradores: posibles };
    }

    // Forzar reasignación referencial para refrescar Svelte
    coberturasSugeridas = [...coberturasSugeridas];
  }

  function liberarGrupoAsignacion(grupo: string, horaInicio: number) {
    agregarGrupoAusente(grupo, horaInicio);
  }

  function goToStep(s: number) {
    step = s;
  }

  async function generarReporteDelDia(fecha: string) {
    const delDia = coberturasHistoricas.filter((c) => c.fecha === fecha);
    if (delDia.length === 0) {
      Swal.fire("Sin datos", "No hay coberturas para esa fecha", "info");
      return;
    }

    diaReportePDF = delDia[0].dia_semana;
    fechaReportePDF = fecha;

    let todosLiberados: import("../../lib/coberturaUtils").CoberturaLiberado[] = [];
    try {
      todosLiberados = await coberturaSheetsService.getLiberados();
      liberadosReportePDF = todosLiberados.filter((l) => l.fecha === fecha);
    } catch {
      liberadosReportePDF = [];
    }

    gruposReportePDF = liberadosReportePDF.map((l) => ({ grupo: l.grupo, horaInicio: l.hora_liberada }));

    coberturasReportePDF = delDia.map((c): CoberturaSugerida => ({
      hora: c.hora,
      docenteAusente: c.docente_ausente,
      grupoAusente: c.grupo_ausente,
      docenteCubre: c.docente_cubre,
      grupoACubrir: c.grupo_a_cubrir,
      aprobada: c.estado === "aprobado",
      posiblesCobradores: [],
      motivoAusencia: c.motivo,
    }));

    mostrarReportePDF = true;
  }

  async function generarWhatsAppDelDia(fecha: string) {
    const delDia = coberturasHistoricas.filter((c) => c.fecha === fecha);
    if (delDia.length === 0) {
      Swal.fire("Sin datos", "No hay coberturas para esa fecha", "info");
      return;
    }

    diaReportePDF = delDia[0].dia_semana;
    fechaReportePDF = fecha;

    let todosLiberados: import("../../lib/coberturaUtils").CoberturaLiberado[] = [];
    try {
      todosLiberados = await coberturaSheetsService.getLiberados();
      liberadosReportePDF = todosLiberados.filter((l) => l.fecha === fecha);
    } catch {
      liberadosReportePDF = [];
    }

    gruposReportePDF = liberadosReportePDF.map((l) => ({ grupo: l.grupo, horaInicio: l.hora_liberada }));

    coberturasReportePDF = delDia.map((c): CoberturaSugerida => ({
      hora: c.hora,
      docenteAusente: c.docente_ausente,
      grupoAusente: c.grupo_ausente,
      docenteCubre: c.docente_cubre,
      grupoACubrir: c.grupo_a_cubrir,
      aprobada: c.estado === "aprobado",
      posiblesCobradores: [],
      motivoAusencia: c.motivo,
    }));

    coberturasGuardadas = [...coberturasReportePDF];
    mostrarReporteWhatsApp = true;
  }

  // Fecha por defecto según reglas de negocio:
  // - Lunes a jueves: hoy (si es hábil), si no el siguiente día hábil.
  // - Viernes (o fin de semana): el lunes siguiente (siguiente día hábil).
  function getFechaPorDefecto(): string {
    const hoyStr = getFechaHoy();
    const diaSemana = new Date(hoyStr + "T00:00:00").getDay();
    // Viernes(5), sábado(6) o domingo(0): saltar al siguiente día hábil.
    if (diaSemana === 5 || diaSemana === 6 || diaSemana === 0) {
      return siguienteDiaHabil(hoyStr);
    }
    // Lunes a jueves: hoy si es hábil, si no el siguiente día hábil.
    return isFestivo(hoyStr) ? siguienteDiaHabil(hoyStr) : hoyStr;
  }

  onMount(() => {
    const fecha = getFechaPorDefecto();
    fechaSeleccionada = fecha;
    diaSeleccionado = getDiaFromFecha(fecha);

    if (shouldShowTour()) {
      irAStepTour(1);
      mostrarTourCobertura = true;
    }

    const handleAyudaPaso = (e: CustomEvent<number>) => {
      const nuevoPaso = e.detail;
      if (nuevoPaso >= 1 && nuevoPaso <= 3) {
        step = nuevoPaso;
      }
    };
    window.addEventListener("cobertura-help-paso", handleAyudaPaso as EventListener);
    return () => {
      window.removeEventListener("cobertura-help-paso", handleAyudaPaso as EventListener);
    };
  });
</script>

<ModuleHeader
  title="Cobertura de Horas"
  onBack={onBack}
/>

<div class="p-4 max-w-7xl mx-auto">
  <div class="flex gap-2 mb-6">
    <button
      onclick={() => { subView = "cobertura"; resetSesion(); }}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {subView === 'cobertura' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {subView === 'cobertura' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {subView === 'cobertura' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Gestionar Coberturas
    </button>
    <button
      onclick={() => { subView = "historial"; loadHistorico(); }}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all"
      style="background-color: {subView === 'historial' ? 'rgb(var(--accent-primary))' : 'rgb(var(--card-bg))'}; color: {subView === 'historial' ? 'white' : 'rgb(var(--text-primary))'}; border: 1px solid {subView === 'historial' ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"
    >
      Ver Historial
    </button>
    <button
      onclick={() => mostrarAyudaCoberturas = true}
      class="px-4 py-2 rounded-lg font-medium text-sm transition-all flex items-center gap-2"
      style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--border-primary));"
      title="Ayuda sobre gestión de coberturas"
    >
      <HelpCircle size={16} />
      Ayuda
    </button>
  </div>

  {#if subView === "historial"}
    <HistorialCoberturas {coberturasHistoricas} {loading} onReload={loadHistorico} onGenerarReporte={generarReporteDelDia} onGenerarWhatsApp={generarWhatsAppDelDia} />
  {:else}
    {#if step >= 1}
      <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
        {#each [1, 2, 3] as s}
          <div class="flex items-center gap-2">
            <button
              onclick={() => s <= step && goToStep(s)}
              class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition-all
                {s === step ? 'ring-2 ring-offset-2' : ''}"
              style="
                background-color: {s < step ? 'rgb(var(--accent-primary))' : s === step ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'};
                color: {s <= step ? 'white' : 'rgb(var(--text-secondary))'};
                ring-color: {s === step ? 'rgb(var(--accent-primary))' : 'transparent'};
              "
            >
              {s}
            </button>
            {#if s < 3}
              <div class="w-8 h-0.5" style="background-color: {s < step ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};"></div>
            {/if}
          </div>
        {/each}
      </div>
    {/if}

    {#if step === 1}
      <div class="p-6 rounded-2xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
        <h2 id="tour-step1-header" class="text-lg font-bold mb-4" style="color: rgb(var(--text-primary));">Step 1 — Día y Ausencias</h2>

        <div id="tour-step1-dia" class="mb-4">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">Día de la semana</p>
          <div class="flex gap-2 flex-wrap">
            {#each DIAS as dia, i}
              <button
                onclick={() => seleccionarDia(dia)}
                class="px-4 py-2 rounded-lg font-medium text-sm transition-all min-h-[48px] sm:min-h-[auto]"
                style="
                  background-color: {diaSeleccionado === dia ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'};
                  color: {diaSeleccionado === dia ? 'white' : 'rgb(var(--text-primary))'};
                  border: 1px solid {diaSeleccionado === dia ? 'rgb(var(--accent-primary))' : 'rgb(var(--border-primary))'};
                "
              >
                {dia.toUpperCase()}
              </button>
            {/each}
          </div>
          <div class="mt-3 max-w-xs">
            <DatePicker
              id="fecha-cobertura"
              label={diaSeleccionado ? `Fecha exacta (${formatoDia(diaSeleccionado)})` : "Fecha exacta"}
              bind:value={fechaSeleccionada}
              minDate={fechaMin}
              maxDate={fechaMax}
              onchange={(v) => seleccionarFecha(v)}
            />
          </div>
        </div>

        <div id="tour-step1-docentes" class="mb-4">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">
            Docentes ausentes
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 rounded-lg border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--bg-secondary));">
            {#each docentes as docente}
              {@const ausencia = docentesAusentes.find((a) => a.nombre === docente)}
              <label class="flex items-center gap-2 cursor-pointer p-2 rounded hover:bg-[rgb(var(--card-bg))] min-h-[44px]">
                <input
                  type="checkbox"
                  checked={!!ausencia}
                  onclick={(e) => {
                    const target = e.currentTarget;
                    if (ausencia) {
                      docentesAusentes = docentesAusentes.filter((a) => a.nombre !== docente);
                    } else {
                      e.preventDefault();
                      target.checked = false;
                      docenteSeleccionado = docente;
                      mostrarModalTipoAusencia = true;
                    }
                  }}
                  class="w-5 h-5 accent-[rgb(var(--accent-primary))] shrink-0"
                />
                <span class="text-xs truncate" style="color: rgb(var(--text-primary));">{docente}</span>
                {#if ausencia && TIPOS_ICONOS[ausencia.tipo]}
                  {@const Icon = TIPOS_ICONOS[ausencia.tipo].icono}
                  <Icon size={14} style="color: {TIPOS_ICONOS[ausencia.tipo].color}" />
                {/if}
              </label>
            {/each}
          </div>
        </div>

        <div id="tour-step1-grupos" class="mb-6">
          <p class="block text-sm font-medium mb-2" style="color: rgb(var(--text-secondary));">
            Grupos ausentes
          </p>
          <button
            onclick={() => mostrarModalGrupos = true}
            class="w-full py-3 rounded-xl font-bold transition-all border-2 min-h-[52px]"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--accent-primary)); border-color: rgb(var(--accent-primary));"
          >
            LIBERAR GRUPOS {gruposAusentes.length > 0 ? `(${gruposAusentes.length})` : ""}
          </button>
          {#if gruposAusentes.length > 0}
            <div class="flex flex-wrap gap-2 mt-2">
              {#each gruposAusentes as g}
                <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-200">
                  {g.grupo} {g.horaInicio > 1 ? `desde h${g.horaInicio}` : "desde h1"}
                </span>
              {/each}
            </div>
          {/if}
        </div>

        <button
          id="tour-step1-boton"
          onclick={analizarHorasLibres}
          disabled={loading}
          class="w-full py-3 rounded-xl font-bold text-white transition-all flex items-center justify-center gap-2 min-h-[52px]"
          style="background-color: rgb(var(--accent-primary)); opacity: {loading ? 0.7 : 1};"
        >
          {#if loading}
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Calculando...
          {:else}
            Analizar horas libres →
          {/if}
        </button>
      </div>
    {/if}

    {#if step === 2}
      <div id="tour-step2-analisis">
        <AnalisisView
          {diaSeleccionado}
          {fechaSeleccionada}
          {docentesAusentes}
          {gruposAusentes}
          slots={slotsConAusencia}
          {loading}
          bind:permitirRepetir
          bind:ignorarHorasPropietarias
          onGenerar={generarAsignaciones}
          onBack={() => step = 1}
          onOpenGruposModal={() => mostrarModalGrupos = true}
        />
      </div>
    {/if}

    {#if step === 3}
      <div id="tour-step3-opciones">
        <AsignacionesView
        {diaSeleccionado}
        {fechaSeleccionada}
        {coberturasSugeridas}
        {gruposSugeridosAAusentar}
        {coberturasHistoricas}
        {gruposAusentes}
        {loading}
        onToggle={toggleCobertura}
        onCambiarDocenteCubre={cambiarDocenteCubre}
        onAgregarGrupoAusente={agregarGrupoAusente}
        onGuardar={guardarCoberturas}
        onBack={() => step = 2}
        onOpenGruposModal={() => mostrarModalGrupos = true}
        onLiberarGrupoDesdeHora={liberarGrupoConAdelantos}
        onAprobarTodo={aprobarTodo}
        horariosEfectivos={horariosEfectivos}
        />
        {#if isDev}
          <div class="mt-4 p-4 rounded-xl border-2 border-dashed" style="border-color: rgb(var(--accent-primary));">
            <p class="text-xs font-medium mb-2 text-center" style="color: rgb(var(--accent-primary));">MODO DESARROLLO</p>
            <button
              onclick={() => vistaPreviaReporte = true}
              class="w-full py-2 rounded-lg font-medium transition-all"
              style="background-color: rgb(var(--accent-primary)); color: white;"
            >
              Vista previa imagen WhatsApp
            </button>
          </div>
        {/if}
      </div>
    {/if}

    {#if mostrarModalGrupos}
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
        <div class="rounded-xl p-6 w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">Liberar Grupos</h3>
            <button
              onclick={() => mostrarModalGrupos = false}
              class="w-8 h-8 flex items-center justify-center rounded-full"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));"
            >
              ✕
            </button>
          </div>

          <div class="flex-1 overflow-y-auto mb-4">
            <div class="flex gap-2 flex-wrap p-2 rounded-lg border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--bg-secondary));">
              {#each grupos as grupo}
                <div class="flex items-center gap-2 px-3 py-2 rounded hover:bg-[rgb(var(--card-bg))] min-w-[80px]">
                  <input
                    type="checkbox"
                    checked={isGrupoAusente(grupo)}
                    onchange={(e) => toggleGrupoAusente(grupo, e.currentTarget.checked, 5)}
                    class="w-5 h-5 accent-[rgb(var(--accent-primary))] shrink-0"
                  />
                  <span class="text-xs font-medium" style="color: rgb(var(--text-primary));">{grupo}</span>
                  {#if isGrupoAusente(grupo)}
                    <select
                      value={getHoraInicio(grupo)}
                      onchange={(e) => actualizarHoraInicio(grupo, parseInt(e.currentTarget.value))}
                      class="ml-1 px-1 py-0.5 text-xs rounded border"
                      style="background-color: rgb(var(--bg-primary)); color: rgb(var(--accent-primary)); border-color: rgb(var(--border-primary));"
                    >
                      <option value={1}>desde h1</option>
                      <option value={3}>desde h3</option>
                      <option value={5}>desde h5</option>
                      <option value={6}>desde h6</option>
                      <option value={7}>desde h7</option>
                    </select>
                  {/if}
                </div>
              {/each}
            </div>
          </div>

          {#if gruposAusentes.length > 0}
            <div class="mb-4 p-3 rounded-lg" style="background-color: rgb(var(--bg-secondary));">
              <p class="text-xs font-medium mb-2" style="color: rgb(var(--text-secondary));">Grupos seleccionados:</p>
              <div class="flex flex-wrap gap-2">
                {#each gruposAusentes as g}
                  <span class="px-2 py-1 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-200">
                    {g.grupo} {g.horaInicio > 1 ? `desde h${g.horaInicio}` : "desde h1"}
                  </span>
                {/each}
              </div>
            </div>
          {/if}

          <div class="flex gap-3">
            <button
              onclick={() => { gruposAusentes = []; }}
              class="flex-1 py-3 rounded-lg font-medium transition-all min-h-[48px]"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
            >
              Limpiar todo
            </button>
            <button
              onclick={() => { mostrarModalGrupos = false; if (step >= 2) recalcularCoberturas(); }}
              class="flex-1 py-3 rounded-lg font-bold text-white transition-all min-h-[48px]"
              style="background-color: rgb(var(--accent-primary));"
            >
              Listo
            </button>
          </div>
        </div>
      </div>
    {/if}

    {#if mostrarModalTipoAusencia}
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);" role="dialog" aria-modal="true" aria-labelledby="tipo-ausencia-title">
        <div class="rounded-xl p-6 w-full max-w-md" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
          <h3 id="tipo-ausencia-title" class="text-lg font-bold mb-2" style="color: rgb(var(--text-primary));">Tipo de ausencia <span style="color: #ef4444;">*</span></h3>
          <p class="text-sm mb-4" style="color: rgb(var(--text-secondary));">
            Selecciona obligatoriamente el tipo de ausencia para <strong>{docenteSeleccionado}</strong>. Si cancelas, el docente no quedará marcado como ausente.
          </p>
          <div class="grid grid-cols-2 gap-2 mb-4">
            {#each [
              { tipo: "CALAMIDAD", icono: Flame, color: "#f97316" },
              { tipo: "CAPACITACION", icono: GraduationCap, color: "#8b5cf6" },
              { tipo: "DESPLAZAMIENTO PEDAGOGICO", icono: Car, color: "#06b6d4" },
              { tipo: "FAMILIAR", icono: Heart, color: "#ec4899" },
              { tipo: "FUERZA MAYOR", icono: Shield, color: "#6366f1" },
              { tipo: "INCAPACIDAD", icono: Stethoscope, color: "#ef4444" },
              { tipo: "INTERCOLEGIADOS", icono: SportShoe, color: "#aa4466" },
              { tipo: "JURADO", icono: Scale, color: "#a16207" },
              { tipo: "LICENCIA", icono: Award, color: "#eab308" },
              { tipo: "LUTO", icono: Skull, color: "#1f2937" },
              { tipo: "MEDICO", icono: Stethoscope, color: "#10b981" },
              { tipo: "PERSONAL", icono: Briefcase, color: "#f59e0b" },
              { tipo: "QUINQUENIO", icono: Calendar, color: "#3b82f6" },
              { tipo: "REUNION", icono: Users, color: "#14b8a6" },
              { tipo: "SECRETARIA", icono: Laptop, color: "#0ea5e9" },
              { tipo: "SINDICATO", icono: Scale, color: "#78716c" },
            ] as item}
              {@const Icon = item.icono}
              <button
                onclick={() => {
                  docentesAusentes = [...docentesAusentes.filter((a) => a.nombre !== docenteSeleccionado), { nombre: docenteSeleccionado, tipo: item.tipo }];
                  mostrarModalTipoAusencia = false;
                  docenteSeleccionado = "";
                }}
                class="py-3 px-3 rounded-lg text-sm font-medium transition-all flex items-center gap-2 min-h-[48px]"
                style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
              >
                <Icon size={16} style="color: {item.color}" />
                <span class="hidden sm:inline">{item.tipo}</span>
                <span class="sm:hidden text-xs">{item.tipo.slice(0,4)}</span>
              </button>
            {/each}
          </div>
          <button
            onclick={() => { mostrarModalTipoAusencia = false; docenteSeleccionado = ""; }}
            class="w-full py-3 rounded-lg font-medium min-h-[48px]"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
          >
            Cancelar
          </button>
        </div>
      </div>
    {/if}

    {#if mostrarModalAdelantos && adelantoModalData}
      {@const dataAdel = adelantoModalData}
      {@const aplicados = dataAdel.adelantos.filter((a) => a.aplicable)}
      {@const noAplicables = dataAdel.adelantos.filter((a) => !a.aplicable)}
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);" role="dialog" aria-modal="true" aria-labelledby="adelantos-title">
        <div class="rounded-xl p-6 w-full max-w-lg max-h-[80vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
          <div class="flex justify-between items-center mb-4">
            <h3 id="adelantos-title" class="text-lg font-bold" style="color: rgb(var(--text-primary));">
              Adelanto de clases — Grupo {dataAdel.grupo}
            </h3>
            <button
              onclick={() => { mostrarModalAdelantos = false; }}
              class="w-8 h-8 flex items-center justify-center rounded-full"
              style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));"
            >
              ✕
            </button>
          </div>

          <div class="flex-1 overflow-y-auto space-y-4">
            <div class="p-3 rounded-lg" style="background-color: rgb(var(--bg-secondary));">
              <p class="text-sm" style="color: rgb(var(--text-primary));">
                El grupo <strong>{dataAdel.grupo}</strong> queda libre desde la hora <strong>{dataAdel.horaLiberada}</strong>
                porque <strong>{dataAdel.docenteAusente}</strong> está ausente y su clase de esa hora no tiene cobertura.
              </p>
            </div>

            <div>
              <p class="text-sm font-bold mb-2" style="color: rgb(var(--accent-primary));">Adelantos aplicados</p>
              {#if aplicados.length === 0}
                <p class="text-sm" style="color: rgb(var(--text-secondary));">No se aplicaron adelantos.</p>
              {:else}
                <ul class="space-y-1">
                  {#each aplicados as a}
                    <li class="text-sm flex items-start gap-2" style="color: rgb(var(--text-primary));">
                      <span style="color: #10b981;">✓</span>
                      <span><strong>{a.docente}</strong>: {a.materia} {a.grupoGrado} — adelantada de la hora {a.horaOrigen + 1} a la hora {a.horaDestino + 1}.</span>
                    </li>
                  {/each}
                </ul>
              {/if}
            </div>

            {#if noAplicables.length > 0}
              <div>
                <p class="text-sm font-bold mb-2" style="color: #ef4444;">No se pudo adelantar</p>
                <ul class="space-y-1">
                  {#each noAplicables as a}
                    <li class="text-sm flex items-start gap-2" style="color: rgb(var(--text-secondary));">
                      <span style="color: #ef4444;">✕</span>
                      <span><strong>{a.docente}</strong>: {a.materia} {a.grupoGrado} en la hora {a.horaOrigen + 1} — {a.motivoNoAplicable}.</span>
                    </li>
                  {/each}
                </ul>
              </div>
            {/if}

            {#if dataAdel.huecosPorAdelanto && dataAdel.huecosPorAdelanto.length > 0}
              <div class="p-3 rounded-lg" style="background-color: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3);">
                <p class="text-sm font-bold mb-2" style="color: #ef4444;">
                  ⚠️ Huecos libres por adelantamiento — requieren cobertura
                </p>
                <ul class="space-y-2">
                  {#each dataAdel.huecosPorAdelanto as hueco}
                    {@const docentesPosibles = hueco.docenteAusente || ""}
                    <li class="text-sm" style="color: rgb(var(--text-primary));">
                      <strong>Hora {hueco.hora + 1}</strong> — {hueco.docenteAusente}
                      <span class="text-xs block mt-0.5" style="color: rgb(var(--text-secondary));">
                        Posibles docentes: {
                          hueco.docente
                            ? (coberturasSugeridas.find(c => c.hora === hueco.hora && c.grupoAusente === hueco.grupoAusente)?.posiblesCobradores.join(", ") || "Ninguno disponible")
                            : "Ninguno disponible"
                        }
                      </span>
                    </li>
                  {/each}
                </ul>
              </div>
            {/if}
          </div>

          <button
            onclick={() => { mostrarModalAdelantos = false; }}
            class="mt-4 w-full py-2 rounded-lg font-bold text-white transition-all"
            style="background-color: rgb(var(--accent-primary));"
          >
            Entendido
          </button>
        </div>
      </div>
    {/if}
  {/if}

  {#if mostrarReporteWhatsApp}
    <WhatsAppReport
      diaSeleccionado={diaReportePDF}
      fechaSeleccionada={fechaReportePDF}
      coberturas={coberturasGuardadas}
      gruposAusentes={gruposReportePDF}
      {docentesAusentes}
      liberadosData={liberadosReportePDF}
      onClose={() => mostrarReporteWhatsApp = false}
    />
  {/if}

  {#if mostrarReportePDF}
    <WhatsAppReport
      diaSeleccionado={diaReportePDF}
      fechaSeleccionada={fechaReportePDF}
      coberturas={coberturasReportePDF}
      gruposAusentes={gruposReportePDF}
      modoPDF={true}
      liberadosData={liberadosReportePDF}
      onClose={() => mostrarReportePDF = false}
    />
  {/if}

  {#if vistaPreviaReporte}
    <WhatsAppReport
      {diaSeleccionado}
      {fechaSeleccionada}
      coberturas={coberturasSugeridas}
      {gruposAusentes}
      {docentesAusentes}
      onClose={() => vistaPreviaReporte = false}
    />
  {/if}

  {#if mostrarAyudaCoberturas}
    <CoberturasHelp
      pasoActual={step}
      onClose={handleCerrarAyuda}
    />
  {/if}

  {#if mostrarTourCobertura}
    <CoberturaTour
      pasos={tourPasos}
      onClose={cerrarTour}
      onIrAStep={irAStepTour}
      onNoMostrar={marcarTourHecho}
    />
  {/if}
</div>