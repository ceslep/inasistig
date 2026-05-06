<script lang="ts">
  import Swal from 'sweetalert2'
  import { ArrowLeft, Sun, Moon, Info, FileText, List } from '@lucide/svelte'
  import { documentTitle, institutionHeader } from './lib/data.js'
  import Toast from './components/Toast.svelte'
  import eieLogo from '../../assets/eie.png'

  // Props para integración con inasistig
  let { onBack } = $props()

  let currentView = $state('form')
  let FormComponent = $state<any>(null)
  let DataList = $state<any>(null)
  let viewKey = $state(0)
  let darkMode = $state(false)
  let toastRef = $state<any>(null)

  // Persist dark mode preference
  if (typeof window !== 'undefined') {
    darkMode = localStorage.getItem('actirec-dark') === 'true' ||
      (!localStorage.getItem('actirec-dark') && window.matchMedia('(prefers-color-scheme: dark)').matches)
  }

  $effect(() => {
    if (typeof document !== 'undefined') {
      document.documentElement.classList.toggle('dark', darkMode)
      document.body.classList.toggle('dark', darkMode)
      localStorage.setItem('actirec-dark', String(darkMode))
    }
  })

  function toggleDark() {
    darkMode = !darkMode
  }

  async function loadView(view: string) {
    currentView = view
    viewKey++
    if (view === 'form' && !FormComponent) {
      FormComponent = (await import('./components/FormComponent.svelte')).default
    } else if (view === 'list' && !DataList) {
      DataList = (await import('./components/DataList.svelte')).default
    }
  }

  loadView('form')

  function showAbout() {
    Swal.fire({
      title: 'Acerca de ActiRec',
      html: `
        <div style="text-align:left; font-size:14px; line-height:1.7;">
          <p style="margin-bottom:8px;">Aplicacion para la gestion de <strong>Planes de Mejoramiento Academico</strong>.</p>
          <hr style="margin:12px 0; border-color:#e2e8f0;">
          <p><strong>Desarrollado por:</strong></p>
          <p>Cesar Leandro Patino Velez</p>
          <p style="color:#64748b; font-size:13px;">Docente - Institucion Educativa Oficial Instituto Guatica</p>
        </div>
      `,
      icon: 'info',
      confirmButtonText: 'Cerrar',
      confirmButtonColor: '#2563eb',
      background: darkMode ? '#1e293b' : '#fff',
      color: darkMode ? '#e2e8f0' : '#1e293b',
      customClass: { popup: 'rounded-2xl' }
    })
  }
</script>

<div class="min-h-screen flex flex-col">
  <!-- Header -->
  <header class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-violet-600 via-violet-700 to-violet-900"></div>
    <div class="absolute inset-0 opacity-10"
      style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm p-1 shadow-lg ring-1 ring-white/20 shrink-0">
        <img src={eieLogo} alt="Escudo institucional" class="w-full h-full object-contain drop-shadow-lg" />
      </div>
      <div class="flex-1">
        <p class="text-violet-200 text-xs font-medium tracking-wide uppercase whitespace-pre-line leading-tight">{institutionHeader}</p>
        <h1 class="text-white text-sm sm:text-base font-bold leading-snug">{documentTitle}</h1>
      </div>
      <div class="flex items-center gap-1">
        <!-- Dark mode toggle -->
        <button onclick={showAbout}
          class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 text-white/80 hover:text-white"
          title="Acerca de">
          <Info class="text-lg" />
        </button>
        <button onclick={onBack}
          class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-200 text-white/80 hover:text-white"
          title="Volver">
          <ArrowLeft class="text-lg" />
        </button>
      </div>
    </div>
  </header>

  <!-- Tab switcher -->
  <div class="max-w-4xl mx-auto w-full px-4 sm:px-6 mt-4">
    <div class="card inline-flex p-1.5 gap-1">
      <button
        onclick={() => loadView('form')}
        class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 {currentView === 'form' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'}">
        <FileText class="text-base" />
        Formulario
      </button>
      <button
        onclick={() => loadView('list')}
        class="px-6 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 {currentView === 'list' ? 'bg-violet-600 text-white shadow-md' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'}">
        <List class="text-base" />
        Registros
      </button>
    </div>
  </div>

  <!-- Main content with view transition -->
  <main class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 py-8">
    {#key viewKey}
      <div class="view-enter">
        {#if currentView === 'form' && FormComponent}
          <FormComponent toast={toastRef} />
        {:else if currentView === 'list' && DataList}
          <DataList />
        {/if}
      </div>
    {/key}
  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-200/60 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/40 backdrop-blur-sm transition-colors duration-300">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 text-center text-xs text-slate-400 dark:text-slate-500">
      ActiRec &copy; {new Date().getFullYear()} &mdash; I.E. Oficial Instituto Guatica
    </div>
  </footer>
</div>

<!-- Global Toast component -->
<Toast bind:this={toastRef} />
