<script lang="ts">
  import { getDiario } from "../../../api/service";
  import { SPREADSHEET_ID_DIARIO, WORKSHEET_TITLE_DIARIO } from "../../constants";
  import { Clock, User, BookOpen, Hash, Loader2 } from '@lucide/svelte';
  import SlideOver from './SlideOver.svelte';

  interface Props {
    isOpen: boolean;
    onClose: () => void;
    onSelect: (data: DiarioData) => void;
    filterGrado?: string;
    filterMateria?: string;
  }

  let { isOpen = $bindable(), onClose, onSelect, filterGrado = "", filterMateria = "" }: Props = $props();

  interface DiarioData {
    timestamp: string;
    fecha: string;
    horas: string;
    docente: string;
    materia: string;
    grado: string;
    diarioCampo: string;
  }

  let diarios: DiarioData[] = $state([]);
  let isLoading = $state(false);

  const loadDiarios = async () => {
    isLoading = true;
    try {
      const data = await getDiario({
        spreadsheetId: SPREADSHEET_ID_DIARIO,
        worksheetTitle: WORKSHEET_TITLE_DIARIO,
      });

      if (data?.records && Array.isArray(data.records)) {
        const headers = data.records[0]?.values || [];
        diarios = data.records.slice(1).map((row: any) => {
          const values = row.values || [];
          return {
            timestamp: values[0] || "",
            fecha: values[1] || "",
            horas: values[2] || "",
            docente: values[3] || "",
            materia: values[4] || "",
            grado: values[5] || "",
            diarioCampo: values[6] || "",
          };
        }).filter((item: DiarioData) => item.docente && item.fecha);
      }
    } catch (error) {
      console.error("Error cargando diarios:", error);
    } finally {
      isLoading = false;
    }
  };

  $effect(() => {
    if (isOpen) {
      loadDiarios();
    }
  });

  let filteredDiarios = $derived(() => {
    let result = diarios;

    if (filterGrado && filterGrado.trim()) {
      const normalizedFilter = filterGrado.toLowerCase().trim();
      result = result.filter(d =>
        d.grado && d.grado.toLowerCase().trim() === normalizedFilter
      );
    }

    if (filterMateria && filterMateria.trim()) {
      const normalizedFilter = filterMateria.toLowerCase().trim();
      result = result.filter(d =>
        d.materia && d.materia.toLowerCase().trim() === normalizedFilter
      );
    }

    return [...result].sort((a, b) => b.fecha.localeCompare(a.fecha));
  });

  const handleSelect = (diario: DiarioData) => {
    onSelect(diario);
    isOpen = false;
    onClose();
  };

  const formatGrado = (g: string) => g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2");
  const truncate = (text: string, max: number) => text.length > max ? text.substring(0, max) + '...' : text;
</script>

<SlideOver bind:isOpen {onClose} title="Historial de Diario de Campo" size="lg">
  <div class="flex flex-col h-full">
    <!-- List -->
    <div class="flex-1 overflow-y-auto p-4">
      {#if isLoading}
        <div class="flex items-center justify-center py-12">
          <Loader2 class="w-8 h-8 animate-spin" style="color: rgb(var(--text-muted));" />
          <span class="ml-3" style="color: rgb(var(--text-secondary));">Cargando diarios...</span>
        </div>
      {:else if filteredDiarios().length === 0}
        <div class="text-center py-12">
          <Clock class="w-12 h-12 mx-auto mb-4 opacity-40" style="color: rgb(var(--text-muted));" />
          <p style="color: rgb(var(--text-secondary));">
            {filterGrado || filterMateria ? "No hay diarios para estos filtros" : "No hay diarios registrados"}
          </p>
        </div>
      {:else}
        <div class="space-y-3">
          {#each filteredDiarios() as diario, i}
            <button
              onclick={() => handleSelect(diario)}
              class="w-full text-left p-4 rounded-xl border transition-all hover:scale-[1.01] hover:shadow-md"
              style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary));"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-medium px-2 py-0.5 rounded-full" style="background-color: rgb(var(--accent-primary) / 0.1); color: rgb(var(--accent-primary));">
                      {diario.fecha}
                    </span>
                    {#if diario.horas}
                      <span class="text-xs opacity-60">{diario.horas}h</span>
                    {/if}
                  </div>
                  <div class="flex items-center gap-3 text-sm mb-1">
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <User class="w-3.5 h-3.5" />
                      {diario.docente}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 text-sm mb-2">
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <BookOpen class="w-3.5 h-3.5" />
                      {diario.materia}
                    </span>
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <Hash class="w-3.5 h-3.5" />
                      {formatGrado(diario.grado)}
                    </span>
                  </div>
                  {#if diario.diarioCampo}
                    <p class="text-sm opacity-60 truncate" style="color: rgb(var(--text-secondary));">
                      📝 {truncate(diario.diarioCampo, 80)}
                    </p>
                  {/if}
                </div>
              </div>
            </button>
          {/each}
        </div>
      {/if}
    </div>
  </div>
</SlideOver>