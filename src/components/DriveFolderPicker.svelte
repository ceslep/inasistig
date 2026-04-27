<script lang="ts">
  import { onMount } from 'svelte';
  import { Folder, ChevronRight, Home, X, Check, Loader2, FolderPlus } from '@lucide/svelte';
  import { gdriveService } from '../lib/gdriveService';
  import { GOOGLE_CLIENT_ID } from '../constants';
  import { fade, fly } from 'svelte/transition';

  interface Props {
    onSelect: (folder: { id: string; name: string } | null) => void;
    onClose: () => void;
  }

  let { onSelect, onClose }: Props = $props();

  interface FolderItem {
    id: string;
    name: string;
  }

  let currentPath = $state<FolderItem[]>([{ id: 'root', name: 'Mi Unidad' }]);
  let folders = $state<FolderItem[]>([]);
  let isLoading = $state(false);
  let selectedFolderId = $state<string | null>(null);

  const currentFolder = $derived(currentPath[currentPath.length - 1]);

  async function loadFolders(parentId: string) {
    isLoading = true;
    folders = await gdriveService.listFolders(GOOGLE_CLIENT_ID, parentId);
    isLoading = false;
  }

  function navigateTo(folder: FolderItem) {
    currentPath = [...currentPath, folder];
    loadFolders(folder.id);
  }

  function navigateBack(index: number) {
    currentPath = currentPath.slice(0, index + 1);
    loadFolders(currentPath[currentPath.length - 1].id);
  }

  function handleSelect() {
    onSelect(currentFolder.id === 'root' ? null : currentFolder);
  }

  onMount(() => {
    loadFolders('root');
  });
</script>

<div
  class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-[100] p-4"
  transition:fade={{ duration: 200 }}
>
  <div
    class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[80vh]"
    in:fly={{ y: 20, duration: 300 }}
  >
    <!-- Header -->
    <div class="p-6 border-b border-[rgb(var(--card-border))] flex items-center justify-between bg-white/5">
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500">
          <Folder class="w-6 h-6" />
        </div>
        <div>
          <h3 class="text-xl font-bold text-[rgb(var(--text-primary))]">Seleccionar Carpeta</h3>
          <p class="text-sm text-[rgb(var(--text-secondary))]">¿Dónde quieres guardar el reporte?</p>
        </div>
      </div>
      <button
        onclick={onClose}
        class="p-2 rounded-xl hover:bg-white/10 text-[rgb(var(--text-secondary))] transition-colors"
      >
        <X class="w-6 h-6" />
      </button>
    </div>

    <!-- Breadcrumbs -->
    <div class="px-6 py-3 bg-white/5 border-b border-[rgb(var(--card-border))] flex items-center gap-2 overflow-x-auto no-scrollbar">
      {#each currentPath as folder, i}
        {#if i > 0}
          <ChevronRight class="w-4 h-4 text-[rgb(var(--text-muted))] flex-shrink-0" />
        {/if}
        <button
          onclick={() => navigateBack(i)}
          class="text-sm font-medium whitespace-nowrap px-2 py-1 rounded-lg transition-colors {i === currentPath.length - 1 ? 'text-blue-500 bg-blue-500/10' : 'text-[rgb(var(--text-secondary))] hover:bg-white/5'}"
        >
          {folder.name}
        </button>
      {/each}
    </div>

    <!-- Folder List -->
    <div class="flex-1 overflow-y-auto p-4 space-y-1 min-h-[300px]">
      {#if isLoading}
        <div class="flex flex-col items-center justify-center py-20 gap-4">
          <Loader2 class="w-10 h-10 text-blue-500 animate-spin" />
          <p class="text-[rgb(var(--text-secondary))] animate-pulse">Cargando carpetas...</p>
        </div>
      {:else if folders.length === 0}
        <div class="flex flex-col items-center justify-center py-20 text-center px-8">
          <FolderPlus class="w-16 h-16 text-[rgb(var(--text-muted))] mb-4 opacity-20" />
          <p class="text-[rgb(var(--text-primary))] font-medium">No hay subcarpetas aquí</p>
          <p class="text-sm text-[rgb(var(--text-secondary))] mt-1">Puedes guardar el reporte en esta ubicación actual.</p>
        </div>
      {:else}
        {#each folders as folder}
          <button
            onclick={() => navigateTo(folder)}
            class="w-full flex items-center justify-between p-4 rounded-2xl hover:bg-white/5 border border-transparent hover:border-white/10 transition-all group"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-blue-500/5 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                <Folder class="w-5 h-5" />
              </div>
              <span class="font-medium text-[rgb(var(--text-primary))]">{folder.name}</span>
            </div>
            <ChevronRight class="w-5 h-5 text-[rgb(var(--text-muted))] opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-1" />
          </button>
        {/each}
      {/if}
    </div>

    <!-- Footer Action -->
    <div class="p-6 border-t border-[rgb(var(--card-border))] bg-white/5 flex gap-3">
      <button
        onclick={onClose}
        class="flex-1 px-6 py-3 rounded-2xl bg-white/5 border border-white/10 text-[rgb(var(--text-primary))] font-semibold hover:bg-white/10 transition-all"
      >
        Cancelar
      </button>
      <button
        onclick={handleSelect}
        class="flex-[2] px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2"
      >
        <Check class="w-5 h-5" />
        Guardar en "{currentFolder.name}"
      </button>
    </div>
  </div>
</div>

<style>
  .no-scrollbar::-webkit-scrollbar {
    display: none;
  }
  .no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
</style>
