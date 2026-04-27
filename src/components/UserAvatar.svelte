<script lang="ts">
  import { LogOut } from '@lucide/svelte'
  import Swal from 'sweetalert2'

  import { authUser, signOut } from '../lib/authStore'

  const handleSignOut = async () => {
    const result = await Swal.fire({
      title: '¿Cerrar sesión?',
      text: 'Tendrás que iniciar sesión nuevamente para acceder.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, cerrar sesión',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444',
    })

    if (result.isConfirmed) {
      signOut()
    }
  }
</script>

{#if $authUser}
  <div class="flex items-center gap-2">
    <div
      class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))]"
    >
      <img
        src={$authUser.picture}
        alt={$authUser.name}
        class="w-7 h-7 rounded-full object-cover"
        referrerpolicy="no-referrer"
      />
      <span
        class="text-xs font-semibold text-[rgb(var(--text-primary))] hidden sm:inline max-w-[120px] truncate"
      >
        {$authUser.name}
      </span>
    </div>
    <button
      onclick={handleSignOut}
      class="p-2 rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-red-500/50 hover:bg-red-500/10 transition-all duration-300"
      title="Cerrar sesión"
    >
      <LogOut class="w-4 h-4 text-[rgb(var(--text-secondary))]" />
    </button>
  </div>
{/if}
