<script lang="ts">
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import { DOCENTES_URL, MATERIAS_URL, ESTUDIANTES_URL } from "../../constants";
  import { Save, Download, Upload, AlertTriangle, Users, BookOpen, GraduationCap, Plus, Trash2, ArrowLeft } from "@lucide/svelte";

  let { onBack }: { onBack?: () => void } = $props();

  // -------- Tipos --------
  type Vista = "grupo" | "docente";
  type Celda = { area: string; docente: string };
  // Estructura matriz: por grupo -> dia -> hora -> Celda
  // Equivalente plano por docente generado on demand.
  type HorarioPorGrupo = Record<string, Record<DiaKey, Celda[]>>;
  type DocenteCfg = { nombre: string; cargaMax: number; horasExtra: number };

  const DIAS = ["lunes", "martes", "miercoles", "jueves", "viernes"] as const;
  type DiaKey = (typeof DIAS)[number];
  const DIAS_ABREV = { lunes: "LUN", martes: "MAR", miercoles: "MIE", jueves: "JUE", viernes: "VIE" };
  const HORAS_DIA = 7;
  const STORAGE_KEY = "horarioBuilder_v1";

  // -------- Estado --------
  let cargandoCatalogo = $state(false);
  let docentes = $state<string[]>([]);
  let areas = $state<string[]>([]);
  let grupos = $state<string[]>([]);
  // Catálogos editables localmente (sin tocar backend)
  let docentesLocales = $state<string[]>([]);
  let areasLocales = $state<string[]>([]);
  let gruposLocales = $state<string[]>([]);
  // Config docente (carga máx + extras)
  let cfgDocentes = $state<Record<string, DocenteCfg>>({});

  let vista = $state<Vista>("grupo");
  let entidadActiva = $state<string>("");
  let horario = $state<HorarioPorGrupo>({});

  // Modal asignación celda
  let modalAbierto = $state(false);
  let modalCtx = $state<{ grupo: string; dia: DiaKey; hora: number } | null>(null);
  let modalArea = $state("");
  let modalDocente = $state("");
  let modalEsExtra = $state(false);

  // -------- Catálogos --------
  const docentesAll = $derived([...docentes, ...docentesLocales].sort((a, b) => a.localeCompare(b)));
  const areasAll = $derived([...areas, ...areasLocales].sort((a, b) => a.localeCompare(b)));
  const gruposAll = $derived([...grupos, ...gruposLocales].sort((a, b) => Number(a) - Number(b) || a.localeCompare(b)));

  // -------- Persistencia --------
  function guardarEnStorage() {
    try {
      const payload = {
        horario,
        cfgDocentes,
        docentesLocales,
        areasLocales,
        gruposLocales,
        savedAt: new Date().toISOString(),
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch (e) {
      console.error("No se pudo guardar", e);
    }
  }

  function cargarDeStorage() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const p = JSON.parse(raw);
      horario = p.horario || {};
      cfgDocentes = p.cfgDocentes || {};
      docentesLocales = p.docentesLocales || [];
      areasLocales = p.areasLocales || [];
      gruposLocales = p.gruposLocales || [];
    } catch (e) {
      console.error("No se pudo restaurar", e);
    }
  }

  $effect(() => {
    // Auto-guardar ante cualquier cambio relevante
    horario; cfgDocentes; docentesLocales; areasLocales; gruposLocales;
    guardarEnStorage();
  });

  // -------- Carga catálogo backend --------
  async function cargarCatalogo() {
    cargandoCatalogo = true;
    try {
      const [resDoc, resMat, resEst] = await Promise.all([
        fetch(DOCENTES_URL),
        fetch(MATERIAS_URL),
        fetch(ESTUDIANTES_URL),
      ]);
      const dataDoc = await resDoc.json();
      const dataMat = await resMat.json();
      const dataEst = await resEst.json();
      docentes = Array.isArray(dataDoc) ? dataDoc : [];
      areas = Array.isArray(dataMat) ? dataMat.map((m: { materia: string }) => m.materia) : [];
      const setGrupos = new Set<string>();
      for (const e of dataEst) setGrupos.add(String(e.grado));
      grupos = Array.from(setGrupos);
      // Asegurar cfg base
      for (const d of docentes) {
        if (!cfgDocentes[d]) cfgDocentes[d] = { nombre: d, cargaMax: 22, horasExtra: 0 };
      }
    } catch (e) {
      Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar catálogo del backend", confirmButtonColor: "#ef4444" });
    } finally {
      cargandoCatalogo = false;
    }
  }

  // -------- Helpers matriz --------
  function celdaVacia(): Celda {
    return { area: "", docente: "" };
  }

  function asegurarGrupo(grupo: string) {
    if (horario[grupo]) return;
    const fila: Record<DiaKey, Celda[]> = { lunes: [], martes: [], miercoles: [], jueves: [], viernes: [] };
    for (const d of DIAS) fila[d] = Array.from({ length: HORAS_DIA }, celdaVacia);
    horario[grupo] = fila;
    horario = { ...horario };
  }

  function getCelda(grupo: string, dia: DiaKey, hora: number): Celda {
    return horario[grupo]?.[dia]?.[hora] ?? celdaVacia();
  }

  function setCelda(grupo: string, dia: DiaKey, hora: number, c: Celda) {
    asegurarGrupo(grupo);
    horario[grupo][dia][hora] = c;
    horario = { ...horario };
  }

  // -------- Vista por docente derivada --------
  type HorarioDocenteFila = { docente: string; lunes: string[]; martes: string[]; miercoles: string[]; jueves: string[]; viernes: string[] };
  const horarioPorDocente = $derived.by<Record<string, HorarioDocenteFila>>(() => {
    const map: Record<string, HorarioDocenteFila> = {};
    for (const d of docentesAll) {
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
          const texto = c.area ? `${c.area} ${grupo}` : grupo;
          map[c.docente][dia][h] = texto;
        }
      }
    }
    return map;
  });

  // -------- Validaciones --------
  type Conflicto = { tipo: "doble_asignacion" | "carga_excedida" | "grupo_incompleto"; grupo?: string; docente?: string; dia?: DiaKey; hora?: number; mensaje: string };

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
        for (const [doc, gs] of docPorGrupo.entries()) {
          if (gs.length > 1) {
            lista.push({
              tipo: "doble_asignacion",
              docente: doc,
              dia,
              hora: h,
              mensaje: `${doc} asignado a ${gs.join(", ")} en ${dia} hora ${h + 1}`,
            });
          }
        }
      }
    }

    // 2) Carga docente vs cargaMax (sin contar horas extra)
    const horasPorDocente = new Map<string, number>();
    for (const fila of Object.values(horarioPorDocente)) {
      let total = 0;
      for (const d of DIAS) {
        for (const cell of fila[d]) if (cell) total++;
      }
      horasPorDocente.set(fila.docente, total);
    }
    for (const [doc, h] of horasPorDocente.entries()) {
      const cfg = cfgDocentes[doc];
      if (!cfg) continue;
      const tope = cfg.cargaMax + (cfg.horasExtra || 0);
      if (h > tope) {
        lista.push({
          tipo: "carga_excedida",
          docente: doc,
          mensaje: `${doc}: ${h}h asignadas > tope ${cfg.cargaMax}+${cfg.horasExtra} extra`,
        });
      }
    }

    // 3) Grupo con celdas vacías (filas activas)
    for (const [grupo, fila] of Object.entries(horario)) {
      let vacias = 0;
      for (const d of DIAS) for (const c of fila[d]) if (!c.docente || !c.area) vacias++;
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

  const conflictosPorTipo = $derived.by(() => {
    const t = { doble_asignacion: 0, carga_excedida: 0, grupo_incompleto: 0 };
    for (const c of conflictos) t[c.tipo]++;
    return t;
  });

  function celdaTieneConflicto(grupo: string, dia: DiaKey, hora: number, docente: string): boolean {
    if (!docente) return false;
    return conflictos.some(
      (c) => c.tipo === "doble_asignacion" && c.docente === docente && c.dia === dia && c.hora === hora
    );
  }

  // -------- Modal celda --------
  function abrirCelda(grupo: string, dia: DiaKey, hora: number) {
    asegurarGrupo(grupo);
    const c = getCelda(grupo, dia, hora);
    modalCtx = { grupo, dia, hora };
    modalArea = c.area;
    modalDocente = c.docente;
    modalEsExtra = false;
    modalAbierto = true;
  }

  function guardarCelda() {
    if (!modalCtx) return;
    setCelda(modalCtx.grupo, modalCtx.dia, modalCtx.hora, { area: modalArea, docente: modalDocente });
    if (modalEsExtra && modalDocente) {
      const cfg = cfgDocentes[modalDocente] ?? { nombre: modalDocente, cargaMax: 22, horasExtra: 0 };
      cfg.horasExtra = (cfg.horasExtra || 0) + 1;
      cfgDocentes = { ...cfgDocentes, [modalDocente]: cfg };
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

  // -------- CRUD catálogos locales --------
  async function agregarEntidad(tipo: "docente" | "area" | "grupo") {
    const titulos = { docente: "Nuevo docente", area: "Nueva área", grupo: "Nuevo grupo (ej. 802)" };
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
    const v = String(value).trim();
    if (!v) return;
    if (tipo === "docente") {
      if (docentesAll.includes(v)) return;
      docentesLocales = [...docentesLocales, v];
      cfgDocentes = { ...cfgDocentes, [v]: { nombre: v, cargaMax: 22, horasExtra: 0 } };
    } else if (tipo === "area") {
      if (areasAll.includes(v)) return;
      areasLocales = [...areasLocales, v];
    } else {
      if (gruposAll.includes(v)) return;
      gruposLocales = [...gruposLocales, v];
    }
  }

  async function eliminarEntidad(tipo: "docente" | "area" | "grupo", valor: string) {
    const r = await Swal.fire({
      title: "¿Eliminar?",
      text: `Eliminar ${valor}. Las asignaciones que lo usen quedarán vacías.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
    });
    if (!r.isConfirmed) return;
    if (tipo === "docente") {
      docentesLocales = docentesLocales.filter((d) => d !== valor);
      const next = { ...cfgDocentes };
      delete next[valor];
      cfgDocentes = next;
      // Limpiar celdas con ese docente
      for (const g of Object.keys(horario)) {
        for (const d of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            if (horario[g][d][h].docente === valor) horario[g][d][h] = celdaVacia();
          }
        }
      }
      horario = { ...horario };
    } else if (tipo === "area") {
      areasLocales = areasLocales.filter((a) => a !== valor);
      for (const g of Object.keys(horario)) {
        for (const d of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            if (horario[g][d][h].area === valor) horario[g][d][h].area = "";
          }
        }
      }
      horario = { ...horario };
    } else {
      gruposLocales = gruposLocales.filter((g) => g !== valor);
      const next = { ...horario };
      delete next[valor];
      horario = next;
      if (entidadActiva === valor) entidadActiva = "";
    }
  }

  // -------- Export/Import JSON --------
  function exportarHorariosJSON() {
    const filas: HorarioDocenteFila[] = Object.values(horarioPorDocente)
      .filter((f) => DIAS.some((d) => f[d].some((s) => s !== "")))
      .map((f) => ({ docente: f.docente, lunes: f.lunes, martes: f.martes, miercoles: f.miercoles, jueves: f.jueves, viernes: f.viernes }));
    const blob = new Blob([JSON.stringify(filas, null, 2)], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "horarios.json";
    a.click();
    URL.revokeObjectURL(url);
  }

  async function importarHorariosJSON(ev: Event) {
    const file = (ev.target as HTMLInputElement).files?.[0];
    if (!file) return;
    try {
      const txt = await file.text();
      const filas = JSON.parse(txt) as HorarioDocenteFila[];
      if (!Array.isArray(filas)) throw new Error("Formato inválido");
      // Reconstruir horario por grupo desde formato docente
      const next: HorarioPorGrupo = {};
      for (const fila of filas) {
        for (const dia of DIAS) {
          for (let h = 0; h < HORAS_DIA; h++) {
            const slot = fila[dia][h];
            if (!slot) continue;
            const m = slot.match(/(\d{3,4})$/);
            const grupo = m ? m[1] : "";
            if (!grupo) continue;
            const area = slot.replace(/\s*\d{3,4}$/, "").trim();
            if (!next[grupo]) {
              next[grupo] = { lunes: [], martes: [], miercoles: [], jueves: [], viernes: [] };
              for (const d of DIAS) next[grupo][d] = Array.from({ length: HORAS_DIA }, celdaVacia);
            }
            next[grupo][dia][h] = { area, docente: fila.docente };
          }
        }
      }
      horario = next;
      Swal.fire({ icon: "success", title: "Importado", text: `${filas.length} docentes cargados`, confirmButtonColor: "rgb(99,102,241)" });
    } catch (e) {
      Swal.fire({ icon: "error", title: "Error", text: "No se pudo leer JSON" });
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
    cfgDocentes = {};
    docentesLocales = [];
    areasLocales = [];
    gruposLocales = [];
    entidadActiva = "";
    localStorage.removeItem(STORAGE_KEY);
  }

  onMount(() => {
    cargarDeStorage();
    cargarCatalogo();
  });

  // Sugerencias docentes disponibles a una hora dada (no asignados a otro grupo)
  function docentesDisponibles(grupoActual: string, dia: DiaKey, hora: number): string[] {
    const ocupados = new Set<string>();
    for (const [g, fila] of Object.entries(horario)) {
      if (g === grupoActual) continue;
      const c = fila[dia][hora];
      if (c.docente) ocupados.add(c.docente);
    }
    return docentesAll.filter((d) => !ocupados.has(d));
  }

  function horasDocente(docente: string): number {
    const fila = horarioPorDocente[docente];
    if (!fila) return 0;
    let t = 0;
    for (const d of DIAS) for (const s of fila[d]) if (s) t++;
    return t;
  }
</script>

<div class="p-4 max-w-7xl mx-auto">
  <!-- Toolbar superior -->
  <div class="flex flex-wrap items-center gap-2 mb-4 p-3 rounded-xl border" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
    {#if onBack}
      <button
        onclick={onBack}
        class="px-3 py-2 rounded-lg text-sm font-medium border transition-all flex items-center gap-1"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
      >
        <ArrowLeft size={14} /> Volver
      </button>
    {/if}
    <div class="flex gap-2">
      <button
        onclick={() => (vista = "grupo")}
        class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
        style="background-color: {vista === 'grupo' ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {vista === 'grupo' ? 'white' : 'rgb(var(--text-primary))'};"
      >
        <Users size={14} class="inline mr-1" />Vista Grupo
      </button>
      <button
        onclick={() => (vista = "docente")}
        class="px-3 py-2 rounded-lg text-sm font-medium transition-all"
        style="background-color: {vista === 'docente' ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {vista === 'docente' ? 'white' : 'rgb(var(--text-primary))'};"
      >
        <GraduationCap size={14} class="inline mr-1" />Vista Docente
      </button>
    </div>

    <div class="ml-auto flex flex-wrap gap-2">
      <button onclick={cargarCatalogo} disabled={cargandoCatalogo} class="px-3 py-2 rounded-lg text-xs font-medium border transition-all disabled:opacity-50" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));">
        {cargandoCatalogo ? "Cargando..." : "↻ Recargar catálogo"}
      </button>
      <button onclick={exportarHorariosJSON} class="px-3 py-2 rounded-lg text-xs font-medium text-white transition-all flex items-center gap-1" style="background-color: rgb(var(--accent-primary));">
        <Download size={12} /> Export JSON
      </button>
      <label class="px-3 py-2 rounded-lg text-xs font-medium border cursor-pointer transition-all flex items-center gap-1" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));">
        <Upload size={12} /> Import JSON
        <input type="file" accept="application/json" onchange={importarHorariosJSON} class="hidden" />
      </label>
      <button onclick={resetTodo} class="px-3 py-2 rounded-lg text-xs font-medium text-white transition-all" style="background-color: #ef4444;">
        🗑️ Reset
      </button>
    </div>
  </div>

  <!-- Resumen conflictos -->
  {#if conflictos.length > 0}
    <div class="mb-4 p-3 rounded-xl border-2 flex flex-wrap items-center gap-3" style="border-color: #f59e0b; background-color: rgba(245,158,11,0.08);">
      <AlertTriangle size={18} style="color: #b45309;" />
      <span class="text-sm font-bold" style="color: #b45309;">
        {conflictos.length} aviso(s):
      </span>
      <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(239,68,68,0.15); color: #b91c1c;">
        {conflictosPorTipo.doble_asignacion} doble asignación
      </span>
      <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(245,158,11,0.18); color: #92400e;">
        {conflictosPorTipo.carga_excedida} carga excedida
      </span>
      <span class="text-xs px-2 py-1 rounded" style="background-color: rgba(59,130,246,0.18); color: #1e40af;">
        {conflictosPorTipo.grupo_incompleto} grupo(s) incompleto(s)
      </span>
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

  <!-- Columnas: catálogo izquierda + matriz derecha -->
  <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-4">
    <!-- Catálogo -->
    <div class="rounded-xl border p-3 space-y-3" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
      {#if vista === "grupo"}
        <section>
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold flex items-center gap-1" style="color: rgb(var(--text-primary));">
              <Users size={14} /> Grupos
            </h3>
            <button onclick={() => agregarEntidad("grupo")} class="p-1 rounded transition-all" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar grupo">
              <Plus size={12} />
            </button>
          </div>
          <ul class="space-y-1 max-h-[40vh] overflow-y-auto">
            {#each gruposAll as g}
              <li class="flex items-center gap-1">
                <button
                  onclick={() => (entidadActiva = g)}
                  class="flex-1 text-left px-2 py-1.5 rounded text-xs transition-all"
                  style="background-color: {entidadActiva === g ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {entidadActiva === g ? 'white' : 'rgb(var(--text-primary))'};"
                >
                  {g}
                </button>
                {#if gruposLocales.includes(g)}
                  <button onclick={() => eliminarEntidad("grupo", g)} class="p-1 rounded" style="color: #ef4444;" title="Eliminar local">
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
            <button onclick={() => agregarEntidad("docente")} class="p-1 rounded transition-all" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar docente local">
              <Plus size={12} />
            </button>
          </div>
          <ul class="space-y-1 max-h-[40vh] overflow-y-auto">
            {#each docentesAll as d}
              {@const horas = horasDocente(d)}
              {@const cfg = cfgDocentes[d]}
              {@const tope = (cfg?.cargaMax ?? 22) + (cfg?.horasExtra ?? 0)}
              <li class="flex items-center gap-1">
                <button
                  onclick={() => (entidadActiva = d)}
                  class="flex-1 text-left px-2 py-1.5 rounded text-xs transition-all"
                  style="background-color: {entidadActiva === d ? 'rgb(var(--accent-primary))' : 'rgb(var(--bg-secondary))'}; color: {entidadActiva === d ? 'white' : 'rgb(var(--text-primary))'};"
                  title={d}
                >
                  <div class="truncate">{d}</div>
                  <div class="text-[10px] opacity-70">{horas}/{tope}h{cfg?.horasExtra ? ` (+${cfg.horasExtra})` : ""}</div>
                </button>
                {#if docentesLocales.includes(d)}
                  <button onclick={() => eliminarEntidad("docente", d)} class="p-1 rounded" style="color: #ef4444;" title="Eliminar local">
                    <Trash2 size={11} />
                  </button>
                {/if}
              </li>
            {/each}
          </ul>
        </section>
      {/if}

      <section>
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-bold flex items-center gap-1" style="color: rgb(var(--text-primary));">
            <BookOpen size={14} /> Áreas
          </h3>
          <button onclick={() => agregarEntidad("area")} class="p-1 rounded transition-all" style="background-color: rgb(var(--accent-primary)); color: white;" title="Agregar área local">
            <Plus size={12} />
          </button>
        </div>
        <ul class="space-y-1 max-h-[20vh] overflow-y-auto">
          {#each areasAll as a}
            <li class="flex items-center gap-1 px-2 py-1 rounded text-xs" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));">
              <span class="flex-1 truncate" title={a}>{a}</span>
              {#if areasLocales.includes(a)}
                <button onclick={() => eliminarEntidad("area", a)} class="p-0.5" style="color: #ef4444;">
                  <Trash2 size={10} />
                </button>
              {/if}
            </li>
          {/each}
        </ul>
      </section>
    </div>

    <!-- Matriz -->
    <div class="rounded-xl border p-3" style="border-color: rgb(var(--border-primary)); background-color: rgb(var(--card-bg));">
      {#if !entidadActiva}
        <p class="text-center py-8 text-sm" style="color: rgb(var(--text-secondary));">
          Selecciona un {vista === "grupo" ? "grupo" : "docente"} para empezar a editar.
        </p>
      {:else if vista === "grupo"}
        {@const fila = horario[entidadActiva]}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
          <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">Grupo {entidadActiva}</h2>
          <button onclick={() => asegurarGrupo(entidadActiva)} class="px-2 py-1 rounded text-xs" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));">
            Inicializar matriz
          </button>
        </div>
        {#if !fila}
          <p class="text-xs" style="color: rgb(var(--text-secondary));">Aún sin matriz. Haz click en "Inicializar matriz" o en cualquier celda abajo.</p>
        {/if}
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
                    {@const c = getCelda(entidadActiva, d, h)}
                    {@const conflict = celdaTieneConflicto(entidadActiva, d, h, c.docente)}
                    <td class="p-1" style="border: 1px solid rgb(var(--border-primary)); background-color: {conflict ? 'rgba(239,68,68,0.10)' : 'transparent'};">
                      <button
                        onclick={() => abrirCelda(entidadActiva, d, h)}
                        class="w-full min-h-[3rem] px-2 py-1 rounded text-left transition-all hover:shadow-md"
                        style="background-color: {c.docente ? 'rgba(16,185,129,0.10)' : 'rgba(120,120,120,0.05)'}; border: {conflict ? '2px solid #ef4444' : c.docente ? '1px solid #10b981' : '1px dashed rgb(var(--border-primary))'};"
                      >
                        {#if c.docente}
                          <div class="text-[11px] font-bold leading-tight" style="color: rgb(var(--text-primary));">{c.area || "(sin área)"}</div>
                          <div class="text-[10px] truncate" style="color: rgb(var(--text-secondary));" title={c.docente}>{c.docente}</div>
                          {#if conflict}
                            <div class="text-[10px] font-bold mt-0.5" style="color: #ef4444;">⚠ conflicto</div>
                          {/if}
                        {:else}
                          <div class="text-[11px]" style="color: rgb(var(--text-secondary));">+</div>
                        {/if}
                      </button>
                    </td>
                  {/each}
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      {:else}
        {@const filaDoc = horarioPorDocente[entidadActiva]}
        {@const cfg = cfgDocentes[entidadActiva] ?? { nombre: entidadActiva, cargaMax: 22, horasExtra: 0 }}
        {@const horas = horasDocente(entidadActiva)}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
          <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">{entidadActiva}</h2>
          <div class="flex items-center gap-3 text-xs">
            <label class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
              Carga máx
              <input
                type="number"
                min="0"
                max="40"
                value={cfg.cargaMax}
                oninput={(e) => {
                  const v = parseInt(e.currentTarget.value || "0") || 0;
                  cfgDocentes = { ...cfgDocentes, [entidadActiva]: { ...cfg, cargaMax: v } };
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
                  cfgDocentes = { ...cfgDocentes, [entidadActiva]: { ...cfg, horasExtra: v } };
                }}
                class="w-14 px-1 py-0.5 rounded border text-xs"
                style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));"
              />
            </label>
            <span class="px-2 py-1 rounded font-bold" style="background-color: {horas > cfg.cargaMax + cfg.horasExtra ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)'}; color: {horas > cfg.cargaMax + cfg.horasExtra ? '#b91c1c' : '#065f46'};">
              {horas} / {cfg.cargaMax + cfg.horasExtra}h
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
        <p class="text-[11px] mt-2" style="color: rgb(var(--text-secondary));">Para editar celdas usa la <strong>Vista Grupo</strong>. Aquí se ve agregada la carga del docente.</p>
      {/if}
    </div>
  </div>

  <!-- Modal asignación celda -->
  {#if modalAbierto && modalCtx}
    {@const disp = docentesDisponibles(modalCtx.grupo, modalCtx.dia, modalCtx.hora)}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
      <div class="rounded-xl p-6 w-full max-w-md" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
            Asignar — Grupo {modalCtx.grupo}, {DIAS_ABREV[modalCtx.dia]} h{modalCtx.hora + 1}
          </h3>
          <button onclick={() => (modalAbierto = false)} class="w-7 h-7 rounded-full" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));">✕</button>
        </div>

        <label for="hb-modal-area" class="block text-xs font-medium mb-1" style="color: rgb(var(--text-secondary));">Área</label>
        <select id="hb-modal-area" bind:value={modalArea} class="w-full px-2 py-2 rounded border mb-3 text-sm" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));">
          <option value="">— Selecciona —</option>
          {#each areasAll as a}
            <option value={a}>{a}</option>
          {/each}
        </select>

        <label for="hb-modal-docente" class="block text-xs font-medium mb-1" style="color: rgb(var(--text-secondary));">Docente</label>
        <select id="hb-modal-docente" bind:value={modalDocente} class="w-full px-2 py-2 rounded border mb-2 text-sm" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border-color: rgb(var(--border-primary));">
          <option value="">— Selecciona —</option>
          <optgroup label="Disponibles esta hora">
            {#each disp as d}
              <option value={d}>{d}</option>
            {/each}
          </optgroup>
          <optgroup label="Ocupados (forzar)">
            {#each docentesAll.filter((d) => !disp.includes(d)) as d}
              <option value={d}>{d} ⚠</option>
            {/each}
          </optgroup>
        </select>

        <label class="flex items-center gap-2 text-xs mb-4 cursor-pointer" style="color: rgb(var(--text-primary));">
          <input type="checkbox" bind:checked={modalEsExtra} class="w-4 h-4" style="accent-color: rgb(var(--accent-primary));" />
          Esta hora cuenta como hora extra del docente
        </label>

        <div class="flex gap-2">
          <button onclick={limpiarCelda} class="flex-1 py-2 rounded font-medium text-xs" style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));">
            Limpiar celda
          </button>
          <button onclick={guardarCelda} class="flex-1 py-2 rounded font-bold text-white text-xs flex items-center justify-center gap-1" style="background-color: rgb(var(--accent-primary));">
            <Save size={14} /> Guardar
          </button>
        </div>
      </div>
    </div>
  {/if}
</div>
