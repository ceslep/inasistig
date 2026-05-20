<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import { fade, fly } from "svelte/transition";
  import type { Component } from 'svelte';

  import { ArrowLeft, Wifi, WifiOff, CloudUpload, Loader2, BarChart3 } from '@lucide/svelte';
  import Swal from "sweetalert2";
  import Dashboard from "./components/Dashboard.svelte";
  import Loader from "./components/Loader.svelte";
  import { isOnline, pendingCount, pendingOperations, isSyncing, syncPendingOperations, refreshPendingOperations, discardPendingOperation, discardAllPending } from "./lib/networkStore";
  import { initAnalytics, trackViewChange } from "./lib/analyticsService";
  import { initVersionCheck } from "./version";
  import { isAuthenticated, docenteName, authUser } from "./lib/authStore";
  import LoginScreen from "./components/LoginScreen.svelte";
  import { X, RefreshCw, Trash2, Clock } from '@lucide/svelte';

  let showAnalytics = $state(false);
  let AnalyticsModal: ReturnType<typeof $state<typeof import("./components/AnalyticsModal.svelte").default | null>> = $state(null);

  let activeView = $state("dashboard");
  let isLoadingModule = $state(false);
  let showPending = $state(false);

  let externalModuleUrl = $state("");
  let externalModuleTitle = $state("");

  const externalModules: { id: string; title: string; url: string }[] = [];

  // --- History API: gesto/botón atrás vuelve al dashboard en vez de cerrar la PWA ---
  function handlePopState() {
    if (showAnalytics) {
      showAnalytics = false;
    } else if (activeView !== "dashboard") {
      activeView = "dashboard";
    }
  }

  function pushHistoryState(view: string) {
    history.pushState({ view }, "", "");
  }

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
    window.addEventListener("popstate", handlePopState);
    // Estado base para el dashboard
    history.replaceState({ view: "dashboard" }, "", "");
    initAnalytics();
    void initVersionCheck();
  });

  onDestroy(() => {
    if (typeof window !== "undefined") {
      window.removeEventListener("pwa-sync-complete", handleSyncComplete);
      window.removeEventListener("popstate", handlePopState);
    }
  });

  type ModuleComponent = Component<{ onBack: () => void }>;

  let InasistenciaForm: ModuleComponent | null = $state(null);
  let Anotador: ModuleComponent | null = $state(null);
  let Diario: ModuleComponent | null = $state(null);
  let ClassPlannerForm: ModuleComponent | null = $state(null);
  let Observador: ModuleComponent | null = $state(null);
  let Piar: ModuleComponent | null = $state(null);
  let HorasLaborables: ModuleComponent | null = $state(null);
  let ActividadesRecuperacion: ModuleComponent | null = $state(null);
  let ActaArea: ModuleComponent | null = $state(null)
  let ActaIzada: ModuleComponent | null = $state(null);
  let ActaPadres: ModuleComponent | null = $state(null);
  let ComisionEvaluacion: ModuleComponent | null = $state(null);
  let Horarios: ModuleComponent | null = $state(null);

  const handleSelect = async (view: string) => {
    if (view === "dashboard") {
      activeView = "dashboard";
      externalModuleUrl = "";
      externalModuleTitle = "";
      return;
    }

    const externalModule = externalModules.find(m => m.id === view);
    if (externalModule) {
      const params = new URLSearchParams();
      if ($authUser?.email) params.set("email", $authUser.email);
      if ($authUser?.name) params.set("teacher", $authUser.name);
      externalModuleUrl = params.toString()
        ? `${externalModule.url}?${params}`
        : externalModule.url;
      externalModuleTitle = externalModule.title;
      activeView = view;
      pushHistoryState(view);
      trackViewChange(view);
      return;
    }

    activeView = view;
    isLoadingModule = true;

    try {
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
      } else if (view === "horas_laborables" && !HorasLaborables) {
        const module = await import("./components/horas_laborables/HoursRegistration.svelte");
        HorasLaborables = module.default;
      } else if (view === "actividades_recuperacion" && !ActividadesRecuperacion) {
        const module = await import("./components/actividades_recuperacion/App.svelte");
        ActividadesRecuperacion = module.default;
      } else if (view === "acta_area" && !ActaArea) {
        const module = await import("./components/ActaArea.svelte");
        ActaArea = module.default;
      } else if (view === "acta_izada" && !ActaIzada) {
        const module = await import("./components/ActaIzada.svelte");
        ActaIzada = module.default;
      } else if (view === "acta_padres" && !ActaPadres) {
        const module = await import("./components/ActaPadres.svelte");
        ActaPadres = module.default;
      } else if (view === "comision_evaluacion" && !ComisionEvaluacion) {
        const module = await import("./components/ComisionEvaluacion.svelte");
        ComisionEvaluacion = module.default;
      } else if (view === "horarios" && !Horarios) {
        const module = await import("./components/Horarios.svelte");
        Horarios = module.default;
      }
    } finally {
      isLoadingModule = false;
    }

    pushHistoryState(view);
    trackViewChange(view);
  };

  const openAnalytics = async () => {
    if (!AnalyticsModal) {
      const module = await import("./components/AnalyticsModal.svelte");
      AnalyticsModal = module.default;
    }
    showAnalytics = true;
    pushHistoryState("analytics");
  };

  const handleBack = () => {
    activeView = "dashboard";
  };
