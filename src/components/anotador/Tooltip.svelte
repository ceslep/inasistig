<script lang="ts">
  import { fade, fly } from 'svelte/transition';
  import { X, HelpCircle } from '@lucide/svelte';

  interface Props {
    children?: import('svelte').Snippet;
    title?: string;
    content?: string;
    position?: 'top' | 'bottom' | 'left' | 'right';
  }

  let {
    children,
    title = '',
    content = '',
    position = 'top',
  }: Props = $props();

  let isVisible = $state(false);

  const positionClasses = {
    top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
    left: 'right-full top-1/2 -translate-y-1/2 mr-2',
    right: 'left-full top-1/2 -translate-y-1/2 ml-2',
  };

  const arrowClasses = {
    top: 'top-full left-1/2 -translate-x-1/2 border-t-gray-900 dark:border-t-gray-100',
    bottom: 'bottom-full left-1/2 -translate-x-1/2 border-b-gray-900 dark:border-b-gray-100',
    left: 'left-full top-1/2 -translate-y-1/2 border-l-gray-900 dark:border-l-gray-100',
    right: 'right-full top-1/2 -translate-y-1/2 border-r-gray-900 dark:border-r-gray-100',
  };
</script>

<div class="relative inline-flex">
  <button
    type="button"
    class="tooltip-trigger p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
    onmouseenter={() => isVisible = true}
    onmouseleave={() => isVisible = false}
    onfocus={() => isVisible = true}
    onblur={() => isVisible = false}
    aria-label="Información"
  >
    <HelpCircle class="w-4 h-4" style="color: rgb(var(--text-muted));" />
  </button>

  {#if isVisible}
    <div
      class="tooltip-content absolute z-50 {positionClasses[position]} w-64 p-3 rounded-xl shadow-lg border text-sm"
      style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
      role="tooltip"
      transition:fade={{ duration: 150 }}
    >
      {#if title}
        <p class="font-semibold mb-1">{title}</p>
      {/if}
      {#if content}
        <p class="opacity-80">{content}</p>
      {/if}
      {#if children}
        {@render children()}
      {/if}
      <div class="absolute {arrowClasses[position]} border-6 border-transparent"></div>
    </div>
  {/if}
</div>

<style>
  .tooltip-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .tooltip-content {
    animation: tooltip-appear 150ms ease-out;
  }
  @keyframes tooltip-appear {
    from {
      opacity: 0;
      transform: translateY(4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .absolute {
    position: absolute;
  }
  .relative {
    position: relative;
  }
  .inline-flex {
    display: inline-flex;
  }
  .z-50 {
    z-index: 50;
  }
  .w-64 {
    width: 16rem;
  }
  .p-3 {
    padding: 0.75rem;
  }
  .rounded-xl {
    border-radius: 0.75rem;
  }
  .shadow-lg {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  }
  .border {
    border-width: 1px;
  }
  .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
  .mb-1 {
    margin-bottom: 0.25rem;
  }
  .opacity-80 {
    opacity: 0.8;
  }
  .font-semibold {
    font-weight: 600;
  }
  .border-6 {
    border-width: 6px;
  }
  .border-transparent {
    border-color: transparent;
  }
  .border-t-gray-900 {
    border-top-color: #111827;
  }
  .border-b-gray-900 {
    border-bottom-color: #111827;
  }
  .border-l-gray-900 {
    border-left-color: #111827;
  }
  .border-r-gray-900 {
    border-right-color: #111827;
  }
  .dark .border-t-gray-100 {
    border-top-color: #f3f4f6;
  }
  .dark .border-b-gray-100 {
    border-bottom-color: #f3f4f6;
  }
  .dark .border-l-gray-100 {
    border-left-color: #f3f4f6;
  }
  .dark .border-r-gray-100 {
    border-right-color: #f3f4f6;
  }
</style>