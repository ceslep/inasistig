<script lang="ts">
  import { onMount } from 'svelte'
  import { fade, fly } from 'svelte/transition'
  import { Sun, Moon, CloudMoon, WifiOff, User } from 'lucide-svelte'

  import { GOOGLE_CLIENT_ID } from '../constants'
  import { isOnline } from '../lib/networkStore'
  import { theme, type Theme } from '../lib/themeStore'
  import { signIn, authUser, docenteName, setDocenteName, getDocenteName } from '../lib/authStore'
  import { trackEvent } from '../lib/analyticsService'
  import { Skeleton } from './ui'

  import logoEie from '../assets/eie.png'

  let gsiLoaded = $state(false)
  let buttonContainer: HTMLDivElement | undefined = $state()
  let showNameStep = $state(false)
  let nameInput = $state('')

  const toggleTheme = () => {
    theme.update((t) => {
      if (t === 'light') return 'dim'
      if (t === 'dim') return 'dark'
      return 'light'
    })
  }

  function handleCredentialResponse(response: google.accounts.id.CredentialResponse) {
    signIn(response.credential)
    const existingName = getDocenteName()
    if (!existingName) {
      const user = JSON.parse(localStorage.getItem('google_auth_user') || '{}')
      nameInput = user.name || ''
      showNameStep = true
    } else {
      trackEvent('google_login', { docente: existingName, method: 'auto' })
    }
  }

  function confirmName() {
    const trimmed = nameInput.trim()
    if (!trimmed) return
    setDocenteName(trimmed)
    trackEvent('google_login', { docente: trimmed, method: 'first_login' })
    showNameStep = false
  }

  function initializeGsi() {
    google.accounts.id.initialize({
      client_id: GOOGLE_CLIENT_ID,
      callback: handleCredentialResponse,
      auto_select: true,
      cancel_on_tap_outside: false,
    })

    if (buttonContainer) {
      google.accounts.id.renderButton(buttonContainer, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'signin_with',
        shape: 'pill',
        logo_alignment: 'left',
        width: '320',
        locale: 'es',
      })
    }

    google.accounts.id.prompt()
    gsiLoaded = true
  }

  function loadGsiScript() {
    if (typeof google !== 'undefined' && google.accounts?.id) {
      initializeGsi()
      return
    }

    const script = document.createElement('script')
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    script.onload = () => initializeGsi()
    document.head.appendChild(script)
  }

  onMount(() => {
    if ($authUser && !getDocenteName()) {
      nameInput = $authUser.name || ''
      showNameStep = true
    } else {
      loadGsiScript()
    }
  })
</script>

<div
  class="min-h-screen w-full flex flex-col items-center justify-center p-6 transition-colors duration-700 bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]"
>
  <!-- Decorative background -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
    <div
      class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"
    ></div>
    <div
      class="absolute -bottom-[20%] -right-[10%] w-[60%] h-[60%] bg-purple-500/5 blur-[120px] rounded-full animate-pulse"
      style="animation-delay: 2s"
    ></div>
  </div>

  <!-- Theme toggle -->
  <div class="fixed top-4 right-4 z-50">
    <button
      onclick={toggleTheme}
      class="p-3 rounded-2xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-all duration-300"
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

  <!-- Login card -->
  <div
    class="w-full max-w-md flex flex-col items-center gap-8"
    in:fly={{ y: 30, duration: 600 }}
  >
    <!-- Logo -->
    <div class="relative">
      <div class="absolute inset-0 bg-indigo-500/20 blur-3xl rounded-full"></div>
      <img
        src={logoEie}
        alt="Logo Instituto Guática"
        class="relative h-20 md:h-24 object-contain"
      />
    </div>

    <!-- Title -->
    <div class="text-center space-y-2">
      <h1 class="text-2xl md:text-3xl font-black tracking-tight">
        <span class="bg-gradient-to-b from-[rgb(var(--text-primary))] to-[rgb(var(--text-secondary))] bg-clip-text text-transparent">
          Ecosistema Digital
        </span>
      </h1>
      <p class="text-sm text-[rgb(var(--text-muted))] font-medium">
        Instituto Guática — Inicia sesión para continuar
      </p>
    </div>

    <!-- Login area -->
    <div
      class="w-full rounded-3xl border border-[rgb(var(--card-border))] bg-[rgb(var(--card-bg))] p-8 flex flex-col items-center gap-6"
    >
      {#if showNameStep}
        <!-- Name configuration step -->
        <div class="flex flex-col items-center gap-4 w-full" in:fade={{ duration: 300 }}>
          <div class="w-14 h-14 rounded-full bg-indigo-500/10 flex items-center justify-center">
            <User class="w-7 h-7 text-indigo-500" />
          </div>
          <div class="text-center space-y-1">
            <h2 class="text-lg font-bold text-[rgb(var(--text-primary))]">Configura tu nombre</h2>
            <p class="text-sm text-[rgb(var(--text-muted))]">
              Este nombre se usará para identificarte como docente en los módulos.
            </p>
          </div>
          <input
            type="text"
            bind:value={nameInput}
            placeholder="Nombre completo del docente"
            class="w-full px-4 py-3 rounded-xl border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] text-[rgb(var(--text-primary))] focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-center"
            onkeydown={(e) => { if (e.key === 'Enter') confirmName() }}
          />
          <button
            onclick={confirmName}
            disabled={!nameInput.trim()}
            class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-400 disabled:cursor-not-allowed text-white font-semibold transition-all duration-200"
          >
            Continuar
          </button>
        </div>
      {:else}
        {#if !$isOnline}
          <div
            class="flex items-center gap-3 text-red-500 bg-red-500/10 rounded-xl px-4 py-3 w-full"
            in:fade={{ duration: 200 }}
          >
            <WifiOff class="w-5 h-5 flex-shrink-0" />
            <span class="text-sm font-medium">
              Sin conexión a internet. Conéctate para iniciar sesión.
            </span>
          </div>
        {/if}

        {#if !gsiLoaded}
          <div class="flex flex-col items-center gap-4 w-full">
            <Skeleton variant="rect" class="w-80 h-11 rounded-full" />
            <p class="text-xs text-[rgb(var(--text-muted))]">Cargando inicio de sesión...</p>
          </div>
        {/if}

        <div
          bind:this={buttonContainer}
          class="flex justify-center"
          class:hidden={!gsiLoaded}
        ></div>

        <p class="text-xs text-[rgb(var(--text-muted))] text-center leading-relaxed">
          Usa tu cuenta institucional de Google para acceder al ecosistema digital.
        </p>
      {/if}
    </div>

    <!-- Footer -->
    <p
      class="text-[10px] text-[rgb(var(--text-muted))] tracking-widest uppercase font-bold"
      in:fade={{ duration: 800, delay: 400 }}
    >
      2026 EIE DIGITAL
    </p>
  </div>
</div>
