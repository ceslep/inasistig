<script lang="ts">
  import { API_CONFIG } from './constants.js';
  
  let { value = $bindable(""), id = "", initialValue = "" } = $props();

  let teachers = $state<string[]>([]);
  let loading = $state(true);
  let error = $state<string | null>(null);

  function normalizeString(str: string): string {
    return str.trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }

  async function fetchTeachers() {
    try {
      loading = true;
      error = null;
      const response = await fetch(API_CONFIG.profesURL);
      if (!response.ok) {
        throw new Error(`Error fetching teachers: ${response.status}`);
      }
      const data = await response.json();
      
      // Deduplicate teachers by normalized name
      const teacherMap = new Map<string, string>();
      data.forEach((teacher: string) => {
        const normalized = normalizeString(teacher);
        if (!teacherMap.has(normalized)) {
          teacherMap.set(normalized, teacher.trim());
        }
      });
      teachers = Array.from(teacherMap.values()).sort((a, b) => a.localeCompare(b, 'es'));

      // Auto-select teacher from URL parameter
      if (initialValue && !value) {
        const normalizedInitial = normalizeString(initialValue);
        const match = teachers.find(t => normalizeString(t) === normalizedInitial);
        if (match) {
          value = match;
        }
      }
    } catch (err) {
      error = err instanceof Error ? err.message : 'Unknown error';
      console.error('Failed to fetch teachers:', err);
    } finally {
      loading = false;
    }
  }

  fetchTeachers();
</script>

<select
  {id}
  bind:value
  class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all appearance-none cursor-pointer"
  disabled={loading}
>
  <option value="" disabled selected>
    {loading ? 'Cargando docentes...' : 'Seleccione un docente...'}
  </option>
  {#if error}
    <option value="" disabled>Error: {error}</option>
  {:else}
    {#each teachers as teacher}
      <option value={teacher}>{teacher}</option>
    {/each}
  {/if}
</select>

<style>
  select option {
    background: white;
    color: #1a1a1a;
  }
</style>
