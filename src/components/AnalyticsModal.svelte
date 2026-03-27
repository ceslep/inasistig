<script lang="ts">
  import { onMount } from "svelte";
  import { fade, fly } from "svelte/transition";

  import X from "@lucide/svelte/icons/x";
  import BarChart3 from "@lucide/svelte/icons/bar-chart-3";
  import Monitor from "@lucide/svelte/icons/monitor";
  import Wifi from "@lucide/svelte/icons/wifi";
  import Globe from "@lucide/svelte/icons/globe";
  import Smartphone from "@lucide/svelte/icons/smartphone";
  import Activity from "@lucide/svelte/icons/activity";
  import Cpu from "@lucide/svelte/icons/cpu";
  import Loader2 from "@lucide/svelte/icons/loader-2";
  import Trash2 from "@lucide/svelte/icons/trash-2";
  import RefreshCw from "@lucide/svelte/icons/refresh-cw";
  import Users from "@lucide/svelte/icons/users";
  import Clock from "@lucide/svelte/icons/clock";
  import LayoutGrid from "@lucide/svelte/icons/layout-grid";

  import {
    SiGooglechrome,
    SiFirefoxbrowser,
    SiSafari,
    SiOpera,
    SiBrave,
    SiApple,
    SiMacos,
    SiLinux,
    SiAndroid,
  } from "@icons-pack/svelte-simple-icons";
  import Swal from "sweetalert2";

  import {
    getClientInfo,
    fetchAnalytics,
    resetAnalytics,
    type AnalyticsData,
    type ClientInfo,
  } from "../lib/analyticsService";
  import { APP_VERSION, APP_BUILD_DATE } from "../version";
  import type { Component } from "svelte";

  let { onClose }: { onClose: () => void } = $props();

  let activeTab = $state<"client" | "weekly">("client");
  let clientInfo = $state<ClientInfo | null>(null);
  let analytics = $state<AnalyticsData | null>(null);
  let isLoading = $state(false);
  let isResetting = $state(false);

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  type AnyIcon = Component<any>;

  const browserIcons: Record<string, AnyIcon> = {
    chrome: SiGooglechrome,
    firefox: SiFirefoxbrowser,
    safari: SiSafari,
    opera: SiOpera,
    brave: SiBrave,
  };

  const osIcons: Record<string, AnyIcon> = {
    macos: SiMacos,
    "mac os": SiMacos,
    ios: SiApple,
    android: SiAndroid,
    linux: SiLinux,
  };

  const getBrowserIcon = (name: string): AnyIcon => {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(browserIcons)) {
      if (lower.includes(key)) return icon;
    }
    return Globe;
  };

  const getOsIcon = (name: string): AnyIcon => {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(osIcons)) {
      if (lower.includes(key)) return icon;
    }
    return Monitor;
  };

  onMount(async () => {
    clientInfo = await getClientInfo();
    loadAnalytics();
  });

  const loadAnalytics = async () => {
    isLoading = true;
    try {
      analytics = await fetchAnalytics();
    } finally {
      isLoading = false;
    }
  };

  const handleReset = async () => {
    const result = await Swal.fire({
      title: "Reiniciar estadísticas",
      text: "Se eliminarán todos los eventos y sesiones registrados. Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Sí, reiniciar",
      cancelButtonText: "Cancelar",
      customClass: { popup: "swal-above-modal" },
    });

    if (!result.isConfirmed) return;

    isResetting = true;
    try {
      const ok = await resetAnalytics();
      if (ok) {
        analytics = null;
        await loadAnalytics();
        Swal.fire({
          icon: "success",
          title: "Estadísticas reiniciadas",
          text: "Todos los datos han sido eliminados correctamente.",
          timer: 2500,
          showConfirmButton: false,
          toast: true,
          position: "top-end",
          customClass: { popup: "swal-above-modal" },
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudieron reiniciar las estadísticas. Intenta de nuevo.",
          confirmButtonColor: "#ef4444",
          customClass: { popup: "swal-above-modal" },
        });
      }
    } finally {
      isResetting = false;
    }
  };

  const formatDuration = (seconds: number): string => {
    if (seconds < 60) return `${seconds}s`;
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins < 60) return `${mins}m ${secs}s`;
    const hours = Math.floor(mins / 60);
    return `${hours}h ${mins % 60}m`;
  };

  const deviceTypeLabel = (type: string): string => {
    const map: Record<string, string> = {
      desktop: "Escritorio",
      mobile: "Móvil",
      tablet: "Tableta",
      smarttv: "Smart TV",
      wearable: "Wearable",
      console: "Consola",
    };
    return map[type] || type;
  };

  const viewLabels: Record<string, string> = {
    dashboard: "Panel principal",
    inasistencia: "Registro diario",
    anotador: "Anotador de clase",
    diario: "Diario de campo",
    planeador: "Planeador de clases",
    observador: "Observador",
    piar: "PIAR",
    actividades_recuperacion: "Actividades de Recuperación",
    horas_laborables: "Horas laborables",
  };

  const totalModulesUsed = $derived(
    analytics?.docentes_weekly
      ? new Set(analytics.docentes_weekly.flatMap((d) => d.modules_used)).size
      : 0,
  );
