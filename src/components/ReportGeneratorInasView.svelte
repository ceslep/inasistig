<script lang="ts">
  import { onMount } from "svelte";
  import {
    getDocentes,
    getMaterias,
    getEstudiantes,
    getInasistencias,
  } from "../../api/service";
  import Loader from "./Loader.svelte";
  import { theme } from "../lib/themeStore";
  import {
    SPREADSHEET_ID,
    WORKSHEET_TITLE,
    periodos,
  } from "../constants";
  import Swal from "sweetalert2";

  export let onClose: () => void;
  export let initialDocente: string = "";

  interface InasistenciaData {
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    nombre: string;
    motivo: string;
    horas: string;
  }

  interface ReportData {
    docente: string;
    materia: string;
    grado: string;
    periodo: string;
    fechaInicio: string;
    fechaFin: string;
    fechas: string[];
    estudiantes: {
      nombre: string;
      registros: { valor: string; horas: number }[];
      totalHoras: number;
    }[];
  }

  let docentes: string[] = [];
  let materias: { materia: string }[] = [];
  let estudiantes: { nombre: string; grado: number | string }[] = [];
  
  let selectedDocente = initialDocente || "";
  let selectedMateria = "";
  let selectedGrado = "";
  let selectedPeriodo = periodos[0].nombre;

  let isLoading = false;
  let isGenerating = false;
  let reportData: ReportData | null = null;

  const normalize = (str: any) => {
    if (!str) return "";
    return str.toString()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-zA-Z0-9\s]/g, "")
      .replace(/\s+/g, " ")
      .trim()
      .toLowerCase();
  };

  const normalizeFecha = (fecha: string): string => {
    if (!fecha) return "";
    const parts = fecha.split("/");
    if (parts.length === 3) {
      const day = parts[0].padStart(2, "0");
      const month = parts[1].padStart(2, "0");
      const year = parts[2];
      return `${year}-${month}-${day}`;
    }
    return fecha;
  };

  const generateDatesFromPeriodo = (periodo: any): Date[] => {
    const dates: Date[] = [];
    const current = new Date(periodo.fecha_inicio);
    const end = new Date(periodo.fecha_fin);

    while (current <= end) {
      const dayOfWeek = current.getUTCDay();
      if (dayOfWeek >= 1 && dayOfWeek <= 6) {
        dates.push(new Date(current));
      }
      current.setUTCDate(current.getUTCDate() + 1);
    }

    return dates;
  };

  const formatDateDisplay = (date: Date): string => {
    const months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return `${date.getUTCDate()}/${months[date.getUTCMonth()]}/${date.getUTCFullYear().toString().substr(2)}`;
  };

  onMount(async () => {
    isLoading = true;
    try {
      const [docentesData, materiasData, estudiantesData] = await Promise.all([
        getDocentes(),
        getMaterias(),
        getEstudiantes(),
      ]);
      docentes = docentesData;
      materias = materiasData;
      estudiantes = estudiantesData;
    } catch (error) {
      console.error("Error cargando datos iniciales:", error);
    } finally {
      isLoading = false;
    }
  });

  const generateReport = async () => {
    if (!selectedDocente || !selectedMateria || !selectedGrado) {
      Swal.fire({
        icon: "warning",
        title: "Campos incompletos",
        text: "Por favor seleccione Docente, Materia y Grado.",
      });
      return;
    }

    const periodo = periodos.find(p => p.nombre === selectedPeriodo);
    if (!periodo) {
      Swal.fire({ icon: "error", title: "Error", text: "Periodo no encontrado" });
      return;
    }

    isGenerating = true;
    try {
      const response = await getInasistencias({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
      });

      if (!response?.records || !Array.isArray(response.records)) {
        throw new Error("No se pudieron obtener los registros de inasistencias");
      }

      const dataRows = response.records.slice(1);
      const targetDocente = normalize(selectedDocente);
      const targetMateria = normalize(selectedMateria);
      const targetGrado = selectedGrado.toString().trim();

      const filteredInasistencias = dataRows
        .map((row: any) => {
          const values = row.values || [];
          return {
            fecha: values[2] || "",
            docente: values[1] || "",
            materia: values[4] || "",
            grado: (values[6] || "").toString().trim(),
            nombre: values[7] || "",
            motivo: values[5] || "",
            horas: values[3] || "0",
          } as InasistenciaData;
        })
        .filter((item: InasistenciaData) => {
          const itemDocente = normalize(item.docente);
          const itemMateria = normalize(item.materia);
          const itemGrado = item.grado;

          const matchDocente = itemDocente.includes(targetDocente) || targetDocente.includes(itemDocente);
          const matchMateria = itemMateria.includes(targetMateria) || targetMateria.includes(itemMateria);
          const matchGrado = itemGrado === targetGrado || itemGrado.startsWith(targetGrado) || targetGrado.startsWith(itemGrado);

          const fechaNorm = normalizeFecha(item.fecha);
          const itemDate = new Date(fechaNorm);
          const periodoInicio = new Date(periodo.fecha_inicio);
          const periodoFin = new Date(periodo.fecha_fin);
          periodoInicio.setHours(0, 0, 0, 0);
          periodoFin.setHours(23, 59, 59, 999);
          const matchPeriodo = itemDate >= periodoInicio && itemDate <= periodoFin;

          return matchDocente && matchMateria && matchGrado && matchPeriodo;
        });

      const estudiantesGrado = estudiantes
        .filter(e => e.grado.toString() === selectedGrado)
        .map(e => e.nombre)
        .sort();

      if (estudiantesGrado.length === 0) {
        throw new Error("No hay estudiantes registrados para el grado seleccionado");
      }

      const allDatesInPeriod = generateDatesFromPeriodo(periodo);
      
      const fechasConRegistros = allDatesInPeriod.filter(date => {
        const dateStr = date.toISOString().split("T")[0];
        return filteredInasistencias.some((reg: InasistenciaData) => {
          const regFecha = normalizeFecha(reg.fecha);
          return regFecha === dateStr;
        });
      });

      if (fechasConRegistros.length === 0) {
        Swal.fire({
          icon: "info",
          title: "Sin datos",
          text: "No se encontraron inasistencias para los filtros seleccionados en este periodo.",
        });
        return;
      }

      const estudiantesData = estudiantesGrado.map(nombreEstudiante => {
        const registros: { valor: string; horas: number }[] = [];
        let totalHoras = 0;

        fechasConRegistros.forEach((date) => {
          const dateStr = date.toISOString().split("T")[0];
          const inasistencias = filteredInasistencias.filter(
            (i: InasistenciaData) => normalize(i.nombre) === normalize(nombreEstudiante) && normalizeFecha(i.fecha) === dateStr
          );

          if (inasistencias.length > 0) {
            const inas = inasistencias[0];
            const horas = parseFloat(inas.horas) || 0;
            const motivo = inas.motivo || "";
            
            if (horas > 0) {
              registros.push({ valor: horas.toString(), horas });
              totalHoras += horas;
            } else if (motivo) {
              registros.push({ valor: motivo, horas: 0 });
            } else {
              registros.push({ valor: "X", horas: 0 });
            }
          } else {
            registros.push({ valor: "✓", horas: 0 });
          }
        });

        return { nombre: nombreEstudiante, registros, totalHoras };
      });

      reportData = {
        docente: selectedDocente,
        materia: selectedMateria,
        grado: selectedGrado,
        periodo: periodo.nombre,
        fechaInicio: formatDateDisplay(periodo.fecha_inicio),
        fechaFin: formatDateDisplay(periodo.fecha_fin),
        fechas: fechasConRegistros.map(d => formatDateDisplay(d)),
        estudiantes: estudiantesData,
      };

    } catch (error: unknown) {
      console.error("Error generando reporte:", error);
      const errorMessage = error instanceof Error ? error.message : "Error desconocido";
      Swal.fire({
        icon: "error",
        title: "Error",
        text: errorMessage,
      });
    } finally {
      isGenerating = false;
    }
  };

  $: styles = {
    bg: "rgb(var(--bg-primary))",
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    cardBg: "rgb(var(--card-bg))",
    cardBorder: "rgb(var(--card-border))",
    inputBg: "rgb(var(--bg-secondary))",
  };