</script>

{#if $isAuthenticated && $docenteName}
<main class="w-full min-h-screen">
  {#if activeView === "dashboard"}
    <Dashboard onSelect={handleSelect} />
  {:else if isLoadingModule}
    <div class="w-full min-h-screen flex items-center justify-center bg-[rgb(var(--bg-primary))]">
      <Loader message="Cargando módulo..." />
    </div>
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
  {:else if activeView === "horas_laborables" && HorasLaborables}
    <HorasLaborables onBack={handleBack} />
  {:else if activeView === "actividades_recuperacion" && ActividadesRecuperacion}
    <ActividadesRecuperacion onBack={handleBack} />
  {:else if activeView === "acta_area" && ActaArea}
    <ActaArea onBack={handleBack} />
  {:else if activeView === "acta_izada" && ActaIzada}
    <ActaIzada onBack={handleBack} />
  {:else if activeView === "acta_padres" && ActaPadres}
    <ActaPadres onBack={handleBack} />
  {:else if activeView === "comision_evaluacion" && ComisionEvaluacion}
    <ComisionEvaluacion onBack={handleBack} />
  {:else if activeView === "horarios" && Horarios}
    <Horarios onBack={handleBack} />
  {:else if externalModuleUrl}
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
          {externalModuleTitle}
        </h2>
        <div class="w-10"></div>
      </div>
      <iframe
        src={externalModuleUrl}
        title={externalModuleTitle}
        class="flex-1 w-full border-none"
      ></iframe>
    </div>
  {/if}
</main>

<!-- Indicador flotante global de estado de red -->
{#if !$isOnline || $pendingCount > 0 || $isSyncing}
  <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">
    {#if showPending && $pendingCount > 0}
      <div
        class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-2xl overflow-hidden w-72"
        in:fly={{ y: 20, duration: 200 }}
      >
        <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
          <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
            {$pendingCount} item{$pendingCount > 1 ? 's' : ''} pendiente{$pendingCount > 1 ? 's' : ''}
          </span>
          <button
            type="button"
            onclick={() => showPending = false}
            class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700"
          >
            <X class="w-4 h-4 text-zinc-500" />
          </button>
        </div>
        <div class="max-h-48 overflow-y-auto">
          {#each $pendingOperations as op (op.id)}
            <div class="px-4 py-2 border-b border-zinc-200 dark:border-zinc-700 flex items-start gap-3 hover:bg-zinc-100 dark:hover:bg-zinc-800">
              <span class="text-base">{
                op.operationType === 'inasistencia' ? '📋' :
                op.operationType === 'acta' ? '📝' :
                op.operationType === 'anotacion' ? '📌' :
                op.operationType === 'diario' ? '📅' : '📤'
              }</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm truncate text-zinc-900 dark:text-zinc-100">{op.summary}</p>
                <p class="text-xs text-zinc-500">{new Date(op.timestamp).toLocaleTimeString('es-CO')}</p>
              </div>
              <button
                type="button"
                onclick={() => discardPendingOperation(op.id)}
                class="p-1 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded"
              >
                <X class="w-3.5 h-3.5" />
              </button>
            </div>
          {/each}
        </div>
        <div class="px-4 py-2 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 flex justify-between">
          <button
            type="button"
            onclick={() => { discardAllPending(); showPending = false; }}
            class="text-xs text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 px-2 py-1 rounded flex items-center gap-1"
          >
            <Trash2 class="w-3 h-3" />
            Descartar
          </button>
          {#if $isOnline}
            <button
              type="button"
              onclick={() => { syncPendingOperations(); showPending = false; }}
              disabled={$isSyncing}
              class="text-xs bg-emerald-500 text-white px-3 py-1 rounded flex items-center gap-1 hover:bg-emerald-600 disabled:opacity-50"
            >
              <RefreshCw class="w-3 h-3 {$isSyncing ? 'animate-spin' : ''}" />
              Sincronizar
            </button>
          {/if}
        </div>
      </div>
    {/if}

    <button
      type="button"
      onclick={() => { showPending = !showPending; if (showPending) refreshPendingOperations(); }}
      class="flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-sm font-bold tracking-wide transition-all duration-300 hover:scale-105 active:scale-95"
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
            {$pendingCount}
          </span>
        {/if}
      {:else if $isSyncing}
        <Loader2 class="w-5 h-5 animate-spin" />
        <span>SINCRONIZANDO...</span>
      {:else if $pendingCount > 0}
        <CloudUpload class="w-5 h-5" />
        <span>{$pendingCount} PENDIENTE{$pendingCount > 1 ? "S" : ""}</span>
      {/if}
    </button>
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

{:else}
  <LoginScreen />
{/if}

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
  }
</style>
