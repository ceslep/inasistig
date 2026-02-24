<script>
  import { jsPDF } from "jspdf";
  import "jspdf-autotable";
  import { onMount } from "svelte";

  // --- ESTADO REACTIVO (Svelte 5) ---
  let record = $state({
    fecha: new Date().toISOString().split("T")[0],
    estudiante: { nombre: "", grado: "", id: "", email: "" },
    categoria: "Fortaleza / Felicitación",
    impacto: "Bajo",
    descripcion: "",
    descargos: "",
    compromisos: "",
    firmaDocente: null,
    firmaEstudiante: null,
  });

  let darkMode = $state(false);
  let showPreview = $state(false);
  let pdfUrl = $state(null);
  let canvasDocente, canvasEstudiante;

  // --- PERSISTENCIA Y CONFIGURACIÓN ---
  onMount(() => {
    // Recuperar datos guardados
    const saved = localStorage.getItem("observador_draft");
    if (saved) {
      const parsed = JSON.parse(saved);
      // Fusionamos para no perder la estructura reactiva
      Object.assign(record, parsed);
    }

    // Detectar preferencia de color del sistema
    darkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;

    initSignature(canvasDocente, "firmaDocente");
    initSignature(canvasEstudiante, "firmaEstudiante");
  });

  // Guardado automático cada vez que cambia el estado
  $effect(() => {
    localStorage.setItem(
      "observador_draft",
      JSON.stringify($state.snapshot(record)),
    );
  });

  // --- LÓGICA DE FIRMAS ---
  const initSignature = (canvas, key) => {
    const ctx = canvas.getContext("2d");
    ctx.lineWidth = 2.5;
    ctx.lineCap = "round";
    ctx.strokeStyle = darkMode ? "#ffffff" : "#1e293b";

    let drawing = false;
    const getPos = (e) => {
      const rect = canvas.getBoundingClientRect();
      return {
        x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
        y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top,
      };
    };

    const start = (e) => {
      drawing = true;
      ctx.beginPath();
      const p = getPos(e);
      ctx.moveTo(p.x, p.y);
    };
    const move = (e) => {
      if (!drawing) return;
      const p = getPos(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    };
    const stop = () => {
      drawing = false;
      record[key] = canvas.toDataURL();
    };

    canvas.addEventListener("mousedown", start);
    canvas.addEventListener("mousemove", move);
    window.addEventListener("mouseup", stop);
    canvas.addEventListener("touchstart", start, { passive: false });
    canvas.addEventListener("touchmove", move, { passive: false });
    canvas.addEventListener("touchend", stop);
  };

  const clearAll = () => {
    if (confirm("¿Limpiar todo el formulario?")) {
      localStorage.removeItem("observador_draft");
      location.reload();
    }
  };

  // --- EXPORTACIÓN PDF ---
  const generatePDF = () => {
    const doc = new jsPDF();
    const w = doc.internal.pageSize.getWidth();

    doc.setFillColor(30, 41, 59).rect(0, 0, w, 40, "F");
    doc
      .setTextColor(255)
      .setFontSize(18)
      .setFont("helvetica", "bold")
      .text("I.E. INSTITUTO GUÁTICA", 20, 20);
    doc
      .setFontSize(10)
      .text("OBSERVADOR DIGITAL - REPORTE DE CONVIVENCIA", 20, 28);

    doc.autoTable({
      startY: 45,
      head: [["Estudiante", "Grado", "Fecha", "Categoría"]],
      body: [
        [
          record.estudiante.nombre,
          record.estudiante.grado,
          record.fecha,
          record.categoria,
        ],
      ],
      headStyles: { fillColor: [79, 70, 229] },
    });

    doc
      .setTextColor(30)
      .setFontSize(12)
      .text("Descripción:", 14, doc.lastAutoTable.finalY + 15);
    doc
      .setFontSize(10)
      .setFont("helvetica", "normal")
      .text(
        doc.splitTextToSize(record.descripcion, 180),
        14,
        doc.lastAutoTable.finalY + 22,
      );

    if (record.firmaDocente)
      doc.addImage(record.firmaDocente, "PNG", 20, 250, 50, 20);
    if (record.firmaEstudiante)
      doc.addImage(record.firmaEstudiante, "PNG", 130, 250, 50, 20);

    pdfUrl = doc.output("bloburl");
    showPreview = true;
  };
</script>

<div class={darkMode ? "dark" : ""}>
  <div
    class="min-h-screen bg-slate-50 dark:bg-[#0f172a] p-4 md:p-10 transition-colors duration-500 font-sans selection:bg-indigo-500/20"
  >
    <div class="max-w-7xl mx-auto space-y-8">
      <header
        class="flex flex-col md:flex-row justify-between items-center bg-white/80 dark:bg-white/5 backdrop-blur-xl p-6 rounded-[2.5rem] border border-slate-200 dark:border-white/10 shadow-sm gap-6"
      >
        <div class="flex items-center gap-4">
          <div
            class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/40"
          >
            <svg
              class="w-6 h-6 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              ></path></svg
            >
          </div>
          <div>
            <h1
              class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tighter"
            >
              Guática Digital
            </h1>
            <p class="text-[10px] font-bold text-indigo-500 tracking-[0.3em]">
              OBSERVADOR ESCOLAR
            </p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button
            onclick={() => (darkMode = !darkMode)}
            class="p-3 rounded-2xl bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-yellow-400 transition-all hover:scale-110"
          >
            {#if darkMode}
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                ><path
                  d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                ></path></svg
              >
            {:else}
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                ><path
                  d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"
                ></path></svg
              >
            {/if}
          </button>
          <button
            onclick={clearAll}
            class="px-6 py-3 rounded-2xl font-bold text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
            >LIMPIAR</button
          >
          <button
            onclick={generatePDF}
            class="px-8 py-3 rounded-2xl bg-indigo-600 text-white font-bold text-xs shadow-xl shadow-indigo-500/30 hover:bg-indigo-500 hover:-translate-y-0.5 transition-all"
            >FINALIZAR ACTA</button
          >
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <main class="lg:col-span-8 space-y-8">
          <div
            class="bg-white dark:bg-white/5 p-8 rounded-[3rem] border border-slate-200 dark:border-white/10 shadow-sm space-y-8"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label
                  class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1"
                  >Nombre Completo</label
                >
                <input
                  bind:value={record.estudiante.nombre}
                  class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border-none outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-white font-semibold transition-all"
                />
              </div>
              <div class="space-y-2">
                <label
                  class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1"
                  >Grado</label
                >
                <input
                  bind:value={record.estudiante.grado}
                  class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border-none outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-white font-semibold transition-all"
                />
              </div>
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1"
                >Categoría de Seguimiento</label
              >
              <div class="flex flex-wrap gap-2">
                {#each ["Fortaleza / Felicitación", "Situación Tipo I", "Situación Tipo II", "Situación Tipo III"] as cat}
                  <button
                    onclick={() => (record.categoria = cat)}
                    class="px-4 py-2 rounded-xl text-[11px] font-black transition-all border {record.categoria ===
                    cat
                      ? 'bg-indigo-600 border-indigo-600 text-white'
                      : 'bg-transparent border-slate-200 dark:border-white/10 text-slate-400 hover:border-indigo-400'}"
                  >
                    {cat}
                  </button>
                {/each}
              </div>
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1"
                >Descripción de los Hechos</label
              >
              <textarea
                bind:value={record.descripcion}
                rows="8"
                class="w-full p-6 rounded-[2rem] bg-slate-50 dark:bg-white/5 border-none outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-white font-medium resize-none leading-relaxed transition-all"
              ></textarea>
            </div>
          </div>
        </main>

        <aside class="lg:col-span-4 space-y-8">
          <div
            class="bg-indigo-950 dark:bg-indigo-600/10 p-8 rounded-[3rem] shadow-2xl space-y-10 relative overflow-hidden"
          >
            <div class="relative z-10 space-y-8">
              <h3 class="text-white text-xl font-black italic tracking-tighter">
                Validación<br />Escolar.
              </h3>

              <div class="space-y-4">
                <div class="flex justify-between items-end px-2">
                  <span
                    class="text-[10px] font-black text-indigo-300 uppercase tracking-widest"
                    >Firma Docente</span
                  >
                  <button
                    onclick={() => {
                      const ctx = canvasDocente.getContext("2d");
                      ctx.clearRect(0, 0, 320, 150);
                      record.firmaDocente = null;
                    }}
                    class="text-[9px] text-white/40 font-bold uppercase"
                    >Borrar</button
                  >
                </div>
                <canvas
                  bind:this={canvasDocente}
                  width="320"
                  height="150"
                  class="w-full bg-white rounded-[2rem] cursor-crosshair shadow-lg touch-none"
                ></canvas>
              </div>

              <div class="space-y-4">
                <div class="flex justify-between items-end px-2">
                  <span
                    class="text-[10px] font-black text-indigo-300 uppercase tracking-widest"
                    >Firma Estudiante</span
                  >
                  <button
                    onclick={() => {
                      const ctx = canvasEstudiante.getContext("2d");
                      ctx.clearRect(0, 0, 320, 150);
                      record.firmaEstudiante = null;
                    }}
                    class="text-[9px] text-white/40 font-bold uppercase"
                    >Borrar</button
                  >
                </div>
                <canvas
                  bind:this={canvasEstudiante}
                  width="320"
                  height="150"
                  class="w-full bg-white rounded-[2rem] cursor-crosshair shadow-lg touch-none"
                ></canvas>
              </div>
            </div>
            <div
              class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"
            ></div>
          </div>
        </aside>
      </div>
    </div>

    {#if showPreview}
      <div
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 backdrop-blur-xl p-4 md:p-10 animate-in fade-in zoom-in duration-300"
      >
        <div
          class="bg-white dark:bg-slate-900 w-full max-w-6xl h-full rounded-[3.5rem] flex flex-col shadow-2xl border border-white/20"
        >
          <div
            class="p-8 flex justify-between items-center border-b border-slate-100 dark:border-white/5"
          >
            <span
              class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.4em]"
              >Acta Generada</span
            >
            <button
              onclick={() => (showPreview = false)}
              class="w-12 h-12 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all transition-colors font-bold"
              >✕</button
            >
          </div>
          <iframe
            src={pdfUrl}
            title="Vista PDF"
            class="flex-1 w-full border-none"
          ></iframe>
          <div
            class="p-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/5 flex justify-center"
          >
            <a
              href={pdfUrl}
              download="Observador_{record.estudiante.nombre}.pdf"
              class="bg-indigo-600 text-white px-12 py-5 rounded-[2rem] font-black text-sm tracking-widest shadow-2xl shadow-indigo-500/40 hover:scale-105 transition-all"
              >DESCARGAR DOCUMENTO FINAL</a
            >
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>

<style>
  :global(html) {
    scroll-behavior: smooth;
  }

  canvas {
    image-rendering: -webkit-optimize-contrast;
  }

  ::-webkit-scrollbar {
    width: 8px;
  }
  ::-webkit-scrollbar-track {
    background: transparent;
  }
  ::-webkit-scrollbar-thumb {
    background: #6366f133;
    border-radius: 10px;
  }
</style>
