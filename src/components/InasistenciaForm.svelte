<script lang="ts">
  import { saveInasistencias } from "../../api/service";
  const DOCENTES_URL = "http://app.iedeoccidente.com/ig/getprofes.php";
  const MATERIAS_URL = "http://app.iedeoccidente.com/ig/getMaterias.php";
  const ESTUDIANTES_URL = "http://app.iedeoccidente.com/ig/getEstudiantes.php";
  import eieLogo from "../assets/eie.png";

  let docentes: string[] = [];
  let materias: Array<{ materia: string }> = [];
  let estudiantes: Array<{ nombre: string; grado: number }> = [];
  let isLoadingDocentes = false;
  let isLoadingMaterias = false;
  let isLoadingEstudiantes = false;

  let formData = {
    docente: "",
    materia: "",
    horas: "",
    grado: "",
    fecha: new Date().toISOString().split("T")[0],
    observaciones: "",
  };

  let inasistencias: Array<{ nombre: string; motivo: string }> = [];

  const motivos = [
    {
      value: "Sin excusa",
      label: "Sin excusa",
      color: "bg-red-500",
      icon: "🚫",
      bgColor: "bg-red-50",
      borderColor: "border-red-300",
      textColor: "text-red-700",
    },
    {
      value: "Excusa",
      label: "Excusa",
      color: "bg-blue-500",
      icon: "📄",
      bgColor: "bg-blue-50",
      borderColor: "border-blue-300",
      textColor: "text-blue-700",
    },
    {
      value: "Permiso",
      label: "Permiso",
      color: "bg-green-500",
      icon: "✅",
      bgColor: "bg-green-50",
      borderColor: "border-green-300",
      textColor: "text-green-700",
    },
    {
      value: "Pacto de Aula",
      label: "Pacto de Aula",
      color: "bg-purple-500",
      icon: "🤝",
      bgColor: "bg-purple-50",
      borderColor: "border-purple-300",
      textColor: "text-purple-700",
    },
    {
      value: "Uso del celular",
      label: "Uso del celular",
      color: "bg-orange-500",
      icon: "📱",
      bgColor: "bg-orange-50",
      borderColor: "border-orange-300",
      textColor: "text-orange-700",
    },
    {
      value: "Desorden en Clase",
      label: "Desorden en Clase",
      color: "bg-yellow-500",
      icon: "🔊",
      bgColor: "bg-yellow-50",
      borderColor: "border-yellow-300",
      textColor: "text-yellow-700",
    },
    {
      value: "Fuga",
      label: "Fuga",
      color: "bg-red-600",
      icon: "🏃‍♂️",
      bgColor: "bg-red-100",
      borderColor: "border-red-400",
      textColor: "text-red-800",
    },
    {
      value: "LLegada Tarde",
      label: "Llegada Tarde",
      color: "bg-indigo-500",
      icon: "⏰",
      bgColor: "bg-indigo-50",
      borderColor: "border-indigo-300",
      textColor: "text-indigo-700",
    },
    {
      value: "No realización de Aseo",
      label: "No realización de Aseo",
      color: "bg-teal-500",
      icon: "🧹",
      bgColor: "bg-teal-50",
      borderColor: "border-teal-300",
      textColor: "text-teal-700",
    },
    {
      value: "Licencia por salud",
      label: "Licencia por salud",
      color: "bg-cyan-500",
      icon: "🏥",
      bgColor: "bg-cyan-50",
      borderColor: "border-cyan-300",
      textColor: "text-cyan-700",
    },
    {
      value: "Incapacidad",
      label: "Incapacidad",
      color: "bg-pink-500",
      icon: "🩺",
      bgColor: "bg-pink-50",
      borderColor: "border-pink-300",
      textColor: "text-pink-700",
    },
    {
      value: "Reunión interna",
      label: "Reunión interna",
      color: "bg-gray-500",
      icon: "👥",
      bgColor: "bg-gray-50",
      borderColor: "border-gray-300",
      textColor: "text-gray-700",
    },
  ];

  let isLoading = false;
  let message = "";

  // Cargar docentes al montar el componente
  const loadDocentes = async () => {
    isLoadingDocentes = true;
    try {
      const response = await fetch(DOCENTES_URL);
      const data = await response.json();
      docentes = data;
    } catch (error) {
      console.error("Error cargando docentes:", error);
    } finally {
      isLoadingDocentes = false;
    }
  };

  // Cargar materias al montar el componente
  const loadMaterias = async () => {
    isLoadingMaterias = true;
    try {
      const response = await fetch(MATERIAS_URL);
      const data = await response.json();
      materias = data;
    } catch (error) {
      console.error("Error cargando materias:", error);
    } finally {
      isLoadingMaterias = false;
    }
  };

  loadDocentes();
  loadMaterias();

  // Cargar estudiantes al montar el componente
  const loadEstudiantes = async () => {
    isLoadingEstudiantes = true;
    try {
      const response = await fetch(ESTUDIANTES_URL);
      const data = await response.json();
      estudiantes = data;
    } catch (error) {
      console.error("Error cargando estudiantes:", error);
    } finally {
      isLoadingEstudiantes = false;
    }
  };

  loadDocentes();
  loadMaterias();
  loadEstudiantes();

  const handleChange = (event: Event) => {
    const target = event.target as
      | HTMLInputElement
      | HTMLSelectElement
      | HTMLTextAreaElement;
    const { name, value } = target;
    (formData as any)[name] = value;
  };

  const handleSubmit = async (event: Event) => {
    event.preventDefault();
    isLoading = true;
    message = "";

    try {
      // Importar el SPREADSHEET_ID desde constants
      const { SPREADSHEET_ID } = await import("../constants");

      // Preparar el payload con la estructura de 9 columnas
      const currentTimestamp = new Date().toISOString();
      const inasistenciasPayload = inasistencias.map((item) => [
        currentTimestamp, // 0. Marca temporal
        formData.docente, // 1. Docente
        formData.fecha, // 2. Fecha
        formData.horas === "0"
          ? "Sin hora específica"
          : `${formData.horas} horas`, // 3. Horas de Inasistencia
        formData.materia, // 4. Asignatura
        item.motivo, // 5. Tipo de registro
        formData.grado, // 6. Grupo (Grado)
        formData.observaciones, // 7. Observaciones
        item.nombre, // 8. Estudiante
      ]);

      const payload = {
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: new Date().toISOString().slice(0, 7), // YYYY-MM
        inasistencias: inasistenciasPayload,
      };

      await saveInasistencias(payload);
      message = `${inasistencias.length} inasistencia(s) registrada(s) exitosamente`;
      formData = {
        docente: "",
        materia: "",
        horas: "",
        grado: "",
        fecha: new Date().toISOString().split("T")[0],
        observaciones: "",
      };
      inasistencias = [];
    } catch (error) {
      console.error("Error detallado:", error);
      message = "Error al registrar la inasistencia";
    } finally {
      isLoading = false;
    }
  };
