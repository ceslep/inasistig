<script lang="ts">
  import { onMount } from 'svelte';
  import { Folder, ChevronRight, X, Check, Loader2, FolderPlus, AlertTriangle, RefreshCw, Star, Users, HardDrive, Pin } from '@lucide/svelte';
  import { gdriveService, type FolderItem } from '../lib/gdriveService';
  import { GOOGLE_CLIENT_ID } from '../constants';
  import { fade, fly } from 'svelte/transition';

  interface Props {
    onSelect: (folder: { id: string; name: string } | null) => void;
    onClose: () => void;
  }

  let { onSelect, onClose }: Props = $props();

  type RootSection = 'my-drive' | 'starred' | 'shared';

  const FAVORITE_FOLDER_KEY = 'gdrive_favorite_folder';

  interface FavoriteFolder {
    id: string;
    name: string;
    section: RootSection;
    path?: string[];
  }

   let activeSection = $state<RootSection>('my-drive');
   let currentPath = $state<FolderItem[]>([{ id: 'root', name: 'Mi Unidad' }]);
   let folders = $state<FolderItem[]>([]);
   let isLoading = $state(false);
   let errorMessage = $state<string | null>(null);
   let activeDriveId = $state<string | undefined>(undefined);
   let isCreatingFolder = $state(false);
   let newFolderName = $state('');
   let favoriteFolder = $state<FavoriteFolder | null>(null);

  const currentFolder = $derived(currentPath[currentPath.length - 1]);
  const isAtRoot = $derived(currentPath.length === 1);

  async function loadFolders(parentId: string, driveId?: string) {
    isLoading = true;
    errorMessage = null;

    const result = await gdriveService.listFolders(GOOGLE_CLIENT_ID, parentId, driveId);

    folders = result.folders;
    if (result.error) {
      errorMessage = result.error;
    }

    isLoading = false;
  }

  async function loadStarred() {
    isLoading = true;
    errorMessage = null;

    const result = await gdriveService.listStarredFolders(GOOGLE_CLIENT_ID);

    folders = result.folders;
    if (result.error) {
      errorMessage = result.error;
    }

    isLoading = false;
  }

  async function loadSharedDrives() {
    isLoading = true;
    errorMessage = null;

    const result = await gdriveService.listSharedDrives(GOOGLE_CLIENT_ID);

    folders = result.folders;
    if (result.error) {
      errorMessage = result.error;
    }

    isLoading = false;
  }

  function switchSection(section: RootSection) {
    activeSection = section;
    activeDriveId = undefined;

    if (section === 'my-drive') {
      currentPath = [{ id: 'root', name: 'Mi Unidad' }];
      loadFolders('root');
    } else if (section === 'starred') {
      currentPath = [{ id: 'starred', name: 'Destacados' }];
      loadStarred();
    } else {
      currentPath = [{ id: 'shared', name: 'Compartidas' }];
      loadSharedDrives();
    }
  }

  function navigateTo(folder: FolderItem) {
    if (activeSection === 'shared' && isAtRoot) {
      activeDriveId = folder.id;
      currentPath = [{ id: 'shared', name: 'Compartidas' }, folder];
      loadFolders(folder.id, folder.id);
    } else if (activeSection === 'starred' && isAtRoot) {
      activeDriveId = undefined;
      currentPath = [{ id: 'starred', name: 'Destacados' }, folder];
      loadFolders(folder.id);
    } else {
      currentPath = [...currentPath, folder];
      loadFolders(folder.id, activeDriveId);
    }
  }

   function navigateBack(index: number) {
     currentPath = currentPath.slice(0, index + 1);
     const target = currentPath[currentPath.length - 1];

     if (index === 0) {
       if (activeSection === 'starred') {
         loadStarred();
       } else if (activeSection === 'shared') {
         activeDriveId = undefined;
         loadSharedDrives();
       } else {
         loadFolders('root');
       }
     } else {
       loadFolders(target.id, activeDriveId);
     }
   }

   async function createNewFolder() {
     if (!newFolderName.trim()) return;
     
     isCreatingFolder = true;
     errorMessage = null;
     
     try {
       const parentId = currentFolder.id;
       const driveId = activeSection === 'shared' && isAtRoot ? activeDriveId : undefined;
       
       const result = await gdriveService.createFolder(
         GOOGLE_CLIENT_ID, 
         newFolderName.trim(), 
         parentId, 
         driveId
       );
       
       if (result.error) {
         errorMessage = result.error;
       } else {
         // Navigate to the newly created folder
         if (result.folder) {
           navigateTo(result.folder);
         }
         // Reset form
         newFolderName = '';
       }
     } catch (error) {
       errorMessage = error instanceof Error ? error.message : 'Error al crear la carpeta';
     } finally {
       isCreatingFolder = false;
     }
   }

  function retryLoad() {
    const target = currentFolder;
    if (isAtRoot) {
      switchSection(activeSection);
    } else {
      loadFolders(target.id, activeDriveId);
    }
  }

  function handleSelect() {
    if (currentFolder.id === 'root' || currentFolder.id === 'starred' || currentFolder.id === 'shared') {
      onSelect(null);
    } else {
      onSelect(currentFolder);
    }
  }

  const sections: { id: RootSection; label: string; icon: typeof HardDrive }[] = [
    { id: 'my-drive', label: 'Mi Unidad', icon: HardDrive },
    { id: 'starred', label: 'Destacados', icon: Star },
    { id: 'shared', label: 'Compartidas', icon: Users },
  ];

  function loadFavoriteFolder() {
    const stored = localStorage.getItem(FAVORITE_FOLDER_KEY);
    if (stored) {
      try {
        favoriteFolder = JSON.parse(stored);
      } catch {
        favoriteFolder = null;
      }
    }
  }

  function saveFavoriteFolder() {
    if (currentFolder.id === 'root' || currentFolder.id === 'starred' || currentFolder.id === 'shared') {
      return;
    }
    const fav: FavoriteFolder = {
      id: currentFolder.id,
      name: currentFolder.name,
      section: activeSection,
      path: currentPath.map(f => f.name)
    };
    localStorage.setItem(FAVORITE_FOLDER_KEY, JSON.stringify(fav));
    favoriteFolder = fav;
  }

  function navigateToFavorite() {
    if (!favoriteFolder) return;
    activeSection = favoriteFolder.section;
    activeDriveId = undefined;
    
    if (favoriteFolder.section === 'my-drive') {
      currentPath = [{ id: 'root', name: 'Mi Unidad' }];
      loadFolders('root');
    } else if (favoriteFolder.section === 'starred') {
      currentPath = [{ id: 'starred', name: 'Destacados' }];
      loadStarred();
    } else {
      currentPath = [{ id: 'shared', name: 'Compartidas' }];
      loadSharedDrives();
    }
  }

  const isCurrentFolderFavorite = $derived(favoriteFolder?.id === currentFolder.id);

  onMount(() => {
    loadFavoriteFolder();
    loadFolders('root');
  });
