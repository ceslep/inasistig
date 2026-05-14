<script lang="ts">
  import { getAnotador } from "../../../api/service";
  import { SPREADSHEET_ID_ANOTADOR, WORKSHEET_TITLE_ANOTADOR } from "../../constants";
  import { Clock, User, BookOpen, Hash, Loader2 } from '@lucide/svelte';
  import SlideOver from './SlideOver.svelte';

  interface Props {
    isOpen: boolean;
    onClose: () => void;
    onSelect: (data: AnnotationData) => void;
    filterGrado?: string;
    filterMateria?: string;
  }

  let { isOpen = $bindable(), onClose, onSelect, filterGrado = "", filterMateria = "" }: Props = $props();

  interface AnnotationData {
    timestamp: string;
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    horas: string;
    anotacion: string;
    observacion: string;
  }

  let annotations: AnnotationData[] = $state([]);
  let isLoading = $state(false);

  const loadAnnotations = async () => {
    isLoading = true;
    try {
      const data = await getAnotador({
        spreadsheetId: SPREADSHEET_ID_ANOTADOR,
        worksheetTitle: WORKSHEET_TITLE_ANOTADOR,
      });

      if (data?.records && Array.isArray(data.records)) {
        const headers = data.records[0]?.values || [];
        annotations = data.records.slice(1).map((row: any) => {
          const values = row.values || [];
          return {
            timestamp: values[0] || "",
            fecha: values[1] || "",
            docente: values[2] || "",
            materia: values[3] || "",
            grado: values[4] || "",
            horas: values[5] || "",
            anotacion: values[6] || "",
            observacion: values[7] || "",
          };
        }).filter((item: AnnotationData) => item.docente && item.fecha);
      }
    } catch (error) {
      console.error("Error cargando anotaciones:", error);
    } finally {
      isLoading = false;
    }
  };

  $effect(() => {
    if (isOpen) {
      loadAnnotations();
    }
  });

  let filteredAnnotations = $derived(() => {
    let result = annotations;

    if (filterGrado && filterGrado.trim()) {
      const normalizedFilter = filterGrado.toLowerCase().trim();
      result = result.filter(a =>
        a.grado && a.grado.toLowerCase().trim() === normalizedFilter
      );
    }

    if (filterMateria && filterMateria.trim()) {
      const normalizedFilter = filterMateria.toLowerCase().trim();
      result = result.filter(a =>
        a.materia && a.materia.toLowerCase().trim() === normalizedFilter
      );
    }

    return [...result].sort((a, b) => b.fecha.localeCompare(a.fecha));
  });

  const handleSelect = (annotation: AnnotationData) => {
    onSelect(annotation);
    isOpen = false;
    onClose();
  };

  const formatGrado = (g: string) => g.replace(/0(\d)$/, "°$1").replace(/(\d{1,2})0(\d)/, "$1°$2");
  const truncate = (text: string, max: number) => text.length > max ? text.substring(0, max) + '...' : text;
</script>

<SlideOver bind:isOpen {onClose} title="Historial de Anotaciones" size="lg">
  <div class="flex flex-col h-full">
    <!-- List -->
    <div class="flex-1 overflow-y-auto p-4">
      {#if isLoading}
        <div class="flex items-center justify-center py-12">
          <Loader2 class="w-8 h-8 animate-spin" style="color: rgb(var(--text-muted));" />
          <span class="ml-3" style="color: rgb(var(--text-secondary));">Cargando anotaciones...</span>
        </div>
      {:else if filteredAnnotations().length === 0}
        <div class="text-center py-12">
          <Clock class="w-12 h-12 mx-auto mb-4 opacity-40" style="color: rgb(var(--text-muted));" />
          <p style="color: rgb(var(--text-secondary));">
            {filterGrado || filterMateria ? "No hay anotaciones para estos filtros" : "No hay anotaciones registradas"}
          </p>
        </div>
      {:else}
        <div class="space-y-3">
          {#each filteredAnnotations() as annotation, i}
            <button
              onclick={() => handleSelect(annotation)}
              class="w-full text-left p-4 rounded-xl border transition-all hover:scale-[1.01] hover:shadow-md"
              style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary));"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-medium px-2 py-0.5 rounded-full" style="background-color: rgb(var(--accent-primary) / 0.1); color: rgb(var(--accent-primary));">
                      {annotation.fecha}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 text-sm mb-1">
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <User class="w-3.5 h-3.5" />
                      {annotation.docente}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 text-sm mb-2">
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <BookOpen class="w-3.5 h-3.5" />
                      {annotation.materia}
                    </span>
                    <span class="flex items-center gap-1" style="color: rgb(var(--text-secondary));">
                      <Hash class="w-3.5 h-3.5" />
                      {formatGrado(annotation.grado)}
                    </span>
                  </div>
                  {#if annotation.anotacion}
                    <p class="text-sm opacity-60 truncate" style="color: rgb(var(--text-secondary));">
                      📝 {truncate(annotation.anotacion, 80)}
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