</script>

<div class="min-h-screen bg-gray-50 py-8 px-4">
  <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-center mb-6">
      <img src={eieLogo} alt="EIE Logo" class="h-16 w-auto" />
    </div>

    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
      Registrar Inasistencia
    </h1>

    <form on:submit={handleSubmit} class="space-y-4">
      <div>
        <label
          for="docente"
          class="block text-sm font-medium text-gray-700 mb-1"
        >
          Docente
        </label>
        <select
          id="docente"
          name="docente"
          bind:value={formData.docente}
          on:change={handleChange}
          required
          disabled={isLoadingDocentes}
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
        >
          <option value="">
            {isLoadingDocentes ? "Cargando..." : "Seleccione un docente"}
          </option>
          {#each docentes as docente}
            <option value={docente}>{docente}</option>
          {/each}
        </select>
      </div>

      <div>
        <label
          for="materia"
          class="block text-sm font-medium text-gray-700 mb-1"
        >
          Materia
        </label>
        <select
          id="materia"
          name="materia"
          bind:value={formData.materia}
          on:change={handleChange}
          required
          disabled={isLoadingMaterias}
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
        >
          <option value="">
            {isLoadingMaterias ? "Cargando..." : "Seleccione una materia"}
          </option>
          {#each materias as materia}
            <option value={materia.materia}>{materia.materia}</option>
          {/each}
        </select>
      </div>

      <div>
        <label for="horas" class="block text-sm font-medium text-gray-700 mb-1">
          Cantidad de Horas
        </label>
        <select
          id="horas"
          name="horas"
          bind:value={formData.horas}
          on:change={handleChange}
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Seleccione horas</option>
          <option value="0">Sin hora específica</option>
          <option value="1">1 Hora</option>
          <option value="2">2 Horas</option>
          <option value="3">3 Horas</option>
          <option value="4">4 Horas</option>
        </select>
      </div>

      <div>
        <label for="grado" class="block text-sm font-medium text-gray-700 mb-1">
          Grado
        </label>
        <select
          id="grado"
          name="grado"
          bind:value={formData.grado}
          on:change={handleChange}
          required
          disabled={isLoadingEstudiantes}
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
        >
          <option value="">
            {isLoadingEstudiantes ? "Cargando..." : "Seleccione un grado"}
          </option>
          <option value="601">6°1</option>
          <option value="602">6°2</option>
          <option value="701">7°1</option>
          <option value="702">7°2</option>
          <option value="801">8°1</option>
          <option value="802">8°2</option>
          <option value="901">9°1</option>
          <option value="902">9°2</option>
          <option value="1001">10°1</option>
          <option value="1101">11°1</option>
          <option value="1102">11°2</option>
        </select>
      </div>

      {#if formData.grado && formData.horas !== ""}
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Estudiantes del Grado {formData.grado} ({formData.horas === "0"
              ? "Sin hora específica"
              : formData.horas +
                (parseInt(formData.horas) === 1 ? " hora" : " horas")})
          </label>
          <div
            class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-md p-2"
          >
            {#each estudiantes.filter((e) => e.grado.toString() === formData.grado) as estudiante}
              {@const currentInasistencia = inasistencias.find(
                (item) => item.nombre === estudiante.nombre,
              )}
              {@const motivoSeleccionado = currentInasistencia?.motivo
                ? motivos.find((m) => m.value === currentInasistencia.motivo)
                : null}

              <div
                class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
              >
                <div class="flex-1">
                  <span class="text-sm font-medium text-gray-900"
                    >{estudiante.nombre}</span
                  >
                  {#if motivoSeleccionado}
                    <div class="mt-1">
                      <span
                        class={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${motivoSeleccionado.bgColor} ${motivoSeleccionado.textColor} ${motivoSeleccionado.borderColor} border`}
                      >
                        <span class="mr-1">{motivoSeleccionado.icon}</span>
                        {motivoSeleccionado.label}
                      </span>
                    </div>
                  {/if}
                </div>

                <div class="relative">
                  <select
                    value={currentInasistencia?.motivo || ""}
                    on:change={(e) => {
                      const motivoValue = (e.target as HTMLSelectElement).value;
                      const existingIndex = inasistencias.findIndex(
                        (item) => item.nombre === estudiante.nombre,
                      );
                      if (existingIndex >= 0) {
                        if (motivoValue) {
                          inasistencias[existingIndex].motivo = motivoValue;
                        } else {
                          inasistencias.splice(existingIndex, 1);
                        }
                      } else if (motivoValue) {
                        inasistencias.push({
                          nombre: estudiante.nombre,
                          motivo: motivoValue,
                        });
                      }
                    }}
                    class="appearance-none bg-white border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer hover:border-gray-400 transition-colors"
                  >
                    <option value="">Motivo...</option>
                    {#each motivos as motivo}
                      <option value={motivo.value}
                        >{motivo.icon} {motivo.label}</option
                      >
                    {/each}
                  </select>
                  <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                  >
                    <svg
                      class="fill-current h-4 w-4"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                      />
                    </svg>
                  </div>
                </div>
              </div>
            {/each}
          </div>
        </div>
      {/if}

      <div>
        <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
          Fecha de Inasistencia/Registro
        </label>
        <input
          type="date"
          id="fecha"
          name="fecha"
          bind:value={formData.fecha}
          on:change={handleChange}
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>

      <div></div>

      <div>
        <label
          for="observaciones"
          class="block text-sm font-medium text-gray-700 mb-1"
        >
          Observaciones
        </label>
        <textarea
          id="observaciones"
          name="observaciones"
          bind:value={formData.observaciones}
          on:change={handleChange}
          rows={3}
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>

      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-600">
          Total de inasistencias:
          <span class="font-semibold text-gray-900">{inasistencias.length}</span
          >
        </div>

        <button
          type="submit"
          disabled={isLoading || inasistencias.length === 0}
          class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
        >
          {#if isLoading}
            <svg
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            Registrando...
          {:else}
            <svg
              class="-ml-1 mr-2 h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            Registrar {inasistencias.length}
            {inasistencias.length === 1 ? "Inasistencia" : "Inasistencias"}
          {/if}
        </button>
      </div>
    </form>

    {#if message}
      <div
        class={`mt-4 p-3 rounded-md text-sm ${message.includes("exitosamente") ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"}`}
      >
        {message}
      </div>
    {/if}
  </div>
</div>
