<script lang="ts">
  import { X } from '@lucide/svelte';
  import { fade } from 'svelte/transition';

  interface Props {
    isOpen: boolean;
    onClose: () => void;
    title?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    children?: import('svelte').Snippet;
  }

  let {
    isOpen = $bindable(),
    onClose,
    title = 'Panel',
    size = 'md',
    children,
  }: Props = $props();

  const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
  };

  const handleBackdropClick = (e: MouseEvent) => {
    if (e.target === e.currentTarget) {
      isOpen = false;
      onClose();
    }
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      isOpen = false;
      onClose();
    }
  };
</script>

<svelte:window onkeydown={handleKeydown} />

{#if isOpen}
  <div
    class="fixed inset-0 z-50 flex"
    role="dialog"
    aria-modal="true"
    aria-label={title}
    transition:fade={{ duration: 200 }}
    onclick={handleBackdropClick}
    onkeydown={(e) => e.stopPropagation()}
  >
    <!-- Backdrop -->
    <div 
      class="absolute inset-0 bg-black/50 backdrop-blur-sm"
      aria-hidden="true"
    ></div>

    <!-- Panel -->
    <div 
      class="relative ml-auto h-full {sizeClasses[size]} w-full shadow-2xl flex flex-col"
      transition:fade={{ duration: 200 }}
    >
      <div 
        class="flex items-center justify-between px-4 py-3 border-b shrink-0"
        style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary));"
      >
        <h2 
          class="text-lg font-semibold"
          style="color: rgb(var(--text-primary));"
        >
          {title}
        </h2>
        <button
          onclick={() => { isOpen = false; onClose(); }}
          class="p-2 rounded-lg transition-colors hover:bg-black/5 dark:hover:bg-white/5"
          style="color: rgb(var(--text-secondary));"
          aria-label="Cerrar panel"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div 
        class="flex-1 overflow-y-auto"
        style="background-color: rgb(var(--bg-primary));"
      >
        {@render children?.()}
      </div>
    </div>
  </div>
{/if}

<style>
  .fixed {
    position: fixed;
  }
  .inset-0 {
    inset: 0;
  }
  .absolute {
    position: absolute;
  }
  .relative {
    position: relative;
  }
  .ml-auto {
    margin-left: auto;
  }
  .h-full {
    height: 100%;
  }
  .w-full {
    width: 100%;
  }
  .flex {
    display: flex;
  }
  .flex-col {
    flex-direction: column;
  }
  .items-center {
    align-items: center;
  }
  .justify-between {
    justify-content: space-between;
  }
  .shrink-0 {
    flex-shrink: 0;
  }
  .flex-1 {
    flex: 1 1 0%;
  }
  .overflow-y-auto {
    overflow-y: auto;
  }
  .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }
  .border-b {
    border-bottom-width: 1px;
  }
  .shadow-2xl {
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  }
  .rounded-lg {
    border-radius: 0.5rem;
  }
  .p-2 {
    padding: 0.5rem;
  }
  .text-lg {
    font-size: 1.125rem;
  }
  .font-semibold {
    font-weight: 600;
  }
</style>