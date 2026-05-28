<script lang="ts">
  let { value = $bindable("") } = $props();

  const intensities = ["1", "2", "3", "4", "5", "6"];
</script>

<select
  bind:value
  class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-3 py-2 text-sm text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none"
>
  <option value="" disabled>Int...</option>
  {#each intensities as intensity}
    <option value={intensity}>{intensity}</option>
  {/each}
</select>