<script lang="ts">
  import { Check, X, AlertCircle, Info, Loader2 } from '@lucide/svelte';
  import { fly, fade } from 'svelte/transition';

  type ToastType = 'success' | 'error' | 'warning' | 'info' | 'loading';

  interface Props {
    type?: ToastType;
    message: string;
    duration?: number;
    onClose?: () => void;
  }

  let { type = 'info', message, duration = 4000, onClose }: Props = $props();

  const icons = {
    success: Check,
    error: X,
    warning: AlertCircle,
    info: Info,
    loading: Loader2,
  };

  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-amber-500',
    info: 'bg-blue-500',
    loading: 'bg-indigo-500',
  };

  let isVisible = $state(true);

  $effect(() => {
    if (duration > 0 && type !== 'loading') {
      const timer = setTimeout(() => {
        isVisible = false;
        setTimeout(() => onClose?.(), 300);
      }, duration);
      return () => clearTimeout(timer);
    }
  });

  const Icon = $derived(icons[type]);
  const bgColor = $derived(colors[type]);
</script>

{#if isVisible}
  <div
    transition:fly={{ y: -20, duration: 300 }}
    class="fixed top-4 right-4 z-50 max-w-sm w-full rounded-xl shadow-lg border p-4 flex items-start gap-3"
    style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--card-border));"
    role="alert"
  >
    <div
      class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {bgColor} {type === 'loading' ? 'animate-spin' : ''}"
    >
      <Icon class="w-4 h-4 text-white" />
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium" style="color: rgb(var(--text-primary));">
        {message}
      </p>
    </div>
    {#if type !== 'loading'}
      <button
        onclick={() => { isVisible = false; setTimeout(() => onClose?.(), 300); }}
        class="flex-shrink-0 p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
        aria-label="Cerrar"
      >
        <X class="w-4 h-4" style="color: rgb(var(--text-muted))" />
      </button>
    {/if}
  </div>
{/if}