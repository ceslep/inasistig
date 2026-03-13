<script lang="ts">
  import { onMount } from "svelte";
  import { fade, fly } from "svelte/transition";
  import {
    X,
    BarChart3,
    Clock,
    Users,
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
  } from "lucide-svelte";
  import {
    getClientInfo,
    fetchAnalytics,
    type AnalyticsData,
    type ClientInfo,
    type SessionDetail,
  } from "../lib/analyticsService";
  import { APP_VERSION, APP_BUILD_DATE } from "../version";

  let { onClose }: { onClose: () => void } = $props();

  let activeTab = $state<"client" | "stats" | "sessions">("client");
  let expandedSession = $state<string | null>(null);
  let clientInfo = $state<ClientInfo | null>(null);
  let analytics = $state<AnalyticsData | null>(null);
  let isLoading = $state(false);
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

  const formatDuration = (seconds: number): string => {
    if (seconds < 60) return `${seconds}s`;
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins < 60) return `${mins}m ${secs}s`;
    const hours = Math.floor(mins / 60);
    return `${hours}h ${mins % 60}m`;
  };

  const eventTypeLabels: Record<string, string> = {
    session_start: "Inicio de sesión",
    session_end: "Fin de sesión",
    view_change: "Cambio de vista",
    action: "Acción",
    app_hidden: "App oculta",
    app_visible: "App visible",
    error: "Error",
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
            Información del cliente y métricas
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
      <button
        onclick={() => (activeTab = "client")}
        class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 -mb-px"
        style="
          color: rgb(var({activeTab === 'client' ? '--accent-primary' : '--text-muted'}));
          border-color: {activeTab === 'client' ? 'rgb(var(--accent-primary))' : 'transparent'};
        "
      >
        <Monitor class="w-4 h-4 inline-block mr-1 -mt-0.5" />
        Mi Dispositivo
      </button>
      <button
        onclick={() => (activeTab = "stats")}
        class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 -mb-px"
        style="
          color: rgb(var({activeTab === 'stats' ? '--accent-primary' : '--text-muted'}));
          border-color: {activeTab === 'stats' ? 'rgb(var(--accent-primary))' : 'transparent'};
        "
      >
        <Activity class="w-4 h-4 inline-block mr-1 -mt-0.5" />
        Estadísticas
      </button>
      <button
        onclick={() => (activeTab = "sessions")}
        class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 -mb-px"
        style="
          color: rgb(var({activeTab === 'sessions' ? '--accent-primary' : '--text-muted'}));
          border-color: {activeTab === 'sessions' ? 'rgb(var(--accent-primary))' : 'transparent'};
        "
      >
        <List class="w-4 h-4 inline-block mr-1 -mt-0.5" />
        Sesiones
      </button>
    </div>

    <!-- Content -->
    <div class="overflow-y-auto flex-1 p-6">
      {#if activeTab === "client" && clientInfo}
        {@const items = [
            {
              icon: Fingerprint,
              label: "ID Sesión",
              value: clientInfo.session_id.substring(0, 16) + "...",
            },
            {
              icon: Gauge,
              label: "Versión App",
              value: clientInfo.app_version,
            },
            {
              icon: Monitor,
              label: "Plataforma",
              value: clientInfo.platform,
            },
            { icon: Globe, label: "Idioma", value: clientInfo.language },
            {
              icon: Smartphone,
              label: "Pantalla",
              value: clientInfo.screen,
            },
            { icon: Eye, label: "Viewport", value: clientInfo.viewport },
            {
              icon: Wifi,
              label: "Conexión",
              value: `${clientInfo.connection_type} (${clientInfo.connection_speed})`,
            },
            {
              icon: Wifi,
              label: "Estado",
              value: clientInfo.is_online ? "En línea ✅" : "Sin conexión ❌",
            },
            { icon: Clock, label: "Zona Horaria", value: clientInfo.timezone },
            { icon: Cpu, label: "Memoria", value: clientInfo.memory },
            {
              icon: Cpu,
              label: "Núcleos CPU",
              value: clientInfo.cores.toString(),
            },
            {
              icon: Smartphone,
              label: "Táctil",
              value: clientInfo.touch ? "Sí" : "No",
            },
            {
              icon: Monitor,
              label: "PWA Instalada",
              value: clientInfo.pwa_installed ? "Sí" : "No",
            },
            { icon: Globe, label: "Referrer", value: clientInfo.referrer },
            {
              icon: Calendar,
              label: "Inicio Sesión",
              value: clientInfo.session_start,
            },
            {
              icon: Clock,
              label: "Duración Sesión",
              value: clientInfo.session_duration,
            },
          ]}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {#each items as item}
            <div
              class="flex items-center gap-3 p-3 rounded-xl transition-colors"
              style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border));"
            >
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                style="background-color: rgb(var(--accent-primary) / 0.1);"
              >
                <item.icon
                  class="w-4 h-4"
                  style="color: rgb(var(--accent-primary));"
                />
              </div>
              <div class="min-w-0">
                <p
                  class="text-[11px] font-medium uppercase tracking-wider"
                  style="color: rgb(var(--text-muted));"
                >
                  {item.label}
                </p>
                <p
                  class="text-sm font-semibold truncate"
                  style="color: rgb(var(--text-primary));"
                >
                  {item.value}
                </p>
              </div>
            </div>
          {/each}
        </div>

        <!-- User Agent completo -->
        <div
          class="mt-4 p-3 rounded-xl text-xs break-all"
          style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--card-border)); color: rgb(var(--text-muted));"
        >
          <span class="font-semibold" style="color: rgb(var(--text-secondary));"
            >User Agent:</span
          >
          {clientInfo.user_agent}
        </div>

        <!-- Versión de la App -->
        <div
          class="mt-4 p-4 rounded-xl flex items-center justify-between"
          style="background: linear-gradient(135deg, rgb(var(--accent-primary) / 0.1), rgb(var(--accent-secondary) / 0.1)); border: 1px solid rgb(var(--accent-primary) / 0.2);"
        >
          <div class="flex items-center gap-3">
            <Tag class="w-5 h-5" style="color: rgb(var(--accent-primary));" />
            <div>
              <p class="text-sm font-bold" style="color: rgb(var(--text-primary));">
                Inasistig v{APP_VERSION}
              </p>
              <p class="text-[11px]" style="color: rgb(var(--text-muted));">
                Build: {APP_BUILD_DATE}
              </p>
            </div>
          </div>
          <span
            class="text-[10px] font-bold uppercase px-2 py-1 rounded-full"
            style="background-color: rgb(16, 185, 129, 0.15); color: rgb(16, 185, 129);"
          >
            Actualizado
          </span>
        </div>
      {:else if activeTab === "stats"}
        <!-- Period selector -->
        <div class="flex gap-2 mb-4 flex-wrap">
          {#each [{ key: "today", label: "Hoy" }, { key: "week", label: "7 días" }, { key: "month", label: "30 días" }, { key: "all", label: "Todo" }] as p}
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

        {#if isLoading}
          <div class="flex items-center justify-center py-12">
            <Loader2
              class="w-8 h-8 animate-spin"
              style="color: rgb(var(--accent-primary));"
            />
          </div>
        {:else if analytics}
          <!-- Summary cards -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
            {#each [{ label: "Eventos", value: analytics.total_events.toLocaleString(), color: "99, 102, 241" }, { label: "Sesiones", value: analytics.total_sessions.toLocaleString(), color: "16, 185, 129" }, { label: "Duración Prom.", value: formatDuration(analytics.avg_session_duration), color: "245, 158, 11" }, { label: "Docentes", value: analytics.unique_docentes.toLocaleString(), color: "236, 72, 153" }] as card}
              <div
                class="p-3 rounded-xl text-center"
                style="background-color: rgba({card.color}, 0.1); border: 1px solid rgba({card.color}, 0.2);"
              >
                <p
                  class="text-xl font-bold"
                  style="color: rgba({card.color}, 1);"
                >
                  {card.value}
                </p>
                <p class="text-[10px] font-medium uppercase tracking-wider" style="color: rgb(var(--text-muted));">
                  {card.label}
                </p>
              </div>
            {/each}
          </div>

          <!-- Events by type -->
          {#if analytics.events_by_type.length > 0}
            <div class="mb-5">
              <h3
                class="text-sm font-bold mb-2"
                style="color: rgb(var(--text-primary));"
              >
                Eventos por Tipo
              </h3>
              <div class="space-y-1.5">
                {#each analytics.events_by_type as evt}
                  {@const maxCount = analytics.events_by_type[0]?.count || 1}
                  <div class="flex items-center gap-2">
                    <span
                      class="text-xs w-28 truncate shrink-0"
                      style="color: rgb(var(--text-secondary));"
                    >
                      {eventTypeLabels[evt.event_type] || evt.event_type}
                    </span>
                    <div class="flex-1 h-5 rounded-full overflow-hidden" style="background-color: rgb(var(--bg-secondary));">
                      <div
                        class="h-full rounded-full transition-all duration-500"
                        style="width: {(evt.count / maxCount) * 100}%; background-color: rgb(var(--accent-primary));"
                      ></div>
                    </div>
                    <span
                      class="text-xs font-bold w-10 text-right"
                      style="color: rgb(var(--text-primary));"
                    >
                      {evt.count}
                    </span>
                  </div>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Top views -->
          {#if analytics.top_views.length > 0}
            <div class="mb-5">
              <h3
                class="text-sm font-bold mb-2"
                style="color: rgb(var(--text-primary));"
              >
                Vistas Más Visitadas
              </h3>
              <div class="grid grid-cols-2 gap-2">
                {#each analytics.top_views as view}
                  <div
                    class="flex items-center justify-between p-2 rounded-lg"
                    style="background-color: rgb(var(--bg-secondary));"
                  >
                    <span
                      class="text-xs capitalize"
                      style="color: rgb(var(--text-secondary));"
                    >
                      {view.view_name || "dashboard"}
                    </span>
                    <span
                      class="text-xs font-bold px-2 py-0.5 rounded-full"
                      style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                    >
                      {view.count}
                    </span>
                  </div>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Activity by hour (simple text-based chart) -->
          {#if analytics.activity_by_hour.length > 0}
            <div class="mb-5">
              <h3
                class="text-sm font-bold mb-2"
                style="color: rgb(var(--text-primary));"
              >
                Actividad por Hora
              </h3>
              <div class="flex items-end gap-0.5 h-20">
                {#each Array.from({ length: 24 }, (_, i) => i) as hour}
                  {@const found = analytics.activity_by_hour.find(
                    (h) => h.hora === hour
                  )}
                  {@const count = found?.count || 0}
                  {@const maxH =
                    Math.max(
                      ...analytics.activity_by_hour.map((h) => h.count)
                    ) || 1}
                  <div class="flex-1 flex flex-col items-center gap-0.5">
                    <div
                      class="w-full rounded-t transition-all duration-300"
                      style="
                        height: {Math.max((count / maxH) * 100, 2)}%;
                        background-color: rgb(var(--accent-primary) / {count > 0 ? 0.7 : 0.1});
                      "
                      title="{hour}:00 - {count} eventos"
                    ></div>
                    {#if hour % 6 === 0}
                      <span
                        class="text-[8px]"
                        style="color: rgb(var(--text-muted));"
                      >
                        {hour}
                      </span>
                    {/if}
                  </div>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Platforms -->
          {#if analytics.platforms.length > 0}
            <div class="mb-5">
              <h3
                class="text-sm font-bold mb-2"
                style="color: rgb(var(--text-primary));"
              >
                Plataformas
              </h3>
              <div class="flex flex-wrap gap-2">
                {#each analytics.platforms as plat}
                  <span
                    class="text-xs px-3 py-1.5 rounded-full font-medium"
                    style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-secondary)); border: 1px solid rgb(var(--card-border));"
                  >
                    {plat.platform || "Desconocida"} ({plat.count})
                  </span>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Recent events -->
          {#if analytics.recent_events.length > 0}
            <div>
              <h3
                class="text-sm font-bold mb-2"
                style="color: rgb(var(--text-primary));"
              >
                Eventos Recientes
              </h3>
              <div
                class="space-y-1 max-h-40 overflow-y-auto rounded-xl p-2"
                style="background-color: rgb(var(--bg-secondary));"
              >
                {#each analytics.recent_events as evt}
                  <div
                    class="flex items-center justify-between text-xs py-1 px-2 rounded-lg"
                    style="background-color: rgb(var(--bg-primary) / 0.5);"
                  >
                    <span
                      class="font-medium"
                      style="color: rgb(var(--text-secondary));"
                    >
                      {eventTypeLabels[evt.event_type] || evt.event_type}
                    </span>
                    <span style="color: rgb(var(--text-muted));">
                      {new Date(evt.created_at).toLocaleString("es-CO", {
                        hour: "2-digit",
                        minute: "2-digit",
                        day: "2-digit",
                        month: "short",
                      })}
                    </span>
                  </div>
                {/each}
              </div>
            </div>
          {/if}
        {:else}
          <div class="text-center py-12">
            <BarChart3
              class="w-12 h-12 mx-auto mb-3"
              style="color: rgb(var(--text-muted));"
            />
            <p class="text-sm" style="color: rgb(var(--text-muted));">
              No se pudieron cargar las estadísticas del servidor.<br />
              Las estadísticas locales del dispositivo están disponibles en "Mi
              Dispositivo".
            </p>
          </div>
        {/if}
      {:else if activeTab === "sessions"}
        <!-- Period selector for sessions -->
        <div class="flex gap-2 mb-4 flex-wrap">
          {#each [{ key: "today", label: "Hoy" }, { key: "week", label: "7 días" }, { key: "month", label: "30 días" }, { key: "all", label: "Todo" }] as p}
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

        {#if isLoading}
          <div class="flex items-center justify-center py-12">
            <Loader2
              class="w-8 h-8 animate-spin"
              style="color: rgb(var(--accent-primary));"
            />
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
                <!-- Session header (clickeable) -->
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
                        {session.id_docente || "Anónimo"}
                      </span>
                      {#if session.app_version}
                        <span
                          class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                          style="background-color: rgb(var(--accent-primary) / 0.15); color: rgb(var(--accent-primary));"
                        >
                          v{session.app_version}
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
                    </p>
                  </div>

                  <div class="flex items-center gap-2 shrink-0">
                    <span
                      class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                      style="background-color: rgba(245, 158, 11, 0.15); color: rgb(245, 158, 11);"
                    >
                      {formatDuration(session.duration_seconds)}
                    </span>
                  </div>
                </button>

                <!-- Session detail (expandible) -->
                {#if isExpanded}
                  <div
                    class="px-4 pb-3 pt-1 border-t grid grid-cols-2 sm:grid-cols-3 gap-2"
                    style="border-color: rgb(var(--card-border));"
                    transition:fly={{ y: -10, duration: 200 }}
                  >
                    <div class="flex items-center gap-2">
                      <Fingerprint class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">ID Sesión</p>
                        <p class="text-xs font-medium truncate" style="color: rgb(var(--text-secondary));">
                          {session.session_id.substring(0, 16)}...
                        </p>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <Eye class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">Vistas</p>
                        <p class="text-xs font-bold" style="color: rgb(var(--text-primary));">
                          {session.page_views}
                        </p>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <Activity class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">Eventos</p>
                        <p class="text-xs font-bold" style="color: rgb(var(--text-primary));">
                          {session.events_count}
                        </p>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <Monitor class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">Plataforma</p>
                        <p class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
                          {session.platform || "Desconocida"}
                        </p>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <MapPin class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">IP</p>
                        <p class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
                          {session.ip_address || "N/A"}
                        </p>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <Clock class="w-3.5 h-3.5 shrink-0" style="color: rgb(var(--text-muted));" />
                      <div>
                        <p class="text-[10px] uppercase" style="color: rgb(var(--text-muted));">Última actividad</p>
                        <p class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
                          {new Date(session.last_activity).toLocaleString("es-CO", {
                            hour: "2-digit",
                            minute: "2-digit",
                            second: "2-digit",
                          })}
                        </p>
                      </div>
                    </div>
                  </div>
                {/if}
              </div>
            {/each}
          </div>
        {:else}
          <div class="text-center py-12">
            <List
              class="w-12 h-12 mx-auto mb-3"
              style="color: rgb(var(--text-muted));"
            />
            <p class="text-sm" style="color: rgb(var(--text-muted));">
              No hay sesiones registradas para este período.
            </p>
          </div>
        {/if}
      {/if}
    </div>
  </div>
</div>
