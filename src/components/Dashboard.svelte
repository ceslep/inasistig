<script lang="ts">
  import { onMount } from 'svelte'

  import { fade, fly } from 'svelte/transition'
  import {
    Sun, Moon, CloudMoon,
    ClipboardCheck, BookOpen, Notebook, CalendarDays,
    Eye, Heart, Clock, RotateCcw, FileSignature, Flag,
    Users, ClipboardList, CalendarClock,
  } from '@lucide/svelte'

  import { theme, type Theme } from '../lib/themeStore'
  import { APP_VERSION } from '../version'
  import logoEie from '../assets/eie.png'
  import FeaturePopup from './FeaturePopup.svelte'
  import { Badge, NetworkStatus } from './ui'
  import UserAvatar from './UserAvatar.svelte'

  interface Props {
    onSelect: (view: string) => void
  }

  let { onSelect }: Props = $props()

  let mounted = $state(false)

  // --- Feature Alert for Diario ---
  let showFeatureAlertDiario = $state(true)
  const FEATURE_MESSAGE_DIARIO = '¡Nueva forma de anotar el Diario de Campo!'
  const FEATURE_DESCRIPTION_DIARIO = 'Ahora el Diario de Campo permite seleccionar y personalizar anotaciones predefinidas. ¡Explóralo!'

  function checkFeatureAlertDiarioVisibility(): boolean {
    const DEBUG_FORCE_SHOW = false

    if (DEBUG_FORCE_SHOW) {
      return true
    }

    const dismissed = localStorage.getItem('dismissedFeatureAlertDashboardDiario')

    if (!dismissed) {
      return true
    }

    const dismissedDate = new Date(dismissed)
    const now = new Date()
    const daysSinceDismissed = (now.getTime() - dismissedDate.getTime()) / (1000 * 60 * 60 * 24)

    return daysSinceDismissed > 5
  }

  const dismissFeatureAlertDiario = () => {
    localStorage.setItem('dismissedFeatureAlertDashboardDiario', new Date().toISOString())
    showFeatureAlertDiario = false
  }

  const tryFeatureNowDiario = () => {
    localStorage.setItem('dismissedFeatureAlertDashboardDiario', new Date().toISOString())
    showFeatureAlertDiario = false
    onSelect('diario')
  }

  onMount(() => {
    mounted = true
    showFeatureAlertDiario = checkFeatureAlertDiarioVisibility()
  })

  const toggleTheme = () => {
    theme.update((t: Theme) => {
      if (t === 'light') return 'dim'
      if (t === 'dim') return 'dark'
      return 'light'
    })
  }

  const colorClasses: Record<string, { border: string; iconBg: string; iconText: string }> = {
    blue: { border: 'border-l-blue-500', iconBg: 'bg-blue-500/10', iconText: 'text-blue-600' },
    purple: { border: 'border-l-purple-500', iconBg: 'bg-purple-500/10', iconText: 'text-purple-600' },
    teal: { border: 'border-l-teal-500', iconBg: 'bg-teal-500/10', iconText: 'text-teal-600' },
    cyan: { border: 'border-l-cyan-500', iconBg: 'bg-cyan-500/10', iconText: 'text-cyan-600' },
    rose: { border: 'border-l-rose-500', iconBg: 'bg-rose-500/10', iconText: 'text-rose-600' },
    amber: { border: 'border-l-amber-500', iconBg: 'bg-amber-500/10', iconText: 'text-amber-600' },
    orange: { border: 'border-l-orange-500', iconBg: 'bg-orange-500/10', iconText: 'text-orange-600' },
    emerald: { border: 'border-l-emerald-500', iconBg: 'bg-emerald-500/10', iconText: 'text-emerald-600' },
    indigo: { border: 'border-l-indigo-500', iconBg: 'bg-indigo-500/10', iconText: 'text-indigo-600' },
    violet: { border: 'border-l-violet-500', iconBg: 'bg-violet-500/10', iconText: 'text-violet-600' },
  }

  const modules = [
    {
      id: 'actividades_recuperacion',
      title: 'Actividades de Recuperación',
      subtitle: 'Refuerzo Académico',
      description: 'Gestión y seguimiento de actividades de recuperación para estudiantes.',
      icon: RotateCcw,
      color: 'emerald',
      tag: 'Académico',
    },
    {
      id: 'inasistencia',
      title: 'Registro Diario',
      subtitle: 'Gestión de Asistencia',
      description: 'Control preciso de inasistencias y novedades diarias del aula.',
      icon: ClipboardCheck,
      color: 'blue',
      tag: 'Operativo',
    },
    {
      id: 'anotador',
      title: 'Anotador de Clase',
      subtitle: 'Seguimiento Ágil',
      description: 'Registro dinámico de incidencias y avances pedagógicos por sesión.',
      icon: BookOpen,
      color: 'purple',
      tag: 'Académico',
    },
    {
      id: 'diario',
      title: 'Diario de Campo',
      subtitle: 'Reflexión Docente',
      description: 'Espacio para la reflexión profunda y documentación pedagógica.',
      icon: Notebook,
      color: 'teal',
      tag: 'Estratégico',
    },
    {
      id: 'planeador',
      title: 'Planeador de Clases',
      subtitle: 'Planificación MEN',
      description: 'Diseña tus clases alineadas a los estándares y DBA del MEN Colombia.',
      icon: CalendarDays,
      color: 'cyan',
      tag: 'Planeación',
    },
    {
      id: 'horas_laborables',
      title: 'Horas Laborables',
      subtitle: 'Gestión de Tiempo',
      description: 'Calculadora especializada para el seguimiento de jornadas y horas docentes.',
      icon: Clock,
      color: 'orange',
      tag: 'Herramienta',
    },
    {
      id: 'horarios',
      title: 'Horario General',
      subtitle: 'Consulta de Horarios',
      description: 'Consulta el horario general semanal de todos los docentes del Instituto Guática.',
      icon: CalendarClock,
      color: 'cyan',
      tag: 'Herramienta',
    },
    {
      id: 'observador',
      title: 'Observador Escolar',
      subtitle: 'Convivencia y Seguimiento',
      description: 'Registro normativo de situaciones, acciones inmediatas y seguimiento estudiantil.',
      icon: Eye,
      color: 'rose',
      tag: 'Normativo',
    },
    {
      id: 'piar',
      title: 'Registro PIAR',
      subtitle: 'Inclusión Educativa',
      description: 'Plan Individual de Ajustes Razonables para estudiantes con necesidades especiales.',
      icon: Heart,
      color: 'amber',
      tag: 'Inclusión',
    },
    {
      id: 'acta_area',
      title: 'Acta de Reunión de Area',
      subtitle: 'Reuniones de Área',
      description: 'Registro legal de actas conforme Ley 115 de 1994 y Decreto 1860 de 1994.',
      icon: FileSignature,
      color: 'indigo',
      tag: 'Normativo',
    },
    {
      id: 'acta_izada',
      title: 'Acta de Izada',
      subtitle: 'Ceremonia Cívica',
      description: 'Registro de izada de bandera semanal conforme al Decreto 1860 de 1994. Genera PDF, Excel y guarda en Drive.',
      icon: Flag,
      color: 'amber',
      tag: 'Cívico',
    },
    {
      id: 'acta_padres',
      title: 'Acta de Padres',
      subtitle: 'Reuniones de Familia',
      description: 'Registro de reuniones de padres de familia conforme al Decreto 1286 de 2005. Genera PDF, Excel y guarda en Drive.',
      icon: Users,
      color: 'teal',
      tag: 'Normativo',
    },
    {
      id: 'comision_evaluacion',
      title: 'Comision de Evaluacion',
      subtitle: 'Evaluacion y Promocion',
      description: 'Seguimiento y decisiones de la comision de evaluacion y promocion conforme al Decreto 1290 de 2009.',
      icon: ClipboardList,
      color: 'violet',
      tag: 'Normativo',
    },
  ]

  const handleModuleClick = (module: typeof modules[number]) => {
    onSelect(module.id)
  }
