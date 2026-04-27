<script lang="ts">
  import { onMount } from 'svelte';
  import { slide } from 'svelte/transition';
  import { getDiarioOptions } from '../../api/service';
  import { theme } from '../lib/themeStore';
  import Loader from './Loader.svelte';
  import { ChevronDown, Check } from '@lucide/svelte';

  interface Props {
    selectedDiarioAnots?: string[];
    onchange?: (anots: string[]) => void;
  }

  let { selectedDiarioAnots = $bindable([]), onchange }: Props = $props();

  interface DiarioOption {
    id: string;
    categoria: string;
    titulo: string;
    descripcion: string;
    impacto: number;
    tiempo_estimado: number;
  }

  interface DiarioOpcionAnotacion {
    id: string;
    text: string;
    selected: boolean;
    originalTitulo: string;
    categoria: string;
    impacto: number;
    tiempo_estimado: number;
  }

  let diarioOpcionesGrupos: Record<string, DiarioOpcionAnotacion[]> = $state({});
  let expandedCategories: Record<string, boolean> = $state({});
  let isLoadingOptions = $state(false);

  const styles = $derived({
    text: 'rgb(var(--text-primary))',
    label: 'rgb(var(--text-secondary))',
    border: 'rgb(var(--border-primary))',
    inputBg: 'rgb(var(--bg-secondary))',
  });

  function syncSelectedAnots() {
    const newlySelectedAnots = Object.values(diarioOpcionesGrupos)
      .flat()
      .filter((o) => o.selected && o.text.trim() !== '')
      .map((o) => `[${o.categoria}] ${o.originalTitulo}: ${o.text}`);
    if (JSON.stringify(newlySelectedAnots) !== JSON.stringify(selectedDiarioAnots)) {
      selectedDiarioAnots = newlySelectedAnots;
      onchange?.(selectedDiarioAnots);
    }
  }

  const toggleCategory = (category: string) => {
    if (expandedCategories[category]) {
      expandedCategories[category] = false;
    } else {
      for (const cat in expandedCategories) {
        expandedCategories[cat] = false;
      }
      expandedCategories[category] = true;
    }
  };

  const getCategoryColor = (cat: string) => {
    const colors: Record<string, string> = {
      'Administrativo': '#6366f1',
      'Emergencia': '#ef4444',
      'Académico': '#f59e0b',
      'Convivencia': '#22c55e',
      'Infraestructura': '#0ea5e9',
      'Salud': '#ec4899',
      'Tecnológico': '#8b5cf6',
      'Comunicación': '#3b82f6',
      'Disciplinario': '#64748b',
      'Normalidad': '#10b981',
    };
    return colors[cat] || '#6366f1';
  };

  const loadOptions = async () => {
    isLoadingOptions = true;
    try {
      const response = await getDiarioOptions();
      const situaciones = response.situaciones || {};

      const transformed: Record<string, DiarioOpcionAnotacion[]> = {};
      const initialExpandedState: Record<string, boolean> = {};

      Object.entries(situaciones).forEach(([categoria, items]) => {
        if (items.length > 0) {
          initialExpandedState[categoria] = false;
          transformed[categoria] = items.map((option) => ({
            id: option.id,
            text: option.descripcion,
            selected: false,
            originalTitulo: option.titulo,
            categoria: option.categoria,
            impacto: option.impacto,
            tiempo_estimado: option.tiempo_estimado,
          }));
        }
      });

      diarioOpcionesGrupos = transformed;
      expandedCategories = initialExpandedState;
    } catch (error) {
      console.error('Error cargando opciones de diario:', error);
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
          onclick={() => toggleCategory(categoria)}
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
          <ChevronDown
            class="w-5 h-5 text-gray-500 transform transition-transform duration-200 flex-shrink-0 {expandedCategories[categoria] ? 'rotate-180' : ''}"
          />
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
                <div class="flex flex-col p-4 gap-3">
                   <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                      <label class="flex-shrink-0 cursor-pointer">
                        <input
                          type="checkbox"
                          bind:checked={opcion.selected}
                          class="hidden"
                          onchange={() => syncSelectedAnots()}
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
                            <Check class="w-3 h-3 text-white" />
                          {/if}
                        </div>
                      </label>
                      <span class="font-bold text-sm leading-tight" style="color: {styles.text};">
                        {opcion.originalTitulo}
                      </span>
                    </div>

                    <div class="flex gap-1.5 flex-shrink-0">
                      <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 opacity-60" style="color: {styles.text};">
                        IMP: {opcion.impacto}
                      </span>
                      <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 opacity-60" style="color: {styles.text};">
                        {opcion.tiempo_estimado} MIN
                      </span>
                    </div>
                  </div>

                  <textarea
                    bind:value={opcion.text}
                    rows="4"
                    class="w-full bg-transparent border-none focus:ring-0 text-sm font-medium leading-relaxed resize-none p-0 transition-colors opacity-80"
                    style="color: {styles.text};"
                    placeholder="Escriba aquí..."
                    oninput={() => syncSelectedAnots()}
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