</script>

<!-- Backdrop -->
<div
  class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
  transition:fade={{ duration: 200 }}
>
  <div
    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
    onclick={onClose}
    role="button"
    tabindex="-1"
    onkeydown={(e) => e.key === "Escape" && onClose()}
  ></div>

  <!-- Modal -->
  <div
    class="relative w-full max-w-2xl max-h-[85vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col"
    style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--card-border));"
    transition:fly={{ y: 50, duration: 300 }}
  >
    <!-- Header -->
    <div
      class="flex items-center justify-between px-6 py-4 border-b shrink-0"
      style="border-color: rgb(var(--card-border));"
    >
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-xl flex items-center justify-center"
          style="background-color: rgb(var(--accent-primary) / 0.15);"
        >
          <BarChart3
            size={20}
            color="rgb(var(--accent-primary))"
          />
        </div>
        <div>
          <h2
            class="text-lg font-bold"
            style="color: rgb(var(--text-primary));"
          >
            Estadísticas de Uso
          </h2>
          <p class="text-xs" style="color: rgb(var(--text-muted));">
            Inasistig v{APP_VERSION} &middot; Build {APP_BUILD_DATE}
          </p>
        </div>
      </div>
      <button
        onclick={onClose}
        class="p-2 rounded-xl transition-colors hover:bg-black/10"
        style="color: rgb(var(--text-secondary));"
      >
        <X size={20} />
      </button>
    </div>

    <!-- Tabs -->
    <div
      class="flex border-b shrink-0 px-6"
      style="border-color: rgb(var(--card-border));"
    >
      {#each [
        { key: "client" as const, icon: Monitor, label: "Mi Dispositivo" },
        { key: "weekly" as const, icon: Activity, label: "Uso Semanal" },
      ] as tab}
        <button
          onclick={() => (activeTab = tab.key)}
          class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 -mb-px"
          style="
            color: rgb(var({activeTab === tab.key ? '--accent-primary' : '--text-muted'}));
            border-color: {activeTab === tab.key ? 'rgb(var(--accent-primary))' : 'transparent'};
          "
        >
          <tab.icon size={16} class="inline-block mr-1 -mt-0.5" />
          {tab.label}
        </button>
      {/each}
    </div>

    <!-- Content -->
    <div class="overflow-y-auto flex-1 p-6">

      <!-- ==================== TAB: MI DISPOSITIVO ==================== -->
      {#if activeTab === "client" && clientInfo}
        {@const BrowserIcon = getBrowserIcon(clientInfo.browser_name)}
        {@const OsIcon = getOsIcon(clientInfo.os_name)}

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
          {#each [
            { icon: BrowserIcon, label: "Navegador", value: `${clientInfo.browser_name} ${clientInfo.browser_version}` },
            { icon: OsIcon, label: "Sistema operativo", value: `${clientInfo.os_name} ${clientInfo.os_version}` },
            { icon: Smartphone, label: "Tipo de dispositivo", value: deviceTypeLabel(clientInfo.device_type) },
            { icon: Monitor, label: "Pantalla", value: clientInfo.screen },
            { icon: Wifi, label: "Conexión", value: `${clientInfo.connection_type} (${clientInfo.connection_speed})` },
            { icon: Cpu, label: "Núcleos CPU", value: clientInfo.cores.toString() },
            { icon: Smartphone, label: "Pantalla táctil", value: clientInfo.touch ? "Sí" : "No" },
            { icon: Monitor, label: "PWA instalada", value: clientInfo.pwa_installed ? "Sí" : "No" },
          ] as item}
            <div
              class="flex items-center gap-3 p-3 rounded-xl"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                style="background-color: rgb(var(--accent-primary) / 0.1);"
              >
                <item.icon class="size-4" style="color: rgb(var(--accent-primary));" />
              </div>
              <div class="min-w-0">
                <p class="text-[11px] font-medium uppercase tracking-wider" style="color: rgb(var(--text-muted));">
                  {item.label}
                </p>
                <p class="text-sm font-semibold truncate" style="color: rgb(var(--text-primary));">
                  {item.value}
                </p>
              </div>
            </div>
          {/each}
        </div>

      <!-- ==================== TAB: USO SEMANAL ==================== -->
      {:else if activeTab === "weekly"}

        <!-- Acciones: Recargar + Reiniciar -->
        <div class="flex items-center justify-between mb-4">
          <p class="text-xs font-medium" style="color: rgb(var(--text-muted));">
            Últimos 7 días
          </p>
          <div class="flex gap-2">
            <button
              onclick={() => loadAnalytics()}
              disabled={isLoading}
              class="p-1.5 rounded-lg transition-colors hover:bg-black/5"
              style="color: rgb(var(--text-muted));"
              title="Recargar datos"
            >
              <RefreshCw size={16} class={isLoading ? 'animate-spin' : ''} />
            </button>
            <button
              onclick={handleReset}
              disabled={isResetting || isLoading}
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2);"
            >
              {#if isResetting}
                <Loader2 size={14} class="animate-spin" />
              {:else}
                <Trash2 size={14} />
              {/if}
              Reiniciar
            </button>
          </div>
        </div>

        {#if isLoading}
          <div class="flex items-center justify-center py-12">
            <Loader2
              size={32}
              color="rgb(var(--accent-primary))"
              class="animate-spin"
            />
          </div>
        {:else if analytics}

          <!-- 4 tarjetas resumen -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            {#each [
              { icon: Activity, label: "Total Sesiones", value: analytics.total_sessions.toLocaleString("es-CO"), color: "99, 102, 241" },
              { icon: Users, label: "Docentes Activos", value: analytics.unique_docentes.toLocaleString("es-CO"), color: "236, 72, 153" },
              { icon: Clock, label: "Duración Promedio", value: formatDuration(analytics.avg_session_duration), color: "245, 158, 11" },
              { icon: LayoutGrid, label: "Módulos Usados", value: totalModulesUsed.toString(), color: "16, 185, 129" },
            ] as card}
              <div
                class="p-3 rounded-xl text-center"
                style="background-color: rgba({card.color}, 0.08); border: 1px solid rgba({card.color}, 0.2);"
              >
                <card.icon
                  size={20}
                  class="mx-auto mb-1"
                  color="rgba({card.color}, 0.8)"
                />
                <p class="text-2xl font-bold" style="color: rgba({card.color}, 1);">
                  {card.value}
                </p>
                <p class="text-[10px] font-medium uppercase tracking-wider mt-1" style="color: rgb(var(--text-muted));">
                  {card.label}
                </p>
              </div>
            {/each}
          </div>

          <!-- Tabla de docentes -->
          {#if analytics.docentes_weekly.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Ranking de Docentes
            </h3>
            <div
              class="rounded-xl overflow-hidden mb-6"
              style="border: 1px solid rgb(var(--card-border));"
            >
              <table class="w-full text-xs">
                <thead>
                  <tr style="background-color: rgb(var(--bg-secondary));">
                    <th class="text-left px-3 py-2 font-semibold" style="color: rgb(var(--text-muted));">#</th>
                    <th class="text-left px-3 py-2 font-semibold" style="color: rgb(var(--text-muted));">Docente</th>
                    <th class="text-center px-3 py-2 font-semibold" style="color: rgb(var(--text-muted));">Sesiones</th>
                    <th class="text-center px-3 py-2 font-semibold" style="color: rgb(var(--text-muted));">Tiempo</th>
                    <th class="text-center px-3 py-2 font-semibold hidden sm:table-cell" style="color: rgb(var(--text-muted));">Módulos</th>
                  </tr>
                </thead>
                <tbody>
                  {#each analytics.docentes_weekly as docente, i}
                    <tr
                      class="border-t"
                      style="border-color: rgb(var(--card-border));"
                    >
                      <td class="px-3 py-2.5" style="color: rgb(var(--text-muted));">
                        <span
                          class="text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"
                          style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                        >
                          {i + 1}
                        </span>
                      </td>
                      <td class="px-3 py-2.5 font-medium truncate max-w-[150px]" style="color: rgb(var(--text-primary));">
                        {docente.id_docente}
                      </td>
                      <td class="px-3 py-2.5 text-center font-bold tabular-nums" style="color: rgb(var(--text-secondary));">
                        {docente.sessions}
                      </td>
                      <td class="px-3 py-2.5 text-center tabular-nums" style="color: rgb(var(--text-secondary));">
                        {formatDuration(docente.total_duration)}
                      </td>
                      <td class="px-3 py-2.5 text-center hidden sm:table-cell" style="color: rgb(var(--text-muted));">
                        {docente.modules_used.length}
                      </td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {:else}
            <div
              class="text-center py-6 mb-6 rounded-xl"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <Users size={32} color="rgb(var(--text-muted))" class="mx-auto mb-2" />
              <p class="text-xs" style="color: rgb(var(--text-muted));">
                No hay actividad de docentes esta semana.
              </p>
            </div>
          {/if}

          <!-- Módulos más usados -->
          {#if analytics.top_views.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Módulos Más Usados
            </h3>
            <div class="grid grid-cols-2 gap-2">
              {#each analytics.top_views as view, i}
                <div
                  class="flex items-center justify-between p-2.5 rounded-xl"
                  style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
                >
                  <div class="flex items-center gap-2 min-w-0">
                    <span
                      class="text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                      style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                    >
                      {i + 1}
                    </span>
                    <span class="text-xs font-medium truncate" style="color: rgb(var(--text-secondary));">
                      {viewLabels[view.view_name] || view.view_name || "Panel principal"}
                    </span>
                  </div>
                  <span
                    class="text-xs font-bold px-2 py-0.5 rounded-full shrink-0"
                    style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                  >
                    {view.count}
                  </span>
                </div>
              {/each}
            </div>
          {/if}

        {:else}
          <div class="text-center py-12">
            <BarChart3 size={48} color="rgb(var(--text-muted))" class="mx-auto mb-3" />
            <p class="text-sm" style="color: rgb(var(--text-muted));">
              No se pudieron cargar las estadísticas del servidor.<br />
              Revisa tu conexión e intenta de nuevo.
            </p>
          </div>
        {/if}
      {/if}
    </div>
  </div>
</div>

<style>
  :global(.swal-above-modal) {
    z-index: 99999 !important;
  }
  :global(.swal2-container:has(.swal-above-modal)) {
    z-index: 99999 !important;
  }
</style>
