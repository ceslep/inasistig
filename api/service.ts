import {
  API_URL_GS,
  BASE_URL,
  DOCENTES_URL,
  MATERIAS_URL,
  ESTUDIANTES_URL,
  ANOTADOR_URL,
  ANOTADOR2_URL,
  ANOTADOR3_URL,
  SAVE_INASISTENCIAS_URL,
  SAVE_ANOTADOR_URL,
  SAVE_DIARIO_URL,
  DIARIO_OPTIONS_URL,
  SAVE_PLANEADOR_URL,
  GET_PLANEADOR_URL,
  SPREADSHEET_ID_PLANEADOR,
  WORKSHEET_TITLE_PLANEADOR,
  SAVE_PIAR_URL,
  GET_PIAR_URL,
  SPREADSHEET_ID_PIAR,
  WORKSHEET_TITLE_PIAR,
  SAVE_ACTA_URL,
  URL_DBAS,
  URL_EBCS,
  UPLOAD_TEMAS_URL,
  GET_TEMAS_BASE_URL,
} from "../src/constants";
import { enqueue } from "../src/lib/offlineQueue";
import { refreshPendingCount } from "../src/lib/networkStore";

async function offlineFallback(
  endpoint: string,
  payload: unknown,
  operationType: string,
) {
  await enqueue({ endpoint, payload, timestamp: Date.now(), operationType });
  refreshPendingCount();
  return { success: true, offline: true, message: "Guardado en cola offline" };
}

function isNetworkError(error: unknown): boolean {
  return error instanceof TypeError && error.message.includes("fetch");
}

export interface InasistenciaPayload {
  [key: string]: string | number | boolean | null | undefined;
}

export interface AnotadorPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: string[][];
}

export interface ActaPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: string[][];
}

export interface DiarioPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: string[][];
}

export interface DiarioOption {
  id: string;
  categoria: string;
  titulo: string;
  descripcion: string;
  impacto: number;
  tiempo_estimado: number;
}

export interface DiarioOptionsResponse {
  success: boolean;
  situaciones: Record<string, DiarioOption[]>;
  metadata?: Record<string, unknown>;
}

export const getDiarioOptions = async (): Promise<DiarioOptionsResponse> => {
  try {
    const response = await fetch(`${DIARIO_OPTIONS_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching diario options:", error);
    throw error;
  }
};

export const getInasistencias = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL_GS}/get_inasistencias.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching inasistencias:", error);
    throw error;
  }
};

export const getAnotador = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL_GS}/get_anotador.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching inasistencias:", error);
    throw error;
  }
};

export const getDiario = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL_GS}/get_diario.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching diario:", error);
    throw error;
  }
};

export const saveInasistencias = async (payload: InasistenciaPayload) => {
  if (!navigator.onLine) {
    return offlineFallback(SAVE_INASISTENCIAS_URL, payload, "saveInasistencias");
  }
  try {
    const response = await fetch(`${SAVE_INASISTENCIAS_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_INASISTENCIAS_URL, payload, "saveInasistencias");
    }
    console.error("Error saving inasistencias:", error);
    throw error;
  }
};

