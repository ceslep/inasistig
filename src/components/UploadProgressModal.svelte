<script lang="ts">
  import { fade, scale } from 'svelte/transition';
  import { CheckCircle2, XCircle, Loader2, FileSpreadsheet, Cloud, Sparkles } from '@lucide/svelte';

  type Phase = 'idle' | 'generating' | 'uploading' | 'done';

  interface Props {
    phase: Phase;
    current: number;
    total: number;
    currentFile: string;
    successCount: number;
    failedCount: number;
  }

  let { phase, current, total, currentFile, successCount, failedCount }: Props = $props();

  const phaseConfig = $derived(() => {
    switch (phase) {
      case 'idle':
        return {
          icon: Loader2,
          title: 'Preparando',
          subtitle: 'Iniciando proceso...',
          color: 'text-gray-500',
          bgColor: 'bg-gray-500/10',
          progressColor: 'from-gray-400 to-gray-500'
        };
      case 'generating':
        return {
          icon: FileSpreadsheet,
          title: 'Generando reportes',
          subtitle: 'Creando archivos Excel...',
          color: 'text-green-500',
          bgColor: 'bg-green-500/10',
          progressColor: 'from-green-400 to-emerald-500'
        };
      case 'uploading':
        return {
          icon: Cloud,
          title: 'Subiendo a Drive',
          subtitle: 'Guardando en Google Drive...',
          color: 'text-blue-500',
          bgColor: 'bg-blue-500/10',
          progressColor: 'from-blue-400 to-indigo-500'
        };
      case 'done':
        return {
          icon: Sparkles,
          title: 'Completado',
          subtitle: successCount > 0 ? `${successCount} archivo${successCount > 1 ? 's' : ''} guardado${successCount > 1 ? 's' : ''} exitosamente` : 'Proceso terminado',
          color: failedCount > 0 ? 'text-amber-500' : 'text-green-500',
          bgColor: failedCount > 0 ? 'bg-amber-500/10' : 'bg-green-500/10',
          progressColor: failedCount > 0 ? 'from-amber-400 to-orange-500' : 'from-green-400 to-emerald-500'
        };
    }
  });

  const progressPercent = $derived(total > 0 ? Math.round((current / total) * 100) : 0);
</script>

<div
  class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center z-[100] p-4"
  transition:fade={{ duration: 200 }}
>
  <div
    class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-3xl w-full max-w-md overflow-hidden shadow-2xl"
    in:scale={{ start: 0.95, duration: 300 }}
  >
    <div class="p-8 text-center">
      {#if phase === 'done'}
        {#if failedCount > 0}
          <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-amber-500/10 flex items-center justify-center">
            <Sparkles class="w-10 h-10 text-amber-500" />
          </div>
        {:else}
          <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-500/10 flex items-center justify-center">
            <CheckCircle2 class="w-10 h-10 text-green-500" />
          </div>
        {/if}
      {:else}
        <div class="w-20 h-20 mx-auto mb-6 rounded-full {phaseConfig().bgColor} flex items-center justify-center">
          <Loader2 class="w-10 h-10 {phaseConfig().color} animate-spin" />
        </div>
      {/if}

      <h3 class="text-2xl font-bold text-[rgb(var(--text-primary))] mb-2">
        {phaseConfig().title}
      </h3>
      <p class="text-[rgb(var(--text-secondary))] mb-6">
        {phaseConfig().subtitle}
      </p>

      {#if phase !== 'done'}
        <div class="space-y-4">
          <div class="w-full h-3 bg-[rgb(var(--bg-secondary))] rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r {phaseConfig().progressColor} rounded-full transition-all duration-500 ease-out"
              style="width: {progressPercent}%"
            ></div>
          </div>
          
          <div class="flex items-center justify-between text-sm">
            <span class="text-[rgb(var(--text-secondary))]">
              {current} de {total}
            </span>
            <span class="text-[rgb(var(--text-muted))]">
              {progressPercent}%
            </span>
          </div>

          {#if currentFile && phase === 'uploading'}
            <p class="text-xs text-[rgb(var(--text-muted))] mt-4 truncate px-4">
              {currentFile}
            </p>
          {/if}
        </div>
      {:else}
        <div class="space-y-3">
          <div class="flex items-center justify-center gap-6 py-4">
            <div class="text-center">
              <div class="text-3xl font-bold {failedCount > 0 ? 'text-amber-500' : 'text-green-500'}">
                {successCount}
              </div>
              <div class="text-xs text-[rgb(var(--text-secondary))]">Exitosos</div>
            </div>
            {#if failedCount > 0}
              <div class="w-px h-12 bg-[rgb(var(--border-primary))]"></div>
              <div class="text-center">
                <div class="text-3xl font-bold text-red-500">
                  {failedCount}
                </div>
                <div class="text-xs text-[rgb(var(--text-secondary))]">Fallidos</div>
              </div>
            {/if}
          </div>
        </div>
      {/if}
    </div>
  </div>
</div>