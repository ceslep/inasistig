<script lang="ts">
  interface Props {
    materias: string[];
    loading?: boolean;
    value?: string;
  }

  let { materias = [], loading = false, value = $bindable("") }: Props = $props();
</script>

<div class="relative">
  {#if loading}
    <div class="absolute inset-0 flex items-center justify-center">
      <div class="h-4 w-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
    </div>
  {/if}
  <select
    bind:value
    disabled={loading}
    class="w-full rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-3 py-2 text-sm text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none disabled:opacity-50"
  >
    <option value="" disabled>Seleccione un área...</option>
    {#each materias as materia}
      <option value={materia}>{materia}</option>
    {/each}
  </select>
</div>