export const getDocentes = async () => {
  try {
    const response = await fetch(`${DOCENTES_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching docentes:", error);
    throw error;
  }
};

export const getMaterias = async () => {
  try {
    const response = await fetch(`${MATERIAS_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching materias:", error);
    throw error;
  }
};

export const getEstudiantes = async () => {
  try {
    const response = await fetch(`${ESTUDIANTES_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching estudiantes:", error);
    throw error;
  }
};

export const getOpcionesAnotador = async () => {
  try {
    const response = await fetch(`${ANOTADOR_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching opciones anotador:", error);
    throw error;
  }
};

export const getOpcionesAnotador2 = async () => {
  try {
    const response = await fetch(`${ANOTADOR2_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching opciones anotador:", error);
    throw error;
  }
};

export const getOpcionesAnotador3 = async () => {
  try {
    const response = await fetch(`${ANOTADOR3_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching opciones anotador3:", error);
    throw error;
  }
};

export const saveAnotador = async (payload: AnotadorPayload) => {
  if (!navigator.onLine) {
    return offlineFallback(SAVE_ANOTADOR_URL, payload, "saveAnotador");
  }
  try {
    const response = await fetch(`${SAVE_ANOTADOR_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_ANOTADOR_URL, payload, "saveAnotador");
    }
    console.error("Error saving anotador:", error);
    throw error;
  }
};

export const saveActa = async (payload: ActaPayload) => {
  if (!navigator.onLine) {
    return offlineFallback(SAVE_ACTA_URL, payload, "saveActa");
  }
  try {
    const response = await fetch(`${SAVE_ACTA_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_ACTA_URL, payload, "saveActa");
    }
    console.error("Error saving acta:", error);
    throw error;
  }
};

export const saveDiario = async (payload: DiarioPayload) => {
  if (!navigator.onLine) {
    return offlineFallback(SAVE_DIARIO_URL, payload, "saveDiario");
  }
  try {
    const response = await fetch(`${SAVE_DIARIO_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_DIARIO_URL, payload, "saveDiario");
    }
    console.error("Error saving diario:", error);
    throw error;
  }
};

export interface RegistroPayload {
  id_grupo: number;
  id_docente: number;
  id_materia: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface Estudiante {
  id: number;
  nombre: string;
  grado: string;
}

export interface Registro {
  id_estudiante: number;
  fecha: string;
  presente: boolean;
  motivo?: string;
}

export interface ReportData {
  estudiantes: Estudiante[];
  registros: Registro[];
}

export const getRegistrosReporte = async (
  payload: RegistroPayload,
): Promise<ReportData> => {
  try {
    const response = await fetch(`${API_URL_GS}/get_registros_reporte.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    const data = await response.json();

    if (!data || typeof data !== "object") {
      throw new Error("Respuesta inválida del servidor");
    }

    if (data.error) {
      throw new Error(data.error);
    }

    if (!data.estudiantes || !Array.isArray(data.estudiantes)) {
      throw new Error("No se encontraron datos de estudiantes");
    }

    if (!data.registros || !Array.isArray(data.registros)) {
      data.registros = [];
    }

return data as ReportData;
  } catch (error) {
    console.error("Error fetching registros reporte:", error);
    if (error instanceof Error) {
      throw error;
    }
    throw new Error("Error desconocido al obtener datos del reporte");
  }
};

// ==================== PLANEADOR ====================

export interface PlaneadorData {
  id?: string;
  fecha_creacion?: string;
  docente: string;
  institution: string;
  campus: string;
  grade: string;
  subject: string;
  period: string;
  dba: string[];
  standard: string[];
  dba_manual: string;
  competency: string;
  has_piar: boolean;
  piar_description: string;
  learning_objectives: string;
  competencias: string;
  indicadores_logro: string;
  exploration: string;
  exploration_activities: string[];
  tiempo_exploracion: number;
  structuring: string;
  structuring_activities: string[];
  tiempo_estructuracion: number;
  practice: string;
  practice_activities: string[];
  tiempo_practica: number;
  transfer: string;
  transfer_activities: string[];
  tiempo_transferencia: number;
  assessment_moment: string;
  assessment_activities: string[];
  tiempo_valoracion: number;
  eval_type: string;
  eval_modalidades: string[];
  eval_instrumentos: string[];
  eval_criterios: string[];
  eval_evidencias: string[];
  eval_criteria: string;
  eval_evidence: string;
  eval_ponderacion_conceptos: number;
  eval_ponderacion_procedimientos: number;
  eval_ponderacion_actitudes: number;
  eval_descripcion_auto: string;
  resources: string;
  planeacion_tipo: string;
  periodo_academico: string;
  fecha_inicio: string;
  fecha_fin: string;
  firma_docente: string;
  fecha_firma: string;
}

export interface PlaneadorPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: PlaneadorData[];
}

export const savePlaneador = async (data: PlaneadorData): Promise<{ success: boolean; message: string }> => {
  const payload = {
    spreadsheetId: SPREADSHEET_ID_PLANEADOR,
    worksheetTitle: WORKSHEET_TITLE_PLANEADOR,
    datos: [data],
  };

  if (!navigator.onLine) {
    return offlineFallback(SAVE_PLANEADOR_URL, payload, "savePlaneador") as Promise<{ success: boolean; message: string }>;
  }
  try {
    const response = await fetch(SAVE_PLANEADOR_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const result = await response.json();
    return result;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_PLANEADOR_URL, payload, "savePlaneador") as Promise<{ success: boolean; message: string }>;
    }
    console.error("Error saving planeador:", error);
    throw error;
  }
};

export interface PlaneadorFiltros {
  docente?: string;
  grado?: string;
  materia?: string;
  periodo?: string;
  fechaDesde?: string;
  fechaHasta?: string;
}

export const getPlaneador = async (filtros: PlaneadorFiltros = {}): Promise<PlaneadorData[]> => {
  try {
    const response = await fetch(GET_PLANEADOR_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(filtros),
    });

    if (!response.ok) {
      throw new Error(`Error ${response.status}`);
    }

    const data = await response.json();
    return data.data || [];
  } catch (error) {
    console.error("Error fetching planeador:", error);
    throw error;
  }
};

// ==================== PLANEADOR LOCAL (localStorage) ====================

export interface PlaneadorLocal extends PlaneadorData {
  id_local: string;
  fecha_local: string;
}

const MAX_LOCAL_PLANEACIONES = 100;
const LOCAL_STORAGE_KEY = 'planeaciones_local';

export const savePlaneadorLocal = (data: PlaneadorData): PlaneadorLocal => {
  const existentes = getPlaneadoresLocales();

  const nueva: PlaneadorLocal = {
    ...data,
    id_local: `local_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`,
    fecha_local: new Date().toISOString(),
  };

  existentes.push(nueva);

  const limitadas = existentes.slice(-MAX_LOCAL_PLANEACIONES);

  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(limitadas));

  return nueva;
};

export const getPlaneadoresLocales = (): PlaneadorLocal[] => {
  try {
    return JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '[]');
  } catch {
    return [];
  }
};

export const getPlaneadorLocal = (id_local: string): PlaneadorLocal | null => {
  const existentes = getPlaneadoresLocales();
  return existentes.find(p => p.id_local === id_local) || null;
};

export const deletePlaneadorLocal = (id_local: string): boolean => {
  const existentes = getPlaneadoresLocales();
  const filtradas = existentes.filter(p => p.id_local !== id_local);
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(filtradas));
  return true;
};

export const clearPlaneadoresLocales = (): void => {
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify([]));
};

