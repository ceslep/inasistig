<script lang="ts">
  import { onMount, createEventDispatcher } from "svelte";
  import { slide } from "svelte/transition";
  import { getDiarioOptions } from "../../api/service"; // Adjust path if needed
  import { theme } from "../lib/themeStore"; // For theme-based styling
  import Loader from "./Loader.svelte"; // Re-use Loader component

  // --- Props ---
  // Prop to bind the selected annotations back to the parent component (Diario.svelte)
  // This will be an array of strings, which the parent will then join into formData.diarioCampo
  export let selectedDiarioAnots: string[] = [];

  // --- Interfaces ---
  interface DiarioOption {
    id: string;
    categoria: string;
    titulo: string;
    descripcion: string;
    impacto: number; // Not directly used in UI, but part of data
    tiempo_estimado: number; // Not directly used in UI, but part of data
  }

  // Similar to OpcionAnotacion in Anotador.svelte
  interface DiarioOpcionAnotacion {
    id: string; // Keep original ID for potential future use
    text: string; // The editable description
    selected: boolean;
    originalTitulo: string; // Keep original title for context
    categoria: string;
  }

  // --- State ---
  let diarioOpcionesGrupos: Record<string, DiarioOpcionAnotacion[]> = {};
  let expandedCategories: Record<string, boolean> = {}; // New state for accordion
  let isLoadingOptions = false;

  const dispatch = createEventDispatcher();

  // --- Reactive statements ---
  // Reactively update selectedDiarioAnots when diarioOpcionesGrupos changes
  $: {
    const newlySelectedAnots = Object.values(diarioOpcionesGrupos)
      .flat()
      .filter((o) => o.selected && o.text.trim() !== "")
      .map((o) => `[${o.categoria}] ${o.originalTitulo}: ${o.text}`);
    // Only dispatch if the array content actually changed to avoid infinite loops
    if (JSON.stringify(newlySelectedAnots) !== JSON.stringify(selectedDiarioAnots)) {
      selectedDiarioAnots = newlySelectedAnots;
      dispatch("change", selectedDiarioAnots); // Notify parent of changes
    }
  }

  // Reactivity for styles
  $: styles = {
    text: "rgb(var(--text-primary))",
    label: "rgb(var(--text-secondary))",
    border: "rgb(var(--border-primary))",
    inputBg: "rgb(var(--bg-secondary))",
  };

  // --- Functions ---
  const toggleCategory = (category: string) => {
    // If the clicked category is already expanded, close it
    if (expandedCategories[category]) {
      expandedCategories[category] = false;
    } else {
      // Close all other categories
      for (const cat in expandedCategories) {
        expandedCategories[cat] = false;
      }
      // Open the clicked category
      expandedCategories[category] = true;
    }
    // This line is needed to trigger reactivity when updating an object property
    expandedCategories = expandedCategories;
  };

  const getCategoryColor = (cat: string) => {
    const colors: Record<string, string> = {
      "Administrativo": "#6366f1", // Indigo
      "Emergencia": "#ef4444",    // Red
      "Académico": "#f59e0b",     // Amber
      "Convivencia": "#22c55e",   // Green
      "Infraestructura": "#0ea5e9", // Sky Blue
      "Salud": "#ec4899",         // Pink
      "Tecnológico": "#8b5cf6",   // Violet
      "Comunicación": "#3b82f6",  // Blue
    };
    return colors[cat] || "#6366f1"; // Default to Indigo
  };


  const loadOptions = async () => {
    isLoadingOptions = true;
    try {
      const optionsData = await getDiarioOptions();

      const transformed: Record<string, DiarioOpcionAnotacion[]> = {};
      const initialExpandedState: Record<string, boolean> = {};

      // Initialize categories and options
      optionsData.forEach((option) => {
        if (!transformed[option.categoria]) {
          transformed[option.categoria] = [];
          initialExpandedState[option.categoria] = false; // All categories start minimized
        }
        transformed[option.categoria].push({
          id: option.id,
          text: option.descripcion, // Use description as initial editable text
          selected: false,
          originalTitulo: option.titulo, // Keep original title for display context
          categoria: option.categoria,
        });
      });

      diarioOpcionesGrupos = transformed;
      expandedCategories = initialExpandedState;
    } catch (error) {
      console.error("Error cargando opciones de diario:", error);
      // Optionally, show a user-friendly error message
    } finally {
      isLoadingOptions = false;
    }
  };

  onMount(() => {
    loadOptions();
  });
</script>

<div class="space-y-8">
  {#if isLoadingOptions}
    <div class="flex items-center justify-center py-12">
      <Loader message="Cargando opciones de diario..." />
    </div>
  {:else}
    {#each Object.entries(diarioOpcionesGrupos) as [categoria, opciones]}
      {@const catColor = getCategoryColor(categoria)}
      <div class="space-y-4">
        <button
          type="button"
          on:click={() => toggleCategory(categoria)}
          class="flex items-center gap-3 w-full text-left cursor-pointer group focus:outline-none"
        >
          <h3
            class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full text-white flex-shrink-0 transition-all duration-300 group-hover:shadow-md"
            style="background-color: {catColor};"
          >
            {categoria}
          </h3>
          <div
            class="h-px flex-1 transition-all duration-300 group-hover:opacity-50"
            style="background-color: {catColor}; opacity: 0.2;"
          ></div>
          <svg
            class="w-5 h-5 text-gray-500 transform transition-transform duration-200 flex-shrink-0"
            class:rotate-180={expandedCategories[categoria]}
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>

        {#if expandedCategories[categoria]}
          <div transition:slide class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {#each opciones as opcion (opcion.id)}
              <div
                class="relative group flex flex-col p-0 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md"
                style="
                  background-color: {opcion.selected
                  ? `${catColor}10`
                  : styles.inputBg};
                  border-color: {opcion.selected
                  ? catColor
                  : styles.border};
                "
              >
                <div class="flex items-start gap-1 p-4">
                  <label class="flex-shrink-0 cursor-pointer p-1 mt-1">
                    <input
                      type="checkbox"
                      bind:checked={opcion.selected}
                      class="hidden"
                      on:change={() => {
                        // Trigger reactivity for selectedDiarioAnots after change
                        diarioOpcionesGrupos = diarioOpcionesGrupos;
                      }}
                    />
                    <div
                      class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                      style="
                        border-color: {opcion.selected
                        ? catColor
                        : styles.border};
                        background-color: {opcion.selected
                        ? catColor
                        : 'transparent'};
                      "
                    >
                      {#if opcion.selected}
                        <svg
                          class="w-3 h-3 text-white"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      {/if}
                    </div>
                  </label>

                  <textarea
                    bind:value={opcion.text}
                    rows="8"
                    class="w-full bg-transparent border-none focus:ring-0 text-base font-medium leading-relaxed resize-none p-1 transition-colors min-h-[180px] pb-6"
                    style="color: {styles.text};"
                    placeholder="Escriba aquí..."
                    on:input={() => {
                       // Trigger reactivity for selectedDiarioAnots after text change
                       diarioOpcionesGrupos = diarioOpcionesGrupos;
                    }}
                  ></textarea>
                </div>
              </div>
            {/each}
          </div>
        {/if}
      </div>
    {/each}
  {/if}
</div>