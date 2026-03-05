<script lang="ts">
  import type { Snippet } from "svelte";

  let {
    text = "",
    position = "top",
    children,
  }: {
    text?: string;
    position?: "top" | "bottom" | "left" | "right";
    children: Snippet;
  } = $props();

  const positionClasses: Record<string, string> = {
    top: "bottom-full left-1/2 -translate-x-1/2 mb-2",
    bottom: "top-full left-1/2 -translate-x-1/2 mt-2",
    left: "right-full top-1/2 -translate-y-1/2 mr-2",
    right: "left-full top-1/2 -translate-y-1/2 ml-2",
  };
</script>

<div class="relative group inline-flex">
  {@render children()}
  {#if text}
    <div
      class="absolute {positionClasses[position]} px-2.5 py-1.5 text-xs font-medium text-white bg-slate-900 dark:bg-slate-700 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
    >
      {text}
    </div>
  {/if}
</div>