export const exportPlaneadoresLocales = (): void => {
  const data = getPlaneadoresLocales();
  const jsonStr = JSON.stringify(data, null, 2);
  const blob = new Blob([jsonStr], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `planeaciones_${new Date().toISOString().split('T')[0]}.json`;
  a.click();
  URL.revokeObjectURL(url);
};

export const importPlaneadoresLocales = (jsonData: string): { success: boolean; count: number; message: string } => {
  try {
    const nuevas = JSON.parse(jsonData);
    if (!Array.isArray(nuevas)) {
      throw new Error('El formato debe ser un array de planeaciones');
    }

    const existentes = getPlaneadoresLocales();
    const combinadas = [...existentes, ...nuevas].slice(-MAX_LOCAL_PLANEACIONES);
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(combinadas));

    const count = combinadas.length - existentes.length;
    return { success: true, count, message: `${count} planeación(es) importada(s)` };
  } catch (error) {
    return { success: false, count: 0, message: `Error: ${error instanceof Error ? error.message : 'Formato inválido'}` };
  }
};

// ==================== TEMAS DEL DOCENTE ====================

export interface TemaDocenteEntry {
  grado: string;
  periodo: string;
  temas: string[];
  actividades: string[];
}

export const uploadTemasDocente = async (
  docente: string,
  data: TemaDocenteEntry[]
): Promise<{ success: boolean; message: string }> => {
  // Sanitizar nombre del docente para el filename
  const sanitizedDocente = docente
    .trim()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "") // Quitar tildes
    .replace(/[^a-zA-Z0-9]/g, "_"); // Reemplazar espacios/caracteres especiales por _
  
  const filename = `${sanitizedDocente}_plan.json`;
  
  const payload = {
    filename,
    data,
  };

  if (!navigator.onLine) {
    return offlineFallback(UPLOAD_TEMAS_URL, payload, "uploadTemasDocente") as Promise<{ success: boolean; message: string }>;
  }
  try {
    const response = await fetch(UPLOAD_TEMAS_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const result = await response.json();
    return result;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(UPLOAD_TEMAS_URL, payload, "uploadTemasDocente") as Promise<{ success: boolean; message: string }>;
    }
    console.error("Error uploading temas docente:", error);
    throw error;
  }
};