</script>

<div
  class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-[100] p-4"
  transition:fade={{ duration: 200 }}
  onclick={(e) => e.stopPropagation()}
  onkeydown={(e) => e.stopPropagation()}
>
  <div
    class="bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col max-h-[85vh]"
    in:fly={{ y: 20, duration: 300 }}
  >
    <!-- Header -->
    <div class="p-4 border-b border-[rgb(var(--card-border))] flex items-center justify-between bg-white/5 gap-2">
      <div class="flex items-center gap-3 min-w-0">
        <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500 flex-shrink-0">
          <Folder class="w-6 h-6" />
        </div>
        <div class="min-w-0">
          <h3 class="text-xl font-bold text-[rgb(var(--text-primary))]">Seleccionar Carpeta</h3>
          <p class="text-sm text-[rgb(var(--text-secondary))] truncate">¿Dónde quieres guardar el reporte?</p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <button
          onclick={handleSelect}
          disabled={currentFolder.id === 'root' || currentFolder.id === 'starred' || currentFolder.id === 'shared'}
          class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors text-sm whitespace-nowrap disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Guardar aquí
        </button>
        <button
          onclick={onClose}
          class="p-2 rounded-xl hover:bg-white/10 text-[rgb(var(--text-secondary))] transition-colors"
        >
          <X class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- Section Tabs -->
    <div class="px-4 py-2 bg-white/5 border-b border-[rgb(var(--card-border))] flex gap-1">
      {#each sections as section}
        <button
          onclick={() => switchSection(section.id)}
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {activeSection === section.id ? 'bg-blue-500/15 text-blue-500' : 'text-[rgb(var(--text-secondary))] hover:bg-white/5'}"
        >
          <section.icon class="w-3.5 h-3.5" />
          {section.label}
        </button>
      {/each}
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
    <div class="flex-1 overflow-y-auto p-4 space-y-1 min-h-[150px]">
      {#if isLoading}
        <div class="flex flex-col items-center justify-center py-20 gap-4">
          <Loader2 class="w-10 h-10 text-blue-500 animate-spin" />
          <p class="text-[rgb(var(--text-secondary))] animate-pulse">Conectando con Google Drive...</p>
        </div>
      {:else if errorMessage}
        <div class="flex flex-col items-center justify-center py-16 text-center px-8 gap-4">
          <div class="p-3 rounded-full bg-red-500/10">
            <AlertTriangle class="w-10 h-10 text-red-500" />
          </div>
          <div>
            <p class="text-[rgb(var(--text-primary))] font-semibold">Error al cargar carpetas</p>
            <p class="text-sm text-[rgb(var(--text-secondary))] mt-1 max-w-xs">{errorMessage}</p>
          </div>
          <button
            onclick={retryLoad}
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500/10 text-blue-500 font-medium hover:bg-blue-500/20 transition-colors"
          >
            <RefreshCw class="w-4 h-4" />
            Reintentar
          </button>
        </div>
       {:else if folders.length === 0}
         <div class="flex flex-col items-center justify-center py-20 text-center px-8">
           <FolderPlus class="w-16 h-16 text-[rgb(var(--text-muted))] mb-4 opacity-20" />
           {#if activeSection === 'starred' && isAtRoot}
             <p class="text-[rgb(var(--text-primary))] font-medium">Sin carpetas destacadas</p>
             <p class="text-sm text-[rgb(var(--text-secondary))] mt-1">Marca carpetas con estrella en Google Drive para verlas aquí.</p>
           {:else if activeSection === 'shared' && isAtRoot}
             <p class="text-[rgb(var(--text-primary))] font-medium">Sin unidades compartidas</p>
             <p class="text-sm text-[rgb(var(--text-secondary))] mt-1">No tienes acceso a unidades compartidas.</p>
           {:else}
             <p class="text-[rgb(var(--text-primary))] font-medium">No hay subcarpetas aquí</p>
             <p class="text-sm text-[rgb(var(--text-secondary))] mt-1">Puedes guardar el reporte en esta ubicación actual.</p>
           {/if}
         </div>
       {:else}
         <!-- New Folder Form -->
         {#if !isCreatingFolder}
           <div class="flex items-center gap-2 px-2 mb-4">
             <input
               type="text"
               bind:value={newFolderName}
               placeholder="Nombre de nueva carpeta..."
               class="flex-1 px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
               style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
               onkeydown={(e) => { if (e.key === 'Enter') { e.stopPropagation(); createNewFolder(); } }}
             />
              <button
                onclick={(e) => { e.stopPropagation(); createNewFolder(); }}
                disabled={!newFolderName.trim()}
                class="px-3 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                style="background-color: rgb(99, 102, 241); color: white"
              >
                <FolderPlus class="w-4 h-4" />
                Crear carpeta
              </button>
           </div>
         {/if}
         {#if isCreatingFolder}
           <div class="flex items-center justify-center py-2">
             <Loader2 class="w-5 h-5 text-blue-500 animate-spin" />
             <span class="ml-2 text-[rgb(var(--text-primary))]">Creando carpeta...</span>
           </div>
         {/if}
         {#each folders as folder}
           <button
             onclick={() => navigateTo(folder)}
             class="w-full flex items-center justify-between p-4 rounded-2xl hover:bg-white/5 border border-transparent hover:border-white/10 transition-all group"
           >
             <div class="flex items-center gap-4">
               <div class="w-10 h-10 rounded-xl {activeSection === 'shared' && isAtRoot ? 'bg-purple-500/5 text-purple-400' : activeSection === 'starred' && isAtRoot ? 'bg-yellow-500/5 text-yellow-400' : 'bg-blue-500/5 text-blue-400'} flex items-center justify-center group-hover:scale-110 transition-transform">
                 {#if activeSection === 'shared' && isAtRoot}
                   <Users class="w-5 h-5" />
                 {:else if activeSection === 'starred' && isAtRoot}
                   <Star class="w-5 h-5" />
                 {:else}
                   <Folder class="w-5 h-5" />
                 {/if}
               </div>
               <span class="font-medium text-[rgb(var(--text-primary))]">{folder.name}</span>
             </div>
             <ChevronRight class="w-5 h-5 text-[rgb(var(--text-muted))] opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-1" />
           </button>
         {/each}
       {/if}
    </div>

    <!-- Footer Action -->
    <div class="p-4 border-t border-[rgb(var(--card-border))] bg-white/5 flex-shrink-0">
      <div class="flex gap-2 mb-3">
        {#if favoriteFolder && !isCurrentFolderFavorite}
          <button
            onclick={navigateToFavorite}
            class="flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-500/10 text-amber-500 font-medium hover:bg-amber-500/20 transition-all text-sm"
          >
            <Pin class="w-4 h-4" />
            Ir a "{favoriteFolder.name}"
          </button>
        {/if}
        {#if !isAtRoot && currentFolder.id !== 'starred' && currentFolder.id !== 'shared'}
          <button
            onclick={saveFavoriteFolder}
            class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 text-[rgb(var(--text-secondary))] font-medium hover:bg-white/10 transition-all text-sm border border-white/5"
          >
            <Star class="w-4 h-4 {isCurrentFolderFavorite ? 'text-amber-500 fill-amber-500' : ''}" />
            {isCurrentFolderFavorite ? 'Favorito' : 'Guardar como favorito'}
          </button>
        {/if}
      </div>
      <div class="flex gap-3">
        <button
          onclick={onClose}
          class="flex-1 px-6 py-3 rounded-2xl bg-white/5 border border-white/10 text-[rgb(var(--text-primary))] font-semibold hover:bg-white/10 transition-all"
        >
          Cancelar
        </button>
        <button
          onclick={handleSelect}
          disabled={!!errorMessage}
          class="flex-[2] px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2"
        >
          <Check class="w-5 h-5" />
          Guardar aquí
        </button>
      </div>
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
