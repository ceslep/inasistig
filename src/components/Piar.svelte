<script lang="ts">
  import { onMount } from "svelte";
  import { getEstudiantes } from "../../api/service";
  import { theme } from "../lib/themeStore";
  import { ArrowLeft, Download, Plus, Trash2, X } from "lucide-svelte";

  // Tipos básicos
  type Estudiante = {
    nombre: string;
    grado: number | string;
  };

  let { onBack }: { onBack: () => void } = $props();

  let formData = $state({
    estudiante: { nombre: "", documento: "", grado: "", foto: null as string | null },
    salud: { diagnostico: "" },
    ajustes: [{ area: "", barrera: "", ajuste: "" }],
    firma: null as string | null,
  });

  // Estudiantes desde la API
  let estudiantes = $state<Estudiante[]>([]);
  let isLoadingEstudiantes = $state(false);

  // Estudiantes filtrados por grado seleccionado
  let estudiantesFiltrados = $derived(
    formData.estudiante.grado
      ? estudiantes.filter(e => e.grado.toString() === formData.estudiante.grado)
      : []
  );

  // Grados únicos disponibles
  let gradosDisponibles = $derived(
    [...new Set(estudiantes.map(e => e.grado.toString()))].sort((a, b) => {
      const numA = parseInt(a.replace(/\D/g, '')) || 0;
      const numB = parseInt(b.replace(/\D/g, '')) || 0;
      return numA - numB;
    })
  );

  // Referencias del canvas
  let canvas: HTMLCanvasElement;
  let ctx: CanvasRenderingContext2D | null = null;
  let isDrawing = false;
  let showPreview = $state(false);
  let pdfUrl = $state<string | null>(null);
  let fotoPreview = $state<string | null>(null);

  // Cargar estudiantes
  const loadEstudiantes = async () => {
    isLoadingEstudiantes = true;
    try {
      estudiantes = await getEstudiantes();
    } catch (error) {
      console.error("Error cargando estudiantes:", error);
    } finally {
      isLoadingEstudiantes = false;
    }
  };

  // Configuración del Canvas para Firma
  onMount(async () => {
    // Cargar estudiantes
    await loadEstudiantes();
    
    if (canvas) {
      ctx = canvas.getContext("2d");
      ctx.lineWidth = 2;
      ctx.lineJoin = "round";
      ctx.lineCap = "round";
      ctx.strokeStyle = "#000000";
    }
  });

  const startDrawing = (e) => {
    isDrawing = true;
    const { offsetX, offsetY } = getCoords(e);
    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY);
  };

  const draw = (e) => {
    if (!isDrawing) return;
    const { offsetX, offsetY } = getCoords(e);
    ctx.lineTo(offsetX, offsetY);
    ctx.stroke();
  };

  const stopDrawing = () => {
    isDrawing = false;
    formData.firma = canvas.toDataURL("image/png");
  };

  const getCoords = (e) => {
    if (e.touches) {
      const rect = canvas.getBoundingClientRect();
      return {
        offsetX: e.touches[0].clientX - rect.left,
        offsetY: e.touches[0].clientY - rect.top,
      };
    }
    return { offsetX: e.offsetX, offsetY: e.offsetY };
  };

  const clearSignature = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    formData.firma = null;
  };

  // Manejo de Foto
  const handleFile = (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        fotoPreview = ev.target.result;
        formData.estudiante.foto = ev.target.result;
      };
      reader.readAsDataURL(file);
    }
  };

  const generatePDF = async () => {
    // Import dinámico de jsPDF y autotable
    const [{ default: jsPDF }, { default: autoTable }] = await Promise.all([
      import('jspdf'),
      import('jspdf-autotable')
    ]);
    
    const doc = new jsPDF();
    const pageWidth = doc.internal.pageSize.getWidth();

    // Header
    doc.setFillColor(31, 41, 55);
    doc.rect(0, 0, pageWidth, 40, "F");
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(16);
    doc.text("ACTA PIAR - INSTITUTO GUÁTICA", 15, 25);

    if (formData.estudiante.foto)
      doc.addImage(formData.estudiante.foto, "JPEG", 170, 5, 30, 30);

    doc.setTextColor(0);
    autoTable(doc, {
      startY: 45,
      head: [["Estudiante", "Grado", "Documento"]],
      body: [
        [
          formData.estudiante.nombre,
          formData.estudiante.grado,
          formData.estudiante.documento,
        ],
      ],
      headStyles: { fillColor: [55, 65, 81] },
    });

    autoTable(doc, {
      startY: doc.lastAutoTable.finalY + 10,
      head: [["Área", "Ajuste Razonable"]],
      body: formData.ajustes.map((a) => [a.area, a.ajuste]),
    });

    // Insertar Firma
    if (formData.firma) {
      const finalY = doc.lastAutoTable.finalY + 20;
      doc.text("Firma del Responsable:", 15, finalY);
      doc.addImage(formData.firma, "PNG", 15, finalY + 5, 50, 20);
      doc.line(15, finalY + 25, 70, finalY + 25);
    }

    pdfUrl = doc.output("bloburl");
    showPreview = true;
  };