</script>

<div
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-2 md:p-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modal-title"
  tabindex="-1"
  on:click|self={onClose}
  on:keydown={(e: KeyboardEvent) => e.key === 'Escape' && onClose()}
>
  <div
    class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-[95vw] max-h-[90vh] overflow-hidden shadow-2xl border flex flex-col"
    style="background-color: {styles.cardBg}; border-color: {styles.cardBorder}; color: {styles.text};"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-4 md:p-6 border-b flex-shrink-0" style="border-color: {styles.cardBorder};">
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <h2 id="modal-title" class="text-lg md:text-xl font-bold">Vista de Reporte</h2>
          <p class="text-sm opacity-75">Inasistencias por materia y grado</p>
        </div>
      </div>
      <button on:click={onClose} class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Cerrar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Content -->
    <div class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1">
      {#if isLoading}
        <div class="flex justify-center py-12">
          <Loader message="Cargando datos..." />
        </div>
      {:else}
        {#if !reportData}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label for="view-docente" class="block text-sm font-medium" style="color: {styles.label};">Docente</label>
              <select id="view-docente" bind:value={selectedDocente} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
                <option value="">Seleccione docente</option>
                {#each docentes as docente}
                  <option value={docente}>{docente}</option>
                {/each}
              </select>
            </div>

            <div class="space-y-2">
              <label for="view-materia" class="block text-sm font-medium" style="color: {styles.label};">Materia</label>
              <select id="view-materia" bind:value={selectedMateria} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
                <option value="">Seleccione materia</option>
                {#each materias as materia}
                  <option value={materia.materia}>{materia.materia}</option>
                {/each}
              </select>
            </div>

            <div class="space-y-2">
              <label for="view-grado" class="block text-sm font-medium" style="color: {styles.label};">Grado</label>
              <select id="view-grado" bind:value={selectedGrado} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
                <option value="">Seleccione grado</option>
                {#each [...new Set(estudiantes.map(e => e.grado.toString()))] as g}
                  <option value={g}>{g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2")}</option>
                {/each}
              </select>
            </div>

            <div class="space-y-2">
              <label for="view-periodo" class="block text-sm font-medium" style="color: {styles.label};">Periodo</label>
              <select id="view-periodo" bind:value={selectedPeriodo} class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none" style="background-color: {styles.inputBg}; border-color: {styles.border}; color: {styles.text};">
                {#each periodos as p}
                  <option value={p.nombre}>Periodo {p.nombre}</option>
                {/each}
              </select>
            </div>
          </div>

          <div class="pt-4 flex gap-3">
            <button
              on:click={onClose}
              class="flex-1 px-6 py-3 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium"
              style="border-color: {styles.border};"
            >
              Cancelar
            </button>
            <button
              on:click={generateReport}
              disabled={isGenerating || !selectedDocente || !selectedMateria || !selectedGrado}
              class="flex-[2] px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all font-bold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              {#if isGenerating}
                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generando...
              {:else}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Ver Reporte
              {/if}
            </button>
          </div>
        {:else}
          <!-- Report Table -->
          <div class="space-y-4 min-w-0">
            <!-- Title -->
            <div class="text-center p-4 rounded-lg flex-shrink-0" style="background-color: #2E75B6; color: white;">
              <h3 class="text-lg font-bold whitespace-nowrap">REPORTE DE INASISTENCIAS - {reportData.materia} - GRADO {reportData.grado}</h3>
            </div>
            
            <!-- Info -->
            <div class="p-3 rounded-lg text-sm flex-shrink-0" style="background-color: #5B9BD5; color: white;">
              <p class="font-medium whitespace-nowrap">Docente: {reportData.docente} | Periodo: {reportData.periodo} ({reportData.fechaInicio} - {reportData.fechaFin})</p>
            </div>

            <!-- Table -->
            <div class="overflow-auto border rounded-lg flex-1 min-h-[200px] max-h-[50vh]">
              <table class="w-full border-collapse text-xs md:text-sm">
                <thead class="sticky top-0 z-30 shadow-sm">
                  <tr>
                    <th class="p-2 border text-white font-bold sticky left-0 z-40" style="background-color: #4472C4; width: 200px;">Estudiantes</th>
                    {#each reportData.fechas as fecha}
                      <th class="p-2 border text-white font-bold text-center sticky top-0 z-20" style="background-color: #4472C4; min-width: 60px;">{fecha}</th>
                    {/each}
                    <th class="p-2 border text-white font-bold text-center sticky top-0 z-20" style="background-color: #FF6B6B; width: 80px;">Total Horas</th>
                  </tr>
                </thead>
                <tbody class="bg-white">
                  {#each reportData.estudiantes as estudiante}
                    <tr class="hover-row">
                      <td class="p-2 border font-medium sticky left-0 z-10 bg-[#F2F2F2]" style="background-color: #F2F2F2;">{estudiante.nombre}</td>
                      {#each estudiante.registros as registro}
                        {@const horas = parseFloat(registro.valor) || 0}
                        {@const isCheck = registro.valor === "✓"}
                        <td 
                          class="p-2 border text-center font-medium bg-white cell-hover"
                          style={isCheck 
                            ? "color: #00AA00;" 
                            : "color: #FF0000; background-color: #FFE6E6;"}
                        >
                          {registro.valor}
                        </td>
                      {/each}
                      <td 
                        class="p-2 border text-center font-bold bg-white cell-hover"
                        style={estudiante.totalHoras > 0 
                          ? "color: #FF0000; background-color: #FFE6E6;" 
                          : "color: #00AA00; background-color: #E6FFE6;"}
                      >
                        {estudiante.totalHoras}
                      </td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>

            <!-- Actions -->
            <div class="pt-4 flex gap-3 flex-shrink-0">
              <button
                on:click={() => reportData = null}
                class="flex-1 px-6 py-3 border rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium"
                style="border-color: {styles.border};"
              >
                Nueva Consulta
              </button>
              <button
                on:click={onClose}
                class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl transition-colors font-medium"
              >
                Cerrar
              </button>
            </div>
          </div>
        {/if}
      {/if}
    </div>
  </div>
</div>

<style>
  .hover-row:hover td {
    background-color: #e0f2fe !important;
  }
  .hover-row:hover td:first-child {
    background-color: #bfdbfe !important;
  }
  :global(.dark) .hover-row:hover td {
    background-color: #1e3a5f !important;
  }
  :global(.dark) .hover-row:hover td:first-child {
    background-color: #172554 !important;
  }
</style>
