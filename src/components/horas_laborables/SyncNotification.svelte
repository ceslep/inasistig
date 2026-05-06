<script lang="ts">
  type NotificationType = 'saving' | 'saved' | 'error' | 'info';

  let { 
    show = $bindable(false), 
    message = "Sincronizando...", 
    type = 'saving' as NotificationType 
  } = $props();

  const config: Record<NotificationType, { bgColor: string; icon: string; textColor: string; borderColor: string }> = {
    saving: {
      bgColor: 'bg-gradient-to-r from-blue-600 to-indigo-600',
      icon: '⏳',
      textColor: 'text-white',
      borderColor: 'border-blue-500'
    },
    saved: {
      bgColor: 'bg-gradient-to-r from-emerald-600 to-teal-600',
      icon: '✅',
      textColor: 'text-white',
      borderColor: 'border-emerald-500'
    },
    error: {
      bgColor: 'bg-gradient-to-r from-rose-600 to-red-600',
      icon: '❌',
      textColor: 'text-white',
      borderColor: 'border-rose-500'
    },
    info: {
      bgColor: 'bg-gradient-to-r from-purple-600 to-violet-600',
      icon: 'ℹ️',
      textColor: 'text-white',
      borderColor: 'border-purple-500'
    }
  };

  const currentConfig = $derived(config[type]);
</script>

{#if show}
  <div
    class="fixed top-4 right-4 z-[9999] flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border backdrop-blur-xl transition-all duration-500 ease-out transform min-w-[320px] max-w-[400px] {currentConfig.bgColor} {currentConfig.textColor} {currentConfig.borderColor}"
    role="alert"
    aria-live="polite"
    style="box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(255, 255, 255, 0.05);"
  >
    <!-- Animated Icon -->
    <div class="flex-shrink-0">
      {#if type === 'saving'}
        <div class="w-6 h-6 relative">
          <div class="absolute inset-0 border-2 border-white/30 rounded-full"></div>
          <div class="absolute inset-0 border-2 border-white rounded-full border-t-transparent animate-spin"></div>
        </div>
      {:else}
        <span class="text-xl">{currentConfig.icon}</span>
      {/if}
    </div>

    <!-- Message -->
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold">
        {message}
      </p>
      {#if type === 'saving'}
        <p class="text-xs opacity-90 mt-1">
          Por favor espera mientras sincronizamos...
        </p>
      {/if}
    </div>

    <!-- Progress indicator for saving -->
    {#if type === 'saving'}
      <div class="w-8 h-1 bg-white/30 rounded-full overflow-hidden">
        <div 
          class="h-full bg-white rounded-full"
          style="animation: progress 2s ease-in-out infinite;"
        ></div>
      </div>
    {/if}

    <!-- Close button for non-saving notifications -->
    {#if type !== 'saving'}
      <button
        type="button"
        onclick={() => show = false}
        class="flex-shrink-0 ml-2 p-1 rounded-lg hover:bg-white/20 transition-colors"
        aria-label="Cerrar notificación"
        title="Cerrar"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    {/if}
  </div>

  <style>
    @keyframes progress {
      0% {
        transform: translateX(-100%);
        opacity: 0.3;
      }
      50% {
        opacity: 1;
      }
      100% {
        transform: translateX(100%);
        opacity: 0.3;
      }
    }
  </style>
{/if}