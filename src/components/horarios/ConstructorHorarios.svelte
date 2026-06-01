<script lang="ts">
  import { onMount } from "svelte";
  import ModuleHeader from "../ModuleHeader.svelte";
  import { DOCENTES_URL, MATERIAS_URL, ESTUDIANTES_URL } from "../../constants";
  import { Save, Download, Upload, AlertTriangle, Users, BookOpen, GraduationCap, Plus, Trash2, ArrowLeft, CheckCircle2, XCircle, GripVertical, Sparkles, ListChecks } from "@lucide/svelte";
  import Swal from "sweetalert2";
  import {
    derivarCarga,
    totalesPorDocente,
    validarCarga,
    generarAutomatico as generarAutomaticoLib,
    type CargaItem,
    type GenConfig,
    type GenResult,
  } from "../../lib/horarioGenerator";

  let { onBack }: { onBack: () => void } = $props();

  // -------- Tipos --------
  type Vista = "grupo" | "docente" | "carga";
  type Celda = { materia: string; docente: string };

  type HorarioGrupoMatrix = Record<DiaKey, Celda[]>;

  type DocenteConfig = {
    nombre: string;
    cargaMin: number;
    horasExtra: number;
  };

  const DIAS = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
  type DiaKey = (typeof DIAS)[number];
  const DIAS_ABREV: Record<DiaKey, string> = { lunes: "LUN", martes: "MAR", miercoles: "MIE", jueves: "JUE", viernes: "VIE" };
  const HORAS_DIA = 7;
  const STORAGE_KEY = "constructorHorarios_v1";

  // -------- Estado --------
  let cargando = $state(false);
  let docentesBackend = $state<string[]>([]);
  let materiasBackend = $state<string[]>([]);
  let gruposBackend = $state<string[]>([]);

  // Catálogos locales editables
  let docentesLocales = $state<string[]>([]);
  let materiasLocales = $state<string[]>([]);
  let gruposLocales = $state<string[]>([]);

  // Config docente
  let configDocentes = $state<Record<string, DocenteConfig>>({});

  let vista = $state<Vista>("grupo");
  let grupoActivo = $state<string>("");
  let docenteActivo = $state<string>("");

  // Matriz de horario por grupo
  let horario = $state<Record<string, HorarioGrupoMatrix>>({});

  // Carga académica + generación automática
  let carga = $state<CargaItem[]>([]);
  let genResult = $state<GenResult | null>(null);
  let generando = $state(false);
  let anioActivo = $state<number>(new Date().getFullYear());

  // Modal celda
  let modalAbierto = $state(false);
  let modalCtx = $state<{ grupo: string; dia: DiaKey; hora: number } | null>(null);
  let modalMateria = $state("");
  let modalDocente = $state("");
  let modalEsExtra = $state(false);

  // -------- Catálogos combinados --------
  const todosDocentes = $derived([...docentesBackend, ...docentesLocales].sort((a, b) => a.localeCompare(a, "es")));
  const todasMaterias = $derived([...materiasBackend, ...materiasLocales].sort((a, b) => a.localeCompare(b, "es")));
  const todosGrupos = $derived(
    [...gruposBackend, ...gruposLocales].sort((a, b) => {
      const na = parseInt(a) || 0;
      const nb = parseInt(b) || 0;
      return na - nb || a.localeCompare(b);
    })
  );

  // -------- Persistencia --------
  function guardarStorage() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        horario,
        configDocentes,
        docentesLocales,
        materiasLocales,
        gruposLocales,
        anioActivo,
      }));
    } catch {}
  }

  function cargarStorage() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const p = JSON.parse(raw);
      horario = p.horario || {};
      configDocentes = p.configDocentes || {};
      docentesLocales = p.docentesLocales || [];
      materiasLocales = p.materiasLocales || [];
      gruposLocales = p.gruposLocales || [];
      if (typeof p.anioActivo === "number") anioActivo = p.anioActivo;
    } catch {}
  }

  $effect(() => {
    horario; configDocentes; docentesLocales; materiasLocales; gruposLocales; anioActivo;
    guardarStorage();
  });

  // -------- Cargar desde backend --------
  async function cargarCatalogo() {
    cargando = true;
    try {
      const [rDoc, rMat, rEst] = await Promise.all([
        fetch(DOCENTES_URL),
        fetch(MATERIAS_URL),
        fetch(ESTUDIANTES_URL),
      ]);
      const datosDoc = await rDoc.json();
      const datosMat = await rMat.json();
      const datosEst = await rEst.json();

      docentesBackend = Array.isArray(datosDoc) ? datosDoc : [];
      materiasBackend = Array.isArray(datosMat) ? datosMat.map((m: { materia: string }) => m.materia) : [];

      const gruposSet = new Set<string>();
      for (const e of datosEst) if (e.grado) gruposSet.add(String(e.grado));
      gruposBackend = Array.from(gruposSet);

      for (const d of todosDocentes) {
        if (!configDocentes[d]) {
          configDocentes[d] = { nombre: d, cargaMin: 22, horasExtra: 0 };
        }
      }
    } catch {
      Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar el catálogo del servidor", confirmButtonColor: "#ef4444" });
    } finally {
      cargando = false;
    }
  }

  // -------- Matriz helpers --------
  function celdaVacia(): Celda {
    return { materia: "", docente: "" };
  }

  function asegurarGrupo(g: string) {
    if (horario[g]) return;
    horario[g] = { lunes: [], martes: [], miercoles: [], jueves: [], viernes: [] };
    for (const d of DIAS) horario[g][d] = Array.from({ length: HORAS_DIA }, celdaVacia);
    horario = { ...horario };
  }

  function getCelda(g: string, d: DiaKey, h: number): Celda {
    return horario[g]?.[d]?.[h] ?? celdaVacia();
  }

  function setCelda(g: string, d: DiaKey, h: number, c: Celda) {
    asegurarGrupo(g);
    horario[g][d][h] = c;
    horario = { ...horario };
  }

  // -------- Horario por docente --------
  type HorarioDocentePlano = {
    docente: string;
    lunes: string[];
    martes: string[];
    miercoles: string[];
    jueves: string[];
    viernes: string[];
  };

  const horarioDocentePlano = $derived.by<Record<string, HorarioDocentePlano>>(() => {
    const map: Record<string, HorarioDocentePlano> = {};
    for (const d of todosDocentes) {
      map[d] = { docente: d, lunes: Array(HORAS_DIA).fill(""), martes: Array(HORAS_DIA).fill(""), miercoles: Array(HORAS_DIA).fill(""), jueves: Array(HORAS_DIA).fill(""), viernes: Array(HORAS_DIA).fill("") };
    }
    for (const [grupo, dias] of Object.entries(horario)) {
      for (const dia of DIAS) {
        for (let h = 0; h < HORAS_DIA; h++) {
          const c = dias[dia][h];
          if (!c.docente) continue;
          if (!map[c.docente]) {
            map[c.docente] = { docente: c.docente, lunes: Array(HORAS_DIA).fill(""), martes: Array(HORAS_DIA).fill(""), miercoles: Array(HORAS_DIA).fill(""), jueves: Array(HORAS_DIA).fill(""), viernes: Array(HORAS_DIA).fill("") };
          }
          const texto = c.materia ? `${c.materia} ${grupo}` : grupo;
          map[c.docente][dia][h] = texto;
        }
      }
    }
    return map;
  });

  // -------- Validaciones --------
  type Conflicto = {
    tipo: "doble" | "carga_excedida" | "grupo_incompleto";
    grupo?: string;
    docente?: string;
    dia?: DiaKey;
    hora?: number;
    mensaje: string;
  };

  const conflictos = $derived.by<Conflicto[]>(() => {
    const lista: Conflicto[] = [];

    // 1) Docente en 2 grupos misma hora
    for (const dia of DIAS) {
      for (let h = 0; h < HORAS_DIA; h++) {
        const docPorGrupo = new Map<string, string[]>();
        for (const [grupo, fila] of Object.entries(horario)) {
          const c = fila[dia][h];
          if (!c.docente) continue;
          const arr = docPorGrupo.get(c.docente) ?? [];
          arr.push(grupo);
          docPorGrupo.set(c.docente, arr);
        }
        for (const [doc, grupos] of docPorGrupo.entries()) {
          if (grupos.length > 1) {
            lista.push({
              tipo: "doble",
              docente: doc,
              dia,
              hora: h,
              mensaje: `${doc} asignado a ${grupos.join(", ")} el ${dia} hora ${h + 1}`,
            });
          }
        }
      }
    }

    // 2) Carga docente
    for (const [doc, fila] of Object.entries(horarioDocentePlano)) {
      let total = 0;
      for (const d of DIAS) {
        for (const s of fila[d]) if (s) total++;
      }
      const cfg = configDocentes[doc];
      if (!cfg) continue;
      const tope = cfg.cargaMin + (cfg.horasExtra || 0);
      if (total > tope) {
        lista.push({
          tipo: "carga_excedida",
          docente: doc,
          mensaje: `${doc}: ${total}h > máximo ${cfg.cargaMin} + ${cfg.horasExtra} extra`,
        });
      }
    }

    // 3) Grupo incompleto
    for (const [grupo, fila] of Object.entries(horario)) {
      let vacias = 0;
      for (const d of DIAS) for (const c of fila[d]) if (!c.materia || !c.docente) vacias++;
      if (vacias > 0) {
        lista.push({
          tipo: "grupo_incompleto",
          grupo,
          mensaje: `Grupo ${grupo}: ${vacias} celda(s) sin asignar`,
        });
      }
    }

    return lista;
  });

  const conflictosPorTipo = $derived(() => {
    const t = { doble: 0, carga_excedida: 0, grupo_incompleto: 0 };
    for (const c of conflictos) t[c.tipo]++;
    return t;
  });

  function celdaEnConflicto(g: string, d: DiaKey, h: number): boolean {
    const c = getCelda(g, d, h);
    if (!c.docente) return false;
    return conflictos.some((x) => x.tipo === "doble" && x.docente === c.docente && x.dia === d && x.hora === h);
  }

  function horasGrupo(g: string): number {
    const fila = horario[g];
    if (!fila) return 0;
    let t = 0;
    for (const d of DIAS) for (const c of fila[d]) if (c.materia && c.docente) t++;
    return t;
  }

  function horasDocente(doc: string): number {
    const fila = horarioDocentePlano[doc];
    if (!fila) return 0;
    let t = 0;
    for (const d of DIAS) for (const s of fila[d]) if (s) t++;
    return t;
  }

  // -------- Modal celda --------
  function abrirCelda(g: string, d: DiaKey, h: number) {
    asegurarGrupo(g);
    const c = getCelda(g, d, h);
    modalCtx = { grupo: g, dia: d, hora: h };
    modalMateria = c.materia;
    modalDocente = c.docente;
    modalEsExtra = false;
    modalAbierto = true;
  }

  function docentesDisponibles(g: string, d: DiaKey, h: number): string[] {
    const ocupados = new Set<string>();
    for (const [grupo, fila] of Object.entries(horario)) {
      if (grupo === g) continue;
      const c = fila[d][h];
      if (c.docente) ocupados.add(c.docente);
    }
    return todosDocentes.filter((d) => !ocupados.has(d));
  }

  function guardarCelda() {
    if (!modalCtx) return;
    setCelda(modalCtx.grupo, modalCtx.dia, modalCtx.hora, { materia: modalMateria, docente: modalDocente });
    if (modalEsExtra && modalDocente) {
      const cfg = configDocentes[modalDocente] ?? { nombre: modalDocente, cargaMin: 22, horasExtra: 0 };
      cfg.horasExtra = (cfg.horasExtra || 0) + 1;
      configDocentes = { ...configDocentes, [modalDocente]: cfg };
    }
    modalAbierto = false;
    modalCtx = null;
  }

  function limpiarCelda() {
    if (!modalCtx) return;
    setCelda(modalCtx.grupo, modalCtx.dia, modalCtx.hora, celdaVacia());
    modalAbierto = false;
    modalCtx = null;
  }

  // -------- CRUD catálogos --------
  async function agregar(tipo: "docente" | "materia" | "grupo") {
    const titulos = { docente: "Nuevo docente", materia: "Nueva asignatura", grupo: "Nuevo grupo" };
    const { value } = await Swal.fire({
      title: titulos[tipo],
      input: "text",
      inputAttributes: { autocapitalize: "characters" },
      showCancelButton: true,
      confirmButtonText: "Agregar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "rgb(99,102,241)",
    });
    if (!value) return;
    const v = String(value).trim().toUpperCase();
    if (!v) return;
    if (tipo === "docente") {
      if (todosDocentes.includes(v)) return;
      docentesLocales = [...docentesLocales, v];
      configDocentes = { ...configDocentes, [v]: { nombre: v, cargaMin: 22, horasExtra: 0 } };
    } else if (tipo === "materia") {
      if (todasMaterias.includes(v)) return;
      materiasLocales = [...materiasLocales, v];
    } else {
      if (todosGrupos.includes(v)) return;
      gruposLocales = [...gruposLocales, v];
    }
  }

  async function eliminar(tipo: "docente" | "materia" | "grupo", valor: string) {
    const r = await Swal.fire({
      title: "¿Eliminar?",
      text: `Eliminar ${valor}. Las asignaciones existentes quedarán vacías.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
    });
    if (!r.isConfirmed) return;

    if (tipo === "docente") {
      docentesLocales = docentesLocales.filter((d) => d !== valor);
      const next = { ...configDocentes };
      delete next[valor];
      configDocentes = next;
      for (const g of Object.keys(horario)) {
        for (const d of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            if (horario[g][d][h].docente === valor) horario[g][d][h] = celdaVacia();
          }
        }
      }
      horario = { ...horario };
      if (docenteActivo === valor) docenteActivo = "";
    } else if (tipo === "materia") {
      materiasLocales = materiasLocales.filter((a) => a !== valor);
      for (const g of Object.keys(horario)) {
        for (const d of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            if (horario[g][d][h].materia === valor) horario[g][d][h] = { ...horario[g][d][h], materia: "" };
          }
        }
      }
      horario = { ...horario };
    } else {
      gruposLocales = gruposLocales.filter((g) => g !== valor);
      const next = { ...horario };
      delete next[valor];
      horario = next;
      if (grupoActivo === valor) grupoActivo = "";
    }
  }

  // -------- Carga académica + generación automática --------
  function construirGenConfig(): GenConfig {
    const cfg: Record<string, { horasExtra: number }> = {};
    for (const [doc, c] of Object.entries(configDocentes)) {
      cfg[doc] = { horasExtra: c.horasExtra || 0 };
    }
    // Asegurar que todos los docentes con carga tengan entrada
    for (const item of carga) {
      if (!cfg[item.docente]) cfg[item.docente] = { horasExtra: 0 };
    }
    return { horasDia: HORAS_DIA, dias: DIAS, cfgDocentes: cfg };
  }

  const totalesCarga = $derived.by(() => totalesPorDocente(carga, construirGenConfig()));

  function derivarCargaDesdeHorario() {
    const filas = Object.values(horarioDocentePlano);
    const nueva = derivarCarga(filas, DIAS);
    if (nueva.length === 0) {
      Swal.fire({ icon: "info", title: "Sin datos", text: "No hay horario cargado para derivar la carga. Importa un horarios.json o asigna clases primero.", confirmButtonColor: "rgb(99,102,241)" });
      return;
    }
    carga = nueva;
    vista = "carga";
  }

  function editarHorasCarga(i: number, horas: number) {
    const v = Math.max(0, Math.floor(horas) || 0);
    carga = carga.map((c, idx) => (idx === i ? { ...c, horas: v } : c));
  }

  function editarCampoCarga(i: number, campo: "docente" | "area" | "grupo", valor: string) {
    carga = carga.map((c, idx) => (idx === i ? { ...c, [campo]: valor } : c));
  }

  function agregarFilaCarga() {
    carga = [...carga, { docente: todosDocentes[0] ?? "", area: todasMaterias[0] ?? "", grupo: todosGrupos[0] ?? "", horas: 1 }];
  }

  function quitarFilaCarga(i: number) {
    carga = carga.filter((_, idx) => idx !== i);
  }

  async function generarAutomatico() {
    if (generando) return;
    if (carga.length === 0) {
      Swal.fire({ icon: "warning", title: "Sin carga", text: "Deriva o agrega la carga académica antes de generar.", confirmButtonColor: "#ef4444" });
      return;
    }

    const cfg = construirGenConfig();
    const previos = validarCarga(carga, cfg);
    if (previos.length > 0) {
      const r = await Swal.fire({
        icon: "warning",
        title: "Carga con avisos",
        html: `<div style="text-align:left;font-size:13px;">${previos.map((c) => `• ${c.mensaje}`).join("<br>")}</div><br>Corrige en la tabla de Carga o continúa para generar parcialmente.`,
        showCancelButton: true,
        confirmButtonText: "Generar igual",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#ef4444",
      });
      if (!r.isConfirmed) return;
    }

    const hayDatos = Object.keys(horario).length > 0;
    if (hayDatos) {
      const r = await Swal.fire({
        icon: "question",
        title: "¿Sobrescribir horario?",
        text: "Se reemplazará el horario actual con el generado automáticamente.",
        showCancelButton: true,
        confirmButtonText: "Sobrescribir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#ef4444",
      });
      if (!r.isConfirmed) return;
    }

    generando = true;
    // Permitir que el spinner pinte antes del cálculo síncrono pesado.
    await new Promise((res) => setTimeout(res, 30));
    try {
      const res = generarAutomaticoLib(carga, cfg);

      // Volcar la matriz generada
      const next: Record<string, HorarioGrupoMatrix> = {};
      for (const [grupo, dias] of Object.entries(res.horario)) {
        next[grupo] = { lunes: [], martes: [], miercoles: [], jueves: [], viernes: [] };
        for (const d of DIAS) {
          next[grupo][d] = Array.from({ length: HORAS_DIA }, (_, h) => {
            const c = dias[d]?.[h];
            return c ? { materia: c.materia, docente: c.docente } : celdaVacia();
          });
        }
      }
      horario = next;
      genResult = res;

      const resumen = res.ok
        ? `Horario generado sin pendientes en ${res.ms} ms.`
        : `Generado con ${res.noAsignados.length} asignación(es) pendiente(s). Revisa el panel inferior y ajusta manualmente.`;
      await Swal.fire({
        icon: res.ok ? "success" : "warning",
        title: res.ok ? "Horario generado" : "Generado parcialmente",
        text: resumen,
        confirmButtonColor: "rgb(99,102,241)",
      });
    } catch (e) {
      console.error(e);
      Swal.fire({ icon: "error", title: "Error", text: "No se pudo generar el horario", confirmButtonColor: "#ef4444" });
    } finally {
      generando = false;
    }
  }

  // -------- Exportar JSON --------
  function exportarJSON() {
    // Clonar el plano e inyectar los slots DESC/PEDAG del último resultado generado.
    const planoBase = horarioDocentePlano;
    const filas: HorarioDocentePlano[] = Object.values(planoBase).map((f) => ({
      docente: f.docente,
      lunes: [...f.lunes],
      martes: [...f.martes],
      miercoles: [...f.miercoles],
      jueves: [...f.jueves],
      viernes: [...f.viernes],
    }));

    if (genResult?.descPedag) {
      const porDocente = new Map(filas.map((f) => [f.docente, f]));
      for (const [docente, slots] of Object.entries(genResult.descPedag)) {
        const fila = porDocente.get(docente);
        if (!fila) continue;
        // slots viene en orden de inserción del generador: [DESC, PEDAG] por par.
        slots.forEach((s, idx) => {
          const dia = s.dia as DiaKey;
          if (fila[dia] && fila[dia][s.hora] === "") {
            fila[dia][s.hora] = idx % 2 === 0 ? "DESC" : "PEDAG";
          }
        });
      }
    }

    const filtradas = filas.filter((f) => DIAS.some((d) => f[d].some((s) => s !== "")));
    const blob = new Blob([JSON.stringify(filtradas, null, 2)], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `horarios_${anioActivo}.json`;
    a.click();
    URL.revokeObjectURL(url);
  }

  async function importarJSON(ev: Event) {
    const file = (ev.target as HTMLInputElement).files?.[0];
    if (!file) return;
    try {
      const txt = await file.text();
      const filas = JSON.parse(txt) as HorarioDocentePlano[];
      if (!Array.isArray(filas)) throw new Error("Formato inválido");

      const next: Record<string, HorarioGrupoMatrix> = {};
      for (const fila of filas) {
        for (const dia of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            const slot = fila[dia][h];
            if (!slot) continue;
            const m = slot.match(/(\d{3,4})$/);
            const grupo = m ? m[1] : "";
            if (!grupo) continue;
            const materia = slot.replace(/\s*\d{3,4}$/, "").trim();
            if (!next[grupo]) {
              next[grupo] = { lunes: [], martes: [], miercoles: [], jueves: [], viernes: [] };
              for (const d of DIAS) next[grupo][d] = Array.from({ length: HORAS_DIA }, celdaVacia);
            }
            next[grupo][dia][h] = { materia, docente: fila.docente };
          }
        }
      }
      horario = next;
      Swal.fire({ icon: "success", title: "Importado", text: `${filas.length} docentes cargados`, confirmButtonColor: "rgb(99,102,241)" });
    } catch {
      Swal.fire({ icon: "error", title: "Error", text: "No se pudo leer el archivo JSON" });
    } finally {
      (ev.target as HTMLInputElement).value = "";
    }
  }

  async function resetTodo() {
    const r = await Swal.fire({
      title: "¿Borrar todo?",
      text: "Elimina horarios y catálogos locales. Acción irreversible.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      confirmButtonText: "Borrar",
      cancelButtonText: "Cancelar",
    });
    if (!r.isConfirmed) return;
    horario = {};
    configDocentes = {};
    docentesLocales = [];
    materiasLocales = [];
    gruposLocales = [];
    grupoActivo = "";
    docenteActivo = "";
    localStorage.removeItem(STORAGE_KEY);
  }

  // -------- Inicializar matriz --------
  function inicializarGrupo(g: string) {
    asegurarGrupo(g);
    grupoActivo = g;
  }

  onMount(() => {
    cargarStorage();
    cargarCatalogo();
  });
</script>

<div class="flex flex-col h-full">
  <ModuleHeader title="Constructor de Horarios" {onBack} />

  <div class="flex-1 overflow-auto p-4">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-3 mb-4 p-3 rounded-xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
      <div class="flex gap-2">
        <button
          onclick={() => { vista = "grupo"; docenteActivo = ""; }}
          class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
          style="background-color: {vista === 'grupo' ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {vista === 'grupo' ? 'white' : 'rgb(var(--text-primary))'};"
        >
          <Users size={14} class="inline mr-1" />Por Grupo
        </button>
        <button
          onclick={() => { vista = "docente"; grupoActivo = ""; }}
          class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
          style="background-color: {vista === 'docente' ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {vista === 'docente' ? 'white' : 'rgb(var(--text-primary))'};"
        >
          <GraduationCap size={14} class="inline mr-1" />Por Docente
        </button>
        <button
          onclick={() => { vista = "carga"; }}
          class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
          style="background-color: {vista === 'carga' ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {vista === 'carga' ? 'white' : 'rgb(var(--text-primary))'};"
        >
          <ListChecks size={14} class="inline mr-1" />Carga Académica
        </button>
      </div>

      <div class="flex-1"></div>

      <div class="flex flex-wrap gap-2 items-center">
        <label class="flex items-center gap-1 text-xs" style="color: rgb(var(--text-secondary));" title="Año del horario (se usa en el nombre del archivo exportado)">
          Año
          <input
            type="number"
            min="2020"
            max="2100"
            bind:value={anioActivo}
            class="w-20 px-1 py-1 rounded border text-xs"
            style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
          />
        </label>
        <button
          onclick={derivarCargaDesdeHorario}
          class="px-3 py-2 rounded-lg text-xs font-medium border transition-all flex items-center gap-1"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
          title="Derivar la carga académica desde el horario cargado"
        >
          <ListChecks size={12} />Derivar carga
        </button>
        <button
          onclick={generarAutomatico}
          disabled={generando}
          class="px-3 py-2 rounded-lg text-xs font-bold text-white transition-all flex items-center gap-1 disabled:opacity-50"
          style="background-color: #8b5cf6;"
          title="Generar el horario automáticamente desde la carga académica"
        >
          <Sparkles size={12} />{generando ? "Generando..." : "Generar automático"}
        </button>
        <button
          onclick={cargarCatalogo}
          disabled={cargando}
          class="px-3 py-2 rounded-lg text-xs font-medium border transition-all disabled:opacity-50"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
        >
          {cargando ? "Cargando..." : "↻ Recargar catálogo"}
        </button>
        <button
          onclick={exportarJSON}
          class="px-3 py-2 rounded-lg text-xs font-medium text-white transition-all flex items-center gap-1"
          style="background-color: rgb(var(--accent-primary));"
        >
          <Download size={12} />Exportar JSON
        </button>
        <label class="px-3 py-2 rounded-lg text-xs font-medium border cursor-pointer transition-all flex items-center gap-1" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));">
          <Upload size={12} />Importar
          <input type="file" accept="application/json" onchange={importarJSON} class="hidden" />
        </label>
        <button
          onclick={resetTodo}
          class="px-3 py-2 rounded-lg text-xs font-medium text-white transition-all"
          style="background-color: #ef4444;"
        >
          🗑️ Reset
        </button>
      </div>
    </div>

    <!-- Aviso de conflictos -->
    {#if conflictos.length > 0}
      <div class="mb-4 p-3 rounded-xl border-2 flex flex-wrap items-center gap-3" style="border-color: #f59e0b; background-color: rgba(245,158,11,0.08);">
        <AlertTriangle size={18} style="color: #b45309;" />
        <span class="text-sm font-bold" style="color: #b45309;">{conflictos.length} aviso(s):</span>
        <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(239,68,68,0.15); color: #b91c1c;">{conflictosPorTipo().doble} doble asignación</span>
        <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(245,158,11,0.18); color: #92400e;">{conflictosPorTipo().carga_excedida} carga excedida</span>
        <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(59,130,246,0.18); color: #1e40af;">{conflictosPorTipo().grupo_incompleto} grupo(s) incompleto(s)</span>
        <details class="w-full mt-1">
          <summary class="text-xs cursor-pointer" style="color: rgb(var(--text-secondary));">Ver detalle</summary>
          <ul class="mt-2 space-y-1 text-xs" style="color: rgb(var(--text-secondary));">
            {#each conflictos as c}
              <li>• {c.mensaje}</li>
            {/each}
          </ul>
        </details>
      </div>
    {/if}

    <!-- Panel de no-asignados de la última generación -->
    {#if genResult && genResult.noAsignados.length > 0}
      <div class="mb-4 p-3 rounded-xl border-2" style="border-color: #ef4444; background-color: rgba(239,68,68,0.06);">
        <div class="flex items-center gap-2 mb-2">
          <XCircle size={18} style="color: #b91c1c;" />
          <span class="text-sm font-bold" style="color: #b91c1c;">
            {genResult.noAsignados.length} asignación(es) sin ubicar (ajusta manualmente en Vista Grupo)
          </span>
        </div>
        <ul class="space-y-1 text-xs max-h-40 overflow-y-auto" style="color: rgb(var(--text-secondary));">
          {#each genResult.noAsignados as na}
            <li>
              • {na.docente}
              {#if na.area}— {na.area} {na.grupo}{/if}
              {#if na.area === ""}— DESC/PEDAG{/if}
              : faltan {na.horasFaltantes}h
              <span style="opacity:0.7;">({na.motivo})</span>
            </li>
          {/each}
        </ul>
      </div>
    {/if}

    {#if vista === "carga"}
      <!-- Vista Carga Académica -->
      <div class="rounded-xl border p-4" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
          <h2 class="text-lg font-bold flex items-center gap-2" style="color: rgb(var(--text-primary));">
            <ListChecks size={18} /> Carga Académica ({carga.length} asignaciones)
          </h2>
          <div class="flex gap-2">
            <button onclick={agregarFilaCarga} class="px-3 py-1.5 rounded-lg text-xs font-medium text-white flex items-center gap-1" style="background-color: rgb(var(--accent-primary));">
              <Plus size={12} /> Agregar fila
            </button>
          </div>
        </div>

        {#if carga.length === 0}
          <p class="text-center py-8 text-sm" style="color: rgb(var(--text-secondary));">
            No hay carga académica. Usa <strong>"Derivar carga"</strong> (desde un horario importado) o <strong>"Agregar fila"</strong>.
          </p>
        {:else}
          <div class="overflow-x-auto">
            <table class="w-full text-xs" style="border-collapse: collapse;">
              <thead>
                <tr style="background-color: rgb(var(--bg-secondary));">
                  <th class="p-2 text-left" style="color: rgb(var(--text-primary));">Docente</th>
                  <th class="p-2 text-left" style="color: rgb(var(--text-primary));">Área</th>
                  <th class="p-2 text-left" style="color: rgb(var(--text-primary));">Grupo</th>
                  <th class="p-2 text-center" style="color: rgb(var(--text-primary));">Horas/sem</th>
                  <th class="p-2 w-10"></th>
                </tr>
              </thead>
              <tbody>
                {#each carga as item, i}
                  <tr style="border-bottom: 1px solid rgb(var(--border-primary));">
                    <td class="p-1">
                      <select
                        value={item.docente}
                        onchange={(e) => editarCampoCarga(i, "docente", e.currentTarget.value)}
                        class="w-full px-1 py-1 rounded border text-xs"
                        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                      >
                        {#each todosDocentes as d}
                          <option value={d}>{d}</option>
                        {/each}
                        {#if !todosDocentes.includes(item.docente)}
                          <option value={item.docente}>{item.docente}</option>
                        {/if}
                      </select>
                    </td>
                    <td class="p-1">
                      <select
                        value={item.area}
                        onchange={(e) => editarCampoCarga(i, "area", e.currentTarget.value)}
                        class="w-full px-1 py-1 rounded border text-xs"
                        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                      >
                        {#each todasMaterias as m}
                          <option value={m}>{m}</option>
                        {/each}
                        {#if !todasMaterias.includes(item.area)}
                          <option value={item.area}>{item.area}</option>
                        {/if}
                      </select>
                    </td>
                    <td class="p-1">
                      <select
                        value={item.grupo}
                        onchange={(e) => editarCampoCarga(i, "grupo", e.currentTarget.value)}
                        class="w-full px-1 py-1 rounded border text-xs"
                        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                      >
                        {#each todosGrupos as g}
                          <option value={g}>{g}</option>
                        {/each}
                        {#if !todosGrupos.includes(item.grupo)}
                          <option value={item.grupo}>{item.grupo}</option>
                        {/if}
                      </select>
                    </td>
                    <td class="p-1 text-center">
                      <input
                        type="number"
                        min="0"
                        max="35"
                        value={item.horas}
                        oninput={(e) => editarHorasCarga(i, parseInt(e.currentTarget.value || "0"))}
                        class="w-16 px-1 py-1 rounded border text-xs text-center"
                        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                      />
                    </td>
                    <td class="p-1 text-center">
                      <button onclick={() => quitarFilaCarga(i)} style="color: #ef4444;" title="Quitar fila">
                        <Trash2 size={12} />
                      </button>
                    </td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>

          <!-- Totales por docente -->
          <div class="mt-4">
            <h3 class="text-sm font-bold mb-2" style="color: rgb(var(--text-primary));">Totales por docente</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
              {#each Object.entries(totalesCarga).sort((a, b) => a[0].localeCompare(b[0], "es")) as [doc, t]}
                {@const excede = t.total > t.tope}
                <div class="px-2 py-1.5 rounded text-xs" style="background-color: rgb(var(--bg-secondary)); border: 1px solid {excede ? '#ef4444' : 'rgb(var(--border-primary))'};">
                  <div class="truncate font-medium" style="color: rgb(var(--text-primary));" title={doc}>{doc}</div>
                  <div style="color: {excede ? '#ef4444' : 'rgb(var(--text-secondary))'};">
                    {t.horasClase}h clase{t.descPedag ? " + 2 D/P" : ""} / tope {t.tope}
                    {#if excede}<span class="font-bold"> ⚠</span>{/if}
                  </div>
                </div>
              {/each}
            </div>
          </div>
        {/if}
      </div>
    {:else}
    <!-- Layout: catálogo + matriz -->
    <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-4">
      <!-- Panel izquierdo: catálogos -->
      <div class="rounded-xl border p-4 space-y-4" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
        {#if vista === "grupo"}
          <section>
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-bold flex items-center gap-1" style="color: rgb(var(--text-primary));">
                <Users size={14} /> Grupos
              </h3>
              <button onclick={() => agregar("grupo")} class="p-1 rounded" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar grupo">
                <Plus size={12} />
              </button>
            </div>
            <ul class="space-y-1 max-h-[35vh] overflow-y-auto">
              {#each todosGrupos as g}
                {@const hrs = horasGrupo(g)}
                {@const incompleto = hrs < 35}
                <li class="flex items-center gap-1">
                  <button
                    onclick={() => { grupoActivo = grupoActivo === g ? "" : g; }}
                    class="flex-1 text-left px-2 py-1.5 rounded text-xs transition-all flex items-center justify-between"
                    style="background-color: {grupoActivo === g ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {grupoActivo === g ? 'white' : 'rgb(var(--text-primary))'};"
                  >
                    <span>{g}</span>
                    <span class="text-[10px] {incompleto ? 'font-bold' : ''}" style={incompleto ? (grupoActivo === g ? 'color:rgba(255,255,255,0.7)' : 'color:#f59e0b') : ''}>
                      {hrs}/35h
                    </span>
                  </button>
                  {#if gruposLocales.includes(g)}
                    <button onclick={() => eliminar("grupo", g)} style="color: #ef4444;" title="Eliminar">
                      <Trash2 size={11} />
                    </button>
                  {/if}
                </li>
              {/each}
            </ul>
          </section>
        {:else}
          <section>
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-bold flex items-center gap-1" style="color: rgb(var(--text-primary));">
                <GraduationCap size={14} /> Docentes
              </h3>
              <button onclick={() => agregar("docente")} class="p-1 rounded" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar docente">
                <Plus size={12} />
              </button>
            </div>
            <ul class="space-y-1 max-h-[35vh] overflow-y-auto">
              {#each todosDocentes as d}
                {@const hrs = horasDocente(d)}
                {@const cfg = configDocentes[d]}
                {@const tope = (cfg?.cargaMin ?? 22) + (cfg?.horasExtra ?? 0)}
                {@const excedido = hrs > tope}
                <li class="flex items-center gap-1">
                  <button
                    onclick={() => { docenteActivo = docenteActivo === d ? "" : d; }}
                    class="flex-1 text-left px-2 py-1.5 rounded text-xs transition-all"
                    style="background-color: {docenteActivo === d ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {docenteActivo === d ? 'white' : 'rgb(var(--text-primary))'};"
                    title={d}
                  >
                    <div class="truncate">{d}</div>
                    <div class="text-[10px] {excedido ? 'font-bold' : 'opacity-70'}" style={excedido ? 'color:#ef4444' : ''}>
                      {hrs}/{tope}h cfg. {cfg?.horasExtra ? `+${cfg.horasExtra}` : ""}
                    </div>
                  </button>
                  {#if docentesLocales.includes(d)}
                    <button onclick={() => eliminar("docente", d)} style="color: #ef4444;" title="Eliminar">
                      <Trash2 size={11} />
                    </button>
                  {/if}
                </li>
              {/each}
            </ul>
          </section>
        {/if}

        <!-- Asignaturas -->
        <section>
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold flex items-center gap-1" style="color: rgb(var(--text-primary));">
              <BookOpen size={14} /> Asignaturas
            </h3>
            <button onclick={() => agregar("materia")} class="p-1 rounded" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar asignatura">
              <Plus size={12} />
            </button>
          </div>
          <ul class="space-y-1 max-h-[20vh] overflow-y-auto">
            {#each todasMaterias as m}
              <li class="flex items-center gap-1 px-2 py-1 rounded text-xs" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));">
                <span class="flex-1 truncate" title={m}>{m}</span>
                {#if materiasLocales.includes(m)}
                  <button onclick={() => eliminar("materia", m)} style="color: #ef4444;">
                    <Trash2 size={10} />
                  </button>
                {/if}
              </li>
            {/each}
          </ul>
        </section>

        <!-- Resumen global -->
        <div class="p-3 rounded-lg text-xs" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary));">
          <div class="font-semibold mb-1">Resumen</div>
          <div>Grupos: {todosGrupos.length}</div>
          <div>Docentes: {todosDocentes.length}</div>
          <div>Asignaturas: {todasMaterias.length}</div>
        </div>
      </div>

      <!-- Panel derecho: matriz -->
      <div class="rounded-xl border p-4" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
        {#if vista === "grupo"}
          {#if !grupoActivo}
            <p class="text-center py-8 text-sm" style="color: rgb(var(--text-secondary));">
              Selecciona un grupo para ver y editar su horario.
            </p>
          {:else}
            {@const fila = horario[grupoActivo]}
            {@const hrs = horasGrupo(grupoActivo)}
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
              <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
                Grupo {grupoActivo}
              </h2>
              <div class="flex items-center gap-2">
                <span class="text-sm px-3 py-1 rounded font-bold" style="background-color: {hrs >= 35 ? 'rgba(16,185,129,0.15)' : 'rgba(245,158,11,0.15)'}; color: {hrs >= 35 ? '#065f46' : '#92400e'};">
                  {hrs}/35 horas
                </span>
                {#if hrs >= 35}
                  <CheckCircle2 size={16} style="color: #059669;" />
                {:else}
                  <XCircle size={16} style="color: #d97706;" />
                {/if}
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-xs" style="border-collapse: collapse;">
                <thead>
                  <tr style="background-color: rgb(var(--bg-secondary));">
                    <th class="p-2" style="color: rgb(var(--text-primary));">HORA</th>
                    {#each DIAS as d}
                      <th class="p-2" style="color: rgb(var(--text-primary));">{DIAS_ABREV[d]}</th>
                    {/each}
                  </tr>
                </thead>
                <tbody>
                  {#each Array(HORAS_DIA) as _, h}
                    <tr>
                      <td class="p-2 text-center font-bold" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--border-primary));">{h + 1}</td>
                      {#each DIAS as d}
                        {@const c = getCelda(grupoActivo, d, h)}
                        {@const conflict = celdaEnConflicto(grupoActivo, d, h)}
                        <td class="p-1" style="border: 1px solid rgb(var(--border-primary)); background-color: {conflict ? 'rgba(239,68,68,0.08)' : 'transparent'};">
                          <button
                            onclick={() => abrirCelda(grupoActivo, d, h)}
                            class="w-full min-h-[2.8rem] px-2 py-1.5 rounded text-left transition-all hover:shadow-md"
                            style="background-color: {c.materia ? 'rgba(16,185,129,0.10)' : 'rgba(120,120,120,0.05)'}; border: {conflict ? '2px solid #ef4444' : c.materia ? '1px solid #10b981' : '1px dashed rgb(var(--border-primary))'};"
                          >
                            {#if c.materia}
                              <div class="text-[11px] font-bold leading-tight" style="color: rgb(var(--text-primary));">{c.materia}</div>
                              <div class="text-[10px] truncate" style="color: rgb(var(--text-secondary));" title={c.docente}>{c.docente}</div>
                              {#if conflict}
                                <div class="text-[10px] font-bold mt-0.5" style="color: #ef4444;">⚠ conflicto</div>
                              {/if}
                            {:else}
                              <div class="text-[11px]" style="color: rgb(var(--text-secondary));">+ asignar</div>
                            {/if}
                          </button>
                        </td>
                      {/each}
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {/if}
        {:else}
          {#if !docenteActivo}
            <p class="text-center py-8 text-sm" style="color: rgb(var(--text-secondary));">
              Selecciona un docente para ver su horario.
            </p>
          {:else}
            {@const filaDoc = horarioDocentePlano[docenteActivo]}
            {@const cfg = configDocentes[docenteActivo] ?? { nombre: docenteActivo, cargaMin: 22, horasExtra: 0 }}
            {@const hrs = horasDocente(docenteActivo)}
            <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
              <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">{docenteActivo}</h2>
              <div class="flex items-center gap-3 text-xs">
                <label class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                  Carga mín
                  <input
                    type="number"
                    min="0"
                    max="40"
                    value={cfg.cargaMin}
                    oninput={(e) => {
                      const v = parseInt(e.currentTarget.value || "0") || 0;
                      configDocentes = { ...configDocentes, [docenteActivo]: { ...cfg, cargaMin: v } };
                    }}
                    class="w-14 px-1 py-0.5 rounded border text-xs"
                    style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                  />
                </label>
                <label class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                  Horas extra
                  <input
                    type="number"
                    min="0"
                    max="20"
                    value={cfg.horasExtra}
                    oninput={(e) => {
                      const v = parseInt(e.currentTarget.value || "0") || 0;
                      configDocentes = { ...configDocentes, [docenteActivo]: { ...cfg, horasExtra: v } };
                    }}
                    class="w-14 px-1 py-0.5 rounded border text-xs"
                    style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
                  />
                </label>
                <span class="px-2 py-1 rounded font-bold" style="background-color: {hrs > cfg.cargaMin + cfg.horasExtra ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)'}; color: {hrs > cfg.cargaMin + cfg.horasExtra ? '#b91c1c' : '#065f46'};">
                  {hrs}/{cfg.cargaMin + cfg.horasExtra}h
                </span>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-xs" style="border-collapse: collapse;">
                <thead>
                  <tr style="background-color: rgb(var(--bg-secondary));">
                    <th class="p-2" style="color: rgb(var(--text-primary));">HORA</th>
                    {#each DIAS as d}
                      <th class="p-2" style="color: rgb(var(--text-primary));">{DIAS_ABREV[d]}</th>
                    {/each}
                  </tr>
                </thead>
                <tbody>
                  {#each Array(HORAS_DIA) as _, h}
                    <tr>
                      <td class="p-2 text-center font-bold" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--border-primary));">{h + 1}</td>
                      {#each DIAS as d}
                        {@const slot = filaDoc?.[d]?.[h] ?? ""}
                        <td class="p-1" style="border: 1px solid rgb(var(--border-primary));">
                          {#if slot}
                            <div class="px-2 py-2 rounded text-[11px] font-bold" style="background-color: rgba(16,185,129,0.10); color: rgb(var(--text-primary)); border: 1px solid #10b981;">
                              {slot}
                            </div>
                          {:else}
                            <div class="px-2 py-2 rounded text-[11px]" style="border: 1px dashed rgb(var(--border-primary)); color: rgb(var(--text-secondary));">LIBRE</div>
                          {/if}
                        </td>
                      {/each}
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
            <p class="text-[11px] mt-2" style="color: rgb(var(--text-secondary));">Para editar celdas usa la <strong>Vista Grupo</strong>. Aquí ves la consolidación del docente.</p>
          {/if}
        {/if}
      </div>
    </div>
    {/if}
  </div>
</div>

<!-- Modal asignación celda -->
{#if modalAbierto && modalCtx}
  {@const disponibles = docentesDisponibles(modalCtx.grupo, modalCtx.dia, modalCtx.hora)}
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
    <div class="rounded-xl p-6 w-full max-w-md" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
          Asignar — {modalCtx.grupo}, {DIAS_ABREV[modalCtx.dia]} hora {modalCtx.hora + 1}
        </h3>
        <button onclick={() => (modalAbierto = false)} class="w-7 h-7 rounded-full flex items-center justify-center" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));">
          ✕
        </button>
      </div>

      <label for="ch-modal-materia" class="block text-xs font-medium mb-1" style="color: rgb(var(--text-secondary));">Asignatura</label>
      <select
        id="ch-modal-materia"
        bind:value={modalMateria}
        class="w-full px-2 py-2 rounded border mb-3 text-sm"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <option value="">— Selecciona —</option>
        {#each todasMaterias as m}
          <option value={m}>{m}</option>
        {/each}
      </select>

      <label for="ch-modal-docente" class="block text-xs font-medium mb-1" style="color: rgb(var(--text-secondary));">Docente</label>
      <select
        id="ch-modal-docente"
        bind:value={modalDocente}
        class="w-full px-2 py-2 rounded border mb-2 text-sm"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <option value="">— Selecciona —</option>
        <optgroup label="Disponibles esta hora">
          {#each disponibles as d}
            <option value={d}>{d}</option>
          {/each}
        </optgroup>
        <optgroup label="Ocupados (forzar)">
          {#each todosDocentes.filter((d) => !disponibles.includes(d)) as d}
            <option value={d}>{d} ⚠</option>
          {/each}
        </optgroup>
      </select>

      <label class="flex items-center gap-2 text-xs mb-4 cursor-pointer" style="color: rgb(var(--text-primary));">
        <input type="checkbox" bind:checked={modalEsExtra} class="w-4 h-4" style="accent-color: rgb(var(--accent-primary));" />
        Marcar esta hora como hora extra del docente
      </label>

      <div class="flex gap-2">
        <button
          onclick={limpiarCelda}
          class="flex-1 py-2 rounded font-medium text-xs"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
        >
          Limpiar celda
        </button>
        <button
          onclick={guardarCelda}
          class="flex-1 py-2 rounded font-bold text-white text-xs flex items-center justify-center gap-1"
          style="background-color: rgb(var(--accent-primary));"
        >
          <Save size={14} /> Guardar
        </button>
      </div>
    </div>
  </div>
{/if}