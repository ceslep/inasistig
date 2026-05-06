<script>
  import Icon from '@iconify/svelte'

  let toasts = $state([])
  let nextId = 0

  const iconMap = {
    success: 'mdi:check-circle',
    error: 'mdi:alert-circle',
    warning: 'mdi:alert',
    info: 'mdi:information'
  }

  const colorMap = {
    success: 'bg-emerald-500',
    error: 'bg-rose-500',
    warning: 'bg-amber-500',
    info: 'bg-primary-500'
  }

  const bgMap = {
    success: 'bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-800',
    error: 'bg-rose-50 dark:bg-rose-950 border-rose-200 dark:border-rose-800',
    warning: 'bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800',
    info: 'bg-blue-50 dark:bg-blue-950 border-blue-200 dark:border-blue-800'
  }

  const textMap = {
    success: 'text-emerald-800 dark:text-emerald-200',
    error: 'text-rose-800 dark:text-rose-200',
    warning: 'text-amber-800 dark:text-amber-200',
    info: 'text-blue-800 dark:text-blue-200'
  }

  export function show(message, type = 'success', duration = 3500) {
    const id = nextId++
    toasts = [...toasts, { id, message, type, leaving: false }]

    setTimeout(() => {
      toasts = toasts.map(t => t.id === id ? { ...t, leaving: true } : t)
      setTimeout(() => {
        toasts = toasts.filter(t => t.id !== id)
      }, 300)
    }, duration)
  }

  function dismiss(id) {
    toasts = toasts.map(t => t.id === id ? { ...t, leaving: true } : t)
    setTimeout(() => {
      toasts = toasts.filter(t => t.id !== id)
    }, 300)
  }
</script>

{#if toasts.length > 0}
  <div class="toast-container">
    {#each toasts as toast (toast.id)}
      <div class="toast-item border {bgMap[toast.type]}"
        class:toast-enter={!toast.leaving}
        class:toast-leave={toast.leaving}>
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full {colorMap[toast.type]} flex items-center justify-center shrink-0">
            <Icon icon={iconMap[toast.type]} class="text-white text-lg" />
          </div>
          <p class="text-sm font-medium {textMap[toast.type]} flex-1">{toast.message}</p>
          <button onclick={() => dismiss(toast.id)}
            class="w-6 h-6 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center transition-colors shrink-0">
            <Icon icon="mdi:close" class="text-sm text-slate-400" />
          </button>
        </div>
      </div>
    {/each}
  </div>
{/if}

<style>
  .toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-width: 380px;
    width: calc(100% - 2rem);
    pointer-events: none;
  }

  .toast-item {
    pointer-events: all;
    padding: 0.875rem 1rem;
    border-radius: 0.875rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(12px);
  }

  .toast-enter {
    animation: toastIn 0.35s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
  }

  .toast-leave {
    animation: toastOut 0.3s ease-in forwards;
  }

  @keyframes toastIn {
    from {
      opacity: 0;
      transform: translateX(100%) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translateX(0) scale(1);
    }
  }

  @keyframes toastOut {
    from {
      opacity: 1;
      transform: translateX(0) scale(1);
    }
    to {
      opacity: 0;
      transform: translateX(100%) scale(0.95);
    }
  }
</style>