export const getTemasDocente = async (docente: string): Promise<TemaDocenteEntry[]> => {
  try {
    // Sanitizar nombre del docente para el filename (mismo proceso que en upload)
    const sanitizedDocente = docente
      .trim()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-zA-Z0-9]/g, "_");
    
    const filename = `${sanitizedDocente}_plan.json`;
    const response = await fetch(`${GET_TEMAS_BASE_URL}?file=${encodeURIComponent(filename)}`);

    if (response.status === 404) {
      return [];
    }

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return Array.isArray(data) ? data : [];
  } catch (error) {
    if (isNetworkError(error)) {
      return [];
    }
    console.error("Error fetching temas docente:", error);
    return [];
  }
};

// ==================== NORMATIVA ====================

export interface NormativaItem {
  id: string;
  tipo: 'DBA' | 'EBC';
  codigo: string;
  descripcion: string;
  metadata: {
    grado?: string;
    area?: string;
    grupo?: string;
    dimension?: string;
    evidencia?: string;
  };
}

export const fetchNormativa = async (
  tipo: 'DBA' | 'EBC',
  filtros: { grado?: string; area?: string }
): Promise<NormativaItem[]> => {
  try {
    const url = tipo === 'DBA' ? URL_DBAS : URL_EBCS;
    const params = new URLSearchParams();
    if (filtros.area) params.append('area', filtros.area.toLowerCase());
    if (filtros.grado) params.append('grado', filtros.grado.toLowerCase());

    const response = await fetch(`${url}?${params.toString()}`);
    if (!response.ok) throw new Error(`Error: ${response.status}`);
    const data = await response.json();
    return data.data || [];
  } catch (error) {
    console.error(`Error fetching ${tipo}s:`, error);
    throw error;
  }
};

// ==================== PIAR ====================

export interface PiarData {
  id?: string;
  fecha_creacion?: string;
  docente?: string;
  nombres?: string;
  apellidos?: string;
  tipoDoc?: string;
  numDoc?: string;
  grado?: string;
  fechaNacimiento?: string;
  lugarNacimiento?: string;
  telefono?: string;
  correo?: string;
  barrio?: string;
  eps?: string;
  tieneDiagnostico?: boolean;
  cualDiagnostico?: string;
  asisteTerapias?: boolean;
  terapias?: string;
  tratamientoMedico?: boolean;
  cualTratamiento?: string;
  productosApoyo?: boolean;
  cualesApoyos?: string;
  nombreMadre?: string;
  nombrePadre?: string;
  nombreCuidador?: string;
  telefonoCuidador?: string;
  vinculadoOtraInstitucion?: boolean;
  observacionesGrado?: string;
  descripcionEstudiante?: string;
  habilidadesCompetencias?: string;
  recomendacionesFamilia?: string;
  compromisosEspecificos?: string;
}

