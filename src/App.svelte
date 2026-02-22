<script lang="ts">
  import { onMount } from "svelte";
  import { fade, fly } from "svelte/transition";
  import Dashboard from "./components/Dashboard.svelte";

  let activeView = $state("dashboard");

  let InasistenciaForm: any = $state(null);
  let Anotador: any = $state(null);
  let Diario: any = $state(null);
  let ClassPlannerForm: any = $state(null);

  const handleSelect = async (view: string) => {
    if (view === "dashboard") {
      activeView = "dashboard";
      return;
    }

    if (view === "inasistencia" && !InasistenciaForm) {
      const module = await import("./components/InasistenciaForm.svelte");
      InasistenciaForm = module.default;
    } else if (view === "anotador" && !Anotador) {
      const module = await import("./components/Anotador.svelte");
      Anotador = module.default;
    } else if (view === "diario" && !Diario) {
      const module = await import("./components/Diario.svelte");
      Diario = module.default;
    } else if (view === "planeador" && !ClassPlannerForm) {
      const module = await import("./components/ClassPlannerForm.svelte");
      ClassPlannerForm = module.default;
    }

    activeView = view;
  };

  const handleBack = () => {
    activeView = "dashboard";
  };
</script>

<main class="w-full min-h-screen">
  {#if activeView === "dashboard"}
    <Dashboard onSelect={handleSelect} />
  {:else if activeView === "inasistencia" && InasistenciaForm}
    <InasistenciaForm onBack={handleBack} />
{:else if activeView === "anotador" && Anotador}
    <Anotador onBack={handleBack} />
  {:else if activeView === "diario" && Diario}
    <Diario onBack={handleBack} />
  {:else if activeView === "planeador" && ClassPlannerForm}
    <div in:fade={{ duration: 300 }}>
      <ClassPlannerForm onBack={handleBack} />
    </div>
  {:else if activeView === "horas_laborables"}
    <div class="w-full h-screen flex flex-col bg-[rgb(var(--bg-primary))]">
      <div
        class="h-16 flex items-center justify-between px-6 border-b transition-colors duration-200"
        style="background-color: rgb(var(--card-bg)); border-color: rgb(var(--card-border));"
      >
        <button
          onclick={handleBack}
          class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5 font-medium"
          style="color: rgb(var(--text-primary));"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Volver al Dashboard
        </button>
        <h2 class="text-lg font-bold" style="color: rgb(var(--text-primary));">
          Horas Laborables
        </h2>
        <div class="w-10"></div>
      </div>
      <iframe
        src="https://ceslep.github.io/horas_laborables"
        title="Horas Laborables"
        class="flex-1 w-full border-none"
      ></iframe>
    </div>
  {/if}
</main>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
  }
</style>
