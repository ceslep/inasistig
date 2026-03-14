<script lang="ts">
  import { onMount } from "svelte";
  import { fade, fly } from "svelte/transition";
  import {
    X,
    BarChart3,
    Clock,
    Monitor,
    Wifi,
    Globe,
    Smartphone,
    Activity,
    Eye,
    Cpu,
    Fingerprint,
    Gauge,
    Calendar,
    Loader2,
    List,
    ChevronDown,
    ChevronRight,
    MapPin,
    Tag,
    Trash2,
    RefreshCw,
  } from "lucide-svelte";
  import Swal from "sweetalert2";

  import {
    getClientInfo,
    fetchAnalytics,
    resetAnalytics,
    type AnalyticsData,
    type ClientInfo,
  } from "../lib/analyticsService";
  import { APP_VERSION, APP_BUILD_DATE } from "../version";

  let { onClose }: { onClose: () => void } = $props();

  let activeTab = $state<"client" | "stats" | "sessions">("client");
  let expandedSession = $state<string | null>(null);
  let clientInfo = $state<ClientInfo | null>(null);
  let analytics = $state<AnalyticsData | null>(null);
  let isLoading = $state(false);
  let isResetting = $state(false);
  let period = $state("all");

  onMount(() => {
    clientInfo = getClientInfo();
    loadAnalytics();
  });

  const loadAnalytics = async () => {
    isLoading = true;
    try {
      analytics = await fetchAnalytics(period);
    } finally {
      isLoading = false;
    }
  };

  const changePeriod = (p: string) => {
    period = p;
    loadAnalytics();
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

  const eventTypeLabels: Record<string, string> = {
    session_start: "Inicio de sesión",
    session_end: "Fin de sesión",
    view_change: "Cambio de vista",
    action: "Acción del usuario",
    app_hidden: "App en segundo plano",
    app_visible: "App en primer plano",
    error: "Error detectado",
  };

  const viewLabels: Record<string, string> = {
    dashboard: "Panel principal",
    inasistencia: "Registro diario",
    anotador: "Anotador de clase",
    diario: "Diario de campo",
    planeador: "Planeador de clases",
    observador: "Observador",
    piar: "PIAR",
    horas_laborables: "Horas laborables",
  };
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
            class="w-5 h-5"
            style="color: rgb(var(--accent-primary));"
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
        <X class="w-5 h-5" />
      </button>
    </div>

    <!-- Tabs -->
    <div
      class="flex border-b shrink-0 px-6"
      style="border-color: rgb(var(--card-border));"
    >
      {#each [
        { key: "client" as const, icon: Monitor, label: "Mi Dispositivo" },
        { key: "stats" as const, icon: Activity, label: "Estadísticas" },
        { key: "sessions" as const, icon: List, label: "Sesiones" },
      ] as tab}
        <button
          onclick={() => (activeTab = tab.key)}
          class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 -mb-px"
          style="
            color: rgb(var({activeTab === tab.key ? '--accent-primary' : '--text-muted'}));
            border-color: {activeTab === tab.key ? 'rgb(var(--accent-primary))' : 'transparent'};
          "
        >
          <tab.icon class="w-4 h-4 inline-block mr-1 -mt-0.5" />
          {tab.label}
        </button>
      {/each}
    </div>

    <!-- Content -->
    <div class="overflow-y-auto flex-1 p-6">

      <!-- ==================== TAB: MI DISPOSITIVO ==================== -->
      {#if activeTab === "client" && clientInfo}

        <!-- Sección: Navegador y Sistema -->
        <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
          Navegador y Sistema
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-5">
          {#each [
            { icon: Globe, label: "Navegador", value: `${clientInfo.browser_name} ${clientInfo.browser_version}` },
            { icon: Monitor, label: "Sistema operativo", value: `${clientInfo.os_name} ${clientInfo.os_version}` },
            { icon: Smartphone, label: "Tipo de dispositivo", value: deviceTypeLabel(clientInfo.device_type) },
            { icon: Cpu, label: "Arquitectura CPU", value: clientInfo.cpu_architecture },
            { icon: Globe, label: "Idioma", value: clientInfo.language },
            { icon: Clock, label: "Zona horaria", value: clientInfo.timezone },
          ] as item}
            <div
              class="flex items-center gap-3 p-3 rounded-xl"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                style="background-color: rgb(var(--accent-primary) / 0.1);"
              >
                <item.icon class="w-4 h-4" style="color: rgb(var(--accent-primary));" />
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

        <!-- Sección: Pantalla y Conectividad -->
        <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
          Pantalla y Conectividad
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-5">
          {#each [
            { icon: Smartphone, label: "Resolución pantalla", value: clientInfo.screen },
            { icon: Eye, label: "Viewport", value: clientInfo.viewport },
            { icon: Wifi, label: "Tipo de conexión", value: `${clientInfo.connection_type} (${clientInfo.connection_speed})` },
            { icon: Wifi, label: "Estado de red", value: clientInfo.is_online ? "En línea" : "Sin conexión" },
            { icon: Cpu, label: "Memoria RAM", value: clientInfo.memory },
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
                <item.icon class="w-4 h-4" style="color: rgb(var(--accent-primary));" />
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

        <!-- Sección: Sesión Actual -->
        <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
          Sesión Actual
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-5">
          {#each [
            { icon: Fingerprint, label: "ID de sesión", value: clientInfo.session_id.substring(0, 16) + "..." },
            { icon: Calendar, label: "Inicio de sesión", value: clientInfo.session_start },
            { icon: Clock, label: "Tiempo activo", value: clientInfo.session_duration },
            { icon: Globe, label: "Origen de tráfico", value: clientInfo.referrer },
          ] as item}
            <div
              class="flex items-center gap-3 p-3 rounded-xl"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                style="background-color: rgb(var(--accent-primary) / 0.1);"
              >
                <item.icon class="w-4 h-4" style="color: rgb(var(--accent-primary));" />
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

        <!-- Detalles técnicos colapsables -->
        <details class="group">
          <summary
            class="cursor-pointer text-xs font-bold uppercase tracking-wider mb-2 flex items-center gap-1"
            style="color: rgb(var(--text-muted));"
          >
            <ChevronRight class="w-3.5 h-3.5 transition-transform group-open:rotate-90" />
            Detalles técnicos
          </summary>
          <div
            class="p-3 rounded-xl text-xs break-all space-y-2"
            style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border)); color: rgb(var(--text-muted));"
          >
            <div>
              <span class="font-semibold" style="color: rgb(var(--text-secondary));">Fabricante:</span>
              {clientInfo.device_vendor}
              {#if clientInfo.device_model !== "Desconocido"}
                &middot; {clientInfo.device_model}
              {/if}
            </div>
            <div>
              <span class="font-semibold" style="color: rgb(var(--text-secondary));">User Agent:</span>
              {clientInfo.user_agent}
            </div>
          </div>
        </details>

      <!-- ==================== TAB: ESTADÍSTICAS ==================== -->
      {:else if activeTab === "stats"}

        <!-- Periodo + Reiniciar -->
        <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
          <div class="flex gap-2 flex-wrap">
            {#each [
              { key: "today", label: "Hoy" },
              { key: "week", label: "7 días" },
              { key: "month", label: "30 días" },
              { key: "all", label: "Todo" },
            ] as p}
              <button
                onclick={() => changePeriod(p.key)}
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                style="
                  background-color: rgb(var({period === p.key ? '--accent-primary' : '--bg-secondary'}));
                  color: {period === p.key ? 'white' : 'rgb(var(--text-secondary))'};
                "
              >
                {p.label}
              </button>
            {/each}
          </div>
          <div class="flex gap-2">
            <button
              onclick={() => loadAnalytics()}
              disabled={isLoading}
              class="p-1.5 rounded-lg transition-colors hover:bg-black/5"
              style="color: rgb(var(--text-muted));"
              title="Recargar datos"
            >
              <RefreshCw class="w-4 h-4 {isLoading ? 'animate-spin' : ''}" />
            </button>
            <button
              onclick={handleReset}
              disabled={isResetting || isLoading}
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2);"
            >
              {#if isResetting}
                <Loader2 class="w-3.5 h-3.5 animate-spin" />
              {:else}
                <Trash2 class="w-3.5 h-3.5" />
              {/if}
              Reiniciar
            </button>
          </div>
        </div>

        {#if isLoading}
          <div class="flex items-center justify-center py-12">
            <Loader2
              class="w-8 h-8 animate-spin"
              style="color: rgb(var(--accent-primary));"
            />
          </div>
        {:else if analytics}

          <!-- Resumen general -->
          <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
            Resumen General
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            {#each [
              { label: "Total Eventos", value: analytics.total_events.toLocaleString("es-CO"), color: "99, 102, 241" },
              { label: "Sesiones", value: analytics.total_sessions.toLocaleString("es-CO"), color: "16, 185, 129" },
              { label: "Duración Promedio", value: formatDuration(analytics.avg_session_duration), color: "245, 158, 11" },
              { label: "Docentes Activos", value: analytics.unique_docentes.toLocaleString("es-CO"), color: "236, 72, 153" },
            ] as card}
              <div
                class="p-3 rounded-xl text-center"
                style="background-color: rgba({card.color}, 0.08); border: 1px solid rgba({card.color}, 0.2);"
              >
                <p class="text-2xl font-bold" style="color: rgba({card.color}, 1);">
                  {card.value}
                </p>
                <p class="text-[10px] font-medium uppercase tracking-wider mt-1" style="color: rgb(var(--text-muted));">
                  {card.label}
                </p>
              </div>
            {/each}
          </div>

          <!-- Módulos más usados -->
          {#if analytics.top_views.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Módulos Más Usados
            </h3>
            <div class="grid grid-cols-2 gap-2 mb-6">
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

          <!-- Actividad por hora -->
          {#if analytics.activity_by_hour.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Actividad por Hora del Día
            </h3>
            <div
              class="rounded-xl p-4 mb-6"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <div class="flex items-end gap-[2px] h-24">
                {#each Array.from({ length: 24 }, (_, i) => i) as hour}
                  {@const found = analytics.activity_by_hour.find((h) => h.hora === hour)}
                  {@const count = found?.count || 0}
                  {@const maxH = Math.max(...analytics.activity_by_hour.map((h) => h.count)) || 1}
                  <div class="flex-1 flex flex-col items-center gap-0.5">
                    <div
                      class="w-full rounded-t transition-all duration-300 min-h-[2px]"
                      style="
                        height: {Math.max((count / maxH) * 100, 2)}%;
                        background-color: rgb(var(--accent-primary) / {count > 0 ? 0.7 : 0.1});
                      "
                      title="{hour}:00 — {count} evento{count !== 1 ? 's' : ''}"
                    ></div>
                    {#if hour % 4 === 0}
                      <span class="text-[8px]" style="color: rgb(var(--text-muted));">
                        {hour.toString().padStart(2, "0")}
                      </span>
                    {/if}
                  </div>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Sistemas operativos -->
          {#if analytics.platforms.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Sistemas Operativos
            </h3>
            <div class="flex flex-wrap gap-2 mb-6">
              {#each analytics.platforms as plat}
                {@const total = analytics.platforms.reduce((s, p) => s + p.count, 0)}
                {@const pct = total > 0 ? Math.round((plat.count / total) * 100) : 0}
                <span
                  class="text-xs px-3 py-1.5 rounded-full font-medium"
                  style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--card-border));"
                >
                  {plat.platform || "Desconocido"} &middot; {pct}%
                </span>
              {/each}
            </div>
          {/if}

          <!-- Tipos de evento -->
          {#if analytics.events_by_type.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Tipos de Evento
            </h3>
            <div
              class="rounded-xl p-4 mb-6 space-y-2"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              {#each analytics.events_by_type as evt}
                {@const maxCount = analytics.events_by_type[0]?.count || 1}
                <div class="flex items-center gap-3">
                  <span
                    class="text-xs w-36 truncate shrink-0 font-medium"
                    style="color: rgb(var(--text-secondary));"
                  >
                    {eventTypeLabels[evt.event_type] || evt.event_type}
                  </span>
                  <div class="flex-1 h-4 rounded-full overflow-hidden" style="background-color: rgb(var(--bg-primary));">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      style="width: {(evt.count / maxCount) * 100}%; background-color: rgb(var(--accent-primary) / 0.6);"
                    ></div>
                  </div>
                  <span
                    class="text-xs font-bold w-12 text-right tabular-nums"
                    style="color: rgb(var(--text-primary));"
                  >
                    {evt.count.toLocaleString("es-CO")}
                  </span>
                </div>
              {/each}
            </div>
          {/if}

          <!-- Eventos recientes -->
          {#if analytics.recent_events.length > 0}
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: rgb(var(--text-muted));">
              Últimos Eventos
            </h3>
            <div
              class="space-y-1 max-h-48 overflow-y-auto rounded-xl p-3"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              {#each analytics.recent_events as evt}
                <div
                  class="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg"
                  style="background-color: rgb(var(--bg-primary) / 0.5);"
                >
                  <span class="font-medium" style="color: rgb(var(--text-secondary));">
                    {eventTypeLabels[evt.event_type] || evt.event_type}
                  </span>
                  <span class="tabular-nums" style="color: rgb(var(--text-muted));">
                    {new Date(evt.created_at).toLocaleString("es-CO", {
                      day: "2-digit",
                      month: "short",
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </span>
                </div>
              {/each}
            </div>
          {/if}

        {:else}
          <div class="text-center py-12">
            <BarChart3 class="w-12 h-12 mx-auto mb-3" style="color: rgb(var(--text-muted));" />
            <p class="text-sm" style="color: rgb(var(--text-muted));">
              No se pudieron cargar las estadísticas del servidor.<br />
              Revisa tu conexión e intenta de nuevo.
            </p>
          </div>
        {/if}

      <!-- ==================== TAB: SESIONES ==================== -->
      {:else if activeTab === "sessions"}

        <!-- Periodo + Reiniciar -->
        <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
          <div class="flex gap-2 flex-wrap">
            {#each [
              { key: "today", label: "Hoy" },
              { key: "week", label: "7 días" },
              { key: "month", label: "30 días" },
              { key: "all", label: "Todo" },
            ] as p}
              <button
                onclick={() => changePeriod(p.key)}
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                style="
                  background-color: rgb(var({period === p.key ? '--accent-primary' : '--bg-secondary'}));
                  color: {period === p.key ? 'white' : 'rgb(var(--text-secondary))'};
                "
              >
                {p.label}
              </button>
            {/each}
          </div>
          <button
            onclick={handleReset}
            disabled={isResetting || isLoading}
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
            style="background-color: rgba(239, 68, 68, 0.1); color: rgb(239, 68, 68); border: 1px solid rgba(239, 68, 68, 0.2);"
          >
            {#if isResetting}
              <Loader2 class="w-3.5 h-3.5 animate-spin" />
            {:else}
              <Trash2 class="w-3.5 h-3.5" />
            {/if}
            Reiniciar
          </button>
        </div>

        {#if isLoading}
          <div class="flex items-center justify-center py-12">
            <Loader2 class="w-8 h-8 animate-spin" style="color: rgb(var(--accent-primary));" />
          </div>
        {:else if analytics && analytics.all_sessions && analytics.all_sessions.length > 0}
          <p class="text-xs mb-3" style="color: rgb(var(--text-muted));">
            {analytics.all_sessions.length} sesión{analytics.all_sessions.length !== 1 ? "es" : ""} encontrada{analytics.all_sessions.length !== 1 ? "s" : ""}
          </p>

          <div class="space-y-2">
            {#each analytics.all_sessions as session}
              {@const isExpanded = expandedSession === session.session_id}
              <div
                class="rounded-xl overflow-hidden transition-all duration-200"
                style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
              >
                <!-- Encabezado de sesión -->
                <button
                  onclick={() => expandedSession = isExpanded ? null : session.session_id}
                  class="w-full flex items-center gap-3 p-3 text-left transition-colors hover:opacity-80"
                >
                  {#if isExpanded}
                    <ChevronDown class="w-4 h-4 shrink-0" style="color: rgb(var(--accent-primary));" />
                  {:else}
                    <ChevronRight class="w-4 h-4 shrink-0" style="color: rgb(var(--text-muted));" />
                  {/if}

                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="text-sm font-semibold" style="color: rgb(var(--text-primary));">
                        {session.id_docente || "Usuario anónimo"}
                      </span>
                      {#if session.app_version}
                        <span
                          class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                          style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                        >
                          v{session.app_version}
                        </span>
                      {/if}
                      {#if session.device_type}
                        <span
                          class="text-[10px] px-1.5 py-0.5 rounded-full"
                          style="background-color: rgb(var(--bg-primary)); color: rgb(var(--text-muted)); border: 1px solid rgb(var(--card-border));"
                        >
                          {deviceTypeLabel(session.device_type)}
                        </span>
                      {/if}
                    </div>
                    <p class="text-[11px] mt-0.5" style="color: rgb(var(--text-muted));">
                      {new Date(session.started_at).toLocaleString("es-CO", {
                        weekday: "short",
                        day: "2-digit",
                        month: "short",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                      {#if session.browser_name && session.os_name}
                        &middot; {session.browser_name} en {session.os_name}
                      {:else if session.platform}
                        &middot; {session.platform}
                      {/if}
                    </p>
                  </div>

                  <span
                    class="text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0"
                    style="background-color: rgba(245, 158, 11, 0.15); color: rgb(245, 158, 11);"
                  >
                    {formatDuration(session.duration_seconds)}
                  </span>
                </button>

                <!-- Detalle expandido -->
                {#if isExpanded}
                  <div
                    class="px-4 pb-3 pt-1 border-t grid grid-cols-2 sm:grid-cols-3 gap-2"
                    style="border-color: rgb(var(--card-border));"
                    transition:fly={{ y: -10, duration: 200 }}
                  >
                    {#each [
                      { icon: Fingerprint, label: "ID Sesión", value: session.session_id.substring(0, 16) + "..." },
                      { icon: Eye, label: "Páginas vistas", value: session.page_views.toString() },
                      { icon: Activity, label: "Total eventos", value: session.events_count.toString() },
                      { icon: Monitor, label: "Navegador", value: session.browser_name && session.browser_version ? `${session.browser_name} ${session.browser_version}` : "—" },
                      { icon: Monitor, label: "Sistema operativo", value: session.os_name && session.os_version ? `${session.os_name} ${session.os_version}` : (session.platform || "—") },
                      { icon: MapPin, label: "Dirección IP", value: session.ip_address || "N/A" },
                      { icon: Clock, label: "Última actividad", value: new Date(session.last_activity).toLocaleString("es-CO", { hour: "2-digit", minute: "2-digit", second: "2-digit" }) },
                    ] as detail}
                      <div class="flex items-center gap-2">
                        <detail.icon class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                        <div>
                          <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">{detail.label}</p>
                          <p class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
                            {detail.value}
                          </p>
                        </div>
                      </div>
                    {/each}
                  </div>
                {/if}
              </div>
            {/each}
          </div>
        {:else}
          <div class="text-center py-12">
            <List class="w-12 h-12 mx-auto mb-3" style="color: rgb(var(--text-muted));" />
            <p class="text-sm" style="color: rgb(var(--text-muted));">
              No hay sesiones registradas para este período.
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
