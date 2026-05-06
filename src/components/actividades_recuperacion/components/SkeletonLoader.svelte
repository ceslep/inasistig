<script>
  let { type = 'cards' } = $props()
</script>

{#if type === 'cards'}
  <!-- Skeleton para la sección de filtros -->
  <div class="card p-5 sm:p-6 animate-pulse">
    <div class="flex items-center gap-2 mb-5">
      <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
      <div class="h-5 w-24 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="h-11 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
      <div class="h-11 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
      <div class="h-11 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
    </div>
  </div>

  <!-- Skeleton para stats -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    {#each Array(4) as _}
      <div class="skeleton-stat-card animate-pulse">
        <div class="flex items-center justify-between mb-3">
          <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
          <div class="h-4 w-12 rounded-md bg-slate-200 dark:bg-slate-700"></div>
        </div>
        <div class="h-7 w-16 rounded-lg bg-slate-200 dark:bg-slate-700 mb-1"></div>
        <div class="h-3 w-20 rounded bg-slate-200 dark:bg-slate-700"></div>
      </div>
    {/each}
  </div>

  <!-- Skeleton para tarjetas de estudiantes -->
  <div class="h-3 w-48 rounded bg-slate-200 dark:bg-slate-700 animate-pulse"></div>
  <div class="student-grid">
    {#each Array(6) as _, i}
      <div class="skeleton-student-card animate-pulse" style="animation-delay: {i * 100}ms">
        <div class="flex items-start gap-3 mb-3">
          <div class="w-11 h-11 rounded-xl bg-slate-200 dark:bg-slate-700 shrink-0"></div>
          <div class="flex-1">
            <div class="h-4 w-3/4 rounded bg-slate-200 dark:bg-slate-700 mb-2"></div>
            <div class="h-3 w-1/2 rounded bg-slate-200 dark:bg-slate-700"></div>
          </div>
        </div>
        <div class="flex gap-1.5 mb-3">
          <div class="h-6 w-20 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
          <div class="h-6 w-16 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
        </div>
        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between">
          <div class="h-3 w-16 rounded bg-slate-200 dark:bg-slate-700"></div>
          <div class="h-3 w-12 rounded bg-slate-200 dark:bg-slate-700"></div>
        </div>
      </div>
    {/each}
  </div>

{:else if type === 'form'}
  <div class="card p-6 sm:p-10 space-y-8 animate-pulse">
    <!-- Section header -->
    <div>
      <div class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
        <div class="h-5 w-40 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {#each Array(4) as _}
          <div>
            <div class="h-3 w-20 rounded bg-slate-200 dark:bg-slate-700 mb-2"></div>
            <div class="h-11 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
          </div>
        {/each}
      </div>
    </div>
    <hr class="border-slate-100 dark:border-slate-700" />
    <!-- Students section -->
    <div>
      <div class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
        <div class="h-5 w-32 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
      </div>
      <div class="h-32 rounded-xl bg-slate-200 dark:bg-slate-700"></div>
    </div>
  </div>
{/if}

<style>
  .skeleton-stat-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 4px 24px rgba(148, 163, 184, 0.12);
  }

  :global(.dark) .skeleton-stat-card {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(51, 65, 85, 0.6);
  }

  .skeleton-student-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-top: 4px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(148, 163, 184, 0.12);
  }

  :global(.dark) .skeleton-student-card {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(51, 65, 85, 0.6);
    border-top-color: #334155;
  }

  .student-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  @media (min-width: 540px) {
    .student-grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (min-width: 900px) {
    .student-grid { grid-template-columns: repeat(3, 1fr); }
  }
</style>
