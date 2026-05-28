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
    modoPDF = false,
    onClose,
  }: {
    diaSeleccionado: string;
    fechaSeleccionada: string;
    coberturas: CoberturaSugerida[];
    gruposAusentes?: Array<{ grupo: string; horaInicio: number }>;
    docentesAusentes?: DocenteAusente[];
    modoPDF?: boolean;
    onClose: () => void;
  } = $props();

  // Filtros para reportes:
  // - Grupos que NO ASISTEN (gruposAusentes con horaInicio === 1)
  // - Grupos que se LIBERAN desde hora N (>= 2), ya sea por horaInicio>=2 en gruposAusentes
  //   o porque la cobertura quedó sin docente que cubra (grupoLiberado por ausencia docente).
  const gruposNoAsisten = $derived(
    gruposAusentes.filter((g) => g.horaInicio === 1)
  );

  // Liberados intra-día: combinación de gruposAusentes con horaInicio>=2 + coberturas sin docenteCubre
  type Liberado = { grupo: string; hora: number; docenteAusente?: string; motivo: string };
  const gruposLiberadosIntraDia = $derived.by<Liberado[]>(() => {
    const lista: Liberado[] = [];
    const vistos = new Set<string>();

    // Coberturas sin docente que cubra -> grupo liberado en esa hora
    for (const c of coberturas) {
      if (c.docenteCubre) continue;
      if (!c.grupoAusente && !c.grupoACubrir) continue;
      const g = c.grupoAusente || c.grupoACubrir;
      const key = `${g}-${c.hora}`;
      if (vistos.has(key)) continue;
      vistos.add(key);
      lista.push({
        grupo: g,
        hora: c.hora + 1,
        docenteAusente: c.docenteAusente,
        motivo: c.docenteAusente ? `Sin cubridor — ${c.docenteAusente} ausente` : "Sin cubridor",
      });
    }

    // Grupos liberados desde horaInicio>=2 declarados manualmente
    for (const g of gruposAusentes) {
      if (g.horaInicio <= 1) continue;
      const key = `${g.grupo}-${g.horaInicio - 1}`;
      if (vistos.has(key)) continue;
      vistos.add(key);
      lista.push({
        grupo: g.grupo,
        hora: g.horaInicio,
        motivo: "Grupo liberado",
      });
    }

    return lista.sort((a, b) => a.hora - b.hora || a.grupo.localeCompare(b.grupo));
  });

  let generando = $state(false);

  function formatearFecha(fecha: string): string {
    if (!fecha) return "";
    const [y, m, d] = fecha.split("-");
    const meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    return `${parseInt(d)} ${meses[parseInt(m) - 1]} ${y}`;
  }

  // Horarios reales del IE de Occidente — hora 1-indexed
  const HORAS_INICIO_REAL: Record<number, string> = {
    1: "6:45 a.m.",
    2: "7:35 a.m.",
    3: "8:25 a.m.",
    4: "9:30 a.m.",
    5: "10:20 a.m.",
    6: "11:10 a.m.",
    7: "12:00 m.",
  };

  function horaReal(h: number): string {
    return HORAS_INICIO_REAL[h] || `Hora ${h}`;
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
      {#if modoPDF}
        <div id="report-card" class="bg-white text-black" style="font-family: Arial, sans-serif; background-color: #ffffff; color: #000000; padding: 40px 50px; max-width: 8.5in; margin: 0 auto;">
          <div class="text-center mb-6" style="border-bottom: 2px solid #000; padding-bottom: 12px;">
            <h1 class="text-2xl font-bold" style="color: #000; margin: 0 0 4px 0;">INSTITUCION EDUCATIVA</h1>
            <h2 class="text-xl font-bold" style="color: #000; margin: 0;">INSTITUTO GUATICA</h2>
            <p class="text-sm" style="color: #333; margin: 8px 0 0 0;">Reporte de Coberturas - {formatoDia(diaSeleccionado)}</p>
            <p class="text-sm" style="color: #333; margin: 4px 0 0 0;">{formatearFecha(fechaSeleccionada)}</p>
          </div>

          {#if gruposNoAsisten.length > 0}
            <div style="margin: 16px 0;">
              <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">GRUPOS QUE NO ASISTEN:</p>
              <ul style="list-style: disc; padding-left: 24px; margin: 0;">
                {#each gruposNoAsisten as g}
                  <li>Grupo {g.grupo} (no asisten desde hora 1 — {horaReal(1)})</li>
                {/each}
              </ul>
            </div>
          {/if}

          {#if gruposLiberadosIntraDia.length > 0}
            <div style="margin: 16px 0;">
              <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">GRUPOS LIBERADOS DURANTE LA JORNADA:</p>
              <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #f3f4f6;">
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Grupo</th>
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Se libera desde</th>
                    <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  {#each gruposLiberadosIntraDia as l}
                    <tr>
                      <td style="padding: 6px; border: 1px solid #000;">{l.grupo}</td>
                      <td style="padding: 6px; border: 1px solid #000;">Hora {l.hora} ({horaReal(l.hora)})</td>
                      <td style="padding: 6px; border: 1px solid #000;">{l.motivo}</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {/if}

          <div style="margin: 16px 0;">
            <p class="font-bold" style="margin: 0 0 8px 0; text-decoration: underline;">COBERTURAS DEL DIA:</p>
            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
              <thead>
                <tr style="background-color: #f3f4f6;">
                  <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Hora</th>
                  <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Ausente</th>
                  <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Cubre</th>
                  <th style="padding: 6px; text-align: left; font-weight: bold; border: 1px solid #000;">Grupo</th>
                </tr>
              </thead>
              <tbody>
                {#each coberturas as cov}
                  <tr>
                    <td style="padding: 6px; border: 1px solid #000;">{formatoHora(cov.hora)}</td>
                    <td style="padding: 6px; border: 1px solid #000;">{cov.docenteAusente}</td>
                    <td style="padding: 6px; border: 1px solid #000;">{cov.docenteCubre || "Por asignar"}</td>
                    <td style="padding: 6px; border: 1px solid #000;">{cov.grupoAusente || cov.grupoACubrir}</td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>

          <div style="margin-top: 40px; padding-top: 16px; border-top: 1px solid #000;">
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
              <div style="text-align: center;">
                <div style="width: 200px; border-bottom: 1px solid #000; margin-bottom: 4px;">&nbsp;</div>
                <p style="margin: 0; font-size: 11px;">Firma Director(a)</p>
              </div>
              <div style="text-align: center;">
                <div style="width: 150px; border-bottom: 1px solid #000; margin-bottom: 4px;">&nbsp;</div>
                <p style="margin: 0; font-size: 11px;">Fecha</p>
              </div>
            </div>
          </div>

          <div class="text-center" style="margin-top: 30px; font-size: 10px; color: #666;">
            Generado por Sistema de Inasistencias
          </div>
        </div>
      {:else}
        <div id="report-card" class="bg-white rounded-lg p-5 text-gray-800" style="font-family: Arial, sans-serif; background-color: #ffffff; color: #374151;">
          <div class="text-center mb-5 pb-4 border-b" style="border-color: #e5e7eb;">
            <h1 class="text-xl font-bold" style="color: #1e40af;">INSTITUTO GUATICA</h1>
            <p class="text-base font-medium" style="color: #6b7280;">Reporte de Coberturas - {formatoDia(diaSeleccionado)}</p>
            <p class="text-sm" style="color: #9ca3af;">{formatearFecha(fechaSeleccionada)}</p>
          </div>

          {#if gruposNoAsisten.length > 0}
            <div style="margin-bottom: 20px; padding: 16px; background-color: #fef3c7; border: 3px solid #f59e0b; border-radius: 12px;">
              <p style="font-size: 16px; font-weight: bold; color: #92400e; margin: 0 0 8px 0;">
                ⚠️ AVISO A PADRES Y ACUDIENTES
              </p>
              <p style="font-size: 13px; color: #78350f; margin: 0 0 12px 0; line-height: 1.5;">
                Los siguientes grupos <strong>NO ASISTIRÁN</strong> el día de hoy. Por favor no enviar los estudiantes al colegio:
              </p>
              <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #fde68a;">
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #92400e; border: 1px solid #f59e0b;">Grupo</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #92400e; border: 1px solid #f59e0b;">No asisten desde</th>
                  </tr>
                </thead>
                <tbody>
                  {#each gruposNoAsisten as g}
                    <tr>
                      <td style="padding: 8px; font-weight: bold; color: #b45309; border: 1px solid #fde68a; background-color: #fffbeb;">{g.grupo}</td>
                      <td style="padding: 8px; color: #78350f; border: 1px solid #fde68a; background-color: #fffbeb; font-weight: bold;">Hora 1 ({horaReal(1)})</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
              <p style="font-size: 11px; color: #92400e; margin: 10px 0 0 0; font-style: italic;">
                Compartir esta información con los acudientes vía WhatsApp.
              </p>
            </div>
          {/if}

          {#if gruposLiberadosIntraDia.length > 0}
            <div style="margin-bottom: 20px; padding: 16px; background-color: #fef3c7; border: 3px solid #f97316; border-radius: 12px;">
              <p style="font-size: 16px; font-weight: bold; color: #9a3412; margin: 0 0 8px 0;">
                🔔 GRUPOS LIBERADOS DURANTE LA JORNADA
              </p>
              <p style="font-size: 13px; color: #7c2d12; margin: 0 0 12px 0; line-height: 1.5;">
                Estos grupos quedan <strong>sin clase</strong> desde la hora indicada:
              </p>
              <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
                <thead>
                  <tr style="background-color: #fed7aa;">
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Grupo</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Libre desde</th>
                    <th style="padding: 8px; text-align: left; font-weight: bold; color: #9a3412; border: 1px solid #f97316;">Motivo</th>
                  </tr>
                </thead>
                <tbody>
                  {#each gruposLiberadosIntraDia as l}
                    <tr>
                      <td style="padding: 8px; font-weight: bold; color: #9a3412; border: 1px solid #fed7aa; background-color: #fff7ed;">{l.grupo}</td>
                      <td style="padding: 8px; color: #7c2d12; border: 1px solid #fed7aa; background-color: #fff7ed;">Hora {l.hora} ({horaReal(l.hora)})</td>
                      <td style="padding: 8px; color: #7c2d12; border: 1px solid #fed7aa; background-color: #fff7ed; font-style: italic;">{l.motivo}</td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {/if}

          <div style="margin-bottom: 20px;">
            <p style="font-size: 15px; font-weight: 600; margin: 0 0 12px 0; color: #374151;">
              Coberturas asignadas ({coberturas.length})
            </p>
            <table style="width: 100%; font-size: 13px; line-height: 1.6; border-collapse: collapse;">
              <thead>
                <tr style="background-color: #f3f4f6;">
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Hora</th>
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Ausente</th>
                  <th style="padding: 10px; text-align: left; font-weight: bold; color: #374151;">Cubre</th>
                </tr>
              </thead>
              <tbody>
                {#each coberturas as cov}
                  <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 10px; font-weight: 600; color: #1e40af;">{formatoHora(cov.hora)}</td>
                    <td style="padding: 10px; color: #6b7280;">
                      <div style="font-weight: 500;">{cov.docenteAusente}</div>
                      <div style="font-size: 11px; color: #9ca3af;">Grupo {cov.grupoAusente || cov.grupoACubrir}</div>
                    </td>
                    <td style="padding: 10px; font-weight: 600; color: #059669;">{cov.docenteCubre || "Por asignar"}</td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>

          {#if docentesAusentes.length > 0 || gruposAusentes.length === 0}
            <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
              <p style="font-size: 15px; font-weight: bold; margin: 0 0 12px 0; color: #374151;">RESUMEN DE AUSENCIAS</p>
              <div style="font-size: 13px; color: #6b7280;">
                {#if docentesAusentes.length > 0}
                  <p style="margin: 0 0 8px 0;"><strong>Docentes ausentes:</strong></p>
                  <ul style="list-style: disc; padding-left: 24px; margin: 0;">
                    {#each docentesAusentes as d}
                      <li>{d.nombre} <span style="font-weight: 600; color: #dc2626;">({d.tipo})</span></li>
                    {/each}
                  </ul>
                {/if}
                {#if gruposAusentes.length === 0 && docentesAusentes.length === 0}
                  <p style="margin: 0;">No hay ausencias registradas.</p>
                {/if}
              </div>
            </div>
          {/if}

          <div class="text-center text-sm pt-4 mt-4 border-t" style="color: #9ca3af;">
            Generado por Inasistig
          </div>
        </div>
      {/if}
    </div>

    <div class="flex gap-3 p-4 border-t shrink-0" style="border-color: rgb(var(--border-primary));">
      {#if modoPDF}
        <button
          onclick={() => window.print()}
          class="flex-1 py-2 rounded-lg font-medium transition-all flex items-center justify-center gap-2"
          style="background-color: rgb(var(--accent-primary)); color: white;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Imprimir / Guardar PDF
        </button>
        <button
          onclick={onClose}
          class="flex-1 py-2 rounded-lg font-medium transition-all"
          style="background-color: rgb(var(--bg-secondary)); color: rgb(var(--text-primary)); border: 1px solid rgb(var(--border-primary));"
        >
          Cerrar
        </button>
      {:else}
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
      {/if}
    </div>
  </div>
</div>