<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import { fade, fly } from "svelte/transition";
  import { ArrowLeft, Wifi, WifiOff, CloudUpload, Loader2, BarChart3 } from "lucide-svelte";
  import Swal from "sweetalert2";
  import Dashboard from "./components/Dashboard.svelte";
  import { isOnline, pendingCount, isSyncing } from "./lib/networkStore";
  import { initAnalytics, trackViewChange } from "./lib/analyticsService";
  import { initVersionCheck } from "./version";

  let showAnalytics = $state(false);
  let AnalyticsModal: ReturnType<typeof $state<typeof import("./components/AnalyticsModal.svelte").default | null>> = $state(null);

  let activeView = $state("dashboard");

  function handleSyncComplete(e: Event) {
    const { synced } = (e as CustomEvent).detail;
    Swal.fire({
      icon: "success",
      title: "¡Datos sincronizados!",
      text: `${synced} operaci${synced > 1 ? "ones" : "ón"} sincronizada${synced > 1 ? "s" : ""} exitosamente.`,
      timer: 4000,
      timerProgressBar: true,
      showConfirmButton: false,
      position: "top-end",
      toast: true,
    });
  }

  onMount(() => {
    window.addEventListener("pwa-sync-complete", handleSyncComplete);
    initAnalytics();
    void initVersionCheck();
  });

  onDestroy(() => {
    if (typeof window !== "undefined") {
      window.removeEventListener("pwa-sync-complete", handleSyncComplete);
    }
  });

  let InasistenciaForm: any = $state(null);
  let Anotador: any = $state(null);
  let Diario: any = $state(null);
  let ClassPlannerForm: any = $state(null);
  let Observador: any = $state(null);
  let Piar: any = $state(null);

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
    } else if (view === "observador" && !Observador) {
      const module = await import("./components/Observador.svelte");
      Observador = module.default;
    } else if (view === "piar" && !Piar) {
      const module = await import("./components/Piar.svelte");
      Piar = module.default;
    }

    activeView = view;
    trackViewChange(view);
  };

  const openAnalytics = async () => {
    if (!AnalyticsModal) {
      const module = await import("./components/AnalyticsModal.svelte");
      AnalyticsModal = module.default;
    }
    showAnalytics = true;
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
  {:else if activeView === "observador" && Observador}
    <Observador onBack={handleBack} />
  {:else if activeView === "piar" && Piar}
    <Piar onBack={handleBack} />
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
          <ArrowLeft class="w-5 h-5" />
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

<!-- Indicador flotante global de estado de red -->
{#if !$isOnline || $pendingCount > 0 || $isSyncing}
  <div
    class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-sm font-bold tracking-wide transition-all duration-300"
    style="
      background-color: {$isOnline ? ($isSyncing ? 'rgb(59, 130, 246)' : 'rgb(245, 158, 11)') : 'rgb(239, 68, 68)'};
      color: white;
    "
    in:fly={{ y: 40, duration: 300 }}
    out:fade={{ duration: 200 }}
  >
    {#if !$isOnline}
      <WifiOff class="w-5 h-5" />
      <span>SIN CONEXIÓN</span>
      {#if $pendingCount > 0}
        <span class="px-2 py-0.5 rounded-full bg-white/20 text-[11px]">
          {$pendingCount} pendiente{$pendingCount > 1 ? "s" : ""}
        </span>
      {/if}
    {:else if $isSyncing}
      <Loader2 class="w-5 h-5 animate-spin" />
      <span>SINCRONIZANDO...</span>
    {:else if $pendingCount > 0}
      <CloudUpload class="w-5 h-5" />
      <span>{$pendingCount} PENDIENTE{$pendingCount > 1 ? "S" : ""}</span>
    {/if}
  </div>
{/if}

<!-- Botón flotante de estadísticas -->
<button
  onclick={openAnalytics}
  class="fixed bottom-6 left-6 z-40 w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 active:scale-95"
  style="background-color: rgb(var(--accent-primary)); color: white;"
  title="Estadísticas de uso"
>
  <BarChart3 class="w-5 h-5" />
</button>

<!-- Modal de estadísticas -->
{#if showAnalytics && AnalyticsModal}
  <AnalyticsModal onClose={() => (showAnalytics = false)} />
{/if}

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
  }
</style>
