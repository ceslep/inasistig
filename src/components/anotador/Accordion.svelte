<script lang="ts">
  import type { Snippet } from 'svelte';
  import { ChevronDown } from '@lucide/svelte';
  import { slide } from 'svelte/transition';

  interface Props {
    title: string;
    isExpanded: boolean;
    onToggle: () => void;
    color: string;
    count?: number;
    showCount?: boolean;
    highlighted?: boolean;
    children: Snippet;
  }

  let {
    title,
    isExpanded = $bindable(),
    onToggle,
    color,
    count,
    showCount = false,
    highlighted = false,
    children,
  }: Props = $props();

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      onToggle();
    }
  };
</script>

<div class="space-y-4">
  <button
    type="button"
    onclick={onToggle}
    onkeydown={handleKeydown}
    class="flex items-center gap-3 w-full text-left cursor-pointer group focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 rounded-lg"
    aria-expanded={isExpanded}
  >
    <div class="flex items-center gap-3 flex-1">
      <h3
        class="text-xs font-black uppercase tracking-widest px-3 py-1 rounded-full text-white flex-shrink-0 transition-all duration-300 group-hover:shadow-md"
        style="
          background-color: {color};
          {highlighted ? `box-shadow: 0 0 25px ${color}60, 0 0 50px ${color}30; border: 2px solid ${color};` : ''}
        "
      >
        {title}
        {#if highlighted}
          <span class="ml-2 inline-block animate-bounce">✨</span>
        {/if}
      </h3>
      {#if showCount && count !== undefined}
        <span class="text-xs font-medium" style="color: {color};">
          ({count} resultado{count !== 1 ? "s" : ""})
        </span>
      {/if}
    </div>

    {#if !showCount}
      <div
        class="h-px flex-1 transition-all duration-300 group-hover:opacity-50"
        style="background-color: {color}; opacity: 0.2;"
      ></div>
    {/if}

    <ChevronDown
      class="w-5 h-5 text-gray-500 transform transition-transform duration-200 flex-shrink-0"
      style="transform: {isExpanded ? 'rotate(180deg)' : ''}"
    />
  </button>

  {#if isExpanded}
    <div transition:slide={{ duration: 300, easing: (t) => t * (2 - t) }}>
      {@render children()}
    </div>
  {/if}
</div>

<style>
  @keyframes glow-pulse {
    0%, 100% {
      box-shadow: 0 0 10px currentColor, 0 0 20px currentColor;
      transform: scale(1);
    }
    50% {
      box-shadow: 0 0 20px currentColor, 0 0 40px currentColor;
      transform: scale(1.05);
    }
  }
</style>