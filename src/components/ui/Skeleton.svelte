<script lang="ts">
  let {
    variant = "text",
    lines = 1,
    class: className = "",
  }: {
    variant?: "text" | "circle" | "rect" | "card";
    lines?: number;
    class?: string;
  } = $props();
</script>

{#if variant === "text"}
  <div class="space-y-3 {className}">
    {#each Array(lines) as _, i}
      <div
        class="h-4 rounded-lg bg-[rgb(var(--bg-tertiary))] animate-pulse"
        style="width: {i === lines - 1 ? '60%' : '100%'};"
      ></div>
    {/each}
  </div>
{:else if variant === "circle"}
  <div
    class="rounded-full bg-[rgb(var(--bg-tertiary))] animate-pulse {className}"
  ></div>
{:else if variant === "rect"}
  <div
    class="rounded-xl bg-[rgb(var(--bg-tertiary))] animate-pulse {className}"
  ></div>
{:else if variant === "card"}
  <div
    class="rounded-2xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--card-bg))] p-6 space-y-4 {className}"
  >
    <div class="flex items-center gap-3">
      <div
        class="w-10 h-10 rounded-full bg-[rgb(var(--bg-tertiary))] animate-pulse"
      ></div>
      <div class="flex-1 space-y-2">
        <div
          class="h-4 w-1/3 rounded bg-[rgb(var(--bg-tertiary))] animate-pulse"
        ></div>
        <div
          class="h-3 w-1/2 rounded bg-[rgb(var(--bg-tertiary))] animate-pulse"
        ></div>
      </div>
    </div>
    <div class="space-y-2">
      <div
        class="h-3 rounded bg-[rgb(var(--bg-tertiary))] animate-pulse"
      ></div>
      <div
        class="h-3 rounded bg-[rgb(var(--bg-tertiary))] animate-pulse w-4/5"
      ></div>
    </div>
    <div
      class="h-32 rounded-xl bg-[rgb(var(--bg-tertiary))] animate-pulse"
    ></div>
  </div>
{/if}