</script>

<div
  class="min-h-screen w-full flex flex-col items-center px-4 py-6 sm:px-6 lg:px-12 lg:py-10 transition-colors duration-500 bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]"
>
  {#if mounted}
    <!-- Header -->
    <header
      class="max-w-7xl w-full mb-8 lg:mb-10 relative"
      in:fly={{ y: -20, duration: 600, delay: 100 }}
    >
      <!-- Top bar: logo + controls -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <img
            src={logoEie}
            alt="Logo EIE"
            class="h-10 object-contain"
          />
          <div class="hidden sm:block">
            <h1 class="text-lg font-bold leading-tight text-[rgb(var(--text-primary))]">
              Ecosistema Digital
            </h1>
            <p class="text-xs text-[rgb(var(--text-muted))]">Instituto Guática</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <UserAvatar />
          <button
            onclick={toggleTheme}
            class="flex items-center justify-center w-11 h-11 rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-all duration-300"
            aria-label="Cambiar tema"
          >
            {#if $theme === 'light'}
              <Sun class="w-5 h-5 text-amber-500" />
            {:else if $theme === 'dim'}
              <CloudMoon class="w-5 h-5 text-indigo-400" />
            {:else}
              <Moon class="w-5 h-5 text-indigo-500" />
            {/if}
          </button>
        </div>
      </div>

      <!-- Title area (visible on mobile too) -->
      <div class="sm:hidden mb-2">
        <h1 class="text-xl font-bold text-[rgb(var(--text-primary))]">
          Ecosistema Digital
        </h1>
        <p class="text-xs text-[rgb(var(--text-muted))]">Instituto Guática</p>
      </div>

      <p class="text-sm text-[rgb(var(--text-secondary))] max-w-lg">
        Herramientas pedagógicas para la gestión docente diaria.
      </p>
    </header>

    <!-- Module cards grid -->
    <div
      class="max-w-7xl w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 lg:gap-6"
    >
      {#each modules as module, i (module.id)}
        {@const colors = colorClasses[module.color]}
        {@const Icon = module.icon}
        <button
          id={module.id === 'diario' ? 'diario-module-target' : undefined}
          onclick={() => handleModuleClick(module)}
          class="group relative flex flex-col text-left rounded-xl border-l-4 {colors.border} border border-[rgb(var(--card-border))] bg-[rgb(var(--card-bg))] p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:border-[rgb(var(--accent-primary))] hover:shadow-lg"
          in:fly={{ y: 30, duration: 500, delay: 150 + i * 80 }}
        >
          <!-- Top row: icon + badge -->
          <div class="flex items-start justify-between mb-3">
            <div
              class="flex items-center justify-center w-12 h-12 rounded-xl {colors.iconBg} transition-colors duration-300"
            >
              <Icon class="w-6 h-6 {colors.iconText}" />
            </div>
            <Badge>{module.tag}</Badge>
          </div>

          <!-- Title -->
          <h2
            class="text-base font-bold text-[rgb(var(--text-primary))] group-hover:text-[rgb(var(--accent-primary))] transition-colors duration-300 mb-1"
          >
            {module.title}
          </h2>

          <!-- Description -->
          <p class="text-sm text-[rgb(var(--text-muted))] line-clamp-2 leading-relaxed">
            {module.description}
          </p>
        </button>
      {/each}
    </div>

    <!-- Footer -->
    <footer
      class="mt-12 flex flex-wrap items-center justify-center gap-4 text-[rgb(var(--text-muted))] text-xs"
      in:fade={{ duration: 800, delay: 900 }}
    >
      <NetworkStatus />
      <div class="hidden sm:block w-px h-4 bg-[rgb(var(--border-primary))]"></div>
      <Badge variant="info">v{APP_VERSION}</Badge>
      <div class="hidden sm:block w-px h-4 bg-[rgb(var(--border-primary))]"></div>
      <span class="font-medium tracking-wide uppercase">2026 EIE Digital</span>
    </footer>
  {/if}
</div>

<!-- Feature Popup for Diario -->
<FeaturePopup
  featureMessage={FEATURE_MESSAGE_DIARIO}
  description={FEATURE_DESCRIPTION_DIARIO}
  onTryNow={tryFeatureNowDiario}
  onDismiss={dismissFeatureAlertDiario}
  targetSelector="#diario-module-target"
  showPopup={false}
  colorTheme="emerald"
/>
