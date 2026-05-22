<script lang="ts">
  import type { CoberturaSugerida } from "../../lib/coberturaUtils";
  import { formatoHora, formatoDia } from "../../lib/coberturaUtils";
  import html2canvas from "html2canvas";
  import Swal from "sweetalert2";

  type DocenteAusente = { nombre: string; tipo: string };

  let {
    diaSeleccionado,
    fechaSeleccionada,
    coberturas,
    gruposAusentes = [],
    docentesAusentes = [],
    onClose,
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    coberturas: CoberturaSugerida[];
    gruposAusentes?: Array<{ grupo: string; horaInicio: number }>;
    docentesAusentes?: DocenteAusente[];
    onClose: () => void;
  } = $props();

  let generando = $state(false);

  function formatearFecha(fecha: string): string {
    if (!fecha) return "";
    const [y, m, d] = fecha.split("-");
    const meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return `${parseInt(d)} ${meses[parseInt(m) - 1]} ${y}`;
  }

  async function generarImagen() {
    generando = true;
    try {
      const elemento = document.getElementById("report-card");
      if (!elemento) return;

      const modalContent = elemento.closest('[style*="max-h"]') as HTMLElement;
      const scrollContainer = modalContent?.querySelector('[class*="overflow-y-auto"]') as HTMLElement;

      if (scrollContainer) {
        const maxScroll = scrollContainer.scrollHeight;
        scrollContainer.style.overflow = "visible";
        scrollContainer.style.maxHeight = "none";
        scrollContainer.scrollTop = maxScroll + 500;
        await new Promise((r) => setTimeout(r, 300));
      }

      elemento.style.height = "auto";
      elemento.style.maxHeight = "none";
      elemento.style.overflow = "visible";

      await new Promise((r) => setTimeout(r, 200));

      const altoCompleto = elemento.scrollHeight;
      const anchoCompleto = elemento.scrollWidth;

      elemento.style.height = `${altoCompleto + 50}px`;

      await new Promise((r) => setTimeout(r, 100));

      const canvas = await html2canvas(elemento, {
        scale: 2,
        backgroundColor: "#ffffff",
        useCORS: true,
        logging: false,
        imageTimeout: 0,
        width: Math.max(anchoCompleto, 400),
        height: altoCompleto + 50,
        windowWidth: Math.max(anchoCompleto + 50, 450),
        windowHeight: altoCompleto + 100,
      });

      elemento.style.height = "";
      elemento.style.maxHeight = "";
      elemento.style.overflow = "";

      if (scrollContainer) {
        scrollContainer.style.overflow = "";
        scrollContainer.style.maxHeight = "";
        scrollContainer.scrollTop = 0;
      }

      const link = document.createElement("a");
      link.download = `Coberturas_${fechaSeleccionada}.png`;
      link.href = canvas.toDataURL("image/png");
      link.click();

      Swal.fire({
        icon: "success",
        title: "Imagen generada",
        text: "La imagen se descargó correctamente",
        confirmButtonText: "Cerrar",
      });
    } catch (e) {
      console.error(e);
      Swal.fire("Error", "No se pudo generar la imagen", "error");
    } finally {
      generando = false;
    }
  }
</script>

<div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
  <div class="rounded-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col" style="background-color: rgb(var(--bg-primary)); border: 1px solid rgb(var(--border-primary));">
    <div class="flex justify-between items-center p-4 border-b shrink-0" style="border-color: rgb(var(--border-primary));">
      <h3 class="text-lg font-bold" style="color: rgb(var(--text-primary));">Reporte para WhatsApp</h3>
      <button
        onclick={onClose}
        class="w-8 h-8 flex items-center justify-center rounded-full"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary));"
      >
        ✕
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-4 min-h-0">
      <div id="report-card" class="bg-white rounded-lg p-5 text-gray-800" style="font-family: Arial, sans-serif; background-color: #ffffff; color: #374151;">
        <div class="text-center mb-5 pb-4 border-b" style="border-color: #e5e7eb;">
          <h1 class="text-xl font-bold" style="color: #1e40af;">INSTITUTO GUATICA</h1>
          <p class="text-base font-medium" style="color: #6b7280;">Reporte de Coberturas - {formatoDia(diaSeleccionado)}</p>
          <p class="text-sm" style="color: #9ca3af;">{formatearFecha(fechaSeleccionada)}</p>
        </div>

        <div class="mb-5">
          <p class="text-base font-semibold mb-3" style="color: #374151;">
            Coberturas asignadas ({coberturas.length})
          </p>
          <table class="w-full text-sm" style="line-height: 1.6;">
            <thead>
              <tr style="background-color: #f3f4f6;">
                <th class="p-3 text-left font-bold" style="color: #374151;">Hora</th>
                <th class="p-3 text-left font-bold" style="color: #374151;">Ausente</th>
                <th class="p-3 text-left font-bold" style="color: #374151;">Cubre</th>
              </tr>
            </thead>
            <tbody>
              {#each coberturas as cov}
                <tr class="border-b" style="border-color: #f3f4f6;">
                  <td class="p-3 font-semibold" style="color: #1e40af;">{formatoHora(cov.hora)}</td>
                  <td class="p-3" style="color: #6b7280;">
                    <div class="font-medium">{cov.docenteAusente}</div>
                    <div class="text-xs" style="color: #9ca3af;">Grupo {cov.grupoAusente || cov.grupoACubrir}</div>
                  </td>
                  <td class="p-3 font-semibold" style="color: #059669;">{cov.docenteCubre || "Por asignar"}</td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>

        <div class="pt-4 border-t" style="border-color: #e5e7eb;">
          <p class="text-base font-bold mb-3" style="color: #374151;">INFORMACIÓN PARA PADRES Y ACUDIENTES</p>
          <div class="text-sm" style="color: #6b7280;">
            {#if gruposAusentes.length > 0}
              <p class="mb-2"><strong>Grupos que no asisten:</strong></p>
              <ul class="list-disc list-inside mb-4 pl-4">
                {#each gruposAusentes as g}
                  <li>Grupo {g.grupo} (desde hora {g.horaInicio})</li>
                {/each}
              </ul>
            {/if}
            {#if docentesAusentes.length > 0}
              <p class="mb-2"><strong>Docentes ausentes:</strong></p>
              <ul class="list-disc list-inside pl-4">
                {#each docentesAusentes as d}
                  <li>{d.nombre} <span class="font-semibold" style="color: #dc2626;">({d.tipo})</span></li>
                {/each}
              </ul>
            {/if}
            {#if gruposAusentes.length === 0 && docentesAusentes.length === 0}
              <p>No hay ausencias registradas.</p>
            {/if}
          </div>
        </div>

        <div class="text-center text-sm pt-4 mt-4 border-t" style="color: #9ca3af;">
          Generado por Inasistig
        </div>
      </div>
    </div>

    <div class="flex gap-3 p-4 border-t shrink-0" style="border-color: rgb(var(--border-primary));">
      <button
        onclick={generarImagen}
        disabled={generando}
        class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2"
        style="background-color: rgb(var(--accent-primary)); color: white;"
      >
        {#if generando}
          Generando...
        {:else}
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Descargar Imagen
        {/if}
      </button>
      <button
        onclick={onClose}
        class="flex-1 py-2 rounded-lg font-medium transition-all"
        style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
      >
        Cerrar
      </button>
    </div>
  </div>
</div>