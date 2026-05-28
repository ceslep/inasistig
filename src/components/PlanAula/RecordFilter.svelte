<script lang="ts">
  import eieLogo from "../../assets/eie.png";

  let {
    records = [],
    onSelectRecord
  }: {
    records: any[];
    onSelectRecord: (record: any[]) => void;
  } = $props();

  let searchTerm = $state("");
  let selectedArea = $state("");
  let selectedGrade = $state("");
  let selectedTeacher = $state("");
  let selectedPeriod = $state("");
  let sortBy = $state("recent");

  const uniqueAreas = $derived(() => {
    const areas = records.map((r: any) => r[0]).filter(Boolean);
    return [...new Set(areas)].sort();
  });

  const uniqueGrades = $derived(() => {
    const grades = records.map((r: any) => r[2]).filter(Boolean);
    return [...new Set(grades)].sort();
  });

  const uniqueTeachers = $derived(() => {
    const teachers = records.map((r: any) => r[1]).filter(Boolean);
    return [...new Set(teachers)].sort();
  });

  const uniquePeriods = $derived(() => {
    const periods = records.map((r: any) => r[4]).filter(Boolean);
    return [...new Set(periods)].sort();
  });

  const shouldShowPDFButton = $derived(() => {
    if (!selectedArea || !selectedGrade || selectedPeriod) return false;
    const allPossiblePeriods = ['I', 'II', 'III', 'IV'];
    const currentGroupPeriods = new Set(
      records
        .filter((r: any) => r[0] === selectedArea && r[2] === selectedGrade)
        .map((r: any) => r[4])
        .filter(Boolean)
    );
    return allPossiblePeriods.every(period => currentGroupPeriods.has(period)) &&
           currentGroupPeriods.size === allPossiblePeriods.length;
  });

  const filteredRecords = $derived(() => {
    let filtered = records.filter((record: any) => {
      const matchesSearch = !searchTerm ||
        record.some((field: any) =>
          field && field.toString().toLowerCase().includes(searchTerm.toLowerCase())
        );
      const matchesArea = !selectedArea || record[0] === selectedArea;
      const matchesGrade = !selectedGrade || record[2] === selectedGrade;
      const matchesTeacher = !selectedTeacher || record[1] === selectedTeacher;
      const matchesPeriod = !selectedPeriod || record[4] === selectedPeriod;
      return matchesSearch && matchesArea && matchesGrade && matchesTeacher && matchesPeriod;
    });

    switch (sortBy) {
      case "area":
        return filtered.sort((a: any, b: any) => (a[0] || "").localeCompare(b[0] || ""));
      case "teacher":
        return filtered.sort((a: any, b: any) => (a[1] || "").localeCompare(b[1] || ""));
      case "period":
        return filtered.sort((a: any, b: any) => (a[4] || "").localeCompare(b[4] || ""));
      default:
        return filtered;
    }
  });

  const groupedRecords = $derived(() => {
    const groups: Record<string, any[]> = {};
    for (const row of filteredRecords()) {
      const key = `${row[0]} - ${row[2]}`;
      if (!groups[key]) groups[key] = [];
      groups[key].push(row);
    }
    return groups;
  });

  function clearFilters() {
    searchTerm = "";
    selectedArea = "";
    selectedGrade = "";
    selectedTeacher = "";
    selectedPeriod = "";
    sortBy = "recent";
  }

  function getRecordCount() {
    return filteredRecords().length;
  }

  function generatePDF() {
    if (!selectedArea || !selectedGrade) return;

    const allPeriodRecords = records.filter((r: any) =>
      r[0] === selectedArea && r[2] === selectedGrade
    );

    const sortedRecords = allPeriodRecords.sort((a: any, b: any) => {
      const periodOrder = ['I', 'II', 'III', 'IV'];
      return periodOrder.indexOf(a[4]) - periodOrder.indexOf(b[4]);
    });

    const logoSrc = eieLogo || '';

    const headerHTML = `
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: white;">
        <tbody>
          <tr>
            <td rowspan="2" style="width: 80px; padding: 10px; border: 1px solid black; text-align: center; vertical-align: middle;">
              ${logoSrc ? `<img src="${logoSrc}" alt="Escudo" style="max-width: 60px; max-height: 70px; width: auto; height: auto;" />` : `
              <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M9 12l2 2 4-4"/>
              </svg>`}
            </td>
            <td style="border: 1px solid black; padding: 8px;">
              <h1 style="font-size: 1.2rem; margin: 0; font-weight: bold;">INSTITUCIÓN EDUCATIVA INSTITUTO GUÁTICA</h1>
              <p style="margin: 4px 0 0; font-size: 0.9rem;">DANE</p>
            </td>
            <td rowspan="2" style="border: 1px solid black; padding: 8px; vertical-align: middle;">
              <h2 style="font-size: 1rem; margin: 0; font-weight: bold;">GESTIÓN ACADÉMICA</h2>
            </td>
            <td style="width: 150px; font-size: 0.8rem; font-weight: bold; height: 20px; border: 1px solid black;">CÓDIGO</td>
          </tr>
          <tr>
            <td style="border: 1px solid black; padding: 8px; height: 40px;"></td>
            <td style="border: 1px solid black; padding: 8px; background-color: #eee;">
              <div style="font-weight: bold; font-size: 0.9rem;">PLAN DE AULA</div>
              <div style="font-size: 0.8rem;">2026</div>
            </td>
          </tr>
        </tbody>
      </table>
    `;

    let allPeriodsHTML = `
      <div style="font-family: Arial, sans-serif; padding: 20px;">
        ${headerHTML}
        <h2 style="text-align: center; margin: 20px 0 30px 0;">
          PLAN DE AULA COMPLETO - ${selectedArea} - GRADO ${selectedGrade}
        </h2>
    `;

    sortedRecords.forEach((record: any, index: number) => {
      const isLastRecord = index === sortedRecords.length - 1;
      allPeriodsHTML += `
        <div style="${isLastRecord ? '' : 'page-break-after: always;'} margin-bottom: 40px;">
          ${index === 0 ? '' : headerHTML}
          <h3 style="color: #333; margin-bottom: 15px; text-align: center; font-size: 1.1rem;">PERIODO ${record[4]}</h3>

          <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
              <td style="width: 50%; padding: 8px; border: 1px solid #333; font-weight: bold;">ÁREA:</td>
              <td style="width: 50%; padding: 8px; border: 1px solid #333;">${record[0]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">DOCENTE:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[1]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">GRADO:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[2]}</td>
            </tr>
            <tr>
              <td style="padding: 8px; border: 1px solid #333; font-weight: bold;">INTENSIDAD HORARIA:</td>
              <td style="padding: 8px; border: 1px solid #333;">${record[3]}</td>
            </tr>
          </table>

          <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead>
              <tr>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  Estándares /<br/>CONTENIDOS
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 22%; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  INDICADORES DE DESEMPEÑO
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  DBA
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 18%; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  CRITERIOS DE EVALUACIÓN
                </th>
                <th style="border: 1px solid #333; padding: 8px 6px; background-color: #f0f0f0; width: 20%; font-size: 0.8rem; text-align: center; vertical-align: middle;">
                  ACTIVIDADES Y RECURSOS
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="border: 1px solid #333; padding: 8px; vertical-align: top; height: 180px; font-size: 0.85rem; white-space: pre-wrap;">${record[5] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px; vertical-align: top; height: 180px; font-size: 0.85rem; white-space: pre-wrap;">${record[6] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px; vertical-align: top; height: 180px; font-size: 0.85rem; white-space: pre-wrap;">${record[7] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px; vertical-align: top; height: 180px; font-size: 0.85rem; white-space: pre-wrap;">${record[8] || ''}</td>
                <td style="border: 1px solid #333; padding: 8px; vertical-align: top; height: 180px; font-size: 0.85rem; white-space: pre-wrap;">${record[9] || ''}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `;
    });

    allPeriodsHTML += '</div>';

    const printWindow = window.open('', '_blank');
    if (printWindow) {
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Plan de Aula - ${selectedArea} - ${selectedGrade}</title>
          <style>
            @media print {
              body { margin: 0; font-family: Arial, sans-serif; }
              div { page-break-after: always; }
              div:last-child { page-break-after: auto; }
              @page { margin: 1.5cm; }
            }
            body { font-family: Arial, sans-serif; margin: 0; font-size: 12px; }
            table { border-collapse: collapse; width: 100%; }
            td, th { border: 1px solid #000; }
          </style>
        </head>
        <body>${allPeriodsHTML}</body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    }
  }
</script>

<div class="flex flex-col gap-3 h-full">
  <div class="relative">
    <input
      type="text"
      placeholder="Buscar..."
      bind:value={searchTerm}
      class="w-full rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-3 py-2 pl-10 text-sm text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none"
    />
    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[rgb(var(--text-muted))]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
    </svg>
  </div>

  <div class="grid grid-cols-2 gap-2">
    <select bind:value={selectedArea} class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none">
      <option value="">Todas las áreas</option>
      {#each uniqueAreas() as area}
        <option value={area}>{area}</option>
      {/each}
    </select>

    <select bind:value={selectedGrade} class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none">
      <option value="">Todos los grados</option>
      {#each uniqueGrades() as grade}
        <option value={grade}>Grado {grade}</option>
      {/each}
    </select>

    <select bind:value={selectedTeacher} class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none">
      <option value="">Todos los docentes</option>
      {#each uniqueTeachers() as teacher}
        <option value={teacher}>{teacher}</option>
      {/each}
    </select>

    <select bind:value={selectedPeriod} class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-primary))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] focus:border-[rgb(var(--accent-primary))] focus:outline-none">
      <option value="">Todos los períodos</option>
      {#each uniquePeriods() as period}
        <option value={period}>Período {period}</option>
      {/each}
    </select>
  </div>

  <div class="flex items-center gap-2 border-t border-[rgb(var(--border-primary))] pt-3">
    <select bind:value={sortBy} class="flex-1 rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-primary))] transition-colors duration-200">
      <option value="recent">Más reciente</option>
      <option value="area">Por área</option>
      <option value="teacher">Por docente</option>
      <option value="period">Por período</option>
    </select>

    <button
      onclick={clearFilters}
      disabled={!searchTerm && !selectedArea && !selectedGrade && !selectedTeacher && !selectedPeriod}
      class="rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-secondary))] px-2 py-1.5 text-xs text-[rgb(var(--text-muted))] transition-colors duration-200 hover:border-[rgb(var(--accent-primary))] disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Limpiar
    </button>

    {#if shouldShowPDFButton()}
      <button
        onclick={generatePDF}
        class="flex items-center gap-1 rounded-lg bg-emerald-600 px-2 py-1.5 text-xs font-medium text-white transition-colors duration-200 hover:bg-emerald-700"
      >
        PDF
      </button>
    {/if}
  </div>

  <div class="text-xs text-[rgb(var(--text-muted))]">
    {getRecordCount()} registro{getRecordCount() !== 1 ? 's' : ''}
  </div>

  <div class="flex-1 overflow-y-auto">
    {#if Object.keys(groupedRecords()).length === 0}
      <div class="flex flex-col items-center justify-center py-8 text-center">
        <p class="text-sm text-[rgb(var(--text-muted))]">No se encontraron registros</p>
        <small class="text-xs text-[rgb(var(--text-muted))]">Ajusta los filtros o el término de búsqueda</small>
      </div>
    {:else}
      {#each Object.entries(groupedRecords()) as [key, rows]}
        <div class="mb-4">
          <div class="mb-2 flex items-center gap-2 border-l-2 border-[rgb(var(--accent-primary))] pl-2">
            <span class="text-xs font-bold uppercase text-[rgb(var(--text-muted))]">{key}</span>
          </div>
          <div class="flex flex-col gap-1">
            {#each rows as row}
              <button
                onclick={() => onSelectRecord(row)}
                class="group flex flex-col items-start rounded-lg border border-[rgb(var(--border-primary))] bg-[rgb(var(--card-bg))] p-3 text-left transition-all duration-200 hover:border-[rgb(var(--accent-primary))] hover:shadow-md"
              >
                <div class="flex w-full items-center justify-between">
                  <span class="rounded bg-[rgb(var(--accent-primary))]/10 px-1.5 py-0.5 text-xs font-bold text-[rgb(var(--accent-primary))]">Per: {row[4]}</span>
                  <span class="text-xs text-[rgb(var(--text-muted))]">{row[3]}h</span>
                </div>
                <span class="mt-1 text-sm text-[rgb(var(--text-primary))]">{row[1]}</span>
              </button>
            {/each}
          </div>
        </div>
      {/each}
    {/if}
  </div>
</div>