</script>

<div class="max-w-5xl mx-auto p-6 min-h-screen space-y-8 font-sans" style="background-color: rgb(var(--bg-primary)); color: rgb(var(--text-primary));">
  <div
    class="rounded-[2rem] shadow-xl overflow-hidden"
    style="background-color: rgb(var(--card-bg)); border: 1px solid rgb(var(--card-border));"
  >
    <header
      class="bg-slate-800 p-8 text-white flex justify-between items-center"
    >
      <div class="flex items-center gap-4">
        <button
          onclick={onBack}
          class="p-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition-colors"
          aria-label="Volver al Dashboard"
        >
          <ArrowLeft class="w-5 h-5" />
        </button>
        <div
          class="w-16 h-16 rounded-full bg-slate-700 border-2 border-slate-500 overflow-hidden relative group"
        >
          {#if fotoPreview}<img
              src={fotoPreview}
              alt="Foto"
              class="w-full h-full object-cover"
            />{/if}
          <label
            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity"
          >
            <input type="file" onchange={handleFile} class="hidden" />
            <span class="text-[10px] font-bold">SUBIR</span>
          </label>
        </div>
        <h1 class="text-xl font-black tracking-tight">
          Registro PIAR <span class="text-slate-400 font-normal">v2026</span>
        </h1>
      </div>
      <button
        onclick={generatePDF}
        class="bg-emerald-500 hover:bg-emerald-400 px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg flex items-center gap-2"
        ><Download class="w-4 h-4" /> FINALIZAR Y FIRMAR</button
      >
    </header>

    <div class="p-8 space-y-10">
      <!-- Selector de Grado y Estudiante -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-black uppercase tracking-widest mb-2" style="color: rgb(var(--text-secondary));">
            Grado
          </label>
          <select
            bind:value={formData.estudiante.grado}
            class="w-full p-4 rounded-xl border-2 outline-none font-bold"
            style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
            disabled={isLoadingEstudiantes}
          >
            <option value="">
              {isLoadingEstudiantes ? "Cargando..." : "Seleccione grado..."}
            </option>
            {#each gradosDisponibles as grado}
              <option value={grado}>{grado}</option>
            {/each}
          </select>
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-widest mb-2" style="color: rgb(var(--text-secondary));">
            Estudiante
          </label>
          <select
            bind:value={formData.estudiante.nombre}
            class="w-full p-4 rounded-xl border-2 outline-none font-bold"
            style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
            disabled={!formData.estudiante.grado || isLoadingEstudiantes}
          >
            <option value="">
              {!formData.estudiante.grado
                ? "Seleccione grado primero..."
                : isLoadingEstudiantes
                  ? "Cargando..."
                  : "Seleccione estudiante..."}
            </option>
            {#each estudiantesFiltrados as estudiante}
              <option value={estudiante.nombre}>{estudiante.nombre}</option>
            {/each}
          </select>
        </div>
      </div>

      <!-- Información adicional del estudiante -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-black uppercase tracking-widest mb-2" style="color: rgb(var(--text-secondary));">
            Documento (opcional)
          </label>
          <input
            bind:value={formData.estudiante.documento}
            placeholder="Número de documento"
            class="w-full p-4 rounded-xl border-2 outline-none"
            style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
          />
        </div>
      </div>

      <div class="space-y-4">
        {#each formData.ajustes as ajuste}
          <div
            class="flex gap-4 p-4 rounded-2xl"
            style="background-color: rgb(var(--bg-secondary)); border: 1px solid rgb(var(--border-primary));"
          >
            <input
              bind:value={ajuste.area}
              placeholder="Área"
              class="w-1/3 bg-transparent border-b font-bold outline-none uppercase text-xs"
              style="border-color: rgb(var(--border-primary)); color: rgb(var(--text-primary));"
            />
            <textarea
              bind:value={ajuste.ajuste}
              placeholder="Ajustes..."
              class="w-2/3 p-3 rounded-lg text-sm border-none shadow-sm h-16 outline-none"
              style="background-color: rgb(var(--card-bg)); color: rgb(var(--text-primary));"
            ></textarea>
          </div>
        {/each}
        <button
          onclick={() =>
            (formData.ajustes = [
              ...formData.ajustes,
              { area: "", ajuste: "" },
            ])}
          class="text-xs font-bold uppercase tracking-widest flex items-center gap-1"
          style="color: rgb(var(--text-muted));"
          ><Plus class="w-3 h-3" /> Añadir Área</button
        >
      </div>

      <div class="pt-6" style="border-top: 1px solid rgb(var(--border-primary));">
        <div class="flex justify-between items-center mb-4">
          <label
            class="text-xs font-black uppercase tracking-widest"
            style="color: rgb(var(--text-secondary));"
            >Firma del Docente / Acudiente</label
          >
          <button
            onclick={clearSignature}
            class="text-[10px] text-red-500 font-bold hover:underline flex items-center gap-1"
            ><Trash2 class="w-3 h-3" /> LIMPIAR PANEL</button
          >
        </div>
        <div
          class="rounded-2xl border-2 border-dashed p-2 inline-block"
          style="background-color: rgb(var(--bg-secondary)); border-color: rgb(var(--border-primary));"
        >
          <canvas
            bind:this={canvas}
            width="400"
            height="150"
            onmousedown={startDrawing}
            onmousemove={draw}
            onmouseup={stopDrawing}
            onmouseleave={stopDrawing}
            ontouchstart={startDrawing}
            ontouchmove={draw}
            ontouchend={stopDrawing}
            class="rounded-lg cursor-crosshair touch-none"
            style="background-color: rgb(var(--card-bg));"
          ></canvas>
        </div>
        <p class="text-[10px] mt-2 italic" style="color: rgb(var(--text-muted));">
          Use el mouse o panel táctil para firmar dentro del recuadro.
        </p>
      </div>
    </div>
  </div>

  {#if showPreview}
    <div
      class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/95 backdrop-blur-md p-4 animate-in fade-in duration-300"
    >
      <div
        class="w-full max-w-5xl h-[95vh] rounded-[2.5rem] flex flex-col shadow-2xl overflow-hidden"
        style="background-color: rgb(var(--card-bg));"
      >
        <div
          class="p-4 flex justify-between items-center"
          style="background-color: rgb(var(--bg-secondary)); border-bottom: 1px solid rgb(var(--border-primary));"
        >
          <span class="text-sm font-bold px-4" style="color: rgb(var(--text-secondary));"
            >Documento PIAR Generado con Firma Digital</span
          >
          <button
            onclick={() => (showPreview = false)}
            class="w-8 h-8 rounded-full shadow hover:text-red-500 transition-colors flex items-center justify-center"
            style="background-color: rgb(var(--card-bg)); color: rgb(var(--text-primary));"
            ><X class="w-4 h-4" /></button
          >
        </div>
        <iframe src={pdfUrl} title="PDF" class="flex-1 w-full border-none"
        ></iframe>
        <div class="p-6 flex justify-center" style="background-color: rgb(var(--card-bg)); border-top: 1px solid rgb(var(--border-primary));">
          <a
            href={pdfUrl}
            download="PIAR_FIRMADO.pdf"
            class="bg-slate-900 text-white px-12 py-4 rounded-2xl font-black hover:bg-black transition-all shadow-xl"
            >DESCARGAR ACTA OFICIAL</a
          >
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  canvas {
    image-rendering: -moz-crisp-edges;
    image-rendering: -webkit-crisp-edges;
    image-rendering: pixelated;
  }
</style>
