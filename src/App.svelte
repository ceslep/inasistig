<script lang="ts">
  import { onMount } from "svelte";
  import Dashboard from "./components/Dashboard.svelte";
  import InasistenciaForm from "./components/InasistenciaForm.svelte";
  import Anotador from "./components/Anotador.svelte";
  import Diario from "./components/Diario.svelte";

  let activeView = "dashboard";

  const handleSelect = (view: string) => {
    activeView = view;
  };

  const handleBack = () => {
    activeView = "dashboard";
  };
</script>

<main class="w-full min-h-screen">
  {#if activeView === "dashboard"}
    <Dashboard onSelect={handleSelect} />
  {:else if activeView === "inasistencia"}
    <InasistenciaForm onBack={handleBack} />
  {:else if activeView === "anotador"}
    <Anotador onBack={handleBack} />
  {:else if activeView === "diario"}
    <Diario onBack={handleBack} />
  {/if}
</main>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
  }
</style>