export const savePiar = async (data: PiarData): Promise<{ success: boolean; message: string }> => {
  const payload = {
    spreadsheetId: SPREADSHEET_ID_PIAR,
    worksheetTitle: WORKSHEET_TITLE_PIAR,
    datos: [data],
  };

  if (!navigator.onLine) {
    return offlineFallback(SAVE_PIAR_URL, payload, "savePiar") as Promise<{ success: boolean; message: string }>;
  }

  try {
    const response = await fetch(SAVE_PIAR_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const result = await response.json();
    return result;
  } catch (error) {
    if (isNetworkError(error)) {
      return offlineFallback(SAVE_PIAR_URL, payload, "savePiar") as Promise<{ success: boolean; message: string }>;
    }
    console.error("Error saving PIAR:", error);
    throw error;
  }
};

export interface PiarFiltros {
  docente?: string;
  nombres?: string;
  apellidos?: string;
  grado?: string;
}

export const getPiar = async (filtros: PiarFiltros = {}): Promise<PiarData[]> => {
  try {
    const response = await fetch(GET_PIAR_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(filtros),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data.data || [];
  } catch (error) {
    console.error("Error fetching PIAR:", error);
    throw error;
  }
};

// ==================== INASISTENCIA STATS ====================

export interface InasistenciaStatsPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  docente: string;
  grado: string;
  materias: string[];
}

export interface StudentRankingEntry {
  nombre: string;
  count: number;
}

export interface InasistenciaStats {
  totalCount: number;
  currentMonthCount: number;
  prevMonthCount: number;
  trendPercent: number;
  byMotivo: Record<string, number>;
  byDayOfWeek: Record<string, number>;
  studentRanking: StudentRankingEntry[];
  topStudents: StudentRankingEntry[];
}

const DAY_NAMES: Record<number, string> = {
  1: "Lunes",
  2: "Martes",
  3: "Miércoles",
  4: "Jueves",
  5: "Viernes",
  6: "Sábado",
};

export const loadInasistenciaStats = async (payload: InasistenciaStatsPayload): Promise<InasistenciaStats> => {
  const { spreadsheetId, worksheetTitle, docente, grado, materias } = payload;

  const response = await getInasistencias({ spreadsheetId, worksheetTitle });

  if (!response?.records || !Array.isArray(response.records)) {
    throw new Error("No se pudieron obtener los registros");
  }

  const dataRows = response.records.slice(1);

  const normalize = (str: string | null | undefined): string => {
    if (!str) return "";
    return str
      .toString()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-zA-Z0-9\s]/g, "")
      .replace(/\s+/g, " ")
      .trim()
      .toLowerCase();
  };

  const normalizeFecha = (fecha: string): string => {
    if (!fecha) return "";
    const parts = fecha.split("/");
    if (parts.length === 3) {
      const day = parts[0].padStart(2, "0");
      const month = parts[1].padStart(2, "0");
      const year = parts[2];
      return `${year}-${month}-${day}`;
    }
    return fecha;
  };

  const targetDocente = normalize(docente);
  const targetGrado = grado.toString().trim();

  const filtered = dataRows
    .map((row: any) => {
      const values = row.values || [];
      return {
        fecha: values[2] || "",
        docente: values[1] || "",
        materia: values[4] || "",
        grado: (values[6] || "").toString().trim(),
        nombre: values[7] || "",
        motivo: values[5] || "",
        horas: values[3] || "0",
      };
    })
    .filter((item: any) => {
      const itemDocente = normalize(item.docente);
      const itemGrado = item.grado;
      const itemMateria = normalize(item.materia);

      const matchDocente =
        itemDocente.includes(targetDocente) ||
        targetDocente.includes(itemDocente);
      const matchGrado =
        itemGrado === targetGrado ||
        itemGrado.startsWith(targetGrado) ||
        targetGrado.startsWith(itemGrado);
      const matchMateria = materias.some((m) => {
        const nm = normalize(m);
        return itemMateria.includes(nm) || nm.includes(itemMateria);
      });

      return matchDocente && matchGrado && matchMateria;
    });

  const now = new Date();
  const currentYear = now.getUTCFullYear();
  const currentMonth = now.getUTCMonth();
  const prevMonth = currentMonth === 0 ? 11 : currentMonth - 1;
  const prevMonthYear = currentMonth === 0 ? currentYear - 1 : currentYear;

  let currentMonthCount = 0;
  let prevMonthCount = 0;
  const byMotivo: Record<string, number> = {};
  const byDayOfWeek: Record<number, number> = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 };
  const studentCounts: Record<string, number> = {};

  for (const item of filtered) {
    const fechaNorm = normalizeFecha(item.fecha);
    const itemDate = new Date(fechaNorm);
    if (isNaN(itemDate.getTime())) continue;

    const itemMonth = itemDate.getUTCMonth();
    const itemYear = itemDate.getUTCFullYear();

    if (itemYear === currentYear && itemMonth === currentMonth) {
      currentMonthCount++;
    } else if (itemYear === prevMonthYear && itemMonth === prevMonth) {
      prevMonthCount++;
    }

    byMotivo[item.motivo] = (byMotivo[item.motivo] || 0) + 1;

    const dayOfWeek = itemDate.getUTCDay();
    if (dayOfWeek >= 1 && dayOfWeek <= 6) {
      byDayOfWeek[dayOfWeek]++;
    }

    studentCounts[item.nombre] = (studentCounts[item.nombre] || 0) + 1;
  }

  const trendPercent =
    prevMonthCount > 0
      ? Math.round(((currentMonthCount - prevMonthCount) / prevMonthCount) * 100)
      : currentMonthCount > 0
        ? 100
        : 0;

  const studentRanking = Object.entries(studentCounts)
    .map(([nombre, count]) => ({ nombre, count }))
    .sort((a, b) => b.count - a.count);

  const topStudents = studentRanking.slice(0, 5);

  return {
    totalCount: filtered.length,
    currentMonthCount,
    prevMonthCount,
    trendPercent,
    byMotivo,
    byDayOfWeek,
    studentRanking,
    topStudents,
  };
};


