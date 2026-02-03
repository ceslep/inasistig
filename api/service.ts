import {
  API_URL_GS,
  BASE_URL,
  DOCENTES_URL,
  MATERIAS_URL,
  ESTUDIANTES_URL,
  ANOTADOR_URL,
  ANOTADOR2_URL,
  SAVE_INASISTENCIAS_URL,
  SAVE_ANOTADOR_URL,
  SAVE_DIARIO_URL,
  DIARIO_OPTIONS_URL,
} from "../src/constants";

export interface InasistenciaPayload {
  [key: string]: any;
}

export interface AnotadorPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: any[][];
}

export interface DiarioPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: any[][];
}

export interface DiarioOption {
  id: string;
  categoria: string;
  titulo: string;
  descripcion: string;
  impacto: number;
  tiempo_estimado: number;
}

export const getDiarioOptions = async (): Promise<DiarioOption[]> => {
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

export const saveInasistencias = async (payload: InasistenciaPayload) => {
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

export const saveAnotador = async (payload: AnotadorPayload) => {
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
    console.error("Error saving inasistencias:", error);
    throw error;
  }
};

export const saveDiario = async (payload: DiarioPayload) => {
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
    console.error("Error saving inasistencias:", error);
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
